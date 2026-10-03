@extends('layouts.app')
@section('title', 'Edit Expense')
@section('content')
    <div class="page-title mb-3">Edit Expense</div>
    <div class="card-panel" style="max-width: 600px;">
        <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label small fw-semibold">Date</label>
                <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', $expense->expense_date->toDateString()) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Category</label>
                <select name="category" class="form-select" required>
                    @foreach(\App\Models\Expense::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" @selected(old('category', $expense->category) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Description</label>
                <input type="text" name="description" class="form-control" value="{{ old('description', $expense->description) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Amount</label>
                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount', $expense->amount) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Payment Method</label>
                <select name="payment_method" class="form-select" required>
                    @foreach(\App\Models\Expense::PAYMENT_METHODS as $key => $label)
                        <option value="{{ $key }}" @selected(old('payment_method', $expense->payment_method) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Paid To (optional)</label>
                <input type="text" name="paid_to" class="form-control" value="{{ old('paid_to', $expense->paid_to) }}">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Reference # (optional)</label>
                <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number', $expense->reference_number) }}">
            </div>
            @if($expense->attachment_url)
                <div class="mb-3">
                    <label class="form-label small fw-semibold d-block">Current Attachment</label>
                    <a href="{{ $expense->attachment_url }}" target="_blank">{{ $expense->attachment }}</a>
                </div>
            @endif
            <div class="mb-3">
                <label class="form-label small fw-semibold">Replace Attachment (optional)</label>
                <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Notes (optional)</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $expense->notes) }}</textarea>
            </div>
            <button type="submit" class="btn btn-navy">Update Expense</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
@endsection
