<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraProduct extends Model
{
    protected $fillable = ['name', 'category', 'price', 'active', 'sort_order'];

    protected $casts = [
        'price'  => 'decimal:2',
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
