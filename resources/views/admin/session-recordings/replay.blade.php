@extends('layouts.admin')

@section('title', 'Replay session')
@section('page-title', 'Session replay')

@push('styles')
<link rel="stylesheet" href="{{ session_recording_cdn_url('player_css') }}" crossorigin="anonymous" referrerpolicy="no-referrer">
@endpush

@section('content')
<div class="my-4">
    <a href="{{ route('admin.session-recordings.user', $recording->user_id) }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-1"></i>Back to user sessions
    </a>

    <div class="mb-3">
        <h2 class="h5 mb-1" style="font-weight: 700; color: #1e293b;">
            <i class="fas fa-user me-2 text-primary"></i>
            @if($recording->user)
                {{ $recording->user->name }} · {{ $recording->user->email }}
            @else
                User #{{ $recording->user_id }}
            @endif
        </h2>
        <p class="text-muted small mb-0">
            {{ $recording->started_at?->format('Y-m-d H:i:s') }}
            @if($recording->landing_path)
                · <code>{{ \Illuminate\Support\Str::limit($recording->landing_path, 120) }}</code>
            @endif
            · {{ number_format($recording->byte_size / 1024, 1) }} KB
        </p>
    </div>

    <div id="rrweb-replay-status" class="alert alert-warning d-none mb-3" role="alert"></div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <div id="rrweb-replayer" class="rrweb-replayer-root" style="min-height: 520px;"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.__RRWEB_EVENTS_URL__ = @json(route('admin.session-recordings.events', $recording->uuid));
</script>
@include('partials.rrweb-session-replay')
@endpush
@endsection
