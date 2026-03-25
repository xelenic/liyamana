@extends('layouts.admin')

@section('title', 'Gemini Image Settings')
@section('page-title', 'Gemini Image Settings')

@section('content')
<div class="my-3">
    <div class="mb-2">
        <a href="{{ route('admin.settings', ['tab' => 'general']) }}" class="text-decoration-none small">
            <i class="fas fa-arrow-left me-1"></i>Back to Settings
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-cog me-2"></i>Gemini Image Settings</h6>
                    <form action="{{ route('admin.nanobanana.settings.update') }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-0">Gemini API Key</label>
                            <input type="password" name="gemini_api_key" class="form-control form-control-sm" value="{{ $gemini_api_key }}" placeholder="Your Gemini API key" autocomplete="off">
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Required for AI image generation. Get your key from <a href="https://aistudio.google.com/apikey" target="_blank">Google AI Studio</a>.</small>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-0">Model</label>
                            <select name="gemini_image_model" class="form-select form-select-sm">
                                <option value="gemini-3-pro-image-preview" {{ ($gemini_image_model ?? 'gemini-3-pro-image-preview') === 'gemini-3-pro-image-preview' ? 'selected' : '' }}>Gemini 3 Pro Image</option>
                                <option value="gemini-2.5-flash-image" {{ ($gemini_image_model ?? '') === 'gemini-2.5-flash-image' ? 'selected' : '' }}>Gemini 2.5 Flash Image</option>
                            </select>
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Pro: higher quality. Flash: faster, lower cost. Choose based on your needs.</small>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-0">Aspect Ratio</label>
                            <select name="nanobanana_image_size" class="form-select form-select-sm">
                                <option value="1:1" {{ ($nanobanana_image_size ?? '') === '1:1' ? 'selected' : '' }}>1:1 (Square)</option>
                                <option value="16:9" {{ ($nanobanana_image_size ?? '') === '16:9' ? 'selected' : '' }}>16:9</option>
                                <option value="9:16" {{ ($nanobanana_image_size ?? '') === '9:16' ? 'selected' : '' }}>9:16</option>
                                <option value="4:3" {{ ($nanobanana_image_size ?? '') === '4:3' ? 'selected' : '' }}>4:3</option>
                                <option value="3:4" {{ ($nanobanana_image_size ?? '') === '3:4' ? 'selected' : '' }}>3:4</option>
                                <option value="2:3" {{ ($nanobanana_image_size ?? '') === '2:3' ? 'selected' : '' }}>2:3</option>
                                <option value="3:2" {{ ($nanobanana_image_size ?? '') === '3:2' ? 'selected' : '' }}>3:2</option>
                                <option value="21:9" {{ ($nanobanana_image_size ?? '') === '21:9' ? 'selected' : '' }}>21:9 (Ultra-wide)</option>
                                <option value="auto" {{ ($nanobanana_image_size ?? '') === 'auto' ? 'selected' : '' }}>Auto</option>
                            </select>
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Output image dimensions. Affects flipbook layouts and print formats.</small>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-0">Cost per Image (Credits)</label>
                            <input type="number" name="gemini_image_cost" class="form-control form-control-sm" value="{{ $gemini_image_cost ?? 1 }}" min="0" step="0.01" placeholder="1" style="max-width: 100px;">
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Credits deducted from user's top-up balance per image. Set 0 to make free.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold mb-0">Resolution</label>
                            <select name="nanobanana_resolution" class="form-select form-select-sm" style="max-width: 120px;">
                                <option value="1K" {{ ($nanobanana_resolution ?? '2K') === '1K' ? 'selected' : '' }}>1K</option>
                                <option value="2K" {{ ($nanobanana_resolution ?? '2K') === '2K' ? 'selected' : '' }}>2K</option>
                                <option value="4K" {{ ($nanobanana_resolution ?? '2K') === '4K' ? 'selected' : '' }}>4K</option>
                            </select>
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Higher = sharper images, larger files. Applies to Gemini 3 Pro only; Flash uses default.</small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save me-1"></i>Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-lightbulb me-2 text-warning"></i>AI Prompt Template – Integration Tips</h6>
                    <div class="small" style="font-size: 0.8125rem; line-height: 1.6;">
                        <p class="mb-2"><strong>How it works</strong></p>
                        <p class="text-muted mb-3">Create templates in <a href="{{ route('admin.nanobanana.templates.index') }}">AI Image Template</a>. Each template has a <strong>prompt</strong> and <strong>defined fields</strong>. The prompt is sent to Gemini; user input replaces placeholders.</p>

                        <p class="mb-2"><strong>Placeholders</strong></p>
                        <p class="text-muted mb-2">Use <code class="bg-light px-1 rounded">@{{fieldName}}</code> in the prompt. Each field name must match a defined field.</p>
                        <p class="mb-3"><span class="text-muted">Example:</span> <code class="bg-light px-1 rounded d-block mt-1" style="font-size: 0.75rem;">A professional logo for @{{company_name}}, with tagline @{{tagline}}. Minimalist, modern style.</code></p>

                        <p class="mb-2"><strong>Defined fields</strong></p>
                        <ul class="text-muted mb-3 ps-3">
                            <li><strong>Name</strong> – unique ID for the placeholder (e.g. <code>subject</code>, <code>company_name</code>)</li>
                            <li><strong>Label</strong> – shown to users in the form</li>
                            <li><strong>Type</strong> – text, textarea, or select (dropdown)</li>
                            <li><strong>Options</strong> – for select: comma-separated values</li>
                        </ul>

                        <p class="mb-2"><strong>Prompt tips</strong></p>
                        <ul class="text-muted mb-0 ps-3">
                            <li>Be specific: style, colors, mood, format</li>
                            <li>Use placeholders for user-specific content</li>
                            <li>Describe perspective, lighting, composition when relevant</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
