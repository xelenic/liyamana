<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiContentGeneration extends Model
{
    protected $fillable = [
        'user_id',
        'ai_content_template_id',
        'design_session_id',
        'name',
        'pages',
        'is_multi_page',
        'page_count',
        'type',
        'thumbnail',
    ];

    protected $casts = [
        'pages' => 'array',
        'is_multi_page' => 'boolean',
        'page_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiContentTemplate(): BelongsTo
    {
        return $this->belongsTo(AiContentTemplate::class, 'ai_content_template_id');
    }
}
