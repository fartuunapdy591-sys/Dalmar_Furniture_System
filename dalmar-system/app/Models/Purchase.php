<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use Auditable;

    protected $fillable = [
        'purchase_number', 'supplier_id', 'user_id', 'purchase_date',
        'subtotal', 'discount', 'tax', 'total', 'status', 'notes',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceDueAttribute()
    {
        return max(0, round($this->total - $this->paid_amount, 2));
    }

    public function refreshStatus(): void
    {
        $paid = $this->paid_amount;

        if ($paid <= 0) {
            $status = 'unpaid';
        } elseif ($paid >= $this->total) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        if ($status !== $this->status) {
            $this->update(['status' => $status]);
        }
    }
}
