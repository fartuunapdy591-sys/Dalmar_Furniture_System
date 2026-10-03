@extends('layouts.app')

@section('title', 'Receipts')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Receipts</div>
            <div class="page-subtitle">Home / Receipts</div>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div><div class="stat-value">{{ $totalReceipts }}</div><div class="stat-label">Total Receipts</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div><div class="stat-value">${{ number_format($totalAmount, 2) }}</div><div class="stat-label">Total Amount</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div><div class="stat-value">${{ number_format($outstandingAmount, 2) }}</div><div class="stat-label">Outstanding</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div><div class="stat-value">{{ $overdueCount }}</div><div class="stat-label">Overdue</div></div>
            </div>
        </div>
    </div>

    <div class="card-panel mb-3">
        <form class="row g-2" method="GET">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search by receipt #, order # or customer..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                    <option value="partial" @selected(request('status') === 'partial')>Partial</option>
                    <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-light w-100">Filter</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('receipts.index') }}" class="btn btn-light">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card-panel">
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>Receipt #</th>
                <th>Order</th>
                <th>Customer</th>
                <th>Issued Date</th>
                <th>Amount</th>
                <th>Balance Due</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($receipts as $receipt)
                <tr>
                    <td class="fw-semibold">{{ $receipt->receipt_number }}</td>
                    <td>#{{ $receipt->order->order_number ?? '-' }}</td>
                    <td>{{ $receipt->customer->name ?? '-' }}</td>
                    <td>{{ $receipt->issued_date->format('M d, Y') }}</td>
                    <td>${{ number_format($receipt->amount, 2) }}</td>
                    <td>${{ number_format($receipt->balance_due, 2) }}</td>
                    <td>
                        <span class="badge-status badge-{{ $receipt->status }}">{{ ucfirst($receipt->status) }}</span>
                        @if($receipt->is_credit_sale)
                            <span class="badge-status badge-unpaid">CREDIT</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('receipts.show', $receipt) }}" class="icon-btn"><i class="bi bi-eye-fill"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No receipts found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $receipts->links() }}</div>
    </div>
@endsection
