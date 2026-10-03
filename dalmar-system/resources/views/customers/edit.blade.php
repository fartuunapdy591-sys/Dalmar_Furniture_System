@extends('layouts.app')
@section('title', 'Edit Customer')
@section('content')
    <div class="page-title mb-3">Edit Customer</div>
    <div class="card-panel" style="max-width: 600px;">
        <form action="{{ route('customers.update', $customer) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label small fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email (optional)</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Phone (optional)</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select" required>
                    <option value="active" @selected($customer->status === 'active')>Active</option>
                    <option value="inactive" @selected($customer->status === 'inactive')>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-navy">Update Customer</button>
            <a href="{{ route('customers.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
@endsection
