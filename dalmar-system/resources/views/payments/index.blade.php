@extends('layouts.app')

@section('title', 'Payments')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Payments</div>
            <div class="page-subtitle">Home / Payments</div>
        </div>
        <button type="button" class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#recordPaymentModal"><i class="bi bi-plus-lg me-1"></i> Record Payment</button>
    </div>

    <div class="card-panel">
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>Receipt #</th>
                <th>Order</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Sender Phone</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td class="fw-semibold">{{ $payment->receipt_number ?? '-' }}</td>
                    <td>#{{ $payment->order->order_number ?? '-' }}</td>
                    <td>{{ $payment->receipt->customer->name ?? '-' }}</td>
                    <td>${{ number_format($payment->amount, 2) }}</td>
                    <td><span class="badge-method method-{{ $payment->method }}">{{ $payment->method_label }}</span></td>
                    <td>{{ $payment->sender_phone ?? '-' }}</td>
                    <td><span class="badge-status badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                    <td>{{ $payment->paid_at?->format('M d, Y') ?? '-' }}</td>
                    <td>
                        <a href="{{ route('payments.show', $payment) }}" class="icon-btn"><i class="bi bi-eye-fill"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No payments found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $payments->links() }}</div>
    </div>

    <div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('payments.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Record Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if($receipts->isEmpty())
                            <p class="text-muted mb-0">There are no unpaid or partially paid receipts right now.</p>
                        @else
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Receipt</label>
                                <select name="receipt_id" id="receiptSelect" class="form-select" required>
                                    <option value="">-- Select Receipt --</option>
                                    @foreach($receipts as $receipt)
                                        <option value="{{ $receipt->id }}"
                                            data-balance="{{ $receipt->balance_due }}"
                                            @selected($selectedReceiptId === $receipt->id)>
                                            {{ $receipt->receipt_number }} - {{ $receipt->customer->name ?? '-' }} (Balance: ${{ number_format($receipt->balance_due, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-semibold">Amount</label>
                                    <input type="number" step="0.01" min="0.01" name="amount" id="amountInput" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-semibold">Payment Method</label>
                                    <select name="method" id="paymentMethodSelect" class="form-select" required>
                                        <option value="sahal">Sahal</option>
                                        <option value="e_dahab">e-Dahab</option>
                                        <option value="mycash">MyCash</option>
                                        <option value="cash">Cash</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3" id="senderPhoneWrap">
                                <label class="form-label small fw-semibold">Sender's Phone Number</label>
                                <input type="text" name="sender_phone" class="form-control" placeholder="e.g. 61XXXXXXX">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Reference (optional)</label>
                                <input type="text" name="reference" class="form-control" placeholder="Transaction / reference number">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Notes (optional)</label>
                                <input type="text" name="notes" class="form-control">
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        @if($receipts->isNotEmpty())
                            <button type="submit" class="btn btn-navy">Record Payment</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const receiptSelect = document.getElementById('receiptSelect');
    const amountInput = document.getElementById('amountInput');
    const paymentMethodSelect = document.getElementById('paymentMethodSelect');
    const senderPhoneWrap = document.getElementById('senderPhoneWrap');

    function toggleSenderPhone() {
        if (!paymentMethodSelect || !senderPhoneWrap) return;
        senderPhoneWrap.classList.toggle('d-none', paymentMethodSelect.value === 'cash');
    }

    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', toggleSenderPhone);
        toggleSenderPhone();
    }

    function applyBalance() {
        if (!receiptSelect || !amountInput) return;
        const option = receiptSelect.options[receiptSelect.selectedIndex];
        const balance = option ? parseFloat(option.dataset.balance) : 0;
        if (balance > 0) {
            amountInput.value = balance.toFixed(2);
            amountInput.max = balance;
        }
    }

    if (receiptSelect) {
        receiptSelect.addEventListener('change', applyBalance);
        applyBalance();
    }

    @if($errors->any() || request('receipt_id'))
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('recordPaymentModal')).show();
        });
    @endif
</script>
@endsection
