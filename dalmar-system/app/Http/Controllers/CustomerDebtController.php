<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerDebtController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()
            ->when(! $request->boolean('all'), fn ($q) => $q->where(function ($q2) {
                $q2->whereRaw('opening_balance > 0')
                    ->orWhereHas('receipts', fn ($i) => $i->whereIn('status', ['unpaid', 'partial']));
            }))
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%"))
            ->orderBy('name');

        $customers = $query->paginate(15)->withQueryString();

        $customers->getCollection()->transform(function (Customer $customer) {
            $customer->computed_balance = $customer->balance_due;
            $customer->computed_last_transaction = $customer->last_transaction_at;
            $customer->computed_due_date = $customer->next_due_date;
            $customer->computed_overdue = $customer->has_overdue_debt;

            return $customer;
        });

        $totalOutstanding = Customer::get()->sum('balance_due');

        return view('customer-debts.index', compact('customers', 'totalOutstanding'));
    }

    public function pay(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,sahal,e_dahab,mycash,card'],
            'notes' => ['nullable', 'string', 'max:255'],
            'allow_overpayment' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $previousBalance = $customer->balance_due;
        $amount = (float) $data['amount'];

        if ($amount > $previousBalance) {
            if (! ($user->isAdmin() && $request->boolean('allow_overpayment'))) {
                return back()->withInput()->withErrors([
                    'amount' => 'Amount exceeds the remaining debt of $'.number_format($previousBalance, 2).'. Only an admin can allow an overpayment.',
                ]);
            }
        }

        DB::transaction(function () use ($customer, $amount, $data, $user) {
            $remaining = $amount;

            $openReceipts = $customer->receipts()
                ->whereIn('status', ['unpaid', 'partial'])
                ->oldest('issued_date')
                ->get();

            foreach ($openReceipts as $receipt) {
                if ($remaining <= 0) {
                    break;
                }

                $due = $receipt->balance_due;
                if ($due <= 0) {
                    continue;
                }

                $pay = min($remaining, $due);

                Payment::create([
                    'order_id' => $receipt->order_id,
                    'receipt_id' => $receipt->id,
                    'amount' => $pay,
                    'method' => $data['method'],
                    'status' => 'paid',
                    'receipt_number' => 'RCPT-'.now()->format('Y').'-'.str_pad((Payment::max('id') + 1), 3, '0', STR_PAD_LEFT),
                    'paid_at' => now(),
                    'notes' => $data['notes'] ?? 'Debt payment',
                ]);

                $receipt->payments()->where('status', 'pending')->delete();
                $receipt->refreshStatus();
                $remaining = round($remaining - $pay, 2);
            }

            if ($remaining > 0 && $customer->opening_balance > 0) {
                $customer->decrement('opening_balance', min($remaining, $customer->opening_balance));
            }
        });

        $customer->refresh();
        $newBalance = $customer->balance_due;
        $status = $newBalance <= 0 ? 'Paid / Cleared' : 'Partial Payment';

        ActivityLog::record('debt_payment', $customer, "{$user->name} recorded a debt payment of \${$amount} for {$customer->name}. Previous balance: \${$previousBalance}, New balance: \${$newBalance} ({$status}).", [
            'amount' => $amount,
            'method' => $data['method'],
            'previous_balance' => $previousBalance,
            'new_balance' => $newBalance,
        ]);

        return back()->with('success', "Payment of \$".number_format($amount, 2)." recorded. Status: {$status}. New balance: \$".number_format($newBalance, 2).".");
    }
}
