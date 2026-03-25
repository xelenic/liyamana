<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserHeatmapClick;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class AdminUserHeatmapController extends Controller
{
    public function index()
    {
        $paginator = DB::table('user_heatmap_clicks')
            ->select('user_id')
            ->selectRaw('COUNT(*) as clicks_count')
            ->selectRaw('COUNT(DISTINCT path) as paths_count')
            ->selectRaw('MAX(created_at) as last_click_at')
            ->groupBy('user_id')
            ->orderByDesc('clicks_count')
            ->paginate(20)
            ->withQueryString();

        $userIds = collect($paginator->items())
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $usersById = $userIds === []
            ? collect()
            : User::query()->whereIn('id', $userIds)->get()->keyBy('id');

        $paginator->setCollection(
            collect($paginator->items())->map(function ($row) use ($usersById) {
                $uid = (int) $row->user_id;

                return (object) [
                    'user_id' => $uid,
                    'user' => $usersById->get($uid),
                    'clicks_count' => (int) $row->clicks_count,
                    'paths_count' => (int) $row->paths_count,
                    'last_click_at' => $row->last_click_at,
                ];
            })
        );

        return view('admin.heatmap.index', ['userPaginator' => $paginator]);
    }

    public function user(User $user)
    {
        $paths = UserHeatmapClick::query()
            ->where('user_id', $user->id)
            ->select('path')
            ->selectRaw('COUNT(*) as clicks_count')
            ->groupBy('path')
            ->orderByDesc('clicks_count')
            ->limit(150)
            ->get();

        $selectedPath = request('path');
        if ($selectedPath !== null && $selectedPath !== '') {
            $exists = $paths->contains(fn ($p) => $p->path === $selectedPath);
            if (! $exists) {
                $selectedPath = $paths->first()?->path;
            }
        } else {
            $selectedPath = $paths->first()?->path;
        }

        return view('admin.heatmap.user', [
            'user' => $user,
            'paths' => $paths,
            'selectedPath' => $selectedPath,
            'heatmapIframeSrc' => $this->safePagePreviewUrl($selectedPath),
        ]);
    }

    /**
     * Only allow same-app relative paths in iframes (avoid open redirects).
     */
    private function safePagePreviewUrl(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }
        $path = trim($path);
        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return '';
        }
        if (str_contains($path, "\n") || str_contains($path, "\r")) {
            return '';
        }

        return URL::to($path);
    }

    public function data(User $user, Request $request): JsonResponse
    {
        $path = (string) $request->query('path', '');
        $max = user_heatmap_admin_max_points_per_response();

        if ($path === '') {
            return response()->json([
                'points' => [],
                'max' => 1,
                'containerWidth' => 900,
                'containerHeight' => 560,
            ]);
        }

        $rows = UserHeatmapClick::query()
            ->where('user_id', $user->id)
            ->where('path', $path)
            ->orderByDesc('id')
            ->limit($max)
            ->get(['x_pct', 'y_pct']);

        $cw = 900;
        $ch = 560;
        $buckets = [];
        foreach ($rows as $r) {
            $x = (int) round($r->x_pct / 100 * $cw);
            $y = (int) round($r->y_pct / 100 * $ch);
            $k = $x.':'.$y;
            $buckets[$k] = ($buckets[$k] ?? 0) + 1;
        }

        $points = [];
        $maxVal = 1;
        foreach ($buckets as $k => $v) {
            [$x, $y] = array_map('intval', explode(':', $k, 2));
            $points[] = ['x' => $x, 'y' => $y, 'value' => $v];
            $maxVal = max($maxVal, $v);
        }

        return response()->json([
            'points' => $points,
            'max' => $maxVal,
            'containerWidth' => $cw,
            'containerHeight' => $ch,
            'totalClicksLoaded' => $rows->count(),
        ]);
    }
}
