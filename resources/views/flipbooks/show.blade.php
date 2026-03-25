@extends('layouts.app')

@section('title', $flipBook->title)
@section('page-title', $flipBook->title)

@push('styles')
<style>
    .flipbook-show-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        color: white;
        margin-bottom: 1.5rem;
    }
    .flipbook-show-header .back-link {
        color: rgba(255,255,255,0.9);
        font-size: 0.8125rem;
        font-weight: 500;
        transition: color 0.2s;
    }
    .flipbook-show-header .back-link:hover {
        color: white;
    }
    .flipbook-show-header h1 {
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0.5rem 0 0.25rem 0;
        letter-spacing: -0.02em;
    }
    .flipbook-show-header .meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
        margin-top: 0.75rem;
        font-size: 0.8125rem;
        opacity: 0.95;
    }
    .flipbook-show-header .meta-row .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.6rem;
        font-weight: 600;
    }
    .flipbook-show-content {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 1.5rem;
    }
    @media (max-width: 991px) {
        .flipbook-show-content {
            grid-template-columns: 1fr;
        }
    }
    .flipbook-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .flipbook-cover-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .flipbook-cover-card .cover-img {
        aspect-ratio: 3/4;
        object-fit: cover;
        width: 100%;
    }
    .flipbook-cover-card .card-body {
        padding: 0.85rem 1rem;
        font-size: 0.8125rem;
        color: #64748b;
    }
    .flipbook-actions-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        padding: 1rem;
    }
    .flipbook-actions-card .btn {
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .flipbook-actions-card .btn:last-child {
        margin-bottom: 0;
    }
    .flipbook-main {
        min-width: 0;
    }
    .flipbook-section {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 1rem;
    }
    .flipbook-section .card-body {
        padding: 1.25rem 1.5rem;
    }
    .flipbook-section-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .flipbook-pages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 0.75rem;
    }
    .flipbook-page-item {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        transition: all 0.2s;
    }
    .flipbook-page-item:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
    }
    .flipbook-page-item img {
        width: 100%;
        aspect-ratio: 3/4;
        object-fit: cover;
        display: block;
    }
    .flipbook-page-item .page-label {
        padding: 0.35rem 0.5rem;
        font-size: 0.6875rem;
        font-weight: 600;
        color: #64748b;
        text-align: center;
        background: #f8fafc;
    }
    .flipbook-settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0.75rem;
    }
    .flipbook-setting-item {
        font-size: 0.8125rem;
        padding: 0.5rem 0.75rem;
        background: #f8fafc;
        border-radius: 6px;
    }
    .flipbook-setting-item strong {
        color: #475569;
        font-weight: 600;
    }
    .flipbook-sheet-type-detail {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1rem;
    }
    @media (max-width: 767px) {
        .flipbook-sheet-type-detail {
            grid-template-columns: 1fr;
        }
    }
    .flipbook-sheet-type-video {
        border-radius: 8px;
        overflow: hidden;
        background: #1e293b;
        aspect-ratio: 16/10;
    }
    .flipbook-sheet-type-video video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .flipbook-sheet-type-desc {
        font-size: 0.875rem;
        color: #475569;
        line-height: 1.6;
    }
    .inline-edit {
        cursor: pointer;
        border-radius: 4px;
        padding: 0.15rem 0.35rem;
        margin: -0.15rem -0.35rem;
        transition: background 0.2s;
    }
    .inline-edit:hover {
        background: rgba(255,255,255,0.2);
    }
    .inline-edit-input, .inline-edit-textarea {
        width: 100%;
        background: rgba(255,255,255,0.95);
        color: #1e293b;
        border: 2px solid rgba(255,255,255,0.8);
        border-radius: 6px;
        padding: 0.35rem 0.5rem;
        font-size: inherit;
        font-weight: inherit;
    }
    .inline-edit-textarea {
        min-height: 80px;
        resize: vertical;
    }
    .section-edit-btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        background: transparent;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }
    .section-edit-btn:hover {
        background: #f1f5f9;
        color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .edit-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }
    .edit-form-group label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #64748b;
        display: block;
        margin-bottom: 0.25rem;
    }
    .edit-form-group select, .edit-form-group input {
        width: 100%;
        padding: 0.35rem 0.5rem;
        font-size: 0.8125rem;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
    }
    .edit-form-actions {
        margin-top: 1rem;
        display: flex;
        gap: 0.5rem;
    }
    .edit-form-actions .btn-sm {
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
    }
    .flipbook-pricing-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        padding: 1rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px solid #bbf7d0;
    }
    .flipbook-pricing-card .pricing-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.8125rem;
        padding: 0.35rem 0;
        color: #475569;
    }
    .flipbook-pricing-card .pricing-total {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary-color);
        padding-top: 0.5rem;
        margin-top: 0.5rem;
        border-top: 2px solid #bbf7d0;
    }
