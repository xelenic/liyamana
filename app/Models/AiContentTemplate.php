<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class AiContentTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'prompt',
        'fields',
        'image_path',
        'editor_json',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'fields' => 'array',
        'editor_json' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
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
     * Scope to get only active templates
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

    public function generations(): HasMany
    {
        return $this->hasMany(AiContentGeneration::class, 'ai_content_template_id');
    }

    /**
     * How many pages the multi-page editor should use after placeholders are substituted.
     * Uses the editor layout page count and/or a "N pages" phrase in the prompt (max 20).
     */
    public function resolvePageCount(string $resolvedPrompt): int
    {
        $fromEditor = 0;
        $pages = $this->editor_json['pages'] ?? null;
        if (is_array($pages)) {
            $fromEditor = count($pages);
        }

        $fromPrompt = 0;
        if (preg_match('/\b(\d{1,2})\s*pages?\b/i', $resolvedPrompt, $m)) {
            $fromPrompt = (int) $m[1];
        }

        $n = max($fromEditor, $fromPrompt);
        if ($n < 1) {
            $n = 1;
        }

        return min(20, $n);
    }
}
