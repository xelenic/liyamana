@extends('layouts.admin')

@section('title', 'Thumbnail Prompts')
@section('page-title', 'Thumbnail Prompts')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-magic me-2 text-primary"></i>Thumbnail Prompts
            </h2>
            <p class="text-muted mb-0">Prompts used in the "Generate thumbnail with AI" dropdown on Templates Management</p>
        </div>
        <a href="{{ route('admin.thumbnail-prompts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Prompt
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Name</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Prompt</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Sort</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prompts as $p)
                        <tr>
                            <td style="padding: 1rem; font-weight: 500; color: #1e293b;">{{ $p->name }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">{{ Str::limit($p->prompt, 80) }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">{{ $p->sort_order }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <a href="{{ route('admin.thumbnail-prompts.edit', $p->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.thumbnail-prompts.delete', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this prompt?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No prompts yet. <a href="{{ route('admin.thumbnail-prompts.create') }}">Add one</a></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($prompts->hasPages())
    <div class="mt-4">{{ $prompts->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
