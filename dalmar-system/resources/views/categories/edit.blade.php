@extends('layouts.app')
@section('title', 'Edit Category')
@section('content')
    <div class="page-title mb-3">Edit Category</div>
    <div class="card-panel" style="max-width: 600px;">
        <form action="{{ route('categories.update', $category) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label small fw-semibold">Category Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Description</label>
                <input type="text" name="description" class="form-control" value="{{ old('description', $category->description) }}">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select" required>
                    <option value="active" @selected($category->status === 'active')>Active</option>
                    <option value="inactive" @selected($category->status === 'inactive')>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-navy">Update Category</button>
            <a href="{{ route('categories.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
@endsection
