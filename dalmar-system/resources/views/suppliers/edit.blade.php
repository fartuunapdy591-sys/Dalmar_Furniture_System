@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('content')
    <div class="page-title mb-3">Edit Supplier</div>
    <div class="card-panel" style="max-width: 600px;">
        <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label small fw-semibold">Supplier Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Company Name (optional)</label>
                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $supplier->company_name) }}">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Phone (optional)</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email (optional)</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Address (optional)</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $supplier->address) }}">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Opening Balance</label>
                <input type="number" step="0.01" min="0" name="opening_balance" class="form-control" value="{{ old('opening_balance', $supplier->opening_balance) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select" required>
                    <option value="active" @selected($supplier->status === 'active')>Active</option>
                    <option value="inactive" @selected($supplier->status === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Notes (optional)</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $supplier->notes) }}</textarea>
            </div>
            <button type="submit" class="btn btn-navy">Update Supplier</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
@endsection
