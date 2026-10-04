@extends('layouts.app')

@section('title', 'Orders')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Orders</div>
            <div class="page-subtitle">Home / Orders</div>
        </div>
        <button type="button" class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#addOrderModal"><i class="bi bi-plus-lg me-1"></i> Add Order</button>
    </div>

    <div class="card-panel">
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total Amount</th>
                <th>Payment</th>
                <th>Source</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr>
                    <td class="fw-semibold">#{{ $order->order_number }}</td>
                    <td>{{ $order->customer->name ?? '-' }}</td>
                    <td>{{ $order->order_date->format('M d, Y') }}</td>
                    <td>${{ number_format($order->total_amount, 2) }}</td>
                    <td>{{ ucfirst($order->payment_method) }}</td>
                    <td>
                        @if($order->source === 'web')
                            <span class="badge-method"><i class="bi bi-globe me-1"></i>Website</span>
                        @else
                            <span class="badge-method"><i class="bi bi-shop me-1"></i>In-Store</span>
                        @endif
                    </td>
                    <td><span class="badge-status badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                    <td>
                        <button type="button" class="icon-btn" data-bs-toggle="modal" data-bs-target="#viewOrderModal{{ $order->id }}">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No orders found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $orders->links() }}</div>
    </div>

    @foreach($orders as $order)
        <div class="modal fade" id="viewOrderModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Order #{{ $order->order_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="small text-muted">Customer</div>
                                <div class="fw-semibold">{{ $order->customer->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted">Phone</div>
                                <div class="fw-semibold">{{ $order->customer->phone ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted">Address</div>
                                <div class="fw-semibold">{{ $order->shipping_address ?: ($order->customer->address ?? '-') }}</div>
                            </div>
                        </div>

                        <table class="table-dalmar">
                            <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'Deleted product' }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td>${{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-between fw-bold pt-2 border-top">
                            <span>Order Total</span>
                            <span>${{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-light">Full Details</a>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="addOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-semibold mb-0">Customer</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 small" data-bs-toggle="modal" data-bs-target="#orderNewCustomerModal">
                                        <i class="bi bi-plus-circle me-1"></i>New Customer
                                    </button>
                                </div>
                                <div class="position-relative">
                                    <input type="text" id="orderCustomerSearch" class="form-control" placeholder="Search by name or phone..." autocomplete="off">
                                    <input type="hidden" name="customer_id" id="orderCustomerSelect">
                                    <div id="orderCustomerResults" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1055; max-height: 220px; overflow-y: auto; display: none;"></div>
                                </div>
                                <div id="orderCustomerError" class="text-danger small mt-1 d-none">Please search and select a customer.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Order Date</label>
                                <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Shipping Address</label>
                            <input type="text" name="shipping_address" class="form-control" placeholder="Optional">
                        </div>

                        <label class="form-label small fw-semibold">Products</label>
                        <div id="productLines">
                            <div class="row product-line mb-2">
                                <div class="col-6">
                                    <select name="products[0][id]" class="form-select product-select" required>
                                        <option value="">-- Select Product --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" @disabled($product->stock <= 0)>
                                                {{ $product->name }} (${{ $product->price }}) &mdash; {{ $product->stock > 0 ? 'Available' : 'Unavailable' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <input type="number" name="products[0][qty]" class="form-control order-qty" placeholder="Qty" min="1" value="1" required>
                                </div>
                                <div class="col-3">
                                    <button type="button" class="btn btn-light w-100 add-line">+ Add</button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between fw-bold mt-2 mb-3 pb-2 border-bottom">
                            <span>Order Total</span>
                            <span id="orderTotalDisplay">$0.00</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="e_dahab">e-Dahab</option>
                            </select>
                            <div class="form-text">The order is recorded as pending. The receipt and payment are created automatically once the order status is changed to Completed.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy">Create Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="orderNewCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="orderNewCustomerForm">
                    <div class="modal-header">
                        <h5 class="modal-title">New Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="orderNewCustomerError" class="alert alert-danger py-2 small d-none"></div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Phone (optional)</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <p class="text-muted small mb-0">You can enter this customer's delivery address in the Shipping Address field after creating them.</p>
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
    const productLines = document.getElementById('productLines');
    const orderTotalDisplay = document.getElementById('orderTotalDisplay');

    function recalcOrderTotal() {
        let total = 0;
        productLines.querySelectorAll('.product-line').forEach(line => {
            const select = line.querySelector('.product-select');
            const qty = parseFloat(line.querySelector('.order-qty').value) || 0;
            const option = select.options[select.selectedIndex];
            const price = option ? parseFloat(option.dataset.price) || 0 : 0;
            total += price * qty;
        });
        orderTotalDisplay.textContent = '$' + total.toFixed(2);
        return total;
    }

    productLines.addEventListener('input', recalcOrderTotal);
    productLines.addEventListener('change', recalcOrderTotal);
    recalcOrderTotal();

    let lineIndex = 1;
    productLines.addEventListener('click', function (e) {
        if (e.target.classList.contains('add-line')) {
            const template = document.querySelector('.product-line').cloneNode(true);
            template.querySelectorAll('select, input').forEach(el => {
                const name = el.getAttribute('name');
                if (name) el.setAttribute('name', name.replace(/\[\d+\]/, `[${lineIndex}]`));
                if (el.tagName === 'INPUT') el.value = el.type === 'number' ? 1 : '';
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });
            template.querySelector('.add-line').outerHTML =
                '<button type="button" class="btn btn-outline-danger w-100 remove-line">Remove</button>';
            productLines.appendChild(template);
            lineIndex++;
        }
        if (e.target.classList.contains('remove-line')) {
            e.target.closest('.product-line').remove();
        }
        recalcOrderTotal();
    });

    document.getElementById('addOrderModal').addEventListener('hidden.bs.modal', function () {
        amountPaidTouched = false;
    });

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('addOrderModal')).show();
        });
    @endif

    const orderCustomerSelect = document.getElementById('orderCustomerSelect');
    const orderCustomerSearch = document.getElementById('orderCustomerSearch');
    const orderCustomerResults = document.getElementById('orderCustomerResults');
    const orderCustomerError = document.getElementById('orderCustomerError');
    const orderNewCustomerForm = document.getElementById('orderNewCustomerForm');
    const orderNewCustomerError = document.getElementById('orderNewCustomerError');
    const orderNewCustomerModalEl = document.getElementById('orderNewCustomerModal');
    let orderCustomers = @json($customers->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone]));

    function customerLabel(c) { return c.name + (c.phone ? ' - ' + c.phone : ''); }

    function renderCustomerResults(term) {
        const list = !term
            ? orderCustomers
            : orderCustomers.filter(c => (c.name + ' ' + (c.phone || '')).toLowerCase().includes(term));

        if (list.length === 0) {
            orderCustomerResults.innerHTML = '<div class="list-group-item text-muted small">No customers found.</div>';
        } else {
            orderCustomerResults.innerHTML = list.slice(0, 30).map(c => (
                '<button type="button" class="list-group-item list-group-item-action" data-id="' + c.id + '">' + customerLabel(c) + '</button>'
            )).join('');
        }
        orderCustomerResults.style.display = '';
    }

    function selectCustomer(id) {
        const c = orderCustomers.find(c => String(c.id) === String(id));
        if (!c) return;
        orderCustomerSelect.value = c.id;
        orderCustomerSearch.value = customerLabel(c);
        orderCustomerResults.style.display = 'none';
        orderCustomerError.classList.add('d-none');
    }

    if (orderCustomerSearch) {
        orderCustomerSearch.addEventListener('focus', function () { renderCustomerResults(this.value.trim().toLowerCase()); });
        orderCustomerSearch.addEventListener('input', function () {
            orderCustomerSelect.value = '';
            renderCustomerResults(this.value.trim().toLowerCase());
        });
        orderCustomerResults.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-id]');
            if (btn) selectCustomer(btn.dataset.id);
        });
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#orderCustomerSearch') && !e.target.closest('#orderCustomerResults')) {
                orderCustomerResults.style.display = 'none';
            }
        });
    }

    document.getElementById('orderForm').addEventListener('submit', function (e) {
        if (!orderCustomerSelect.value) {
            e.preventDefault();
            orderCustomerError.classList.remove('d-none');
            orderCustomerSearch.focus();
        }
    });

    orderNewCustomerForm.addEventListener('submit', function (e) {
        e.preventDefault();
        orderNewCustomerError.classList.add('d-none');

        const formData = new FormData(orderNewCustomerForm);
        formData.append('status', 'active');

        fetch('{{ route('customers.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw data;
            return data;
        })
        .then(customer => {
            orderCustomers.push({ id: customer.id, name: customer.name, phone: customer.phone });
            selectCustomer(customer.id);

            orderNewCustomerForm.reset();
            bootstrap.Modal.getInstance(orderNewCustomerModalEl).hide();
        })
        .catch(err => {
            const messages = err.errors ? Object.values(err.errors).flat().join(' ') : 'Something went wrong. Please try again.';
            orderNewCustomerError.textContent = messages;
            orderNewCustomerError.classList.remove('d-none');
        });
    });
</script>
@endsection
