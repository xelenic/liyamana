<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    protected $fillable = [
        'name',
        'description',
        'short_description',
        'category',
        'tags',
        'type',
        'price',
        'licence',
        'pages',
        'page_count',
        'thumbnail_path',
        'images',
        'variables',
        'fonts',
        'is_active',
        'is_public',
        'is_product',
        'stock_enabled',
        'stock_qty',
        'selling_price',
        'cost',
        'product_description',
        'disable_sheet_selection',
        'disable_material_selection',
        'disable_envelope_option',
        'is_featured',
        'featured_sort_order',
        'created_by',
    ];

    protected $casts = [
        'pages' => 'array',
        'tags' => 'array',
        'variables' => 'array',
        'fonts' => 'array',
        'images' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'is_product' => 'boolean',
        'stock_enabled' => 'boolean',
        'stock_qty' => 'integer',
        'selling_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'disable_sheet_selection' => 'boolean',
        'disable_material_selection' => 'boolean',
        'disable_envelope_option' => 'boolean',
        'is_featured' => 'boolean',
        'featured_sort_order' => 'integer',
        'page_count' => 'integer',
        'price' => 'decimal:2',
    ];

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->orderBy('featured_sort_order')
            ->orderBy('id');
    }

    /**
     * Get the user who created this template
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path && \Storage::disk('public')->exists($this->thumbnail_path)) {
            return \Storage::disk('public')->url($this->thumbnail_path);
        }

        return null;
    }

    /**
     * Get template images URLs
     */
    public function getImagesUrlsAttribute()
    {
        if (! $this->images || ! is_array($this->images)) {
            return [];
        }

        $urls = [];
        foreach ($this->images as $imagePath) {
            if ($imagePath && \Storage::disk('public')->exists($imagePath)) {
                $urls[] = \Storage::disk('public')->url($imagePath);
            }
        }

        return $urls;
    }

    /**
     * Products assigned to this template (many-to-many).
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_template')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Get orders that used this template (use count).
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get reviews for this template
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(TemplateReview::class)->where('is_approved', true);
    }

    /**
     * Get comments for this template
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TemplateComment::class)->where('is_approved', true)->whereNull('parent_id');
    }

    /**
     * Get average rating
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }
}