</style>
@endpush

@section('content')
@php
    $printSettings = $flipBook->settings['print_settings'] ?? null;
    $hasPrintDetails = $printSettings && (!empty($printSettings['print_sheet_type']) || !empty($printSettings['print_size']) || !empty($printSettings['print_quality']) || !empty($printSettings['binding_type']));
@endphp
<div class="flipbook-show-header">
    <a href="{{ route('design.index') }}" class="back-link text-decoration-none">
        <i class="fas fa-arrow-left me-2"></i>Back to My Designs
    </a>
    <h1>
        <span id="flipbookTitleDisplay" class="inline-edit" data-value="{{ e($flipBook->title) }}" title="Click to edit">{{ $flipBook->title }}</span>
    </h1>
    <div class="mb-0" style="font-size: 0.875rem; opacity: 0.9;">
        <span id="flipbookDescDisplay" class="inline-edit" data-value="{{ e($flipBook->description ?? '') }}" title="Click to edit" style="{{ empty($flipBook->description) ? 'font-style: italic; opacity: 0.8;' : '' }}">{{ $flipBook->description ?: 'Add description...' }}</span>
    </div>
    <div class="meta-row">
        <span class="badge bg-{{ $flipBook->status === 'published' ? 'success' : 'secondary' }}">
            {{ ucfirst($flipBook->status) }}
        </span>
        <span><i class="fas fa-calendar me-1"></i>{{ $flipBook->created_at->format('M d, Y') }}</span>
        <span><i class="fas fa-file-alt me-1"></i>{{ count($flipBook->pages ?? []) }} pages</span>
    </div>
</div>

