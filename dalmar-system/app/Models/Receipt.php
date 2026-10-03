<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use Auditable;

    protected $fillable = [
        'receipt_number', 'order_id', 'customer_id', 'amount', 'issued_date', 'due_date', 'status', 'notes',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->where('status', 'paid')->sum('amount');
    }

    public function getBalanceDueAttribute()
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    public function getIsCreditSaleAttribute(): bool
    {
        return (bool) ($this->order?->is_credit_sale);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->isPast();
    }

    public function refreshStatus(): void
    {
        $paid = $this->paid_amount;

        if ($paid <= 0) {
            $status = 'unpaid';
        } elseif ($paid >= $this->amount) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        if ($status !== $this->status) {
            $this->update(['status' => $status]);
        }
    }
}
