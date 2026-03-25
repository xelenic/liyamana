<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DesignFont extends Model
{
    protected $fillable = [
        'name',
        'stored_path',
        'original_filename',
        'extension',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * @return array{id: string, path: string, url: string, name: string, filename: string, extension: string, source: string, deletable: bool}
     */
    public function toLibraryPayload(): array
    {
        return [
            'id' => 'design-font:'.$this->id,
            'path' => $this->stored_path,
            'url' => Storage::disk('public')->url($this->stored_path),
            'name' => $this->name,
            'filename' => $this->original_filename,
            'extension' => strtolower($this->extension),
            'source' => 'admin',
            'deletable' => false,
        ];
    }

    /**
     * @return array{name: string, path: string|null, url: string|null, filename: string|null, extension: string|null}
     */
    public function toExportPayload(): array
    {
        return [
            'name' => $this->name,
            'path' => $this->stored_path,
            'url' => Storage::disk('public')->exists($this->stored_path)
                ? Storage::disk('public')->url($this->stored_path)
                : null,
            'filename' => $this->original_filename,
            'extension' => strtolower($this->extension),
        ];
    }
}
