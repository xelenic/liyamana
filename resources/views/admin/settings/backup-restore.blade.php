@extends('layouts.admin')

@section('title', 'Backup & restore')
@section('page-title', 'Backup & restore')

@section('content')
<div class="my-2 settings-page-compact">
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

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-2" style="font-weight: 600; color: #1e293b;">
                        <i class="fas fa-file-archive text-primary me-2"></i>Create backup
                    </h5>
                    <p class="text-muted mb-3" style="font-size: 0.85rem;">
                        Queues a job that builds a <code>.zip</code> containing the database (SQLite file or MySQL dump), <code>storage/app/public</code> (uploads, images), and <code>storage/app/private</code> (excluding cache/session backup folders). Large sites may take several minutes.
                    </p>
                    <form action="{{ route('admin.settings.backup-restore.backup') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download me-1"></i>Queue backup
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-2" style="font-weight: 600; color: #1e293b;">
                        <i class="fas fa-upload text-warning me-2"></i>Restore from backup
                    </h5>
                    <p class="text-muted mb-3" style="font-size: 0.85rem;">
                        Upload a <strong>FlipBook backup zip</strong> created by this panel (same app version recommended). A queued job replaces the database and storage files. <strong class="text-danger">This overwrites current data.</strong>
                    </p>
                    <form action="{{ route('admin.settings.backup-restore.restore') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="restore_backup" class="form-label">Backup file (.zip)</label>
                            <input type="file" class="form-control" id="restore_backup" name="restore_backup" accept=".zip,application/zip" required>
                            <small class="text-muted">Max {{ number_format(config('backup.max_upload_bytes', 512 * 1024 * 1024) / 1048576, 0) }} MB (see BACKUP_MAX_UPLOAD_BYTES).</small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="restore_confirm" name="restore_confirm" required>
                                <label class="form-check-label" for="restore_confirm">
                                    I understand this will overwrite the database and user files
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-rotate me-1"></i>Queue restore
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="px-3 py-2 border-bottom bg-light">
                        <h6 class="mb-0" style="font-size: 0.9rem; font-weight: 600;">Recent operations</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>When</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="backup-operations-body">
                                @forelse($operations as $op)
                                <tr data-op-uuid="{{ $op->uuid }}" @if(($highlightUuid ?? '') === $op->uuid) class="table-primary" @endif>
                                    <td class="text-muted small">{{ $op->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $op->type === 'backup' ? 'Backup' : 'Restore' }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match ($op->status) {
                                                'completed' => 'bg-success',
                                                'failed' => 'bg-danger',
                                                'processing' => 'bg-warning text-dark',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge rounded-pill backup-status {{ $badgeClass }}" data-status="{{ $op->status }}">
                                            <span class="backup-status-label">{{ ucfirst($op->status) }}</span>
                                        </span>
                                    </td>
                                    <td class="text-end backup-download-cell">
                                        @if($op->type === 'backup' && $op->status === 'completed' && $op->result_path)
                                            <a href="{{ route('admin.settings.backup-restore.download', $op) }}" class="btn btn-sm btn-outline-primary py-0">Download</a>
                                        @endif
                                    </td>
                                </tr>
                                @if($op->error_message)
                                <tr class="border-0">
                                    <td colspan="4" class="text-danger small pt-0 pb-2">{{ \Illuminate\Support\Str::limit($op->error_message, 200) }}</td>
                                </tr>
                                @endif
                                @empty
                                <tr>
                                    <td colspan="4" class="text-muted text-center py-4">No backup or restore jobs yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <h5 class="card-title mb-2" style="font-size: 0.9rem;">
                        <i class="fas fa-server me-1 text-primary"></i>Queue worker
                    </h5>
                    <p class="text-muted mb-2" style="font-size: 0.75rem;">
                        Jobs run asynchronously. Ensure a worker is processing the queue (e.g. <code>php artisan queue:work</code> or your process supervisor). With <code>QUEUE_CONNECTION=sync</code>, jobs run immediately in the web request (may time out on large backups).
                    </p>
                    <p class="text-muted mb-0" style="font-size: 0.75rem;">
                        MySQL backup/restore requires <code>mysqldump</code> and <code>mysql</code> CLI tools on the server.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!empty($highlightUuid))
@push('scripts')
<script>
(function () {
    var uuid = @json($highlightUuid);
    var statusUrl = @json(route('admin.settings.backup-restore.status', ['backupOperation' => $highlightUuid]));
    var poll = function () {
        fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var row = document.querySelector('tr[data-op-uuid="' + uuid + '"]');
                if (row) {
                    var label = row.querySelector('.backup-status-label');
                    var badge = row.querySelector('.backup-status');
                    if (label) {
                        label.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                    }
                    if (badge) {
                        badge.className = 'badge rounded-pill backup-status';
                        if (data.status === 'completed') badge.classList.add('bg-success');
                        else if (data.status === 'failed') badge.classList.add('bg-danger');
                        else if (data.status === 'processing') badge.classList.add('bg-warning', 'text-dark');
                        else badge.classList.add('bg-secondary');
                    }
                    var cell = row.querySelector('.backup-download-cell');
                    if (cell && data.download_url && data.type === 'backup') {
                        cell.innerHTML = '<a href="' + data.download_url + '" class="btn btn-sm btn-outline-primary py-0">Download</a>';
                    }
                }
                if (!data.finished) {
                    setTimeout(poll, 2500);
                } else {
                    var key = 'flipbook-backup-polled-' + uuid;
                    if (!sessionStorage.getItem(key)) {
                        sessionStorage.setItem(key, '1');
                        window.location.replace(@json(route('admin.settings.backup-restore')));
                    }
                }
            })
            .catch(function () { setTimeout(poll, 4000); });
    };
    setTimeout(poll, 1500);
})();
</script>
@endpush
@endif
@endsection

@push('styles')
<style>
    .settings-page-compact .form-label { font-size: 0.8rem; }
    .settings-page-compact .form-control { font-size: 0.8rem; }
    .settings-page-compact .btn { font-size: 0.85rem; }
</style>
@endpush
