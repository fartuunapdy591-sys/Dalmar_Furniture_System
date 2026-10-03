<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'status', 'avatar',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('uploads/avatars/'.$this->avatar) : null;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canAccessSales(): bool
    {
        return in_array($this->role, ['admin', 'sales_manager', 'salesperson']);
    }

    public function canAccessInventory(): bool
    {
        return in_array($this->role, ['admin', 'sales_manager']);
    }

    public function canAccessProcurement(): bool
    {
        return $this->role === 'admin';
    }

    public function canAccessReports(): bool
    {
        return in_array($this->role, ['admin', 'sales_manager', 'accountant']);
    }

    public function canManageDiscounts(): bool
    {
        return in_array($this->role, ['admin', 'sales_manager']);
    }

    public function canCreateCreditSales(): bool
    {
        return in_array($this->role, ['admin', 'sales_manager']);
    }
}
