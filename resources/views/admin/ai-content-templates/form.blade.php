@extends('layouts.admin')

@section('title', isset($template) ? 'Edit AI Content Template' : 'Create AI Content Template')
@section('page-title', isset($template) ? 'Edit AI Content Template' : 'Create AI Content Template')

@section('content')
<div class="my-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ isset($template) ? route('admin.ai-content-templates.update', $template->id) : route('admin.ai-content-templates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($template))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $template->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Display name for the template</small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description', $template->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Optional description of the template</small>
                        </div>

                        <div class="mb-3">
                            <label for="prompt" class="form-label">Prompt <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('prompt') is-invalid @enderror" id="prompt" name="prompt" rows="4" required>{{ old('prompt', $template->prompt ?? '') }}</textarea>
                            @error('prompt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @verbatim
<small class="text-muted">AI prompt for content generation. Use {{ field_name }} for dynamic field placeholders.</small>
@endverbatim
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Fields</label>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleFieldsMode" title="Switch to JSON editor">
                                    <i class="fas fa-code me-1"></i><span id="fieldsModeLabel">Show JSON</span>
                                </button>
                            </div>
                            <div id="fieldsBuilder">
                                <div id="fieldsList" class="mb-2"></div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addFieldBtn">
                                    <i class="fas fa-plus me-1"></i>Add Field
                                </button>
                            </div>
                            <div id="fieldsJsonWrap" style="display: none;">
                                <textarea class="form-control font-monospace @error('fields') is-invalid @enderror" id="fieldsJson" rows="6" placeholder='[{"key": "title", "label": "Title", "type": "text"}]'></textarea>
                            </div>
                            <input type="hidden" name="fields" id="fieldsHidden" value="{{ old('fields', isset($template) && $template->fields ? json_encode($template->fields) : '[]') }}">
                            @error('fields')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @verbatim
<small class="text-muted">Input fields for the AI form. Key is used in prompts as {{ key }}.</small>
@endverbatim
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">JPEG, PNG, GIF, WebP. Max 2MB. Optional.</small>
                            @if(isset($template) && $template->image_url)
                            <div class="mt-2">
                                <img src="{{ $template->image_url }}" alt="Current image" class="img-thumbnail" style="max-height: 120px;">
                                <small class="d-block text-muted mt-1">Current image. Upload new to replace.</small>
                            </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="editor_json" class="form-label">Editor JSON Structure</label>
                            <textarea class="form-control font-monospace @error('editor_json') is-invalid @enderror" id="editor_json" name="editor_json" rows="12" placeholder='{"version": 1, "pages": [{"data": "..."} ]}' style="font-size: 0.8rem;">{{ old('editor_json', isset($template) && $template->editor_json ? json_encode($template->editor_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                            @error('editor_json')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Design/editor structure (version, pages with Fabric.js data). Export from multi-page editor in debug mode.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $template->sort_order ?? '0') }}">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Lower numbers appear first</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                                <small class="text-muted d-block mt-1">Only active templates are available</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.ai-content-templates') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ isset($template) ? 'Update' : 'Create' }} Template
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
                        <strong>Prompt:</strong>
                        @verbatim
<p class="text-muted small mb-0">The AI prompt sent to the content generation API. Use placeholders like {{ title }} or {{ subtitle }} that match field keys.</p>
@endverbatim
                    </div>
                    <div class="mb-3">
                        <strong>Fields:</strong>
                        <p class="text-muted small mb-0">JSON array defining input fields for the AI form. Each object: key, label, type (text, textarea, etc.).</p>
                    </div>
                    <div class="mb-3">
                        <strong>Editor JSON:</strong>
                        @verbatim
<p class="text-muted small mb-0">The layout structure for the design editor. Format: {"version": 1, "pages": [{"data": "<fabric json>"}]}. Export from the multi-page editor (File → Export JSON) when APP_DEBUG=true.</p>
@endverbatim
                    </div>
                    <div>
                        <strong>Image:</strong>
                        <p class="text-muted small mb-0">Preview/thumbnail image for the template in selection UIs.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@verbatim
<script>
(function() {
    const FIELD_TYPES = [
        { value: 'text', label: 'Text' },
        { value: 'textarea', label: 'Textarea' },
        { value: 'number', label: 'Number' },
        { value: 'email', label: 'Email' },
        { value: 'url', label: 'URL' }
    ];

    function getFieldsData() {
        const val = document.getElementById('fieldsHidden').value;
        if (!val || val === '[]') return [];
        try {
            const arr = JSON.parse(val);
            return Array.isArray(arr) ? arr : [];
        } catch (e) {
            return [];
        }
    }

    function setFieldsData(arr) {
        document.getElementById('fieldsHidden').value = JSON.stringify(arr);
    }

    function escapeAttr(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function buildFieldRow(field, index) {
        const div = document.createElement('div');
        div.className = 'fields-row d-flex gap-2 mb-2 align-items-center';
        div.dataset.index = index;
        div.innerHTML = `
            <input type="text" class="form-control form-control-sm" style="flex: 0 0 120px;" placeholder="Key" value="${escapeAttr(field.key)}" data-key title="Reference in prompt using this key">
            <input type="text" class="form-control form-control-sm" style="flex: 1;" placeholder="Label" value="${escapeAttr(field.label)}" data-label>
            <select class="form-select form-select-sm" style="flex: 0 0 120px;" data-type>
                ${FIELD_TYPES.map(t => `<option value="${t.value}" ${(field.type || 'text') === t.value ? 'selected' : ''}>${t.label}</option>`).join('')}
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger" title="Remove"><i class="fas fa-trash"></i></button>
        `;
        div.querySelector('button').onclick = () => removeField(index);
        div.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('change', syncFieldsToHidden);
            el.addEventListener('input', syncFieldsToHidden);
        });
        return div;
    }

    function syncFieldsToHidden() {
        const rows = document.querySelectorAll('#fieldsList .fields-row');
        const arr = [];
        rows.forEach(row => {
            const key = (row.querySelector('[data-key]').value || '').trim();
            const label = (row.querySelector('[data-label]').value || '').trim();
            const type = row.querySelector('[data-type]').value || 'text';
            if (key) arr.push({ key, label: label || key, type });
        });
        setFieldsData(arr);
    }

    function removeField(index) {
        document.querySelector(`#fieldsList .fields-row[data-index="${index}"]`)?.remove();
        document.querySelectorAll('#fieldsList .fields-row').forEach((r, i) => r.dataset.index = i);
        syncFieldsToHidden();
    }

    function renderFields() {
        const list = document.getElementById('fieldsList');
        list.innerHTML = '';
        const data = getFieldsData();
        data.forEach((f, i) => list.appendChild(buildFieldRow(f, i)));
    }

    function toggleMode() {
        const builder = document.getElementById('fieldsBuilder');
        const jsonWrap = document.getElementById('fieldsJsonWrap');
        const label = document.getElementById('fieldsModeLabel');
        const jsonTa = document.getElementById('fieldsJson');

        if (builder.style.display === 'none') {
            builder.style.display = '';
            jsonWrap.style.display = 'none';
            label.textContent = 'Show JSON';
            try {
                const arr = JSON.parse(jsonTa.value || '[]');
                setFieldsData(Array.isArray(arr) ? arr : []);
                renderFields();
            } catch (e) {}
        } else {
            jsonTa.value = JSON.stringify(getFieldsData(), null, 2);
            builder.style.display = 'none';
            jsonWrap.style.display = '';
            label.textContent = 'Use Builder';
        }
    }

    document.getElementById('addFieldBtn').onclick = function() {
        const list = document.getElementById('fieldsList');
        const index = list.querySelectorAll('.fields-row').length;
        list.appendChild(buildFieldRow({ key: '', label: '', type: 'text' }, index));
    };

    document.getElementById('toggleFieldsMode').onclick = toggleMode;

    document.querySelector('form').addEventListener('submit', function() {
        if (document.getElementById('fieldsJsonWrap').style.display !== 'none') {
            const val = document.getElementById('fieldsJson').value.trim();
            document.getElementById('fieldsHidden').value = val || '[]';
        } else {
            syncFieldsToHidden();
        }
    });

    renderFields();

    // Editor JSON validation
    const editorEl = document.getElementById('editor_json');
    if (editorEl) {
        editorEl.addEventListener('blur', function() {
            const val = this.value.trim();
            if (!val) return;
            try {
                JSON.parse(val);
                this.classList.remove('is-invalid');
            } catch (e) {
                this.classList.add('is-invalid');
            }
        });
    }
})();
</script>
@endverbatim
@endpush
@endsection
