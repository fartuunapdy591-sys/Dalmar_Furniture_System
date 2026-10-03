<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name', 'sku', 'image', 'category_id', 'supplier_id', 'price', 'cost',
        'stock', 'min_stock', 'unit', 'status', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Rolls the newly purchased quantity/cost into a weighted average cost,
     * matching how the business actually prices stock bought at different times.
     */
    public function applyPurchase(int $qty, float $unitCost): void
    {
        $existingValue = $this->stock * $this->cost;
        $incomingValue = $qty * $unitCost;
        $newStock = $this->stock + $qty;

        $newCost = $newStock > 0 ? ($existingValue + $incomingValue) / $newStock : $unitCost;

        $this->update([
            'stock' => $newStock,
            'cost' => round($newCost, 2),
        ]);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image || ! file_exists(public_path('uploads/products/'.$this->image))) {
            return null;
        }

        return asset('uploads/products/'.$this->image);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Derives a friendly stock label the same way the dashboard mockups show it.
    public function getStockLabelAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'Out of Stock';
        }

        return $this->stock <= $this->min_stock ? 'Low Stock' : 'In Stock';
    }
}
