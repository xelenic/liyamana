<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoPage extends Model
{
    protected $fillable = [
        'page_key',
        'label',
        'path_hint',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'twitter_card',
        'canonical_url',
        'robots',
        'focus_keyword',
    ];

    public static function registry(): array
    {
        return config('seo.pages', []);
    }

    public static function syncRegistry(): void
    {
        foreach (static::registry() as $key => $meta) {
            static::query()->updateOrCreate(
                ['page_key' => $key],
                [
                    'label' => $meta['label'] ?? $key,
                    'path_hint' => $meta['path'] ?? null,
                ]
            );
        }
    }
}
