@extends('layouts.admin')

@section('title', 'AI Content Templates')
@section('page-title', 'AI Content Templates')

@section('content')
<div class="my-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-robot me-2 text-primary"></i>AI Content Templates
            </h2>
            <p class="text-muted mb-0">Manage AI prompts, fields, and editor layouts for content generation</p>
        </div>
        <a href="{{ route('admin.ai-content-templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add New Template
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.ai-content-templates') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, description, or prompt..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; width: 60px;">Image</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Name</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Description</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Prompt</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Fields</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Editor JSON</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Sort</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $t)
                        <tr>
                            <td style="padding: 1rem; vertical-align: middle;">
                                @if($t->image_url)
                                <img src="{{ $t->image_url }}" alt="{{ $t->name }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;">
                                @else
                                <div style="width: 48px; height: 48px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                    <i class="fas fa-image"></i>
                                </div>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                <div style="font-size: 0.9rem; font-weight: 500; color: #1e293b;">{{ $t->name }}</div>
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">
                                {{ Str::limit($t->description ?? '-', 40) }}
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">
                                {{ Str::limit($t->prompt ?? '-', 50) }}
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">
                                @if($t->fields && is_array($t->fields))
                                    <span class="badge bg-info">{{ count($t->fields) }} field(s)</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">
                                @if($t->editor_json && isset($t->editor_json['pages']))
                                    <span class="badge bg-success">{{ count($t->editor_json['pages']) }} page(s)</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                @if($t->is_active)
                                    <span class="badge bg-success" style="font-size: 0.75rem;">Active</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 0.75rem;">Inactive</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">{{ $t->sort_order }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.ai-content-templates.edit', $t->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.ai-content-templates.delete', $t->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this AI content template?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No AI content templates found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($templates->hasPages())
    <div class="mt-3">
        {{ $templates->links() }}
    </div>
    @endif
</div>
@endsection
