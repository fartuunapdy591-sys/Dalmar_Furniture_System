@extends('layouts.app')
@section('title', 'Customers')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Customers</div>
            <div class="page-subtitle">Home / Customers</div>
        </div>
        <button type="button" class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="bi bi-plus-lg me-1"></i> Add Customer</button>
    </div>

    <div class="card-panel">
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>#</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Total Orders</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($customers as $index => $customer)
                <tr>
                    <td>{{ $customers->firstItem() + $index }}</td>
                    <td class="fw-semibold">{{ $customer->name }}</td>
                    <td>{{ $customer->email ?? '-' }}</td>
                    <td>{{ $customer->phone ?? '-' }}</td>
                    <td>{{ $customer->orders_count }}</td>
                    <td><span class="badge-status badge-{{ $customer->status }}">{{ ucfirst($customer->status) }}</span></td>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}" class="icon-btn"><i class="bi bi-eye-fill"></i></a>
                        <a href="{{ route('customers.edit', $customer) }}" class="icon-btn"><i class="bi bi-pencil-fill"></i></a>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="icon-btn border-0 bg-transparent"><i class="bi bi-trash-fill"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No customers found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $customers->links() }}</div>
    </div>

    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('customers.store') }}" method="POST" id="addCustomerForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Email (optional)</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Phone (optional)</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="sell_now" value="1" class="form-check-input" id="sellNowCheck" @checked(old('sell_now'))>
                            <label class="form-check-label fw-semibold" for="sellNowCheck">
                                <i class="bi bi-cash-coin me-1"></i> Sell an item to this customer now
                            </label>
                        </div>

                        <div id="sellNowSection" class="d-none">
                            <label class="form-label small fw-semibold">Products</label>
                            <div id="custProductLines">
                                <div class="row cust-product-line mb-2">
                                    <div class="col-6">
                                        <select name="items[0][id]" class="form-select cust-product-select">
                                            <option value="">-- Select Product --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" @disabled($product->stock <= 0)>
                                                    {{ $product->name }} (${{ number_format($product->price, 2) }}) &mdash; {{ $product->stock > 0 ? 'Available' : 'Unavailable' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-3">
                                        <input type="number" name="items[0][qty]" class="form-control" placeholder="Qty" min="1" value="1">
                                    </div>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-light w-100 cust-add-line">+ Add</button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 mt-3">
                                <label class="form-label small fw-semibold">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="sahal">Sahal</option>
                                    <option value="e_dahab">e-Dahab</option>
                                    <option value="mycash">MyCash</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const sellNowCheck = document.getElementById('sellNowCheck');
    const sellNowSection = document.getElementById('sellNowSection');
    const custProductLines = document.getElementById('custProductLines');

    function toggleSellNow() {
        sellNowSection.classList.toggle('d-none', !sellNowCheck.checked);
        sellNowSection.querySelectorAll('.cust-product-select').forEach(el => {
            el.required = sellNowCheck.checked;
        });
    }

    sellNowCheck.addEventListener('change', toggleSellNow);
    toggleSellNow();

    let custLineIndex = 1;
    custProductLines.addEventListener('click', function (e) {
        if (e.target.classList.contains('cust-add-line')) {
            const template = document.querySelector('.cust-product-line').cloneNode(true);
            template.querySelectorAll('select, input').forEach(el => {
                const name = el.getAttribute('name');
                if (name) el.setAttribute('name', name.replace(/\[\d+\]/, `[${custLineIndex}]`));
                if (el.tagName === 'INPUT') el.value = 1;
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });
            template.querySelector('.cust-add-line').outerHTML =
                '<button type="button" class="btn btn-outline-danger w-100 cust-remove-line">Remove</button>';
            custProductLines.appendChild(template);
            custLineIndex++;
        }
        if (e.target.classList.contains('cust-remove-line')) {
            e.target.closest('.cust-product-line').remove();
        }
    });

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('addCustomerModal')).show();
        });
    @endif
</script>
@endsection
