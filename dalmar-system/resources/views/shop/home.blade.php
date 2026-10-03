@extends('layouts.shop')

@section('title', 'Home')

@section('hero')
    <style>
        .hero-section {
            background: var(--navy-dark);
            color: #fff;
            padding: 90px 0;
            position: relative;
            overflow: hidden;
            isolation: isolate;
        }
        /* photo layer (blurred so the small source image stays smooth) */
        .hero-section::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -2;
            background: url('{{ asset('images/login-livingroom.jpg') }}') center / cover no-repeat;
            filter: saturate(1.1);
        }
        /* navy + gold overlay on top of the photo */
        .hero-section::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                radial-gradient(circle at 85% 15%, rgba(224, 162, 34, .28) 0%, transparent 45%),
                linear-gradient(110deg, rgba(10, 17, 38, .90) 0%, rgba(20, 31, 66, .72) 55%, rgba(36, 52, 92, .45) 100%);
        }
        .hero-section h1 { text-shadow: 0 4px 24px rgba(0,0,0,.35); }
        .hero-section h1 { font-weight: 700; font-size: 44px; line-height: 1.2; }
        .hero-section p.lead { color: #c7cfe2; font-size: 18px; max-width: 520px; }
        .hero-badge { display: inline-block; background: rgba(224,162,34,.15); color: var(--gold); font-weight: 600; font-size: 12px; letter-spacing: 1px; padding: 6px 16px; border-radius: 30px; margin-bottom: 18px; }
        .hero-art { font-size: 200px; color: rgba(255,255,255,.08); text-align: center; }
        .hero-img-wrap { position: relative; z-index: 1; }
        .hero-img-wrap img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,.35);
            border: 4px solid rgba(255,255,255,.08);
        }

        .trust-strip { background: #fff; box-shadow: 0 2px 10px rgba(16,25,46,.06); }
        .trust-strip .trust-item { padding: 22px 0; text-align: center; }
        .trust-strip i { font-size: 26px; color: var(--navy); }
        .trust-strip .trust-label { font-size: 13px; font-weight: 600; color: var(--navy); margin-top: 6px; }

        .section-title { font-weight: 700; color: var(--navy); }
        .section-subtitle { color: #8b93a7; }

        .cta-banner {
            background: linear-gradient(120deg, var(--navy) 0%, var(--navy-dark) 100%);
            border-radius: 18px;
            color: #fff;
            padding: 48px;
        }
    </style>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="hero-badge">TRUSTED FURNITURE STORE</span>
                    <h1>Furnish Your Home With Comfort &amp; Style</h1>
                    <p class="lead my-3">
                        Dalmar Furniture &amp; House Interior brings you quality, affordable furniture for every
                        room — from beds and sofas to office and dining sets. Order online, we deliver to your door.
                    </p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="{{ route('shop.index') }}" class="btn btn-gold btn-lg px-4"><i class="bi bi-cart3 me-1"></i> Shop Now</a>
                        <a href="{{ route('shop.about') }}" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="hero-img-wrap">
                        <img src="{{ asset('images/login-furniture.jpg') }}" alt="Modern furniture living room">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="trust-strip">
        <div class="container">
            <div class="row">
                <div class="col-6 col-md-3 trust-item">
                    <i class="bi bi-truck"></i>
                    <div class="trust-label">Fast Delivery</div>
                </div>
                <div class="col-6 col-md-3 trust-item">
                    <i class="bi bi-shield-check"></i>
                    <div class="trust-label">Quality Guaranteed</div>
                </div>
                <div class="col-6 col-md-3 trust-item">
                    <i class="bi bi-tags"></i>
                    <div class="trust-label">Best Prices</div>
                </div>
                <div class="col-6 col-md-3 trust-item">
                    <i class="bi bi-headset"></i>
                    <div class="trust-label">Friendly Support</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="text-center mb-4">
        <div class="section-subtitle text-uppercase small fw-semibold" style="letter-spacing: 1px;">Our Collection</div>
        <h3 class="section-title">Featured Furniture</h3>
    </div>

    <div class="row g-4 mb-5">
        @forelse($featured as $product)
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
                            <button type="submit" class="btn btn-navy btn-sm flex-grow-1"><i class="bi bi-cart-plus"></i> Order</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-4">No furniture available right now. Check back soon!</div>
        @endforelse
    </div>

    <div class="text-center mb-5">
        <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary px-4">View All Furniture <i class="bi bi-arrow-right ms-1"></i></a>
    </div>

    <div class="cta-banner d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Need help choosing the right furniture?</h4>
            <p class="mb-0" style="color:#c7cfe2;">Our team is ready to help you find exactly what your home needs.</p>
        </div>
        <a href="{{ route('shop.contact') }}" class="btn btn-gold px-4">Contact Us</a>
    </div>
@endsection
