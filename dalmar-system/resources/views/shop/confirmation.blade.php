@extends('layouts.shop')

@section('title', 'Order Confirmed')

@section('content')
    <div class="text-center mb-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 54px;"></i>
        <h3 class="fw-bold mt-3" style="color: var(--navy);">Thank you, {{ $order->customer->name }}!</h3>
        <p class="text-muted">Your order <strong>#{{ $order->order_number }}</strong> has been received and is pending confirmation.</p>
    </div>

    <div style="background:#fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 10px rgba(16,25,46,.06); max-width: 640px; margin: 0 auto;">
        <div class="row mb-3">
            <div class="col-6">
                <div class="small text-muted">Phone</div>
                <div class="fw-semibold">{{ $order->customer->phone }}</div>
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

        <div class="d-flex justify-content-between fw-bold fs-5 pt-3 border-top">
            <span>Order Total</span>
            <span style="color: var(--navy);">${{ number_format($order->total_amount, 2) }}</span>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('shop.index') }}" class="btn btn-navy">Continue Shopping</a>
    </div>
@endsection
