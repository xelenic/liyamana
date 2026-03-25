@extends('layouts.admin')

@section('title', 'Documentation')
@section('page-title', 'Documentation')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-book me-2 text-primary"></i>Documentation
            </h2>
            <p class="text-muted mb-0">Manage help and documentation pages</p>
        </div>
        <a href="{{ route('admin.documentation.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Documentation
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.documentation') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search title, slug, content..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Title</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Slug</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Category</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Sort</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documentations as $doc)
                        <tr>
                            <td style="padding: 1rem; font-weight: 600;">{{ $doc->title }}</td>
                            <td style="padding: 1rem;"><code class="small">{{ $doc->slug }}</code></td>
                            <td style="padding: 1rem;">
                                @if($doc->categories->isNotEmpty())
                                    @foreach($doc->categories as $cat)
                                        <span class="badge bg-light text-dark border me-1">{{ $cat->name }}</span>
                                    @endforeach
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                @if($doc->is_published)
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">{{ $doc->sort_order }}</td>
                            <td style="padding: 1rem;">
                                <a href="{{ route('admin.documentation.edit', $doc->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.documentation.delete', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this documentation page?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No documentation yet. <a href="{{ route('admin.documentation.create') }}">Add one</a>.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($documentations->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $documentations->links() }}
    </div>
    @endif
</div>
@endsection
