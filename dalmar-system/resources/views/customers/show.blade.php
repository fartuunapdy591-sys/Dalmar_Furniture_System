@extends('layouts.app')
@section('title', 'Customer Debt Account')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">{{ $customer->name }}</div>
            <div class="page-subtitle">Home / Customers / {{ $customer->name }}</div>
        </div>
        <div class="d-flex gap-2">
            @if($customer->balance_due > 0)
                <button type="button" class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#payDebtModal">
                    <i class="bi bi-wallet2 me-1"></i> Pay Debt / Bixi Daynta
                </button>
            @endif
            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-light"><i class="bi bi-pencil-fill me-1"></i> Edit</a>
            <button class="btn btn-light" onclick="window.print()"><i class="bi bi-printer-fill me-1"></i> Print Statement</button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card-panel">
                <div class="text-muted small">Total Purchases</div>
                <div class="fs-4 fw-bold">${{ number_format($customer->opening_balance + $customer->receipts->sum('amount'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-panel">
                <div class="text-muted small">Total Paid</div>
                <div class="fs-4 fw-bold">${{ number_format($customer->total_paid, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-panel">
                <div class="text-muted small">Remaining Debt</div>
                <div class="fs-4 fw-bold text-danger">${{ number_format($customer->balance_due, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-panel">
                <div class="text-muted small">Credit Sales</div>
                <div class="fs-4 fw-bold">${{ number_format($customer->total_credit_sales, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card-panel h-100">
                <div class="fw-semibold mb-2">Contact Info</div>
                <table class="table-dalmar">
                    <tr><td class="text-muted">Email</td><td class="text-end">{{ $customer->email ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Phone</td><td class="text-end">{{ $customer->phone ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Address</td><td class="text-end">{{ $customer->address ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Status</td><td class="text-end"><span class="badge-status badge-{{ $customer->status }}">{{ ucfirst($customer->status) }}</span></td></tr>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-panel h-100">
                <div class="fw-semibold mb-2">Debt Status</div>
                <table class="table-dalmar">
                    <tr><td class="text-muted">Next Due Date</td><td class="text-end">{{ $customer->next_due_date ? \Illuminate\Support\Carbon::parse($customer->next_due_date)->format('M d, Y') : '-' }}</td></tr>
                    <tr>
                        <td class="text-muted">Account Status</td>
                        <td class="text-end">
                            @if($customer->balance_due <= 0)
                                <span class="badge-status badge-paid">Cleared</span>
                            @elseif($customer->has_overdue_debt)
                                <span class="badge-status badge-unpaid">Overdue</span>
                            @else
                                <span class="badge-status badge-partial">Outstanding</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="card-panel mb-3">
        <div class="fw-semibold mb-2">Credit Sales</div>
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>Receipt #</th>
                <th>Date</th>
                <th>Due Date</th>
                <th class="text-end">Amount</th>
                <th class="text-end">Balance Due</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse($creditSales as $receipt)
                <tr>
                    <td><a href="{{ route('receipts.show', $receipt) }}">{{ $receipt->receipt_number }}</a></td>
                    <td>{{ $receipt->issued_date->format('M d, Y') }}</td>
                    <td>{{ $receipt->due_date?->format('M d, Y') ?? '-' }}</td>
                    <td class="text-end">${{ number_format($receipt->amount, 2) }}</td>
                    <td class="text-end">${{ number_format($receipt->balance_due, 2) }}</td>
                    <td><span class="badge-status badge-{{ $receipt->status }}">{{ ucfirst($receipt->status) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No credit sales yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-panel" id="transactions">
        <div class="fw-semibold mb-2">Transaction History</div>
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Reference</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Balance</th>
            </tr>
            </thead>
            <tbody>
            @forelse($transactions as $entry)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($entry['date'])->format('M d, Y') }}</td>
                    <td>{{ $entry['type'] }}</td>
                    <td><a href="{{ $entry['link'] }}">{{ $entry['reference'] }}</a></td>
                    <td class="text-end">{{ $entry['debit'] > 0 ? '$'.number_format($entry['debit'], 2) : '-' }}</td>
                    <td class="text-end">{{ $entry['credit'] > 0 ? '$'.number_format($entry['credit'], 2) : '-' }}</td>
                    <td class="text-end fw-semibold">${{ number_format($entry['balance'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No transactions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="payDebtModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('customer-debts.pay', $customer) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Pay Debt / Bixi Daynta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Remaining debt: <strong>${{ number_format($customer->balance_due, 2) }}</strong></p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Amount Paid</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Payment Method</label>
                            <select name="method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="sahal">Sahal</option>
                                <option value="e_dahab">e-Dahab</option>
                                <option value="mycash">MyCash</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Notes (optional)</label>
                            <input type="text" name="notes" class="form-control">
                        </div>
                        @if(auth()->user()?->isAdmin())
                            <div class="form-check">
                                <input type="checkbox" name="allow_overpayment" value="1" class="form-check-input" id="allowOverpayment">
                                <label class="form-check-label small" for="allowOverpayment">Allow amount to exceed remaining debt (admin override)</label>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    @if($errors->any() || request('pay'))
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('payDebtModal')).show();
        });
    @endif

    @if(request('print'))
        window.addEventListener('load', () => window.print());
    @endif
</script>
@endsection
