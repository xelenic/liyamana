@extends('layouts.app')

@section('title', $template->name . ' - Template Details')

@push('styles')
<style>
    :root {
        --primary-color: #6366f1;
        --secondary-color: #8b5cf6;
        --light-bg: #f8fafc;
        --dark-text: #1e293b;
        --border-color: #e2e8f0;
    }

    .template-details-header {
        background: #fff;
        border-bottom: 1px solid var(--border-color);
        padding: 0.75rem 0 1rem;
        margin-bottom: 1rem;
        color: var(--dark-text);
    }

    .product-image-section {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--border-color);
        margin-bottom: 1rem;
    }

    .product-image-main {
        position: relative;
        width: 100%;
        aspect-ratio: 4/3;
        min-height: 260px;
        background: #fafbfc;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        border-bottom: 1px solid var(--border-color);
    }

    .product-image-main img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: opacity 0.2s;
    }

    .product-image-main .no-thumbnail {
        color: #cbd5e1;
        font-size: 4rem;
    }

    .product-image-main .placeholder-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-image-main .placeholder-bg .no-thumbnail {
        color: white;
        opacity: 0.9;
    }

    .product-thumbnails {
        display: flex;
        gap: 0.375rem;
        padding: 0.75rem 1rem;
        overflow-x: auto;
        scrollbar-width: thin;
        -webkit-overflow-scrolling: touch;
    }

    .product-thumbnails::-webkit-scrollbar {
        height: 6px;
    }

    .product-thumbnails::-webkit-scrollbar-track {
        background: var(--light-bg);
        border-radius: 3px;
    }

    .product-thumbnails::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .product-thumb-item {
        flex-shrink: 0;
        width: 72px;
        height: 72px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
        background: var(--light-bg);
    }

    .product-thumb-item:hover {
        border-color: rgba(99, 102, 241, 0.4);
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.15);
    }

    .product-thumb-item.active {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }

    .product-thumb-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    @media (max-width: 576px) {
        .product-image-main {
            min-height: 240px;
            padding: 1rem;
        }
        .product-thumb-item {
            width: 60px;
            height: 60px;
        }
        .product-thumbnails {
            padding: 0.75rem 1rem;
        }
    }


    .template-info-card {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid var(--border-color);
        margin-bottom: 0.75rem;
    }

    .template-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark-text);
        margin-bottom: 0.5rem;
    }

    .template-meta-info {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #64748b;
    }

    .meta-item i {
        color: var(--primary-color);
        font-size: 1rem;
    }

    .meta-item strong {
        color: var(--dark-text);
    }

    .template-category {
        display: inline-block;
        padding: 0.25rem 0.625rem;
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary-color);
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .template-description {
        font-size: 0.9375rem;
        color: #475569;
        line-height: 1.6;
        margin-bottom: 0;
    }

    .author-info {
        background: var(--light-bg);
        border-radius: 8px;
        padding: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin-bottom: 0.75rem;
    }

    .author-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.125rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .author-details h5 {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--dark-text);
        margin: 0 0 0.125rem 0;
    }

    .author-details p {
        font-size: 0.8125rem;
        color: #64748b;
        margin: 0;
    }

    .template-price-large {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .action-buttons {
        display: flex;
        gap: 0.3rem;
        flex-wrap: wrap;
        margin-top: 0;
    }

    .action-btn {
        flex: 1;
        min-width: 85px;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        border: none;
        cursor: pointer;
    }
    .action-btn i { font-size: 0.7rem; }

    .action-btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
    }

    .action-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
    }

    .action-btn-secondary {
        background: white;
        color: var(--primary-color);
        border: 1.5px solid var(--primary-color);
    }

    .action-btn-secondary:hover {
        background: var(--light-bg);
        transform: translateY(-1px);
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        color: #64748b;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.8125rem;
        transition: color 0.2s;
        margin-bottom: 0.5rem;
    }

    .back-link:hover {
        color: var(--primary-color);
    }

    .back-link i {
        font-size: 0.75rem;
    }


    .template-header-inner {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .template-header-content { flex: 1; min-width: 0; }

    .template-header-title {
        font-size: 1.375rem;
        font-weight: 700;
        color: var(--dark-text);
        line-height: 1.3;
        margin: 0 0 0.375rem 0;
        letter-spacing: -0.02em;
    }

    .template-header-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.8125rem;
        color: #64748b;
    }

    .template-header-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }

    .template-header-meta i {
        font-size: 0.75rem;
        color: var(--primary-color);
    }

    .template-header-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .template-header-category {
        padding: 0.375rem 0.75rem;
        background: rgba(99, 102, 241, 0.08);
        color: var(--primary-color);
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .template-header-price-tag {
        padding: 0.375rem 0.75rem;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        color: #065f46;
        border-radius: 6px;
        font-size: 0.8125rem;
        font-weight: 700;
    }

    .template-header-price-tag.free {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        color: #4338ca;
    }


    /* Price Badge */
    .price-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-radius: 8px;
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .price-badge.free {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    }

    /* Public Badge */
    .public-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        background: #eef2ff;
        color: #6366f1;
        border-radius: 6px;
        font-size: 0.8125rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .template-meta-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.625rem;
        background: var(--light-bg);
        border-radius: 6px;
        font-size: 0.8125rem;
    }

    .template-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.375rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
    }

    .stat-item {
        text-align: center;
        padding: 0.5rem 0.375rem;
        background: var(--light-bg);
        border-radius: 6px;
    }

    .stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-color);
        display: block;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.6875rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 0.25rem;
        display: block;
    }

    .template-sidebar-panel {
        position: sticky;
        top: 1.5rem;
        background: white;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        overflow: hidden;
    }

    .sidebar-section {
        padding: 0.875rem 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .sidebar-section:last-child {
        border-bottom: none;
    }

    .sidebar-section-title {
        font-size: 0.625rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .sidebar-section-title i {
        font-size: 0.75rem;
        color: var(--primary-color);
    }

    .sidebar-price-block {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-radius: 8px;
        border: 1px solid #bbf7d0;
    }

    .sidebar-price-block.free {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        border-color: #c7d2fe;
    }

    .sidebar-price-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .sidebar-price-block.free .sidebar-price-icon {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    }

    .sidebar-price-text {
        font-size: 1.25rem;
        font-weight: 700;
        color: #065f46;
    }

    .sidebar-price-block.free .sidebar-price-text {
        color: #4338ca;
    }

    .sidebar-price-label {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.125rem;
    }

    .sidebar-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
        gap: 0.375rem;
    }

    .sidebar-meta-item {
        text-align: center;
        padding: 0.5rem 0.375rem;
        background: var(--light-bg);
        border-radius: 6px;
    }

    .sidebar-meta-value {
        font-size: 1rem;
        font-weight: 700;
        color: var(--dark-text);
        display: block;
        line-height: 1.2;
    }

    .sidebar-meta-label {
        font-size: 0.6875rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 0.25rem;
    }

    .sidebar-name-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .sidebar-template-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--dark-text);
        line-height: 1.3;
        flex: 1;
        min-width: 0;
    }

    .template-header-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .template-header-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 0.875rem;
        border: 1px solid var(--border-color);
        background: white;
        border-radius: 8px;
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .template-header-action-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        background: rgba(99, 102, 241, 0.06);
    }

    .template-header-action-btn.favorited {
        border-color: #fecaca;
        color: #ef4444;
        background: rgba(239, 68, 68, 0.06);
    }

    .template-header-action-btn.favorited:hover {
        border-color: #ef4444;
        background: rgba(239, 68, 68, 0.1);
    }

    .template-detail-tabs .nav-link {
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
        padding: 0.5rem 1rem;
        border: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        background: none;
    }
    .template-detail-tabs .nav-link:hover { color: var(--primary-color); }
    .template-detail-tabs .nav-link.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.06);
    }
    .template-detail-tab-content { padding-top: 0.75rem; }
    .sidebar-price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.375rem 0;
        font-size: 0.875rem;
    }
    .sidebar-price-row .label { color: #64748b; }
    .sidebar-price-row .value { font-weight: 600; color: var(--dark-text); }
    .sidebar-price-row.total .value { font-size: 1.125rem; color: var(--primary-color); }

    .letter-product-card {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        background: white;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        margin-bottom: 1rem;
        align-items: flex-start;
    }
    .letter-product-card:last-child { margin-bottom: 0; }
    .letter-product-image {
        flex-shrink: 0;
        width: 140px;
        height: 140px;
        border-radius: 8px;
        overflow: hidden;
        background: var(--light-bg);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .letter-product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .letter-product-image .no-image {
        color: #cbd5e1;
        font-size: 2rem;
    }
    .letter-product-details { flex: 1; min-width: 0; }
    .letter-product-name { font-size: 1.125rem; font-weight: 700; color: var(--dark-text); margin-bottom: 0.25rem; }
    .letter-product-sku { font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; }
    .letter-product-desc { font-size: 0.875rem; color: #475569; line-height: 1.5; margin-bottom: 0.75rem; }
    .letter-product-price-block {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem 1rem;
        padding-top: 0.5rem;
        border-top: 1px solid var(--border-color);
    }
    .letter-product-total { font-size: 1.125rem; font-weight: 700; color: var(--primary-color); }
    .letter-product-breakdown { font-size: 0.8125rem; color: #64748b; }
    .letter-product-faq { margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color); }
    .letter-product-faq-title { font-size: 0.8125rem; font-weight: 700; color: var(--dark-text); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.375rem; }
    .letter-product-faq-title i { color: var(--primary-color); }
    .letter-product-faq-item { padding: 0.5rem 0; border-bottom: 1px solid var(--light-bg); }
    .letter-product-faq-item:last-child { border-bottom: none; }
    .letter-product-faq-q { font-size: 0.875rem; font-weight: 600; color: var(--dark-text); margin-bottom: 0.25rem; }
    .letter-product-faq-a { font-size: 0.8125rem; color: #64748b; line-height: 1.45; margin: 0; }
    @media (max-width: 576px) {
        .letter-product-card { flex-direction: column; }
        .letter-product-image { width: 100%; height: 180px; }
    }
</style>
@endpush

@section('content')
<div class="template-details-header">
    <div class="container">
        <a href="{{ route('design.templates.explore') }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Templates
        </a>
        <div class="template-header-inner">
            <div class="template-header-content">
                <h1 class="template-header-title">{{ $template->name }}</h1>
                <div class="template-header-meta">
                    @if($template->page_count)
                        <span><i class="fas fa-file-alt"></i>{{ $template->page_count }} {{ $template->page_count > 1 ? 'Pages' : 'Page' }}</span>
                    @endif
                    <span><i class="fas fa-shopping-cart"></i>{{ $template->orders_count ?? 0 }} {{ ($template->orders_count ?? 0) === 1 ? 'Use' : 'Uses' }}</span>
                    @if($template->is_public && $template->licence)
                        <span><i class="fas fa-certificate"></i>{{ ucfirst($template->licence) }} License</span>
                    @endif
                    @if($template->variables && count($template->variables) > 0)
                        <span><i class="fas fa-code"></i>{{ count($template->variables) }} Variables</span>
                    @endif
                </div>
            </div>
            <div class="template-header-badges" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                @if($template->category && $template->category !== 'general')
                    <span class="template-header-category">{{ ucfirst($template->category) }}</span>
                @endif
                @if($template->is_public)
                    <span class="template-header-price-tag {{ $template->price == 0 ? 'free' : '' }}">
                        @if($template->price > 0)
                            {{ format_price($template->price) }}
                        @else
                            Free
                        @endif
                    </span>
                @endif
                <div class="template-header-actions">
                    <button type="button" class="template-header-action-btn" onclick="shareTemplate()" title="Share">
                        <i class="fas fa-share-alt"></i>
                        <span>Share</span>
                    </button>
                    <button type="button" class="template-header-action-btn" id="favoriteBtn" onclick="toggleTemplateFavorite()" title="Add to favorites">
                        <i class="far fa-heart"></i>
                        <span>Favorite</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            @if(isset($assignedProducts) && $assignedProducts->count() > 0)
            <ul class="nav template-detail-tabs mb-3" id="templateDetailTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-template" data-bs-toggle="tab" data-bs-target="#panel-template" type="button" role="tab" aria-controls="panel-template" aria-selected="true"><i class="fas fa-layer-group me-1"></i>Template</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-letter" data-bs-toggle="tab" data-bs-target="#panel-letter" type="button" role="tab" aria-controls="panel-letter" aria-selected="false"><i class="fas fa-box me-1"></i>Attached Product</button>
                </li>
            </ul>
            <div class="tab-content template-detail-tab-content" id="templateDetailTabContent">
                <div class="tab-pane fade show active" id="panel-template" role="tabpanel" aria-labelledby="tab-template">
            @endif
            <!-- Product View - Image Section -->
            @php
                $templateImages = $template->images_urls ?? [];
                $mainImageUrl = $template->thumbnail_url;
                $allImages = [];
                if ($mainImageUrl) {
                    $allImages[] = $mainImageUrl;
                    foreach ($templateImages as $img) {
                        if ($img !== $mainImageUrl) $allImages[] = $img;
                    }
                } else {
                    $allImages = $templateImages;
                }
                $displayImage = $mainImageUrl ?? ($templateImages[0] ?? null);
                $hasGallery = count($allImages) > 1;
            @endphp
            <div class="product-image-section">
                <div class="product-image-main">
                    @if($displayImage)
                        <img src="{{ $displayImage }}" alt="{{ $template->name }}" id="mainTemplateImage">
                    @else
                        <div class="placeholder-bg">
                            <div class="no-thumbnail">
                                <i class="fas fa-layer-group"></i>
                            </div>
                        </div>
                    @endif
                </div>
                @if($hasGallery)
                    <div class="product-thumbnails">
                        @foreach($allImages as $index => $imageUrl)
                            <div class="product-thumb-item {{ $index === 0 ? 'active' : '' }}" onclick="changeMainImage('{{ $imageUrl }}', this)">
                                <img src="{{ $imageUrl }}" alt="Template view {{ $index + 1 }}">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Template Description -->
            <div class="template-info-card">
                @if($template->category && $template->category !== 'general')
                    <span class="template-category">
                        {{ ucfirst($template->category) }}
                    </span>
                @endif

                @if($template->short_description)
                    <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--dark-text); margin-bottom: 0.75rem;">Overview</h3>
                    <div class="template-description" style="margin-bottom: 1rem;">
                        {!! nl2br(e($template->short_description)) !!}
                    </div>
                @endif

                @if($template->description)
                    <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--dark-text); margin-bottom: 0.75rem;">Description</h3>
                    <div class="template-description">
                        {!! nl2br(e($template->description)) !!}
                    </div>
                @endif
            </div>

            <!-- Template Features/Specifications -->
            @if($template->variables && count($template->variables) > 0)
                <div class="template-info-card">
                    <h4 style="font-size: 1rem; font-weight: 700; color: var(--dark-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-code me-2" style="color: var(--primary-color);"></i>Template Variables
                    </h4>
                    <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.75rem;">This template includes {{ count($template->variables) }} customizable variable(s):</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        @foreach($template->variables as $variable)
                            <span style="padding: 0.375rem 0.75rem; background: var(--light-bg); border-radius: 6px; font-size: 0.8125rem; color: var(--dark-text); font-weight: 500; font-family: monospace;">
                                {&#123;{ {{ $variable['name'] ?? 'variable' }} }&#125;}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if(isset($assignedProducts) && $assignedProducts->count() > 0)
                </div>
                <div class="tab-pane fade" id="panel-letter" role="tabpanel" aria-labelledby="tab-letter">
                    <div class="template-info-card">
                        <h4 style="font-size: 1rem; font-weight: 700; color: var(--dark-text); margin-bottom: 0.75rem;">
                            <i class="fas fa-box me-2" style="color: var(--primary-color);"></i>Attached Product
                        </h4>
                        <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 1rem;">Products you can use with this template. Total = Template price + Product price.</p>
                        @php $templatePriceNum = (float) ($template->price ?? 0); @endphp
                        @foreach($assignedProducts as $product)
                            @php
                                $productPrice = (float) ($product->price ?? 0);
                                $total = $templatePriceNum + $productPrice;
                                $productImageUrl = $product->image_url ?? (($product->image && \Storage::disk('public')->exists($product->image)) ? \Storage::disk('public')->url($product->image) : null);
                            @endphp
                            <div class="letter-product-card">
                                <div class="letter-product-image">
                                    @if($productImageUrl)
                                        <img src="{{ $productImageUrl }}" alt="{{ $product->name }}">
                                    @else
                                        <span class="no-image"><i class="fas fa-box-open"></i></span>
                                    @endif
                                </div>
                                <div class="letter-product-details">
                                    <h5 class="letter-product-name">{{ $product->name }}</h5>
                                    @if($product->sku)
                                        <div class="letter-product-sku"><code>{{ $product->sku }}</code></div>
                                    @endif
                                    @if($product->description)
                                        <div class="letter-product-desc">{{ $product->description }}</div>
                                    @endif
                                    <div class="letter-product-price-block">
                                        <span class="letter-product-total">{{ format_price($total) }}</span>
                                        <span class="letter-product-breakdown">Template {{ format_price($templatePriceNum) }} + Product {{ format_price($productPrice) }}</span>
                                    </div>
                                    @if($product->faqs && is_array($product->faqs) && count($product->faqs) > 0)
                                        <div class="letter-product-faq">
                                            <div class="letter-product-faq-title"><i class="fas fa-question-circle"></i>FAQ</div>
                                            @foreach($product->faqs as $faq)
                                                @if(!empty($faq['question']) || !empty($faq['answer']))
                                                    <div class="letter-product-faq-item">
                                                        @if(!empty($faq['question']))
                                                            <div class="letter-product-faq-q">{{ $faq['question'] }}</div>
                                                        @endif
                                                        @if(!empty($faq['answer']))
                                                            <p class="letter-product-faq-a">{{ $faq['answer'] }}</p>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Template Info Sidebar - Professional Panel -->
            <div class="template-sidebar-panel">
                <!-- Name & Badge -->
                <div class="sidebar-section">
                    <div class="sidebar-name-row">
                        <h2 class="sidebar-template-name">{{ $template->name }}</h2>
                        @if($template->is_public)
                            <span class="public-badge" style="flex-shrink: 0;">
                                <i class="fas fa-globe"></i>Public
                            </span>
                        @endif
                    </div>

                    <!-- Price Block: template price, product price(s), total (when assigned products) -->
                    @if($template->is_public)
                        <div class="sidebar-section-title">Price</div>
                        @if(isset($assignedProducts) && $assignedProducts->count() > 0)
                            @php
                                $sidebarTemplatePrice = (float) ($template->price ?? 0);
                                $sidebarProductPrices = $assignedProducts->map(fn($p) => (float)($p->price ?? 0));
                                $sidebarMinProduct = $sidebarProductPrices->min();
                                $sidebarMaxProduct = $sidebarProductPrices->max();
                                $sidebarTotalMin = $sidebarTemplatePrice + $sidebarMinProduct;
                                $sidebarTotalMax = $sidebarTemplatePrice + $sidebarMaxProduct;
                            @endphp
                            <div class="sidebar-price-row">
                                <span class="label">Template price</span>
                                <span class="value">{{ $sidebarTemplatePrice == 0 ? 'Free' : format_price($sidebarTemplatePrice) }}</span>
                            </div>
                            <div class="sidebar-price-row">
                                <span class="label">Product price</span>
                                <span class="value">
                                    @if($sidebarMinProduct == $sidebarMaxProduct)
                                        {{ format_price($sidebarMinProduct) }}
                                    @else
                                        {{ format_price($sidebarMinProduct) }} – {{ format_price($sidebarMaxProduct) }}
                                    @endif
                                </span>
                            </div>
                            <div class="sidebar-price-row total">
                                <span class="label">Total (Template + Product)</span>
                                <span class="value">
                                    @if($sidebarTotalMin == $sidebarTotalMax)
                                        {{ format_price($sidebarTotalMin) }}
                                    @else
                                        {{ format_price($sidebarTotalMin) }} – {{ format_price($sidebarTotalMax) }}
                                    @endif
                                </span>
                            </div>
                        @else
                            <div class="sidebar-price-block {{ $template->price == 0 ? 'free' : '' }}">
                                <div class="sidebar-price-icon">
                                    @if($template->price > 0)
                                        <i class="fas fa-dollar-sign"></i>
                                    @else
                                        <i class="fas fa-gift"></i>
                                    @endif
                                </div>
                                <div>
                                    <span class="sidebar-price-text">
                                        @if($template->price > 0)
                                            {{ format_price($template->price) }}
                                        @else
                                            Free
                                        @endif
                                    </span>
                                    <span class="sidebar-price-label">Template price</span>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Meta Stats: Pages, License, Variables -->
                @if($template->page_count || ($template->is_public && $template->licence) || ($template->variables && count($template->variables) > 0))
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Details</div>
                    <div class="sidebar-meta-grid">
                        @if($template->page_count)
                            <div class="sidebar-meta-item">
                                <span class="sidebar-meta-value">{{ $template->page_count }}</span>
                                <span class="sidebar-meta-label">{{ $template->page_count > 1 ? 'Pages' : 'Page' }}</span>
                            </div>
                        @endif
                        @if($template->is_public && $template->licence)
                            <div class="sidebar-meta-item">
                                <span class="sidebar-meta-value" style="font-size: 0.8125rem;">{{ ucfirst($template->licence) }}</span>
                                <span class="sidebar-meta-label">License</span>
                            </div>
                        @endif
                        @if($template->variables && count($template->variables) > 0)
                            <div class="sidebar-meta-item">
                                <span class="sidebar-meta-value">{{ count($template->variables) }}</span>
                                <span class="sidebar-meta-label">Variables</span>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Stats: Rating, Reviews, Comments -->
                @if($template->is_public)
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Stats</div>
                    <div class="template-stats">
                        <div class="stat-item">
                            <span class="stat-value">{{ $averageRating > 0 ? number_format($averageRating, 1) : '0.0' }}</span>
                            <span class="stat-label">Rating</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">{{ $totalReviews }}</span>
                            <span class="stat-label">Reviews</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">{{ $comments->count() }}</span>
                            <span class="stat-label">Comments</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Author Info -->
                @if($template->creator)
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Creator</div>
                    <div class="author-info" style="margin-bottom: 0;">
                        <div class="author-avatar">
                            {{ strtoupper(substr($template->creator->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="author-details">
                            <h5>{{ $template->creator->name ?? 'Unknown Author' }}</h5>
                            <p>Template Creator</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="sidebar-section">
                    <div class="action-buttons" style="margin-top: 0;">
                        <button class="action-btn action-btn-primary" onclick="useTemplate()">
                            <i class="fas fa-download"></i>
                            Use Template
                        </button>
                        <button class="action-btn action-btn-secondary" onclick="quickUseTemplate()">
                            <i class="fas fa-bolt"></i>
                            Quick Use
                        </button>
                    </div>
                </div>

                <!-- Available Sheet Types -->
                @if($sheetTypes && $sheetTypes->count() > 0)
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Sheet Types</div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        @foreach($sheetTypes as $sheetType)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.625rem 0.75rem; background: var(--light-bg); border-radius: 8px; border: 1px solid transparent;">
                                <span style="font-size: 0.8125rem; color: var(--dark-text); font-weight: 500;">{{ $sheetType->name }}</span>
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">{{ format_price($sheetType->price_per_sheet) }}/sheet</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Available Sheet Sizes -->
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Sheet Sizes</div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.625rem 0.75rem; background: var(--light-bg); border-radius: 8px;">
                            <span style="font-size: 0.8125rem; color: var(--dark-text); font-weight: 500;">A4 (8.27" × 11.69")</span>
                            <span style="font-size: 0.6875rem; color: #64748b; text-transform: uppercase;">Standard</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.625rem 0.75rem; background: var(--light-bg); border-radius: 8px;">
                            <span style="font-size: 0.8125rem; color: var(--dark-text); font-weight: 500;">Letter (8.5" × 11")</span>
                            <span style="font-size: 0.6875rem; color: #64748b; text-transform: uppercase;">US Standard</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.625rem 0.75rem; background: var(--light-bg); border-radius: 8px;">
                            <span style="font-size: 0.8125rem; color: var(--dark-text); font-weight: 500;">Legal (8.5" × 14")</span>
                            <span style="font-size: 0.6875rem; color: #64748b; text-transform: uppercase;">Extended</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="row mt-2">
        <div class="col-12">
            <div class="template-info-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <h4 style="font-size: 1.125rem; font-weight: 700; color: var(--dark-text); margin: 0;">
                        <i class="fas fa-star me-2" style="color: #fbbf24;"></i>Reviews
                    </h4>
                    @if($totalReviews > 0)
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 0.9375rem; font-weight: 600; color: var(--dark-text);">{{ number_format($averageRating, 1) }}</span>
                            <div style="color: #fbbf24;">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star{{ $i <= round($averageRating) ? '' : '-o' }}" style="font-size: 0.875rem;"></i>
                                @endfor
                            </div>
                            <span style="font-size: 0.875rem; color: #64748b;">({{ $totalReviews }} {{ $totalReviews > 1 ? 'reviews' : 'review' }})</span>
                        </div>
                    @endif
                </div>

                @if($reviews->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($reviews as $review)
                            <div style="padding: 0.75rem; background: var(--light-bg); border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 0.75rem; font-weight: 600;">
                                            {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-size: 0.875rem; font-weight: 600; color: var(--dark-text);">{{ $review->user->name ?? 'Anonymous' }}</div>
                                            <div style="font-size: 0.75rem; color: #64748b;">{{ $review->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                    <div style="color: #fbbf24;">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}" style="font-size: 0.75rem;"></i>
                                        @endfor
                                    </div>
                                </div>
                                @if($review->review)
                                    <p style="font-size: 0.875rem; color: #475569; margin: 0; line-height: 1.5;">{{ $review->review }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="font-size: 0.875rem; color: #64748b; text-align: center; padding: 1rem; margin: 0;">No reviews yet. Be the first to review this template!</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="row mt-1">
        <div class="col-12">
            <div class="template-info-card">
                <h4 style="font-size: 1rem; font-weight: 700; color: var(--dark-text); margin-bottom: 0.75rem;">
                    <i class="fas fa-comments me-2" style="color: var(--primary-color);"></i>Comments ({{ $comments->count() }})
                </h4>

                @if($comments->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($comments as $comment)
                            <div style="padding: 0.75rem; background: var(--light-bg); border-radius: 8px;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 0.75rem; font-weight: 600;">
                                        {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-size: 0.875rem; font-weight: 600; color: var(--dark-text);">{{ $comment->user->name ?? 'Anonymous' }}</div>
                                        <div style="font-size: 0.75rem; color: #64748b;">{{ $comment->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                <p style="font-size: 0.875rem; color: #475569; margin: 0 0 0.5rem 0; line-height: 1.5;">{{ $comment->comment }}</p>

                                <!-- Replies -->
                                @if($comment->replies && $comment->replies->count() > 0)
                                    <div style="margin-left: 2rem; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color);">
                                        @foreach($comment->replies as $reply)
                                            <div style="padding: 0.5rem; margin-bottom: 0.5rem; background: white; border-radius: 6px;">
                                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 0.625rem; font-weight: 600;">
                                                        {{ strtoupper(substr($reply->user->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div style="font-size: 0.8125rem; font-weight: 600; color: var(--dark-text);">{{ $reply->user->name ?? 'Anonymous' }}</div>
                                                        <div style="font-size: 0.6875rem; color: #64748b;">{{ $reply->created_at->diffForHumans() }}</div>
                                                    </div>
                                                </div>
                                                <p style="font-size: 0.8125rem; color: #475569; margin: 0; line-height: 1.4;">{{ $reply->comment }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="font-size: 0.875rem; color: #64748b; text-align: center; padding: 1rem; margin: 0;">No comments yet. Be the first to comment!</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Use Template Modal -->
<div id="useTemplateModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);" onclick="if(event.target === this) closeUseTemplateModal();">
    <div class="modal-content" style="background-color: white; margin: 10% auto; padding: 0; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden;" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 1.5rem; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Use Template</h3>
                <button onclick="closeUseTemplateModal()" style="background: rgba(255,255,255,0.2); border: none; font-size: 1.5rem; cursor: pointer; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">&times;</button>
            </div>
        </div>

        <!-- Modal Body -->
        <div style="padding: 2rem;">
            <p style="color: #64748b; margin-bottom: 1.5rem; font-size: 0.9375rem;">How would you like to use this template?</p>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <!-- Option 1: Use for Design Page -->
                <button onclick="useTemplateForDesign()" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: white; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: all 0.3s; text-align: left;" onmouseover="this.style.borderColor='#6366f1'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.15)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-palette" style="color: white; font-size: 1.25rem;"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #1e293b; font-size: 1rem; margin-bottom: 0.25rem;">Use Template for Design Page</div>
                        <div style="font-size: 0.8125rem; color: #64748b;">Open template in multi-page design tool for editing</div>
                    </div>
                    <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                </button>

                <!-- Option 2: Quick Use -->
                <button onclick="quickUseTemplateFromModal()" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: white; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: all 0.3s; text-align: left;" onmouseover="this.style.borderColor='#6366f1'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.15)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-bolt" style="color: white; font-size: 1.25rem;"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #1e293b; font-size: 1rem; margin-bottom: 0.25rem;">Quick Use</div>
                        <div style="font-size: 0.8125rem; color: #64748b;">Quick use template with variable forms</div>
                    </div>
                    <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@include('design.partials.share-template-modal')
@endsection

@push('scripts')
<script>
    function useTemplate() {
        document.getElementById('useTemplateModal').style.display = 'block';
    }

    function closeUseTemplateModal() {
        document.getElementById('useTemplateModal').style.display = 'none';
    }

    function useTemplateForDesign() {
        const templateId = {{ $template->id }};
        const templateType = @json($template->type ?? '');
        const baseUrl = '{{ route("design.create") }}';
        let url = baseUrl + '?multi=true&template=' + encodeURIComponent(templateId);
        if (templateType) url += '&type=' + encodeURIComponent(templateType);
        window.location.href = url;
    }

    function quickUseTemplate() {
        const templateId = {{ $template->id }};
        const templateType = @json($template->type ?? '');
        if (templateType === 'letter') {
            window.location.href = '{{ route("design.templates.sendLetter", ":id") }}'.replace(':id', templateId);
        } else {
            window.location.href = '{{ route("design.templates.quickUse", ":id") }}'.replace(':id', templateId);
        }
    }

    function quickUseTemplateFromModal() {
        closeUseTemplateModal();
        quickUseTemplate();
    }

    // Change main image when clicking on gallery thumbnails
    function changeMainImage(imageUrl, clickedElement) {
        const mainImage = document.getElementById('mainTemplateImage');
        if (mainImage) {
            mainImage.src = imageUrl;
        }

        // Update active state
        document.querySelectorAll('.product-thumb-item').forEach(item => {
            item.classList.remove('active');
        });
        if (clickedElement) clickedElement.classList.add('active');
    }

    function shareTemplate() {
        if (typeof openShareTemplateModal === 'function') {
            openShareTemplateModal(
                {{ $template->id }},
                @json($template->name),
                @json((bool) $template->is_public)
            );
            return;
        }
        const url = window.location.href;
        const title = @json($template->name);
        const text = 'Check out this template: ' + title;
        if (navigator.share) {
            navigator.share({ title: title, text: text, url: url }).then(() => {}).catch(() => fallbackCopyLink(url));
        } else {
            fallbackCopyLink(url);
        }
    }

    function fallbackCopyLink(url) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copied to clipboard!');
            }).catch(() => promptCopy(url));
        } else {
            promptCopy(url);
        }
    }

    function promptCopy(url) {
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        alert('Link copied to clipboard!');
    }

    function toggleTemplateFavorite() {
        const btn = document.getElementById('favoriteBtn');
        const icon = btn.querySelector('i');
        const span = btn.querySelector('span');
        const isFavorited = btn.classList.contains('favorited');
        const templateId = {{ $template->id }};
        const storageKey = 'template_favorites';

        if (isFavorited) {
            btn.classList.remove('favorited');
            icon.classList.remove('fas');
            icon.classList.add('far');
            span.textContent = 'Favorite';
            try {
                const favs = JSON.parse(localStorage.getItem(storageKey) || '[]');
                localStorage.setItem(storageKey, JSON.stringify(favs.filter(id => id !== templateId)));
            } catch (e) {}
        } else {
            btn.classList.add('favorited');
            icon.classList.remove('far');
            icon.classList.add('fas');
            span.textContent = 'Favorited';
            try {
                const favs = JSON.parse(localStorage.getItem(storageKey) || '[]');
                if (!favs.includes(templateId)) favs.push(templateId);
                localStorage.setItem(storageKey, JSON.stringify(favs));
            } catch (e) {}
        }
    }

    // Restore favorite state from localStorage on page load
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const favs = JSON.parse(localStorage.getItem('template_favorites') || '[]');
            if (favs.includes({{ $template->id }})) {
                const btn = document.getElementById('favoriteBtn');
                if (btn) {
                    btn.classList.add('favorited');
                    const icon = btn.querySelector('i');
                    const span = btn.querySelector('span');
                    if (icon) { icon.classList.remove('far'); icon.classList.add('fas'); }
                    if (span) span.textContent = 'Favorited';
                }
            }
        } catch (e) {}
    });
</script>
@endpush

