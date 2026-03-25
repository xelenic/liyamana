@extends('layouts.admin')

@section('title', 'Manage Images - ' . $category->name)
@section('page-title', 'Manage Images - ' . $category->name)

@section('content')
<div class="my-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('admin.global-images.index') }}" class="text-muted text-decoration-none mb-2 d-inline-block">
                <i class="fas fa-arrow-left me-1"></i>Back to Categories
            </a>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-images me-2 text-primary"></i>{{ $category->name }}
            </h2>
            <p class="text-muted mb-0">{{ $category->images->count() }} image(s) in this category</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.global-images.upload', $category->id) }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <input type="file" name="images[]" id="imageInput" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml" multiple style="display: none;">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-upload me-1"></i>Upload Images
                </button>
            </form>
            <a href="{{ route('admin.global-images.categories.edit', $category->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-edit me-1"></i>Edit Category
            </a>
        </div>
    </div>

    <!-- Images Grid -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($category->images->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-image text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                <h5 class="mt-3 text-muted">No images in this category</h5>
                <p class="text-muted mb-4">Upload images to make them available in the design editor</p>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-upload me-1"></i>Upload Images
                </button>
            </div>
            @else
            <p class="text-muted small mb-3">JPG, PNG, GIF, WebP, SVG supported. Max 10MB per file.</p>
            <div class="row g-3">
                @foreach($category->images as $image)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card border h-100" style="overflow: hidden;">
                        <div style="aspect-ratio: 1; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            @if(preg_match('/\.svg$/i', $image->path))
                                <img src="{{ $image->url }}" alt="{{ $image->name ?? 'Image' }}" style="max-width: 100%; max-height: 100%; object-fit: contain; padding: 0.5rem;">
                            @else
                                <img src="{{ $image->url }}" alt="{{ $image->name ?? 'Image' }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @endif
                        </div>
                        <div class="card-body p-2">
                            <small class="text-muted d-block text-truncate" title="{{ $image->name ?? basename($image->path) }}">{{ $image->name ?? basename($image->path) }}</small>
                            <form action="{{ route('admin.global-images.images.delete', [$category->id, $image->id]) }}" method="POST" class="d-inline mt-1" onsubmit="return confirm('Delete this image?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('imageInput').addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            document.getElementById('uploadForm').submit();
        }
    });
</script>
@endpush
@endsection
