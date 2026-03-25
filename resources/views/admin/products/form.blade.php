@extends('layouts.admin')

@section('title', isset($product) ? 'Edit Product' : 'Create Product')
@section('page-title', isset($product) ? 'Edit Product' : 'Create Product')

@section('content')
<div class="my-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ isset($product) ? route('admin.products.update', $product->id) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($product))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $product->slug ?? '') }}" required>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">URL-friendly identifier</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span for="description">Description</span>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="generateDescAndFaqBtn" title="Generate description and FAQ using AI"><i class="fas fa-magic me-1"></i>Generate description & FAQ</button>
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $product->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="generateDescAndFaqStatus" class="small mt-1" style="display: none;"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ \App\Models\Setting::get('currency_symbol', '$') }}</span>
                                        <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', isset($product) ? $product->price : '0') }}" required>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sku" class="form-label">SKU</label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku ?? '') }}" placeholder="e.g. PROD-001">
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span><i class="fas fa-question-circle me-1"></i>FAQ</span>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addFaqBtn"><i class="fas fa-plus me-1"></i>Add FAQ</button>
                                </div>
                            </label>
                            <small class="text-muted d-block mb-2">Add frequently asked questions and answers. Use "Generate description & FAQ" above to fill both at once.</small>
                            <div id="generateFaqStatus" class="small mb-2" style="display: none;"></div>
                            <div id="faqList">
                                @php
                                    $faqs = old('faqs', isset($product) && is_array($product->faqs) ? $product->faqs : []);
                                    if (is_array(old('faq_questions')) && count(old('faq_questions')) > 0) {
                                        $faqs = array_map(function($q, $a) { return ['question' => $q ?? '', 'answer' => $a ?? '']; }, old('faq_questions', []), old('faq_answers', []));
                                    }
                                    if (empty($faqs)) {
                                        $faqs = [['question' => '', 'answer' => '']];
                                    }
                                @endphp
                                @foreach($faqs as $index => $faq)
                                    <div class="faq-item input-group mb-2">
                                        <input type="text" name="faq_questions[]" class="form-control" placeholder="Question" value="{{ is_array($faq) ? ($faq['question'] ?? '') : '' }}">
                                        <input type="text" name="faq_answers[]" class="form-control" placeholder="Answer" value="{{ is_array($faq) ? ($faq['answer'] ?? '') : '' }}">
                                        <button type="button" class="btn btn-outline-danger remove-faq" title="Remove"><i class="fas fa-times"></i></button>
                                    </div>
                                @endforeach
                            </div>
                            <template id="faqItemTemplate">
                                <div class="faq-item input-group mb-2">
                                    <input type="text" name="faq_questions[]" class="form-control" placeholder="Question">
                                    <input type="text" name="faq_answers[]" class="form-control" placeholder="Answer">
                                    <button type="button" class="btn btn-outline-danger remove-faq" title="Remove"><i class="fas fa-times"></i></button>
                                </div>
                            </template>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">JPEG, PNG, GIF or WebP. Max 2MB. {{ isset($product) ? 'Leave empty to keep current image.' : 'Optional.' }}</small>
                            @if(isset($product) && $product->image_url)
                                <div class="mt-2">
                                    <img src="{{ $product->image_url }}" alt="Current product" style="max-height: 120px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? '0') }}">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Lower numbers appear first</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input type="hidden" name="is_active" value="0">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.products') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ isset($product) ? 'Update' : 'Create' }} Product
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
                        Products can be used for catalog, orders, or custom logic. Name and slug are required. Price uses the site currency symbol from Settings. SKU is optional. Only active products can be shown on the storefront if you use them there.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.autoGenerated = 'false';
    });

    document.getElementById('addFaqBtn').addEventListener('click', function() {
        const tpl = document.getElementById('faqItemTemplate');
        document.getElementById('faqList').appendChild(tpl.content.cloneNode(true));
    });
    document.getElementById('faqList').addEventListener('click', function(e) {
        if (e.target.closest('.remove-faq')) {
            e.target.closest('.faq-item').remove();
        }
    });

    document.getElementById('generateDescAndFaqBtn').addEventListener('click', function() {
        const btn = this;
        const name = (document.getElementById('name') && document.getElementById('name').value || '').trim();
        const description = (document.getElementById('description') && document.getElementById('description').value || '').trim();
        const statusEl = document.getElementById('generateDescAndFaqStatus');
        const faqStatusEl = document.getElementById('generateFaqStatus');
        if (!name) {
            statusEl.textContent = 'Please enter a product name first.';
            statusEl.style.display = 'block';
            statusEl.className = 'small mt-1 text-danger';
            return;
        }
        btn.disabled = true;
        statusEl.textContent = 'Generating description and FAQ…';
        statusEl.style.display = 'block';
        statusEl.className = 'small mt-1 text-muted';
        if (faqStatusEl) faqStatusEl.style.display = 'none';
        fetch('{{ route("admin.products.generateDescriptionAndFaq") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name: name, description: description })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            statusEl.style.display = 'block';
            if (data.success) {
                if (data.description != null && document.getElementById('description')) {
                    document.getElementById('description').value = data.description;
                }
                if (data.faqs && data.faqs.length > 0) {
                    function escAttr(s) {
                        return String(s || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    }
                    const list = document.getElementById('faqList');
                    list.innerHTML = '';
                    data.faqs.forEach(function(faq) {
                        const div = document.createElement('div');
                        div.className = 'faq-item input-group mb-2';
                        div.innerHTML = '<input type="text" name="faq_questions[]" class="form-control" placeholder="Question" value="' + escAttr(faq.question) + '">' +
                            '<input type="text" name="faq_answers[]" class="form-control" placeholder="Answer" value="' + escAttr(faq.answer) + '">' +
                            '<button type="button" class="btn btn-outline-danger remove-faq" title="Remove"><i class="fas fa-times"></i></button>';
                        list.appendChild(div);
                    });
                }
                var msg = 'Generated';
                if (data.description) msg += ' description';
                if (data.faqs && data.faqs.length) msg += (data.description ? ' and ' : '') + data.faqs.length + ' FAQ(s)';
                msg += '. You can edit and save.';
                statusEl.textContent = msg;
                statusEl.className = 'small mt-1 text-success';
            } else {
                statusEl.textContent = data.message || 'Could not generate.';
                statusEl.className = 'small mt-1 text-danger';
            }
        })
        .catch(function(err) {
            statusEl.textContent = 'Request failed. Try again.';
            statusEl.className = 'small mt-1 text-danger';
            statusEl.style.display = 'block';
        })
        .finally(function() {
            btn.disabled = false;
        });
    });
</script>
@endpush
@endsection
