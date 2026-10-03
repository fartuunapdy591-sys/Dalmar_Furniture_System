<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StockMovement extends Model
{
    protected $fillable = ['product_id', 'supplier_id', 'type', 'quantity', 'unit_cost', 'reference_type', 'reference_id', 'user_id', 'notes'];

    protected $casts = [
        'unit_cost' => 'decimal:2',
    ];

    public const TYPES = [
        'purchase' => 'Purchase (Stock In)',
        'sale' => 'Sale (Stock Out)',
        'adjustment' => 'Manual Adjustment',
        'return_in' => 'Return from Customer',
        'return_out' => 'Return to Supplier',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public static function log(Product $product, string $type, int $quantity, ?Model $reference = null, ?string $notes = null): self
    {
        return static::create([
            'product_id' => $product->id,
            'type' => $type,
            'quantity' => $quantity,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'user_id' => Auth::id(),
            'notes' => $notes,
        ]);
    }
}
