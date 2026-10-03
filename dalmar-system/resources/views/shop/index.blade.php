@extends('layouts.shop')

@section('title', 'Shop')

@section('content')
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1" style="color: var(--navy);">Our Furniture</h3>
            <div class="text-muted">Browse our collection and place your order below.</div>
        </div>
        <form class="d-flex gap-2" method="GET">
            @if(request('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search furniture...">
            <button class="btn btn-navy" type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>

    @if($categories->count())
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('shop.index') }}" class="btn btn-sm {{ request('category_id') ? 'btn-outline-secondary' : 'btn-navy' }}">All</a>
            @foreach($categories as $category)
                <a href="{{ route('shop.index', ['category_id' => $category->id]) }}"
                   class="btn btn-sm {{ (string) request('category_id') === (string) $category->id ? 'btn-navy' : 'btn-outline-secondary' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    @endif

    <div class="row g-4">
        @forelse($products as $product)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="product-card">
                    <div class="product-img">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                        @else
                            <i class="bi bi-image"></i>
                        @endif
                    </div>
                    <div class="p-3">
                        <div class="fw-semibold mb-1">{{ $product->name }}</div>
                        <div class="fw-bold mb-2" style="color: var(--navy);">${{ number_format($product->price, 2) }}</div>
                        <form action="{{ route('shop.cart.add', $product) }}" method="POST" class="d-flex gap-2">
                            @csrf
                            <input type="number" name="qty" value="1" min="1" max="{{ $product->stock }}" class="form-control form-control-sm" style="width: 60px;">
                            <button type="submit" class="btn btn-navy btn-sm flex-grow-1">
                                <i class="bi bi-cart-plus"></i> Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">No furniture available right now.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
@endsection
