@extends('layouts.admin')

@section('title', 'Log: ' . $fileName)
@section('page-title', 'Log viewer')

@section('content')
<style>
    .log-viewer-pre { max-height: 75vh; overflow: auto; background: #0f172a; color: #e2e8f0; font-size: 0.75rem; line-height: 1.45; border-radius: 0 0 8px 8px; white-space: pre-wrap; word-break: break-word; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    .log-line { display: block; }
    .log-line--error { background: rgba(239, 68, 68, 0.12); color: #fecaca; border-left: 3px solid #ef4444; padding-left: 0.35rem; margin-left: -0.35rem; }
    .log-line--warning { background: rgba(245, 158, 11, 0.1); color: #fde68a; border-left: 3px solid #f59e0b; padding-left: 0.35rem; margin-left: -0.35rem; }
    .log-line--notice { color: #a5b4fc; }
    .log-line--info { color: #93c5fd; }
    .log-line--debug { color: #94a3b8; }
    .log-meta { color: #fbbf24; display: block; margin-bottom: 0.5rem; }
</style>
<div class="my-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-3">
        <div>
            <a href="{{ route('admin.logs') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Back to logs
            </a>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">
                <i class="fas fa-file-code me-2 text-primary"></i><code>{{ $fileName }}</code>
            </h2>
            <p class="text-muted small mb-0">
                Size: {{ number_format($fileSize / 1024, 1) }} KB
                @if($truncated)
                    <span class="badge bg-warning text-dark ms-1">Tail only (512 KB max)</span>
                @endif
            </p>
            <p class="small text-muted mb-0 mt-1">
                <span class="me-2">Lines by level:</span>
                <span class="badge bg-danger bg-opacity-75">error {{ $levelCounts['error'] ?? 0 }}</span>
                <span class="badge bg-warning text-dark">warning {{ $levelCounts['warning'] ?? 0 }}</span>
                <span class="badge bg-secondary">notice {{ $levelCounts['notice'] ?? 0 }}</span>
                <span class="badge bg-info text-dark">info {{ $levelCounts['info'] ?? 0 }}</span>
                <span class="badge bg-dark">debug {{ $levelCounts['debug'] ?? 0 }}</span>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form method="get" action="{{ route('admin.logs.show') }}" class="d-flex align-items-center gap-2 flex-wrap">
                <input type="hidden" name="file" value="{{ $fileName }}">
                <label class="small text-muted mb-0">Show</label>
                <select name="filter" class="form-select form-select-sm" style="width: auto; min-width: 10rem;" onchange="this.form.submit()">
                    <option value="all" @selected($filter === 'all')>All lines</option>
                    <option value="errors" @selected($filter === 'errors')>Errors only (ERROR+)</option>
                    <option value="warning" @selected($filter === 'warning')>Warnings only</option>
                    <option value="notice" @selected($filter === 'notice')>Notice only</option>
                    <option value="info" @selected($filter === 'info')>Info only</option>
                    <option value="debug" @selected($filter === 'debug')>Debug only</option>
                </select>
            </form>
            <form method="post" action="{{ route('admin.logs.clear') }}" class="d-inline"
                  onsubmit="return confirm('Clear the entire contents of {{ e($fileName) }}? This cannot be undone.');">
                @csrf
                <input type="hidden" name="file" value="{{ $fileName }}">
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash-alt me-1"></i>Clear log
                </button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="mb-0 p-3 small text-start log-viewer-pre">
                @if($metaPrefix !== '')
                    <span class="log-meta">{{ e($metaPrefix) }}</span>
                @endif
                @foreach($lines as $line)
                    <span class="log-line log-line--{{ $line['level'] }}">{{ e($line['text']) }}</span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
