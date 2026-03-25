@extends('layouts.admin')

@section('title', isset($step) ? 'Edit Intro Step' : 'Add Intro Step')
@section('page-title', isset($step) ? 'Edit Intro Step' : 'Add Intro Step')

@section('content')
@php
    $tour = $tour ?? ($step->tour_slug ?? 'multi_page_editor');
    $isExplore = $tour === 'templates_explore';
    $selectorPlaceholder = $isExplore ? 'e.g. #exploreFilterSection or #exploreSidebar or #templatesContainer' : 'e.g. #designToolbar or #leftSidebar or #canvasContainer or #propertiesPanel';
@endphp
<div class="my-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ isset($step) ? route('admin.design-intro.steps.update', $step->id) : route('admin.design-intro.steps.store') }}" method="POST">
                @csrf
                @if(isset($step)) @method('PUT') @endif
                @if(!isset($step))
                <input type="hidden" name="tour_slug" value="{{ $tour }}">
                @endif

                <div class="mb-3">
                    <label for="element_selector" class="form-label">Element selector (optional)</label>
                    <input type="text" class="form-control @error('element_selector') is-invalid @enderror" id="element_selector" name="element_selector" value="{{ old('element_selector', $step->element_selector ?? '') }}" placeholder="{{ $selectorPlaceholder }}">
                    @error('element_selector')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">CSS selector to highlight. Leave empty for intro-only. {{ $isExplore ? 'Explore: #exploreFilterSection, #exploreSidebar, #templatesContainer' : 'Design tool: #designToolbar, #leftSidebar, #canvasContainer, #propertiesPanel' }}</small>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Title (optional)</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $step->title ?? '') }}" maxlength="255" placeholder="e.g. Toolbar">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="intro_text" class="form-label">Intro text <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('intro_text') is-invalid @enderror" id="intro_text" name="intro_text" rows="4" required maxlength="10000" placeholder="Content shown in the tour popover. You can use HTML e.g. <strong>bold</strong>.">{{ old('intro_text', $step->intro_text ?? '') }}</textarea>
                    @error('intro_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">HTML allowed (e.g. &lt;strong&gt;, &lt;br&gt;)</small>
                </div>

                <div class="mb-3">
                    <label for="sort_order" class="form-label">Sort order</label>
                    <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $step->sort_order ?? 0) }}">
                    @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Lower numbers appear first in the tour</small>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.design-intro') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ isset($step) ? 'Update' : 'Add' }} step</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
