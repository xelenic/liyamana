<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserHeatmapClick extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'path',
        'x_pct',
        'y_pct',
        'viewport_w',
        'viewport_h',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'x_pct' => 'float',
            'y_pct' => 'float',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
