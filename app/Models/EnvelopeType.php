<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvelopeType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price_per_letter',
        'description',
        'is_active',
        'sort_order',
        'stock_quantity',
    ];

    protected $casts = [
        'price_per_letter' => 'decimal:4',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'stock_quantity' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }
}
