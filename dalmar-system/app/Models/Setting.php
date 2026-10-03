<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_name', 'logo', 'email', 'phone', 'address',
        'currency', 'timezone', 'email_notifications', 'low_stock_alerts',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'low_stock_alerts' => 'boolean',
    ];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('uploads/settings/'.$this->logo) : null;
    }
}
