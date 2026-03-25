@extends('layouts.app')

@section('title', 'Manage: ' . $template->name)
@section('page-title', 'Manage Template')

@push('styles')
<style>
    :root {
        --mp-primary: #6366f1;
        --mp-secondary: #8b5cf6;
        --mp-bg: #f8fafc;
        --mp-text: #1e293b;
        --mp-muted: #64748b;
        --mp-border: #e2e8f0;
    }
    .manage-page { padding: 0.5rem 0 1.5rem; }
    .manage-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        font-size: 0.75rem;
        color: var(--mp-muted);
    }
    .manage-breadcrumb a {
        color: var(--mp-muted);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .manage-breadcrumb a:hover { color: var(--mp-primary); }
    .manage-breadcrumb span { color: #94a3b8; }
    .manage-page-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--mp-text);
        margin: 0 0 0.125rem 0;
        letter-spacing: -0.02em;
    }
    .manage-page-sub {
        font-size: 0.6875rem;
        color: var(--mp-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .manage-card {
        background: #fff;
        border-radius: 10px;
        border: 1px solid var(--mp-border);
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        overflow: hidden;
        margin-bottom: 0.75rem;
    }
    .manage-card-body { padding: 0.875rem 1rem; }
    .manage-thumb-wrap {
        aspect-ratio: 16/10;
        background: var(--mp-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .manage-thumb-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .manage-thumb-wrap .no-thumb {
        color: #cbd5e1;
        font-size: 2rem;
    }
    .manage-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 0.75rem;
        margin-bottom: 0.5rem;
        font-size: 0.6875rem;
        color: var(--mp-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .manage-meta span { display: inline-flex; align-items: center; gap: 0.25rem; }
    .manage-meta i { font-size: 0.625rem; color: var(--mp-primary); }
    .manage-price {
        font-size: 0.875rem;
        font-weight: 700;
        color: #059669;
    }
    .manage-price.free { color: var(--mp-primary); }
    .manage-desc {
        font-size: 0.75rem;
        color: #475569;
        line-height: 1.45;
        margin-top: 0.375rem;
    }
    .manage-section-title {
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
    .manage-section-title i { font-size: 0.6875rem; color: var(--mp-primary); }
    .manage-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        margin-bottom: 0.875rem;
    }
    .manage-stat {
        text-align: center;
        padding: 0.5rem 0.375rem;
        background: var(--mp-bg);
        border-radius: 6px;
        border: 1px solid var(--mp-border);
    }
    .manage-stat-value {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--mp-primary);
        display: block;
        line-height: 1.2;
    }
    .manage-stat-label {
        font-size: 0.5625rem;
        color: var(--mp-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 0.125rem;
    }
    .manage-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
    }
    .manage-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.45rem 0.75rem;
        border-radius: 6px;
    }
    .manage-actions .btn-primary {
        background: linear-gradient(135deg, var(--mp-primary) 0%, var(--mp-secondary) 100%);
        border: none;
    }
    .manage-actions .btn-outline-primary {
        border-width: 1px;
    }
    .manage-actions .btn-outline-secondary {
        font-size: 0.6875rem;
        padding: 0.4rem 0.65rem;
    }
    .manage-detail-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.35rem 0;
        font-size: 0.75rem;
        border-bottom: 1px solid var(--mp-border);
    }
    .manage-detail-row:last-child { border-bottom: none; }
    .manage-detail-row .label { color: var(--mp-muted); flex-shrink: 0; }
    .manage-detail-row .value { color: var(--mp-text); font-weight: 500; text-align: right; }
    .manage-detail-section { margin-bottom: 0.75rem; }
    .manage-detail-section:last-child { margin-bottom: 0; }
    .manage-desc-full { font-size: 0.75rem; color: #475569; line-height: 1.5; margin: 0; white-space: pre-wrap; }
    .manage-sheet-list { font-size: 0.6875rem; color: var(--mp-muted); margin-top: 0.25rem; }
    .manage-sheet-list span { display: inline-block; margin-right: 0.5rem; margin-bottom: 0.125rem; }
    .manage-chart-wrap {
        height: 180px;
        margin-top: 0.5rem;
        position: relative;
    }
    .manage-chart-wrap canvas { max-height: 180px; }
    .manage-tabs .nav-link {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--mp-muted);
        padding: 0.5rem 0.75rem;
        border: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        background: none;
    }
    .manage-tabs .nav-link:hover { color: var(--mp-primary); }
    .manage-tabs .nav-link.active {
        color: var(--mp-primary);
        border-bottom-color: var(--mp-primary);
        background: rgba(99, 102, 241, 0.06);
    }
    .manage-tab-content { padding-top: 0.75rem; }
    .manage-products-table .col-actions { width: 10rem; min-width: 10rem; white-space: nowrap; text-align: right; }
    .manage-products-table .btn-unassign { white-space: nowrap; }
    @media (max-width: 576px) {
        .manage-stats { grid-template-columns: 1fr; }
        .manage-products-table .col-actions { width: auto; min-width: 0; }
    }
</style>
@endpush

@section('content')
<div class="container manage-page">
    <nav class="manage-breadcrumb">
        <a href="{{ route('design.templates.index') }}"><i class="fas fa-arrow-left"></i> My Templates</a>
        <span>/</span>
        <span>Manage</span>
    </nav>
    <h1 class="manage-page-title">{{ $template->name }}</h1>
    <p class="manage-page-sub"><i class="fas fa-globe me-1"></i> Public template</p>

    <div class="row mt-3">
        <div class="col-lg-4 mb-2">
            <div class="manage-card">
                @if($template->thumbnail_url)
                    <div class="manage-thumb-wrap">
                        <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}">
                    </div>
                @else
                    <div class="manage-thumb-wrap">
                        <span class="no-thumb"><i class="fas fa-layer-group"></i></span>
                    </div>
                @endif
                <div class="manage-card-body">
                    <div class="manage-meta">
                        @if($template->category)
                            <span><i class="fas fa-tag"></i>{{ ucfirst($template->category) }}</span>
                        @endif
                        @if($template->page_count)
                            <span><i class="fas fa-file-alt"></i>{{ $template->page_count }} {{ Str::plural('page', $template->page_count) }}</span>
                        @endif
                    </div>
                    <div class="manage-detail-section mb-2">
                        <div class="manage-detail-row">
                            <span class="label">Template price</span>
                            <span class="value {{ $template->price == 0 ? 'manage-price free' : 'manage-price' }}">
                                @if($template->price === null)
                                    —
                                @elseif($template->price == 0)
                                    Free
                                @else
                                    {{ format_price($template->price) }}
                                @endif
                            </span>
                        </div>
                        <div class="manage-detail-row">
                            <span class="label">Cost</span>
                            <span class="value">
                                @if(isset($template->cost) && (float) $template->cost > 0)
                                    {{ format_price($template->cost) }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        @if(isset($assignedProducts) && $assignedProducts->count() > 0)
                            @php
                                $productPrices = $assignedProducts->map(fn($p) => (float) ($p->price ?? 0))->unique()->values();
                                $templatePriceNum = (float) ($template->price ?? 0);
                                $totals = $assignedProducts->map(fn($p) => $templatePriceNum + (float) ($p->price ?? 0))->unique()->values();
                            @endphp
                            <div class="manage-detail-row">
                                <span class="label">Product price</span>
                                <span class="value">
                                    @if($productPrices->count() === 1)
                                        {{ format_price($productPrices->first()) }}
                                    @else
                                        {{ format_price($productPrices->min()) }} – {{ format_price($productPrices->max()) }}
                                    @endif
                                </span>
                            </div>
                            <div class="manage-detail-row">
                                <span class="label">Template + product</span>
                                <span class="value fw-semibold">
                                    @if($totals->count() === 1)
                                        {{ format_price($totals->first()) }}
                                    @else
                                        {{ format_price($totals->min()) }} – {{ format_price($totals->max()) }}
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                    @if($template->short_description || $template->description)
                        <p class="manage-desc mb-0">{{ Str::limit($template->short_description ?: $template->description, 120) }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="manage-card">
                <div class="manage-card-body">
                    <ul class="nav manage-tabs" id="manageTemplateTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-panel" type="button" role="tab" aria-controls="overview-panel" aria-selected="true"><i class="fas fa-th-large me-1"></i>Overview</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="revenue-tab" data-bs-toggle="tab" data-bs-target="#revenue-panel" type="button" role="tab" aria-controls="revenue-panel" aria-selected="false"><i class="fas fa-dollar-sign me-1"></i>Revenue</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="products-tab" data-bs-toggle="tab" data-bs-target="#products-panel" type="button" role="tab" aria-controls="products-panel" aria-selected="false"><i class="fas fa-box me-1"></i>Assigned Products</button>
                        </li>
                    </ul>
                    <div class="tab-content manage-tab-content" id="manageTemplateTabContent">
                        <div class="tab-pane fade show active" id="overview-panel" role="tabpanel" aria-labelledby="overview-tab">
                            <div class="manage-stats">
                                <div class="manage-stat">
                                    <span class="manage-stat-value">{{ $ordersCount }}</span>
                                    <span class="manage-stat-label">Orders</span>
                                </div>
                                <div class="manage-stat">
                                    <span class="manage-stat-value">{{ $reviewsCount }}</span>
                                    <span class="manage-stat-label">Reviews</span>
                                </div>
                                <div class="manage-stat">
                                    <span class="manage-stat-value">{{ number_format($averageRating, 1) }}</span>
                                    <span class="manage-stat-label">Rating</span>
                                </div>
                            </div>
                            <div class="manage-chart-wrap">
                                <canvas id="overviewChart" height="180"></canvas>
                            </div>

                            <div class="manage-section-title"><i class="fas fa-file-alt"></i> Paper & format</div>
                            <div class="manage-detail-section">
                                <div class="manage-detail-row">
                                    <span class="label">Pages</span>
                                    <span class="value">{{ $template->page_count ?? 0 }} {{ Str::plural('page', $template->page_count ?? 0) }}</span>
                                </div>
                                @if(!empty($pageDimensions) && ($pageDimensions['width'] ?? $pageDimensions['height']))
                                    <div class="manage-detail-row">
                                        <span class="label">Size</span>
                                        <span class="value">{{ $pageDimensions['width'] ?? '—' }} × {{ $pageDimensions['height'] ?? '—' }} px</span>
                                    </div>
                                @endif
                                @if(isset($sheetTypes) && $sheetTypes->count() > 0)
                                    <div class="manage-detail-row">
                                        <span class="label">Sheet types</span>
                                        <span class="value">{{ $sheetTypes->count() }} available</span>
                                    </div>
                                    <div class="manage-sheet-list">
                                        @foreach($sheetTypes as $st)
                                            <span>{{ $st->name }} — {{ format_price($st->price_per_sheet ?? 0) }}/sheet</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($template->type)
                                    <div class="manage-detail-row">
                                        <span class="label">Type</span>
                                        <span class="value">{{ ucfirst($template->type) }}</span>
                                    </div>
                                    @if($template->disable_sheet_selection)
                                        <div class="manage-detail-row">
                                            <span class="label">Options</span>
                                            <span class="value">Sheet selection disabled</span>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <div class="manage-section-title"><i class="fas fa-tag"></i> Pricing</div>
                            <div class="manage-detail-section">
                                <div class="manage-detail-row">
                                    <span class="label">Template price</span>
                                    <span class="value {{ $template->price == 0 ? 'manage-price free' : 'manage-price' }}">
                                        @if($template->price === null)
                                            —
                                        @elseif($template->price == 0)
                                            Free
                                        @else
                                            {{ format_price($template->price) }}
                                        @endif
                                    </span>
                                </div>
                                @if($template->licence)
                                    <div class="manage-detail-row">
                                        <span class="label">Licence</span>
                                        <span class="value">{{ ucfirst($template->licence) }}</span>
                                    </div>
                                @endif
                                @if($template->is_product && $template->selling_price !== null)
                                    <div class="manage-detail-row">
                                        <span class="label">Selling price</span>
                                        <span class="value">{{ format_price($template->selling_price) }}</span>
                                    </div>
                                @endif
                                @if($template->is_product && $template->cost !== null)
                                    <div class="manage-detail-row">
                                        <span class="label">Cost</span>
                                        <span class="value">{{ format_price($template->cost) }}</span>
                                    </div>
                                @endif
                            </div>

                            @if($template->description || $template->product_description || $template->short_description)
                                <div class="manage-section-title"><i class="fas fa-align-left"></i> Description</div>
                                <div class="manage-detail-section">
                                    <p class="manage-desc-full">{{ $template->short_description ?: $template->description ?: $template->product_description ?? '—' }}</p>
                                </div>
                            @endif

                            <div class="manage-section-title"><i class="fas fa-bolt"></i> Actions</div>
                            <div class="manage-actions">
                                <a href="{{ route('design.create', ['multi' => 'true', 'template' => $template->id]) }}" class="btn btn-primary">
                                    <i class="fas fa-palette"></i> Edit in Design Tool
                                </a>
                                @if($template->type === 'letter')
                                    <a href="{{ route('design.templates.sendLetter', $template->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-envelope"></i> Quick Use (Send Letter)
                                    </a>
                                @else
                                    <a href="{{ route('design.templates.quickUse', $template->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-bolt"></i> Quick Use
                                    </a>
                                @endif
                                <a href="{{ route('design.templates.show', $template->id) }}" class="btn btn-outline-secondary" target="_blank">
                                    <i class="fas fa-external-link-alt"></i> View Public Page
                                </a>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="revenue-panel" role="tabpanel" aria-labelledby="revenue-tab">
                            <div class="manage-stats mb-3">
                                <div class="manage-stat">
                                    <span class="manage-stat-value">{{ format_price($totalRevenue ?? 0) }}</span>
                                    <span class="manage-stat-label">Total revenue</span>
                                </div>
                                <div class="manage-stat">
                                    <span class="manage-stat-value">{{ $ordersCount }}</span>
                                    <span class="manage-stat-label">Orders</span>
                                </div>
                            </div>
                            <div class="manage-section-title"><i class="fas fa-chart-line"></i> Revenue (last 30 days)</div>
                            <div class="manage-chart-wrap">
                                <canvas id="revenueChart" height="180"></canvas>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="products-panel" role="tabpanel" aria-labelledby="products-tab">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible py-2 small mb-3" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            <div class="manage-section-title"><i class="fas fa-box"></i> Products assigned to this template</div>
                            @if(isset($assignedProducts) && $assignedProducts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-3 manage-products-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 3rem;"></th>
                                                <th>Name</th>
                                                <th>SKU</th>
                                                <th class="text-end">Product price</th>
                                                <th class="text-end">Template + product</th>
                                                <th class="text-end col-actions">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $templatePrice = (float) ($template->price ?? 0);
                                            @endphp
                                            @foreach($assignedProducts as $product)
                                                @php
                                                    $productPrice = (float) ($product->price ?? 0);
                                                    $totalPrice = $templatePrice + $productPrice;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        @if($product->image && \Storage::disk('public')->exists($product->image))
                                                            <img src="{{ \Storage::disk('public')->url($product->image) }}" alt="" class="rounded" style="width: 2.5rem; height: 2.5rem; object-fit: cover;">
                                                        @else
                                                            <span class="text-muted"><i class="fas fa-box-open fa-lg"></i></span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $product->name }}</td>
                                                    <td><code class="small">{{ $product->sku ?? '—' }}</code></td>
                                                    <td class="text-end">{{ format_price($productPrice) }}</td>
                                                    <td class="text-end fw-semibold">{{ format_price($totalPrice) }}</td>
                                                    <td class="text-end col-actions">
                                                        <form action="{{ route('design.templates.products.unassign', [$template->id, $product->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this product from the template?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm btn-unassign" title="Remove this product from the template"><i class="fas fa-times me-1"></i>Unassign</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted small mb-3">No products assigned yet. Use the form below to assign products.</p>
                            @endif
                            @php
                                $availableProducts = isset($allProducts) ? $allProducts->whereNotIn('id', $assignedProducts->pluck('id') ?? []) : collect();
                            @endphp
                            @if($availableProducts->count() > 0)
                                <div class="manage-section-title mt-3"><i class="fas fa-plus"></i> Assign a product</div>
                                <form action="{{ route('design.templates.products.assign', $template->id) }}" method="POST" class="d-flex flex-wrap gap-2 align-items-end">
                                    @csrf
                                    <div class="flex-grow-1" style="min-width: 200px;">
                                        <label class="form-label small mb-1">Product</label>
                                        <select name="product_id" class="form-select form-select-sm" required>
                                            <option value="">Select product…</option>
                                            @foreach($availableProducts as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}{{ $p->sku ? ' (' . $p->sku . ')' : '' }} — {{ format_price($p->price ?? 0) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-link me-1"></i> Assign</button>
                                </form>
                            @else
                                <p class="text-muted small mt-2">All available products are already assigned, or there are no products in the system.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('overviewChart');
    if (!ctx) return;
    var labels = @json($overviewChartLabels ?? []);
    var ordersData = @json($overviewChartOrders ?? []);
    var reviewsData = @json($overviewChartReviews ?? []);
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Orders',
                    data: ordersData,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.08)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                    pointHoverRadius: 4
                },
                {
                    label: 'Reviews',
                    data: reviewsData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                    pointHoverRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { boxWidth: 12, font: { size: 11 } }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { maxTicksLimit: 10, font: { size: 10 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { stepSize: 1, font: { size: 10 } }
                }
            }
        }
    });

    var revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        var revenueLabels = @json($revenueChartLabels ?? []);
        var revenueData = @json($revenueChartData ?? []);
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Revenue',
                    data: revenueData,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.12)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                    pointHoverRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxTicksLimit: 10, font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 10 }, callback: function(v) { return typeof v === 'number' ? (v % 1 === 0 ? v : v.toFixed(2)) : v; } }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
