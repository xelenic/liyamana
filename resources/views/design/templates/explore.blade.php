@extends('layouts.app')

@section('title', $explorePageTitle ?? 'Explore Templates')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
    :root {
        --primary-color: #6366f1;
        --secondary-color: #8b5cf6;
        --light-bg: #f8fafc;
        --dark-text: #1e293b;
        --border-color: #e2e8f0;
    }

    /* Template Card - Product-style */
    .template-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
        cursor: pointer;
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--border-color);
        background: white;
        position: relative;
    }

    .template-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        border-color: rgba(99, 102, 241, 0.3);
    }

    .template-card:hover .template-thumbnail img {
        transform: scale(1.02);
    }

    /* Thumbnail */
    .template-thumbnail {
        aspect-ratio: 4 / 3;
        min-height: 160px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .template-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
        padding: 6px;
    }

    .template-thumbnail .no-thumbnail {
        color: #94a3b8;
        font-size: 2.5rem;
        opacity: 0.6;
    }

    .template-thumbnail-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        z-index: 2;
    }
    .template-thumbnail-badge.template-price-badge {
        left: auto;
        right: 8px;
    }
    .template-thumbnail-badge .price-tag {
        padding: 0.3rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-block;
    }
    .template-thumbnail-badge .price-tag.free {
        background: #10b981;
        color: white;
    }
    .template-thumbnail-badge .price-tag.paid {
        background: rgba(0,0,0,0.75);
        color: white;
    }

    .template-info {
        padding: 0.75rem 0.875rem 0.875rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        position: relative;
        min-height: 0;
    }

    .template-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.375rem;
        margin-bottom: 0.25rem;
        min-width: 0;
    }

    .template-card-header .header-left {
        flex: 1;
        min-width: 0;
    }

    .template-favorite-btn {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 8px;
        cursor: pointer;
        padding: 0.35rem;
        color: #94a3b8;
        font-size: 0.875rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        flex-shrink: 0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .template-favorite-btn:hover {
        color: #ef4444;
        transform: scale(1.08);
        background: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
    }

    .template-favorite-btn.favorited {
        color: #ef4444;
    }

    .template-review-score {
        display: flex;
        align-items: center;
        gap: 0.2rem;
        font-size: 0.6875rem;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .template-review-score .stars {
        color: #f59e0b;
        font-size: 0.6875rem;
    }

    .template-card-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 0.5rem;
        flex-shrink: 0;
    }
    .template-card-actions .btn-group-right {
        display: flex;
        gap: 0.35rem;
    }
    .template-card-actions .btn-icon {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        border: 1px solid var(--border-color);
        color: #64748b;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .template-card-actions .btn-icon:hover {
        background: #e2e8f0;
        color: var(--primary-color);
        border-color: rgba(99,102,241,0.3);
    }

    .template-title {
        font-size: 0.9375rem;
        font-weight: 600;
        margin-bottom: 0;
        color: var(--dark-text);
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        letter-spacing: -0.01em;
    }

    .template-description {
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 0.375rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.35;
    }

    .template-meta {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.375rem;
        font-size: 0.6875rem;
        color: #64748b;
        flex-wrap: wrap;
        align-items: center;
    }

    .template-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .template-actions {
        display: none;
    }

    .template-card-actions .btn,
    .template-card-actions .btn-primary {
        font-size: 0.8125rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s;
        white-space: nowrap;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
        color: white !important;
        border: none !important;
        box-shadow: 0 2px 6px rgba(99, 102, 241, 0.35);
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        cursor: pointer;
    }

    .template-card-actions .btn:hover,
    .template-card-actions .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        color: white !important;
    }

    .template-category {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.08) 100%);
        color: var(--primary-color);
        border-radius: 6px;
        font-size: 0.6875rem;
        font-weight: 600;
        margin-bottom: 0.375rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .template-price-display {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    .template-price-display .price-value {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--primary-color);
    }
    .template-price-display .price-value.free {
        color: #10b981;
        font-weight: 700;
    }

    /* Grid - tight product layout */
    #templatesContainer .col-12 { margin-bottom: 0.5rem; }
    @media (min-width: 576px) { #templatesContainer .col-sm-6 { margin-bottom: 0.75rem; } }
    @media (min-width: 768px) { #templatesContainer .col-md-4 { margin-bottom: 0.75rem; } }

    /* Latest Added Templates - smaller card items */
    #templatesContainer .template-thumbnail {
        min-height: 120px;
        aspect-ratio: 4 / 3;
    }
    #templatesContainer .template-thumbnail img {
        padding: 4px;
    }
    #templatesContainer .template-info {
        padding: 0.5rem 0.6rem 0.6rem;
    }
    #templatesContainer .template-title {
        font-size: 0.8125rem;
        -webkit-line-clamp: 2;
    }
    #templatesContainer .template-description {
        font-size: 0.6875rem;
        -webkit-line-clamp: 1;
        margin-bottom: 0.25rem;
    }
    #templatesContainer .template-meta {
        font-size: 0.625rem;
        margin-bottom: 0.25rem;
    }
    #templatesContainer .template-review-score,
    #templatesContainer .template-review-score .stars {
        font-size: 0.625rem;
    }
    #templatesContainer .template-card-actions .btn,
    #templatesContainer .template-card-actions .btn-primary {
        font-size: 0.7rem;
        padding: 0.35rem 0.6rem;
    }
    #templatesContainer .template-card-actions .btn-icon {
        width: 28px;
        height: 28px;
        font-size: 0.75rem;
    }
    #templatesContainer .template-favorite-btn {
        padding: 0.25rem;
        font-size: 0.75rem;
    }
    #templatesContainer .template-thumbnail-badge .price-tag {
        font-size: 0.65rem;
        padding: 0.2rem 0.35rem;
    }

    /* Template card hover tooltip */
    #templateCardTooltip {
        position: fixed;
        z-index: 1100;
        max-width: 280px;
        padding: 0.65rem 0.75rem;
        background: #1e293b;
        color: #f1f5f9;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        font-size: 0.8125rem;
        line-height: 1.4;
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.15s ease, visibility 0.15s ease;
    }
    #templateCardTooltip.visible {
        opacity: 1;
        visibility: visible;
    }
    #templateCardTooltip .template-tooltip-title {
        font-weight: 700;
        font-size: 0.875rem;
        margin-bottom: 0.35rem;
        color: white;
    }
    #templateCardTooltip .template-tooltip-desc {
        color: #cbd5e1;
        margin-bottom: 0.5rem;
    }
    #templateCardTooltip .template-tooltip-meta {
        color: #94a3b8;
        font-size: 0.75rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 0.75rem;
    }

    /* Filter - compact */
    .explore-filter-wrapper {
        background: white;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        margin-bottom: 0.75rem;
    }

    .explore-search-bar {
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0 0.875rem;
        transition: all 0.2s ease;
        flex: 1;
        min-width: 180px;
        max-width: 360px;
        height: 38px;
    }

    .explore-search-bar:hover {
        background: white;
        border-color: #c7d2fe;
        box-shadow: 0 1px 4px rgba(99, 102, 241, 0.08);
    }

    .explore-search-bar:focus-within {
        background: white;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    .explore-search-bar .search-icon {
        color: #94a3b8;
        font-size: 0.875rem;
        margin-right: 0.5rem;
        transition: color 0.2s;
    }

    .explore-search-bar:focus-within .search-icon {
        color: var(--primary-color);
    }

    .explore-search-bar .search-input {
        flex: 1;
        border: none;
        padding: 0;
        font-size: 0.8125rem;
        color: var(--dark-text);
        background: transparent;
        min-width: 0;
    }

    .explore-search-bar .search-input::placeholder {
        color: #94a3b8;
    }

    .explore-search-bar .search-clear-btn {
        padding: 0.25rem;
        margin-left: 0.2rem;
        background: rgba(100, 116, 139, 0.1);
        border: none;
        border-radius: 4px;
        color: #64748b;
        cursor: pointer;
        font-size: 0.6875rem;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .explore-search-bar.has-value .search-clear-btn {
        display: flex;
    }

    .explore-search-bar .search-clear-btn:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .filter-section-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .search-results-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.625rem;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        color: var(--primary-color);
        border-radius: 16px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .search-results-badge strong {
        margin-right: 0.2rem;
    }

    .search-results-section {
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .search-results-heading {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--dark-text);
        margin: 0 0 0.25rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .search-results-heading i {
        color: var(--primary-color);
    }
    .search-results-sub {
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
    }

    .category-carousel-wrapper {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        position: relative;
    }

    .category-carousel-arrow {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border: 1px solid var(--border-color);
        background: white;
        border-radius: 6px;
        color: #64748b;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6875rem;
        transition: all 0.2s;
    }

    .category-carousel-arrow:hover:not(:disabled) {
        border-color: var(--primary-color);
        color: var(--primary-color);
        background: rgba(99, 102, 241, 0.06);
    }

    .category-carousel-arrow:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .category-carousel-track {
        flex: 1;
        overflow: hidden;
        min-width: 0;
    }

    .category-filter {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.375rem;
        overflow-x: auto;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding: 0.2rem 0;
    }

    .category-filter::-webkit-scrollbar {
        display: none;
    }

    .category-section-label {
        font-size: 0.625rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.375rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .category-section-label i {
        color: var(--primary-color);
        font-size: 0.6875rem;
    }

    .category-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.35rem 0.65rem;
        border: 1px solid var(--border-color);
        background: white;
        color: #475569;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .category-btn i {
        font-size: 0.75rem;
        opacity: 0.7;
    }

    .category-btn:hover {
        border-color: #a5b4fc;
        color: var(--primary-color);
        background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(99, 102, 241, 0.1);
    }

    .category-btn.active {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
    }

    .category-btn.active i {
        opacity: 1;
    }

    @media (max-width: 768px) {
        .filter-section-header {
            flex-direction: column;
            align-items: stretch;
        }
        .explore-search-bar {
            max-width: none;
        }
    }

    .explore-pagination-wrap {
        margin-top: 0.5rem;
        margin-bottom: 1.5rem;
        padding: 1rem 0.5rem 0;
        border-top: 1px solid #e2e8f0;
    }
    .explore-pagination-wrap .pagination .page-link {
        border-radius: 8px;
        margin: 0 2px;
        min-width: 2.25rem;
        text-align: center;
        color: #475569;
        border-color: #e2e8f0;
    }
    .explore-pagination-wrap .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-color: transparent;
        color: #fff;
    }
    .explore-pagination-wrap .pagination .page-item:not(.disabled):not(.active) .page-link:hover {
        background: #f8fafc;
        border-color: #c7d2fe;
        color: var(--primary-color);
    }

    .explore-layout {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
    }

    .explore-main {
        flex: 1;
        min-width: 0;
        position: relative;
    }

    /* Full-area preloader while JSON + thumbnails load */
    #explorePreloaderOverlay {
        position: absolute;
        inset: 0;
        z-index: 120;
        min-height: 55vh;
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 1rem;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.97) 0%, rgba(241, 245, 249, 0.98) 100%);
        backdrop-filter: blur(6px);
        border-radius: 0 0 12px 12px;
        pointer-events: all;
    }
    #explorePreloaderOverlay.is-visible {
        display: flex;
    }
    .explore-preloader-spinner {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: 3px solid #e2e8f0;
        border-top-color: var(--primary-color);
        animation: explore-preloader-spin 0.75s linear infinite;
    }
    @keyframes explore-preloader-spin {
        to { transform: rotate(360deg); }
    }
    .explore-preloader-text {
        margin: 0;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #64748b;
    }

    .explore-sidebar {
        width: 260px;
        flex-shrink: 0;
        position: sticky;
        top: 1.5rem;
    }

    .filter-sidebar-panel {
        background: white;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        overflow: hidden;
    }

    .filter-sidebar-section {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .filter-sidebar-section:last-child {
        border-bottom: none;
    }

    .filter-sidebar-title {
        font-size: 0.6875rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-sidebar-title i {
        color: var(--primary-color);
        font-size: 0.75rem;
    }

    .filter-sidebar-search {
        display: flex;
        align-items: center;
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0 0.75rem;
        height: 40px;
    }

    .filter-sidebar-search:focus-within {
        border-color: var(--primary-color);
        background: white;
    }

    .filter-sidebar-search i {
        color: #94a3b8;
        font-size: 0.875rem;
        margin-right: 0.5rem;
    }

    .filter-sidebar-search input {
        flex: 1;
        border: none;
        background: transparent;
        font-size: 0.875rem;
        color: var(--dark-text);
    }

    .filter-sidebar-search input:focus {
        outline: none;
    }

    .filter-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0;
        cursor: pointer;
        font-size: 0.875rem;
        color: #475569;
        transition: color 0.2s;
    }

    .filter-option:hover {
        color: var(--primary-color);
    }

    .filter-option input[type="radio"],
    .filter-option input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--primary-color);
        cursor: pointer;
    }

    .filter-option.selected {
        color: var(--primary-color);
        font-weight: 600;
    }

    .filter-select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.875rem;
        color: var(--dark-text);
        background: white;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .filter-price-range {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .filter-price-range input {
        flex: 1;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.875rem;
    }

    .filter-reset-btn {
        width: 100%;
        padding: 0.5rem 1rem;
        margin-top: 0.5rem;
        border: 1px solid var(--border-color);
        background: white;
        border-radius: 8px;
        font-size: 0.8125rem;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-reset-btn:hover {
        background: #f8fafc;
        color: var(--primary-color);
    }

    .mobile-filters-btn {
        display: none;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border: 1px solid var(--border-color);
        background: white;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        margin-bottom: 1rem;
    }

    .mobile-filters-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .filter-sidebar-mobile-header {
        display: none !important;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        align-items: center;
        justify-content: space-between;
        background: white;
    }

    @media (max-width: 991px) {
        .explore-sidebar {
            display: none;
            position: fixed;
            right: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            max-width: 90%;
            z-index: 1060;
            background: white;
            box-shadow: -4px 0 20px rgba(0,0,0,0.15);
            overflow-y: auto;
        }
        .explore-sidebar.mobile-open {
            display: flex !important;
            flex-direction: column;
        }
        .explore-sidebar.mobile-open .filter-sidebar-mobile-header {
            display: flex !important;
        }
        .mobile-filters-btn {
            display: inline-flex;
        }
    }

    @media (min-width: 992px) {
        .mobile-filters-btn {
            display: none !important;
        }
    }

    /* Sticky Search Bar - Shows when main filter scrolls out of view */
    .sticky-search-bar {
        position: fixed;
        top: 60px;
        left: 250px;
        right: 0;
        z-index: 999;
        background: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        padding: 0.5rem 1rem;
        display: none;
        align-items: center;
        gap: 1rem;
        transform: translateZ(0);
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
    }

    .sticky-search-bar.visible {
        display: flex;
    }

    .sticky-search-bar .sticky-search-input {
        flex: 1;
        min-width: 120px;
        max-width: 280px;
        padding: 0.4rem 0.75rem 0.4rem 2rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.8125rem;
        background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 0.65rem center;
    }

    .sticky-search-bar .sticky-search-input:focus {
        outline: none;
        border-color: var(--primary-color);
        background-color: white;
    }

    .sticky-search-bar .sticky-categories {
        display: flex;
        gap: 0.375rem;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        flex: 1;
        min-width: 0;
    }

    .sticky-search-bar .sticky-categories::-webkit-scrollbar {
        display: none;
    }

    .sticky-search-bar .sticky-category-btn {
        flex-shrink: 0;
        padding: 0.35rem 0.65rem;
        border: 1px solid var(--border-color);
        background: white;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .sticky-search-bar .sticky-category-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .sticky-search-bar .sticky-category-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .sticky-search-bar {
            left: 0;
            top: 56px;
            flex-wrap: wrap;
        }

        .sticky-search-bar .sticky-search-input {
            max-width: none;
            flex: 1 1 100%;
        }
    }

    /* Explore Slider (Hero-style: 1 image per slide) - Admin-managed */
    .explore-slider-section {
        margin-bottom: 1.5rem;
        overflow: hidden;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background: white;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }
    .explore-slider {
        overflow: hidden;
        padding: 0;
    }
    .explore-slide-inner {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .explore-slide-image {
        aspect-ratio: 21 / 9;
        min-height: 200px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .explore-slide-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .explore-slider .swiper-button-prev,
    .explore-slider .swiper-button-next {
        color: var(--primary-color);
        background: white;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .explore-slider .swiper-button-prev::after,
    .explore-slider .swiper-button-next::after {
        font-size: 0.875rem;
    }
    .explore-slider .swiper-pagination-bullet-active {
        background: var(--primary-color);
    }

    /* Featured Templates Section - Swiper carousel */
    .featured-templates-section {
        margin-bottom: 1.5rem;
    }
    .featured-section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--dark-text);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .featured-section-title i {
        color: #f59e0b;
    }
    .featured-swiper {
        overflow: hidden;
        padding: 0.25rem 0;
    }
    .featured-swiper .swiper-slide {
        height: auto;
        box-sizing: border-box;
    }
    .featured-swiper .swiper-slide .col-12 {
        width: 100%;
        max-width: 100%;
        margin: 0;
    }
    /* Smaller card content inside featured carousel */
    .featured-swiper .template-thumbnail {
        min-height: 120px;
        aspect-ratio: 4 / 3;
    }
    .featured-swiper .template-thumbnail img {
        padding: 4px;
    }
    .featured-swiper .template-info {
        padding: 0.5rem 0.6rem 0.6rem;
    }
    .featured-swiper .template-title {
        font-size: 0.8125rem;
        -webkit-line-clamp: 2;
    }
    .featured-swiper .template-description {
        font-size: 0.6875rem;
        -webkit-line-clamp: 1;
        margin-bottom: 0.25rem;
    }
    .featured-swiper .template-meta {
        font-size: 0.625rem;
        margin-bottom: 0.25rem;
    }
    .featured-swiper .template-review-score,
    .featured-swiper .template-review-score .stars {
        font-size: 0.625rem;
    }
    .featured-swiper .template-card-actions .btn,
    .featured-swiper .template-card-actions .btn-primary {
        font-size: 0.7rem;
        padding: 0.35rem 0.6rem;
    }
    .featured-swiper .template-card-actions .btn-icon {
        width: 28px;
        height: 28px;
        font-size: 0.75rem;
    }
    .featured-swiper .template-favorite-btn {
        padding: 0.25rem;
        font-size: 0.75rem;
    }
    .featured-swiper .template-thumbnail-badge .price-tag {
        font-size: 0.65rem;
        padding: 0.2rem 0.35rem;
    }
    .featured-swiper .swiper-button-prev,
    .featured-swiper .swiper-button-next {
        color: var(--primary-color);
        background: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid var(--border-color);
    }
    .featured-swiper .swiper-button-prev:after,
    .featured-swiper .swiper-button-next:after {
        font-size: 1rem;
        font-weight: 700;
    }
    .featured-swiper .swiper-button-prev:hover,
    .featured-swiper .swiper-button-next:hover {
        background: var(--light-bg);
        color: var(--secondary-color);
    }
    /* Featured carousel: mobile – 2 per page, smaller cards */
    @media (max-width: 767px) {
        .featured-templates-section { margin-bottom: 1rem; }
        .featured-section-title { font-size: 1rem; margin-bottom: 0.5rem; }
        .featured-swiper .swiper-slide { max-width: 50%; }
        .featured-swiper .template-thumbnail {
            min-height: 72px;
            aspect-ratio: 4 / 3;
        }
        .featured-swiper .template-thumbnail img { padding: 2px; }
        .featured-swiper .template-info {
            padding: 0.35rem 0.4rem 0.45rem;
        }
        .featured-swiper .template-title {
            font-size: 0.7rem;
            -webkit-line-clamp: 2;
            line-height: 1.2;
        }
        .featured-swiper .template-description {
            font-size: 0.6rem;
            -webkit-line-clamp: 1;
            margin-bottom: 0.15rem;
        }
        .featured-swiper .template-meta {
            font-size: 0.55rem;
            margin-bottom: 0.15rem;
        }
        .featured-swiper .template-review-score,
        .featured-swiper .template-review-score .stars {
            font-size: 0.55rem;
        }
        .featured-swiper .template-card-actions .btn,
        .featured-swiper .template-card-actions .btn-primary {
            font-size: 0.6rem;
            padding: 0.25rem 0.45rem;
        }
        .featured-swiper .template-card-actions .btn-icon {
            width: 24px;
            height: 24px;
            font-size: 0.65rem;
        }
        .featured-swiper .template-favorite-btn {
            padding: 0.2rem;
            font-size: 0.65rem;
        }
        .featured-swiper .template-thumbnail-badge .price-tag {
            font-size: 0.55rem;
            padding: 0.15rem 0.25rem;
        }
        .featured-swiper .swiper-button-prev,
        .featured-swiper .swiper-button-next {
            width: 32px;
            height: 32px;
        }
        .featured-swiper .swiper-button-prev:after,
        .featured-swiper .swiper-button-next:after {
            font-size: 0.75rem;
        }
    }
    /* Latest Added Templates header */
    .latest-templates-header {
        margin-top: 0.25rem;
    }
    .latest-section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--dark-text);
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .latest-section-title i {
        color: var(--primary-color);
    }
</style>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js@7/minified/introjs.min.css">
@endpush

@section('content')
<!-- Sticky Search Bar - appears when main filter scrolls out of view -->
<div class="sticky-search-bar" id="stickySearchBar">
    <input type="text" class="sticky-search-input" id="stickySearchInput" placeholder="Search templates..." oninput="handleStickySearchInput()" onkeydown="handleSearchKeydown(event)">
    @if($exploreShowCategories ?? true)
    <div class="sticky-categories" id="stickyCategories">
        <button type="button" class="sticky-category-btn active" data-category="all" onclick="filterByCategoryFromSticky('all', this)"><i class="fas fa-th-large me-1"></i>All</button>
        @foreach($categories ?? [] as $cat)
        <button type="button" class="sticky-category-btn" data-category="{{ $cat->slug }}" onclick="filterByCategoryFromSticky('{{ $cat->slug }}', this)"><i class="fas fa-folder me-1"></i>{{ $cat->name }}</button>
        @endforeach
    </div>
    @endif
</div>

<div class="container">
    <div class="explore-layout">
        <!-- Main Content -->
        <div class="explore-main">
    <div id="explorePreloaderOverlay" class="is-visible" role="status" aria-live="polite" aria-busy="true">
        <div class="explore-preloader-spinner" aria-hidden="true"></div>
        <p class="explore-preloader-text">Loading templates…</p>
    </div>
    <button type="button" class="mobile-filters-btn" id="mobileFiltersBtn" onclick="toggleMobileFilters()">
        <i class="fas fa-filter"></i> Filters
    </button>
    <!-- Search & Filter Section - Premium Design -->
    <div class="explore-filter-wrapper" id="exploreFilterSection">
        <div class="filter-section-header">
            <div class="explore-search-bar" id="searchInputWrapper">
                <span class="search-icon"><i class="fas fa-search"></i></span>
                <input type="text" class="search-input" id="templateSearchInput" placeholder="Search templates by name, description, or category..." oninput="handleSearchInput()" onkeydown="handleSearchKeydown(event)">
                <button type="button" class="search-clear-btn" onclick="clearSearch()" title="Clear search">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <span class="search-results-badge" id="searchResultsCount" style="display: none;"></span>
        </div>
        @if($exploreShowCategories ?? true)
        <div class="category-section-label">
            <i class="fas fa-layer-group"></i>
            Browse by Category
        </div>
        <div class="category-carousel-wrapper">
            <button type="button" class="category-carousel-arrow" id="categoryArrowPrev" onclick="scrollCategoryCarousel(-1)" title="Previous" aria-label="Previous categories">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="category-carousel-track">
                <div class="category-filter" id="categoryFilter">
                    <button class="category-btn active" onclick="filterByCategory('all', this)"><i class="fas fa-th-large"></i>All</button>
                    @foreach($categories ?? [] as $cat)
                    <button class="category-btn" onclick="filterByCategory('{{ $cat->slug }}', this)"><i class="fas fa-folder"></i>{{ $cat->name }}</button>
                    @endforeach
                </div>
            </div>
            <button type="button" class="category-carousel-arrow" id="categoryArrowNext" onclick="scrollCategoryCarousel(1)" title="Next" aria-label="Next categories">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        @endif
    </div>

    <!-- Slider + AI Content Templates (hidden when search is active) -->
    <div id="exploreHeroSection">
        @if(isset($slides) && $slides->count() > 0)
        <div class="explore-slider-section mb-4">
            <div class="swiper explore-slider" id="exploreSlider">
                <div class="swiper-wrapper">
                    @foreach($slides as $slide)
                    <div class="swiper-slide">
                        <div class="explore-slide-inner">
                            <div class="explore-slide-image">
                                @if($slide->image_url)
                                    <img src="{{ $slide->image_url }}" alt="{{ $slide->title ?? 'Slide' }}">
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
        @endif
        @include('design.partials.ai-content-templates')
    </div>

    <!-- Featured Templates - Swiper carousel -->
    @if(($exploreShowFeatured ?? true) && isset($featuredTemplates) && count($featuredTemplates) > 0)
    <div class="featured-templates-section mb-3" id="featuredTemplatesSection">
        <h3 class="featured-section-title"><i class="fas fa-star"></i> Featured Templates</h3>
        <div class="swiper featured-swiper" id="featuredTemplatesSwiper">
            <div class="swiper-wrapper" id="featuredTemplatesContainer">
                <!-- Slides rendered by JS -->
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>
    @endif

    <!-- Search results heading (shown when URL has ?q= or search is active) -->
    <div id="searchResultsSection" class="search-results-section mb-3" style="display: none;">
        <h3 class="search-results-heading"><i class="fas fa-search"></i> Search results for "<span id="searchResultsQuery"></span>"</h3>
        <p class="search-results-sub" id="searchResultsSub">Loading...</p>
    </div>

    <!-- Latest Added Templates header (hidden when search is active) -->
    <div id="latestTemplatesHeader" class="latest-templates-header mb-2">
        <h3 class="latest-section-title"><i class="fas fa-clock"></i> Latest Added Templates</h3>
    </div>

    <!-- Templates Grid -->
    <div class="row g-2 mb-2" id="templatesContainer">
        <!-- Templates will be loaded here -->
    </div>

    <div id="explorePaginationWrap" class="explore-pagination-wrap d-none" aria-label="Template list pages">
        <div class="explore-pagination-info text-center text-muted small mb-2" id="explorePaginationInfo"></div>
        <nav>
            <ul class="pagination pagination-sm justify-content-center flex-wrap mb-0" id="explorePagination"></ul>
        </nav>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="text-center py-5" style="display: none;">
        <div style="width: 80px; height: 80px; margin: 0 auto 1.25rem; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-layer-group" style="font-size: 2.5rem; color: #94a3b8;"></i>
        </div>
        <h4 style="color: #334155; margin-bottom: 0.5rem; font-weight: 600;">No Templates Found</h4>
        <p style="color: #64748b; font-size: 0.9375rem;">Try adjusting your filters or check back later.</p>
    </div>

    <!-- Loading State (inline; primary UX is #explorePreloaderOverlay until thumbnails load) -->
    <div id="loadingState" class="text-center py-5" style="display: none;">
        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
        <p style="color: #64748b; margin-top: 1rem; font-size: 0.9375rem;">Loading templates...</p>
    </div>

    <!-- Template card hover tooltip (description + details) -->
    <div id="templateCardTooltip" role="tooltip" aria-hidden="true"></div>
        </div>

        <!-- Right Sidebar - Filters -->
        <aside class="explore-sidebar" id="exploreSidebar">
            <div class="filter-sidebar-mobile-header">
                <span style="font-weight: 600; font-size: 1rem; color: var(--dark-text);">Filters</span>
                <button type="button" onclick="toggleMobileFilters()" style="background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; padding: 0.25rem;" aria-label="Close filters"><i class="fas fa-times"></i></button>
            </div>
            <div class="filter-sidebar-panel">
                <div class="filter-sidebar-section">
                    <div class="filter-sidebar-title"><i class="fas fa-search"></i>Search</div>
                    <div class="filter-sidebar-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="sidebarSearchInput" placeholder="Search templates..." oninput="handleSidebarSearchInput()" onkeydown="handleSearchKeydown(event)">
                    </div>
                </div>
                <div class="filter-sidebar-section">
                    <div class="filter-sidebar-title"><i class="fas fa-dollar-sign"></i>Price</div>
                    <label class="filter-option"><input type="radio" name="priceFilter" value="all" checked onchange="applyPriceFilter('all')"> All</label>
                    <label class="filter-option"><input type="radio" name="priceFilter" value="free" onchange="applyPriceFilter('free')"> Free</label>
                    <label class="filter-option"><input type="radio" name="priceFilter" value="paid" onchange="applyPriceFilter('paid')"> Paid</label>
                    <div class="filter-price-range" style="margin-top: 0.5rem;">
                        <input type="number" id="priceMin" placeholder="Min {{ \App\Models\Setting::get('currency_symbol') ?: '$' }}" min="0" step="0.01" style="width: 70px;" onchange="applyPriceRange()">
                        <span style="color: #94a3b8; font-size: 0.75rem;">–</span>
                        <input type="number" id="priceMax" placeholder="Max {{ \App\Models\Setting::get('currency_symbol') ?: '$' }}" min="0" step="0.01" style="width: 70px;" onchange="applyPriceRange()">
                    </div>
                </div>
                <div class="filter-sidebar-section">
                    <div class="filter-sidebar-title"><i class="fas fa-file-alt"></i>Pages</div>
                    <select class="filter-select" id="pageCountFilter" onchange="applyPageFilter()">
                        <option value="all">All</option>
                        <option value="1">1 page</option>
                        <option value="2-5">2–5 pages</option>
                        <option value="6-10">6–10 pages</option>
                        <option value="11+">11+ pages</option>
                    </select>
                </div>
                <div class="filter-sidebar-section">
                    <div class="filter-sidebar-title"><i class="fas fa-sort"></i>Sort By</div>
                    <select class="filter-select" id="sortFilter" onchange="applySortFilter()">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="popular">Most Popular</option>
                        <option value="name">Name A–Z</option>
                    </select>
                </div>
                <div class="filter-sidebar-section">
                    <div class="filter-sidebar-title"><i class="fas fa-certificate"></i>License</div>
                    <select class="filter-select" id="licenseFilter" onchange="applyLicenseFilter()">
                        <option value="all">All</option>
                        @foreach($licenses ?? [] as $lic)
                            <option value="{{ $lic->slug }}">{{ $lic->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-sidebar-section">
                    <button type="button" class="filter-reset-btn" onclick="resetAllFilters()">
                        <i class="fas fa-undo me-1"></i>Reset All Filters
                    </button>
                </div>
            </div>
        </aside>
    </div>

    <!-- Mobile Filters Overlay -->
    <div id="mobileFiltersOverlay" style="display: none; position: fixed; z-index: 1055; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4);" onclick="toggleMobileFilters()"></div>
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
                <button onclick="quickUseTemplate()" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: white; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: all 0.3s; text-align: left;" onmouseover="this.style.borderColor='#6366f1'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.15)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
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

@if(!empty($showSpecialOffersModal))
<!-- Special Offers Banner Modal -->
<div id="specialOffersModal" class="special-offers-modal" style="display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;" onclick="if(event.target === this) dismissSpecialOffersModal();">
    <div class="special-offers-modal-content" style="position: relative; background: white; border-radius: 16px; max-width: 480px; width: 100%; overflow: hidden; box-shadow: 0 24px 48px rgba(0,0,0,0.2);" onclick="event.stopPropagation();">
        <button type="button" onclick="dismissSpecialOffersModal()" style="position: absolute; top: 0.5rem; right: 0.5rem; z-index: 2; background: rgba(0,0,0,0.4); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1.25rem; line-height: 1; display: flex; align-items: center; justify-content: center;">&times;</button>
        @if(!empty($specialOffersModalImageUrl))
            <div style="width: 100%; max-height: 200px; overflow: hidden; background: #f1f5f9;">
                <img src="{{ $specialOffersModalImageUrl }}" alt="Special offers" style="width: 100%; height: auto; object-fit: cover; display: block;">
            </div>
            <div style="padding: 1.25rem;">
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.25rem; font-weight: 700; color: #1e293b;">Special Offers</h3>
                <p style="color: #64748b; font-size: 0.9375rem; margin-bottom: 1rem; line-height: 1.5;">Welcome! Check out exclusive templates and deals for you.</p>
                <button type="button" onclick="dismissSpecialOffersModal()" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none; padding: 0.65rem; font-weight: 600; border-radius: 10px;">
                    <i class="fas fa-check me-2"></i>Got it
                </button>
            </div>
        @else
            <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 1.5rem; text-align: center; color: white;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;"><i class="fas fa-gift"></i></div>
                <h3 style="margin: 0 0 0.25rem 0; font-size: 1.35rem; font-weight: 700;">Special Offers</h3>
                <p style="margin: 0; font-size: 0.9rem; opacity: 0.95;">Welcome! Check out exclusive templates and deals for you.</p>
            </div>
            <div style="padding: 1.5rem;">
                <p style="color: #64748b; font-size: 0.9375rem; margin-bottom: 1.25rem; line-height: 1.5;">Explore our curated templates and start creating. This offer is just for you.</p>
                <button type="button" onclick="dismissSpecialOffersModal()" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none; padding: 0.65rem; font-weight: 600; border-radius: 10px;">
                    <i class="fas fa-check me-2"></i>Got it
                </button>
            </div>
        @endif
    </div>
</div>
@endif

@include('design.partials.share-template-modal')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intro.js@7/minified/intro.min.js"></script>
<script>
    window.currencySymbol = @json(\App\Models\Setting::get('currency_symbol') ?: '$');
    window.exploreIntroShowMode = @json($introShowMode ?? 'first_time');
    window.exploreIntroAlreadySeenForAccount = @json($introAlreadySeenForAccount ?? false);
    window.exploreIntroSteps = @json($introExploreSteps ?? []);
    var EXPLORE_INTRO_STORAGE_KEY = 'design_intro_seen_templates_explore';
    var EXPLORE_INTRO_MARK_SEEN_URL = @json(route('design.intro.markSeen'));

    /** After Explore intro (or if intro is skipped), show "Complete your profile" if layout deferred it. */
    function scheduleRequiredContactModalAfterExplore() {
        if (!window.__deferRequiredContactUntilExploreIntro) return;
        setTimeout(function() {
            if (typeof window.showRequiredContactModalIfNeeded === 'function') window.showRequiredContactModalIfNeeded();
        }, 400);
    }

    function startExploreIntro() {
        if (typeof introJs === 'undefined') return;
        var steps = [];
        if (window.exploreIntroSteps && window.exploreIntroSteps.length > 0) {
            window.exploreIntroSteps.forEach(function(s) {
                var el = (s.element_selector && document.querySelector(s.element_selector)) || null;
                var intro = (s.title ? '<strong>' + s.title + '</strong><br><br>' : '') + (s.intro_text || '');
                steps.push(el ? { element: el, intro: intro } : { intro: intro });
            });
        } else {
            steps = [
                { intro: '<strong>Explore Templates</strong><br><br>This tour shows how to find and use templates. Click the <strong>Tour</strong> button anytime to see it again.' },
                { element: document.getElementById('exploreFilterSection'), intro: '<strong>Search &amp; categories</strong><br><br>Search by name or description, and filter by category to find templates quickly.' },
                { element: document.getElementById('exploreSidebar'), intro: '<strong>Filters</strong><br><br>Narrow by price, page count, sort order, and license. Reset when you want to start over.' },
                { element: document.getElementById('templatesContainer'), intro: '<strong>Template grid</strong><br><br>Click a template to open it, then choose to use it in the design tool or for Quick Use.' },
                { intro: '<strong>You\'re set</strong><br><br>Pick a template and start designing. Use Template for Design Page to edit in the multi-page editor, or Quick Use for variable-based flow.' }
            ];
        }
        var filtered = steps.filter(function(s) { return !s.element || (s.element && document.body.contains(s.element)); });
        if (filtered.length === 0) {
            scheduleRequiredContactModalAfterExplore();
            return;
        }
        var mode = window.exploreIntroShowMode || 'first_time';
        if (mode !== 'first_time_account') try { localStorage.setItem(EXPLORE_INTRO_STORAGE_KEY, '1'); } catch (e) {}
        var inst = introJs().setOptions({
            steps: filtered,
            exitOnOverlayClick: true,
            showStepNumbers: true,
            showBullets: true
        });
        var afterIntroDone = function() {
            if (mode === 'first_time_account') {
                fetch(EXPLORE_INTRO_MARK_SEEN_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ tour: 'explore' })
                }).catch(function() {});
            }
            setTimeout(function() {
                if (typeof window.showRequiredContactModalIfNeeded === 'function') window.showRequiredContactModalIfNeeded();
            }, 450);
        };
        inst.oncomplete(afterIntroDone).onexit(afterIntroDone);
        inst.start();
    }
    function runExploreIntroIfNeeded() {
        if (typeof introJs === 'undefined') {
            scheduleRequiredContactModalAfterExplore();
            return;
        }
        if (SHOW_SPECIAL_OFFERS_MODAL) return; // Intro + profile after dismissSpecialOffersModal
        var mode = window.exploreIntroShowMode || 'first_time';
        if (mode === 'never') {
            scheduleRequiredContactModalAfterExplore();
            return;
        }
        if (mode === 'always') {
            setTimeout(function() { startExploreIntro(); }, 800);
            return;
        }
        if (mode === 'first_time_account') {
            if (!window.exploreIntroAlreadySeenForAccount) setTimeout(function() { startExploreIntro(); }, 800);
            else scheduleRequiredContactModalAfterExplore();
            return;
        }
        if (mode === 'first_time') {
            try {
                if (!localStorage.getItem(EXPLORE_INTRO_STORAGE_KEY)) setTimeout(function() { startExploreIntro(); }, 800);
                else scheduleRequiredContactModalAfterExplore();
            } catch (e) {
                scheduleRequiredContactModalAfterExplore();
            }
            return;
        }
        scheduleRequiredContactModalAfterExplore();
    }
    document.addEventListener('DOMContentLoaded', runExploreIntroIfNeeded);
</script>
<script>
    window.currencySymbol = @json(\App\Models\Setting::get('currency_symbol') ?: '$');
    window.featuredTemplates = @json($featuredTemplates ?? []);
    window.nanoBananaTemplates = @json($nanoBananaTemplates ?? []);
    window.currencyDecimals = parseInt(@json(\App\Models\Setting::get('price_decimal_places') ?: 2), 10);
    function formatPrice(amount) {
        const sym = window.currencySymbol || '$';
        const dec = window.currencyDecimals ?? 2;
        return sym + parseFloat(amount ?? 0).toFixed(dec);
    }

    let currentCategory = 'all';
    const exploreBaseUrl = '{{ route("design.templates.explore") }}';
    let allTemplates = [];
    let searchTerm = '';
    let searchDebounceTimer = null;
    let searchFromUrl = false;
    let priceFilter = 'all';
    let pageFilter = 'all';
    let sortFilter = 'newest';
    let licenseFilter = 'all';
    let exploreCurrentPage = 1;
    const EXPLORE_PER_PAGE = 24;
    let templateCardTooltipTimer = null;
    const TEMPLATE_TOOLTIP_ENABLED = {{ ($exploreTooltipEnabled ?? true) ? 'true' : 'false' }};
    const TEMPLATE_TOOLTIP_DELAY_MS = {{ (int) ($exploreTooltipDelayMs ?? 700) }};
    const SHOW_SPECIAL_OFFERS_MODAL = {{ !empty($showSpecialOffersModal) ? 'true' : 'false' }};

    function dismissSpecialOffersModal() {
        var modal = document.getElementById('specialOffersModal');
        if (modal) modal.style.display = 'none';
        if (!SHOW_SPECIAL_OFFERS_MODAL) return;
        fetch('{{ route("design.specialOffersModal.dismiss") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).catch(function() {});
        // Run intro tour after modal is closed; then "Complete your profile" if deferred
        var mode = window.exploreIntroShowMode || 'first_time';
        if (mode === 'never') {
            scheduleRequiredContactModalAfterExplore();
            return;
        }
        if (mode === 'always') {
            setTimeout(function() { startExploreIntro(); }, 400);
            return;
        }
        if (mode === 'first_time_account') {
            if (!window.exploreIntroAlreadySeenForAccount) setTimeout(function() { startExploreIntro(); }, 400);
            else scheduleRequiredContactModalAfterExplore();
            return;
        }
        if (mode === 'first_time') {
            try {
                if (!localStorage.getItem(EXPLORE_INTRO_STORAGE_KEY)) setTimeout(function() { startExploreIntro(); }, 400);
                else scheduleRequiredContactModalAfterExplore();
            } catch (e) {
                scheduleRequiredContactModalAfterExplore();
            }
            return;
        }
        scheduleRequiredContactModalAfterExplore();
    }

    function showTemplateCardTooltip(cardEl, template) {
        const tooltip = document.getElementById('templateCardTooltip');
        if (!tooltip) return;
        const name = template.name || 'Untitled Template';
        const desc = (template.short_description || template.description || '').trim();
        const parts = [];
        if (template.page_count) parts.push('<i class="fas fa-file-alt"></i> ' + template.page_count + ' page' + (template.page_count > 1 ? 's' : ''));
        if (template.category && template.category !== 'general') parts.push('<i class="fas fa-folder"></i> ' + escapeHtml(template.category.charAt(0).toUpperCase() + template.category.slice(1)));
        if (template.orders_count !== undefined && template.orders_count !== null) parts.push('<i class="fas fa-shopping-cart"></i> ' + (template.orders_count || 0) + ' use' + ((template.orders_count || 0) !== 1 ? 's' : ''));
        const displayPrice = template.display_price ?? template.price;
        const displayCost = template.cost_per_generate ?? displayPrice;
        if (template.is_nanobanana && displayCost != null && parseFloat(displayCost) > 0) parts.push('<i class="fas fa-coins"></i> ' + formatPrice(displayCost) + '/generate');
        else if (displayPrice != null && parseFloat(displayPrice) === 0) parts.push('<i class="fas fa-gift"></i> Free');
        else if (displayPrice != null && parseFloat(displayPrice) > 0) parts.push('<i class="fas fa-tag"></i> ' + formatPrice(displayPrice));
        if (template.licence) parts.push('<i class="fas fa-certificate"></i> ' + escapeHtml(template.licence.charAt(0).toUpperCase() + template.licence.slice(1)));
        const descText = desc.length > 160 ? desc.slice(0, 157) + '…' : desc;
        tooltip.innerHTML = '<div class="template-tooltip-title">' + escapeHtml(name) + '</div>' +
            (descText ? '<div class="template-tooltip-desc">' + escapeHtml(descText) + '</div>' : '') +
            (parts.length ? '<div class="template-tooltip-meta">' + parts.join(' &nbsp; ') + '</div>' : '');
        const rect = cardEl.getBoundingClientRect();
        tooltip.style.left = '';
        tooltip.style.right = '';
        tooltip.style.top = '';
        tooltip.style.bottom = '';
        tooltip.style.display = 'block';
        const ttRect = tooltip.getBoundingClientRect();
        const gap = 8;
        const viewW = window.innerWidth;
        const viewH = window.innerHeight;
        let x = rect.left + (rect.width / 2) - (ttRect.width / 2);
        let y = rect.top - ttRect.height - gap;
        if (y < 12) y = rect.bottom + gap;
        if (x < 12) x = 12;
        if (x + ttRect.width > viewW - 12) x = viewW - ttRect.width - 12;
        if (y + ttRect.height > viewH - 12) y = viewH - ttRect.height - 12;
        tooltip.style.left = x + 'px';
        tooltip.style.top = y + 'px';
        tooltip.classList.add('visible');
        tooltip.setAttribute('aria-hidden', 'false');
    }

    function hideTemplateCardTooltip() {
        if (templateCardTooltipTimer) {
            clearTimeout(templateCardTooltipTimer);
            templateCardTooltipTimer = null;
        }
        const tooltip = document.getElementById('templateCardTooltip');
        if (tooltip) {
            tooltip.classList.remove('visible');
            tooltip.setAttribute('aria-hidden', 'true');
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showExplorePreloader() {
        const el = document.getElementById('explorePreloaderOverlay');
        if (el) {
            el.classList.add('is-visible');
            el.setAttribute('aria-busy', 'true');
        }
    }

    function hideExplorePreloader() {
        const el = document.getElementById('explorePreloaderOverlay');
        if (el) {
            el.classList.remove('is-visible');
            el.setAttribute('aria-busy', 'false');
        }
    }

    function waitForImagesInRoot(root) {
        if (!root) {
            return Promise.resolve();
        }
        const imgs = Array.from(root.querySelectorAll('img')).filter(function(img) {
            return img.src && img.getAttribute('src');
        });
        if (imgs.length === 0) {
            return Promise.resolve();
        }
        return Promise.all(imgs.map(function(img) {
            if (img.complete) {
                return Promise.resolve();
            }
            return new Promise(function(resolve) {
                var done = function() {
                    resolve();
                };
                img.addEventListener('load', done, { once: true });
                img.addEventListener('error', done, { once: true });
            });
        }));
    }

    /** After templates are in the DOM, wait for visible thumbnails (capped) then hide overlay. */
    function finishExploreInitialPreload() {
        var maxMs = 14000;
        var roots = [
            document.getElementById('templatesContainer'),
            document.getElementById('featuredTemplatesContainer'),
            document.getElementById('exploreSlider'),
            document.getElementById('exploreHeroSection')
        ].filter(Boolean);
        var waitAll = Promise.all(roots.map(waitForImagesInRoot));
        return Promise.race([
            waitAll,
            new Promise(function(r) { setTimeout(r, maxMs); })
        ]).finally(hideExplorePreloader);
    }

    function loadPublicTemplates() {
        const container = document.getElementById('templatesContainer');
        const emptyState = document.getElementById('emptyState');
        const loadingState = document.getElementById('loadingState');

        showExplorePreloader();
        if (loadingState) loadingState.style.display = 'none';
        container.innerHTML = '';
        emptyState.style.display = 'none';

        const url = '{{ route("design.templates.index") }}?all=1';

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (loadingState) loadingState.style.display = 'none';

            if (data.success && data.templates && data.templates.length > 0) {
                allTemplates = [...data.templates, ...(window.nanoBananaTemplates || [])];
                renderFilteredTemplates();
            } else {
                allTemplates = (window.nanoBananaTemplates || []).length > 0 ? window.nanoBananaTemplates : [];
                if (allTemplates.length > 0) {
                    renderFilteredTemplates();
                } else {
                    emptyState.style.display = 'block';
                }
            }
            return finishExploreInitialPreload();
        })
        .catch(error => {
            console.error('Error loading templates:', error);
            if (loadingState) loadingState.style.display = 'none';
            emptyState.style.display = 'block';
            hideExplorePreloader();
        });
    }

    function initExploreSlider() {
        const slider = document.getElementById('exploreSlider');
        if (!slider || typeof Swiper === 'undefined') return;
        const slides = slider.querySelectorAll('.swiper-slide');
        if (slides.length === 0) return;
        new Swiper('#exploreSlider', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: slides.length >= 2,
            autoplay: { delay: 5000, disableOnInteraction: false },
            navigation: {
                nextEl: slider.querySelector('.swiper-button-next'),
                prevEl: slider.querySelector('.swiper-button-prev')
            },
            pagination: {
                el: slider.querySelector('.swiper-pagination'),
                clickable: true
            }
        });
    }

    function getFilteredTemplates() {
        let filtered = allTemplates;

        // Filter by category
        if (currentCategory !== 'all') {
            filtered = filtered.filter(t => t.category === currentCategory);
        }

        // Filter by search term
        if (searchTerm.trim()) {
            const term = searchTerm.toLowerCase().trim();
            filtered = filtered.filter(t => {
                const name = (t.name || '').toLowerCase();
                const desc = (t.short_description || t.description || '').toLowerCase();
                const category = (t.category || '').toLowerCase();
                return name.includes(term) || desc.includes(term) || category.includes(term);
            });
        }

        // Filter by price (use display_price = template + product when available)
        const priceFor = (t) => (t.display_price ?? t.price ?? 0);
        if (priceFilter === 'free') {
            filtered = filtered.filter(t => priceFor(t) === 0);
        } else if (priceFilter === 'paid') {
            filtered = filtered.filter(t => priceFor(t) > 0);
        }
        const minVal = document.getElementById('priceMin')?.value;
        const maxVal = document.getElementById('priceMax')?.value;
        if (minVal && !isNaN(parseFloat(minVal))) {
            filtered = filtered.filter(t => priceFor(t) >= parseFloat(minVal));
        }
        if (maxVal && !isNaN(parseFloat(maxVal))) {
            filtered = filtered.filter(t => priceFor(t) <= parseFloat(maxVal));
        }

        // Filter by page count
        if (pageFilter !== 'all') {
            if (pageFilter === '1') filtered = filtered.filter(t => (t.page_count ?? 0) === 1);
            else if (pageFilter === '2-5') filtered = filtered.filter(t => { const p = t.page_count ?? 0; return p >= 2 && p <= 5; });
            else if (pageFilter === '6-10') filtered = filtered.filter(t => { const p = t.page_count ?? 0; return p >= 6 && p <= 10; });
            else if (pageFilter === '11+') filtered = filtered.filter(t => (t.page_count ?? 0) >= 11);
        }

        // Filter by license
        if (licenseFilter !== 'all') {
            filtered = filtered.filter(t => (t.licence || '').toLowerCase() === licenseFilter.toLowerCase());
        }

        // Sort
        filtered = [...filtered];
        if (sortFilter === 'newest') filtered.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
        else if (sortFilter === 'oldest') filtered.sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));
        else if (sortFilter === 'price-low') filtered.sort((a, b) => (a.display_price ?? a.price ?? 0) - (b.display_price ?? b.price ?? 0));
        else if (sortFilter === 'price-high') filtered.sort((a, b) => (b.display_price ?? b.price ?? 0) - (a.display_price ?? a.price ?? 0));
        else if (sortFilter === 'popular') filtered.sort((a, b) => (b.total_reviews ?? 0) - (a.total_reviews ?? 0));
        else if (sortFilter === 'name') filtered.sort((a, b) => (a.name || '').localeCompare(b.name || ''));

        return filtered;
    }

    function goExplorePage(page) {
        const filtered = getFilteredTemplates();
        const totalPages = Math.max(1, Math.ceil(filtered.length / EXPLORE_PER_PAGE));
        let p = parseInt(page, 10);
        if (isNaN(p) || p < 1) p = 1;
        if (p > totalPages) p = totalPages;
        exploreCurrentPage = p;
        renderFilteredTemplates({ keepPage: true });
        const grid = document.getElementById('templatesContainer');
        if (grid) grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function renderExplorePaginationBar(totalItems, totalPages) {
        const wrap = document.getElementById('explorePaginationWrap');
        const info = document.getElementById('explorePaginationInfo');
        const ul = document.getElementById('explorePagination');
        if (!wrap || !info || !ul) return;

        if (totalItems === 0 || totalPages <= 1) {
            wrap.classList.add('d-none');
            ul.innerHTML = '';
            info.textContent = '';
            return;
        }

        wrap.classList.remove('d-none');
        const startIdx = (exploreCurrentPage - 1) * EXPLORE_PER_PAGE + 1;
        const endIdx = Math.min(exploreCurrentPage * EXPLORE_PER_PAGE, totalItems);
        info.textContent = 'Showing ' + startIdx + '–' + endIdx + ' of ' + totalItems + ' templates';

        function appendPageItem(label, targetPage, opts) {
            opts = opts || {};
            const disabled = !!opts.disabled;
            const active = !!opts.active;
            const li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            if (label === 'prev') {
                a.innerHTML = '&laquo;';
                a.setAttribute('aria-label', 'Previous page');
            } else if (label === 'next') {
                a.innerHTML = '&raquo;';
                a.setAttribute('aria-label', 'Next page');
            } else {
                a.textContent = label;
            }
            if (!disabled) {
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    goExplorePage(targetPage);
                });
            } else {
                a.tabIndex = -1;
            }
            li.appendChild(a);
            ul.appendChild(li);
        }

        ul.innerHTML = '';

        appendPageItem('prev', exploreCurrentPage - 1, { disabled: exploreCurrentPage <= 1 });

        const maxButtons = 5;
        let startP = Math.max(1, exploreCurrentPage - Math.floor(maxButtons / 2));
        let endP = Math.min(totalPages, startP + maxButtons - 1);
        if (endP - startP < maxButtons - 1) {
            startP = Math.max(1, endP - maxButtons + 1);
        }

        if (startP > 1) {
            appendPageItem('1', 1, { active: exploreCurrentPage === 1 });
            if (startP > 2) {
                const li = document.createElement('li');
                li.className = 'page-item disabled';
                li.innerHTML = '<span class="page-link">&hellip;</span>';
                ul.appendChild(li);
            }
        }

        for (let p = startP; p <= endP; p++) {
            appendPageItem(String(p), p, { active: p === exploreCurrentPage });
        }

        if (endP < totalPages) {
            if (endP < totalPages - 1) {
                const li = document.createElement('li');
                li.className = 'page-item disabled';
                li.innerHTML = '<span class="page-link">&hellip;</span>';
                ul.appendChild(li);
            }
            appendPageItem(String(totalPages), totalPages, { active: exploreCurrentPage === totalPages });
        }

        appendPageItem('next', exploreCurrentPage + 1, { disabled: exploreCurrentPage >= totalPages });
    }

    function renderFilteredTemplates(options) {
        options = options || {};
        if (!options.keepPage) {
            exploreCurrentPage = 1;
        }

        const container = document.getElementById('templatesContainer');
        const emptyState = document.getElementById('emptyState');
        const filteredTemplates = getFilteredTemplates();
        const searchSection = document.getElementById('searchResultsSection');
        const searchQueryEl = document.getElementById('searchResultsQuery');
        const searchSubEl = document.getElementById('searchResultsSub');

        const totalFiltered = filteredTemplates.length;
        const totalPages = Math.max(1, Math.ceil(totalFiltered / EXPLORE_PER_PAGE));
        if (exploreCurrentPage > totalPages) {
            exploreCurrentPage = totalPages;
        }
        if (exploreCurrentPage < 1) {
            exploreCurrentPage = 1;
        }

        const sliceStart = (exploreCurrentPage - 1) * EXPLORE_PER_PAGE;
        const pageSlice = filteredTemplates.slice(sliceStart, sliceStart + EXPLORE_PER_PAGE);

        container.innerHTML = '';
        updateSearchResultsCount(totalFiltered);

        if (searchTerm.trim()) {
            if (searchSection) {
                searchSection.style.display = 'block';
                if (searchQueryEl) searchQueryEl.textContent = searchTerm;
                if (searchSubEl) {
                    let sub = totalFiltered + ' template' + (totalFiltered !== 1 ? 's' : '') + ' found';
                    if (totalFiltered > EXPLORE_PER_PAGE) {
                        const s = sliceStart + 1;
                        const e = sliceStart + pageSlice.length;
                        sub += ' · Showing ' + s + '–' + e;
                    }
                    searchSubEl.textContent = sub;
                }
            }
            var latestHeader = document.getElementById('latestTemplatesHeader');
            if (latestHeader) latestHeader.style.display = 'none';
            var featuredSection = document.getElementById('featuredTemplatesSection');
            if (featuredSection) featuredSection.style.display = 'none';
            var heroSection = document.getElementById('exploreHeroSection');
            if (heroSection) heroSection.style.display = 'none';
            if (searchFromUrl) {
                searchFromUrl = false;
                searchSection?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } else {
            if (searchSection) searchSection.style.display = 'none';
            var latestHeader = document.getElementById('latestTemplatesHeader');
            if (latestHeader) latestHeader.style.display = '';
            var featuredSection = document.getElementById('featuredTemplatesSection');
            if (featuredSection) featuredSection.style.display = '';
            var heroSection = document.getElementById('exploreHeroSection');
            if (heroSection) heroSection.style.display = '';
        }

        if (totalFiltered === 0) {
            renderExplorePaginationBar(0, 1);
            emptyState.style.display = 'block';
            const emptyTitle = emptyState.querySelector('h4');
            const emptyText = emptyState.querySelector('p');
            if (searchTerm.trim()) {
                if (emptyTitle) emptyTitle.textContent = 'No Results Found';
                if (emptyText) emptyText.textContent = 'No templates match "' + searchTerm + '". Try different keywords or categories.';
            } else {
                if (emptyTitle) emptyTitle.textContent = 'No Templates Found';
                if (emptyText) emptyText.textContent = 'Try adjusting your filters or check back later.';
            }
            return;
        }

        emptyState.style.display = 'none';
        pageSlice.forEach(template => {
            const templateCard = createTemplateCard(template);
            container.appendChild(templateCard);
        });

        renderExplorePaginationBar(totalFiltered, totalPages);
    }

    function handleSearchInput() {
        const input = document.getElementById('templateSearchInput');
        const wrapper = document.getElementById('searchInputWrapper');
        const sidebarInput = document.getElementById('sidebarSearchInput');
        if (sidebarInput) sidebarInput.value = input?.value || '';

        if (input?.value.trim()) {
            wrapper?.classList.add('has-value');
        } else {
            wrapper?.classList.remove('has-value');
        }

        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            searchTerm = input?.value || '';
            renderFilteredTemplates();
        }, 300);
        const stickyInput = document.getElementById('stickySearchInput');
        if (stickyInput) stickyInput.value = input?.value || '';
    }

    function clearSearch() {
        const input = document.getElementById('templateSearchInput');
        const wrapper = document.getElementById('searchInputWrapper');
        const sidebarInput = document.getElementById('sidebarSearchInput');
        if (input) input.value = '';
        if (sidebarInput) sidebarInput.value = '';
        const stickyInput = document.getElementById('stickySearchInput');
        if (stickyInput) stickyInput.value = '';
        searchTerm = '';
        if (wrapper) wrapper.classList.remove('has-value');
        if (input) input.focus();
        if (window.location.search) {
            window.location = exploreBaseUrl;
            return;
        }
        renderFilteredTemplates();
    }

    function handleSearchKeydown(e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const input = e.target;
        const term = (input && input.value ? input.value : '').trim();
        const url = term ? (exploreBaseUrl + '?q=' + encodeURIComponent(term)) : exploreBaseUrl;
        if (url !== (window.location.pathname + window.location.search)) {
            window.location = url;
        }
    }

    function handleSidebarSearchInput() {
        const sidebarInput = document.getElementById('sidebarSearchInput');
        const mainInput = document.getElementById('templateSearchInput');
        if (sidebarInput && mainInput) mainInput.value = sidebarInput.value;
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            searchTerm = sidebarInput?.value || '';
            const wrapper = document.getElementById('searchInputWrapper');
            if (wrapper) wrapper.classList.toggle('has-value', !!searchTerm.trim());
            const mainInput = document.getElementById('templateSearchInput');
            const stickyInput = document.getElementById('stickySearchInput');
            if (mainInput) mainInput.value = sidebarInput?.value || '';
            if (stickyInput) stickyInput.value = sidebarInput?.value || '';
            renderFilteredTemplates();
        }, 300);
    }

    function applyPriceFilter(val) {
        priceFilter = val;
        renderFilteredTemplates();
    }

    function applyPriceRange() {
        renderFilteredTemplates();
    }

    function applyPageFilter() {
        pageFilter = document.getElementById('pageCountFilter')?.value || 'all';
        renderFilteredTemplates();
    }

    function applySortFilter() {
        sortFilter = document.getElementById('sortFilter')?.value || 'newest';
        renderFilteredTemplates();
    }

    function applyLicenseFilter() {
        licenseFilter = document.getElementById('licenseFilter')?.value || 'all';
        renderFilteredTemplates();
    }

    function resetAllFilters() {
        searchTerm = '';
        priceFilter = 'all';
        pageFilter = 'all';
        sortFilter = 'newest';
        licenseFilter = 'all';
        const mainInput = document.getElementById('templateSearchInput');
        const sidebarInput = document.getElementById('sidebarSearchInput');
        const stickyInput = document.getElementById('stickySearchInput');
        const wrapper = document.getElementById('searchInputWrapper');
        if (mainInput) mainInput.value = '';
        if (sidebarInput) sidebarInput.value = '';
        if (stickyInput) stickyInput.value = '';
        if (wrapper) wrapper.classList.remove('has-value');
        if (window.location.search) {
            window.location = exploreBaseUrl;
            return;
        }
        document.querySelectorAll('input[name="priceFilter"]').forEach(r => { r.checked = r.value === 'all'; });
        const priceMinEl = document.getElementById('priceMin');
        const priceMaxEl = document.getElementById('priceMax');
        if (priceMinEl) priceMinEl.value = '';
        if (priceMaxEl) priceMaxEl.value = '';
        const pageFilterEl = document.getElementById('pageCountFilter');
        const sortFilterEl = document.getElementById('sortFilter');
        const licenseFilterEl = document.getElementById('licenseFilter');
        if (pageFilterEl) pageFilterEl.value = 'all';
        if (sortFilterEl) sortFilterEl.value = 'newest';
        if (licenseFilterEl) licenseFilterEl.value = 'all';
        renderFilteredTemplates();
    }

    function updateSearchResultsCount(count) {
        const el = document.getElementById('searchResultsCount');
        if (!el) return;
        el.style.display = 'inline-flex';
        el.innerHTML = '<strong>' + count + '</strong> ' + (count === 1 ? 'template' : 'templates');
    }

    function toggleMobileFilters() {
        const sidebar = document.querySelector('.explore-sidebar');
        const overlay = document.getElementById('mobileFiltersOverlay');
        if (!sidebar || !overlay) return;
        const isOpen = sidebar.classList.contains('mobile-open');
        if (isOpen) {
            sidebar.classList.remove('mobile-open');
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        } else {
            sidebar.classList.add('mobile-open');
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    function getTemplateThumbnailSrc(template) {
        if (template.thumbnail_url) return template.thumbnail_url;
        if (template.thumbnail && template.thumbnail.startsWith('data:')) return template.thumbnail;
        if (template.thumbnail_path) return '{{ asset("storage") }}/' + template.thumbnail_path;
        return null;
    }

    function createTemplateCard(template) {
        const col = document.createElement('div');
        col.className = 'col-12 col-sm-6 col-md-4';
        col.dataset.templateId = template.id;
        col.dataset.templateType = template.type || '';

        const card = document.createElement('div');
        card.className = 'template-card';
        // Delayed hover tooltip (description + details) – only when enabled
        if (TEMPLATE_TOOLTIP_ENABLED) {
            card.addEventListener('mouseenter', function() {
                if (templateCardTooltipTimer) clearTimeout(templateCardTooltipTimer);
                templateCardTooltipTimer = setTimeout(function() {
                    templateCardTooltipTimer = null;
                    showTemplateCardTooltip(card, template);
                }, TEMPLATE_TOOLTIP_DELAY_MS);
            });
            card.addEventListener('mouseleave', function() {
                hideTemplateCardTooltip();
            });
        }
        // Add click handler to navigate to details page
        card.onclick = function(e) {
            // Don't navigate if clicking on buttons or action areas
            if (e.target.closest('.template-card-actions') ||
                e.target.closest('.btn') ||
                e.target.closest('.template-favorite-btn')) {
                return;
            }
            if (template.is_nanobanana && template.nanobanana_id) {
                window.location.href = '{{ route("design.nanobanana.useTemplate", ["id" => ":id"]) }}'.replace(':id', template.nanobanana_id);
            } else {
                window.location.href = '{{ route("design.templates.show", ":id") }}'.replace(':id', template.id);
            }
        };

        // Thumbnail - attractive image presentation
        const thumbnail = document.createElement('div');
        thumbnail.className = 'template-thumbnail';
        const thumbSrc = getTemplateThumbnailSrc(template);
        if (thumbSrc) {
            const img = document.createElement('img');
            img.src = thumbSrc;
            img.alt = template.name || 'Template preview';
            img.loading = 'eager';
            img.decoding = 'async';
            img.onerror = function() {
                this.style.display = 'none';
                const icon = document.createElement('div');
                icon.className = 'no-thumbnail';
                icon.innerHTML = '<i class="fas fa-layer-group"></i>';
                thumbnail.appendChild(icon);
            };
            thumbnail.appendChild(img);
        } else {
            const icon = document.createElement('div');
            icon.className = 'no-thumbnail';
            icon.innerHTML = '<i class="fas fa-layer-group"></i>';
            thumbnail.appendChild(icon);
        }

        // Price badge on thumbnail - Free (left) or Price (right). Use display_price (template + product) when available.
        const cardDisplayPrice = template.display_price ?? template.price;
        const cost = template.cost_per_generate ?? cardDisplayPrice;
        if (template.is_nanobanana) {
            if (cost !== undefined && cost !== null && parseFloat(cost) > 0) {
                const badge = document.createElement('div');
                badge.className = 'template-thumbnail-badge template-price-badge';
                badge.innerHTML = '<span class="price-tag paid">' + formatPrice(cost) + '/image</span>';
                thumbnail.appendChild(badge);
            }
        } else if (cardDisplayPrice !== undefined && cardDisplayPrice !== null) {
            const badge = document.createElement('div');
            badge.className = 'template-thumbnail-badge' + (parseFloat(cardDisplayPrice) > 0 ? ' template-price-badge' : '');
            const isFree = parseFloat(cardDisplayPrice) === 0;
            badge.innerHTML = '<span class="price-tag ' + (isFree ? 'free' : 'paid') + '">' + (isFree ? 'Free' : formatPrice(cardDisplayPrice)) + '</span>';
            thumbnail.appendChild(badge);
        }

        // Info
        const info = document.createElement('div');
        info.className = 'template-info';

        // Card Header with Favorite Button
        const cardHeader = document.createElement('div');
        cardHeader.className = 'template-card-header';

        const headerLeft = document.createElement('div');
        headerLeft.className = 'header-left';

        if (template.category && template.category !== 'general') {
            const category = document.createElement('span');
            category.className = 'template-category';
            category.textContent = template.category.charAt(0).toUpperCase() + template.category.slice(1);
            headerLeft.appendChild(category);
        }

        const title = document.createElement('h5');
        title.className = 'template-title';
        title.textContent = template.name || 'Untitled Template';
        headerLeft.appendChild(title);

        cardHeader.appendChild(headerLeft);
        if (!template.is_nanobanana) {
            const favoriteBtn = document.createElement('button');
            favoriteBtn.className = 'template-favorite-btn';
            favoriteBtn.innerHTML = '<i class="far fa-heart"></i>';
            favoriteBtn.onclick = function(e) {
                e.stopPropagation();
                toggleFavorite(template.id, favoriteBtn);
            };
            cardHeader.appendChild(favoriteBtn);
        }
        info.appendChild(cardHeader);

        // Review Score
        if (template.average_rating !== undefined && template.total_reviews !== undefined) {
            const reviewScore = document.createElement('div');
            reviewScore.className = 'template-review-score';
            const stars = Math.round(template.average_rating || 0);
            let starsHtml = '';
            for (let i = 1; i <= 5; i++) {
                starsHtml += '<i class="fas fa-star' + (i <= stars ? '' : '-o') + '"></i>';
            }
            reviewScore.innerHTML = '<span class="stars">' + starsHtml + '</span> <span>' + (template.average_rating || 0).toFixed(1) + '</span> <span style="color: #94a3b8;">(' + (template.total_reviews || 0) + ')</span>';
            info.appendChild(reviewScore);
        }

        if (template.short_description || template.description) {
            const description = document.createElement('div');
            description.className = 'template-description';
            description.textContent = template.short_description || template.description || '';
            info.appendChild(description);
        }

        // Price display. Use display_price (template + product) when available.
        const infoDisplayPrice = template.display_price ?? template.price;
        const displayCost = template.cost_per_generate ?? infoDisplayPrice;
        if (template.is_nanobanana) {
            if (displayCost !== undefined && displayCost !== null && parseFloat(displayCost) > 0) {
                const priceDisplay = document.createElement('div');
                priceDisplay.className = 'template-price-display';
                const priceVal = document.createElement('span');
                priceVal.className = 'price-value';
                priceVal.textContent = formatPrice(displayCost) + ' per generate';
                priceDisplay.appendChild(priceVal);
                info.appendChild(priceDisplay);
            }
        } else if (infoDisplayPrice !== undefined && infoDisplayPrice !== null) {
            const priceDisplay = document.createElement('div');
            priceDisplay.className = 'template-price-display';
            const priceVal = document.createElement('span');
            priceVal.className = 'price-value' + (parseFloat(infoDisplayPrice) === 0 ? ' free' : '');
            priceVal.textContent = parseFloat(infoDisplayPrice) === 0 ? 'Free' : formatPrice(infoDisplayPrice);
            priceDisplay.appendChild(priceVal);
            info.appendChild(priceDisplay);
        }

        const meta = document.createElement('div');
        meta.className = 'template-meta';

        if (template.page_count) {
            const pageCount = document.createElement('span');
            pageCount.innerHTML = '<i class="fas fa-file-alt me-1"></i>' + template.page_count + ' page' + (template.page_count > 1 ? 's' : '');
            meta.appendChild(pageCount);
        }

        if (template.orders_count !== undefined && template.orders_count !== null) {
            const usesCount = document.createElement('span');
            usesCount.innerHTML = '<i class="fas fa-shopping-cart me-1"></i>' + (template.orders_count || 0) + ' use' + ((template.orders_count || 0) !== 1 ? 's' : '');
            meta.appendChild(usesCount);
        }

        if (template.licence) {
            const licence = document.createElement('span');
            licence.innerHTML = '<i class="fas fa-certificate me-1"></i>' + template.licence.charAt(0).toUpperCase() + template.licence.slice(1);
            meta.appendChild(licence);
        }

        info.appendChild(meta);

        // Side Actions (Use Button + right side icons)
        const sideActions = document.createElement('div');
        sideActions.className = 'template-card-actions';

        const useBtn = document.createElement('button');
        useBtn.className = 'btn btn-primary';
        useBtn.innerHTML = template.is_nanobanana ? '<i class="fas fa-magic me-1"></i>Generate' : '<i class="fas fa-download me-1"></i>Use';
        useBtn.onclick = function(e) {
            e.stopPropagation();
            if (template.is_nanobanana && template.nanobanana_id) {
                window.location.href = '{{ route("design.nanobanana.useTemplate", ["id" => ":id"]) }}'.replace(':id', template.nanobanana_id);
            } else {
                useTemplate(template.id);
            }
        };
        sideActions.appendChild(useBtn);

        const btnGroupRight = document.createElement('div');
        btnGroupRight.className = 'btn-group-right';

        const viewBtn = document.createElement('a');
        viewBtn.className = 'btn-icon';
        viewBtn.href = template.is_nanobanana && template.nanobanana_id
            ? '{{ route("design.nanobanana.useTemplate", ["id" => ":id"]) }}'.replace(':id', template.nanobanana_id)
            : '{{ route("design.templates.show", ":id") }}'.replace(':id', template.id);
        viewBtn.title = template.is_nanobanana ? 'Create with AI' : 'View details';
        viewBtn.innerHTML = '<i class="fas fa-' + (template.is_nanobanana ? 'magic' : 'eye') + '"></i>';
        viewBtn.onclick = function(e) { e.stopPropagation(); };

        btnGroupRight.appendChild(viewBtn);
        if (!template.is_nanobanana) {
            const shareBtn = document.createElement('button');
            shareBtn.className = 'btn-icon';
            shareBtn.title = 'Share';
            shareBtn.innerHTML = '<i class="fas fa-share-alt"></i>';
            shareBtn.onclick = function(e) {
                e.stopPropagation();
                shareTemplate(template.id, template.name);
            };
            btnGroupRight.appendChild(shareBtn);
        }
        sideActions.appendChild(btnGroupRight);
        info.appendChild(sideActions);

        card.appendChild(thumbnail);
        card.appendChild(info);

        col.appendChild(card);
        return col;
    }

    function filterByCategory(category, clickedBtn) {
        currentCategory = category;

        // Update active button (main + sticky)
        document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.sticky-category-btn').forEach(btn => btn.classList.remove('active'));
        if (clickedBtn) clickedBtn.classList.add('active');
        const stickyBtn = document.querySelector('.sticky-category-btn[data-category="' + category + '"]');
        if (stickyBtn) stickyBtn.classList.add('active');

        // Re-render with current filters
        renderFilteredTemplates();
    }

    function scrollCategoryCarousel(direction) {
        const track = document.getElementById('categoryFilter');
        if (!track) return;
        const scrollAmount = 200;
        track.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
        setTimeout(updateCategoryArrowStates, 300);
    }

    function updateCategoryArrowStates() {
        const track = document.getElementById('categoryFilter');
        const prevBtn = document.getElementById('categoryArrowPrev');
        const nextBtn = document.getElementById('categoryArrowNext');
        if (!track || !prevBtn || !nextBtn) return;

        const canScrollLeft = track.scrollLeft > 0;
        const canScrollRight = track.scrollLeft < track.scrollWidth - track.clientWidth - 2;

        prevBtn.disabled = !canScrollLeft;
        nextBtn.disabled = !canScrollRight;
    }

    function handleStickySearchInput() {
        const stickyInput = document.getElementById('stickySearchInput');
        const mainInput = document.getElementById('templateSearchInput');
        const sidebarInput = document.getElementById('sidebarSearchInput');
        if (mainInput) mainInput.value = stickyInput?.value || '';
        if (sidebarInput) sidebarInput.value = stickyInput?.value || '';
        const wrapper = document.getElementById('searchInputWrapper');
        if (wrapper) wrapper.classList.toggle('has-value', !!(stickyInput?.value || '').trim());
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            searchTerm = stickyInput?.value || '';
            renderFilteredTemplates();
        }, 300);
    }

    function filterByCategoryFromSticky(category, clickedBtn) {
        currentCategory = category;
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('onclick') && btn.getAttribute('onclick').indexOf("'" + category + "'") !== -1) btn.classList.add('active');
        });
        document.querySelectorAll('.sticky-category-btn').forEach(btn => btn.classList.remove('active'));
        if (clickedBtn) clickedBtn.classList.add('active');
        renderFilteredTemplates();
    }

    function syncStickyFromMain() {
        const mainInput = document.getElementById('templateSearchInput');
        const stickyInput = document.getElementById('stickySearchInput');
        if (stickyInput && mainInput) stickyInput.value = mainInput.value;
        document.querySelectorAll('.sticky-category-btn').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-category') === currentCategory);
        });
    }

    var featuredSwiperInstance = null;
    function renderFeaturedTemplates() {
        const wrapper = document.getElementById('featuredTemplatesContainer');
        const swiperEl = document.getElementById('featuredTemplatesSwiper');
        if (!wrapper || !swiperEl || !window.featuredTemplates || window.featuredTemplates.length === 0) return;
        if (featuredSwiperInstance) {
            featuredSwiperInstance.destroy(true, true);
            featuredSwiperInstance = null;
        }
        wrapper.innerHTML = '';
        window.featuredTemplates.forEach(function(template) {
            const slide = document.createElement('div');
            slide.className = 'swiper-slide';
            const col = createTemplateCard(template);
            slide.appendChild(col);
            wrapper.appendChild(slide);
        });
        if (typeof Swiper !== 'undefined') {
            featuredSwiperInstance = new Swiper('#featuredTemplatesSwiper', {
                slidesPerView: 3,
                spaceBetween: 12,
                loop: window.featuredTemplates.length >= 3,
                speed: 800,
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: swiperEl.querySelector('.swiper-button-next'),
                    prevEl: swiperEl.querySelector('.swiper-button-prev'),
                },
                breakpoints: {
                    320: { slidesPerView: 2, spaceBetween: 8 },
                    576: { slidesPerView: 2, spaceBetween: 10 },
                    768: { slidesPerView: 3, spaceBetween: 12 },
                },
            });
        }
    }

    function applySearchFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const q = params.get('q');
        if (q != null && String(q).trim() !== '') {
            const term = String(q).trim();
            searchTerm = term;
            searchFromUrl = true;
            const mainInput = document.getElementById('templateSearchInput');
            const sidebarInput = document.getElementById('sidebarSearchInput');
            const stickyInput = document.getElementById('stickySearchInput');
            const wrapper = document.getElementById('searchInputWrapper');
            if (mainInput) mainInput.value = term;
            if (sidebarInput) sidebarInput.value = term;
            if (stickyInput) stickyInput.value = term;
            if (wrapper) wrapper.classList.add('has-value');
            var heroSection = document.getElementById('exploreHeroSection');
            if (heroSection) heroSection.style.display = 'none';
        }
    }

    // Load templates on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (SHOW_SPECIAL_OFFERS_MODAL) {
            var modal = document.getElementById('specialOffersModal');
            if (modal) {
                modal.style.display = 'flex';
            }
        }
        applySearchFromUrl();
        loadPublicTemplates();
        initExploreSlider();
        renderFeaturedTemplates();
        updateCategoryArrowStates();
        const track = document.getElementById('categoryFilter');
        if (track) {
            track.addEventListener('scroll', updateCategoryArrowStates);
        }
        window.addEventListener('resize', updateCategoryArrowStates);

        // Intersection Observer: show sticky search bar when main filter scrolls out of view
        const filterSection = document.getElementById('exploreFilterSection');
        const stickyBar = document.getElementById('stickySearchBar');
        if (filterSection && stickyBar) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        stickyBar.classList.remove('visible');
                    } else {
                        stickyBar.classList.add('visible');
                        syncStickyFromMain();
                    }
                });
            }, { threshold: 0, rootMargin: '-60px 0px 0px 0px' });
            observer.observe(filterSection);
        }
    });

    let currentTemplateId = null;

    function useTemplate(templateId) {
        currentTemplateId = templateId;
        document.getElementById('useTemplateModal').style.display = 'block';
    }

    function closeUseTemplateModal() {
        document.getElementById('useTemplateModal').style.display = 'none';
        currentTemplateId = null;
    }

    function shareTemplate(templateId, templateName) {
        if (typeof openShareTemplateModal === 'function') {
            openShareTemplateModal(templateId, templateName || 'Template', true);
            return;
        }
        const url = '{{ route("design.templates.show", ["id" => ":id"]) }}'.replace(':id', templateId);
        const text = 'Check out this template: ' + (templateName || 'Template');
        if (navigator.share) {
            navigator.share({ title: templateName || 'Template', text: text, url: url })
                .then(() => {}).catch(() => copyToClipboard(url));
        } else {
            copyToClipboard(url);
        }
    }

    function copyToClipboard(url) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copied to clipboard!');
            }).catch(() => fallbackCopy(url));
        } else {
            fallbackCopy(url);
        }
    }

    function fallbackCopy(url) {
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        alert('Link copied to clipboard!');
    }

    function getCurrentTemplate() {
        const cards = document.querySelectorAll('.template-card');
        for (const card of cards) {
            const col = card.closest('.col-12, .col-sm-6, .col-md-4');
            if (col && col.dataset.templateId == currentTemplateId) {
                return col.dataset.templateType ? { type: col.dataset.templateType } : null;
            }
        }
        return null;
    }

    function useTemplateForDesign() {
        if (!currentTemplateId) return;
        const template = getCurrentTemplate();
        const templateType = template ? template.type : '';
        const baseUrl = '{{ route("design.create") }}';
        let url = baseUrl + '?multi=true&template=' + encodeURIComponent(currentTemplateId);
        if (templateType) url += '&type=' + encodeURIComponent(templateType);
        window.location.href = url;
    }

    function quickUseTemplate() {
        if (!currentTemplateId) return;
        const template = getCurrentTemplate();
        const templateType = template ? template.type : '';
        if (templateType === 'letter') {
            window.location.href = '{{ route("design.templates.sendLetter", ":id") }}'.replace(':id', currentTemplateId);
        } else {
            window.location.href = '{{ route("design.templates.quickUse", ":id") }}'.replace(':id', currentTemplateId);
        }
    }

    function toggleFavorite(templateId, button) {
        // Toggle favorite state
        const icon = button.querySelector('i');
        const isFavorited = button.classList.contains('favorited');

        if (isFavorited) {
            button.classList.remove('favorited');
            icon.classList.remove('fas');
            icon.classList.add('far');
        } else {
            button.classList.add('favorited');
            icon.classList.remove('far');
            icon.classList.add('fas');
        }

        // TODO: Add API call to save favorite state
        // fetch('/api/templates/' + templateId + '/favorite', {
        //     method: 'POST',
        //     headers: {
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        //         'Content-Type': 'application/json'
        //     },
        //     body: JSON.stringify({ favorited: !isFavorited })
        // });
    }

</script>
@endpush

