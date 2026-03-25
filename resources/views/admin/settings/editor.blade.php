@extends('layouts.admin')

@section('title', 'Editor Settings')
@section('page-title', 'Editor Settings')

@section('content')
<div class="editor-settings-page">
    <form action="{{ route('admin.settings.editor.update') }}" method="POST" id="editorSettingsForm">
        @csrf
        <input type="hidden" name="tab" value="{{ $activeTab ?? 'canvas' }}">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="editor-settings-card">
                    <!-- Tab Navigation -->
                    @php $activeTab = $activeTab ?? 'canvas'; @endphp
                    <div class="editor-settings-tabs-wrapper">
                        <ul class="nav editor-settings-tabs" id="editorSettingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'canvas' ? 'active' : '' }}" id="canvas-tab" data-bs-toggle="tab" data-bs-target="#canvas" type="button" role="tab" aria-controls="canvas" aria-selected="{{ $activeTab === 'canvas' ? 'true' : 'false' }}">
                                    <i class="fas fa-expand me-2"></i>Canvas
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'options' ? 'active' : '' }}" id="options-tab" data-bs-toggle="tab" data-bs-target="#options" type="button" role="tab" aria-controls="options" aria-selected="{{ $activeTab === 'options' ? 'true' : 'false' }}">
                                    <i class="fas fa-sliders-h me-2"></i>Editor Options
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'image' ? 'active' : '' }}" id="image-tab" data-bs-toggle="tab" data-bs-target="#image" type="button" role="tab" aria-controls="image" aria-selected="{{ $activeTab === 'image' ? 'true' : 'false' }}">
                                    <i class="fas fa-image me-2"></i>Image Options
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'elements' ? 'active' : '' }}" id="elements-tab" data-bs-toggle="tab" data-bs-target="#elements" type="button" role="tab" aria-controls="elements" aria-selected="{{ $activeTab === 'elements' ? 'true' : 'false' }}">
                                    <i class="fas fa-th-large me-2"></i>Element Panel
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    <div class="editor-settings-tab-content">
                        <div class="tab-content" id="editorSettingsTabsContent">
                            <!-- Canvas Tab -->
                            <div class="tab-pane fade {{ $activeTab === 'canvas' ? 'show active' : '' }}" id="canvas" role="tabpanel" aria-labelledby="canvas-tab">
                                <div class="editor-settings-section">
                                    <h6 class="editor-section-title"><i class="fas fa-expand-arrows-alt me-2"></i>Default Canvas Dimensions</h6>
                                @if(isset($settings['canvas']))
                                    @foreach($settings['canvas'] as $key => $item)
                                        <div class="editor-settings-field">
                                            @if($item['type'] === 'number')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <input type="number" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $item['value']) }}" min="100" max="4000" step="1">
                                                @if(str_contains($key, 'width'))
                                                    <small class="text-muted d-block mt-0">Default width for new pages (100–4000px)</small>
                                                @elseif(str_contains($key, 'height'))
                                                    <small class="text-muted d-block mt-0">Default height for new pages (100–4000px)</small>
                                                @endif
                                            @elseif($item['type'] === 'text')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="text" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $item['value']) }}" placeholder="#ffffff" style="max-width: 120px;">
                                                    <input type="color" class="form-control form-control-color" value="{{ old($key, $item['value']) }}" style="width: 40px; height: 38px; padding: 2px;" onchange="document.getElementById('{{ $key }}').value = this.value">
                                                </div>
                                                @if(str_contains($key, 'color'))
                                                    <small class="text-muted d-block mt-0">Default background for new canvases (hex color)</small>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                                </div>
                            </div>

                            <!-- Editor Options Tab -->
                            <div class="tab-pane fade {{ $activeTab === 'options' ? 'show active' : '' }}" id="options" role="tabpanel" aria-labelledby="options-tab">
                                <div class="editor-settings-section">
                                    <h6 class="editor-section-title"><i class="fas fa-sliders-h me-2"></i>Editor Behavior</h6>
                                @if(isset($settings['options']))
                                    @foreach($settings['options'] as $key => $item)
                                        <div class="editor-settings-field">
                                            @if($item['type'] === 'boolean')
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="{{ $key }}" value="0">
                                                    <input class="form-check-input" type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1" {{ old($key, $item['value']) == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $key }}">{{ $item['label'] }}</label>
                                                </div>
                                                @if($key === 'editor_show_menu_bar')
                                                    <small class="text-muted d-block mt-0">Show File, Edit, View menus at the top of the editor</small>
                                                @elseif($key === 'editor_show_context_menu')
                                                    <small class="text-muted d-block mt-0">Show right-click context menu on canvas and objects</small>
                                                @elseif($key === 'editor_show_rulers')
                                                    <small class="text-muted d-block mt-0">Show horizontal and vertical rulers in the design tool</small>
                                                @elseif($key === 'editor_grid_snap')
                                                    <small class="text-muted d-block mt-0">Snap objects to grid when moving</small>
                                                @elseif($key === 'editor_auto_save')
                                                    <small class="text-muted d-block mt-0">Automatically save design progress</small>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                                </div>

                                <div class="editor-settings-section">
                                    <h6 class="editor-section-title"><i class="fas fa-mouse-pointer me-2"></i>Custom Context Menu Links</h6>
                                    <p class="editor-field-hint">Add custom links that appear in the right-click context menu in the design editor.</p>
                                    <input type="hidden" name="editor_context_menu_links" id="editor_context_menu_links" value="{{ json_encode($contextMenuLinks ?? []) }}">
                                <div id="contextMenuLinksList" class="mb-2">
                                    @forelse(($contextMenuLinks ?? []) as $index => $link)
                                    <div class="context-menu-link-row mb-2 d-flex gap-2 align-items-center" data-index="{{ $index }}">
                                        <input type="text" class="form-control form-control-sm" placeholder="Label" value="{{ $link['label'] ?? '' }}" style="flex: 1; min-width: 100px;">
                                        <input type="text" class="form-control form-control-sm" placeholder="URL (e.g. /help or https://example.com)" value="{{ $link['url'] ?? '' }}" style="flex: 2; min-width: 150px;">
                                        <input type="text" class="form-control form-control-sm" placeholder="Icon (e.g. fa-question)" value="{{ $link['icon'] ?? '' }}" style="flex: 0 0 100px;">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-context-link" title="Remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    @empty
                                    <div class="text-muted small mb-2" id="contextMenuLinksEmptyMsg">No custom links. Click "Add Link" to add one.</div>
                                    @endforelse
                                </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addContextMenuLink">
                                        <i class="fas fa-plus me-1"></i>Add Link
                                    </button>
                                </div>

                                <div class="editor-settings-section">
                                    <h6 class="editor-section-title"><i class="fas fa-bars me-2"></i>Custom Menu Bar Links</h6>
                                    <p class="editor-field-hint">Add custom links that appear in a "Links" menu in the menu bar.</p>
                                <input type="hidden" name="editor_menu_bar_links" id="editor_menu_bar_links" value="{{ json_encode($menuBarLinks ?? []) }}">
                                <div id="menuBarLinksList" class="mb-2">
                                    @forelse(($menuBarLinks ?? []) as $index => $link)
                                    <div class="menu-bar-link-row mb-2 d-flex gap-2 align-items-center" data-index="{{ $index }}">
                                        <input type="text" class="form-control form-control-sm" placeholder="Label" value="{{ $link['label'] ?? '' }}" style="flex: 1; min-width: 100px;">
                                        <input type="text" class="form-control form-control-sm" placeholder="URL (e.g. /help or https://example.com)" value="{{ $link['url'] ?? '' }}" style="flex: 2; min-width: 150px;">
                                        <input type="text" class="form-control form-control-sm" placeholder="Icon (e.g. fa-question)" value="{{ $link['icon'] ?? '' }}" style="flex: 0 0 100px;">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-menu-bar-link" title="Remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    @empty
                                    <div class="text-muted small mb-2" id="menuBarLinksEmptyMsg">No custom links. Click "Add Link" to add one.</div>
                                    @endforelse
                                </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addMenuBarLink">
                                        <i class="fas fa-plus me-1"></i>Add Link
                                    </button>
                                </div>
                            </div>

                            <!-- Image Options Tab -->
                            <div class="tab-pane fade {{ $activeTab === 'image' ? 'show active' : '' }}" id="image" role="tabpanel" aria-labelledby="image-tab">
                                <div class="editor-settings-section">
                                    <h6 class="editor-section-title"><i class="fas fa-image me-2"></i>Image Handling</h6>
                                @if(isset($settings['image']))
                                    @foreach($settings['image'] as $key => $item)
                                        <div class="editor-settings-field">
                                            @if($item['type'] === 'boolean')
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="{{ $key }}" value="0">
                                                    <input class="form-check-input" type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1" {{ old($key, $item['value']) == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $key }}">{{ $item['label'] }}</label>
                                                </div>
                                                @if($key === 'editor_image_reduce_on_add')
                                                    <small class="text-muted d-block mt-0">Resize and compress images when adding to canvas (upload or library)</small>
                                                @endif
                                            @elseif($item['type'] === 'number')
                                                <label for="{{ $key }}" class="form-label">{{ $item['label'] }}</label>
                                                <input type="number" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $item['value']) }}" step="0.1" min="{{ $key === 'editor_image_quality' ? 0.1 : 100 }}" max="{{ $key === 'editor_image_quality' ? 1 : 8000 }}" style="max-width: 120px;">
                                                @if($key === 'editor_image_max_dimension')
                                                    <small class="text-muted d-block mt-0">Max width or height in pixels. Images larger than this will be resized.</small>
                                                @elseif($key === 'editor_image_quality')
                                                    <small class="text-muted d-block mt-0">JPEG quality for compression (0.1=low, 1=lossless). PNG uses 1.</small>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                                </div>
                            </div>

                            <!-- Element Panel Tab -->
                            <div class="tab-pane fade {{ $activeTab === 'elements' ? 'show active' : '' }}" id="elements" role="tabpanel" aria-labelledby="elements-tab">
                                <div class="editor-settings-section">
                                    <h6 class="editor-section-title"><i class="fas fa-th-large me-2"></i>Element Panel Items</h6>
                                    <p class="editor-field-hint mb-3">Enable or disable each element type in the Elements panel (left sidebar) of the design editor.</p>
                                @if(isset($settings['elements']))
                                    <div class="row">
                                        @foreach($settings['elements'] as $key => $item)
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="{{ $key }}" value="0">
                                                <input class="form-check-input" type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1" {{ old($key, $item['value']) == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="{{ $key }}">{{ $item['label'] }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="editor-settings-footer">
                        <a href="{{ route('admin.settings', ['tab' => 'general']) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Settings
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Settings
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="editor-settings-sidebar">
                    <div class="editor-sidebar-header">
                        <i class="fas fa-palette"></i>
                        <h5>Editor Settings</h5>
                    </div>
                    <p class="editor-sidebar-desc">
                        Configure default options for the multi-page design editor. These apply when creating new designs.
                    </p>
                    <div class="editor-sidebar-block">
                        <div class="editor-sidebar-block-icon"><i class="fas fa-expand-arrows-alt"></i></div>
                        <div>
                            <strong>Canvas</strong>
                            <p>Default width, height, and background color for new pages.</p>
                        </div>
                    </div>
                    <div class="editor-sidebar-block">
                        <div class="editor-sidebar-block-icon"><i class="fas fa-sliders-h"></i></div>
                        <div>
                            <strong>Editor Options</strong>
                            <p>Menu bar, context menu, rulers, grid snap, auto-save, and custom links.</p>
                        </div>
                    </div>
                    <div class="editor-sidebar-block">
                        <div class="editor-sidebar-block-icon"><i class="fas fa-image"></i></div>
                        <div>
                            <strong>Image Options</strong>
                            <p>Reduce image size on add, max dimension, and quality settings.</p>
                        </div>
                    </div>
                    <div class="editor-sidebar-block">
                        <div class="editor-sidebar-block-icon"><i class="fas fa-th-large"></i></div>
                        <div>
                            <strong>Element Panel</strong>
                            <p>Enable/disable text, shapes, table, and upload image in the Elements panel.</p>
                        </div>
                    </div>
                    <div class="editor-sidebar-tip">
                        <i class="fas fa-lightbulb"></i>
                        <span>All settings across tabs are saved together when you click Save Settings.</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .editor-settings-page { padding: 0.5rem 0 2rem; }
    .editor-settings-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .editor-settings-tabs-wrapper {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0 1.5rem;
    }
    .editor-settings-tabs {
        display: flex;
        gap: 0.25rem;
        margin: 0;
        padding: 0;
        list-style: none;
        border: none;
    }
    .editor-settings-tabs .nav-link {
        padding: 1rem 1.25rem;
        font-size: 0.9rem;
        font-weight: 500;
        color: #64748b;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        margin-bottom: -1px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
    }
    .editor-settings-tabs .nav-link:hover {
        color: #6366f1;
        background: rgba(99, 102, 241, 0.05);
    }
    .editor-settings-tabs .nav-link.active {
        color: #6366f1;
        border-bottom-color: #6366f1;
        background: white;
    }
    .editor-settings-tab-content { padding: 1.5rem 1.75rem 1.25rem; }
    .editor-settings-tab-content .tab-content { border: none; }
    .editor-settings-section {
        margin-bottom: 1.75rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .editor-settings-section:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    .editor-section-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
    }
    .editor-section-title i { color: #6366f1; opacity: 0.9; }
    .editor-settings-field {
        margin-bottom: 1rem;
    }
    .editor-settings-field:last-child { margin-bottom: 0; }
    .editor-field-hint {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 0.75rem;
        line-height: 1.4;
    }
    .editor-settings-page .form-label { font-size: 0.85rem; font-weight: 500; margin-bottom: 0.35rem; color: #475569; }
    .editor-settings-page .form-control, .editor-settings-page .form-select {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        min-height: 38px;
        border-radius: 8px;
        border-color: #e2e8f0;
    }
    .editor-settings-page .form-control:focus, .editor-settings-page .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .editor-settings-page .form-check-label { font-size: 0.875rem; color: #475569; }
    .editor-settings-page .form-check-input {
        width: 1.15rem;
        height: 1.15rem;
        accent-color: #6366f1;
    }
    .editor-settings-page small.text-muted { font-size: 0.75rem; margin-top: 0.25rem; color: #94a3b8; }
    .editor-settings-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding: 1rem 1.75rem 1.25rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }
    .editor-settings-footer .btn { padding: 0.5rem 1.25rem; font-weight: 500; border-radius: 8px; }
    .editor-settings-sidebar {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        position: sticky;
        top: 1.5rem;
    }
    .editor-sidebar-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .editor-sidebar-header i {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        border-radius: 10px;
        font-size: 1rem;
    }
    .editor-sidebar-header h5 { margin: 0; font-size: 1rem; font-weight: 600; color: #1e293b; }
    .editor-sidebar-desc {
        font-size: 0.8rem;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 1.25rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .editor-sidebar-block {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .editor-sidebar-block-icon {
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #6366f1;
        border-radius: 8px;
        font-size: 0.8rem;
    }
    .editor-sidebar-block strong { font-size: 0.8rem; color: #334155; display: block; margin-bottom: 0.15rem; }
    .editor-sidebar-block p { font-size: 0.75rem; color: #94a3b8; margin: 0; line-height: 1.4; }
    .editor-sidebar-tip {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        margin-top: 1rem;
        padding: 0.75rem;
        background: rgba(99, 102, 241, 0.08);
        border-radius: 8px;
        border: 1px solid rgba(99, 102, 241, 0.15);
    }
    .editor-sidebar-tip i { color: #6366f1; font-size: 0.9rem; margin-top: 0.1rem; flex-shrink: 0; }
    .editor-sidebar-tip span { font-size: 0.75rem; color: #475569; line-height: 1.4; }
    .context-menu-link-row, .menu-bar-link-row {
        padding: 0.5rem;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.querySelector('input[type="color"]');
    const textInput = document.getElementById('editor_default_bg_color');
    if (colorInput && textInput) {
        textInput.addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                colorInput.value = this.value;
            }
        });
    }
    const tabInput = document.querySelector('#editorSettingsForm input[name="tab"]');
    const tabButtons = document.querySelectorAll('#editorSettingsTabs button[data-bs-toggle="tab"]');
    if (tabInput && tabButtons.length) {
        tabButtons.forEach(function(btn) {
            btn.addEventListener('shown.bs.tab', function(e) {
                const target = e.target.getAttribute('data-bs-target');
                if (target === '#canvas') tabInput.value = 'canvas';
                else if (target === '#options') tabInput.value = 'options';
                else if (target === '#image') tabInput.value = 'image';
                else if (target === '#elements') tabInput.value = 'elements';
            });
        });
    }

    function updateContextMenuLinksHidden() {
        const rows = document.querySelectorAll('#contextMenuLinksList .context-menu-link-row');
        const links = [];
        rows.forEach(function(row) {
            const inputs = row.querySelectorAll('input[type="text"]');
            const label = inputs[0] ? inputs[0].value.trim() : '';
            const url = inputs[1] ? inputs[1].value.trim() : '';
            const icon = inputs[2] ? inputs[2].value.trim() : '';
            if (label && url) {
                links.push({ label: label, url: url, icon: icon || 'fa-link' });
            }
        });
        document.getElementById('editor_context_menu_links').value = JSON.stringify(links);
    }

    document.getElementById('addContextMenuLink').addEventListener('click', function() {
        const list = document.getElementById('contextMenuLinksList');
        const emptyMsg = list.querySelector('#contextMenuLinksEmptyMsg') || list.querySelector('.text-muted.small');
        if (emptyMsg) emptyMsg.remove();
        const index = list.querySelectorAll('.context-menu-link-row').length;
        const row = document.createElement('div');
        row.className = 'context-menu-link-row mb-2 d-flex gap-2 align-items-center';
        row.setAttribute('data-index', index);
        row.innerHTML = '<input type="text" class="form-control form-control-sm" placeholder="Label" style="flex: 1; min-width: 100px;">' +
            '<input type="text" class="form-control form-control-sm" placeholder="URL (e.g. /help or https://example.com)" style="flex: 2; min-width: 150px;">' +
            '<input type="text" class="form-control form-control-sm" placeholder="Icon (e.g. fa-question)" style="flex: 0 0 100px;">' +
            '<button type="button" class="btn btn-outline-danger btn-sm remove-context-link" title="Remove"><i class="fas fa-times"></i></button>';
        list.appendChild(row);
        row.querySelector('.remove-context-link').addEventListener('click', function() {
            row.remove();
            updateContextMenuLinksHidden();
        });
        row.querySelectorAll('input').forEach(function(inp) {
            inp.addEventListener('input', updateContextMenuLinksHidden);
        });
        updateContextMenuLinksHidden();
    });

    document.querySelectorAll('.remove-context-link').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('.context-menu-link-row').remove();
            updateContextMenuLinksHidden();
        });
    });
    document.querySelectorAll('#contextMenuLinksList input[type="text"]').forEach(function(inp) {
        inp.addEventListener('input', updateContextMenuLinksHidden);
    });
    document.getElementById('editorSettingsForm').addEventListener('submit', function() {
        updateContextMenuLinksHidden();
        updateMenuBarLinksHidden();
    });

    function updateMenuBarLinksHidden() {
        const rows = document.querySelectorAll('#menuBarLinksList .menu-bar-link-row');
        const links = [];
        rows.forEach(function(row) {
            const inputs = row.querySelectorAll('input[type="text"]');
            const label = inputs[0] ? inputs[0].value.trim() : '';
            const url = inputs[1] ? inputs[1].value.trim() : '';
            const icon = inputs[2] ? inputs[2].value.trim() : '';
            if (label && url) {
                links.push({ label: label, url: url, icon: icon || 'fa-link' });
            }
        });
        document.getElementById('editor_menu_bar_links').value = JSON.stringify(links);
    }

    document.getElementById('addMenuBarLink').addEventListener('click', function() {
        const list = document.getElementById('menuBarLinksList');
        const emptyMsg = list.querySelector('#menuBarLinksEmptyMsg') || list.querySelector('.text-muted.small');
        if (emptyMsg) emptyMsg.remove();
        const row = document.createElement('div');
        row.className = 'menu-bar-link-row mb-2 d-flex gap-2 align-items-center';
        row.innerHTML = '<input type="text" class="form-control form-control-sm" placeholder="Label" style="flex: 1; min-width: 100px;">' +
            '<input type="text" class="form-control form-control-sm" placeholder="URL (e.g. /help or https://example.com)" style="flex: 2; min-width: 150px;">' +
            '<input type="text" class="form-control form-control-sm" placeholder="Icon (e.g. fa-question)" style="flex: 0 0 100px;">' +
            '<button type="button" class="btn btn-outline-danger btn-sm remove-menu-bar-link" title="Remove"><i class="fas fa-times"></i></button>';
        list.appendChild(row);
        row.querySelector('.remove-menu-bar-link').addEventListener('click', function() {
            row.remove();
            updateMenuBarLinksHidden();
        });
        row.querySelectorAll('input').forEach(function(inp) {
            inp.addEventListener('input', updateMenuBarLinksHidden);
        });
        updateMenuBarLinksHidden();
    });

    document.querySelectorAll('.remove-menu-bar-link').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('.menu-bar-link-row').remove();
            updateMenuBarLinksHidden();
        });
    });
    document.querySelectorAll('#menuBarLinksList input[type="text"]').forEach(function(inp) {
        inp.addEventListener('input', updateMenuBarLinksHidden);
    });
});
</script>
@endpush
@endsection
