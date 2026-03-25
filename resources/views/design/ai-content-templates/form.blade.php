@extends('layouts.app')

@section('title', $template->name . ' — AI template')
@section('page-title', 'AI content template')

@php
    $aiGenFlatCredit = \App\Services\AiContentCreditService::computeCost(null);
@endphp

@push('styles')
<style>
    .ai-ct-form-page { --ai-accent: #6366f1; --ai-accent-2: #8b5cf6; }
    .ai-ct-form-hero {
        background: linear-gradient(125deg, #f8fafc 0%, #eef2ff 55%, #f5f3ff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.25rem 1.35rem;
        margin-bottom: 1.25rem;
    }
    @media (min-width: 768px) {
        .ai-ct-form-hero { padding: 1.5rem 1.75rem; }
    }
    .ai-ct-form-breadcrumb {
        font-size: 0.75rem;
        margin-bottom: 1rem;
    }
    .ai-ct-form-breadcrumb a { color: #64748b; text-decoration: none; }
    .ai-ct-form-breadcrumb a:hover { color: var(--ai-accent); }
    .ai-ct-form-breadcrumb span { color: #94a3b8; margin: 0 0.35rem; }
    .ai-ct-form-breadcrumb .current { color: #334155; font-weight: 600; }
    .ai-ct-form-layout { display: grid; gap: 1.25rem; }
    @media (min-width: 992px) {
        .ai-ct-form-layout { grid-template-columns: 1fr 280px; align-items: start; }
    }
    .ai-ct-form-main {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .ai-ct-form-main__inner { padding: 1.35rem 1.35rem 1.5rem; }
    @media (min-width: 768px) {
        .ai-ct-form-main__inner { padding: 1.75rem 2rem 2rem; }
    }
    .ai-ct-form-section-title {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        margin-bottom: 1rem;
    }
    .ai-ct-aside {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);
    }
    @media (min-width: 992px) {
        .ai-ct-aside { position: sticky; top: 5.5rem; }
    }
    .ai-ct-aside__thumb {
        width: 100%;
        aspect-ratio: 1;
        border-radius: 12px;
        object-fit: cover;
        background: linear-gradient(135deg, var(--ai-accent) 0%, var(--ai-accent-2) 100%);
        margin-bottom: 1rem;
    }
    .ai-ct-aside__thumb--placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 2.5rem;
        min-height: 140px;
    }
    .ai-ct-aside__label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 0.25rem; }
    .ai-ct-aside__value { font-size: 0.875rem; color: #334155; font-weight: 600; }
    .ai-ct-field-label { font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem; }
    .ai-ct-form-control {
        border-radius: 10px;
        border-color: #e2e8f0;
        padding: 0.6rem 0.85rem;
        font-size: 0.9375rem;
    }
    .ai-ct-form-control:focus {
        border-color: rgba(99, 102, 241, 0.55);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .ai-ct-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 1.75rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f1f5f9;
    }
    .ai-ct-btn-generate {
        border-radius: 10px;
        font-weight: 600;
        padding: 0.65rem 1.35rem;
        background: linear-gradient(135deg, var(--ai-accent) 0%, #7c3aed 100%);
        border: none;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.35);
    }
    .ai-ct-btn-generate:hover {
        filter: brightness(1.05);
        box-shadow: 0 10px 24px rgba(99, 102, 241, 0.4);
    }
    .ai-ct-btn-generate:disabled { opacity: 0.75; }
</style>
@endpush

@section('content')
<div class="container py-4 ai-ct-form-page">
    <div class="ai-ct-form-hero">
        <nav class="ai-ct-form-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('design.index') }}">Design</a>
            <span>/</span>
            <a href="{{ route('design.templates.index') }}">Templates</a>
            <span>/</span>
            <span class="current">{{ Str::limit($template->name, 48) }}</span>
        </nav>
        <h1 class="h4 mb-2" style="font-weight: 700; color: #0f172a; letter-spacing: -0.02em;">{{ $template->name }}</h1>
        @if($template->description)
            <p class="text-muted mb-0" style="font-size: 0.9375rem; line-height: 1.55; max-width: 42rem;">{{ $template->description }}</p>
        @else
            <p class="text-muted mb-0 small">Complete the fields below to tailor the AI output to your project.</p>
        @endif
    </div>

    @include('design.ai-content-templates._generation-list')

    <div class="ai-ct-form-layout">
        <div class="ai-ct-form-main">
            <div class="ai-ct-form-main__inner">
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3" id="generateErrorAlert" role="alert" style="box-shadow: 0 2px 12px rgba(220, 38, 38, 0.12);">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-warning alert-dismissible fade show border-0 rounded-3" id="validationErrorAlert" role="alert">
                    <strong><i class="fas fa-exclamation-triangle me-2"></i>Please review the following</strong>
                    <ul class="mb-0 mt-2 ps-3 small">
                        @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <form action="{{ route('design.aiContentTemplates.generate', $template->id) }}" method="POST" id="aiContentForm">
                    @csrf

                    @php $fields = $template->fields ?? []; @endphp
                    @if(count($fields) > 0)
                    <p class="ai-ct-form-section-title">Your inputs</p>
                    <div class="row g-3">
                        @foreach($fields as $field)
                        @php
                            $key = $field['key'] ?? '';
                            $label = $field['label'] ?? $key;
                            $type = $field['type'] ?? 'text';
                            $placeholder = $field['placeholder'] ?? '';
                        @endphp
                        @if($key)
                        <div class="col-12">
                            <label for="field_{{ $key }}" class="ai-ct-field-label">{{ $label }}</label>
                            @if($type === 'textarea')
                            <textarea class="form-control ai-ct-form-control @error('field_' . $key) is-invalid @enderror" id="field_{{ $key }}" name="field_{{ $key }}" rows="4" placeholder="{{ $placeholder }}">{{ old('field_' . $key) }}</textarea>
                            @else
                            <input type="{{ $type === 'number' ? 'number' : ($type === 'email' ? 'email' : 'text') }}" class="form-control ai-ct-form-control @error('field_' . $key) is-invalid @enderror" id="field_{{ $key }}" name="field_{{ $key }}" value="{{ old('field_' . $key) }}" placeholder="{{ $placeholder }}">
                            @endif
                            @error('field_' . $key)
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted mb-0" style="font-size: 0.9375rem;">No extra fields are required. Continue to generate using this template’s built-in prompt.</p>
                    @endif

                    <div class="ai-ct-actions">
                        <a href="{{ route('design.templates.index') }}" class="btn btn-outline-secondary rounded-3 px-3">
                            <i class="fas fa-arrow-left me-2"></i>Back to templates
                        </a>
                        <button type="submit" class="btn btn-primary ai-ct-btn-generate" id="generateBtn">
                            <i class="fas fa-wand-magic-sparkles me-2"></i>Generate design
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="ai-ct-aside" aria-label="Template summary">
            @if($template->image_url)
            <img src="{{ $template->image_url }}" alt="" class="ai-ct-aside__thumb" loading="lazy">
            @else
            <div class="ai-ct-aside__thumb ai-ct-aside__thumb--placeholder" aria-hidden="true">
                <i class="fas fa-robot"></i>
            </div>
            @endif
            <div class="mb-3 pb-3 border-bottom border-light">
                <div class="ai-ct-aside__label">Workflow</div>
                <div class="ai-ct-aside__value">Queued generation → open in editor</div>
            </div>
            @if($aiGenFlatCredit > 0 && auth()->check())
            <div class="mb-3 pb-3 border-bottom border-light">
                <div class="ai-ct-aside__label">Credits</div>
                <div class="ai-ct-aside__value">From {{ format_price($aiGenFlatCredit) }} per run</div>
                <div class="small text-muted mt-1">Balance: {{ format_price(auth()->user()->balance ?? 0) }} · <a href="{{ route('credits.index') }}" class="text-decoration-none">Top up</a></div>
            </div>
            @elseif($aiGenFlatCredit > 0)
            <div class="mb-3 pb-3 border-bottom border-light">
                <div class="ai-ct-aside__label">Credits</div>
                <div class="small text-muted">Sign in to use credits for AI generation.</div>
            </div>
            @endif
            <div>
                <div class="ai-ct-aside__label">Tip</div>
                <p class="small text-muted mb-0" style="line-height: 1.5;">Keep this window open after you submit—when the job finishes you’ll be taken to the multi-page editor automatically.</p>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var form = document.getElementById('aiContentForm');
    var btn = document.getElementById('generateBtn');
    if (form && btn) {
        form.addEventListener('submit', function() {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting…';
        });
    }
    var errorEl = document.getElementById('generateErrorAlert') || document.getElementById('validationErrorAlert');
    if (errorEl) {
        errorEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
})();
</script>
@endpush
@endsection
