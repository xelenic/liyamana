@extends('layouts.admin')

@section('title', isset($category) ? 'Edit Category' : 'Add Category')
@section('page-title', isset($category) ? 'Edit Category' : 'Add Category')

@section('content')
<div class="my-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ isset($category) ? route('admin.global-images.categories.update', $category->id) : route('admin.global-images.categories.store') }}" method="POST">
                        @csrf
                        @if(isset($category))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name ?? '') }}" required placeholder="e.g. Icons, Clipart, Backgrounds">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Display name for the category (slug is auto-generated)</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.global-images.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ isset($category) ? 'Update' : 'Create' }} Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-info-circle text-primary me-2"></i>About Categories
                    </h5>
                    <p class="text-muted small mb-0">
                        Categories organize global image parts in the design editor. Users can browse images by category in the "Global" tab of the multi-page design tool.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
