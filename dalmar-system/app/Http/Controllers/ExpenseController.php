<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::with('createdBy')
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->when($request->payment_method, fn ($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->created_by, fn ($q) => $q->where('created_by', $request->created_by))
            ->when($request->search, fn ($q) => $q->where('description', 'like', "%{$request->search}%")
                ->orWhere('paid_to', 'like', "%{$request->search}%"))
            ->when($request->from, fn ($q) => $q->whereDate('expense_date', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('expense_date', '<=', $request->to))
            ->latest('expense_date')
            ->paginate(10)
            ->withQueryString();

        $today = Expense::whereDate('expense_date', today())->sum('amount');
        $thisWeek = Expense::whereBetween('expense_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount');
        $thisMonth = Expense::whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount');
        $total = Expense::sum('amount');

        $byCategory = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('expenses.index', compact(
            'expenses', 'today', 'thisWeek', 'thisMonth', 'total', 'byCategory'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_date' => ['required', 'date'],
            'category' => ['required', 'in:'.implode(',', array_keys(Expense::CATEGORIES))],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:'.implode(',', array_keys(Expense::PAYMENT_METHODS))],
            'paid_to' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $this->storeAttachment($request->file('attachment'));
        }

        $data['expense_number'] = 'EXP-'.now()->format('Y').'-'.str_pad((Expense::max('id') + 1), 3, '0', STR_PAD_LEFT);

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense has been recorded successfully.');
    }

    public function show(Expense $expense)
    {
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'expense_date' => ['required', 'date'],
            'category' => ['required', 'in:'.implode(',', array_keys(Expense::CATEGORIES))],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:'.implode(',', array_keys(Expense::PAYMENT_METHODS))],
            'paid_to' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $this->storeAttachment($request->file('attachment'));
        }

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Expense has been updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->attachment && file_exists(public_path('uploads/expenses/'.$expense->attachment))) {
            unlink(public_path('uploads/expenses/'.$expense->attachment));
        }

        $expense->delete();

        return back()->with('success', 'Expense has been deleted successfully.');
    }

    private function storeAttachment($file): string
    {
        $filename = uniqid('expense_').'.'.$file->getClientOriginalExtension();
        $file->move(public_path('uploads/expenses'), $filename);

        return $filename;
    }
}
