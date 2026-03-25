@extends('layouts.admin')

@section('title', 'Sessions: ' . $user->name)
@section('page-title', 'Session recordings')

@section('content')
<div class="my-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <a href="{{ route('admin.session-recordings.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-1"></i>All users
    </a>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="h5 mb-1" style="font-weight: 700; color: #1e293b;">
                    <i class="fas fa-user me-2 text-primary"></i>{{ $user->name }}
                </h2>
                <p class="text-muted small mb-0">{{ $user->email }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.heatmap.user', $user) }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-fire me-1"></i>Click heatmap
                </a>
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-id-card me-1"></i>User profile
                </a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <strong class="text-dark">Recorded sessions</strong>
            <span class="text-muted small ms-2">({{ $recordings->total() }} total)</span>
        </div>
        <div class="card-body p-0">
            @include('admin.session-recordings._table', ['recordings' => $recordings])
        </div>
        @if($recordings->hasPages())
            <div class="card-footer bg-white border-0">{{ $recordings->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>
@endsection
