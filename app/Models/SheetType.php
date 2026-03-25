<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SheetType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'multiplier',
        'price_per_sheet',
        'description',
        'image_path',
        'video_path',
        'is_active',
        'sort_order',
        'stock_quantity',
    ];

    protected $casts = [
        'multiplier' => 'decimal:2',
        'price_per_sheet' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'stock_quantity' => 'integer',
    ];

    /**
     * Get image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }

        return null;
    }

    /**
     * Get video URL
     */
    public function getVideoUrlAttribute(): ?string
    {
        if ($this->video_path && Storage::disk('public')->exists($this->video_path)) {
            return Storage::disk('public')->url($this->video_path);
        }

        return null;
    }

    /**
     * Scope to get only active sheet types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Sheet types with on-hand stock (for customer checkout / send-letter selectors).
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }
}
