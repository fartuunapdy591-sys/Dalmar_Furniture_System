<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with('receipt.customer', 'order')
            ->when($request->method, fn ($q) => $q->where('method', $request->method))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where('receipt_number', 'like', "%{$request->search}%")
                    ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$request->search}%"))
                    ->orWhereHas('receipt.customer', fn ($c) => $c->where('name', 'like', "%{$request->search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $receipts = Receipt::with('customer')
            ->whereIn('status', ['unpaid', 'partial'])
            ->latest('issued_date')
            ->get();

        $selectedReceiptId = $request->integer('receipt_id') ?: null;

        return view('payments.index', compact('payments', 'receipts', 'selectedReceiptId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'receipt_id' => ['required', 'exists:receipts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,sahal,e_dahab,mycash,card'],
            'sender_phone' => ['nullable', 'string', 'max:30'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $receipt = Receipt::findOrFail($data['receipt_id']);

        if ($data['amount'] > $receipt->balance_due) {
            return back()->withInput()->withErrors([
                'amount' => 'Amount exceeds the remaining balance of $'.number_format($receipt->balance_due, 2).'.',
            ]);
        }

        $payment = Payment::create([
            'order_id' => $receipt->order_id,
            'receipt_id' => $receipt->id,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'sender_phone' => $data['sender_phone'] ?? null,
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'paid',
            'receipt_number' => 'RCPT-'.now()->format('Y').'-'.str_pad((Payment::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'paid_at' => now(),
        ]);

        $receipt->payments()->where('status', 'pending')->delete();

        $receipt->refreshStatus();

        return redirect()->route('payments.show', $payment)->with('success', 'Payment has been recorded successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load('receipt.customer', 'order.items.product', 'order.user', 'createdBy');

        $setting = Setting::current();

        return view('payments.show', compact('payment', 'setting'));
    }
}
