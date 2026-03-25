<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SessionRecording extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'started_at',
        'ended_at',
        'last_event_at',
        'landing_path',
        'user_agent',
        'viewport_w',
        'viewport_h',
        'byte_size',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'last_event_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventsRelativePath(): string
    {
        return 'session-recordings/'.$this->uuid.'.jsonl';
    }

    public function absoluteEventsPath(): string
    {
        return Storage::disk('local')->path($this->eventsRelativePath());
    }

    public function deleteEventsFile(): void
    {
        $path = $this->eventsRelativePath();
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    public static function ensureEventsDirectory(): void
    {
        $dir = Storage::disk('local')->path('session-recordings');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    protected static function booted(): void
    {
        static::deleting(function (SessionRecording $recording): void {
            $recording->deleteEventsFile();
        });
    }
}
