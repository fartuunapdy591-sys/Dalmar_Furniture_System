<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('customer', 'items.product')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where('order_number', 'like', "%{$request->search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$request->search}%"));
            })
            ->latest('order_date')
            ->paginate(10)
            ->withQueryString();

        $customers = Customer::where('status', 'active')->get();
        $products = Product::where('status', 'active')->orderBy('name')->get();

        return view('orders.index', compact('orders', 'customers', 'products'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'order_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,sahal,e_dahab,mycash,card'],
            'shipping_address' => ['nullable', 'string', 'max:255'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'exists:products,id'],
            'products.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        // The order is only recorded as pending here. No receipt or payment is
        // created until the order status is changed to "completed" (see updateStatus),
        // so that a payment only appears once the work for the customer is finished.
        $order = DB::transaction(function () use ($data) {
            $total = 0;
            foreach ($data['products'] as $line) {
                $product = Product::findOrFail($line['id']);
                $total += $product->price * $line['qty'];
            }

            $order = Order::create([
                'order_number' => 'ORD-'.now()->format('Y').'-'.str_pad((Order::max('id') + 1), 3, '0', STR_PAD_LEFT),
                'customer_id' => $data['customer_id'],
                'user_id' => Auth::id(),
                'order_date' => $data['order_date'],
                'payment_method' => $data['payment_method'],
                'is_credit_sale' => false,
                'shipping_address' => $data['shipping_address'] ?? null,
                'status' => 'pending',
                'total_amount' => 0,
            ]);

            foreach ($data['products'] as $line) {
                $product = Product::findOrFail($line['id']);
                $lineTotal = $product->price * $line['qty'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'price' => $product->price,
                    'qty' => $line['qty'],
                    'total' => $lineTotal,
                ]);

                $product->decrement('stock', $line['qty']);
            }

            $order->update(['total_amount' => $total, 'subtotal' => $total]);

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', 'Order has been created successfully.');
    }

    public function show(Order $order)
    {
        $order->load('customer', 'items.product', 'receipt.payments', 'user');

        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'in:pending,processing,completed,cancelled'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return back()->with('info', 'Order is already in this status.');
        }

        DB::transaction(function () use ($order, $oldStatus, $newStatus) {
            $order->load('items.product', 'receipt');

            // If cancelling an active order, restore stock
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->qty);
                        StockMovement::create([
                            'product_id' => $item->product_id,
                            'type' => 'return_in',
                            'quantity' => $item->qty,
                            'reference_type' => Order::class,
                            'reference_id' => $order->id,
                            'user_id' => Auth::id(),
                            'notes' => 'Restored stock from cancelled order '.$order->order_number,
                        ]);
                    }
                }

                if ($order->receipt) {
                    $order->receipt->update(['status' => 'cancelled']);
                }

                ActivityLog::record('order_cancelled', $order, Auth::user()->name." cancelled order {$order->order_number} and restored stock items.");
            }

            // If re-activating a cancelled order, deduct stock back
            if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);
                    if ($product->stock < $item->qty) {
                        abort(422, "Cannot re-activate order. Not enough stock for {$product->name} (only {$product->stock} available).");
                    }
                    $product->decrement('stock', $item->qty);
                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'sale',
                        'quantity' => -$item->qty,
                        'reference_type' => Order::class,
                        'reference_id' => $order->id,
                        'user_id' => Auth::id(),
                        'notes' => 'Deducted stock for re-activated order '.$order->order_number,
                    ]);
                }

                if ($order->receipt) {
                    $order->receipt->refreshStatus();
                }

                ActivityLog::record('order_reactivated', $order, Auth::user()->name." re-activated order {$order->order_number} to {$newStatus}.");
            }

            // The payment only enters the system once the order is marked completed.
            if ($newStatus === 'completed' && ! $order->receipt) {
                $receipt = Receipt::create([
                    'receipt_number' => 'REC-'.now()->format('Y').'-'.str_pad((Receipt::max('id') + 1), 3, '0', STR_PAD_LEFT),
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'amount' => $order->total_amount,
                    'issued_date' => now()->toDateString(),
                    'status' => 'unpaid',
                ]);

                Payment::create([
                    'order_id' => $order->id,
                    'receipt_id' => $receipt->id,
                    'amount' => $order->total_amount,
                    'method' => $order->payment_method ?: 'cash',
                    'status' => 'paid',
                    'receipt_number' => 'RCPT-'.now()->format('Y').'-'.str_pad((Payment::max('id') + 1), 3, '0', STR_PAD_LEFT),
                    'paid_at' => now(),
                ]);

                $receipt->refreshStatus();

                ActivityLog::record('order_completed', $order, Auth::user()->name." marked order {$order->order_number} as completed and recorded payment of \${$order->total_amount}.");
            }

            $order->update(['status' => $newStatus]);
        });

        return back()->with('success', 'Order status has been updated successfully.');
    }

    public function applyDiscount(Request $request, Order $order)
    {
        $user = Auth::user();

        if (! $user->canManageDiscounts()) {
            abort(403, 'You are not authorized to apply discounts.');
        }

        if (in_array($order->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Discount can only be changed on pending or processing orders.');
        }

        $data = $request->validate([
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
        ]);

        $subtotal = (float) ($order->subtotal ?? $order->total_amount);
        $amount = $data['discount_type'] === 'percentage'
            ? $subtotal * min($data['discount_value'], 100) / 100
            : $data['discount_value'];
        $amount = round(min($amount, $subtotal), 2);

        $order->update([
            'subtotal' => $subtotal,
            'discount_type' => $amount > 0 ? $data['discount_type'] : null,
            'discount_value' => $amount > 0 ? $data['discount_value'] : 0,
            'discount_amount' => $amount,
            'discount_by' => $amount > 0 ? $user->id : null,
            'total_amount' => round($subtotal - $amount, 2),
        ]);

        if ($amount > 0) {
            ActivityLog::record('discount_applied', $order, "{$user->name} applied a {$order->discount_label} discount (\${$amount}) to order {$order->order_number}.");
        }

        return back()->with('success', 'Discount has been updated.');
    }

    public function destroy(Order $order)
    {
        DB::transaction(function () use ($order) {
            $order->load('items.product');

            // If order was not cancelled, return stock before deleting
            if ($order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->qty);
                        StockMovement::create([
                            'product_id' => $item->product_id,
                            'type' => 'return_in',
                            'quantity' => $item->qty,
                            'reference_type' => Order::class,
                            'reference_id' => $order->id,
                            'user_id' => Auth::id(),
                            'notes' => 'Restored stock before deleting order '.$order->order_number,
                        ]);
                    }
                }
            }

            $order->delete();
        });

        return back()->with('success', 'Order has been deleted successfully.');
    }
}
