<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shop') | Dalmar Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6fb; }
        .shop-navbar { background: var(--navy-dark); box-shadow: 0 2px 12px rgba(16,25,46,.15); }
        .shop-navbar .navbar-brand { color: #fff; font-weight: 700; letter-spacing: .5px; }
        .shop-navbar .navbar-brand small { display: block; font-size: 10px; font-weight: 400; color: #b9c2d8; letter-spacing: 1px; }
        .shop-navbar .nav-link { color: #dbe1ee; font-weight: 500; }
        .shop-navbar .nav-link:hover, .shop-navbar .nav-link.active-link { color: var(--gold); }
        .cart-badge { background: var(--gold); color: var(--navy-dark); border-radius: 30px; font-size: 11px; font-weight: 700; padding: 2px 8px; margin-left: 4px; }
        .btn-login-nav { background: transparent; border: 1px solid rgba(255,255,255,.35); color: #fff; font-weight: 600; font-size: 13px; padding: 6px 16px; border-radius: 30px; transition: all .15s; }
        .btn-login-nav:hover { background: var(--gold); border-color: var(--gold); color: var(--navy-dark); }
        .product-card { border: none; border-radius: 14px; box-shadow: 0 2px 10px rgba(16,25,46,.06); overflow: hidden; height: 100%; transition: transform .15s, box-shadow .15s; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(16,25,46,.12); }
        .product-card .product-img { height: 180px; background: #eef1f8; display: flex; align-items: center; justify-content: center; }
        .product-card .product-img img { width: 100%; height: 100%; object-fit: cover; }
        .product-card .product-img i { font-size: 46px; color: var(--navy); opacity: .3; }
        .shop-footer { background: var(--navy-dark); color: #b9c2d8; font-size: 13px; padding: 40px 0 20px; margin-top: 60px; }
        .shop-footer h6 { color: #fff; font-weight: 700; margin-bottom: 14px; }
        .shop-footer a { color: #b9c2d8; text-decoration: none; }
        .shop-footer a:hover { color: var(--gold); }
        .shop-footer .footer-bottom { border-top: 1px solid rgba(255,255,255,.1); margin-top: 24px; padding-top: 18px; text-align: center; color: #7d8aa3; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg shop-navbar py-3 mb-0">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            DALMAR FURNITURE
            <small>&amp; HOUSE INTERIOR</small>
        </a>
        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#shopNav">
            <i class="bi bi-list fs-3 text-white"></i>
        </button>
        <div class="collapse navbar-collapse" id="shopNav">
            <div class="navbar-nav me-auto">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active-link fw-bold' : '' }}">Home</a>
                <a href="{{ route('shop.index') }}" class="nav-link {{ request()->routeIs('shop.index') ? 'active-link fw-bold' : '' }}">Shop</a>
                <a href="{{ route('shop.about') }}" class="nav-link {{ request()->routeIs('shop.about') ? 'active-link fw-bold' : '' }}">About Us</a>
                <a href="{{ route('shop.track') }}" class="nav-link {{ request()->routeIs('shop.track*') ? 'active-link fw-bold' : '' }}">Track Order</a>
                <a href="{{ route('shop.contact') }}" class="nav-link {{ request()->routeIs('shop.contact*') ? 'active-link fw-bold' : '' }}">Contact</a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('shop.cart') }}" class="nav-link position-relative">
                    <i class="bi bi-cart3 fs-5"></i> My Order
                    @php($cartCount = collect(session('cart', []))->sum())
                    @if($cartCount > 0)
                        <span class="cart-badge">{{ $cartCount }}</span>
                    @endif
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-login-nav">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-login-nav">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

@hasSection('hero')
    @yield('hero')
@endif

<div class="container pb-5 pt-4">
    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>

<footer class="shop-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h6>DALMAR FURNITURE</h6>
                <p class="mb-0">Quality furniture and house interior solutions for every home and office.</p>
            </div>
            <div class="col-md-4">
                <h6>Quick Links</h6>
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('home') }}">Home</a>
                    <a href="{{ route('shop.index') }}">Shop</a>
                    <a href="{{ route('shop.about') }}">About Us</a>
                    <a href="{{ route('shop.contact') }}">Contact</a>
                </div>
            </div>
            <div class="col-md-4">
                <h6>Contact</h6>
                <p class="mb-1"><i class="bi bi-geo-alt-fill me-1"></i> Mogadishu, Somalia</p>
                <p class="mb-1"><i class="bi bi-telephone-fill me-1"></i> +252 61 XXX XXXX</p>
                <p class="mb-0"><i class="bi bi-envelope-fill me-1"></i> info@dalmarfurniture.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; {{ date('Y') }} Dalmar Furniture &amp; House Interior. All rights reserved.
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
