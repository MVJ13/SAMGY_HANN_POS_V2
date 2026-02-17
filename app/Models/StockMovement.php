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
        'previous_stock',
        'new_stock',
        'notes',
    ];

    protected $casts = [
        'quantity'       => 'decimal:2',
        'previous_stock' => 'decimal:2',
        'new_stock'      => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
