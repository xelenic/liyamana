<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GlobalImage extends Model
{
    protected $fillable = ['category_id', 'path', 'name', 'sort_order'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(GlobalImageCategory::class, 'category_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
