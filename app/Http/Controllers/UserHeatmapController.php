<?php

namespace App\Http\Controllers;

use App\Models\UserHeatmapClick;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserHeatmapController extends Controller
{
    public function ingest(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return response()->json(['ok' => false, 'message' => 'disabled'], 403);
        }

        $maxBatch = user_heatmap_max_clicks_per_ingest();

        $validated = $request->validate([
            'clicks' => ['required', 'array', 'max:'.$maxBatch],
            'clicks.*.path' => ['required', 'string', 'max:1024'],
            'clicks.*.x_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'clicks.*.y_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'clicks.*.viewport_w' => ['nullable', 'integer', 'min:1', 'max:8192'],
            'clicks.*.viewport_h' => ['nullable', 'integer', 'min:1', 'max:8192'],
        ]);

        $now = now();
        $userId = Auth::id();
        $rows = [];

        foreach ($validated['clicks'] as $c) {
            $rows[] = [
                'user_id' => $userId,
                'path' => $c['path'],
                'x_pct' => round((float) $c['x_pct'], 3),
                'y_pct' => round((float) $c['y_pct'], 3),
                'viewport_w' => isset($c['viewport_w']) ? (int) $c['viewport_w'] : null,
                'viewport_h' => isset($c['viewport_h']) ? (int) $c['viewport_h'] : null,
                'created_at' => $now,
            ];
        }

        if ($rows !== []) {
            UserHeatmapClick::insert($rows);
        }

        return response()->json(['ok' => true]);
    }

    private function allowed(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return false;
        }

        return user_heatmap_enabled();
    }
}