<div class="flipbook-show-content">
    <aside class="flipbook-sidebar">
        <div class="card flipbook-cover-card">
            @if($flipBook->cover_image)
                <img src="{{ asset('storage/' . $flipBook->cover_image) }}" alt="Cover" class="cover-img">
            @else
                <div style="aspect-ratio: 3/4; background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-book-open" style="font-size: 3rem; color: #94a3b8;"></i>
                </div>
            @endif
            <div class="card-body text-center">
                Cover
            </div>
        </div>

        <div class="card flipbook-actions-card">
            @if($flipBook->pages && count($flipBook->pages) > 0)
                <a href="{{ route('flipbooks.preview', $flipBook->id) }}" class="btn btn-primary">
                    <i class="fas fa-eye"></i>Preview Flip Book
                </a>
            @endif
            @php $hasDesign = isset($flipBook->settings['created_from_design']) && $flipBook->settings['created_from_design'] && !empty($flipBook->settings['design_data'] ?? null); @endphp
            @if($hasDesign)
                <a href="{{ route('design.create', ['multi' => 'true', 'edit_flipbook' => $flipBook->id]) }}" class="btn btn-outline-primary">
                    <i class="fas fa-palette"></i>Edit Design
                </a>
            @endif
            @if($flipBook->is_public && $flipBook->status === 'published')
                <a href="{{ route('flipbooks.public', $flipBook->slug) }}" target="_blank" class="btn btn-outline-success">
                    <i class="fas fa-external-link-alt"></i>Public URL
                </a>
            @endif
            <a href="{{ route('design.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-th-large"></i>My Designs
            </a>
        </div>

        @if($pricing && $hasPrintDetails && count($flipBook->pages ?? []) > 0)
        <div class="card flipbook-pricing-card">
            <h6 style="font-size: 0.875rem; font-weight: 700; margin-bottom: 0.75rem; color: #166534;"><i class="fas fa-calculator me-2"></i>Pricing</h6>
            <div class="pricing-row">
                <span>Per Page</span>
                <span>{{ $pricing['formatted_per_page'] ?? format_price($pricing['per_page'] ?? 0) }}</span>
            </div>
            <div class="pricing-row">
                <span>Subtotal ({{ count($flipBook->pages ?? []) }} pages)</span>
                <span>{{ $pricing['formatted_subtotal'] ?? format_price($pricing['subtotal'] ?? 0) }}</span>
            </div>
            <div class="pricing-row">
                <span>Binding</span>
                <span>{{ $pricing['formatted_binding'] ?? format_price($pricing['binding_cost'] ?? 0) }}</span>
            </div>
            <div class="pricing-row">
                <span>Shipping</span>
                <span>{{ $pricing['formatted_shipping'] ?? format_price($pricing['shipping'] ?? 0) }}</span>
            </div>
            @if(($pricing['bundle_quantity'] ?? 1) > 1)
            <div class="pricing-row">
                <span>Bundle Qty</span>
                <span>{{ $pricing['bundle_quantity'] ?? 1 }}</span>
            </div>
            @endif
            <div class="pricing-row pricing-total">
                <span>Total</span>
                <span>{{ $pricing['formatted'] ?? format_price($pricing['total'] ?? 0) }}</span>
            </div>
        </div>
        @endif
    </aside>

    <main class="flipbook-main">
        <div class="card flipbook-section">
            <div class="card-body">
                <h5 class="flipbook-section-title">Pages</h5>
                @if($flipBook->pages && count($flipBook->pages) > 0)
                    <div class="flipbook-pages-grid">
                        @foreach($flipBook->pages as $index => $page)
                            <div class="flipbook-page-item">
                                <img src="{{ asset('storage/' . $page['path']) }}" alt="Page {{ $index + 1 }}">
                                <div class="page-label">Page {{ $index + 1 }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">No pages yet.</p>
                @endif
            </div>
        </div>

        <div class="card flipbook-section" id="printDetailsSection">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="flipbook-section-title mb-0"><i class="fas fa-print me-2"></i>Print Details</h5>
                    <button type="button" class="section-edit-btn" id="editPrintDetailsBtn" onclick="togglePrintDetailsEdit()"><i class="fas fa-edit me-1"></i>Edit</button>
                </div>
                <div id="printDetailsView">
                <div class="flipbook-settings-grid">
                    @if(!empty($printSettings['print_sheet_type']))
                    <div class="flipbook-setting-item" style="grid-column: 1 / -1;">
                        <strong>Sheet Type:</strong> {{ $sheetType ? $sheetType->name : ucfirst($printSettings['print_sheet_type']) }}
                    </div>
                    @endif
                    @if(!empty($printSettings['print_size']))
                    <div class="flipbook-setting-item">
                        <strong>Print Size:</strong> {{ $printSettings['print_size'] }}{{ $printSettings['print_size'] === 'Custom' && !empty($printSettings['print_custom_width']) && !empty($printSettings['print_custom_height']) ? ' (' . $printSettings['print_custom_width'] . ' × ' . $printSettings['print_custom_height'] . ' mm)' : '' }}
                    </div>
                    @endif
                    @if(!empty($printSettings['print_quality']))
                    <div class="flipbook-setting-item">
                        <strong>Print Quality:</strong> {{ ucfirst($printSettings['print_quality']) }}
                    </div>
                    @endif
                    @if(!empty($printSettings['binding_type']) && $printSettings['binding_type'] !== 'none')
                    <div class="flipbook-setting-item">
                        <strong>Binding:</strong> {{ ucfirst(str_replace('-', ' ', $printSettings['binding_type'])) }}
                    </div>
                    @endif
                    @if(!empty($printSettings['bundle_quantity']) && $printSettings['bundle_quantity'] > 1)
                    <div class="flipbook-setting-item">
                        <strong>Bundle Quantity:</strong> {{ $printSettings['bundle_quantity'] }}
                    </div>
                    @endif
                    @if(!$hasPrintDetails)
                    <p class="text-muted mb-0" style="font-size: 0.8125rem; grid-column: 1 / -1;">No print details yet. <a href="#" onclick="togglePrintDetailsEdit(); return false;">Add print settings</a></p>
                    @endif
                </div>

                @if($sheetType && (!empty($sheetType->video_url) || !empty($sheetType->description)))
                <div class="flipbook-sheet-type-detail">
                    @if($sheetType->video_url)
                    <div class="flipbook-sheet-type-video">
                        <video src="{{ $sheetType->video_url }}" controls muted loop playsinline preload="metadata"></video>
                    </div>
                    @endif
                    @if($sheetType->description)
                    <div class="flipbook-sheet-type-desc">
                        <strong style="font-size: 0.75rem; color: #64748b; display: block; margin-bottom: 0.5rem;">About {{ $sheetType->name }}</strong>
                        {{ $sheetType->description }}
                    </div>
                    @endif
                </div>
                @endif
                </div>

                <div id="printDetailsEdit" class="d-none">
                    <div class="edit-form-grid">
                        <div class="edit-form-group">
                            <label>Sheet Type</label>
                            <select id="editPrintSheetType" class="form-control form-control-sm">
                                <option value="">Select sheet type</option>
                                @forelse($sheetTypes ?? [] as $st)
                                <option value="{{ $st->slug }}" {{ ($printSettings['print_sheet_type'] ?? '') == $st->slug ? 'selected' : '' }}>{{ $st->name }}</option>
                                @empty
                                <option value="" disabled>No sheet types in stock</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="edit-form-group">
                            <label>Print Size</label>
                            <select id="editPrintSize" class="form-control form-control-sm">
                                <option value="">Select size</option>
                                <option value="A4" {{ ($printSettings['print_size'] ?? '') == 'A4' ? 'selected' : '' }}>A4</option>
                                <option value="A5" {{ ($printSettings['print_size'] ?? '') == 'A5' ? 'selected' : '' }}>A5</option>
                                <option value="A3" {{ ($printSettings['print_size'] ?? '') == 'A3' ? 'selected' : '' }}>A3</option>
                                <option value="Letter" {{ ($printSettings['print_size'] ?? '') == 'Letter' ? 'selected' : '' }}>Letter</option>
                                <option value="Legal" {{ ($printSettings['print_size'] ?? '') == 'Legal' ? 'selected' : '' }}>Legal</option>
                                <option value="Custom" {{ ($printSettings['print_size'] ?? '') == 'Custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                        </div>
                        <div class="edit-form-group" id="editCustomSizeGroup" style="{{ ($printSettings['print_size'] ?? '') == 'Custom' ? '' : 'display:none;' }}">
                            <label>Custom Width (mm)</label>
                            <input type="number" id="editPrintCustomWidth" class="form-control form-control-sm" value="{{ $printSettings['print_custom_width'] ?? '' }}" placeholder="Width">
                        </div>
                        <div class="edit-form-group" id="editCustomHeightGroup" style="{{ ($printSettings['print_size'] ?? '') == 'Custom' ? '' : 'display:none;' }}">
                            <label>Custom Height (mm)</label>
                            <input type="number" id="editPrintCustomHeight" class="form-control form-control-sm" value="{{ $printSettings['print_custom_height'] ?? '' }}" placeholder="Height">
                        </div>
                        <div class="edit-form-group">
                            <label>Print Quality</label>
                            <select id="editPrintQuality" class="form-control form-control-sm">
                                <option value="standard" {{ ($printSettings['print_quality'] ?? '') == 'standard' ? 'selected' : '' }}>Standard</option>
                                <option value="high" {{ ($printSettings['print_quality'] ?? '') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="premium" {{ ($printSettings['print_quality'] ?? '') == 'premium' ? 'selected' : '' }}>Premium</option>
                            </select>
                        </div>
                        <div class="edit-form-group">
                            <label>Binding</label>
                            <select id="editBindingType" class="form-control form-control-sm">
                                <option value="none" {{ ($printSettings['binding_type'] ?? '') == 'none' ? 'selected' : '' }}>None</option>
                                <option value="spiral" {{ ($printSettings['binding_type'] ?? '') == 'spiral' ? 'selected' : '' }}>Spiral</option>
                                <option value="perfect" {{ ($printSettings['binding_type'] ?? '') == 'perfect' ? 'selected' : '' }}>Perfect</option>
                                <option value="saddle" {{ ($printSettings['binding_type'] ?? '') == 'saddle' ? 'selected' : '' }}>Saddle Stitch</option>
                                <option value="wire" {{ ($printSettings['binding_type'] ?? '') == 'wire' ? 'selected' : '' }}>Wire-O</option>
                            </select>
                        </div>
                        <div class="edit-form-group">
                            <label>Bundle Quantity</label>
                            <input type="number" id="editBundleQuantity" class="form-control form-control-sm" value="{{ $printSettings['bundle_quantity'] ?? 1 }}" min="1" max="999">
                        </div>
                    </div>
                    <div class="edit-form-actions">
                        <button type="button" class="btn btn-primary btn-sm" onclick="savePrintDetails()"><i class="fas fa-check me-1"></i>Save</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="togglePrintDetailsEdit()">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card flipbook-section" id="settingsSection">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="flipbook-section-title mb-0">Settings</h5>
                    <button type="button" class="section-edit-btn" onclick="toggleSettingsEdit()"><i class="fas fa-edit me-1"></i>Edit</button>
                </div>
                <div id="settingsView">
                <div class="flipbook-settings-grid">
                    <div class="flipbook-setting-item">
                        <strong>Transition:</strong> <span id="viewTransition">{{ ucfirst($flipBook->settings['transition_effect'] ?? 'slide') }}</span>
                    </div>
                    <div class="flipbook-setting-item">
                        <strong>Auto Play:</strong> <span id="viewAutoPlay">{{ ($flipBook->settings['auto_play'] ?? false) ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="flipbook-setting-item" id="viewIntervalRow" style="{{ ($flipBook->settings['auto_play'] ?? false) ? '' : 'display:none;' }}">
                        <strong>Interval:</strong> <span id="viewInterval">{{ $flipBook->settings['auto_play_interval'] ?? 5 }}</span>s
                    </div>
                    <div class="flipbook-setting-item">
                        <strong>Controls:</strong> <span id="viewControls">{{ ($flipBook->settings['show_controls'] ?? true) ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="flipbook-setting-item">
                        <strong>Thumbnails:</strong> <span id="viewThumbnails">{{ ($flipBook->settings['show_thumbnails'] ?? true) ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="flipbook-setting-item">
                        <strong>Public:</strong> <span id="viewPublic">{{ $flipBook->is_public ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
                </div>
                <div id="settingsEdit" class="d-none">
                    <div class="edit-form-grid">
                        <div class="edit-form-group">
                            <label>Transition Effect</label>
                            <select id="editTransition" class="form-control form-control-sm">
                                <option value="slide" {{ ($flipBook->settings['transition_effect'] ?? '') == 'slide' ? 'selected' : '' }}>Slide</option>
                                <option value="flip" {{ ($flipBook->settings['transition_effect'] ?? '') == 'flip' ? 'selected' : '' }}>Flip</option>
                                <option value="fade" {{ ($flipBook->settings['transition_effect'] ?? '') == 'fade' ? 'selected' : '' }}>Fade</option>
                            </select>
                        </div>
                        <div class="edit-form-group">
                            <label>Auto Play</label>
                            <select id="editAutoPlay" class="form-control form-control-sm">
                                <option value="0" {{ !($flipBook->settings['auto_play'] ?? false) ? 'selected' : '' }}>No</option>
                                <option value="1" {{ ($flipBook->settings['auto_play'] ?? false) ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                        <div class="edit-form-group">
                            <label>Auto Play Interval (s)</label>
                            <input type="number" id="editAutoPlayInterval" class="form-control form-control-sm" value="{{ $flipBook->settings['auto_play_interval'] ?? 5 }}" min="1" max="60">
                        </div>
                        <div class="edit-form-group">
                            <label>Show Controls</label>
                            <select id="editShowControls" class="form-control form-control-sm">
                                <option value="1" {{ ($flipBook->settings['show_controls'] ?? true) ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !($flipBook->settings['show_controls'] ?? true) ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="edit-form-group">
                            <label>Show Thumbnails</label>
                            <select id="editShowThumbnails" class="form-control form-control-sm">
                                <option value="1" {{ ($flipBook->settings['show_thumbnails'] ?? true) ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !($flipBook->settings['show_thumbnails'] ?? true) ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="edit-form-group">
                            <label>Public</label>
                            <select id="editIsPublic" class="form-control form-control-sm">
                                <option value="0" {{ !$flipBook->is_public ? 'selected' : '' }}>No</option>
                                <option value="1" {{ $flipBook->is_public ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                    </div>
                    <div class="edit-form-actions">
                        <button type="button" class="btn btn-primary btn-sm" onclick="saveSettings()"><i class="fas fa-check me-1"></i>Save</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleSettingsEdit()">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const flipbookId = {{ $flipBook->id }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    function initInlineEdit(displayEl, field, isTextarea) {
        if (!displayEl) return;
        displayEl.addEventListener('click', function(e) {
            e.stopPropagation();
            const value = displayEl.dataset.value || '';
            const input = document.createElement(isTextarea ? 'textarea' : 'input');
            input.type = isTextarea ? null : 'text';
            input.value = value;
            input.className = isTextarea ? 'inline-edit-textarea' : 'inline-edit-input';
            input.style.cssText = 'width: 100%; margin: -0.15rem -0.35rem;';
            if (isTextarea) input.rows = 3;

            const save = () => {
                const newVal = input.value.trim();
                displayEl.dataset.value = newVal;
                displayEl.textContent = newVal || (field === 'description' ? 'Add description...' : '');
                if (field === 'description' && !newVal) displayEl.style.fontStyle = 'italic';
                else if (field === 'description') displayEl.style.fontStyle = '';
                displayEl.style.display = '';
                input.remove();

                if (newVal !== value) {
                    fetch('{{ route("flipbooks.update", ":id") }}'.replace(':id', flipbookId), {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ [field]: newVal })
                    }).then(r => r.json()).then(data => {
                        if (!data.success) alert(data.message || 'Failed to update');
                    }).catch(() => alert('Failed to update'));
                }
            };

            input.addEventListener('blur', save);
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !isTextarea) { e.preventDefault(); save(); }
                if (e.key === 'Escape') {
                    displayEl.style.display = '';
                    input.remove();
                }
            });

            displayEl.style.display = 'none';
            displayEl.parentNode.insertBefore(input, displayEl);
            input.focus();
            input.select();
        });
    }

    initInlineEdit(document.getElementById('flipbookTitleDisplay'), 'title', false);
    initInlineEdit(document.getElementById('flipbookDescDisplay'), 'description', true);

    // Print Details edit
    document.getElementById('editPrintSize')?.addEventListener('change', function() {
        const isCustom = this.value === 'Custom';
        document.getElementById('editCustomSizeGroup').style.display = isCustom ? 'block' : 'none';
        document.getElementById('editCustomHeightGroup').style.display = isCustom ? 'block' : 'none';
    });

    function togglePrintDetailsEdit() {
        const view = document.getElementById('printDetailsView');
        const edit = document.getElementById('printDetailsEdit');
        const btn = document.getElementById('editPrintDetailsBtn');
        if (edit.classList.contains('d-none')) {
            view.classList.add('d-none');
            edit.classList.remove('d-none');
            btn.innerHTML = '<i class="fas fa-times me-1"></i>Cancel';
        } else {
            view.classList.remove('d-none');
            edit.classList.add('d-none');
            btn.innerHTML = '<i class="fas fa-edit me-1"></i>Edit';
        }
    }

    function savePrintDetails() {
        const data = {
            print_settings: {
                print_sheet_type: document.getElementById('editPrintSheetType').value || null,
                print_size: document.getElementById('editPrintSize').value || null,
                print_custom_width: document.getElementById('editPrintCustomWidth').value || null,
                print_custom_height: document.getElementById('editPrintCustomHeight').value || null,
                print_quality: document.getElementById('editPrintQuality').value || null,
                binding_type: document.getElementById('editBindingType').value || null,
                bundle_quantity: parseInt(document.getElementById('editBundleQuantity').value) || 1
            }
        };
        fetch('{{ route("flipbooks.update", ":id") }}'.replace(':id', flipbookId), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(data)
        }).then(r => r.json()).then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update');
            }
        }).catch(() => alert('Failed to update'));
    }

    function toggleSettingsEdit() {
        const view = document.getElementById('settingsView');
        const edit = document.getElementById('settingsEdit');
        if (edit.classList.contains('d-none')) {
            view.classList.add('d-none');
            edit.classList.remove('d-none');
        } else {
            view.classList.remove('d-none');
            edit.classList.add('d-none');
        }
    }

    function saveSettings() {
        const data = {
            settings: {
                transition_effect: document.getElementById('editTransition').value,
                auto_play: document.getElementById('editAutoPlay').value === '1',
                auto_play_interval: parseInt(document.getElementById('editAutoPlayInterval').value) || 5,
                show_controls: document.getElementById('editShowControls').value === '1',
                show_thumbnails: document.getElementById('editShowThumbnails').value === '1'
            },
            is_public: document.getElementById('editIsPublic').value === '1'
        };
        fetch('{{ route("flipbooks.update", ":id") }}'.replace(':id', flipbookId), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(data)
        }).then(r => r.json()).then(data => {
            if (data.success) {
                document.getElementById('viewTransition').textContent = data.flipbook.settings?.transition_effect || 'slide';
                document.getElementById('viewAutoPlay').textContent = data.flipbook.settings?.auto_play ? 'Yes' : 'No';
                document.getElementById('viewInterval').textContent = data.flipbook.settings?.auto_play_interval || 5;
                document.getElementById('viewIntervalRow').style.display = data.flipbook.settings?.auto_play ? '' : 'none';
                document.getElementById('viewControls').textContent = data.flipbook.settings?.show_controls !== false ? 'Yes' : 'No';
                document.getElementById('viewThumbnails').textContent = data.flipbook.settings?.show_thumbnails !== false ? 'Yes' : 'No';
                document.getElementById('viewPublic').textContent = data.flipbook.is_public ? 'Yes' : 'No';
                toggleSettingsEdit();
            } else {
                alert(data.message || 'Failed to update');
            }
        }).catch(() => alert('Failed to update'));
    }
});
</script>
@endpush
@endsection
