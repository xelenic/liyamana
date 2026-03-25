@extends('layouts.admin')

@section('title', 'Manage: ' . $template->name)
@section('page-title', 'Template Details')

@push('styles')
<style>
    .manage-page { padding: 0.5rem 0 1.5rem; }
    .manage-breadcrumb { font-size: 0.8rem; color: #64748b; margin-bottom: 0.5rem; }
    .manage-breadcrumb a { color: #64748b; text-decoration: none; }
    .manage-breadcrumb a:hover { color: var(--primary-color, #6366f1); }
    .manage-card { background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 1rem; overflow: hidden; }
    .manage-card-body { padding: 1rem 1.25rem; }
    .manage-thumb-wrap { aspect-ratio: 16/10; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .manage-thumb-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .manage-thumb-wrap .no-thumb { color: #cbd5e1; font-size: 2rem; }
    .manage-detail-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; padding: 0.35rem 0; font-size: 0.85rem; border-bottom: 1px solid #e2e8f0; }
    .manage-detail-row:last-child { border-bottom: none; }
    .manage-detail-row .label { color: #64748b; flex-shrink: 0; }
    .manage-detail-row .value { color: #1e293b; font-weight: 500; text-align: right; }
    .manage-section-title { font-size: 0.7rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.5rem; }
    .manage-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1rem; }
    .manage-stat { text-align: center; padding: 0.75rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
    .manage-stat-value { font-size: 1.25rem; font-weight: 700; color: var(--primary-color, #6366f1); display: block; }
    .manage-stat-label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 0.25rem; }
    .manage-chart-wrap { height: 200px; margin-top: 0.5rem; position: relative; }
    .manage-chart-wrap canvas { max-height: 200px; }
    .manage-tabs .nav-link { font-size: 0.8rem; font-weight: 600; color: #64748b; padding: 0.5rem 0.75rem; border: none; border-bottom: 2px solid transparent; background: none; }
    .manage-tabs .nav-link:hover { color: var(--primary-color, #6366f1); }
    .manage-tabs .nav-link.active { color: var(--primary-color, #6366f1); border-bottom-color: var(--primary-color, #6366f1); background: rgba(99, 102, 241, 0.06); }
    .manage-tab-content { padding-top: 1rem; }
    .manage-price { color: #059669; font-weight: 600; }
    .manage-price.free { color: var(--primary-color, #6366f1); }
    @media (max-width: 576px) { .manage-stats { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="my-4 manage-page">
    <nav class="manage-breadcrumb">
        <a href="{{ route('admin.templates') }}"><i class="fas fa-arrow-left me-1"></i>Templates Management</a>
        <span class="mx-1">/</span>
        <span>Manage</span>
    </nav>

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="mb-0" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">{{ $template->name }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('design.templates.show', $template->id) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                <i class="fas fa-external-link-alt me-1"></i>View Public Page
            </a>
            <a href="{{ route('admin.templates') }}" class="btn btn-outline-secondary btn-sm">Back to list</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-3">
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
                    <div class="mb-2">
                        <span class="badge bg-info me-1">{{ ucfirst($template->category) }}</span>
                        @if($template->is_featured)
                            <span class="badge bg-warning text-dark">Featured</span>
                        @endif
                    </div>
                    <div class="manage-detail-row">
                        <span class="label">Created by</span>
                        <span class="value">{{ $template->creator->name ?? 'System' }}</span>
                    </div>
                    <div class="manage-detail-row">
                        <span class="label">Template price</span>
                        <span class="value {{ $template->price == 0 ? 'manage-price free' : 'manage-price' }}">
                            @if($template->price === null)—@elseif($template->price == 0)Free@else{{ format_price($template->price) }}@endif
                        </span>
                    </div>
                    <div class="manage-detail-row">
                        <span class="label">Pages</span>
                        <span class="value">{{ $template->page_count ?? 0 }} {{ Str::plural('page', $template->page_count ?? 0) }}</span>
                    </div>
                    <div class="manage-detail-row">
                        <span class="label">Created</span>
                        <span class="value">{{ $template->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($template->short_description || $template->description)
                        <p class="small text-muted mt-2 mb-0">{{ Str::limit($template->short_description ?: $template->description, 120) }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="manage-card">
                <div class="manage-card-body">
                    <ul class="nav manage-tabs" id="manageTemplateTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-panel" type="button" role="tab">Overview</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="revenue-tab" data-bs-toggle="tab" data-bs-target="#revenue-panel" type="button" role="tab">Revenue</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-panel" type="button" role="tab">Template Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="products-tab" data-bs-toggle="tab" data-bs-target="#products-panel" type="button" role="tab">Assigned Products</button>
                        </li>
                    </ul>
                    <div class="tab-content manage-tab-content" id="manageTemplateTabContent">
                        <div class="tab-pane fade show active" id="overview-panel" role="tabpanel">
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
                                    <span class="manage-stat-label">Avg. Rating</span>
                                </div>
                            </div>
                            <div class="manage-section-title">Orders & reviews (last 30 days)</div>
                            <div class="manage-chart-wrap">
                                <canvas id="overviewChart" height="200"></canvas>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="revenue-panel" role="tabpanel">
                            <div class="manage-stats mb-3">
                                <div class="manage-stat">
                                    <span class="manage-stat-value">{{ format_price($totalRevenue ?? 0) }}</span>
                                    <span class="manage-stat-label">Total revenue</span>
                                </div>
                                <div class="manage-stat">
                                    <span class="manage-stat-value">{{ $ordersCount }}</span>
                                    <span class="manage-stat-label">Total orders</span>
                                </div>
                            </div>
                            <div class="manage-section-title">Revenue (last 30 days)</div>
                            <div class="manage-chart-wrap">
                                <canvas id="revenueChart" height="200"></canvas>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="details-panel" role="tabpanel">
                            <div class="manage-section-title">Paper & format</div>
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
                            @endif
                            @if($template->type)
                                <div class="manage-detail-row">
                                    <span class="label">Type</span>
                                    <span class="value">{{ ucfirst($template->type) }}</span>
                                </div>
                            @endif

                            <div class="manage-section-title mt-3">Pricing</div>
                            <div class="manage-detail-row">
                                <span class="label">Template price</span>
                                <span class="value {{ $template->price == 0 ? 'manage-price free' : 'manage-price' }}">
                                    @if($template->price === null)—@elseif($template->price == 0)Free@else{{ format_price($template->price) }}@endif
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
                            @if(isset($template->cost) && (float) $template->cost > 0)
                                <div class="manage-detail-row">
                                    <span class="label">Cost</span>
                                    <span class="value">{{ format_price($template->cost) }}</span>
                                </div>
                            @endif

                            @if($template->description || $template->product_description || $template->short_description)
                                <div class="manage-section-title mt-3">Description</div>
                                <p class="small text-muted mb-0" style="white-space: pre-wrap;">{{ $template->short_description ?: $template->description ?: $template->product_description ?? '—' }}</p>
                            @endif

                            @if($template->tags && is_array($template->tags) && count($template->tags) > 0)
                                <div class="manage-section-title mt-3">Tags</div>
                                <p class="small mb-0">
                                    @foreach($template->tags as $tag)
                                        <span class="badge bg-light text-dark border me-1">{{ $tag }}</span>
                                    @endforeach
                                </p>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="products-panel" role="tabpanel">
                            <div class="manage-section-title">Products assigned to this template</div>
                            @if(isset($assignedProducts) && $assignedProducts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 3rem;"></th>
                                                <th>Name</th>
                                                <th>SKU</th>
                                                <th class="text-end">Product price</th>
                                                <th class="text-end">Template + product</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $templatePrice = (float) ($template->price ?? 0); @endphp
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
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted small mb-0">No products assigned to this template.</p>
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
    if (ctx) {
        var labels = @json($overviewChartLabels ?? []);
        var ordersData = @json($overviewChartOrders ?? []);
        var reviewsData = @json($overviewChartReviews ?? []);
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Orders', data: ordersData, borderColor: '#6366f1', backgroundColor: 'rgba(99, 102, 241, 0.08)', fill: true, tension: 0.3, pointRadius: 2, pointHoverRadius: 4 },
                    { label: 'Reviews', data: reviewsData, borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.08)', fill: true, tension: 0.3, pointRadius: 2, pointHoverRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'top' } },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } }
                }
            }
        });
    }
    var revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        var revenueLabels = @json($revenueChartLabels ?? []);
        var revenueData = @json($revenueChartData ?? []);
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{ label: 'Revenue', data: revenueData, borderColor: '#059669', backgroundColor: 'rgba(5, 150, 105, 0.12)', fill: true, tension: 0.3, pointRadius: 2, pointHoverRadius: 4 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'top' } },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
