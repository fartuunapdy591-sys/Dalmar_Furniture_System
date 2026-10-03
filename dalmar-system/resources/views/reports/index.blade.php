@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2 no-print">
        <div>
            <div class="page-title">Reports</div>
            <div class="page-subtitle">Home / Reports</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light" onclick="window.print()"><i class="bi bi-printer-fill me-1"></i> Print Report</button>
            <a href="{{ route('reports.export', request()->only(['from', 'to', 'order_status', 'payment_method', 'customer_id', 'category_id', 'report_type'])) }}" class="btn btn-navy"><i class="bi bi-download me-1"></i> Export CSV</a>
        </div>
    </div>

    <div class="card-panel mb-3 no-print">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex flex-wrap gap-2">
                @foreach($reportTypes as $value => $label)
                    <a href="{{ route('reports.index', array_merge(request()->except('report_type'), ['report_type' => $value])) }}"
                       class="btn btn-sm {{ $reportType === $value ? 'btn-navy' : 'btn-outline-secondary' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($from || $to || $status || $paymentMethod || $customerId || $categoryId)
                    <a href="{{ route('reports.index', ['report_type' => $reportType]) }}" class="text-muted small">Clear filters</a>
                @endif
                <button type="button" class="btn btn-navy btn-sm" data-bs-toggle="modal" data-bs-target="#reportFilterModal">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reportFilterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('reports.index') }}" method="GET" id="reportFilterForm">
                    <input type="hidden" name="report_type" value="{{ $reportType }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Filter {{ $reportTypes[$reportType] }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm report-preset" data-preset="this_week">This Week</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm report-preset" data-preset="last_week">Last Week</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm report-preset" data-preset="this_month">This Month</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm report-preset" data-preset="last_month">Last Month</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm report-preset" data-preset="this_year">This Year</button>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">From</label>
                                <input type="date" name="from" id="reportFrom" class="form-control" value="{{ $from }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">To</label>
                                <input type="date" name="to" id="reportTo" class="form-control" value="{{ $to }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($filterCategories as $category)
                                        <option value="{{ $category->id }}" @selected($categoryId == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="">All Methods</option>
                                    @foreach($filterPaymentMethods as $value => $label)
                                        <option value="{{ $value }}" @selected($paymentMethod === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Order Status</label>
                                <select name="order_status" class="form-select">
                                    <option value="">All (except cancelled)</option>
                                    @foreach($filterStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Customer</label>
                                <select name="customer_id" class="form-select">
                                    <option value="">All Customers</option>
                                    @foreach($filterCustomers as $customer)
                                        <option value="{{ $customer->id }}" @selected($customerId == $customer->id)>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy"><i class="bi bi-funnel-fill me-1"></i> Apply Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card-panel">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <span class="text-muted small">{{ now()->format('Y-m-d H:i') }}</span>
            @if($setting->logo_url)
                <img src="{{ $setting->logo_url }}" alt="" style="height:40px;">
            @endif
        </div>
        <h2 class="text-center fw-bold mb-1">{{ $setting->company_name ?: 'Dalmar Furniture and House Interior' }}</h2>
        <h5 class="text-center text-muted mb-4">{{ $reportTypes[$reportType] }}</h5>

        <div class="row g-2 mb-4">
            <div class="col-md-6">
                <strong>Date:</strong> {{ $from || $to ? ($from ?: 'start') . ' to ' . ($to ?: 'now') : 'All Time' }}<br>
                <strong>Category:</strong> {{ $categoryId ? ($filterCategories->firstWhere('id', $categoryId)->name ?? '-') : 'All Categories' }}<br>
                <strong>Payment Method:</strong> {{ $paymentMethod ? $filterPaymentMethods[$paymentMethod] : 'All Methods' }}
            </div>
            <div class="col-md-6">
                <strong>Order Status:</strong> {{ $status ? $filterStatuses[$status] : 'All (except cancelled)' }}<br>
                <strong>Customer:</strong> {{ $customerId ? ($filterCustomers->firstWhere('id', $customerId)->name ?? '-') : 'All Customers' }}<br>
                <strong>Generated By:</strong> {{ auth()->user()->name }}
            </div>
        </div>

        <table class="table-dalmar">
            <thead>
            <tr>
                @foreach($columns as $col)
                    <th>{{ $col }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($columns) }}" class="text-center text-muted py-4">No records for the selected filters.</td></tr>
            @endforelse
            </tbody>
            @if(count($rows) > 0)
                <tfoot>
                <tr class="fw-bold">
                    @foreach($totalsRow as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection

@section('scripts')
<script>
    (function () {
        function fmt(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        document.querySelectorAll('.report-preset').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const today = new Date();
                let from, to;
                switch (btn.dataset.preset) {
                    case 'this_week': {
                        const day = (today.getDay() + 6) % 7; // Monday = 0
                        from = new Date(today); from.setDate(today.getDate() - day);
                        to = today;
                        break;
                    }
                    case 'last_week': {
                        const day = (today.getDay() + 6) % 7;
                        const thisMonday = new Date(today); thisMonday.setDate(today.getDate() - day);
                        to = new Date(thisMonday); to.setDate(thisMonday.getDate() - 1);
                        from = new Date(thisMonday); from.setDate(thisMonday.getDate() - 7);
                        break;
                    }
                    case 'this_month':
                        from = new Date(today.getFullYear(), today.getMonth(), 1);
                        to = today;
                        break;
                    case 'last_month':
                        from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        to = new Date(today.getFullYear(), today.getMonth(), 0);
                        break;
                    case 'this_year':
                        from = new Date(today.getFullYear(), 0, 1);
                        to = today;
                        break;
                }
                document.getElementById('reportFrom').value = fmt(from);
                document.getElementById('reportTo').value = fmt(to);
                document.getElementById('reportFilterForm').submit();
            });
        });
    })();
</script>
@endsection
