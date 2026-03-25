@extends('layouts.admin')

@section('title', 'Stock overview')
@section('page-title', 'Stock overview')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-boxes me-2 text-primary"></i>Stock overview
            </h2>
            <p class="text-muted mb-0">On-hand quantities for products, sheet types, and envelope types (updated when you record purchases).</p>
        </div>
        <a href="{{ route('admin.stock.purchases.create') }}" class="btn btn-primary">
            <i class="fas fa-cart-plus me-1"></i>Record purchase
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h3 class="mb-0" style="font-size: 1rem; font-weight: 700; color: #1e293b;"><i class="fas fa-box me-2 text-primary"></i>Products</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 0.75rem 1rem; font-size: 0.85rem;">Name</th>
                            <th style="padding: 0.75rem 1rem; font-size: 0.85rem;">SKU</th>
                            <th style="padding: 0.75rem 1rem; font-size: 0.85rem; text-align: right;">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $p)
                        <tr>
                            <td style="padding: 0.75rem 1rem;">{{ $p->name }}</td>
                            <td style="padding: 0.75rem 1rem;"><code>{{ $p->sku ?: '—' }}</code></td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600;">{{ number_format($p->stock_quantity ?? 0) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">No products</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h3 class="mb-0" style="font-size: 1rem; font-weight: 700; color: #1e293b;"><i class="fas fa-layer-group me-2 text-primary"></i>Sheet types</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 0.75rem 1rem; font-size: 0.85rem;">Name</th>
                            <th style="padding: 0.75rem 1rem; font-size: 0.85rem;">Slug</th>
                            <th style="padding: 0.75rem 1rem; font-size: 0.85rem; text-align: right;">Stock (sheets)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sheetTypes as $s)
                        <tr>
                            <td style="padding: 0.75rem 1rem;">{{ $s->name }}</td>
                            <td style="padding: 0.75rem 1rem;"><code>{{ $s->slug }}</code></td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600;">{{ number_format($s->stock_quantity ?? 0) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">No sheet types</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h3 class="mb-0" style="font-size: 1rem; font-weight: 700; color: #1e293b;"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Envelope types</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 0.75rem 1rem; font-size: 0.85rem;">Name</th>
                            <th style="padding: 0.75rem 1rem; font-size: 0.85rem;">Slug</th>
                            <th style="padding: 0.75rem 1rem; font-size: 0.85rem; text-align: right;">Stock (units)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($envelopeTypes as $e)
                        <tr>
                            <td style="padding: 0.75rem 1rem;">{{ $e->name }}</td>
                            <td style="padding: 0.75rem 1rem;"><code>{{ $e->slug }}</code></td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600;">{{ number_format($e->stock_quantity ?? 0) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">No envelope types</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p class="text-muted small mt-3 mb-0">
        <i class="fas fa-info-circle me-1"></i>Stock increases when you <a href="{{ route('admin.stock.purchases.create') }}">record a purchase</a>. Goods receipts, damages, and order deductions can be added in a later phase.
    </p>
</div>
@endsection
