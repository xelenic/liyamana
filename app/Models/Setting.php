<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value, ?string $group = null): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget('settings.' . $key);

        return $setting;
    }

    /**
     * Get all settings as key-value array, optionally filtered by group.
     */
    public static function getAll(?string $group = null): array
    {
        $query = static::query();
        if ($group) {
            $query->where('group', $group);
        }
        return $query->pluck('value', 'key')->toArray();
    }
}
