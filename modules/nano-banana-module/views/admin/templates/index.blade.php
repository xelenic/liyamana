@extends('layouts.admin')

@section('title', 'AI Image Templates')
@section('page-title', 'AI Image Templates')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-magic me-2 text-primary"></i>AI Image Templates
            </h2>
            <p class="text-muted mb-0">AI design templates powered by Gemini</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.nanobanana.settings') }}" class="btn btn-outline-secondary">
                <i class="fas fa-cog me-1"></i>Settings
            </a>
            <a href="{{ route('admin.nanobanana.templates.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Add Template
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; width: 60px;">Image</th>
                            <th style="padding: 1rem;">Name</th>
                            <th style="padding: 1rem;">Prompt</th>
                            <th style="padding: 1rem;">Upload Image</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Order</th>
                            <th style="padding: 1rem; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $t)
                        <tr>
                            <td style="padding: 1rem; vertical-align: middle;">
                                @if($t->thumbnail_url)
                                    <img src="{{ $t->thumbnail_url }}" alt="{{ $t->name }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;">
                                @else
                                    <div style="width: 48px; height: 48px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                <div style="font-weight: 500;">{{ $t->name }}</div>
                                @if($t->description)
                                    <div style="font-size: 0.8rem; color: #94a3b8;">{{ Str::limit($t->description, 40) }}</div>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; max-width: 200px;">
                                <code style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; display: block; overflow: hidden; text-overflow: ellipsis;">{{ Str::limit($t->prompt, 50) }}</code>
                            </td>
                            <td style="padding: 1rem;">
                                @if($t->upload_image)
                                    <span class="badge bg-info">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                @if($t->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">{{ $t->sort_order }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.nanobanana.templates.edit', $t) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.nanobanana.templates.destroy', $t) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this template?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b;">
                                No templates yet. <a href="{{ route('admin.nanobanana.templates.create') }}">Create one</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
