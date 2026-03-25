@extends('layouts.admin')

@section('title', 'Global Image Library')
@section('page-title', 'Global Image Library')

@section('content')
<div class="my-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-globe me-2 text-primary"></i>Global Image Library
            </h2>
            <p class="text-muted mb-0">Manage image parts by category for the design editor</p>
        </div>
        <a href="{{ route('admin.global-images.categories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Category
        </a>
    </div>

    <!-- Categories Grid -->
    <div class="row">
        @forelse($categories as $category)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <h5 class="card-title mb-1" style="font-weight: 600; color: #1e293b;">{{ $category->name }}</h5>
                            <span class="badge bg-info" style="font-size: 0.75rem;">{{ $category->images_count }} image(s)</span>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('admin.global-images.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.global-images.categories.delete', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category and all its images?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">
                        <code style="font-size: 0.75rem;">{{ $category->slug }}</code>
                    </p>
                    <a href="{{ route('admin.global-images.show', $category->id) }}" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-images me-1"></i>Manage Images
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-folder-open text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                    <h5 class="mt-3 text-muted">No categories yet</h5>
                    <p class="text-muted mb-4">Create a category to start adding global image parts</p>
                    <a href="{{ route('admin.global-images.categories.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add First Category
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
