<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlipBook extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'slug',
        'status',
        'settings',
        'pages',
        'cover_image',
        'is_public',
    ];

    protected $casts = [
        'settings' => 'array',
        'pages' => 'array',
        'is_public' => 'boolean',
    ];

    /**
     * Get the user that owns the flip book.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
