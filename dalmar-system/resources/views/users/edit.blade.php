@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
    <div class="page-title mb-3">Edit User</div>
    <div class="card-panel" style="max-width: 600px;">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label small fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">New Password</label>
                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Role</label>
                <select name="role" class="form-select" required>
                    <option value="admin" @selected($user->role === 'admin')>Administrator</option>
                    <option value="sales_manager" @selected($user->role === 'sales_manager')>Sales Manager</option>
                    <option value="salesperson" @selected($user->role === 'salesperson')>Salesperson</option>
                    <option value="accountant" @selected($user->role === 'accountant')>Accountant</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select" required>
                    <option value="active" @selected($user->status === 'active')>Active</option>
                    <option value="inactive" @selected($user->status === 'inactive')>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-navy">Update User</button>
            <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
@endsection
