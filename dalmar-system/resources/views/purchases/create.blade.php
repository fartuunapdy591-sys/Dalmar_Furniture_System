@extends('layouts.app')
@section('title', 'New Purchase')
@section('content')
    <div class="page-title mb-1">New Purchase</div>
    <div class="page-subtitle mb-3">Home / Purchases / New</div>

    <form action="{{ route('purchases.store') }}" method="POST">
        @csrf
        <div class="card-panel mb-3">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label small fw-semibold">Supplier</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">-- Select Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label small fw-semibold">Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', now()->toDateString()) }}" required>
                </div>
            </div>
        </div>

        <div class="card-panel mb-3">
            <div class="fw-semibold mb-2">Items</div>
            <div id="purchaseLines">
                <div class="row purchase-line mb-2 align-items-center">
                    <div class="col-md-4">
                        <select name="items[0][product_id]" class="form-select purchase-product-select" required>
                            <option value="">-- Select Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-cost="{{ $product->cost }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="items[0][qty]" class="form-control purchase-qty" placeholder="Qty" min="1" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" min="0" name="items[0][unit_cost]" class="form-control purchase-unit-cost" placeholder="Unit Cost" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" min="0" name="items[0][discount]" class="form-control purchase-line-discount" placeholder="Discount" value="0">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-light w-100 purchase-add-line">+ Add</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card-panel mb-3">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Discount (overall)</label>
                            <input type="number" step="0.01" min="0" name="discount" id="purchaseDiscount" class="form-control" value="{{ old('discount', 0) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Tax</label>
                            <input type="number" step="0.01" min="0" name="tax" id="purchaseTax" class="form-control" value="{{ old('tax', 0) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card-panel mb-3">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Amount Paid Now (optional)</label>
                            <input type="number" step="0.01" min="0" name="amount_paid" id="purchaseAmountPaid" class="form-control" value="{{ old('amount_paid') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between fw-bold" style="font-size: 18px;">
                        <span>Estimated Total</span>
                        <span id="purchaseTotalDisplay">$0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-navy">Save Purchase</button>
            <a href="{{ route('purchases.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    const purchaseLines = document.getElementById('purchaseLines');
    const purchaseDiscount = document.getElementById('purchaseDiscount');
    const purchaseTax = document.getElementById('purchaseTax');
    const purchaseTotalDisplay = document.getElementById('purchaseTotalDisplay');
    let purchaseLineIndex = 1;

    function recalcTotal() {
        let subtotal = 0;
        purchaseLines.querySelectorAll('.purchase-line').forEach(line => {
            const qty = parseFloat(line.querySelector('.purchase-qty').value) || 0;
            const unitCost = parseFloat(line.querySelector('.purchase-unit-cost').value) || 0;
            const discount = parseFloat(line.querySelector('.purchase-line-discount').value) || 0;
            subtotal += (qty * unitCost) - discount;
        });
        const discount = parseFloat(purchaseDiscount.value) || 0;
        const tax = parseFloat(purchaseTax.value) || 0;
        const total = Math.max(0, subtotal - discount + tax);
        purchaseTotalDisplay.textContent = '$' + total.toFixed(2);
    }

    purchaseLines.addEventListener('input', recalcTotal);
    purchaseDiscount.addEventListener('input', recalcTotal);
    purchaseTax.addEventListener('input', recalcTotal);

    purchaseLines.addEventListener('change', function (e) {
        if (e.target.classList.contains('purchase-product-select')) {
            const option = e.target.options[e.target.selectedIndex];
            const cost = option ? parseFloat(option.dataset.cost) : 0;
            const line = e.target.closest('.purchase-line');
            const unitCostInput = line.querySelector('.purchase-unit-cost');
            if (cost > 0 && !unitCostInput.value) {
                unitCostInput.value = cost.toFixed(2);
            }
            recalcTotal();
        }
    });

    purchaseLines.addEventListener('click', function (e) {
        if (e.target.classList.contains('purchase-add-line')) {
            const template = document.querySelector('.purchase-line').cloneNode(true);
            template.querySelectorAll('select, input').forEach(el => {
                const name = el.getAttribute('name');
                if (name) el.setAttribute('name', name.replace(/\[\d+\]/, `[${purchaseLineIndex}]`));
                if (el.classList.contains('purchase-qty')) el.value = 1;
                if (el.classList.contains('purchase-unit-cost')) el.value = '';
                if (el.classList.contains('purchase-line-discount')) el.value = 0;
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });
            template.querySelector('.purchase-add-line').outerHTML =
                '<button type="button" class="btn btn-outline-danger w-100 purchase-remove-line">Remove</button>';
            purchaseLines.appendChild(template);
            purchaseLineIndex++;
        }
        if (e.target.classList.contains('purchase-remove-line')) {
            e.target.closest('.purchase-line').remove();
            recalcTotal();
        }
    });
</script>
@endsection
