<?php

namespace Modules\NanoBananaModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class NanoBananaTemplate extends Model
{
    protected $table = 'nano_banana_templates';

    protected $fillable = [
        'name',
        'prompt',
        'image_path',
        'description',
        'defined_fields',
        'upload_image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'defined_fields' => 'array',
        'upload_image' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }
        return Storage::disk('public')->exists($this->image_path)
            ? url(Storage::disk('public')->url($this->image_path))
            : null;
    }
}
