@extends('layouts.app')

@section('title', 'Point of Sale')

@section('content')
    <div class="page-title">Point of Sale</div>
    <div class="page-subtitle">Home / POS</div>

    <div class="pos-layout">
        <div class="pos-catalog">
            <div class="pos-search">
                <i class="bi bi-search"></i>
                <input type="text" id="posSearch" placeholder="Search products...">
            </div>

            <div class="pos-categories" id="posCategories">
                <button type="button" class="pos-category-chip active" data-category="">All</button>
                @foreach($categories as $category)
                    <button type="button" class="pos-category-chip" data-category="{{ $category->id }}">{{ $category->name }}</button>
                @endforeach
            </div>

            <div class="pos-grid" id="posGrid">
                @forelse($products as $product)
                    <div class="pos-product-card {{ $product->stock <= 0 ? 'pos-product-card--unavailable' : '' }}"
                         data-id="{{ $product->id }}"
                         data-name="{{ strtolower($product->name) }}"
                         data-category="{{ $product->category_id }}"
                         data-price="{{ $product->price }}"
                         data-stock="{{ $product->stock }}">
                        <span class="badge-status {{ $product->stock > 0 ? 'badge-available' : 'badge-unavailable' }} pos-availability-badge">
                            {{ $product->stock > 0 ? 'Available' : 'Unavailable' }}
                        </span>
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="pos-product-image">
                        @else
                            <div class="pos-product-icon"><i class="bi bi-box-seam-fill"></i></div>
                        @endif
                        <div class="pos-product-name">{{ $product->name }}</div>
                        <div class="pos-product-cat">{{ $product->category->name ?? '-' }}</div>
                        <div class="pos-product-price">${{ number_format($product->price, 2) }}</div>
                        <div class="pos-product-stock">{{ $product->stock }} in stock</div>
                    </div>
                @empty
                    <div class="pos-empty">No products available.</div>
                @endforelse
            </div>
        </div>

        <div class="pos-cart">
            <div class="pos-cart-title"><span><i class="bi bi-cart3 me-2"></i>Cart</span> <span id="posCartCount" class="text-muted small">0 items</span></div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold mb-0">Customer</label>
                    <button type="button" class="btn btn-link btn-sm p-0 small" data-bs-toggle="modal" data-bs-target="#posNewCustomerModal">
                        <i class="bi bi-plus-circle me-1"></i>New Customer
                    </button>
                </div>
                <input type="text" id="posCustomerSearch" class="form-control form-control-sm mb-1" placeholder="Search by name or phone...">
                <select id="posCustomer" class="form-select" size="1">
                    <option value="" data-search="walk-in">Walk-in Customer (no registration)</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" data-search="{{ strtolower($customer->name.' '.$customer->phone) }}">
                            {{ $customer->name }}{{ $customer->phone ? ' - '.$customer->phone : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pos-cart-items" id="posCartItems">
                <div class="pos-cart-empty">Your cart is empty. Click a product to add it.</div>
            </div>

            @if($canDiscount)
                <div class="mb-3">
                    <label class="form-label small fw-semibold mb-1">Discount</label>
                    <div class="d-flex gap-2">
                        <select id="posDiscountType" class="form-select form-select-sm" style="max-width: 110px;">
                            <option value="">No Discount</option>
                            <option value="percentage">Percent %</option>
                            <option value="fixed">Fixed $</option>
                        </select>
                        <input type="number" id="posDiscountValue" class="form-control form-control-sm" placeholder="0" min="0" step="0.01" disabled>
                    </div>
                </div>
            @endif

            @if($canCreditSale)
                <div class="form-check mb-3">
                    <input type="checkbox" id="posCreditSale" class="form-check-input">
                    <label class="form-check-label fw-semibold" for="posCreditSale">
                        <i class="bi bi-journal-minus me-1"></i> Credit Sale / Dayn
                    </label>
                </div>
                <div id="posCreditSection" class="d-none mb-3">
                    <p class="text-muted small mb-2">A registered customer (not walk-in) is required for a credit sale.</p>
                    <label class="form-label small fw-semibold">Amount Paying Now (optional)</label>
                    <input type="number" id="posAmountPaid" class="form-control mb-2" min="0" step="0.01" value="0">
                    <label class="form-label small fw-semibold">Due Date</label>
                    <input type="date" id="posDueDate" class="form-control">
                </div>
            @endif

            <div class="pos-cart-summary">
                <div class="pos-cart-summary-row">
                    <span>Items</span>
                    <span id="posSummaryItems">0</span>
                </div>
                <div class="pos-cart-summary-row">
                    <span>Original Total</span>
                    <span id="posSummarySubtotal">$0.00</span>
                </div>
                <div class="pos-cart-summary-row" id="posDiscountRow" style="display:none;">
                    <span>Discount</span>
                    <span id="posSummaryDiscount">-$0.00</span>
                </div>
                <div class="pos-cart-total">
                    <span>Final Total</span>
                    <span id="posSummaryTotal">$0.00</span>
                </div>
                <div class="pos-cart-summary-row" id="posRemainingRow" style="display:none;">
                    <span>Remaining Debt</span>
                    <span id="posSummaryRemaining" class="fw-semibold text-danger">$0.00</span>
                </div>

                <label class="form-label small fw-semibold mt-2">Payment Method</label>
                <select id="posPaymentMethod" class="form-select mb-3">
                    <option value="sahal">Sahal</option>
                    <option value="e_dahab">e-Dahab</option>
                    <option value="mycash">MyCash</option>
                    <option value="cash">Cash</option>
                </select>

                <div id="posSenderPhoneWrap" class="mb-3">
                    <label class="form-label small fw-semibold">Sender's Phone Number</label>
                    <input type="text" id="posSenderPhone" class="form-control" placeholder="e.g. 61XXXXXXX">
                </div>

                <div id="posCashReceivedWrap" class="mb-3 d-none">
                    <label class="form-label small fw-semibold">Cash Received</label>
                    <input type="number" id="posCashReceived" class="form-control" min="0" step="0.01" placeholder="0.00">
                </div>
                <div class="pos-cart-summary-row" id="posChangeDueRow" style="display:none;">
                    <span>Change Due</span>
                    <span id="posSummaryChangeDue" class="fw-semibold text-success">$0.00</span>
                </div>

                <button type="button" id="posCheckoutBtn" class="btn btn-navy w-100" disabled>
                    <i class="bi bi-check-circle-fill me-1"></i> Complete Sale
                </button>
            </div>
        </div>
    </div>

    <form id="posCheckoutForm" action="{{ route('pos.checkout') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="customer_id" id="posFormCustomer">
        <input type="hidden" name="payment_method" id="posFormMethod">
        <input type="hidden" name="sender_phone" id="posFormSenderPhone">
        <input type="hidden" name="discount_type" id="posFormDiscountType">
        <input type="hidden" name="discount_value" id="posFormDiscountValue">
        <input type="hidden" name="is_credit_sale" id="posFormCreditSale">
        <input type="hidden" name="amount_paid" id="posFormAmountPaid">
        <input type="hidden" name="due_date" id="posFormDueDate">
        <div id="posFormItems"></div>
    </form>

    <div class="modal fade" id="posNewCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="posNewCustomerForm">
                    <div class="modal-header">
                        <h5 class="modal-title">New Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="posNewCustomerError" class="alert alert-danger py-2 small d-none"></div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="e.g. 61XXXXXXX" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email (optional)</label>
                            <input type="email" name="email" class="form-control">
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
    const cart = {};

    const posGrid = document.getElementById('posGrid');
    const posSearch = document.getElementById('posSearch');
    const posCategories = document.getElementById('posCategories');
    const posCartItems = document.getElementById('posCartItems');
    const posCartCount = document.getElementById('posCartCount');
    const posSummaryItems = document.getElementById('posSummaryItems');
    const posSummarySubtotal = document.getElementById('posSummarySubtotal');
    const posDiscountRow = document.getElementById('posDiscountRow');
    const posSummaryDiscount = document.getElementById('posSummaryDiscount');
    const posSummaryTotal = document.getElementById('posSummaryTotal');
    const posRemainingRow = document.getElementById('posRemainingRow');
    const posSummaryRemaining = document.getElementById('posSummaryRemaining');
    const posCheckoutBtn = document.getElementById('posCheckoutBtn');
    const posCustomer = document.getElementById('posCustomer');
    const posCustomerSearch = document.getElementById('posCustomerSearch');

    if (posCustomerSearch) {
        posCustomerSearch.addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();
            let selectedHidden = false;

            Array.from(posCustomer.options).forEach(option => {
                const matches = !term || (option.dataset.search || '').includes(term);
                option.hidden = !matches;
                if (option.selected && !matches) selectedHidden = true;
            });

            if (selectedHidden) {
                const firstVisible = Array.from(posCustomer.options).find(o => !o.hidden);
                if (firstVisible) posCustomer.value = firstVisible.value;
            }
        });
    }
    const posPaymentMethod = document.getElementById('posPaymentMethod');
    const posSenderPhoneWrap = document.getElementById('posSenderPhoneWrap');
    const posSenderPhone = document.getElementById('posSenderPhone');
    const posDiscountType = document.getElementById('posDiscountType');
    const posDiscountValue = document.getElementById('posDiscountValue');
    const posCreditSale = document.getElementById('posCreditSale');
    const posCreditSection = document.getElementById('posCreditSection');
    const posAmountPaid = document.getElementById('posAmountPaid');
    const posDueDate = document.getElementById('posDueDate');

    function currentSubtotal() {
        return Object.keys(cart).reduce((sum, id) => sum + (cart[id].price * cart[id].qty), 0);
    }

    function currentDiscount(subtotal) {
        if (!posDiscountType || !posDiscountType.value) return 0;
        const value = parseFloat(posDiscountValue.value) || 0;
        let discount = posDiscountType.value === 'percentage'
            ? subtotal * Math.min(value, 100) / 100
            : value;
        return Math.max(0, Math.min(discount, subtotal));
    }

    if (posDiscountType) {
        posDiscountType.addEventListener('change', function () {
            posDiscountValue.disabled = !this.value;
            if (!this.value) posDiscountValue.value = '';
            renderCart();
        });
        posDiscountValue.addEventListener('input', renderCart);
    }

    if (posCreditSale) {
        posCreditSale.addEventListener('change', function () {
            posCreditSection.classList.toggle('d-none', !this.checked);
            renderCart();
        });
        posAmountPaid.addEventListener('input', renderCart);
    }

    function toggleSenderPhone() {
        posSenderPhoneWrap.classList.toggle('d-none', posPaymentMethod.value === 'cash');
    }

    const posCashReceivedWrap = document.getElementById('posCashReceivedWrap');
    const posCashReceived = document.getElementById('posCashReceived');
    const posChangeDueRow = document.getElementById('posChangeDueRow');
    const posSummaryChangeDue = document.getElementById('posSummaryChangeDue');

    function toggleCashReceived() {
        const isCash = posPaymentMethod.value === 'cash';
        posCashReceivedWrap.classList.toggle('d-none', !isCash);
        if (!isCash) {
            posCashReceived.value = '';
            posChangeDueRow.style.display = 'none';
        }
        updateChangeDue();
    }

    function updateChangeDue() {
        if (posPaymentMethod.value !== 'cash') { posChangeDueRow.style.display = 'none'; return; }
        const received = parseFloat(posCashReceived.value) || 0;
        if (!received) { posChangeDueRow.style.display = 'none'; return; }

        const subtotal = currentSubtotal();
        const discount = currentDiscount(subtotal);
        const total = Math.max(0, subtotal - discount);
        const amountDueNow = (posCreditSale && posCreditSale.checked)
            ? Math.min(parseFloat(posAmountPaid.value) || 0, total)
            : total;
        const change = received - amountDueNow;

        posChangeDueRow.style.display = '';
        if (change < 0) {
            posSummaryChangeDue.textContent = '-$' + Math.abs(change).toFixed(2) + ' short';
            posSummaryChangeDue.classList.remove('text-success');
            posSummaryChangeDue.classList.add('text-danger');
        } else {
            posSummaryChangeDue.textContent = '$' + change.toFixed(2);
            posSummaryChangeDue.classList.remove('text-danger');
            posSummaryChangeDue.classList.add('text-success');
        }
    }

    posPaymentMethod.addEventListener('change', function () { toggleSenderPhone(); toggleCashReceived(); });
    posCashReceived.addEventListener('input', updateChangeDue);
    toggleSenderPhone();
    toggleCashReceived();

    function filterProducts() {
        const term = posSearch.value.trim().toLowerCase();
        const activeCategory = posCategories.querySelector('.active').dataset.category;

        posGrid.querySelectorAll('.pos-product-card').forEach(card => {
            const matchesTerm = card.dataset.name.includes(term);
            const matchesCategory = !activeCategory || card.dataset.category === activeCategory;
            card.style.display = (matchesTerm && matchesCategory) ? '' : 'none';
        });
    }

    posSearch.addEventListener('input', filterProducts);

    posCategories.addEventListener('click', function (e) {
        const chip = e.target.closest('.pos-category-chip');
        if (!chip) return;
        posCategories.querySelectorAll('.pos-category-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        filterProducts();
    });

    posGrid.addEventListener('click', function (e) {
        const card = e.target.closest('.pos-product-card');
        if (!card) return;

        const id = card.dataset.id;
        const stock = parseInt(card.dataset.stock, 10);
        const current = cart[id]?.qty || 0;

        if (stock <= 0) {
            alert('This product is unavailable (out of stock).');
            return;
        }

        if (current >= stock) {
            alert('Only ' + stock + ' in stock.');
            return;
        }

        if (cart[id]) {
            cart[id].qty++;
        } else {
            cart[id] = {
                id: id,
                name: card.dataset.name,
                displayName: card.querySelector('.pos-product-name').textContent,
                price: parseFloat(card.dataset.price),
                stock: stock,
                qty: 1,
            };
        }

        renderCart();
    });

    function changeQty(id, delta) {
        if (!cart[id]) return;
        const newQty = cart[id].qty + delta;

        if (newQty <= 0) {
            delete cart[id];
        } else if (newQty > cart[id].stock) {
            alert('Only ' + cart[id].stock + ' in stock.');
            return;
        } else {
            cart[id].qty = newQty;
        }

        renderCart();
    }

    function removeItem(id) {
        delete cart[id];
        renderCart();
    }

    function renderCart() {
        const ids = Object.keys(cart);

        if (ids.length === 0) {
            posCartItems.innerHTML = '<div class="pos-cart-empty">Your cart is empty. Click a product to add it.</div>';
        } else {
            posCartItems.innerHTML = ids.map(id => {
                const item = cart[id];
                const lineTotal = (item.price * item.qty).toFixed(2);
                return `
                    <div class="pos-cart-item">
                        <div class="flex-grow-1">
                            <div class="pos-cart-item-name">${item.displayName}</div>
                            <div class="pos-cart-item-price">$${item.price.toFixed(2)} each</div>
                            <div class="pos-qty-control mt-1">
                                <button type="button" onclick="changeQty('${id}', -1)">-</button>
                                <span>${item.qty}</span>
                                <button type="button" onclick="changeQty('${id}', 1)">+</button>
                            </div>
                        </div>
                        <div class="pos-cart-item-total">$${lineTotal}</div>
                        <i class="bi bi-trash3 pos-remove" onclick="removeItem('${id}')"></i>
                    </div>
                `;
            }).join('');
        }

        const totalQty = ids.reduce((sum, id) => sum + cart[id].qty, 0);
        const subtotal = currentSubtotal();
        const discount = currentDiscount(subtotal);
        const total = Math.max(0, subtotal - discount);

        posCartCount.textContent = totalQty + ' item' + (totalQty === 1 ? '' : 's');
        posSummaryItems.textContent = totalQty;
        posSummarySubtotal.textContent = '$' + subtotal.toFixed(2);
        posSummaryTotal.textContent = '$' + total.toFixed(2);

        if (discount > 0) {
            posDiscountRow.style.display = '';
            posSummaryDiscount.textContent = '-$' + discount.toFixed(2);
        } else if (posDiscountRow) {
            posDiscountRow.style.display = 'none';
        }

        if (posCreditSale && posCreditSale.checked) {
            const paidNow = Math.min(parseFloat(posAmountPaid.value) || 0, total);
            const remaining = Math.max(0, total - paidNow);
            posRemainingRow.style.display = '';
            posSummaryRemaining.textContent = '$' + remaining.toFixed(2);
        } else if (posRemainingRow) {
            posRemainingRow.style.display = 'none';
        }

        updateChangeDue();

        posCheckoutBtn.disabled = ids.length === 0;
    }

    posCheckoutBtn.addEventListener('click', function () {
        const ids = Object.keys(cart);
        if (ids.length === 0) return;

        const isCredit = !!(posCreditSale && posCreditSale.checked);

        if (isCredit && !posCustomer.value) {
            alert('Please select a registered customer for a credit sale.');
            return;
        }

        document.getElementById('posFormCustomer').value = posCustomer.value;
        document.getElementById('posFormMethod').value = posPaymentMethod.value;
        document.getElementById('posFormSenderPhone').value = posPaymentMethod.value === 'cash' ? '' : posSenderPhone.value;
        document.getElementById('posFormDiscountType').value = (posDiscountType && posDiscountType.value) || '';
        document.getElementById('posFormDiscountValue').value = (posDiscountValue && posDiscountValue.value) || 0;
        document.getElementById('posFormCreditSale').value = isCredit ? '1' : '0';
        document.getElementById('posFormAmountPaid').value = isCredit ? ((posAmountPaid && posAmountPaid.value) || 0) : '';
        document.getElementById('posFormDueDate').value = isCredit ? ((posDueDate && posDueDate.value) || '') : '';

        const itemsContainer = document.getElementById('posFormItems');
        itemsContainer.innerHTML = '';
        ids.forEach((id, index) => {
            itemsContainer.innerHTML += `
                <input type="hidden" name="items[${index}][id]" value="${id}">
                <input type="hidden" name="items[${index}][qty]" value="${cart[id].qty}">
            `;
        });

        document.getElementById('posCheckoutForm').submit();
    });

    const posNewCustomerForm = document.getElementById('posNewCustomerForm');
    const posNewCustomerError = document.getElementById('posNewCustomerError');
    const posNewCustomerModalEl = document.getElementById('posNewCustomerModal');

    posNewCustomerForm.addEventListener('submit', function (e) {
        e.preventDefault();
        posNewCustomerError.classList.add('d-none');

        const formData = new FormData(posNewCustomerForm);
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
            const option = document.createElement('option');
            option.value = customer.id;
            option.textContent = customer.name + (customer.phone ? ' - ' + customer.phone : '');
            option.dataset.search = (customer.name + ' ' + (customer.phone || '')).toLowerCase();
            option.selected = true;
            posCustomer.appendChild(option);

            posNewCustomerForm.reset();
            bootstrap.Modal.getInstance(posNewCustomerModalEl).hide();
        })
        .catch(err => {
            const messages = err.errors ? Object.values(err.errors).flat().join(' ') : 'Something went wrong. Please try again.';
            posNewCustomerError.textContent = messages;
            posNewCustomerError.classList.remove('d-none');
        });
    });
</script>
@endsection
