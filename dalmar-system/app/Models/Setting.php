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

    /** Phone numbers may be separated by comma, slash or semicolon. */
    public function getPhonesAttribute(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[,\/;]+/', (string) $this->phone))));
    }

    /** First phone in international digits (Somalia 252 assumed for local numbers) for wa.me links. */
    public function getWhatsappNumberAttribute(): ?string
    {
        $first = $this->phones[0] ?? null;
        if (! $first) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $first);
        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            return '252'.substr($digits, 1);
        }

        return $digits ?: null;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('uploads/settings/'.$this->logo) : null;
    }
}
