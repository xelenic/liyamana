@extends('layouts.admin')

@section('title', 'Log Viewer')
@section('page-title', 'Log Viewer')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-file-alt me-2 text-primary"></i>Application logs
            </h2>
            <p class="text-muted mb-0">Files in <code>storage/logs</code>. Large files show the tail only. Use <strong>View</strong> to filter by log level or clear a file.</p>
        </div>
    </div>

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

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">File</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Size</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Modified</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($files as $f)
                        <tr>
                            <td style="padding: 1rem; font-family: monospace; font-size: 0.85rem;">{{ $f['name'] }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">{{ number_format($f['size'] / 1024, 1) }} KB</td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">{{ \Carbon\Carbon::createFromTimestamp($f['modified'])->format('M d, Y H:i') }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <a href="{{ route('admin.logs.show', ['file' => $f['name']]) }}" class="btn btn-sm btn-outline-primary py-0 px-2 me-1" style="font-size: 0.75rem;">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                                <form method="post" action="{{ route('admin.logs.clear') }}" class="d-inline"
                                      onsubmit="return confirm('Clear the entire contents of {{ e($f['name']) }}? This cannot be undone.');">
                                    @csrf
                                    <input type="hidden" name="file" value="{{ $f['name'] }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;">
                                        <i class="fas fa-trash-alt me-1"></i>Clear
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No <code>.log</code> files found in <code>storage/logs</code>.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
