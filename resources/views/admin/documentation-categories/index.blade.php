@extends('layouts.admin')

@section('title', 'Documentation Categories')
@section('page-title', 'Documentation Categories')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-folder me-2 text-primary"></i>Documentation Categories
            </h2>
            <p class="text-muted mb-0">Group documentation pages by category</p>
        </div>
        <a href="{{ route('admin.documentation-categories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Category
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
            <form method="GET" action="{{ route('admin.documentation-categories') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or slug..." value="{{ request('search') }}">
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

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Name</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Slug</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Pages</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Description</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Sort</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td style="padding: 1rem; font-weight: 600;">{{ $category->name }}</td>
                            <td style="padding: 1rem;"><code class="small">{{ $category->slug }}</code></td>
                            <td style="padding: 1rem;"><span class="badge bg-primary">{{ $category->documentations_count ?? 0 }}</span></td>
                            <td style="padding: 1rem; font-size: 0.875rem; color: #64748b;">{{ Str::limit($category->description, 50) ?: '—' }}</td>
                            <td style="padding: 1rem;">{{ $category->sort_order }}</td>
                            <td style="padding: 1rem;">
                                @if($category->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                <a href="{{ route('admin.documentation-categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.documentation-categories.delete', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category? Docs in it will be uncategorized.');">
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
                            <td colspan="7" class="text-center py-4 text-muted">No categories yet. <a href="{{ route('admin.documentation-categories.create') }}">Create one</a>.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($categories->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $categories->links() }}
    </div>
    @endif
</div>
@endsection
