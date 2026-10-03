<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use Auditable;

    protected $fillable = [
        'expense_number', 'expense_date', 'category', 'description', 'amount',
        'payment_method', 'paid_to', 'reference_number', 'notes', 'attachment',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public const CATEGORIES = [
        'rent' => 'Rent',
        'electricity' => 'Electricity',
        'water' => 'Water',
        'internet' => 'Internet',
        'transport' => 'Transport',
        'salaries' => 'Salaries',
        'maintenance' => 'Maintenance',
        'marketing' => 'Marketing',
        'office_supplies' => 'Office Supplies',
        'fuel' => 'Fuel',
        'tax' => 'Tax',
        'other' => 'Other',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'bank' => 'Bank Transfer',
        'mobile_money' => 'Mobile Money',
        'other' => 'Other',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment ? asset('uploads/expenses/'.$this->attachment) : null;
    }
}
