<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntroTourStep extends Model
{
    protected $fillable = [
        'tour_slug',
        'sort_order',
        'element_selector',
        'title',
        'intro_text',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeForTour($query, string $slug)
    {
        return $query->where('tour_slug', $slug)->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
