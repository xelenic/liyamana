<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThumbnailPrompt extends Model
{
    protected $fillable = ['name', 'prompt', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
