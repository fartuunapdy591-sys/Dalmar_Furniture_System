@extends('layouts.app')

@section('title', 'Receipt')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Receipt</div>
            <div class="page-subtitle">Home / Payments / {{ $payment->receipt_number ?? '#'.$payment->id }}</div>
        </div>
        <button class="btn btn-navy" onclick="window.print()"><i class="bi bi-printer-fill me-1"></i> Print Receipt</button>
    </div>

    <div class="card-panel mx-auto" style="max-width: 480px;">
        <div class="text-center mb-3">
            <div class="brand-title" style="color: var(--navy); font-size: 20px;">DALMAR</div>
            <div class="text-muted small">FURNITURE &amp; HOUSE INTERIOR</div>
        </div>
        <hr>
        <table class="table-dalmar">
            <tr><td class="text-muted">Receipt #</td><td class="fw-semibold text-end">{{ $payment->receipt_number ?? '-' }}</td></tr>
            <tr><td class="text-muted">Order</td><td class="text-end">#{{ $payment->order->order_number ?? '-' }}</td></tr>
            <tr><td class="text-muted">Customer</td><td class="text-end">{{ $payment->receipt->customer->name ?? '-' }}</td></tr>
            <tr><td class="text-muted">Date</td><td class="text-end">{{ $payment->paid_at?->format('M d, Y h:i A') ?? '-' }}</td></tr>
            <tr><td class="text-muted">Payment Method</td><td class="text-end"><span class="badge-method method-{{ $payment->method }}">{{ $payment->method_label }}</span></td></tr>
            @if($payment->sender_phone)
                <tr><td class="text-muted">Sender's Phone</td><td class="text-end fw-semibold">{{ $payment->sender_phone }}</td></tr>
            @endif
            @if($payment->reference)
                <tr><td class="text-muted">Reference</td><td class="text-end">{{ $payment->reference }}</td></tr>
            @endif
            <tr><td class="text-muted">Status</td><td class="text-end"><span class="badge-status badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td></tr>
        </table>
        <hr>
        <div class="d-flex justify-content-between fw-bold" style="font-size: 18px;">
            <span>Amount Paid</span>
            <span>${{ number_format($payment->amount, 2) }}</span>
        </div>
        @if($payment->receipt)
            <div class="d-flex justify-content-between text-muted small mt-2">
                <span>Balance Remaining</span>
                <span>${{ number_format($payment->receipt->balance_due, 2) }}</span>
            </div>
        @endif
        <hr>
        <p class="text-center text-muted small mb-0">Thank you for your business!</p>
    </div>

    @if($payment->receipt)
        <div class="text-center mt-3">
            <a href="{{ route('receipts.show', $payment->receipt) }}" class="btn btn-light btn-sm">View Receipt</a>
        </div>
    @endif
@endsection
