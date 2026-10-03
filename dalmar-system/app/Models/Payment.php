<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use Auditable;

    protected $fillable = [
        'order_id', 'receipt_id', 'amount', 'method', 'sender_phone', 'reference',
        'receipt_number', 'status', 'paid_at', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public const METHODS = [
        'cash' => 'Cash',
        'sahal' => 'Sahal',
        'e_dahab' => 'e-Dahab',
        'mycash' => 'MyCash',
        'card' => 'Card',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? ucfirst($this->method);
    }
}
