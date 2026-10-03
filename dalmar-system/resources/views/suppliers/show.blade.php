@extends('layouts.app')
@section('title', 'Supplier Details')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">{{ $supplier->name }}</div>
            <div class="page-subtitle">Home / Suppliers / {{ $supplier->name }}</div>
        </div>
        <div>
            <a href="{{ route('purchases.create') }}" class="btn btn-navy"><i class="bi bi-bag-plus-fill me-1"></i> New Purchase</a>
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-light"><i class="bi bi-pencil-fill me-1"></i> Edit</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card-panel">
                <div class="text-muted small">Total Purchases</div>
                <div class="fs-4 fw-bold">${{ number_format($supplier->total_purchases, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-panel">
                <div class="text-muted small">Total Paid</div>
                <div class="fs-4 fw-bold">${{ number_format($supplier->total_paid, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-panel">
                <div class="text-muted small">Balance Due</div>
                <div class="fs-4 fw-bold text-danger">${{ number_format($supplier->balance_due, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card-panel h-100">
                <div class="fw-semibold mb-2">Contact Info</div>
                <table class="table-dalmar">
                    <tr><td class="text-muted">Company</td><td class="text-end">{{ $supplier->company_name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Phone</td><td class="text-end">{{ $supplier->phone ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Email</td><td class="text-end">{{ $supplier->email ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Address</td><td class="text-end">{{ $supplier->address ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Status</td><td class="text-end"><span class="badge-status badge-{{ $supplier->status }}">{{ ucfirst($supplier->status) }}</span></td></tr>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-panel h-100">
                <div class="fw-semibold mb-2">Notes</div>
                <p class="text-muted mb-0">{{ $supplier->notes ?? 'No notes.' }}</p>
            </div>
        </div>
    </div>

    <div class="card-panel">
        <div class="fw-semibold mb-2">Account Statement</div>
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Reference</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Balance</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="text-muted">-</td>
                <td>Opening Balance</td>
                <td>-</td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end fw-semibold">${{ number_format($supplier->opening_balance, 2) }}</td>
            </tr>
            @forelse($statement as $entry)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($entry['date'])->format('M d, Y') }}</td>
                    <td>{{ $entry['type'] }}</td>
                    <td>
                        @if($entry['link'])
                            <a href="{{ $entry['link'] }}">{{ $entry['reference'] }}</a>
                        @else
                            {{ $entry['reference'] }}
                        @endif
                    </td>
                    <td class="text-end">{{ $entry['debit'] > 0 ? '$'.number_format($entry['debit'], 2) : '-' }}</td>
                    <td class="text-end">{{ $entry['credit'] > 0 ? '$'.number_format($entry['credit'], 2) : '-' }}</td>
                    <td class="text-end fw-semibold">${{ number_format($entry['balance'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No purchases or payments yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
