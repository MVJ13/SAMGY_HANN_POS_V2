<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_name',
        'type',
        'quantity',
        'unit_cost',      // cost per unit locked in at time of movement
        'previous_stock',
        'new_stock',
        'notes',
    ];

    protected $casts = [
        'quantity'       => 'decimal:2',
        'unit_cost'      => 'decimal:2',
        'previous_stock' => 'decimal:2',
        'new_stock'      => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Total cost value of this movement (quantity × unit_cost).
     * Useful for COGS calculations without re-fetching product cost.
     */
    public function getTotalCostAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->unit_cost, 2);
    }
}
