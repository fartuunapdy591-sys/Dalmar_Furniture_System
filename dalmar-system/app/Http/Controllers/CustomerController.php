<?php

namespace App\Http\Controllers;

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

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::withCount('orders')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $products = Product::where('status', 'active')->orderBy('name')->get();

        return view('customers.index', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'in:active,inactive'],
            'sell_now' => ['nullable', 'boolean'],
            'payment_method' => ['required_if:sell_now,1', 'nullable', 'in:cash,sahal,e_dahab,mycash,card'],
            'items' => ['required_if:sell_now,1', 'nullable', 'array', 'min:1'],
            'items.*.id' => ['required_with:items', 'exists:products,id'],
            'items.*.qty' => ['required_with:items', 'integer', 'min:1'],
        ]);

        $sellNow = $request->boolean('sell_now') && ! empty($data['items']);

        $result = DB::transaction(function () use ($data, $sellNow) {
            $customer = Customer::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]);

            if (! $sellNow) {
                return ['customer' => $customer, 'payment' => null];
            }

            $order = Order::create([
                'order_number' => 'ORD-'.now()->format('Y').'-'.str_pad((Order::max('id') + 1), 3, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
                'order_date' => now()->toDateString(),
                'payment_method' => 'paid',
                'status' => 'completed',
                'total_amount' => 0,
            ]);

            $total = 0;

            foreach ($data['items'] as $line) {
                $product = Product::lockForUpdate()->findOrFail($line['id']);

                if ($product->stock < $line['qty']) {
                    abort(422, "Not enough stock for {$product->name}. Only {$product->stock} left.");
                }

                $lineTotal = $product->price * $line['qty'];
                $total += $lineTotal;

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

            $order->update(['total_amount' => $total]);

            $receipt = Receipt::create([
                'receipt_number' => 'REC-'.now()->format('Y').'-'.str_pad((Receipt::max('id') + 1), 3, '0', STR_PAD_LEFT),
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'amount' => $total,
                'issued_date' => now()->toDateString(),
                'status' => 'paid',
            ]);

            $payment = Payment::create([
                'order_id' => $order->id,
                'receipt_id' => $receipt->id,
                'amount' => $total,
                'method' => $data['payment_method'],
                'status' => 'paid',
                'receipt_number' => 'RCPT-'.now()->format('Y').'-'.str_pad((Payment::max('id') + 1), 3, '0', STR_PAD_LEFT),
                'paid_at' => now(),
            ]);

            return ['customer' => $customer, 'payment' => $payment];
        });

        if ($result['payment']) {
            return redirect()->route('payments.show', $result['payment'])
                ->with('success', 'Customer registered and sale completed successfully.');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $result['customer']->id,
                'name' => $result['customer']->name,
                'phone' => $result['customer']->phone,
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer has been added successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['receipts' => fn ($q) => $q->latest('issued_date'), 'receipts.order']);

        $payments = Payment::whereHas('receipt', fn ($q) => $q->where('customer_id', $customer->id))
            ->with('receipt')
            ->latest('paid_at')
            ->get();

        $creditSales = $customer->receipts->filter(fn ($receipt) => $receipt->order?->is_credit_sale);

        $transactions = collect();

        foreach ($customer->receipts as $receipt) {
            $transactions->push([
                'date' => $receipt->issued_date,
                'type' => 'Receipt',
                'reference' => $receipt->receipt_number,
                'debit' => (float) $receipt->amount,
                'credit' => 0,
                'link' => route('receipts.show', $receipt),
            ]);
        }

        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->paid_at,
                'type' => 'Payment',
                'reference' => $payment->receipt_number ?? '-',
                'debit' => 0,
                'credit' => (float) $payment->amount,
                'link' => route('payments.show', $payment),
            ]);
        }

        $transactions = $transactions->sortBy('date')->values();

        $running = (float) $customer->opening_balance;
        $transactions = $transactions->map(function ($entry) use (&$running) {
            $running += $entry['debit'] - $entry['credit'];
            $entry['balance'] = round($running, 2);

            return $entry;
        })->reverse()->values();

        return view('customers.show', compact('customer', 'payments', 'creditSales', 'transactions'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:customers,email,'.$customer->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'Customer has been updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return back()->with('success', 'Customer has been deleted successfully.');
    }
}
