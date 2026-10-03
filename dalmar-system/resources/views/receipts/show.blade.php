@extends('layouts.app')

@section('title', 'Receipt Details')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Receipt Details</div>
            <div class="page-subtitle">Home / Receipts / {{ $receipt->receipt_number }}</div>
        </div>
        <div class="d-flex gap-2">
            @if($receipt->status !== 'paid')
                <a href="{{ route('payments.index', ['receipt_id' => $receipt->id]) }}" class="btn btn-gold"><i class="bi bi-cash-coin me-1"></i> Record Payment</a>
            @endif
            <a href="{{ route('receipts.print', $receipt) }}" target="_blank" class="btn btn-outline-dark"><i class="bi bi-receipt me-1"></i> POS Receipt (80mm)</a>
            <button class="btn btn-navy" onclick="window.print()"><i class="bi bi-printer-fill me-1"></i> Print Receipt (A4)</button>
        </div>
    </div>

    @if($receipt->is_credit_sale)
        <div class="alert alert-warning fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-exclamation-triangle-fill me-1"></i> CREDIT / DAYN SALE</span>
            @if($receipt->due_date)
                <span class="small fw-normal">Due: {{ $receipt->due_date->format('M d, Y') }} @if($receipt->is_overdue) <span class="text-danger fw-bold">(Overdue)</span> @endif</span>
            @endif
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-5">
            <div class="card-panel">
                <div class="card-panel-title">Receipt Information</div>
                <table class="table-dalmar">
                    <tr><td class="text-muted">Receipt #</td><td class="fw-semibold">{{ $receipt->receipt_number }}</td></tr>
                    <tr><td class="text-muted">Order</td><td><a href="{{ route('orders.show', $receipt->order) }}">#{{ $receipt->order->order_number ?? '-' }}</a></td></tr>
                    <tr><td class="text-muted">Customer</td><td>{{ $receipt->customer->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Issued Date</td><td>{{ $receipt->issued_date->format('M d, Y') }}</td></tr>
                    @if($receipt->due_date)
                        <tr><td class="text-muted">Due Date</td><td>{{ $receipt->due_date->format('M d, Y') }}</td></tr>
                    @endif
                    <tr><td class="text-muted">Payment Status</td><td><span class="badge-status badge-{{ $receipt->status }}">{{ ucfirst($receipt->status) }}</span></td></tr>
                    @if($receipt->order && $receipt->order->discount_amount > 0)
                        <tr><td class="text-muted">Subtotal</td><td>${{ number_format($receipt->order->subtotal, 2) }}</td></tr>
                        <tr><td class="text-muted">Discount</td><td class="text-danger">-${{ number_format($receipt->order->discount_amount, 2) }} ({{ $receipt->order->discount_label }})</td></tr>
                    @endif
                    <tr><td class="text-muted fw-semibold">Grand Total</td><td class="fw-bold">${{ number_format($receipt->amount, 2) }}</td></tr>
                    <tr><td class="text-muted">Amount Paid</td><td>${{ number_format($receipt->paid_amount, 2) }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Remaining Debt</td><td class="fw-bold {{ $receipt->balance_due > 0 ? 'text-danger' : '' }}">${{ number_format($receipt->balance_due, 2) }}</td></tr>
                    @if($receipt->order && $receipt->order->discountBy)
                        <tr><td class="text-muted">Discount By</td><td>{{ $receipt->order->discountBy->name }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card-panel">
                <div class="card-panel-title">Items</div>
                <table class="table-dalmar">
                    <thead>
                    <tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                    @foreach($receipt->order->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? 'Deleted product' }}</td>
                            <td>${{ number_format($item->price, 2) }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>${{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Subtotal</span>
                    <span>${{ number_format($receipt->order->subtotal ?? $receipt->amount, 2) }}</span>
                </div>
                @if($receipt->order && $receipt->order->discount_amount > 0)
                    <div class="d-flex justify-content-between text-danger">
                        <span>Discount ({{ $receipt->order->discount_label }})</span>
                        <span>-${{ number_format($receipt->order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between fw-bold mt-1">
                    <span>Grand Total</span>
                    <span>${{ number_format($receipt->amount, 2) }}</span>
                </div>
            </div>

            <div class="card-panel">
                <div class="card-panel-title">Payments</div>
                <table class="table-dalmar">
                    <thead>
                    <tr><th>Receipt #</th><th>Method</th><th>Sender Phone</th><th>Amount</th><th>Date</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                    @forelse($receipt->payments as $payment)
                        <tr>
                            <td class="fw-semibold">{{ $payment->receipt_number ?? '-' }}</td>
                            <td><span class="badge-method method-{{ $payment->method }}">{{ $payment->method_label }}</span></td>
                            <td>{{ $payment->sender_phone ?? '-' }}</td>
                            <td>${{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->paid_at?->format('M d, Y') ?? '-' }}</td>
                            <td><span class="badge-status badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                            <td>
                                @if($payment->status === 'paid')
                                    <a href="{{ route('payments.show', $payment) }}" class="icon-btn"><i class="bi bi-receipt"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No payments recorded yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
