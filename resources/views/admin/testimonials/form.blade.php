@extends('layouts.admin')

@section('title', isset($testimonial) ? 'Edit Testimonial' : 'Create Testimonial')
@section('page-title', isset($testimonial) ? 'Edit Testimonial' : 'Create Testimonial')

@section('content')
<div class="my-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ isset($testimonial) ? route('admin.testimonials.update', $testimonial->id) : route('admin.testimonials.store') }}" method="POST">
                        @csrf
                        @if(isset($testimonial))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $testimonial->name ?? '') }}" placeholder="John Doe" required maxlength="255">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role / Title</label>
                                    <input type="text" class="form-control @error('role') is-invalid @enderror" id="role" name="role" value="{{ old('role', $testimonial->role ?? '') }}" placeholder="Marketing Director" maxlength="255">
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">e.g. Marketing Director, Creative Designer</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Quote / Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="4" placeholder="The testimonial quote text..." required>{{ old('content', $testimonial->content ?? '') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rating" class="form-label">Rating (stars)</label>
                                    <select class="form-select @error('rating') is-invalid @enderror" id="rating" name="rating">
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" {{ old('rating', $testimonial->rating ?? 5) == $i ? 'selected' : '' }}>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                                        @endfor
                                    </select>
                                    @error('rating')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="avatar_url" class="form-label">Avatar URL</label>
                                    <input type="text" class="form-control @error('avatar_url') is-invalid @enderror" id="avatar_url" name="avatar_url" value="{{ old('avatar_url', $testimonial->avatar_url ?? '') }}" placeholder="e.g. storage/avatars/photo.jpg" maxlength="500">
                                    @error('avatar_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Optional. Leave blank to use initials.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort order</label>
                                    <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Lower numbers appear first on the home page.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input type="hidden" name="is_active" value="0">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $testimonial->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active (show on home page)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.testimonials') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ isset($testimonial) ? 'Update' : 'Create' }} Testimonial
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
                        <i class="fas fa-info-circle text-primary me-2"></i>Information
                    </h5>
                    <p class="text-muted small mb-0">
                        Testimonials are displayed on the home page in the "Loved by Thousands" section. Only active testimonials are shown. Use sort order to control the display order (lower = first).
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
