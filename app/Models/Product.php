<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'stock',
        'unit',
        'cost',
        'reorder_level',
        'is_extra',
        'selling_price',
        'available_from',
        'available_until',
    ];

    protected $casts = [
        'stock'          => 'decimal:2',
        'cost'           => 'decimal:2',
        'reorder_level'  => 'decimal:2',
        'is_extra'       => 'boolean',
        'selling_price'  => 'decimal:2',
        'available_from' => 'date',
        'available_until'=> 'date',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        if ((float) $this->reorder_level <= 0) {
            return (float) $this->stock <= 0;
        }
        return (float) $this->stock <= (float) $this->reorder_level;
    }

    /**
     * Whether this product is available for ordering today (PHT).
     * Null on either end = no restriction on that end.
     */
    public function getIsAvailableNowAttribute(): bool
    {
        $today = now()->startOfDay();
        if ($this->available_from  && $today->lt($this->available_from->copy()->startOfDay()))  return false;
        if ($this->available_until && $today->gt($this->available_until->copy()->startOfDay())) return false;
        return true;
    }

    public function scopeExtras($query)
    {
        return $query->where('is_extra', true);
    }
}
