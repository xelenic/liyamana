@extends('layouts.admin')

@section('title', isset($prompt) ? 'Edit Thumbnail Prompt' : 'Create Thumbnail Prompt')
@section('page-title', isset($prompt) ? 'Edit Thumbnail Prompt' : 'Create Thumbnail Prompt')

@section('content')
<div class="my-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ isset($prompt) ? route('admin.thumbnail-prompts.update', $prompt->id) : route('admin.thumbnail-prompts.store') }}" method="POST">
                @csrf
                @if(isset($prompt)) @method('PUT') @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $prompt->name ?? '') }}" required maxlength="255" placeholder="e.g. Modern blue gradient">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Label shown in the dropdown on Templates Management</small>
                </div>

                <div class="mb-3">
                    <label for="prompt" class="form-label">Prompt <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('prompt') is-invalid @enderror" id="prompt" name="prompt" rows="4" required maxlength="5000" placeholder="e.g. Generate a modern thumbnail with blue gradient background and bold white title text.">{{ old('prompt', $prompt->prompt ?? '') }}</textarea>
                    @error('prompt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Instruction sent to Gemini to generate the thumbnail from the current image</small>
                </div>

                <div class="mb-3">
                    <label for="sort_order" class="form-label">Sort order</label>
                    <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $prompt->sort_order ?? 0) }}">
                    @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Lower numbers appear first in the dropdown</small>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.thumbnail-prompts') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ isset($prompt) ? 'Update' : 'Create' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
