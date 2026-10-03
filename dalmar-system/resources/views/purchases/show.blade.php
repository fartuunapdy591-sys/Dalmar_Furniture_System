@extends('layouts.app')
@section('title', 'Purchase Details')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">{{ $purchase->purchase_number }}</div>
            <div class="page-subtitle">Home / Purchases / {{ $purchase->purchase_number }}</div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card-panel">
                <div class="text-muted small">Supplier</div>
                <div class="fw-semibold"><a href="{{ route('suppliers.show', $purchase->supplier) }}">{{ $purchase->supplier->name ?? '-' }}</a></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-panel">
                <div class="text-muted small">Purchase Date</div>
                <div class="fw-semibold">{{ $purchase->purchase_date->format('M d, Y') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-panel">
                <div class="text-muted small">Total</div>
                <div class="fw-semibold">${{ number_format($purchase->total, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-panel">
                <div class="text-muted small">Status</div>
                <div><span class="badge-status badge-{{ $purchase->status }}">{{ ucfirst($purchase->status) }}</span></div>
            </div>
        </div>
    </div>

    <div class="card-panel mb-3">
        <div class="fw-semibold mb-2">Items</div>
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>Product</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Unit Cost</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($purchase->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? '-' }}</td>
                    <td class="text-end">{{ $item->qty }}</td>
                    <td class="text-end">${{ number_format($item->unit_cost, 2) }}</td>
                    <td class="text-end">${{ number_format($item->discount, 2) }}</td>
                    <td class="text-end">${{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-end">
            <table class="table-dalmar" style="max-width: 300px;">
                <tr><td class="text-muted">Subtotal</td><td class="text-end">${{ number_format($purchase->subtotal, 2) }}</td></tr>
                <tr><td class="text-muted">Discount</td><td class="text-end">-${{ number_format($purchase->discount, 2) }}</td></tr>
                <tr><td class="text-muted">Tax</td><td class="text-end">+${{ number_format($purchase->tax, 2) }}</td></tr>
                <tr><td class="fw-bold">Total</td><td class="text-end fw-bold">${{ number_format($purchase->total, 2) }}</td></tr>
                <tr><td class="text-muted">Paid</td><td class="text-end">${{ number_format($purchase->paid_amount, 2) }}</td></tr>
                <tr><td class="text-muted">Balance Due</td><td class="text-end fw-semibold text-danger">${{ number_format($purchase->balance_due, 2) }}</td></tr>
            </table>
        </div>
        @if($purchase->notes)
            <div class="text-muted small mt-2">Notes: {{ $purchase->notes }}</div>
        @endif
    </div>

    <div class="card-panel">
        <div class="fw-semibold mb-2">Payments</div>
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>Payment #</th>
                <th>Date</th>
                <th>Method</th>
                <th class="text-end">Amount</th>
            </tr>
            </thead>
            <tbody>
            @forelse($purchase->payments as $payment)
                <tr>
                    <td>{{ $payment->payment_number }}</td>
                    <td>{{ $payment->paid_at->format('M d, Y') }}</td>
                    <td><span class="badge-method method-{{ $payment->method }}">{{ $payment->method_label }}</span></td>
                    <td class="text-end">${{ number_format($payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No payments recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
