@extends('layouts.app')

@section('title', 'Dashboard')
@section('body-class', 'has-hero')

@section('content')
    <div class="hero-banner">
        <h2>Welcome, Dalmar Furniture 👋</h2>
        <p>Here's what's happening with your store today.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="{{ route('products.index') }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#4c6ef5,#7c93fb);"><i class="bi bi-box-seam-fill"></i></div>
                    <div>
                        <div class="stat-value">{{ $totalProducts }}</div>
                        <div class="stat-label">Total Products</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('customers.index') }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#12b76a,#4ade80);"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <div class="stat-value">{{ $totalCustomers }}</div>
                        <div class="stat-label">Total Customers</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('orders.index') }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#f2b134,#ffd166);"><i class="bi bi-cart-check-fill"></i></div>
                    <div>
                        <div class="stat-value">{{ $totalOrders }}</div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('reports.index') }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#c4b5fd);"><i class="bi bi-currency-dollar"></i></div>
                    <div>
                        <div class="stat-value">${{ number_format($totalSales, 2) }}</div>
                        <div class="stat-label">Total Sales</div>
                    </div>
                </div>
            </a>
        </div>
    </div>


    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card-panel">
                <div class="card-panel-title">Sales Overview</div>
                <canvas id="salesOverviewChart" height="180"></canvas>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card-panel">
                <div class="card-panel-title">Sales by Category</div>
                <canvas id="categoryChart" height="180"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-panel">
                <div class="d-flex justify-content-between">
                    <div class="card-panel-title">Recent Orders</div>
                    <a href="{{ route('orders.index') }}" class="small text-decoration-none">View All</a>
                </div>
                @forelse($recentOrders as $order)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-semibold small">{{ $order->order_number }}</div>
                            <div class="text-muted" style="font-size:11px;">{{ $order->customer->name ?? '-' }} · {{ $order->order_date->format('M d, Y') }}</div>
                        </div>
                        <div class="fw-semibold small">${{ number_format($order->total_amount, 0) }}</div>
                    </div>
                @empty
                    <p class="text-muted small">No orders yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    new Chart(document.getElementById('salesOverviewChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($salesOverview->keys()) !!},
            datasets: [{
                data: {!! json_encode($salesOverview->values()) !!},
                borderColor: '#4c6ef5',
                backgroundColor: 'rgba(76,110,245,.1)',
                tension: 0.4,
                fill: true,
            }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($salesByCategory->keys()) !!},
            datasets: [{
                data: {!! json_encode($salesByCategory->values()) !!},
                backgroundColor: ['#4c6ef5', '#12b76a', '#f2b134', '#8b5cf6', '#ef4444'],
            }]
        },
        options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } } }
    });
</script>
@endsection
