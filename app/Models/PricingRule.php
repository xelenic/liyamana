<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    protected $fillable = [
        'name',
        'sheet_type_slug',
        'min_quantity',
        'max_quantity',
        'discount_percent',
        'applies_to_design',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'discount_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('sort_order')->orderByDesc('min_quantity');
    }
}
