<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_people',
        'packages',
        'addons',
        'extra_items',
        'subtotal',
        'discount_percent',
        'discount_amount',
        'discount_persons',
        'total',
        'payment',
        'amount_received',
        'change_given',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'packages'         => 'array',
        'addons'           => 'array',
        'extra_items'      => 'array',
        'subtotal'         => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'discount_persons' => 'array',
        'total'            => 'decimal:2',
        'amount_received'  => 'decimal:2',
        'change_given'     => 'decimal:2',
        'completed_at'     => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function getReceiptNumberAttribute(): string
    {
        return str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getPackageSummaryAttribute(): string
    {
        if (empty($this->packages)) return '—';
        return collect($this->packages)
            ->map(fn($p) => "{$p['people']}× {$p['name']}")
            ->join(', ');
    }

    public function getAddonsSummaryAttribute(): string
    {
        if (empty($this->addons)) return '';
        return collect($this->addons)
            ->map(fn($a) => "{$a['people']}× {$a['name']}")
            ->join(', ');
    }

    public function getExtraItemsSummaryAttribute(): string
    {
        if (empty($this->extra_items)) return '';
        return collect($this->extra_items)
            ->map(fn($e) => "{$e['qty']}× {$e['name']}")
            ->join(', ');
    }
}
