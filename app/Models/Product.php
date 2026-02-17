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
    ];

    protected $casts = [
        'stock'         => 'decimal:2',
        'cost'          => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'is_extra'      => 'boolean',
        'selling_price' => 'decimal:2',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->reorder_level;
    }

    public function scopeExtras($query)
    {
        return $query->where('is_extra', true);
    }
}
