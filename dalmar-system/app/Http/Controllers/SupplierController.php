<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::withCount('purchases')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('company_name', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:active,inactive'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['status'] = $data['status'] ?? 'active';
        $data['opening_balance'] = $data['opening_balance'] ?? 0;

        $supplier = Supplier::create($data);

        if ($request->wantsJson()) {
            return response()->json(['id' => $supplier->id, 'name' => $supplier->name]);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier has been added successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchases' => fn ($q) => $q->latest('purchase_date'), 'payments' => fn ($q) => $q->latest('paid_at')]);

        $statement = $this->buildStatement($supplier);

        return view('suppliers.show', compact('supplier', 'statement'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('success', 'Supplier has been updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'This supplier has purchase history and cannot be deleted.');
        }

        $supplier->delete();

        return back()->with('success', 'Supplier has been deleted successfully.');
    }

    /**
     * Builds a running-balance ledger (purchases as debits, payments as credits)
     * starting from the supplier's opening balance.
     */
    private function buildStatement(Supplier $supplier): array
    {
        $entries = collect();

        foreach ($supplier->purchases as $purchase) {
            $entries->push([
                'date' => $purchase->purchase_date,
                'type' => 'Purchase',
                'reference' => $purchase->purchase_number,
                'debit' => (float) $purchase->total,
                'credit' => 0,
                'link' => route('purchases.show', $purchase),
            ]);
        }

        foreach ($supplier->payments as $payment) {
            $entries->push([
                'date' => $payment->paid_at->toDateString(),
                'type' => 'Payment',
                'reference' => $payment->payment_number,
                'debit' => 0,
                'credit' => (float) $payment->amount,
                'link' => null,
            ]);
        }

        $entries = $entries->sortBy('date')->values();

        $running = (float) $supplier->opening_balance;

        return $entries->map(function ($entry) use (&$running) {
            $running += $entry['debit'] - $entry['credit'];
            $entry['balance'] = round($running, 2);

            return $entry;
        })->all();
    }
}
