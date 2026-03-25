@extends('layouts.admin')

@section('title', isset($documentation) ? 'Edit Documentation' : 'Create Documentation')
@section('page-title', isset($documentation) ? 'Edit Documentation' : 'Create Documentation')

@section('content')
<div class="my-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ isset($documentation) ? route('admin.documentation.update', $documentation->id) : route('admin.documentation.store') }}" method="POST">
                        @csrf
                        @if(isset($documentation))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $documentation->title ?? '') }}" placeholder="e.g. Getting Started" required maxlength="255">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $documentation->slug ?? '') }}" placeholder="e.g. getting-started (leave blank to auto-generate)" maxlength="255">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">URL-friendly identifier. Leave blank to generate from title.</small>
                        </div>

                        <div class="mb-3">
                            <label for="category_ids" class="form-label">Categories</label>
                            <select class="form-select @error('category_ids') is-invalid @enderror" id="category_ids" name="category_ids[]" multiple size="6">
                                @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->id }}" {{ in_array($cat->id, old('category_ids', isset($documentation) ? $documentation->categories->pluck('id')->toArray() : [])) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple. Manage categories in <a href="{{ route('admin.documentation-categories') }}">Documentation Categories</a>.</small>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <div id="content-editor-container" class="border rounded bg-white @error('content') border-danger @enderror"></div>
                            <textarea class="form-control d-none @error('content') is-invalid @enderror" id="content" name="content" rows="1">{{ old('content', $documentation->content ?? '') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort order</label>
                                    <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $documentation->sort_order ?? 0) }}">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Lower numbers appear first.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input type="hidden" name="is_published" value="0">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', $documentation->is_published ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_published">Published (visible to users)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.documentation') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ isset($documentation) ? 'Update' : 'Create' }} Documentation
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
                        Documentation pages can be used for help content, FAQs, or API docs. Use category to group pages. Only published items are visible on the frontend if you add a docs viewer.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
#content-editor-container .ql-editor { min-height: 320px; }
#content-editor-container .ql-container { font-size: 14px; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var contentInput = document.getElementById('content');
    if (!contentInput) return;
    var container = document.getElementById('content-editor-container');
    if (!container) return;

    var quill = new Quill(container, {
        theme: 'snow',
        placeholder: 'Documentation content...',
        modules: {
            toolbar: [
                [{ 'header': [2, 3, 4, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'blockquote', 'code-block'],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    });
    quill.root.innerHTML = contentInput.value;

    var form = contentInput.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            contentInput.value = quill.root.innerHTML;
        });
    }
});
</script>
@endpush
@endsection
