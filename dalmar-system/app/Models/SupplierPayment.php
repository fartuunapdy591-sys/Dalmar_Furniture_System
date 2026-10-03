<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    use Auditable;

    protected $fillable = [
        'payment_number', 'supplier_id', 'purchase_id', 'amount', 'method',
        'reference', 'notes', 'paid_at', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public const METHODS = [
        'cash' => 'Cash',
        'bank' => 'Bank Transfer',
        'mobile_money' => 'Mobile Money',
        'other' => 'Other',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? ucfirst($this->method);
    }
}
