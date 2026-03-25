@extends('layouts.admin')

@section('title', 'Purchase '.$purchase->reference)
@section('page-title', 'Purchase '.$purchase->reference)

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-file-invoice me-2 text-primary"></i>{{ $purchase->reference }}
            </h2>
            <p class="text-muted mb-0">Purchased {{ $purchase->purchased_at->format('M j, Y') }}</p>
        </div>
        <a href="{{ route('admin.stock.purchases.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to list
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="text-muted small">Supplier</div>
                    <div class="fw-semibold">{{ $purchase->supplier_display_name ?? '—' }}</div>
                    @if($purchase->supplier)
                    <div class="small text-muted mt-1">
                        @if($purchase->supplier->email)<div><i class="fas fa-envelope me-1"></i>{{ $purchase->supplier->email }}</div>@endif
                        @if($purchase->supplier->phone)<div><i class="fas fa-phone me-1"></i>{{ $purchase->supplier->phone }}</div>@endif
                    </div>
                    @endif
                </div>
                <div class="col-md-3 mb-3">
                    <div class="text-muted small">Payment</div>
                    <div class="fw-semibold">{{ $purchase->payment_method_label ?? '—' }}</div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="text-muted small">Recorded by</div>
                    <div class="fw-semibold">{{ $purchase->user?->name ?? '—' }}</div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="text-muted small">Created</div>
                    <div class="fw-semibold">{{ $purchase->created_at->format('M j, Y H:i') }}</div>
                </div>
                @if($purchase->notes)
                <div class="col-12">
                    <div class="text-muted small">Notes</div>
                    <div class="border rounded p-3 bg-light" style="white-space: pre-wrap;">{{ $purchase->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @php
        $displaySubtotal = $purchase->subtotal !== null
            ? (float) $purchase->subtotal
            : (float) $purchase->items->sum(fn ($i) => $i->unit_cost !== null ? $i->quantity * (float) $i->unit_cost : 0);
        $displayDiscount = (float) ($purchase->discount ?? 0);
        $displayDeduction = (float) ($purchase->deduction ?? 0);
        $displayAdditional = (float) ($purchase->additional_charges ?? 0);
        $displayTotal = $purchase->total_cost !== null
            ? (float) $purchase->total_cost
            : max(0, $displaySubtotal - $displayDiscount - $displayDeduction + $displayAdditional);
    @endphp
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h3 class="mb-0" style="font-size: 1rem; font-weight: 700;"><i class="fas fa-calculator me-2 text-primary"></i>Amounts</h3>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-muted small">Line subtotal</div>
                    <div class="fw-semibold">{{ format_price($displaySubtotal) }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">Discount</div>
                    <div class="fw-semibold">{{ format_price($displayDiscount) }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">Deduction</div>
                    <div class="fw-semibold">{{ format_price($displayDeduction) }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">Additional charges</div>
                    <div class="fw-semibold">{{ format_price($displayAdditional) }}</div>
                </div>
                <div class="col-12 pt-2 border-top">
                    <div class="text-muted small">Total cost</div>
                    <div class="fw-bold fs-4" style="color: var(--bs-primary, #6366f1);">{{ format_price($displayTotal) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h3 class="mb-0" style="font-size: 1rem; font-weight: 700;">Line items</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem;">Type</th>
                            <th style="padding: 1rem; font-size: 0.85rem;">Item</th>
                            <th style="padding: 1rem; font-size: 0.85rem; text-align: right;">Quantity</th>
                            <th style="padding: 1rem; font-size: 0.85rem; text-align: right;">Unit cost</th>
                            <th style="padding: 1rem; font-size: 0.85rem; text-align: right;">Line total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                        @php
                            $base = class_basename($item->purchasable_type);
                            $typeLabel = match ($base) {
                                'Product' => 'Product',
                                'SheetType' => 'Sheet type',
                                'EnvelopeType' => 'Envelope',
                                default => $base,
                            };
                            $name = $item->purchasable?->name ?? '(deleted)';
                            $qty = $item->quantity;
                            $unit = $item->unit_cost;
                            $lineTotal = $unit !== null ? (float) $unit * $qty : null;
                        @endphp
                        <tr>
                            <td style="padding: 1rem;">{{ $typeLabel }}</td>
                            <td style="padding: 1rem;">{{ $name }}</td>
                            <td style="padding: 1rem; text-align: right;">{{ number_format($qty) }}</td>
                            <td style="padding: 1rem; text-align: right;">{{ $unit !== null ? format_price((float) $unit) : '—' }}</td>
                            <td style="padding: 1rem; text-align: right;">{{ $lineTotal !== null ? format_price($lineTotal) : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
