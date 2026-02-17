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
        'total',
        'payment',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'packages'        => 'array',
        'addons'          => 'array',
        'extra_items'     => 'array',
        'subtotal'        => 'decimal:2',
        'discount_percent'=> 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total'           => 'decimal:2',
        'completed_at'    => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function getReceiptNumberAttribute(): string
    {
        return str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getPackageSummaryAttribute(): string
    {
        if (empty($this->packages)) return '';
        return collect($this->packages)
            ->map(fn($p) => "{$p['people']}x {$p['name']}")
            ->join(', ');
    }

    public function getAddonsSummaryAttribute(): string
    {
        if (empty($this->addons)) return '';
        return collect($this->addons)
            ->map(fn($a) => "{$a['people']}x {$a['name']}")
            ->join(', ');
    }

    public function getExtraItemsSummaryAttribute(): string
    {
        if (empty($this->extra_items)) return '';
        return collect($this->extra_items)
            ->map(fn($e) => "{$e['qty']}x {$e['name']}")
            ->join(', ');
    }
}
