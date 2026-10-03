<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Dalmar Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body class="@yield('body-class', '')">
<div class="d-flex">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="bi bi-house-door-fill"></i></div>
            <div>
                <div class="brand-title">DALMAR</div>
                <div class="brand-sub">FURNITURE &amp; HOUSE INTERIOR</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            @if(auth()->user()?->canAccessInventory())
            <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam-fill"></i> Products
            </a>
            <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i> Categories
            </a>
            <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Customers
            </a>
            <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }} d-flex align-items-center justify-content-between">
                <span><i class="bi bi-envelope-fill"></i> Messages</span>
                @if(($unreadMessagesCount ?? 0) > 0)
                    <span class="badge rounded-pill bg-danger">{{ $unreadMessagesCount }}</span>
                @endif
            </a>
            @endif
            @if(auth()->user()?->canAccessSales())
            <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'active' : '' }} d-flex align-items-center justify-content-between">
                <span><i class="bi bi-cart-check-fill"></i> Orders</span>
                @if(($notifPendingOrdersCount ?? 0) > 0)
                    <span class="badge rounded-pill bg-danger">{{ $notifPendingOrdersCount }}</span>
                @endif
            </a>
            <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <i class="bi bi-shop-window"></i> POS
            </a>
            @endif
            <a href="{{ route('receipts.index') }}" class="{{ request()->routeIs('receipts.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Receipts
            </a>
            <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card-2-front-fill"></i> Payments
            </a>
            <a href="{{ route('customer-debts.index') }}" class="{{ request()->routeIs('customer-debts.*') ? 'active' : '' }}">
                <i class="bi bi-journal-minus"></i> Customer Debts
            </a>
            @if(auth()->user()?->canAccessProcurement())
            <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i> Suppliers
            </a>
            <a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                <i class="bi bi-bag-plus-fill"></i> Purchases
            </a>
            <a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Expenses
            </a>
            @endif
            @if(auth()->user()?->canAccessReports())
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i> Reports
            </a>
            @endif
            @if(auth()->user()?->isAdmin())
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge-fill"></i> Users
            </a>
            <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear-fill"></i> Settings
            </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-card">
                <img src="{{ asset('images/login-furniture.jpg') }}" alt="" class="sidebar-card-bg">
                <div class="sidebar-card-overlay"></div>
                <p class="mb-0 fw-semibold">Dalmar Furniture &amp; House Interior</p>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">
        <header class="topbar">
            <form class="search-box" action="{{ route('search.index') }}" method="get">
                <i class="bi bi-search"></i>
                <input type="text" name="q" placeholder="Search anything..." value="{{ request('q') }}">
            </form>

            <div class="topbar-right">
                <a href="{{ route('shop.index') }}" target="_blank" class="btn btn-sm btn-navy me-1">
                    <i class="bi bi-box-arrow-up-right me-1"></i> View Site
                </a>
                <div class="dropdown">
                    <button class="btn btn-sm border-0 bg-transparent position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell" style="font-size:18px; color:#8b93a7;"></i>
                        @if(($notifCount ?? 0) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px;">
                                {{ $notifCount }}
                            </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-2" style="width: 320px; max-height: 380px; overflow-y: auto;">
                        <div class="fw-semibold small px-2 py-1">Notifications</div>
                        @forelse(($notifPendingOrders ?? collect()) as $order)
                            <a href="{{ route('orders.show', $order) }}" class="dropdown-item small rounded-2 py-2">
                                <i class="bi bi-clock-history text-warning me-1"></i>
                                Order <strong>#{{ $order->order_number }}</strong> from {{ $order->customer->name ?? '-' }} is pending
                            </a>
                        @empty
                        @endforelse
                        @forelse(($notifLowStock ?? collect()) as $product)
                            <a href="{{ route('products.edit', $product) }}" class="dropdown-item small rounded-2 py-2">
                                <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                                <strong>{{ $product->name }}</strong> is low on stock ({{ $product->stock }} left)
                            </a>
                        @empty
                        @endforelse
                        @if(($notifCount ?? 0) === 0)
                            <div class="text-muted small px-2 py-3 text-center">You're all caught up 🎉</div>
                        @endif
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        @if(auth()->user()?->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="" class="avatar" style="object-fit:cover;">
                        @else
                            <span class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                        @endif
                        {{ auth()->user()->name ?? 'Admin User' }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="bi bi-person-gear me-2"></i> My Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item" type="submit">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="page-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // A browser refresh (not a normal link/form navigation) always returns to the dashboard.
    (function () {
        var nav = performance.getEntriesByType('navigation')[0];
        var wasReload = nav ? nav.type === 'reload' : performance.navigation.type === 1;
        if (wasReload && window.location.pathname !== '{{ route('dashboard', [], false) }}') {
            window.location.replace('{{ route('dashboard') }}');
        }
    })();
</script>
@yield('scripts')
</body>
</html>
