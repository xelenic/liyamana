@extends('layouts.app')

@section('title', 'Create with ' . $template->name)

@push('styles')
<style>
    :root {
        --nb-primary: #6366f1;
        --nb-secondary: #8b5cf6;
        --nb-light: #f8fafc;
        --nb-border: #e2e8f0;
        --nb-dark: #1e293b;
    }
    .nb-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }
    .nb-hero h1 { font-size: 1.75rem; font-weight: 700; margin: 0 0 0.5rem 0; }
    .nb-hero .breadcrumb { background: transparent; padding: 0; margin: 0; }
    .nb-hero .breadcrumb-item { color: rgba(255,255,255,0.9); }
    .nb-hero .breadcrumb-item.active { color: white; }
    .nb-image-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        border: 1px solid var(--nb-border);
    }
    .nb-image-wrap {
        aspect-ratio: 4/3;
        background: var(--nb-light);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    .nb-image-wrap img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .nb-desc-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid var(--nb-border);
        margin-bottom: 1.5rem;
    }
    .nb-form-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid var(--nb-border);
    }
    .nb-form-card .form-label { font-weight: 600; color: var(--nb-dark); }
    .nb-type-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .nb-type-option {
        padding: 0.5rem 1rem;
        border: 2px solid var(--nb-border);
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .nb-type-option:hover { border-color: var(--nb-primary); background: rgba(99,102,241,0.05); }
    .nb-type-option.selected { border-color: var(--nb-primary); background: rgba(99,102,241,0.1); color: var(--nb-primary); }
    .nb-review-card {
        background: var(--nb-light);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        border: 1px solid var(--nb-border);
        margin-bottom: 1rem;
    }
    .nb-review-item { font-size: 0.875rem; color: #475569; margin-bottom: 0.25rem; }
    .nb-review-item strong { color: var(--nb-dark); }
    .nb-section-title { font-size: 1rem; font-weight: 700; color: var(--nb-dark); margin-bottom: 0.75rem; }
    .nb-comments-section { background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--nb-border); }
</style>
@endpush

@section('content')
<div class="nb-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('design.templates.explore') }}" class="text-white text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Back to Explore</a></li>
                <li class="breadcrumb-item active">{{ $template->name }}</li>
            </ol>
        </nav>
        <h1><i class="fas fa-magic me-2"></i>{{ $template->name }}</h1>
        <p class="mb-0 opacity-90" style="font-size: 0.95rem;">AI-powered design template</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        <div class="col-lg-4 order-lg-2 mb-4">
            <!-- Image -->
            <div class="nb-image-card sticky-top" style="top: 1rem;">
                <div class="nb-image-wrap">
                    @if($template->thumbnail_url)
                        <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}">
                    @else
                        <i class="fas fa-image fa-4x text-secondary"></i>
                    @endif
                </div>
                @if($cost > 0)
                <div class="p-3 border-top" style="background: #f8fafc;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted"><i class="fas fa-coins me-1"></i>Cost per image</span>
                        <span class="fw-bold">{{ format_price($cost) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="small text-muted">Your balance</span>
                        <span class="fw-bold">{{ format_price($balance) }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="col-lg-8 order-lg-1">
            <!-- Description -->
            @if($template->description)
            <div class="nb-desc-card">
                <h3 class="nb-section-title"><i class="fas fa-info-circle me-2" style="color: var(--nb-primary);"></i>About this template</h3>
                <div style="color: #475569; line-height: 1.7; font-size: 0.95rem;">{!! nl2br(e($template->description)) !!}</div>
            </div>
            @endif

            <!-- Form -->
            <div class="nb-form-card mb-4">
                <h3 class="nb-section-title"><i class="fas fa-feather-alt me-2" style="color: var(--nb-primary);"></i>Configure your design</h3>

                <form id="nbGenerateForm">
                    @csrf
                    <input type="hidden" name="template_id" value="{{ $template->id }}">
                    <input type="hidden" name="design_type" id="designTypeInput" value="flipbook">

                    @foreach($template->defined_fields ?? [] as $field)
                    <div class="mb-3">
                        <label class="form-label">{{ $field['label'] ?? $field['name'] }}{{ ($field['required'] ?? false) ? ' *' : '' }}</label>
                        @if(($field['type'] ?? 'text') === 'textarea')
                            <textarea name="fields[{{ $field['name'] }}]" class="form-control" rows="3" placeholder="Enter {{ $field['label'] ?? $field['name'] }}..." {{ ($field['required'] ?? false) ? 'required' : '' }}></textarea>
                        @elseif(($field['type'] ?? 'text') === 'select')
                            <select name="fields[{{ $field['name'] }}]" class="form-select" {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                <option value="">-- Select {{ $field['label'] ?? $field['name'] }} --</option>
                                @foreach($field['options'] ?? [] as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" name="fields[{{ $field['name'] }}]" class="form-control" value="{{ $field['options'][0] ?? '' }}" placeholder="Enter {{ $field['label'] ?? $field['name'] }}..." {{ ($field['required'] ?? false) ? 'required' : '' }}>
                        @endif
                    </div>
                    @endforeach

                    @if($template->upload_image)
                    <div class="mb-3" id="nbImageFieldWrap">
                        <label class="form-label">Image *</label>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <label class="btn btn-outline-primary btn-sm mb-0">
                                <i class="fas fa-upload me-1"></i>Upload image
                                <input type="file" name="image" id="nbImageInput" class="d-none" accept="image/*" required>
                            </label>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="nbTakePhotoBtn" onclick="openNbWebcam()">
                                <i class="fas fa-camera me-1"></i>Take photo
                            </button>
                        </div>
                        <small class="text-muted d-block">Required for this template. Upload a file or take a photo with your camera.</small>
                        <div id="nbImagePreview" class="mt-2" style="display: none;">
                            <img id="nbImagePreviewImg" src="" alt="Preview" style="max-height: 120px; border-radius: 8px; border: 1px solid var(--nb-border);">
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2 align-top" onclick="clearNbImage()">Remove</button>
                        </div>
                    </div>
                    <!-- Webcam modal for Take photo -->
                    <div id="nbWebcamModal" class="modal fade" tabindex="-1" aria-hidden="true" style="background: rgba(0,0,0,0.6);">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-camera me-2"></i>Take photo</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeNbWebcam()"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <video id="nbWebcamVideo" autoplay playsinline style="max-width: 100%; width: 100%; background: #000; border-radius: 8px;" width="640" height="480"></video>
                                    <p class="small text-muted mt-2 mb-0">Position yourself and click Capture.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" onclick="closeNbWebcam()">Cancel</button>
                                    <button type="button" class="btn btn-primary" id="nbCaptureBtn" onclick="captureNbPhoto()">
                                        <i class="fas fa-camera me-1"></i>Capture
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Design type -->
                    <div class="mb-4">
                        <label class="form-label">Output type</label>
                        <div class="nb-type-selector">
                            <div class="nb-type-option selected" data-type="flipbook">
                                <i class="fas fa-book"></i> Flipbook
                            </div>
                            <div class="nb-type-option" data-type="letter">
                                <i class="fas fa-envelope"></i> Letter
                            </div>
                            <div class="nb-type-option" data-type="document">
                                <i class="fas fa-file-alt"></i> Document
                            </div>
                            <div class="nb-type-option" data-type="poster">
                                <i class="fas fa-image"></i> Poster
                            </div>
                        </div>
                        <small class="text-muted">Choose how you want to use the generated image</small>
                    </div>

                    <!-- Review summary -->
                    <div class="nb-review-card mb-4" id="reviewSummary">
                        <div class="nb-section-title" style="margin-bottom: 0.5rem;"><i class="fas fa-clipboard-check me-2" style="color: var(--nb-primary);"></i>Summary</div>
                        <div class="nb-review-item">Template: <strong>{{ $template->name }}</strong></div>
                        <div class="nb-review-item">Output: <strong id="reviewOutputType">Flipbook</strong></div>
                        <div class="nb-review-item" id="reviewCost" @if($cost <= 0) style="display:none;" @endif>Cost: <strong>{{ format_price($cost) }}</strong></div>
                    </div>

                    <div id="nbError" class="alert alert-danger d-none" role="alert"></div>
                    <div id="nbProgress" class="d-none mb-3">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                        </div>
                        <p class="small text-muted mb-0 mt-2 text-center">Generating your image with AI...</p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3" id="nbSubmitBtn">
                        <i class="fas fa-magic me-2"></i>Generate with AI
                    </button>
                </form>
            </div>

            <!-- Comments & Reviews placeholder -->
            <div class="nb-comments-section">
                <h3 class="nb-section-title"><i class="fas fa-star me-2" style="color: var(--nb-primary);"></i>Reviews</h3>
                <p class="text-muted small mb-0" style="text-align: center; padding: 1.5rem;">
                    <i class="fas fa-sparkles me-1"></i>No reviews yet. Be the first to create with this template!
                </p>
            </div>
        </div>
    </div>
</div>

<script>
let nbWebcamStream = null;
let nbWebcamModalInstance = null;

function openNbWebcam() {
    const video = document.getElementById('nbWebcamVideo');
    const modalEl = document.getElementById('nbWebcamModal');
    if (!modalEl || !video) return;
    if (nbWebcamModalInstance == null && typeof bootstrap !== 'undefined') {
        nbWebcamModalInstance = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true });
    }
    modalEl.addEventListener('hidden.bs.modal', function onHide() {
        modalEl.removeEventListener('hidden.bs.modal', onHide);
        closeNbWebcam();
    }, { once: true });
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
        .then(function(stream) {
            nbWebcamStream = stream;
            video.srcObject = stream;
            if (nbWebcamModalInstance) nbWebcamModalInstance.show();
        })
        .catch(function(err) {
            alert('Could not access camera: ' + (err.message || 'Permission denied or no device.'));
        });
}

function closeNbWebcam() {
    if (nbWebcamStream) {
        nbWebcamStream.getTracks().forEach(function(t) { t.stop(); });
        nbWebcamStream = null;
    }
    const video = document.getElementById('nbWebcamVideo');
    if (video) video.srcObject = null;
    const modalEl = document.getElementById('nbWebcamModal');
    if (modalEl && nbWebcamModalInstance) nbWebcamModalInstance.hide();
}

function captureNbPhoto() {
    const video = document.getElementById('nbWebcamVideo');
    const input = document.getElementById('nbImageInput');
    if (!video || !input || !video.srcObject) return;
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);
    canvas.toBlob(function(blob) {
        if (!blob) return;
        const file = new File([blob], 'webcam-photo.jpg', { type: 'image/jpeg' });
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
        showNbPreview(file);
        closeNbWebcam();
    }, 'image/jpeg', 0.9);
}

function showNbPreview(file) {
    const wrap = document.getElementById('nbImagePreview');
    const img = document.getElementById('nbImagePreviewImg');
    if (!wrap || !img) return;
    if (!file) {
        wrap.style.display = 'none';
        return;
    }
    const url = URL.createObjectURL(file);
    img.src = url;
    wrap.style.display = 'block';
}

function clearNbImage() {
    const input = document.getElementById('nbImageInput');
    const wrap = document.getElementById('nbImagePreview');
    const img = document.getElementById('nbImagePreviewImg');
    if (input) {
        input.value = '';
        input.files = null;
    }
    if (img && img.src) URL.revokeObjectURL(img.src);
    if (img) img.src = '';
    if (wrap) wrap.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('nbGenerateForm');
    const designTypeInput = document.getElementById('designTypeInput');
    const typeOptions = document.querySelectorAll('.nb-type-option');
    const reviewOutputType = document.getElementById('reviewOutputType');
    const nbImageInput = document.getElementById('nbImageInput');

    if (nbImageInput) {
        nbImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) showNbPreview(this.files[0]);
        });
    }

    typeOptions.forEach(opt => {
        opt.addEventListener('click', function() {
            typeOptions.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            const type = this.dataset.type;
            designTypeInput.value = type;
            reviewOutputType.textContent = type.charAt(0).toUpperCase() + type.slice(1);
        });
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('nbSubmitBtn');
        const errEl = document.getElementById('nbError');
        const progress = document.getElementById('nbProgress');

        btn.disabled = true;
        errEl.classList.add('d-none');
        progress.classList.remove('d-none');

        const formData = new FormData(this);
        const designType = designTypeInput.value;

        try {
            const r = await fetch('{{ route("design.nanobanana.generate") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            const ct = r.headers.get('content-type') || '';
            let data;
            if (ct.includes('application/json')) {
                data = await r.json();
            } else {
                const text = await r.text();
                let msg = 'Server returned an error. ';
                if (r.status === 419) msg += 'Session expired. Please refresh and try again.';
                else if (r.status === 500) msg += 'Please try again or contact support.';
                else if (r.status === 404) msg += 'Page not found.';
                else if (r.status === 413) msg += 'Image too large. Use a smaller image.';
                else if (text.includes('CSRF') || text.includes('419')) msg += 'Session expired. Please refresh and try again.';
                else if (text.includes('500') || text.includes('Internal Server Error')) msg += 'Please try again or contact support.';
                else if (r.status) msg += '(HTTP ' + r.status + ')';
                else msg += 'Please try again or contact support.';
                throw new Error(msg);
            }
            if (!data.success) {
                throw new Error(data.message || 'Generation failed');
            }
            const imgUrl = data.image_url;
            if (imgUrl) {
                const baseUrl = '{{ route("design.create") }}';
                let url = baseUrl + '?multi=true&generated_image=' + encodeURIComponent(imgUrl) + '&type=' + encodeURIComponent(designType);
                const fields = form.querySelectorAll('[name^="fields["]');
                const vals = [];
                fields.forEach(f => { if (f.value && f.value.trim()) vals.push(f.value.trim()); });
                if (vals[0]) url += '&ai_title=' + encodeURIComponent(vals[0]);
                if (vals[1]) url += '&ai_subtitle=' + encodeURIComponent(vals[1]);
                window.location.href = url;
                return;
            }
            throw new Error('No image returned');
        } catch (e) {
            errEl.classList.remove('d-none');
            errEl.textContent = e.message || 'Something went wrong';
        } finally {
            btn.disabled = false;
            progress.classList.add('d-none');
        }
    });
});
</script>
@endsection
