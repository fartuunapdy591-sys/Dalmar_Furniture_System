@extends('layouts.app')
@section('title', 'Purchases')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Purchases</div>
            <div class="page-subtitle">Home / Purchases</div>
        </div>
        <a href="{{ route('purchases.create') }}" class="btn btn-navy"><i class="bi bi-plus-lg me-1"></i> New Purchase</a>
    </div>

    <div class="card-panel mb-3">
        <form class="row g-2" method="GET">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search purchase # or supplier..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="supplier_id" class="form-select">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                    <option value="partial" @selected(request('status') === 'partial')>Partial</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-light w-100">Filter</button>
            </div>
        </form>
    </div>

    <div class="card-panel">
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>Purchase #</th>
                <th>Supplier</th>
                <th>Date</th>
                <th>Total</th>
                <th>Balance Due</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($purchases as $purchase)
                <tr>
                    <td class="fw-semibold">{{ $purchase->purchase_number }}</td>
                    <td>{{ $purchase->supplier->name ?? '-' }}</td>
                    <td>{{ $purchase->purchase_date->format('M d, Y') }}</td>
                    <td>${{ number_format($purchase->total, 2) }}</td>
                    <td>${{ number_format($purchase->balance_due, 2) }}</td>
                    <td><span class="badge-status badge-{{ $purchase->status }}">{{ ucfirst($purchase->status) }}</span></td>
                    <td>
                        <a href="{{ route('purchases.show', $purchase) }}" class="icon-btn"><i class="bi bi-eye-fill"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No purchases found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $purchases->links() }}</div>
    </div>
@endsection
