@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
    <div class="page-title mb-3">Edit Product</div>

    <div class="card-panel" style="max-width: 750px;">
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label small fw-semibold">Product Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Category</label>
                    <select name="category_id" class="form-select" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">-- None / Select Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $product->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label small fw-semibold">Cost Price ($)</label>
                    <input type="number" step="0.01" min="0" name="cost" class="form-control" value="{{ old('cost', $product->cost) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label small fw-semibold">Selling Price ($)</label>
                    <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $product->price) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label small fw-semibold">Stock Quantity</label>
                    <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label small fw-semibold">Unit</label>
                    <select name="unit" class="form-select">
                        <option value="pcs" @selected(old('unit', $product->unit) == 'pcs')>Pcs (Pieces)</option>
                        <option value="set" @selected(old('unit', $product->unit) == 'set')>Set</option>
                        <option value="meter" @selected(old('unit', $product->unit) == 'meter')>Meter</option>
                        <option value="pair" @selected(old('unit', $product->unit) == 'pair')>Pair</option>
                        <option value="box" @selected(old('unit', $product->unit) == 'box')>Box</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label small fw-semibold">Low Stock Alert Level</label>
                    <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', $product->min_stock) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active" @selected(old('status', $product->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Product Image</label>
                @if($product->image_url)
                    <div class="mb-2"><img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-thumb" style="width:64px;height:64px;"></div>
                @endif
                <input type="file" name="image" accept="image/*" class="form-control">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-navy">Update Product</button>
                <a href="{{ route('products.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
@endsection
