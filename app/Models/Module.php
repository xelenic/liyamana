<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'version',
        'enabled',
        'path',
        'manifest',
        'installed_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'manifest' => 'array',
        'installed_at' => 'datetime',
    ];
}
