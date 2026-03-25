@extends('layouts.admin')

@section('title', isset($slide) ? 'Edit Slide' : 'Add Slide')
@section('page-title', isset($slide) ? 'Edit Slide' : 'Add Slide')

@section('content')
<div class="my-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ isset($slide) ? route('admin.explore-slides.update', $slide->id) : route('admin.explore-slides.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($slide)) @method('PUT') @endif

                        <div class="mb-3">
                            <label for="image" class="form-label">Image <span class="text-danger">{{ isset($slide) ? '' : '*' }}</span></label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" {{ isset($slide) ? '' : 'required' }}>
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">JPEG, PNG, GIF, WebP. Max 2MB. {{ isset($slide) ? 'Leave empty to keep current.' : '' }}</small>
                            @if(isset($slide) && $slide->image_url)
                                <div class="mt-2">
                                    <img src="{{ $slide->image_url }}" alt="Current" style="max-height: 120px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $slide->title ?? '') }}" placeholder="Slide title">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Slide description">{{ old('description', $slide->description ?? '') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="link_url" class="form-label">Link URL</label>
                                <input type="url" class="form-control @error('link_url') is-invalid @enderror" id="link_url" name="link_url" value="{{ old('link_url', $slide->link_url ?? '') }}" placeholder="https://...">
                                @error('link_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="link_text" class="form-label">Link Text</label>
                                <input type="text" class="form-control @error('link_text') is-invalid @enderror" id="link_text" name="link_text" value="{{ old('link_text', $slide->link_text ?? '') }}" placeholder="Learn more">
                                @error('link_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $slide->sort_order ?? 0) }}">
                                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $slide->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.explore-slides') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ isset($slide) ? 'Update' : 'Create' }} Slide</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Info</h5>
                    <p class="text-muted small mb-0">Slides appear in a Swiper carousel at the top of the template explore page. Add images with optional title and description. Only active slides are shown.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
