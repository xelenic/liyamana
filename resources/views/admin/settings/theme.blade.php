@extends('layouts.admin')

@section('title', 'UI Theme Settings')
@section('page-title', 'UI Theme Settings')

@section('content')
<div class="theme-settings-page">
    <form action="{{ route('admin.settings.theme.update') }}" method="POST" id="themeSettingsForm">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="theme-settings-card">
                    <!-- Tab Navigation -->
                    <div class="theme-settings-tabs-wrapper">
                        <ul class="nav theme-settings-tabs" id="themeSettingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="colors-tab" data-bs-toggle="tab" data-bs-target="#colors" type="button" role="tab" aria-controls="colors" aria-selected="true">
                                    <i class="fas fa-palette me-2"></i>Colors
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="navbar-tab" data-bs-toggle="tab" data-bs-target="#navbar" type="button" role="tab" aria-controls="navbar" aria-selected="false">
                                    <i class="fas fa-bars me-2"></i>Navbar
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="layout-tab" data-bs-toggle="tab" data-bs-target="#layout" type="button" role="tab" aria-controls="layout" aria-selected="false">
                                    <i class="fas fa-th-large me-2"></i>Layout
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="theme-settings-tab-content">
                        <div class="tab-content" id="themeSettingsTabsContent">
                            <!-- Colors Tab -->
                            <div class="tab-pane fade show active" id="colors" role="tabpanel" aria-labelledby="colors-tab">
                                <div class="theme-settings-section">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                        <h6 class="theme-section-title mb-0"><i class="fas fa-fill-drip me-2"></i>Primary Theme Colors</h6>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDefaultThemeColor" title="Reset to default theme colors">
                                            <i class="fas fa-undo me-1"></i>Default Theme Color
                                        </button>
                                    </div>
                                    <p class="theme-field-hint">These colors control buttons, links, gradients, and accents across the website.</p>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="theme_primary_color" class="form-label">{{ $settings['theme_primary_color']['label'] ?? 'Primary Color' }}</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" class="form-control form-control-color" id="theme_primary_color" name="theme_primary_color" value="{{ old('theme_primary_color', $settings['theme_primary_color']['value'] ?? '#6366f1') }}" style="width: 50px; height: 42px; padding: 2px; cursor: pointer;">
                                                <input type="text" class="form-control" value="{{ old('theme_primary_color', $settings['theme_primary_color']['value'] ?? '#6366f1') }}" id="theme_primary_color_hex" style="max-width: 120px;" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="theme_secondary_color" class="form-label">{{ $settings['theme_secondary_color']['label'] ?? 'Secondary Color' }}</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" class="form-control form-control-color" id="theme_secondary_color" name="theme_secondary_color" value="{{ old('theme_secondary_color', $settings['theme_secondary_color']['value'] ?? '#8b5cf6') }}" style="width: 50px; height: 42px; padding: 2px; cursor: pointer;">
                                                <input type="text" class="form-control" value="{{ old('theme_secondary_color', $settings['theme_secondary_color']['value'] ?? '#8b5cf6') }}" id="theme_secondary_color_hex" style="max-width: 120px;" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="theme-settings-section">
                                    <h6 class="theme-section-title"><i class="fas fa-layer-group me-2"></i>Navbar Gradient</h6>
                                    <p class="theme-field-hint">Customize the navbar background gradient (public header and app sidebar header).</p>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="theme_navbar_bg_start" class="form-label">{{ $settings['theme_navbar_bg_start']['label'] ?? 'Gradient Start' }}</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" class="form-control form-control-color" id="theme_navbar_bg_start" name="theme_navbar_bg_start" value="{{ old('theme_navbar_bg_start', $settings['theme_navbar_bg_start']['value'] ?? '#6366f1') }}" style="width: 50px; height: 42px; padding: 2px; cursor: pointer;">
                                                <input type="text" class="form-control" value="{{ old('theme_navbar_bg_start', $settings['theme_navbar_bg_start']['value'] ?? '#6366f1') }}" style="max-width: 120px;" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="theme_navbar_bg_end" class="form-label">{{ $settings['theme_navbar_bg_end']['label'] ?? 'Gradient End' }}</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" class="form-control form-control-color" id="theme_navbar_bg_end" name="theme_navbar_bg_end" value="{{ old('theme_navbar_bg_end', $settings['theme_navbar_bg_end']['value'] ?? '#8b5cf6') }}" style="width: 50px; height: 42px; padding: 2px; cursor: pointer;">
                                                <input type="text" class="form-control" value="{{ old('theme_navbar_bg_end', $settings['theme_navbar_bg_end']['value'] ?? '#8b5cf6') }}" style="max-width: 120px;" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Navbar Tab -->
                            <div class="tab-pane fade" id="navbar" role="tabpanel" aria-labelledby="navbar-tab">
                                <div class="theme-settings-section">
                                    <h6 class="theme-section-title"><i class="fas fa-bars me-2"></i>Navbar Size</h6>
                                    <p class="theme-field-hint">Adjust padding and font size for the navbar and header areas.</p>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="theme_navbar_padding_y" class="form-label">{{ $settings['theme_navbar_padding_y']['label'] ?? 'Navbar Padding' }}</label>
                                            <select class="form-select" id="theme_navbar_padding_y" name="theme_navbar_padding_y">
                                                @foreach($settings['theme_navbar_padding_y']['options'] ?? [] as $val => $label)
                                                <option value="{{ $val }}" {{ old('theme_navbar_padding_y', $settings['theme_navbar_padding_y']['value'] ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="theme_navbar_font_size" class="form-label">{{ $settings['theme_navbar_font_size']['label'] ?? 'Navbar Font Size' }}</label>
                                            <select class="form-select" id="theme_navbar_font_size" name="theme_navbar_font_size">
                                                @foreach($settings['theme_navbar_font_size']['options'] ?? [] as $val => $label)
                                                <option value="{{ $val }}" {{ old('theme_navbar_font_size', $settings['theme_navbar_font_size']['value'] ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Layout Tab -->
                            <div class="tab-pane fade" id="layout" role="tabpanel" aria-labelledby="layout-tab">
                                <div class="theme-settings-section">
                                    <h6 class="theme-section-title"><i class="fas fa-border-style me-2"></i>Border Radius</h6>
                                    <p class="theme-field-hint">Control the roundness of cards, inputs, and buttons.</p>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="theme_border_radius" class="form-label">{{ $settings['theme_border_radius']['label'] ?? 'Card & Input Radius' }}</label>
                                            <select class="form-select" id="theme_border_radius" name="theme_border_radius">
                                                @foreach($settings['theme_border_radius']['options'] ?? [] as $val => $label)
                                                <option value="{{ $val }}" {{ old('theme_border_radius', $settings['theme_border_radius']['value'] ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="theme_btn_border_radius" class="form-label">{{ $settings['theme_btn_border_radius']['label'] ?? 'Button Radius' }}</label>
                                            <select class="form-select" id="theme_btn_border_radius" name="theme_btn_border_radius">
                                                @foreach($settings['theme_btn_border_radius']['options'] ?? [] as $val => $label)
                                                <option value="{{ $val }}" {{ old('theme_btn_border_radius', $settings['theme_btn_border_radius']['value'] ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="theme-settings-section">
                                    <h6 class="theme-section-title"><i class="fas fa-columns me-2"></i>Sidebar</h6>
                                    <p class="theme-field-hint">Width of the app sidebar (when logged in).</p>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="theme_sidebar_width" class="form-label">{{ $settings['theme_sidebar_width']['label'] ?? 'Sidebar Width' }}</label>
                                            <select class="form-select" id="theme_sidebar_width" name="theme_sidebar_width">
                                                @foreach($settings['theme_sidebar_width']['options'] ?? [] as $val => $label)
                                                <option value="{{ $val }}" {{ old('theme_sidebar_width', $settings['theme_sidebar_width']['value'] ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="theme-settings-footer">
                        <a href="{{ route('admin.settings', ['tab' => 'general']) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Settings
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Theme Settings
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="theme-settings-sidebar">
                    <div class="theme-sidebar-header">
                        <i class="fas fa-paint-brush"></i>
                        <h5>UI Theme</h5>
                    </div>
                    <p class="theme-sidebar-desc">
                        Customize the look and feel of your website. Changes apply instantly across the public site and app when saved.
                    </p>
                    <div class="theme-settings-preview">
                        <div class="preview-label">Preview</div>
                        <div class="preview-navbar" id="themePreviewNavbar"></div>
                        <div class="preview-content">
                            <div class="preview-card"></div>
                            <div class="preview-btn"></div>
                        </div>
                    </div>
                    <div class="theme-sidebar-tip">
                        <i class="fas fa-lightbulb"></i>
                        <span>Primary and secondary colors are used for buttons, links, gradients, and active states.</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .theme-settings-page { padding: 0.5rem 0 2rem; }
    .theme-settings-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; overflow: hidden; }
    .theme-settings-tabs-wrapper { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0 1.5rem; }
    .theme-settings-tabs { display: flex; gap: 0.25rem; margin: 0; padding: 0; list-style: none; }
    .theme-settings-tabs .nav-link { padding: 1rem 1.25rem; font-size: 0.9rem; font-weight: 500; color: #64748b; background: none; border: none; border-bottom: 3px solid transparent; margin-bottom: -1px; transition: all 0.2s; display: flex; align-items: center; }
    .theme-settings-tabs .nav-link:hover { color: #6366f1; background: rgba(99, 102, 241, 0.05); }
    .theme-settings-tabs .nav-link.active { color: #6366f1; border-bottom-color: #6366f1; background: white; }
    .theme-settings-tab-content { padding: 1.5rem 1.75rem 1.25rem; }
    .theme-settings-section { margin-bottom: 1.75rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; }
    .theme-settings-section:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    .theme-section-title { font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 0.5rem; display: flex; align-items: center; }
    .theme-section-title i { color: #6366f1; opacity: 0.9; }
    .theme-field-hint { font-size: 0.8rem; color: #64748b; margin-bottom: 1rem; line-height: 1.4; }
    .theme-settings-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .theme-settings-sidebar { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; padding: 1.5rem; position: sticky; top: 1.5rem; }
    .theme-sidebar-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
    .theme-sidebar-header i { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border-radius: 10px; font-size: 1rem; }
    .theme-sidebar-header h5 { margin: 0; font-size: 1rem; font-weight: 600; color: #1e293b; }
    .theme-sidebar-desc { font-size: 0.8rem; color: #64748b; line-height: 1.5; margin-bottom: 1.25rem; padding-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; }
    .theme-settings-preview { background: #f8fafc; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; border: 1px solid #e2e8f0; }
    .preview-label { font-size: 0.7rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; }
    .preview-navbar { height: 24px; border-radius: 4px; margin-bottom: 0.75rem; }
    .preview-content { display: flex; gap: 0.5rem; align-items: center; }
    .preview-card { width: 60px; height: 40px; background: white; border-radius: 6px; border: 1px solid #e2e8f0; }
    .preview-btn { width: 50px; height: 28px; border-radius: 4px; }
    .theme-sidebar-tip { display: flex; align-items: flex-start; gap: 0.5rem; padding: 0.75rem; background: rgba(99, 102, 241, 0.08); border-radius: 8px; border: 1px solid rgba(99, 102, 241, 0.15); }
    .theme-sidebar-tip i { color: #6366f1; font-size: 0.9rem; margin-top: 0.1rem; flex-shrink: 0; }
    .theme-sidebar-tip span { font-size: 0.75rem; color: #475569; line-height: 1.4; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sync color inputs with hex display
    function syncColorInput(inputId, hexId) {
        const colorInput = document.getElementById(inputId);
        const hexInput = document.getElementById(hexId);
        if (colorInput && hexInput) {
            colorInput.addEventListener('input', function() {
                hexInput.value = this.value;
                updatePreview();
            });
        }
    }
    syncColorInput('theme_primary_color', 'theme_primary_color_hex');
    syncColorInput('theme_secondary_color', 'theme_secondary_color_hex');

    document.querySelectorAll('input[type="color"]').forEach(function(inp) {
        const hexDisplay = inp.parentElement.querySelector('input[type="text"]');
        if (hexDisplay && !hexDisplay.id) {
            inp.addEventListener('input', function() {
                hexDisplay.value = this.value;
                updatePreview();
            });
        }
    });

    function updatePreview() {
        const primary = document.getElementById('theme_primary_color')?.value || '#6366f1';
        const secondary = document.getElementById('theme_navbar_bg_start')?.value || '#6366f1';
        const end = document.getElementById('theme_navbar_bg_end')?.value || '#8b5cf6';
        const navbar = document.getElementById('themePreviewNavbar');
        const btn = document.querySelector('.preview-btn');
        if (navbar) navbar.style.background = 'linear-gradient(135deg, ' + secondary + ' 0%, ' + end + ' 100%)';
        if (btn) btn.style.background = 'linear-gradient(135deg, ' + primary + ' 0%, ' + end + ' 100%)';
    }

    const defaultColors = {
        theme_primary_color: '#6366f1',
        theme_secondary_color: '#8b5cf6',
        theme_navbar_bg_start: '#6366f1',
        theme_navbar_bg_end: '#8b5cf6'
    };

    document.getElementById('btnDefaultThemeColor')?.addEventListener('click', function() {
        Object.keys(defaultColors).forEach(function(id) {
            const inp = document.getElementById(id) || document.querySelector('input[name="' + id + '"]');
            if (inp) {
                inp.value = defaultColors[id];
                const hexDisplay = document.getElementById(id + '_hex') || inp.parentElement?.querySelector('input[type="text"]');
                if (hexDisplay) hexDisplay.value = defaultColors[id];
            }
        });
        updatePreview();
    });

    updatePreview();
});
</script>
@endpush
@endsection
