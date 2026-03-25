@extends('layouts.admin')

@section('title', 'Record purchase')
@section('page-title', 'Record purchase')

@section('content')
<div class="my-4">
    <div class="mb-4">
        <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
            <i class="fas fa-cart-plus me-2 text-primary"></i>Record purchase
        </h2>
        <p class="text-muted mb-0">Add one or more lines. Saving applies stock immediately to the selected items.</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.stock.purchases.store') }}" id="purchaseForm">
                @csrf

                @if($suppliers->isEmpty())
                <div class="alert alert-warning">
                    <strong>No suppliers yet.</strong> <a href="{{ route('admin.suppliers.create') }}">Create a supplier</a> before recording a purchase.
                </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label for="purchased_at" class="form-label">Purchase date <span class="text-danger">*</span></label>
                        <input type="date" name="purchased_at" id="purchased_at" class="form-control @error('purchased_at') is-invalid @enderror"
                               value="{{ old('purchased_at', now()->format('Y-m-d')) }}" required>
                        @error('purchased_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="payment_method" class="form-label">Payment <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            @foreach(\App\Models\StockPurchase::paymentMethodOptions() as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_method', \App\Models\StockPurchase::PAYMENT_CASH) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2 align-items-start flex-wrap">
                            <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required {{ $suppliers->isEmpty() ? 'disabled' : '' }}>
                                <option value="">Select supplier…</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" @selected(old('supplier_id') == $sup->id)>
                                        {{ $sup->name }}@if($sup->code) ({{ $sup->code }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <a href="{{ route('admin.suppliers.create') }}" class="btn btn-outline-secondary btn-sm text-nowrap" target="_blank" rel="noopener">Manage suppliers</a>
                        </div>
                        @error('supplier_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Optional">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <h3 class="h6 fw-bold mb-3">Lines</h3>
                @error('lines')<div class="alert alert-danger">{{ $message }}</div>@enderror

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th style="min-width: 160px;">Type</th>
                                <th style="min-width: 220px;">Item</th>
                                <th style="width: 120px;">Quantity</th>
                                <th style="width: 120px;">Unit cost</th>
                                <th style="width: 110px; text-align: right;">Line total</th>
                                <th style="width: 56px;"></th>
                            </tr>
                        </thead>
                        <tbody id="purchaseLines"></tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addPurchaseLine">
                    <i class="fas fa-plus me-1"></i>Add line
                </button>

                <div class="card border mb-4" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                    <div class="card-body">
                        <h3 class="h6 fw-bold mb-3"><i class="fas fa-calculator me-2 text-primary"></i>Totals</h3>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3 col-6">
                                <label class="form-label small text-muted mb-1">Line subtotal</label>
                                <div class="form-control-plaintext fw-bold fs-5" id="display_subtotal" style="min-height: 2.25rem;">0.00</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label for="purchase_discount" class="form-label">Discount</label>
                                <input type="number" name="discount" id="purchase_discount" class="form-control @error('discount') is-invalid @enderror"
                                       min="0" step="0.0001" value="{{ old('discount', '0') }}">
                                @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3 col-6">
                                <label for="purchase_deduction" class="form-label">Deduction</label>
                                <input type="number" name="deduction" id="purchase_deduction" class="form-control @error('deduction') is-invalid @enderror"
                                       min="0" step="0.0001" value="{{ old('deduction', '0') }}">
                                @error('deduction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3 col-6">
                                <label for="purchase_additional_charges" class="form-label">Additional charges</label>
                                <input type="number" name="additional_charges" id="purchase_additional_charges" class="form-control @error('additional_charges') is-invalid @enderror"
                                       min="0" step="0.0001" value="{{ old('additional_charges', '0') }}">
                                @error('additional_charges')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 pt-2 border-top">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <span class="text-muted small">Formula: subtotal − discount − deduction + additional charges (minimum 0)</span>
                                    <div>
                                        <span class="text-muted me-2">Total cost</span>
                                        <span class="fw-bold fs-4 text-primary" id="display_total">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" {{ $suppliers->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-save me-1"></i>Save &amp; update stock
                    </button>
                    <a href="{{ route('admin.stock.purchases.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $productOptions = $products->map(fn ($p) => ['id' => $p->id, 'label' => $p->name])->values();
    $sheetOptions = $sheetTypes->map(fn ($s) => ['id' => $s->id, 'label' => $s->name.' ('.$s->slug.')'])->values();
    $envelopeOptions = $envelopeTypes->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.' ('.$e->slug.')'])->values();
@endphp
<script>
(function() {
    const catalog = {
        product: @json($productOptions),
        sheet_type: @json($sheetOptions),
        envelope_type: @json($envelopeOptions),
    };

    const tbody = document.getElementById('purchaseLines');
    const addBtn = document.getElementById('addPurchaseLine');

    function parseNum(v) {
        if (v === '' || v === null || v === undefined) return 0;
        const n = parseFloat(String(v).replace(/,/g, ''));
        return isNaN(n) ? 0 : n;
    }

    function formatMoney(n) {
        return (Math.round(n * 10000) / 10000).toFixed(2);
    }

    function recalcTotals() {
        let subtotal = 0;
        tbody.querySelectorAll('tr.purchase-line').forEach(function(tr) {
            const qty = parseNum(tr.querySelector('.line-qty').value);
            const rawCost = tr.querySelector('.line-cost').value;
            const el = tr.querySelector('.line-total');
            if (rawCost === '' || rawCost === null) {
                el.textContent = '—';
            } else {
                const costNum = parseNum(rawCost);
                if (costNum < 0) {
                    el.textContent = '—';
                } else {
                    const lineTot = qty * costNum;
                    el.textContent = formatMoney(lineTot);
                    subtotal += lineTot;
                }
            }
        });
        const discount = parseNum(document.getElementById('purchase_discount').value);
        const deduction = parseNum(document.getElementById('purchase_deduction').value);
        const extra = parseNum(document.getElementById('purchase_additional_charges').value);
        document.getElementById('display_subtotal').textContent = formatMoney(subtotal);
        const total = Math.max(0, subtotal - discount - deduction + extra);
        document.getElementById('display_total').textContent = formatMoney(total);
    }

    function fillItemSelect(selectEl, itemType) {
        selectEl.innerHTML = '';
        const items = catalog[itemType] || [];
        if (items.length === 0) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'No items — add in catalog first';
            selectEl.appendChild(opt);
            return;
        }
        items.forEach(function(row) {
            const opt = document.createElement('option');
            opt.value = String(row.id);
            opt.textContent = row.label;
            selectEl.appendChild(opt);
        });
    }

    function bindRow(tr) {
        const typeSel = tr.querySelector('.line-type');
        const itemSel = tr.querySelector('.line-item');
        typeSel.addEventListener('change', function() {
            fillItemSelect(itemSel, typeSel.value);
        });
        tr.querySelector('.remove-line').addEventListener('click', function() {
            if (tbody.querySelectorAll('tr.purchase-line').length <= 1) return;
            tr.remove();
            reindexLines();
        });
        tr.querySelectorAll('.line-qty, .line-cost').forEach(function(inp) {
            inp.addEventListener('input', recalcTotals);
        });
    }

    function reindexLines() {
        const rows = tbody.querySelectorAll('tr.purchase-line');
        rows.forEach(function(tr, idx) {
            tr.querySelector('.line-type').name = 'lines[' + idx + '][item_type]';
            tr.querySelector('.line-item').name = 'lines[' + idx + '][item_id]';
            tr.querySelector('.line-qty').name = 'lines[' + idx + '][quantity]';
            tr.querySelector('.line-cost').name = 'lines[' + idx + '][unit_cost]';
        });
        recalcTotals();
    }

    function addRow() {
        const idx = tbody.querySelectorAll('tr.purchase-line').length;
        const tr = document.createElement('tr');
        tr.className = 'purchase-line';
        tr.innerHTML =
            '<td><select class="form-select line-type" name="lines[' + idx + '][item_type]" required>' +
            '<option value="product">Product</option>' +
            '<option value="sheet_type">Sheet type</option>' +
            '<option value="envelope_type">Envelope type</option>' +
            '</select></td>' +
            '<td><select class="form-select line-item" name="lines[' + idx + '][item_id]" required></select></td>' +
            '<td><input type="number" class="form-control line-qty" name="lines[' + idx + '][quantity]" min="1" value="1" required></td>' +
            '<td><input type="number" class="form-control line-cost" name="lines[' + idx + '][unit_cost]" min="0" step="0.0001" placeholder="Optional"></td>' +
            '<td class="text-end text-muted small line-total align-middle">—</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove"><i class="fas fa-times"></i></button></td>';
        tbody.appendChild(tr);
        const typeSel = tr.querySelector('.line-type');
        fillItemSelect(tr.querySelector('.line-item'), typeSel.value);
        bindRow(tr);
        reindexLines();
    }

    addBtn.addEventListener('click', function() { addRow(); });

    ['purchase_discount', 'purchase_deduction', 'purchase_additional_charges'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', recalcTotals);
    });

    if (tbody.querySelectorAll('tr.purchase-line').length === 0) {
        addRow();
    } else {
        recalcTotals();
    }
})();
</script>
@endsection
