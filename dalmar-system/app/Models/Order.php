<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'order_number', 'customer_id', 'user_id', 'order_date',
        'total_amount', 'subtotal', 'discount_type', 'discount_value', 'discount_amount', 'discount_by',
        'is_credit_sale', 'payment_method', 'status', 'source', 'shipping_address', 'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'is_credit_sale' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function discountBy()
    {
        return $this->belongsTo(User::class, 'discount_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }

    public function getDiscountLabelAttribute(): ?string
    {
        if ($this->discount_amount <= 0) {
            return null;
        }

        return $this->discount_type === 'percentage'
            ? rtrim(rtrim(number_format($this->discount_value, 2), '0'), '.').'%'
            : '$'.number_format($this->discount_value, 2);
    }
}
