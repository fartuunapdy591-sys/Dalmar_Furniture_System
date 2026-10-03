@extends('layouts.app')
@section('title', 'Expense Details')
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">{{ $expense->expense_number }}</div>
            <div class="page-subtitle">Home / Expenses / {{ $expense->expense_number }}</div>
        </div>
        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-light"><i class="bi bi-pencil-fill me-1"></i> Edit</a>
    </div>

    <div class="card-panel mx-auto" style="max-width: 520px;">
        <table class="table-dalmar">
            <tr><td class="text-muted">Expense #</td><td class="fw-semibold text-end">{{ $expense->expense_number }}</td></tr>
            <tr><td class="text-muted">Date</td><td class="text-end">{{ $expense->expense_date->format('M d, Y') }}</td></tr>
            <tr><td class="text-muted">Category</td><td class="text-end">{{ $expense->category_label }}</td></tr>
            <tr><td class="text-muted">Description</td><td class="text-end">{{ $expense->description }}</td></tr>
            <tr><td class="text-muted">Amount</td><td class="text-end fw-bold">${{ number_format($expense->amount, 2) }}</td></tr>
            <tr><td class="text-muted">Payment Method</td><td class="text-end"><span class="badge-method method-{{ $expense->payment_method }}">{{ \App\Models\Expense::PAYMENT_METHODS[$expense->payment_method] ?? ucfirst($expense->payment_method) }}</span></td></tr>
            @if($expense->paid_to)
                <tr><td class="text-muted">Paid To</td><td class="text-end">{{ $expense->paid_to }}</td></tr>
            @endif
            @if($expense->reference_number)
                <tr><td class="text-muted">Reference #</td><td class="text-end">{{ $expense->reference_number }}</td></tr>
            @endif
            @if($expense->createdBy)
                <tr><td class="text-muted">Recorded By</td><td class="text-end">{{ $expense->createdBy->name }}</td></tr>
            @endif
        </table>

        @if($expense->notes)
            <hr>
            <div class="text-muted small">{{ $expense->notes }}</div>
        @endif

        @if($expense->attachment_url)
            <hr>
            <a href="{{ $expense->attachment_url }}" target="_blank" class="btn btn-light btn-sm w-100">
                <i class="bi bi-paperclip me-1"></i> View Attachment
            </a>
        @endif
    </div>
@endsection
