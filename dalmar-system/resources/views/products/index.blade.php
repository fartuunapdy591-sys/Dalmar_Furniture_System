@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Products</div>
            <div class="page-subtitle">Home / Products</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#restockModal"><i class="bi bi-box-arrow-in-down me-1"></i> Stock In / Restock</button>
            <button type="button" class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="bi bi-plus-lg me-1"></i> Add Product</button>
        </div>
    </div>

    <div class="card-panel mb-3">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label small fw-semibold mb-1">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Product name...">
            </div>
            <div>
                <label class="form-label small fw-semibold mb-1">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="" @selected(request('status') === null || request('status') === '')>All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-navy">Filter</button>
            @if(request()->anyFilled(['search', 'category_id', 'status']))
                <a href="{{ route('products.index') }}" class="text-muted small">Clear filters</a>
            @endif
        </form>
    </div>

    <div class="card-panel">
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>Image</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Cost</th>
                <th>Selling Price</th>
                <th>Stock</th>
                <th>Stock Level</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                <tr class="{{ $product->status === 'inactive' ? 'opacity-50' : '' }}">
                    <td>
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-thumb">
                        @else
                            <div class="product-thumb product-thumb-placeholder"><i class="bi bi-box-seam-fill"></i></div>
                        @endif
                    </td>
                    <td class="fw-semibold">{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td class="text-muted">${{ number_format($product->cost, 2) }}</td>
                    <td class="fw-bold text-success">${{ number_format($product->price, 2) }}</td>
                    <td>
                        <span class="fw-semibold">{{ $product->stock }}</span> <small class="text-muted">{{ $product->unit }}</small>
                        @if($product->stock <= $product->min_stock)
                            <i class="bi bi-exclamation-triangle-fill text-warning ms-1" title="Min stock: {{ $product->min_stock }}"></i>
                        @endif
                    </td>
                    <td>
                        <span class="badge-status badge-{{ strtolower(str_replace(' ', '_', $product->stock_label)) }}">
                            {{ $product->stock_label }}
                        </span>
                    </td>
                    <td>
                        @if($product->status === 'active')
                            <span class="badge-status badge-in_stock">Active</span>
                        @else
                            <span class="badge-status badge-out_of_stock">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="icon-btn border-0 bg-transparent restock-trigger" title="Restock" data-id="{{ $product->id }}">
                            <i class="bi bi-box-arrow-in-down"></i>
                        </button>
                        <a href="{{ route('products.edit', $product) }}" class="icon-btn"><i class="bi bi-pencil-fill"></i></a>
                        @if($product->status === 'active')
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark {{ $product->name }} as inactive? It will be hidden from POS/Orders but you can restore it any time.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="icon-btn border-0 bg-transparent" title="Deactivate"><i class="bi bi-trash-fill"></i></button>
                            </form>
                        @else
                            <form action="{{ route('products.restore', $product) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="icon-btn border-0 bg-transparent" title="Restore"><i class="bi bi-arrow-counterclockwise text-success"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No products found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $products->links() }}</div>
    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Product Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Luxury Sofa Set" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Supplier (optional)</label>
                                <select name="supplier_id" class="form-select">
                                    <option value="">-- None / Select Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Cost Price ($)</label>
                                <input type="number" step="0.01" min="0" name="cost" class="form-control" value="{{ old('cost', '0.00') }}">
                                <span class="text-muted" style="font-size:11px;">Purchase cost for profit calculation</span>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Selling Price ($)</label>
                                <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price') }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Initial Stock Quantity</label>
                                <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Unit</label>
                                <select name="unit" class="form-select">
                                    <option value="pcs" @selected(old('unit') == 'pcs')>Pcs (Pieces)</option>
                                    <option value="set" @selected(old('unit') == 'set')>Set</option>
                                    <option value="meter" @selected(old('unit') == 'meter')>Meter</option>
                                    <option value="pair" @selected(old('unit') == 'pair')>Pair</option>
                                    <option value="box" @selected(old('unit') == 'box')>Box</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Low Stock Alert Level</label>
                                <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', 5) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Product Image</label>
                            <input type="file" name="image" accept="image/*" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="restockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST" id="restockForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Stock In / Restock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Product</label>
                            <select id="restockProductSelect" class="form-select" required>
                                <option value="">-- Search and select an existing product --</option>
                                @foreach($allProducts as $product)
                                    <option value="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-stock="{{ $product->stock }}"
                                        data-cost="{{ $product->cost }}"
                                        data-action="{{ route('products.restock', $product) }}">
                                        {{ $product->name }} (current stock: {{ $product->stock }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-muted small mb-3">Current Stock: <strong id="restockCurrentStock">-</strong></p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Quantity Received</label>
                            <input type="number" name="quantity" id="restockQuantity" class="form-control" min="1" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Supplier (optional)</label>
                                <select name="supplier_id" class="form-select">
                                    <option value="">-- None --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Purchase Price (per unit)</label>
                                <input type="number" step="0.01" min="0" name="unit_cost" id="restockUnitCost" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Notes (optional)</label>
                            <input type="text" name="notes" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy" id="restockSubmitBtn" disabled>Update Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const restockModalEl = document.getElementById('restockModal');
    const restockForm = document.getElementById('restockForm');
    const restockProductSelect = document.getElementById('restockProductSelect');
    const restockCurrentStock = document.getElementById('restockCurrentStock');
    const restockUnitCost = document.getElementById('restockUnitCost');
    const restockSubmitBtn = document.getElementById('restockSubmitBtn');

    function applyRestockProduct(option) {
        if (!option || !option.value) {
            restockForm.action = '';
            restockCurrentStock.textContent = '-';
            restockSubmitBtn.disabled = true;
            return;
        }
        restockForm.action = option.dataset.action;
        restockCurrentStock.textContent = option.dataset.stock;
        if (!restockUnitCost.value && parseFloat(option.dataset.cost) > 0) {
            restockUnitCost.value = parseFloat(option.dataset.cost).toFixed(2);
        }
        restockSubmitBtn.disabled = false;
    }

    restockProductSelect.addEventListener('change', function () {
        applyRestockProduct(this.options[this.selectedIndex]);
    });

    document.querySelectorAll('.restock-trigger').forEach(function (btn) {
        btn.addEventListener('click', function () {
            restockProductSelect.value = this.dataset.id;
            applyRestockProduct(restockProductSelect.options[restockProductSelect.selectedIndex]);
            new bootstrap.Modal(restockModalEl).show();
        });
    });

    restockModalEl.addEventListener('hidden.bs.modal', function () {
        restockForm.reset();
        restockProductSelect.value = '';
        applyRestockProduct(null);
    });

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('addProductModal')).show();
        });
    @endif
</script>
@endsection
