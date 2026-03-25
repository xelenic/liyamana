<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $template->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Prompt</label>
    <textarea name="prompt" class="form-control" rows="4" required>{{ old('prompt', $template->prompt ?? '') }}</textarea>
    <small class="text-muted">Use @{{fieldName}} for placeholder substitution</small>
</div>
<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="2">{{ old('description', $template->description ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Defined Fields</label>
    <small class="text-muted d-block mb-2">Fields shown to users when generating. Use @{{fieldName}} in the prompt above.</small>
    <input type="hidden" name="defined_fields" id="definedFieldsJson" value="">
    <div id="definedFieldsContainer"></div>
    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addDefinedField">
        <i class="fas fa-plus me-1"></i>Add Field
    </button>
</div>

<script>
(function() {
    @php
        $defFields = $template->defined_fields ?? [['name'=>'subject','label'=>'Subject','type'=>'text','required'=>true]];
        if ($old = old('defined_fields')) {
            $defFields = is_string($old) ? json_decode($old, true) : $old;
            if (!is_array($defFields)) $defFields = [];
        }
    @endphp
    const defaultFields = @json($defFields);
    const container = document.getElementById('definedFieldsContainer');
    const jsonInput = document.getElementById('definedFieldsJson');

    function updateJson() {
        const fields = [];
        container.querySelectorAll('.defined-field-card').forEach((card, i) => {
            const name = card.querySelector('.field-name').value.trim();
            const label = card.querySelector('.field-label').value.trim();
            const type = card.querySelector('.field-type').value;
            const required = card.querySelector('.field-required').checked;
            const optionsEl = card.querySelector('.field-options');
            const options = optionsEl ? optionsEl.value.split(',').map(s => s.trim()).filter(Boolean) : [];
            if (name) {
                fields.push({ name, label: label || name, type, required, options: type === 'select' ? options : [] });
            }
        });
        jsonInput.value = JSON.stringify(fields);
    }

    function createFieldCard(data = {}) {
        const card = document.createElement('div');
        card.className = 'defined-field-card card border mb-2';
        card.innerHTML = `
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-secondary">Field</span>
                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-1 btn-remove-field">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm field-name" placeholder="Name (e.g. subject)" value="${(data.name || '').replace(/"/g, '&quot;')}" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm field-label" placeholder="Label" value="${(data.label || '').replace(/"/g, '&quot;')}">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select form-select-sm field-type">
                            <option value="text" ${data.type === 'text' ? 'selected' : ''}>Text</option>
                            <option value="textarea" ${data.type === 'textarea' ? 'selected' : ''}>Textarea</option>
                            <option value="select" ${data.type === 'select' ? 'selected' : ''}>Select</option>
                        </select>
                    </div>
                </div>
                <div class="row g-2 mt-1">
                    <div class="col-md-6">
                        <label class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input field-required" ${data.required !== false ? 'checked' : ''}>
                            <span class="form-check-label small">Required</span>
                        </label>
                    </div>
                    <div class="col-md-6 field-options-wrap" style="display:${data.type === 'select' ? 'block' : 'none'}">
                        <input type="text" class="form-control form-control-sm field-options" placeholder="Options (comma-separated)" value="${(data.options || []).join(', ')}">
                    </div>
                </div>
            </div>
        `;
        card.querySelector('.field-type').addEventListener('change', function() {
            card.querySelector('.field-options-wrap').style.display = this.value === 'select' ? 'block' : 'none';
            updateJson();
        });
        ['field-name','field-label','field-options'].forEach(cls => {
            const el = card.querySelector('.' + cls);
            if (el) el.addEventListener('input', updateJson);
        });
        card.querySelector('.field-required').addEventListener('change', updateJson);
        return card;
    }

    container.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-field')) {
            e.target.closest('.defined-field-card').remove();
            updateJson();
        }
    });

    document.getElementById('addDefinedField').addEventListener('click', function() {
        container.appendChild(createFieldCard({ name: '', label: '', type: 'text', required: true }));
        updateJson();
    });

    (Array.isArray(defaultFields) ? defaultFields : []).forEach(f => container.appendChild(createFieldCard(f)));
    if (container.children.length === 0) container.appendChild(createFieldCard({ name: 'subject', label: 'Subject', type: 'text', required: true }));
    updateJson();
})();
</script>
<div class="mb-3">
    <label class="form-label">Thumbnail Image</label>
    <input type="file" name="image" class="form-control" accept="image/*">
    @if(isset($template) && $template->thumbnail_url)
        <div class="mt-2"><img src="{{ $template->thumbnail_url }}" alt="" style="max-height: 80px; border-radius: 6px;"></div>
    @endif
</div>
<div class="mb-3">
    <div class="form-check form-switch">
        <input type="hidden" name="upload_image" value="0">
        <input type="checkbox" name="upload_image" value="1" class="form-check-input" {{ old('upload_image', $template->upload_image ?? false) ? 'checked' : '' }}>
        <label class="form-check-label">Require user to upload an image</label>
    </div>
</div>
<div class="mb-3">
    <div class="form-check form-switch">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label">Active</label>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Sort Order</label>
    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $template->sort_order ?? 0) }}" min="0">
</div>
