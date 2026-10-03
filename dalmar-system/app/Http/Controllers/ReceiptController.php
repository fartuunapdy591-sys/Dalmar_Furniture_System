<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        $filtered = fn () => Receipt::query()
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where('receipt_number', 'like', "%{$request->search}%")
                    ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$request->search}%"))
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$request->search}%"));
            });

        $receipts = $filtered()
            ->with('customer', 'order')
            ->latest('issued_date')
            ->paginate(10)
            ->withQueryString();

        $totalReceipts = $filtered()->count();
        $totalAmount = (float) $filtered()->sum('amount');
        $outstandingAmount = $filtered()->whereIn('status', ['unpaid', 'partial'])->get()->sum('balance_due');
        $overdueCount = $filtered()
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        return view('receipts.index', compact('receipts', 'totalReceipts', 'totalAmount', 'outstandingAmount', 'overdueCount'));
    }

    public function show(Receipt $receipt)
    {
        $receipt->load('customer', 'order.items.product', 'payments');

        return view('receipts.show', compact('receipt'));
    }

    public function printReceipt(Receipt $receipt)
    {
        $receipt->load('customer', 'order.items.product', 'order.user', 'payments');
        $setting = \App\Models\Setting::current();

        return view('receipts.print', compact('receipt', 'setting'));
    }
}
