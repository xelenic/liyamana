@extends('layouts.app')

@section('title', 'Templates - ' . site_name())

@push('styles')
<style>
    .templates-explore-layout { display: flex; gap: 1rem; align-items: flex-start; }
    .templates-main { flex: 1; min-width: 0; }
    .templates-sidebar { width: 260px; flex-shrink: 0; position: sticky; top: 1.5rem; }
    .templates-filter-panel { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
    .templates-filter-section { padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; }
    .templates-filter-section:last-child { border-bottom: none; }
    .templates-filter-title { font-size: 0.6875rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem; }
    .templates-filter-title i { color: #6366f1; font-size: 0.75rem; }
    .templates-filter-search { display: flex; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0 0.75rem; height: 40px; }
    .templates-filter-search:focus-within { border-color: #6366f1; background: white; }
    .templates-filter-search i { color: #94a3b8; font-size: 0.875rem; margin-right: 0.5rem; }
    .templates-filter-search input { flex: 1; border: none; background: transparent; font-size: 0.875rem; color: #1e293b; }
    .templates-filter-search input:focus { outline: none; }
    .templates-filter-option { display: block; padding: 0.5rem 0; cursor: pointer; font-size: 0.875rem; color: #475569; transition: color 0.2s; text-decoration: none; }
    .templates-filter-option:hover { color: #6366f1; }
    .templates-filter-option.active { color: #6366f1; font-weight: 600; }
    .templates-filter-select { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem; color: #1e293b; background: white; }
    .templates-filter-select:focus { outline: none; border-color: #6366f1; }
    .templates-filter-reset { width: 100%; padding: 0.5rem 1rem; margin-top: 0.5rem; border: 1px solid #e2e8f0; background: white; border-radius: 8px; font-size: 0.8125rem; color: #64748b; cursor: pointer; transition: all 0.2s; }
    .templates-filter-reset:hover { background: #f8fafc; color: #6366f1; }
    .templates-mobile-filters-btn { display: none; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border: 1px solid #e2e8f0; background: white; border-radius: 8px; font-size: 0.875rem; font-weight: 500; color: #475569; cursor: pointer; margin-bottom: 1rem; }
    .templates-mobile-filters-btn:hover { border-color: #6366f1; color: #6366f1; }
    .templates-sidebar-mobile-header { display: none; padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; align-items: center; justify-content: space-between; background: white; }
    @media (max-width: 991px) {
        .templates-sidebar { display: none; position: fixed; right: 0; top: 0; height: 100vh; width: 280px; max-width: 90%; z-index: 1060; background: white; box-shadow: -4px 0 20px rgba(0,0,0,0.15); overflow-y: auto; }
        .templates-sidebar.mobile-open { display: block !important; }
        .templates-sidebar.mobile-open .templates-sidebar-mobile-header { display: flex !important; }
        .templates-mobile-filters-btn { display: inline-flex; }
    }
    @media (min-width: 992px) { .templates-mobile-filters-btn { display: none !important; } }
    .templates-filters-overlay { display: none; position: fixed; z-index: 1055; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); }

    /* Template cards — full preview in frame (no crop), aligned with design/explore */
    .templates-page-card {
        border-radius: 12px;
        overflow: hidden;
        background: white;
        display: flex;
        flex-direction: column;
        height: 100%;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .templates-page-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.1);
        border-color: rgba(99, 102, 241, 0.25);
    }
    .templates-page-card:hover .templates-page-card-thumb img {
        transform: scale(1.02);
    }
    .templates-page-card-thumb {
        width: 100%;
        aspect-ratio: 4 / 3;
        min-height: 160px;
        max-height: 220px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        flex-shrink: 0;
    }
    .templates-page-card-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 8px;
        transition: transform 0.3s ease;
    }
    .templates-page-card .card-body {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
    }
    .templates-page-actions {
        display: flex;
        align-items: stretch;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 0.35rem;
    }
    .templates-page-fav-btn {
        flex: 0 0 auto;
        width: 40px;
        min-height: 38px;
        padding: 0;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: color 0.2s, border-color 0.2s, background 0.2s, transform 0.15s;
    }
    .templates-page-fav-btn:hover {
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.35);
        background: white;
    }
    .templates-page-fav-btn.favorited {
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.4);
        background: rgba(254, 242, 242, 0.6);
    }
    .templates-page-fav-btn i {
        font-size: 0.95rem;
    }
    .templates-page-use-btn {
        flex: 1 1 auto;
        min-width: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
    }
    @media (max-width: 575px) {
        .templates-page-card-thumb {
            min-height: 132px;
            max-height: 180px;
        }
        .templates-page-card-thumb img {
            padding: 6px;
        }
    }

    /* Top promo slider — taller strip, full image visible (no crop) */
    .templates-landing-slider .templates-landing-slide-inner {
        min-height: 280px;
        height: clamp(220px, 66vh, 440px);
        max-height: 640px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .templates-landing-slider .templates-landing-slide-inner img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        padding: 1rem 1.25rem;
        box-sizing: border-box;
    }
    @media (max-width: 575px) {
        .templates-landing-slider .templates-landing-slide-inner {
            min-height: 200px;
            height: clamp(180px, 32vh, 320px);
            max-height: 320px;
        }
        .templates-landing-slider .templates-landing-slide-inner img {
            padding: 0.65rem 0.85rem;
        }
    }
</style>
@endpush

@section('content')
@php
    $filterUrl = fn($overrides = []) => route('templates', array_filter(array_merge(request()->only(['category','price','pages','sort','q']), $overrides), fn($v) => $v !== null && $v !== '' && $v !== 'all'));
@endphp
<!-- Hero Section -->
<section class="hero-section" style="margin-top: 0px !important; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 50%, #f8fafc 100%); padding: 6rem 1rem 4rem; position: relative; overflow: hidden;">
    <!-- Decorative Elements -->
    <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px;border-radius: 50%; filter: blur(80px);"></div>
    <div style="position: absolute; bottom: -150px; left: -150px; width: 500px; height: 500px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(59, 130, 246, 0.05) 100%); border-radius: 50%; filter: blur(100px);"></div>

    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <!-- Badge -->
                <div class="mb-3">
                    <span class="badge px-3 py-2" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 20px;">
                        <i class="fas fa-images me-2"></i>Professional Templates
                    </span>
                </div>

                <!-- Main Heading -->
                <h1 class="mb-4" style="font-size: 3.5rem; font-weight: 900; line-height: 1.1; letter-spacing: -0.5px; color: #0f172a;">
                    Choose Your Perfect <span style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; display: inline-block;">Template</span>
                </h1>

                <!-- Subheading -->
                <p class="mb-0 mx-auto" style="font-size: 1.25rem; line-height: 1.7; font-weight: 400; color: #475569; max-width: 700px;">
                    Browse our collection of professionally designed templates. Each template is fully customizable and ready to use.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Category Filter Section -->
<section class="py-4" style="background: white; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <h3 class="mb-0" style="font-weight: 700; color: #1e293b; font-size: 1.5rem;">Browse Templates</h3>
            </div>
            <div class="col-md-6">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    @php
                        $activeStyle = 'background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none;';
                        $inactiveStyle = 'background: white; color: #475569; border: 2px solid #e2e8f0;';
                    @endphp
                    <a href="{{ $filterUrl() }}" class="btn px-3 py-2" style="{{ !request('category') ? $activeStyle : $inactiveStyle }} font-weight: 600; font-size: 0.9rem; border-radius: 8px; transition: all 0.3s; text-decoration: none;" onmouseover="if(!this.classList.contains('active')){this.style.borderColor='#6366f1'; this.style.color='#6366f1'; this.style.transform='translateY(-2px)'}" onmouseout="if(!this.classList.contains('active')){this.style.borderColor='#e2e8f0'; this.style.color='#475569'; this.style.transform='translateY(0)'}">All</a>
                    @foreach($categories ?? [] as $cat)
                    <a href="{{ $filterUrl(['category' => $cat]) }}" class="btn px-3 py-2" style="{{ request('category') === $cat ? $activeStyle : $inactiveStyle }} font-weight: 600; font-size: 0.9rem; border-radius: 8px; transition: all 0.3s; text-decoration: none;" onmouseover="if(!this.classList.contains('active')){this.style.borderColor='#6366f1'; this.style.color='#6366f1'; this.style.transform='translateY(-2px)'}" onmouseout="if(!this.classList.contains('active')){this.style.borderColor='#e2e8f0'; this.style.color='#475569'; this.style.transform='translateY(0)'}">{{ ucfirst(str_replace('-', ' ', $cat)) }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Templates Section with Right Sidebar -->
<section class="py-5" style="background: linear-gradient(to bottom, #ffffff 0%, #f8fafc 100%); padding: 4rem 0 !important;">
    <div class="container">
        <div class="templates-explore-layout">
            <div class="templates-main">
                <button type="button" class="templates-mobile-filters-btn" id="templatesMobileFiltersBtn" onclick="toggleTemplatesFilters()"><i class="fas fa-filter"></i> Filters</button>

                @if(isset($slides) && $slides->isNotEmpty())
                <div class="mb-4" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                    <div class="swiper templates-landing-slider">
                        <div class="swiper-wrapper">
                            @foreach($slides as $slide)
                            <div class="swiper-slide">
                                <div class="templates-landing-slide-inner" @if(empty($slide->image_url)) style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);" @endif>
                                    @if($slide->image_url ?? null)
                                        <img src="{{ $slide->image_url }}" alt="{{ $slide->title ?? 'Slide' }}" loading="lazy">
                                    @else
                                        <i class="fas fa-images text-white" style="font-size: 3rem; opacity: 0.5;"></i>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
                @endif

                @if(isset($templates) && count($templates) > 0)
        <div class="row">
            @php
                $gradients = ['#6366f1 0%, #8b5cf6 100%', '#10b981 0%, #34d399 100%', '#f59e0b 0%, #fbbf24 100%', '#ef4444 0%, #f87171 100%', '#3b82f6 0%, #60a5fa 100%', '#8b5cf6 0%, #a78bfa 100%'];
                $badgeColors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6'];
            @endphp
            @foreach($templates as $index => $template)
            <div class="col-6 col-md-3 mb-3">
                <div class="card shadow-sm templates-page-card h-100">
                    <a href="@auth{{ route('design.templates.show', $template['id']) }}@else{{ allow_registration() ? route('register') : route('login') }}@endauth" class="text-decoration-none d-block text-dark">
                        <div class="templates-page-card-thumb" @if(empty($template['thumbnail_url'])) style="background: linear-gradient(135deg, {{ $gradients[$index % count($gradients)] }});" @endif>
                            @if(!empty($template['thumbnail_url']))
                                <img src="{{ $template['thumbnail_url'] }}" alt="{{ $template['name'] }}" loading="lazy">
                            @else
                                <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.1); border-radius: 50%; filter: blur(30px);"></div>
                                <i class="fas fa-file-alt text-white" style="font-size: 2.5rem; position: relative; z-index: 1;"></i>
                            @endif
                        </div>
                    </a>
                    <div class="card-body p-3">
                        @if(!empty($template['category']))
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge px-2 py-1" style="background: rgba(99, 102, 241, 0.1); color: {{ $badgeColors[$index % count($badgeColors)] }}; font-size: 0.65rem; font-weight: 600; border-radius: 4px;">{{ ucfirst(str_replace('-', ' ', $template['category'])) }}</span>
                        </div>
                        @endif
                        <h5 class="card-title mb-1" style="font-weight: 700; font-size: 0.95rem; color: #1e293b; line-height: 1.3;">{{ $template['name'] }}</h5>
                        @if(!empty($template['short_description']))
                        <p class="text-muted mb-2" style="line-height: 1.4; font-size: 0.75rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ Str::limit($template['short_description'], 80) }}</p>
                        @endif
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            @if(isset($template['page_count']) && $template['page_count'])
                            <small class="text-muted" style="font-size: 0.7rem;"><i class="fas fa-file me-1"></i>{{ $template['page_count'] }} {{ Str::plural('page', $template['page_count']) }}</small>
                            @endif
                            @if(isset($template['price']) && $template['price'] > 0)
                            <small style="font-weight: 600; color: #6366f1;">{{ \App\Models\Setting::get('currency_symbol') ?? '$' }}{{ number_format($template['price'], 2) }}</small>
                            @else
                            <small style="font-weight: 600; color: #10b981;">Free</small>
                            @endif
                        </div>
                        <div class="templates-page-actions">
                            <button type="button" class="templates-page-fav-btn" data-template-id="{{ $template['id'] }}" onclick="toggleTemplatesPageFavorite(event, {{ $template['id'] }})" title="Save template" aria-label="Add to favorites">
                                <i class="far fa-heart"></i>
                            </button>
                            <a href="@auth{{ route('design.templates.show', $template['id']) }}@else{{ allow_registration() ? route('register') : route('login') }}@endauth" class="btn templates-page-use-btn" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none; padding: 0.5rem 0.75rem; font-weight: 600; font-size: 0.8rem; border-radius: 8px; transition: all 0.3s; box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(99, 102, 241, 0.3)'">
                                <i class="fas fa-rocket"></i><span>Use</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
                @else
                <div class="text-center py-5">
                    <div style="width: 80px; height: 80px; margin: 0 auto 1.25rem; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-layer-group" style="font-size: 2.5rem; color: #94a3b8;"></i>
                    </div>
                    <h4 style="color: #334155; margin-bottom: 0.5rem; font-weight: 600;">No Templates Yet</h4>
                    <p style="color: #64748b; font-size: 0.9375rem;">Check back soon for professionally designed templates.</p>
                </div>
                @endif
            </div>

            <!-- Right Sidebar - Filters -->
            <aside class="templates-sidebar" id="templatesSidebar">
                <div class="templates-sidebar-mobile-header">
                    <span style="font-weight: 600; font-size: 1rem; color: #1e293b;">Filters</span>
                    <button type="button" onclick="toggleTemplatesFilters()" style="background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; padding: 0.25rem;" aria-label="Close filters"><i class="fas fa-times"></i></button>
                </div>
                <div class="templates-filter-panel">
                    <div class="templates-filter-section">
                        <div class="templates-filter-title"><i class="fas fa-search"></i> Search</div>
                        <form method="GET" action="{{ route('templates') }}" class="templates-filter-search">
                            <i class="fas fa-search"></i>
                            <input type="text" name="q" placeholder="Search templates..." value="{{ $filters['q'] ?? '' }}">
                            @foreach(request()->only(['category','price','pages','sort']) as $k => $v)
                                @if($v && $v !== 'all')
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endif
                            @endforeach
                        </form>
                    </div>
                    <div class="templates-filter-section">
                        <div class="templates-filter-title"><i class="fas fa-dollar-sign"></i> Price</div>
                        <a href="{{ $filterUrl(['price' => 'all']) }}" class="templates-filter-option {{ ($filters['price'] ?? 'all') === 'all' ? 'active' : '' }}">All</a>
                        <a href="{{ $filterUrl(['price' => 'free']) }}" class="templates-filter-option {{ ($filters['price'] ?? '') === 'free' ? 'active' : '' }}">Free</a>
                        <a href="{{ $filterUrl(['price' => 'paid']) }}" class="templates-filter-option {{ ($filters['price'] ?? '') === 'paid' ? 'active' : '' }}">Paid</a>
                    </div>
                    <div class="templates-filter-section">
                        <div class="templates-filter-title"><i class="fas fa-file-alt"></i> Pages</div>
                        <select class="templates-filter-select" onchange="window.location.href=this.value">
                            <option value="{{ $filterUrl(['pages' => 'all']) }}" {{ ($filters['pages'] ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="{{ $filterUrl(['pages' => '1']) }}" {{ ($filters['pages'] ?? '') === '1' ? 'selected' : '' }}>1 page</option>
                            <option value="{{ $filterUrl(['pages' => '2-5']) }}" {{ ($filters['pages'] ?? '') === '2-5' ? 'selected' : '' }}>2–5 pages</option>
                            <option value="{{ $filterUrl(['pages' => '6-10']) }}" {{ ($filters['pages'] ?? '') === '6-10' ? 'selected' : '' }}>6–10 pages</option>
                            <option value="{{ $filterUrl(['pages' => '11+']) }}" {{ ($filters['pages'] ?? '') === '11+' ? 'selected' : '' }}>11+ pages</option>
                        </select>
                    </div>
                    <div class="templates-filter-section">
                        <div class="templates-filter-title"><i class="fas fa-sort"></i> Sort By</div>
                        <select class="templates-filter-select" onchange="window.location.href=this.value">
                            <option value="{{ $filterUrl(['sort' => 'newest']) }}" {{ ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="{{ $filterUrl(['sort' => 'oldest']) }}" {{ ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="{{ $filterUrl(['sort' => 'price-low']) }}" {{ ($filters['sort'] ?? '') === 'price-low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="{{ $filterUrl(['sort' => 'price-high']) }}" {{ ($filters['sort'] ?? '') === 'price-high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="{{ $filterUrl(['sort' => 'name']) }}" {{ ($filters['sort'] ?? '') === 'name' ? 'selected' : '' }}>Name A–Z</option>
                        </select>
                    </div>
                    <div class="templates-filter-section">
                        <a href="{{ route('templates') }}" class="templates-filter-reset" style="display: block; text-align: center; text-decoration: none;">
                            <i class="fas fa-undo me-1"></i>Reset All Filters
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<div id="templatesFiltersOverlay" class="templates-filters-overlay" onclick="toggleTemplatesFilters()"></div>

@push('scripts')
<script>
function toggleTemplatesFilters() {
    const sidebar = document.getElementById('templatesSidebar');
    const overlay = document.getElementById('templatesFiltersOverlay');
    if (!sidebar || !overlay) return;
    const isOpen = sidebar.classList.contains('mobile-open');
    sidebar.classList.toggle('mobile-open', !isOpen);
    overlay.style.display = isOpen ? 'none' : 'block';
    document.body.style.overflow = isOpen ? '' : 'hidden';
}

function toggleTemplatesPageFavorite(e, templateId) {
    e.preventDefault();
    e.stopPropagation();
    var btn = e.currentTarget;
    var icon = btn.querySelector('i');
    var storageKey = 'template_favorites';
    var isFavorited = btn.classList.contains('favorited');
    if (isFavorited) {
        btn.classList.remove('favorited');
        if (icon) { icon.classList.remove('fas'); icon.classList.add('far'); }
        try {
            var favs = JSON.parse(localStorage.getItem(storageKey) || '[]');
            localStorage.setItem(storageKey, JSON.stringify(favs.filter(function (id) { return Number(id) !== Number(templateId); })));
        } catch (err) {}
    } else {
        btn.classList.add('favorited');
        if (icon) { icon.classList.remove('far'); icon.classList.add('fas'); }
        try {
            var favs2 = JSON.parse(localStorage.getItem(storageKey) || '[]');
            if (!favs2.some(function (id) { return Number(id) === Number(templateId); })) favs2.push(Number(templateId));
            localStorage.setItem(storageKey, JSON.stringify(favs2));
        } catch (err2) {}
    }
}

document.addEventListener('DOMContentLoaded', function () {
    try {
        var favs = JSON.parse(localStorage.getItem('template_favorites') || '[]');
        document.querySelectorAll('.templates-page-fav-btn[data-template-id]').forEach(function (btn) {
            var id = parseInt(btn.getAttribute('data-template-id'), 10);
            if (favs.some(function (x) { return Number(x) === id; })) {
                btn.classList.add('favorited');
                var icon = btn.querySelector('i');
                if (icon) { icon.classList.remove('far'); icon.classList.add('fas'); }
            }
        });
    } catch (e) {}
});
</script>
@endpush

<!-- CTA Section -->
<section class="py-5" style="background: white; padding: 4rem 0 !important;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%); border-radius: 24px; padding: 3rem 2rem; border: 1px solid rgba(99, 102, 241, 0.1);">
                    <h2 class="mb-3" style="font-size: 2.5rem; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">Need a Custom Template?</h2>
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.7; max-width: 600px; margin: 0 auto;">
                        Contact us to discuss custom template designs tailored to your specific needs and branding requirements.
                    </p>
                    <div class="d-flex gap-3 flex-wrap justify-content-center align-items-center">
                        <a href="{{ route('contact') }}" class="btn shadow-lg" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none; padding: 1rem 2.5rem; font-weight: 700; font-size: 1.05rem; border-radius: 12px; transition: all 0.3s; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(99, 102, 241, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(99, 102, 241, 0.3)'">
                            <i class="fas fa-envelope me-2"></i>Contact Us
                        </a>
                        <a href="{{ allow_registration() ? route('register') : route('login') }}" class="btn" style="background: white; color: #6366f1; border: 2px solid #e2e8f0; padding: 1rem 2.5rem; font-weight: 600; font-size: 1.05rem; border-radius: 12px; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.05);" onmouseover="this.style.borderColor='#6366f1'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.15)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)'">
                            <i class="fas fa-rocket me-2"></i>Get Started
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if(isset($slides) && $slides->isNotEmpty())
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.querySelector('.templates-landing-slider');
    if (el && typeof Swiper !== 'undefined') {
        new Swiper('.templates-landing-slider', {
            loop: true,
            pagination: { el: '.templates-landing-slider .swiper-pagination' },
            autoplay: { delay: 4000 }
        });
    }
});
</script>
@endpush
@endif
@endsection
