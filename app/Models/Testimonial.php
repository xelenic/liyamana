<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'role',
        'content',
        'rating',
        'avatar_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'rating' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get initials from name (e.g. "Sarah Mitchell" -> "SM").
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name), 2);
        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
        }

        return strtoupper(mb_substr($this->name, 0, 2));
    }

    /**
     * Gradient class for avatar (cycle through a few options by id).
     */
    public function getAvatarGradientStyle(): string
    {
        $gradients = [
            'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)',
            'linear-gradient(135deg, #10b981 0%, #34d399 100%)',
            'linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%)',
        ];
        $index = ($this->id - 1) % count($gradients);

        return $gradients[$index];
    }
}
