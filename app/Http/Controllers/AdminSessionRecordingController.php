<?php

namespace App\Http\Controllers;

use App\Models\SessionRecording;
use App\Models\User;
use App\Support\RrwebEventSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AdminSessionRecordingController extends Controller
{
    public function index()
    {
        $userPaginator = DB::table('session_recordings')
            ->select('user_id')
            ->selectRaw('MAX(started_at) as last_started_at')
            ->selectRaw('COUNT(*) as recordings_count')
            ->groupBy('user_id')
            ->orderByDesc('last_started_at')
            ->paginate(20)
            ->withQueryString();

        $userIds = collect($userPaginator->items())
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $usersById = $userIds === []
            ? collect()
            : User::query()->whereIn('id', $userIds)->get()->keyBy('id');

        $userPaginator->setCollection(
            collect($userPaginator->items())->map(function ($row) use ($usersById) {
                $uid = (int) $row->user_id;

                return (object) [
                    'user_id' => $uid,
                    'user' => $usersById->get($uid),
                    'recordings_count' => (int) $row->recordings_count,
                    'last_started_at' => $row->last_started_at,
                ];
            })
        );

        return view('admin.session-recordings.index', [
            'userPaginator' => $userPaginator,
        ]);
    }

    /**
     * Paginated session recordings for a single user.
     */
    public function userRecordings(User $user)
    {
        $recordings = SessionRecording::query()
            ->where('user_id', $user->id)
            ->with('user')
            ->orderByDesc('started_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.session-recordings.user', compact('user', 'recordings'));
    }

    public function replay(string $uuid)
    {
        $recording = SessionRecording::query()->where('uuid', $uuid)->with('user')->firstOrFail();

        return view('admin.session-recordings.replay', compact('recording'));
    }

    public function events(string $uuid): JsonResponse
    {
        $recording = SessionRecording::query()->where('uuid', $uuid)->firstOrFail();
        $path = $recording->absoluteEventsPath();
        $max = session_recording_max_replay_bytes();

        if (! File::isFile($path)) {
            return response()->json([]);
        }

        if (File::size($path) > $max) {
            return response()->json([
                'error' => 'Recording file exceeds max replay size. Delete it or increase the limit in Admin → Settings → Session recording.',
            ], 413);
        }

        $events = [];
        $readBytes = 0;
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return response()->json(['error' => 'Could not read recording.'], 500);
        }
        $jsonDepth = session_recording_json_max_depth();

        try {
            while (($line = fgets($handle)) !== false) {
                $readBytes += strlen($line);
                if ($readBytes > $max) {
                    return response()->json(['error' => 'Recording too large to parse.'], 413);
                }
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                try {
                    $events[] = json_decode($line, true, $jsonDepth, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    Log::warning('session_recording.events_json_decode_failed', [
                        'uuid' => $uuid,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        } finally {
            fclose($handle);
        }

        $events = RrwebEventSanitizer::sanitizeEvents($events);

        return response()->json($events);
    }

    public function destroy(string $uuid)
    {
        $recording = SessionRecording::query()->where('uuid', $uuid)->firstOrFail();
        $userId = $recording->user_id;
        $recording->delete();

        return redirect()
            ->route('admin.session-recordings.user', $userId)
            ->with('success', 'Session recording deleted.');
    }
}
