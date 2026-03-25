<?php

namespace App\Http\Controllers;

use App\Models\SessionRecording;
use App\Support\RrwebEventSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UserSessionRecordingController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        if (! $this->ingestAllowed()) {
            return response()->json(['ok' => false, 'message' => 'disabled'], 403);
        }

        $validated = $request->validate([
            'landing_path' => ['nullable', 'string', 'max:2048'],
            'viewport_w' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'viewport_h' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'user_agent' => ['nullable', 'string', 'max:512'],
        ]);

        $uuid = Str::uuid()->toString();
        SessionRecording::ensureEventsDirectory();

        $recording = SessionRecording::create([
            'uuid' => $uuid,
            'user_id' => Auth::id(),
            'started_at' => now(),
            'landing_path' => $validated['landing_path'] ?? null,
            'viewport_w' => $validated['viewport_w'] ?? null,
            'viewport_h' => $validated['viewport_h'] ?? null,
            'user_agent' => $validated['user_agent'] ?? null,
            'byte_size' => 0,
        ]);

        File::put($recording->absoluteEventsPath(), '');

        return response()->json(['ok' => true, 'id' => $recording->uuid]);
    }

    public function append(Request $request): JsonResponse
    {
        if (! $this->ingestAllowed()) {
            return response()->json(['ok' => false, 'message' => 'disabled'], 403);
        }

        $validated = $request->validate([
            'id' => ['required', 'uuid'],
            'events' => ['required', 'array', 'max:120'],
            'events.*' => ['required'],
        ]);

        $recording = SessionRecording::query()
            ->where('uuid', $validated['id'])
            ->where('user_id', Auth::id())
            ->first();

        if (! $recording) {
            return response()->json(['ok' => false, 'message' => 'not_found'], 404);
        }

        if ($recording->ended_at !== null) {
            return response()->json(['ok' => false, 'message' => 'closed'], 410);
        }

        $max = session_recording_max_bytes_per_session();
        $jsonDepth = session_recording_json_max_depth();
        $chunk = '';
        foreach ($validated['events'] as $event) {
            if (! is_array($event)) {
                return response()->json(['ok' => false, 'message' => 'invalid_event'], 422);
            }
            $event = RrwebEventSanitizer::sanitizeSingleEvent($event);
            $line = json_encode($event, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES, $jsonDepth);
            if ($line === false) {
                return response()->json(['ok' => false, 'message' => 'encode_error'], 422);
            }
            $chunk .= $line."\n";
        }

        $add = strlen($chunk);
        if ($recording->byte_size + $add > $max) {
            return response()->json(['ok' => false, 'message' => 'max_size'], 413);
        }

        File::append($recording->absoluteEventsPath(), $chunk);
        $recording->byte_size += $add;
        $recording->last_event_at = now();
        $recording->save();

        return response()->json(['ok' => true]);
    }

    public function finish(Request $request): JsonResponse
    {
        if (! $this->ingestAllowed()) {
            return response()->json(['ok' => false, 'message' => 'disabled'], 403);
        }

        $validated = $request->validate([
            'id' => ['required', 'uuid'],
        ]);

        $recording = SessionRecording::query()
            ->where('uuid', $validated['id'])
            ->where('user_id', Auth::id())
            ->first();

        if (! $recording) {
            return response()->json(['ok' => false, 'message' => 'not_found'], 404);
        }

        if ($recording->ended_at === null) {
            $recording->ended_at = now();
            $recording->save();
        }

        return response()->json(['ok' => true]);
    }

    private function ingestAllowed(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return false;
        }

        return session_recording_enabled();
    }
}
