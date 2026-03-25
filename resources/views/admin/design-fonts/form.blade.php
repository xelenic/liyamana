@extends('layouts.admin')

@section('title', isset($font) ? 'Edit font' : 'Add font')
@section('page-title', isset($font) ? 'Edit design font' : 'Add design font')

@section('content')
<div class="my-2">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('admin.design-fonts.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card border-0 shadow-sm" style="max-width: 640px;">
        <div class="card-body">
            <form action="{{ isset($font) ? route('admin.design-fonts.update', $font->id) : route('admin.design-fonts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($font))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="form-label">Display name (CSS font-family)</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $font->name ?? '') }}" required maxlength="120" placeholder="e.g. Brand Sans">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Must be unique. This exact string is used in the editor and saved in designs.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Font file {{ isset($font) ? '(leave empty to keep current)' : '' }}</label>
                    <input type="file" name="font_file" class="form-control @error('font_file') is-invalid @enderror" {{ isset($font) ? '' : 'required' }} accept=".ttf,.otf,.woff,.woff2,.eot">
                    @error('font_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">TTF, OTF, WOFF, WOFF2, or EOT — max 10&nbsp;MB.</small>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Sort order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $font->sort_order ?? 0) }}" min="0">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $font->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active (visible in editor)</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ isset($font) ? 'Update' : 'Upload' }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
