@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Expenses</div>
            <div class="page-subtitle">Home / Expenses</div>
        </div>
        <button type="button" class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#addExpenseModal"><i class="bi bi-plus-lg me-1"></i> Add Expense</button>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card-panel">
                <div class="text-muted small">Today</div>
                <div class="fs-4 fw-bold">${{ number_format($today, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-panel">
                <div class="text-muted small">This Week</div>
                <div class="fs-4 fw-bold">${{ number_format($thisWeek, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-panel">
                <div class="text-muted small">This Month</div>
                <div class="fs-4 fw-bold">${{ number_format($thisMonth, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-panel">
                <div class="text-muted small">All Time</div>
                <div class="fs-4 fw-bold">${{ number_format($total, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="card-panel mb-3">
        <form class="row g-2" method="GET">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search description or paid to..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\Expense::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_method" class="form-select">
                    <option value="">All Methods</option>
                    @foreach(\App\Models\Expense::PAYMENT_METHODS as $key => $label)
                        <option value="{{ $key }}" @selected(request('payment_method') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-light w-100">Filter</button>
            </div>
        </form>
    </div>

    <div class="card-panel">
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>Expense #</th>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Paid To</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($expenses as $expense)
                <tr>
                    <td class="fw-semibold">{{ $expense->expense_number }}</td>
                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                    <td>{{ $expense->category_label }}</td>
                    <td>{{ $expense->description }}</td>
                    <td>${{ number_format($expense->amount, 2) }}</td>
                    <td><span class="badge-method method-{{ $expense->payment_method }}">{{ \App\Models\Expense::PAYMENT_METHODS[$expense->payment_method] ?? ucfirst($expense->payment_method) }}</span></td>
                    <td>{{ $expense->paid_to ?? '-' }}</td>
                    <td>
                        <a href="{{ route('expenses.show', $expense) }}" class="icon-btn"><i class="bi bi-eye-fill"></i></a>
                        <a href="{{ route('expenses.edit', $expense) }}" class="icon-btn"><i class="bi bi-pencil-fill"></i></a>
                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="icon-btn border-0 bg-transparent"><i class="bi bi-trash-fill"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No expenses found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $expenses->links() }}</div>
    </div>

    <div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Expense</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Date</label>
                                <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', now()->toDateString()) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Category</label>
                                <select name="category" class="form-select" required>
                                    @foreach(\App\Models\Expense::CATEGORIES as $key => $label)
                                        <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description') }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Amount</label>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Payment Method</label>
                                <select name="payment_method" class="form-select" required>
                                    @foreach(\App\Models\Expense::PAYMENT_METHODS as $key => $label)
                                        <option value="{{ $key }}" @selected(old('payment_method') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Paid To (optional)</label>
                                <input type="text" name="paid_to" class="form-control" value="{{ old('paid_to') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Reference # (optional)</label>
                                <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Attachment (optional)</label>
                            <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy">Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('addExpenseModal')).show();
        });
    @endif
</script>
@endsection
