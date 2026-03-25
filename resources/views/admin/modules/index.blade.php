@extends('layouts.admin')

@section('title', 'Modules')
@section('page-title', 'Modules')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-puzzle-piece me-2 text-primary"></i>Modules
            </h2>
            <p class="text-muted mb-0">Install and manage platform modules (plugins)</p>
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

    <!-- Upload form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0" style="font-size: 1rem;">
                <i class="fas fa-upload me-2 text-primary"></i>Install Module from ZIP
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.modules.store') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-6">
                    <label for="module_zip" class="form-label">Module ZIP file (max 10MB)</label>
                    <input type="file" class="form-control @error('module_zip') is-invalid @enderror" id="module_zip" name="module_zip" accept=".zip" required>
                    @error('module_zip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Install Module
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modules list -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Module</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Version</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Installed</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $module)
                        <tr>
                            <td style="padding: 1rem;">
                                <div style="font-size: 0.9rem; font-weight: 500; color: #1e293b;">
                                    {{ $module->manifest['label'] ?? $module->name }}
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $module->name }}</small>
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">
                                <code style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">{{ $module->version }}</code>
                            </td>
                            <td style="padding: 1rem;">
                                @if($module->enabled)
                                    <span class="badge bg-success" style="font-size: 0.75rem;">Enabled</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 0.75rem;">Disabled</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">
                                {{ $module->installed_at?->format('M d, Y') ?? '—' }}
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <div class="btn-group" role="group">
                                    <form action="{{ route('admin.modules.toggle', $module->name) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $module->enabled ? 'warning' : 'success' }}" title="{{ $module->enabled ? 'Disable' : 'Enable' }}">
                                            <i class="fas fa-{{ $module->enabled ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.modules.destroy', $module->name) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to uninstall this module? This will remove all module files.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Uninstall">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No modules installed. Upload a module ZIP to get started.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
