<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'faqs',
        'price',
        'sku',
        'image',
        'is_active',
        'sort_order',
        'stock_quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'stock_quantity' => 'integer',
        'faqs' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Templates this product is assigned to.
     */
    public function templates()
    {
        return $this->belongsToMany(\App\Models\Template::class, 'product_template')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    /**
     * Full URL for the product image (storage path or external URL).
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }
        if (Storage::disk('public')->exists($this->image)) {
            return Storage::disk('public')->url($this->image);
        }

        return null;
    }
}
