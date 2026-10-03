@extends('layouts.app')
@section('title', 'Customer Debts')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Customer Debts</div>
            <div class="page-subtitle">Home / Customer Debts</div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card-panel">
                <div class="text-muted small">Total Outstanding Debt</div>
                <div class="fs-4 fw-bold text-danger">${{ number_format($totalOutstanding, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="card-panel mb-3">
        <form class="row g-2" method="GET">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name or phone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <div class="form-check mt-2">
                    <input type="checkbox" name="all" value="1" class="form-check-input" id="showAllCheck" @checked(request('all')) onchange="this.form.submit()">
                    <label class="form-check-label" for="showAllCheck">Show all customers (including cleared)</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-light w-100">Filter</button>
            </div>
        </form>
    </div>

    <div class="card-panel">
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>Customer Name</th>
                <th>Phone</th>
                <th>Total Credit</th>
                <th>Total Paid</th>
                <th>Remaining Debt</th>
                <th>Last Transaction</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td class="fw-semibold">{{ $customer->name }}</td>
                    <td>{{ $customer->phone ?? '-' }}</td>
                    <td>${{ number_format($customer->total_credit_sales, 2) }}</td>
                    <td>${{ number_format($customer->total_paid, 2) }}</td>
                    <td class="fw-semibold">${{ number_format($customer->computed_balance, 2) }}</td>
                    <td>{{ $customer->computed_last_transaction ? \Illuminate\Support\Carbon::parse($customer->computed_last_transaction)->format('M d, Y') : '-' }}</td>
                    <td>{{ $customer->computed_due_date ? \Illuminate\Support\Carbon::parse($customer->computed_due_date)->format('M d, Y') : '-' }}</td>
                    <td>
                        @if($customer->computed_balance <= 0)
                            <span class="badge-status badge-paid">Cleared</span>
                        @elseif($customer->computed_overdue)
                            <span class="badge-status badge-unpaid">Overdue</span>
                        @else
                            <span class="badge-status badge-partial">Outstanding</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}" class="icon-btn" title="View Account"><i class="bi bi-eye-fill"></i></a>
                        @if($customer->computed_balance > 0)
                            <a href="{{ route('customers.show', $customer) }}?pay=1" class="icon-btn" title="Add Payment"><i class="bi bi-wallet2"></i></a>
                        @endif
                        <a href="{{ route('customers.show', $customer) }}#transactions" class="icon-btn" title="View Transactions"><i class="bi bi-list-ul"></i></a>
                        <a href="{{ route('customers.edit', $customer) }}" class="icon-btn" title="Edit Customer"><i class="bi bi-pencil-fill"></i></a>
                        <a href="{{ route('customers.show', $customer) }}?print=1" class="icon-btn" title="Print Statement"><i class="bi bi-printer-fill"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No customer debts found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $customers->links() }}</div>
    </div>
@endsection
