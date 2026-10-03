@extends('layouts.app')
@section('title', 'Search Results')
@section('content')
    <div class="page-title mb-1">Search Results</div>
    <div class="page-subtitle">
        @if($query)
            Showing results for "<strong>{{ $query }}</strong>"
        @else
            Type something in the search bar above to search Products, Customers, and Orders.
        @endif
    </div>

    @if($query)
        <div class="card-panel">
            <div class="card-panel-title">Products ({{ $products->count() }})</div>
            @forelse($products as $product)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <a href="{{ route('products.edit', $product) }}" class="fw-semibold text-decoration-none">{{ $product->name }}</a>
                        <div class="text-muted small">{{ $product->category->name ?? '-' }}</div>
                    </div>
                    <div class="fw-semibold">${{ number_format($product->price, 2) }}</div>
                </div>
            @empty
                <p class="text-muted small mb-0">No matching products.</p>
            @endforelse
        </div>

        <div class="card-panel">
            <div class="card-panel-title">Customers ({{ $customers->count() }})</div>
            @forelse($customers as $customer)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <a href="{{ route('customers.edit', $customer) }}" class="fw-semibold text-decoration-none">{{ $customer->name }}</a>
                        <div class="text-muted small">{{ $customer->email ?? '-' }}</div>
                    </div>
                    <div class="text-muted small">{{ $customer->phone ?? '-' }}</div>
                </div>
            @empty
                <p class="text-muted small mb-0">No matching customers.</p>
            @endforelse
        </div>

        <div class="card-panel">
            <div class="card-panel-title">Orders ({{ $orders->count() }})</div>
            @forelse($orders as $order)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <a href="{{ route('orders.show', $order) }}" class="fw-semibold text-decoration-none">#{{ $order->order_number }}</a>
                        <div class="text-muted small">{{ $order->customer->name ?? '-' }}</div>
                    </div>
                    <div class="fw-semibold">${{ number_format($order->total_amount, 2) }}</div>
                </div>
            @empty
                <p class="text-muted small mb-0">No matching orders.</p>
            @endforelse
        </div>
    @endif
@endsection
