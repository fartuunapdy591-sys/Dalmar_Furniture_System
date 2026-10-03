<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
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

class PosController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $categories = Category::where('status', 'active')->get();
        $customers = Customer::where('status', 'active')->get();

        $canDiscount = Auth::user()->canManageDiscounts();
        $canCreditSale = Auth::user()->canCreateCreditSales();

        return view('pos.index', compact('products', 'categories', 'customers', 'canDiscount', 'canCreditSale'));
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:cash,sahal,e_dahab,mycash,card'],
            'sender_phone' => ['nullable', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'discount_type' => ['nullable', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'is_credit_sale' => ['nullable', 'boolean'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
        ]);

        $discountType = $data['discount_type'] ?? null;
        $discountValue = (float) ($data['discount_value'] ?? 0);
        $isCreditSale = $request->boolean('is_credit_sale');

        if ($discountType && $discountValue > 0 && ! $user->canManageDiscounts()) {
            abort(403, 'You are not authorized to apply discounts.');
        }

        if ($isCreditSale && ! $user->canCreateCreditSales()) {
            abort(403, 'You are not authorized to create credit sales.');
        }

        $walkInId = $this->walkInCustomerId();
        $customerId = $data['customer_id'] ?? $walkInId;

        if ($isCreditSale && $customerId === $walkInId) {
            return back()->withInput()->withErrors(['customer_id' => 'A registered customer must be selected for a credit sale.']);
        }

        $result = DB::transaction(function () use ($data, $customerId, $discountType, $discountValue, $isCreditSale, $user) {
            $order = Order::create([
                'order_number' => 'ORD-'.now()->format('Y').'-'.str_pad((Order::max('id') + 1), 3, '0', STR_PAD_LEFT),
                'customer_id' => $customerId,
                'user_id' => Auth::id(),
                'order_date' => now()->toDateString(),
                'payment_method' => 'paid',
                'status' => 'completed',
                'total_amount' => 0,
                'is_credit_sale' => $isCreditSale,
            ]);

            $subtotal = 0;

            foreach ($data['items'] as $line) {
                $product = Product::lockForUpdate()->findOrFail($line['id']);

                if ($product->stock < $line['qty']) {
                    abort(422, "Not enough stock for {$product->name}. Only {$product->stock} left.");
                }

                $lineTotal = $product->price * $line['qty'];
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'price' => $product->price,
                    'qty' => $line['qty'],
                    'total' => $lineTotal,
                ]);

                $product->decrement('stock', $line['qty']);
                StockMovement::log($product, 'sale', -$line['qty'], $order, 'POS sale '.$order->order_number);
            }

            $discountAmount = 0;
            if ($discountType && $discountValue > 0) {
                $discountAmount = $discountType === 'percentage'
                    ? $subtotal * min($discountValue, 100) / 100
                    : $discountValue;
                $discountAmount = round(min($discountAmount, $subtotal), 2);
            }

            $total = round($subtotal - $discountAmount, 2);

            $order->update([
                'subtotal' => $subtotal,
                'discount_type' => $discountAmount > 0 ? $discountType : null,
                'discount_value' => $discountAmount > 0 ? $discountValue : 0,
                'discount_amount' => $discountAmount,
                'discount_by' => $discountAmount > 0 ? $user->id : null,
                'total_amount' => $total,
            ]);

            $amountPaid = $isCreditSale
                ? min((float) ($data['amount_paid'] ?? 0), $total)
                : $total;

            $receipt = Receipt::create([
                'receipt_number' => 'REC-'.now()->format('Y').'-'.str_pad((Receipt::max('id') + 1), 3, '0', STR_PAD_LEFT),
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'amount' => $total,
                'issued_date' => now()->toDateString(),
                'due_date' => $isCreditSale ? ($data['due_date'] ?? now()->addDays(30)->toDateString()) : null,
                'status' => 'unpaid',
            ]);

            if ($amountPaid > 0) {
                Payment::create([
                    'order_id' => $order->id,
                    'receipt_id' => $receipt->id,
                    'amount' => $amountPaid,
                    'method' => $data['payment_method'],
                    'sender_phone' => $data['sender_phone'] ?? null,
                    'status' => 'paid',
                    'receipt_number' => 'RCPT-'.now()->format('Y').'-'.str_pad((Payment::max('id') + 1), 3, '0', STR_PAD_LEFT),
                    'paid_at' => now(),
                ]);
            } else {
                // Credit sale with no down payment: still record it so the order
                // shows up in the Payments list instead of being invisible until
                // the first debt payment comes in.
                Payment::create([
                    'order_id' => $order->id,
                    'receipt_id' => $receipt->id,
                    'amount' => $total,
                    'method' => $data['payment_method'],
                    'status' => 'pending',
                ]);
            }

            $receipt->refreshStatus();

            if ($discountAmount > 0) {
                ActivityLog::record('discount_applied', $order, "{$user->name} applied a {$order->discount_label} discount (\${$discountAmount}) to order {$order->order_number}.");
            }

            if ($isCreditSale) {
                ActivityLog::record('credit_sale_created', $order, "{$user->name} created a credit sale for {$order->customer->name} - order {$order->order_number}, total \${$total}, paid \${$amountPaid}, remaining \${$receipt->balance_due}.");
            }

            return $receipt;
        });

        $message = $isCreditSale ? 'Credit sale recorded successfully.' : 'Sale completed successfully.';

        return redirect()->route('receipts.show', $result)->with('success', $message);
    }

    private function walkInCustomerId(): int
    {
        return Customer::firstOrCreate(
            ['email' => 'walkin@dalmar.local'],
            ['name' => 'Walk-in Customer', 'phone' => 'N/A', 'status' => 'active']
        )->id;
    }
}
