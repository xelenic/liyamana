<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GlobalImageCategory extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(GlobalImage::class, 'category_id')->orderBy('sort_order')->orderBy('name');
    }

    public function imagesCount(): int
    {
        return $this->images()->count();
    }
}
