@extends('layouts.admin')

@section('title', 'Templates Management')
@section('page-title', 'Templates Management')

@section('content')
<div class="my-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-th-large me-2 text-primary"></i>Templates Management
            </h2>
            <p class="text-muted mb-0">Manage public design templates</p>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.templates') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, description, or category..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Thumbnail</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Name</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Category</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Created By</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Featured</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Created</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                        <tr data-template-id="{{ $template->id }}">
                            <td style="padding: 1rem;">
                                @if($template->thumbnail_url)
                                    <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}" class="template-thumb-img" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                                @else
                                    <div class="template-thumb-placeholder" style="width: 60px; height: 60px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                <div style="font-size: 0.9rem; font-weight: 500; color: #1e293b;">{{ $template->name }}</div>
                                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.2rem;"><i class="fas fa-file-alt me-1"></i>{{ $template->page_count }} {{ Str::plural('page', $template->page_count) }}</div>
                                @if($template->description)
                                    <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 0.2rem;">{{ Str::limit($template->description, 50) }}</div>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem;">
                                <span class="badge bg-info" style="font-size: 0.75rem;">{{ ucfirst($template->category) }}</span>
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">
                                {{ $template->creator->name ?? 'System' }}
                            </td>
                            <td style="padding: 1rem;">
                                @if($template->is_active)
                                    <span class="badge bg-success" style="font-size: 0.75rem;">Active</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 0.75rem;">Inactive</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                <form action="{{ route('admin.templates.toggle-featured', $template->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <div class="form-check form-switch mb-0 d-inline-block">
                                        <input class="form-check-input" type="checkbox" role="switch" {{ $template->is_featured ? 'checked' : '' }} onchange="this.form.submit()" title="{{ $template->is_featured ? 'Featured' : 'Not featured' }}">
                                    </div>
                                </form>
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">{{ $template->created_at->format('M d, Y') }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <div class="btn-group btn-group-sm" role="group" style="font-size: 0.65rem;">
                                    <a href="{{ route('admin.templates.manage', $template->id) }}" class="btn btn-sm btn-outline-primary py-0 px-1" style="font-size: 0.65rem; min-width: 26px;" title="Manage (view details)">
                                        <i class="fas fa-cog" style="font-size: 0.7rem;"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 btn-thumbnail-generate" style="font-size: 0.65rem; min-width: 26px;" title="Generate thumbnail with AI"
                                        data-template-id="{{ $template->id }}"
                                        data-template-name="{{ e($template->name) }}"
                                        data-template-thumb-url="{{ $template->thumbnail_url ? e($template->thumbnail_url) : '' }}"
                                        data-assigned-products="{{ $template->products->isEmpty() ? '[]' : $template->products->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->toJson() }}">
                                        <i class="fas fa-magic" style="font-size: 0.7rem;"></i>
                                    </button>
                                    <form action="{{ route('admin.templates.delete', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1 rounded-0 rounded-end" style="font-size: 0.65rem; min-width: 26px;" title="Delete">
                                            <i class="fas fa-trash" style="font-size: 0.7rem;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No public templates found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination (preserves search, category, status, visibility) -->
    @if($templates->hasPages())
    <div class="mt-4 d-flex justify-content-center justify-content-md-start">
        {{ $templates->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<!-- Generate Thumbnail Modal -->
<div class="modal fade" id="thumbnailModal" tabindex="-1" aria-labelledby="thumbnailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="thumbnailModalLabel">
                    <i class="fas fa-magic me-2"></i>Generate thumbnail with AI
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">Template: <strong id="modalTemplateName"></strong></p>
                <div class="mb-3">
                    <label class="form-label">Current thumbnail</label>
                    <div id="modalCurrentThumb" class="border rounded p-2 bg-light text-center" style="min-height: 80px;">
                        <img id="modalCurrentThumbImg" src="" alt="Current" style="max-width: 120px; max-height: 80px; object-fit: contain;" class="d-none">
                        <span id="modalNoThumb" class="text-muted small">No thumbnail yet</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="thumbnailProductId" class="form-label">Attached product (optional)</label>
                    <select id="thumbnailProductId" class="form-select">
                        <option value="">— Template only (no product) —</option>
                        <!-- Options filled by JS from data-assigned-products -->
                    </select>
                    <small class="text-muted">If selected, AI will generate a thumbnail combining the template image with this product's image.</small>
                </div>
                <div class="mb-3">
                    <label for="thumbnailPromptId" class="form-label">Prompt <span class="text-danger">*</span></label>
                    <select id="thumbnailPromptId" class="form-select">
                        <option value="">— Select a prompt —</option>
                        @foreach($thumbnailPrompts as $tp)
                            <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Manage prompts in <a href="{{ route('admin.thumbnail-prompts') }}" target="_blank">Thumbnail Prompts</a>. Generation uses template image (and product image if selected) + prompt.</small>
                </div>
                <div id="thumbnailModalError" class="alert alert-danger py-2 small mb-0" style="display: none;"></div>
                <div id="thumbnailModalSpinner" class="text-center py-2" style="display: none;">
                    <span class="spinner-border spinner-border-sm me-2"></span>Generating...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="thumbnailGenerateBtn" onclick="submitThumbnailGenerate()">
                    <i class="fas fa-magic me-1"></i>Generate
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
var thumbnailModalTemplateId = null;

document.querySelectorAll('.btn-thumbnail-generate').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var productsJson = this.getAttribute('data-assigned-products') || '[]';
        var products = [];
        try { products = JSON.parse(productsJson); } catch (e) {}
        openThumbnailModal(
            this.getAttribute('data-template-id'),
            this.getAttribute('data-template-name') || '',
            this.getAttribute('data-template-thumb-url') || '',
            products
        );
    });
});

function openThumbnailModal(templateId, templateName, thumbnailUrl, assignedProducts) {
    assignedProducts = assignedProducts || [];
    thumbnailModalTemplateId = templateId;
    document.getElementById('modalTemplateName').textContent = templateName || ('Template #' + templateId);
    document.getElementById('thumbnailPromptId').value = '';
    document.getElementById('thumbnailModalError').style.display = 'none';
    document.getElementById('thumbnailModalSpinner').style.display = 'none';
    document.getElementById('thumbnailGenerateBtn').disabled = false;

    var productSelect = document.getElementById('thumbnailProductId');
    productSelect.innerHTML = '<option value="">— Template only (no product) —</option>';
    assignedProducts.forEach(function(p) {
        var opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = p.name;
        productSelect.appendChild(opt);
    });
    var productRow = productSelect.closest('.mb-3');
    if (productRow) productRow.style.display = assignedProducts.length ? 'block' : 'none';

    var imgEl = document.getElementById('modalCurrentThumbImg');
    var noThumbEl = document.getElementById('modalNoThumb');
    if (thumbnailUrl) {
        imgEl.src = thumbnailUrl;
        imgEl.classList.remove('d-none');
        noThumbEl.classList.add('d-none');
    } else {
        imgEl.classList.add('d-none');
        noThumbEl.classList.remove('d-none');
    }

    var modal = new bootstrap.Modal(document.getElementById('thumbnailModal'));
    modal.show();
}

function submitThumbnailGenerate() {
    if (!thumbnailModalTemplateId) return;
    var promptId = document.getElementById('thumbnailPromptId').value;
    if (!promptId) {
        document.getElementById('thumbnailModalError').textContent = 'Please select a prompt.';
        document.getElementById('thumbnailModalError').style.display = 'block';
        return;
    }

    document.getElementById('thumbnailModalError').style.display = 'none';
    document.getElementById('thumbnailModalSpinner').style.display = 'block';
    document.getElementById('thumbnailGenerateBtn').disabled = true;

    var productId = document.getElementById('thumbnailProductId').value;
    var url = '{{ route("admin.templates.generate-thumbnail", [0]) }}'.replace('/0/', '/' + thumbnailModalTemplateId + '/');
    var formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('prompt_id', promptId);
    if (productId) formData.append('product_id', productId);

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
    .then(function(result) {
        document.getElementById('thumbnailModalSpinner').style.display = 'none';
        document.getElementById('thumbnailGenerateBtn').disabled = false;

        if (result.ok && result.data.success) {
            bootstrap.Modal.getInstance(document.getElementById('thumbnailModal')).hide();
            if (result.data.queued) {
                alert(result.data.message || 'Thumbnail generation queued. Refresh the page in a few seconds.');
                setTimeout(function() { window.location.reload(); }, 2000);
            } else if (result.data.thumbnail_url) {
                var row = document.querySelector('tr[data-template-id="' + thumbnailModalTemplateId + '"]');
                if (row) {
                    var td = row.querySelector('td');
                    if (td) {
                        var placeholder = td.querySelector('.template-thumb-placeholder');
                        if (placeholder) {
                            var img = document.createElement('img');
                            img.src = result.data.thumbnail_url;
                            img.alt = 'Thumbnail';
                            img.className = 'template-thumb-img';
                            img.style.cssText = 'width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;';
                            placeholder.replaceWith(img);
                        } else {
                            var img = td.querySelector('.template-thumb-img');
                            if (img) img.src = result.data.thumbnail_url;
                        }
                    }
                }
            }
        } else {
            document.getElementById('thumbnailModalError').textContent = result.data.message || 'Failed to generate thumbnail.';
            document.getElementById('thumbnailModalError').style.display = 'block';
        }
    })
    .catch(function(err) {
        document.getElementById('thumbnailModalSpinner').style.display = 'none';
        document.getElementById('thumbnailGenerateBtn').disabled = false;
        document.getElementById('thumbnailModalError').textContent = 'Request failed: ' + (err.message || 'Unknown error');
        document.getElementById('thumbnailModalError').style.display = 'block';
    });
}
</script>
@endpush
@endsection





