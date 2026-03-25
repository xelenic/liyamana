@extends('layouts.admin')

@section('title', isset($sheetType) ? 'Edit Sheet Type' : 'Create Sheet Type')
@section('page-title', isset($sheetType) ? 'Edit Sheet Type' : 'Create Sheet Type')

@section('content')
<div class="my-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ isset($sheetType) ? route('admin.sheet-types.update', $sheetType->id) : route('admin.sheet-types.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($sheetType))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $sheetType->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Display name for the sheet type (e.g., "Standard", "Glossy")</small>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $sheetType->slug ?? '') }}" required>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">URL-friendly identifier (e.g., "standard", "glossy") - lowercase, no spaces</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price_per_sheet" class="form-label">Price Per Sheet ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control @error('price_per_sheet') is-invalid @enderror" id="price_per_sheet" name="price_per_sheet" value="{{ old('price_per_sheet', $sheetType->price_per_sheet ?? '0.50') }}" required>
                                    @error('price_per_sheet')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Price per sheet in dollars</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="multiplier" class="form-label">Multiplier <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control @error('multiplier') is-invalid @enderror" id="multiplier" name="multiplier" value="{{ old('multiplier', $sheetType->multiplier ?? '1.0') }}" required>
                                    @error('multiplier')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Price multiplier (e.g., 1.0, 1.2, 1.5)</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="description" class="form-label mb-0">Description</label>
                                <button type="button" id="generateDescriptionBtn" class="btn btn-sm btn-outline-primary" onclick="generateSheetTypeDescription()" title="Generate description with AI">
                                    <i class="fas fa-magic me-1"></i>Generate
                                </button>
                            </div>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $sheetType->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Optional description of the sheet type. Use Generate to create with AI.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Image</label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">JPEG, PNG, GIF, WebP. Max 2MB. Optional.</small>
                                    @if(isset($sheetType) && $sheetType->image_url)
                                    <div class="mt-2">
                                        <img src="{{ $sheetType->image_url }}" alt="Current image" class="img-thumbnail" style="max-height: 120px;">
                                        <small class="d-block text-muted mt-1">Current image. Upload new to replace.</small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="video" class="form-label">Video</label>
                                    <input type="file" class="form-control @error('video') is-invalid @enderror" id="video" name="video" accept="video/mp4,video/webm,video/ogg">
                                    @error('video')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">MP4, WebM, OGG. Max 10MB. Optional.</small>
                                    @if(isset($sheetType) && $sheetType->video_url)
                                    <div class="mt-2">
                                        <video src="{{ $sheetType->video_url }}" controls style="max-height: 120px; max-width: 100%;" class="rounded border"></video>
                                        <small class="d-block text-muted mt-1">Current video. Upload new to replace.</small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $sheetType->sort_order ?? '0') }}">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Lower numbers appear first in lists</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input type="hidden" name="is_active" value="0">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $sheetType->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">Only active sheet types will be available for selection</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.sheet-types') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ isset($sheetType) ? 'Update' : 'Create' }} Sheet Type
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
                    <div class="mb-3">
                        <strong>Price Per Sheet:</strong>
                        <p class="text-muted small mb-0">This is the base price charged per sheet. The total cost will be calculated as: price_per_sheet × page_count × quantity</p>
                    </div>
                    <div class="mb-3">
                        <strong>Multiplier:</strong>
                        <p class="text-muted small mb-0">Used for backward compatibility and alternative pricing calculations. Typically ranges from 1.0 to 1.5.</p>
                    </div>
                    <div class="mb-3">
                        <strong>Sort Order:</strong>
                        <p class="text-muted small mb-0">Controls the display order in dropdowns. Lower numbers appear first.</p>
                    </div>
                    <div>
                        <strong>Active Status:</strong>
                        <p class="text-muted small mb-0">Only active sheet types will be visible to users when creating flipbooks or using templates.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        const slugInput = document.getElementById('slug');
        if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
            slugInput.value = name.toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.dataset.autoGenerated = 'true';
        }
    });

    // Clear auto-generated flag when user manually edits slug
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.autoGenerated = 'false';
    });

    async function generateSheetTypeDescription() {
        const btn = document.getElementById('generateDescriptionBtn');
        const descEl = document.getElementById('description');
        const name = document.getElementById('name').value || '';
        const slug = document.getElementById('slug').value || '';

        if (!name && !slug) {
            alert('Please enter a name or slug first.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';

        try {
            const response = await fetch('{{ route("admin.sheet-types.generateDescription") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: name, slug: slug })
            });
            const data = await response.json();

            if (data.success) {
                descEl.value = data.description;
            } else {
                alert(data.message || 'Failed to generate description');
            }
        } catch (e) {
            alert('Failed to generate description. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-magic me-1"></i>Generate';
        }
    }
</script>
@endpush
@endsection

