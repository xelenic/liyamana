@extends('layouts.admin')

@section('title', 'User heatmaps')
@section('page-title', 'User heatmaps')

@section('content')
<div class="my-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-fire me-2 text-danger"></i>Click heatmaps
            </h2>
            <p class="text-muted mb-0 small">
                Aggregated click positions per user (viewport %). Enable <strong>Collect click heatmaps</strong> in Settings → Session &amp; heatmap. Not a screenshot of the page — intensity is overlaid on a fixed canvas.
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th class="small text-muted">User</th>
                            <th class="small text-muted">Email</th>
                            <th class="small text-muted text-end">Clicks</th>
                            <th class="small text-muted text-end">Paths</th>
                            <th class="small text-muted">Last click</th>
                            <th class="small text-muted text-center" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userPaginator as $row)
                            <tr>
                                <td class="small">
                                    @if($row->user)
                                        <strong>{{ $row->user->name }}</strong>
                                    @else
                                        <span class="text-muted">User #{{ $row->user_id }}</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $row->user?->email ?? '—' }}</td>
                                <td class="small text-end"><span class="badge bg-secondary">{{ number_format($row->clicks_count) }}</span></td>
                                <td class="small text-end">{{ number_format($row->paths_count) }}</td>
                                <td class="small text-muted">
                                    @if($row->last_click_at)
                                        {{ \Carbon\Carbon::parse($row->last_click_at)->format('M j, Y H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.heatmap.user', $row->user_id) }}" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.75rem;">
                                        <i class="fas fa-map me-1"></i>Open
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    No heatmap data yet. Enable the feature and collect clicks from the user app.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($userPaginator->hasPages())
            <div class="card-footer bg-white border-0">{{ $userPaginator->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>
@endsection
