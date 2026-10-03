@extends('layouts.app')
@section('title', 'Suppliers')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Suppliers</div>
            <div class="page-subtitle">Home / Suppliers</div>
        </div>
        <button type="button" class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#addSupplierModal"><i class="bi bi-plus-lg me-1"></i> Add Supplier</button>
    </div>

    <div class="card-panel mb-3">
        <form class="row g-2" method="GET">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search by name or company..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-light w-100">Filter</button>
            </div>
        </form>
    </div>

    <div class="card-panel">
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>#</th>
                <th>Supplier</th>
                <th>Company</th>
                <th>Phone</th>
                <th>Purchases</th>
                <th>Balance Due</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($suppliers as $index => $supplier)
                <tr>
                    <td>{{ $suppliers->firstItem() + $index }}</td>
                    <td class="fw-semibold"><a href="{{ route('suppliers.show', $supplier) }}">{{ $supplier->name }}</a></td>
                    <td>{{ $supplier->company_name ?? '-' }}</td>
                    <td>{{ $supplier->phone ?? '-' }}</td>
                    <td>{{ $supplier->purchases_count }}</td>
                    <td>${{ number_format($supplier->balance_due, 2) }}</td>
                    <td><span class="badge-status badge-{{ $supplier->status }}">{{ ucfirst($supplier->status) }}</span></td>
                    <td>
                        <a href="{{ route('suppliers.show', $supplier) }}" class="icon-btn"><i class="bi bi-eye-fill"></i></a>
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="icon-btn"><i class="bi bi-pencil-fill"></i></a>
                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="icon-btn border-0 bg-transparent"><i class="bi bi-trash-fill"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No suppliers found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $suppliers->links() }}</div>
    </div>

    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('suppliers.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Supplier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Supplier Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Company Name (optional)</label>
                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Phone (optional)</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Email (optional)</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Address (optional)</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Opening Balance</label>
                                <input type="number" step="0.01" min="0" name="opening_balance" class="form-control" value="{{ old('opening_balance', 0) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy">Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('addSupplierModal')).show();
        });
    @endif
</script>
@endsection
