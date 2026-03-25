@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="my-2 settings-page-compact">
    <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm">
        @csrf
        <input type="hidden" name="tab" value="{{ $activeTab ?? 'general' }}">

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <!-- Tab Navigation -->
                        @php $activeTab = $activeTab ?? 'general'; @endphp
                        <ul class="nav settings-tabs" id="settingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="{{ $activeTab === 'general' ? 'true' : 'false' }}">
                                    <i class="fas fa-cog me-2"></i>General
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'features' ? 'active' : '' }}" id="features-tab" data-bs-toggle="tab" data-bs-target="#features" type="button" role="tab" aria-controls="features" aria-selected="{{ $activeTab === 'features' ? 'true' : 'false' }}">
                                    <i class="fas fa-toggle-on me-2"></i>Features
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'ai' ? 'active' : '' }}" id="ai-tab" data-bs-toggle="tab" data-bs-target="#ai" type="button" role="tab" aria-controls="ai" aria-selected="{{ $activeTab === 'ai' ? 'true' : 'false' }}">
                                    <i class="fas fa-robot me-2"></i>AI Generation
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'currency' ? 'active' : '' }}" id="currency-tab" data-bs-toggle="tab" data-bs-target="#currency" type="button" role="tab" aria-controls="currency" aria-selected="{{ $activeTab === 'currency' ? 'true' : 'false' }}">
                                    <i class="fas fa-dollar-sign me-2"></i>Currency & Pricing
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'explore' ? 'active' : '' }}" id="explore-tab" data-bs-toggle="tab" data-bs-target="#explore" type="button" role="tab" aria-controls="explore" aria-selected="{{ $activeTab === 'explore' ? 'true' : 'false' }}">
                                    <i class="fas fa-th-large me-2"></i>Template Explore
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content settings-tab-content" id="settingsTabsContent">
                            <!-- General Tab -->
                            <div class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}" id="general" role="tabpanel" aria-labelledby="general-tab">
                                @if(isset($settings['general']))
                                    @foreach($settings['general'] as $key => $item)
                                        <div class="mb-2">
                                            @if($item['type'] === 'text')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <input type="text" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $item['value']) }}" placeholder="{{ $item['label'] }}">
                                            @elseif($item['type'] === 'textarea')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <textarea class="form-control" id="{{ $key }}" name="{{ $key }}" rows="2" placeholder="{{ $item['label'] }}">{{ old($key, $item['value']) }}</textarea>
                                            @elseif($item['type'] === 'email')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <input type="email" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $item['value']) }}" placeholder="admin@example.com">
                                            @elseif($item['type'] === 'number')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <input type="number" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $item['value']) }}" min="1" max="100">
                                                <small class="text-muted">Default pagination size for admin lists</small>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <!-- Features Tab -->
                            <div class="tab-pane fade {{ $activeTab === 'features' ? 'show active' : '' }}" id="features" role="tabpanel" aria-labelledby="features-tab">
                                @if(isset($settings['features']))
                                    @foreach($settings['features'] as $key => $item)
                                        <div class="mb-2">
                                            @if($item['type'] === 'boolean')
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="{{ $key }}" value="0">
                                                    <input class="form-check-input" type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1" {{ old($key, $item['value']) == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $key }}">{{ $item['label'] }}</label>
                                                </div>
                                                @if($key === 'maintenance_mode')
                                                    <small class="text-muted d-block mt-0">When enabled, non-admin users will see a maintenance page</small>
                                                @elseif($key === 'allow_registration')
                                                    <small class="text-muted d-block mt-0">Allow new users to register accounts</small>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <!-- AI Generation Tab -->
                            <div class="tab-pane fade {{ $activeTab === 'ai' ? 'show active' : '' }}" id="ai" role="tabpanel" aria-labelledby="ai-tab">
                                @if(isset($settings['ai']))
                                    @foreach($settings['ai'] as $key => $item)
                                        <div class="mb-2">
                                            @if($item['type'] === 'boolean')
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="{{ $key }}" value="0">
                                                    <input class="form-check-input" type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1" {{ old($key, $item['value']) == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $key }}">{{ $item['label'] }}</label>
                                                </div>
                                                @if($key === 'ai_design_enabled')
                                                    <small class="text-muted d-block mt-0">Enable AI-powered design generation in the design tool</small>
                                                @elseif($key === 'ai_content_use_token_cost')
                                                    <small class="text-muted d-block mt-0">When on, deduct credits by token usage (input/output cost per 1000). When off, use flat cost per generation.</small>
                                                @endif
                                            @elseif($item['type'] === 'password')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <input type="password" class="form-control" id="{{ $key }}" name="{{ $key }}" value="" placeholder="{{ !empty($item['value']) ? '••••••••••••••••' : 'Enter API key' }}" autocomplete="new-password">
                                                @if($key === 'openai_api_key')
                                                    <small class="text-muted d-block mt-0">API key for OpenAI. Leave blank to keep existing. Get your key at platform.openai.com</small>
                                                @elseif($key === 'gemini_api_key')
                                                    <small class="text-muted d-block mt-0">API key for Google Gemini (AI Content Templates). Leave blank to keep existing. Get your key at aistudio.google.com/apikey</small>
                                                @endif
                                            @elseif($item['type'] === 'text')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <input type="text" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $item['value']) }}" placeholder="{{ $item['label'] }}">
                                                @if($key === 'openai_model')
                                                    <small class="text-muted d-block mt-0">Model for AI design generation (e.g. gpt-4o-mini, gpt-4o)</small>
                                                @elseif($key === 'openai_base_url')
                                                    <small class="text-muted d-block mt-0">Custom API endpoint. Leave empty for default (api.openai.com)</small>
                                                @elseif($key === 'gemini_model')
                                                    <small class="text-muted d-block mt-0">Model for AI Content Templates (e.g. gemini-2.5-flash, gemini-2.5-pro, gemini-2.0-flash)</small>
                                                @elseif($key === 'ai_content_template_credit_cost')
                                                    <small class="text-muted d-block mt-0">Used when token billing is off or when API does not return token usage (e.g. 0.5 or 1). Set to 0 to disable deduction.</small>
                                                @elseif($key === 'ai_content_input_token_cost_per_1000')
                                                    <small class="text-muted d-block mt-0">Credits per 1000 prompt/input tokens (e.g. 0.01). Only used when token billing is enabled and usage is available.</small>
                                                @elseif($key === 'ai_content_output_token_cost_per_1000')
                                                    <small class="text-muted d-block mt-0">Credits per 1000 output/response tokens (e.g. 0.02). Only used when token billing is enabled and usage is available.</small>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <!-- Currency & Pricing Tab -->
                            <div class="tab-pane fade {{ $activeTab === 'currency' ? 'show active' : '' }}" id="currency" role="tabpanel" aria-labelledby="currency-tab">
                                @if(isset($settings['currency']))
                                    @foreach($settings['currency'] as $key => $item)
                                        <div class="mb-2">
                                            @if($item['type'] === 'select')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <select class="form-select" id="{{ $key }}" name="{{ $key }}">
                                                    @if($key === 'default_currency')
                                                        <option value="">— Select currency —</option>
                                                    @endif
                                                    @foreach($item['options'] ?? [] as $optVal => $optLabel)
                                                        <option value="{{ $optVal }}" {{ old($key, $item['value']) == $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                                                    @endforeach
                                                </select>
                                                @if($key === 'default_currency')
                                                    <small class="text-muted d-block mt-0">Default currency for templates and pricing. Manage options in <a href="{{ route('admin.currencies') }}">Settings → Currencies</a>.</small>
                                                @endif
                                            @elseif($item['type'] === 'text')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <input type="text" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $item['value']) }}" placeholder="{{ $item['label'] }}">
                                                @if($key === 'currency_symbol')
                                                    <small class="text-muted d-block mt-0">Symbol shown with prices (e.g. $, €, £). Override if different from currency.</small>
                                                @endif
                                            @elseif($item['type'] === 'number')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <input type="number" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $item['value']) }}" min="0" max="4">
                                                @if($key === 'price_decimal_places')
                                                    <small class="text-muted d-block mt-0">Number of decimal places for prices (0–4)</small>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <!-- Template Explore Tab -->
                            <div class="tab-pane fade {{ $activeTab === 'explore' ? 'show active' : '' }}" id="explore" role="tabpanel" aria-labelledby="explore-tab">
                                @if(isset($settings['explore']))
                                    @foreach($settings['explore'] as $key => $item)
                                        <div class="mb-2">
                                            @if($item['type'] === 'boolean')
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="{{ $key }}" value="0">
                                                    <input class="form-check-input" type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1" {{ old($key, $item['value']) == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $key }}">{{ $item['label'] }}</label>
                                                </div>
                                                @if($key === 'explore_show_featured')
                                                    <small class="text-muted d-block mt-0">Show the featured templates slider/hero on the explore page</small>
                                                @elseif($key === 'explore_show_categories')
                                                    <small class="text-muted d-block mt-0">Show category filter in the template explore page</small>
                                                @elseif($key === 'explore_tooltip_enabled')
                                                    <small class="text-muted d-block mt-0">Show hover tooltip on template cards (name, description, meta)</small>
                                                @endif
                                            @elseif($item['type'] === 'text')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <input type="text" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $item['value']) }}" placeholder="{{ $item['label'] }}">
                                                @if($key === 'explore_page_title')
                                                    <small class="text-muted d-block mt-0">Title shown on the design/templates/explore page</small>
                                                @endif
                                            @elseif($item['type'] === 'number')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <input type="number" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $item['value']) }}" min="200" max="2000" step="100">
                                                @if($key === 'explore_tooltip_delay_ms')
                                                    <small class="text-muted d-block mt-0">Delay in milliseconds before showing the hover tooltip on template cards (200–2000)</small>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 px-3 py-2 pt-0">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-2" style="font-size: 0.9rem;">
                            <i class="fas fa-info-circle text-primary me-1"></i>About Settings
                        </h5>
                        <p class="text-muted mb-2" style="font-size: 0.75rem;">
                            Configure your {{ site_name() }} application settings here. Changes take effect immediately after saving.
                        </p>
                        <div class="mb-2">
                            <strong style="font-size: 0.8rem;">General</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">Site name, description, contact email, and pagination options.</p>
                        </div>
                        <div class="mb-2">
                            <strong style="font-size: 0.8rem;">Features</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">Enable or disable registration and maintenance mode.</p>
                        </div>
                        <div class="mb-2">
                            <strong style="font-size: 0.8rem;">AI Generation</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">Configure AI design generation, OpenAI API key, model, and base URL.</p>
                        </div>
                        <div class="mb-2">
                            <strong style="font-size: 0.8rem;">Currency & Pricing</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">Default currency, symbol, and decimal places for prices.</p>
                        </div>
                        <div class="mb-2">
                            <strong style="font-size: 0.8rem;">Template Explore</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">Page title, featured section, category filter, and card tooltip delay for the explore templates page.</p>
                        </div>
                        <div>
                            <strong style="font-size: 0.8rem;">Save</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">All settings across tabs are saved together when you click Save Settings.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .settings-page-compact .form-label {
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }
    .settings-page-compact .form-control,
    .settings-page-compact .form-select {
        font-size: 0.8rem;
        padding: 0.35rem 0.5rem;
        min-height: 32px;
    }
    .settings-page-compact .form-check-label {
        font-size: 0.8rem;
    }
    .settings-page-compact .form-check-input {
        width: 1.1rem;
        height: 1.1rem;
    }
    .settings-page-compact small.text-muted {
        font-size: 0.7rem;
        margin-top: 0.15rem;
    }
    .settings-page-compact .btn {
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
    }
    /* Tab styling - simple underline, not button-like */
    .settings-tabs {
        border-bottom: 1px solid #e2e8f0;
        padding: 0 1.25rem;
        gap: 0;
    }
    .settings-tabs .nav-link {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #64748b;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        margin-bottom: -1px;
        transition: color 0.2s, border-color 0.2s;
    }
    .settings-tabs .nav-link:hover {
        color: #6366f1;
    }
    .settings-tabs .nav-link.active {
        color: #6366f1;
        border-bottom-color: #6366f1;
    }
    .settings-tab-content {
        padding: 1.25rem 1.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabInput = document.querySelector('#settingsForm input[name="tab"]');
    const tabButtons = document.querySelectorAll('#settingsTabs button[data-bs-toggle="tab"]');
    if (tabInput && tabButtons.length) {
        tabButtons.forEach(function(btn) {
            btn.addEventListener('shown.bs.tab', function(e) {
                const target = e.target.getAttribute('data-bs-target');
                if (target === '#general') tabInput.value = 'general';
                else if (target === '#features') tabInput.value = 'features';
                else if (target === '#ai') tabInput.value = 'ai';
                else if (target === '#currency') tabInput.value = 'currency';
            });
        });
    }
});
</script>
@endpush
@endsection
