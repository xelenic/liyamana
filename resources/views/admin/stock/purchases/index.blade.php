@extends('layouts.admin')

@section('title', 'Stock purchases')
@section('page-title', 'Stock purchases')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-file-invoice me-2 text-primary"></i>Stock purchases
            </h2>
            <p class="text-muted mb-0">Incoming stock recorded with purchase orders. Each entry increases on-hand quantities.</p>
        </div>
        <a href="{{ route('admin.stock.purchases.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Record purchase
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.stock.purchases.index') }}" class="row g-3">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control" placeholder="Search reference, supplier, notes..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Reference</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Date</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Supplier</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Payment</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: right;">Total</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Lines</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Recorded by</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td style="padding: 1rem; font-weight: 600;"><code>{{ $purchase->reference }}</code></td>
                            <td style="padding: 1rem;">{{ $purchase->purchased_at->format('M j, Y') }}</td>
                            <td style="padding: 1rem;">{{ $purchase->supplier_display_name ?? '—' }}</td>
                            <td style="padding: 1rem;">{{ $purchase->payment_method_label ?? '—' }}</td>
                            <td style="padding: 1rem; text-align: right; font-weight: 600;">
                                @if($purchase->total_cost !== null)
                                    {{ format_price((float) $purchase->total_cost) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">{{ $purchase->items_count }}</td>
                            <td style="padding: 1rem; font-size: 0.9rem; color: #64748b;">{{ $purchase->user?->name ?? '—' }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <a href="{{ route('admin.stock.purchases.show', $purchase) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No purchases yet. <a href="{{ route('admin.stock.purchases.create') }}">Record purchase</a></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($purchases->hasPages())
    <div class="mt-3">
        {{ $purchases->links() }}
    </div>
    @endif
</div>
@endsection
