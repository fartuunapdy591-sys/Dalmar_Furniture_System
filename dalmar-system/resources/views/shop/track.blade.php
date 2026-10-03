@extends('layouts.shop')

@section('title', 'Track Order')

@section('content')
    <h3 class="fw-bold mb-4 text-center" style="color: var(--navy);">Track Your Order</h3>

    <div style="background:#fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 10px rgba(16,25,46,.06); max-width: 520px; margin: 0 auto;">
        <form action="{{ route('shop.track.result') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold">Order Number</label>
                <input type="text" name="order_number" value="{{ old('order_number') }}" class="form-control" placeholder="e.g. ORD-2026-008" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Phone Number Used When Ordering</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-navy w-100">Check Status</button>
        </form>
    </div>

    @isset($order)
        <div class="mt-4" style="background:#fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 10px rgba(16,25,46,.06); max-width: 640px; margin: 24px auto 0;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0" style="color: var(--navy);">Order #{{ $order->order_number }}</h5>
                <span class="badge-status badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <div class="small text-muted">Order Date</div>
                    <div class="fw-semibold">{{ $order->order_date->format('M d, Y') }}</div>
                </div>
                <div class="col-6">
                    <div class="small text-muted">Delivery Address</div>
                    <div class="fw-semibold">{{ $order->shipping_address }}</div>
                </div>
            </div>
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'Deleted product' }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>${{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-between fw-bold pt-2 border-top">
                <span>Order Total</span>
                <span style="color: var(--navy);">${{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    @endisset
@endsection
