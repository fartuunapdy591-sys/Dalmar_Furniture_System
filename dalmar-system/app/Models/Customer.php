<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'opening_balance', 'status',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function getTotalSpentAttribute()
    {
        return $this->orders()->sum('total_amount');
    }

    public function getTotalPaidAttribute()
    {
        return Payment::where('status', 'paid')
            ->whereHas('receipt', fn ($q) => $q->where('customer_id', $this->id))
            ->sum('amount');
    }

    public function getBalanceDueAttribute()
    {
        return round($this->opening_balance + $this->receipts()->sum('amount') - $this->total_paid, 2);
    }

    public function getTotalCreditSalesAttribute()
    {
        return $this->receipts()->whereHas('order', fn ($q) => $q->where('is_credit_sale', true))->sum('amount');
    }

    public function getOpenReceiptsAttribute()
    {
        return $this->receipts()->whereIn('status', ['unpaid', 'partial'])->oldest('issued_date')->get();
    }

    public function getNextDueDateAttribute()
    {
        return $this->receipts()
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->value('due_date');
    }

    public function getHasOverdueDebtAttribute(): bool
    {
        return $this->receipts()
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->exists();
    }

    public function getLastTransactionAtAttribute()
    {
        $lastReceipt = $this->receipts()->max('issued_date');
        $lastPayment = Payment::whereHas('receipt', fn ($q) => $q->where('customer_id', $this->id))->max('paid_at');

        return collect([$lastReceipt, $lastPayment])->filter()->max();
    }
}
