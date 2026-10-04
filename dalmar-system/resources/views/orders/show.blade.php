@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Order Details</div>
            <div class="page-subtitle">Home / Orders / #{{ $order->order_number }}</div>
        </div>
        <div class="d-flex gap-2">
            @if($order->receipt)
                <a href="{{ route('receipts.show', $order->receipt) }}" class="btn btn-light"><i class="bi bi-receipt me-1"></i> View Receipt</a>
            @endif
            <button class="btn btn-navy" onclick="window.print()"><i class="bi bi-printer-fill me-1"></i> Print Receipt</button>
        </div>
    </div>

    @if($order->is_credit_sale)
        <div class="alert alert-warning fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> CREDIT / DAYN SALE
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-5">
            <div class="card-panel">
                <div class="card-panel-title">Order Information</div>
                <table class="table-dalmar">
                    <tr><td class="text-muted">Order ID</td><td class="fw-semibold">#{{ $order->order_number }}</td></tr>
                    <tr><td class="text-muted">Customer</td><td>{{ $order->customer->name }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $order->customer->email ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Phone</td><td>{{ $order->customer->phone ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Order Date</td><td>{{ $order->order_date->format('M d, Y') }}</td></tr>
                    <tr><td class="text-muted">Payment Method</td><td>{{ ucfirst($order->payment_method) }}</td></tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            <form action="{{ route('orders.status', $order) }}" method="POST" class="d-flex gap-2">
                                @csrf @method('PATCH')
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="pending" @selected($order->status === 'pending')>Pending</option>
                                    <option value="processing" @selected($order->status === 'processing')>Processing</option>
                                    <option value="completed" @selected($order->status === 'completed')>Completed</option>
                                    <option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <tr><td class="text-muted">Shipping Address</td><td>{{ $order->shipping_address ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card-panel">
                <div class="card-panel-title">Order Items</div>
                <table class="table-dalmar">
                    <thead>
                    <tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                    @foreach($order->items as $item)
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
                    <span>${{ number_format($order->subtotal ?? $order->total_amount, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="d-flex justify-content-between text-danger">
                        <span>Discount ({{ $order->discount_label }}) @if($order->discountBy) &mdash; by {{ $order->discountBy->name }} @endif</span>
                        <span>-${{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between fw-bold mt-2">
                    <span>Total Amount</span>
                    <span>${{ number_format($order->total_amount, 2) }}</span>
                </div>

                @if(auth()->user()->canManageDiscounts() && in_array($order->status, ['completed', 'cancelled']))
                    <hr>
                    <div class="small text-muted">To add or change a discount, set the status to Pending or Processing first.</div>
                @endif

                @if(auth()->user()->canManageDiscounts() && ! in_array($order->status, ['completed', 'cancelled']))
                    <hr>
                    <form action="{{ route('orders.discount', $order) }}" method="POST" class="row g-2 align-items-end">
                        @csrf @method('PATCH')
                        <div class="col-5">
                            <label class="form-label small fw-semibold">Discount</label>
                            <select name="discount_type" class="form-select form-select-sm">
                                <option value="percentage" @selected($order->discount_type === 'percentage')>Percent (%)</option>
                                <option value="fixed" @selected($order->discount_type === 'fixed')>Fixed ($)</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <input type="number" step="0.01" min="0" name="discount_value" class="form-control form-control-sm" value="{{ $order->discount_value ?? 0 }}">
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-navy btn-sm w-100">Apply</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
