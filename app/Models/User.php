<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Role helpers
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    /**
     * Returns which tab keys this role can access.
     */
    public function allowedTabs(): array
    {
        return match ($this->role) {
            'super_admin' => ['orders', 'receipts', 'inventory', 'statistics'],
            'admin'       => ['inventory', 'statistics'],
            'cashier'     => ['orders', 'receipts'],
            default       => [],
        };
    }

    public function defaultTab(): string
    {
        return match ($this->role) {
            'super_admin' => 'orders',
            'admin'       => 'inventory',
            'cashier'     => 'orders',
            default       => 'orders',
        };
    }
}
