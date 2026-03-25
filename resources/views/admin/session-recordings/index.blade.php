@extends('layouts.admin')

@section('title', 'Session recordings')
@section('page-title', 'Session recordings')

@section('content')
<div class="my-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-circle-play me-2 text-primary"></i>Session recordings
            </h2>
            <p class="text-muted mb-0 small">
                Users who have at least one recording. Open a user to see replays and delete sessions.
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
                            <th class="small text-muted text-end">Recordings</th>
                            <th class="small text-muted">Last session</th>
                            <th class="small text-muted text-center" style="width: 140px;">Actions</th>
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
                                <td class="small text-muted">
                                    {{ $row->user?->email ?? '—' }}
                                </td>
                                <td class="small text-end">
                                    <span class="badge bg-secondary">{{ $row->recordings_count }}</span>
                                </td>
                                <td class="small text-muted">
                                    @if($row->last_started_at)
                                        {{ \Carbon\Carbon::parse($row->last_started_at)->format('M j, Y H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.session-recordings.user', $row->user_id) }}" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.75rem;">
                                        <i class="fas fa-list me-1"></i>Sessions
                                    </a>
                                    @if($row->user)
                                        <a href="{{ route('admin.users.show', $row->user_id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2 ms-1" style="font-size: 0.75rem;" title="Admin user profile">
                                            <i class="fas fa-user"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    No recordings yet. Enable recording under Settings → Session &amp; heatmap, then browse the app as a non-admin user.
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
