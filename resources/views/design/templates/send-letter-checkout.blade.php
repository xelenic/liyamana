@extends('layouts.app')

@section('title', 'Send Letter - ' . $template->name)

@push('styles')
<style>
    :root {
        --primary-color: #6366f1;
        --secondary-color: #8b5cf6;
        --light-bg: #f8fafc;
        --dark-text: #1e293b;
        --border-color: #e2e8f0;
    }

    .send-letter-container {
        min-height: 100vh;
        background: var(--light-bg);
        padding: 1rem 0;
    }

    .send-letter-sidebar {
        background: white;
        border-radius: 8px;
        padding: 0.85rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        height: fit-content;
        position: sticky;
        top: 1rem;
    }

    .send-letter-sidebar .form-group { margin-bottom: 0.5rem; }
    .send-letter-sidebar .form-label { font-size: 0.75rem !important; margin-bottom: 0.2rem; }
    .send-letter-sidebar .form-control { padding: 0.35rem 0.5rem; font-size: 0.8125rem; }
    .send-letter-sidebar small { font-size: 0.7rem !important; }

    .send-letter-content {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        display: flex;
        flex-direction: column;
        height: calc(100vh - 6rem);
        max-height: 800px;
    }

    .form-group { margin-bottom: 0.5rem; }
    .form-label { font-weight: 600; color: var(--dark-text); margin-bottom: 0.2rem; display: block; font-size: 0.8125rem; }
    .form-control { width: 100%; padding: 0.4rem 0.6rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.8125rem; transition: all 0.2s; }
    .form-control:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }

    .toggle-switch { display: flex; align-items: center; gap: 0.75rem; }
    .toggle-switch input[type="checkbox"] { width: 36px; height: 18px; appearance: none; background: #cbd5e1; border-radius: 12px; position: relative; cursor: pointer; transition: background 0.3s; }
    .toggle-switch input[type="checkbox"]:checked { background: var(--primary-color); }
    .toggle-switch input[type="checkbox"]::before { content: ''; position: absolute; width: 14px; height: 14px; border-radius: 50%; background: white; top: 2px; left: 2px; transition: transform 0.2s; }
    .toggle-switch input[type="checkbox"]:checked::before { transform: translateX(18px); }

    .variable-form-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        margin-bottom: 0.5rem;
        overflow: hidden;
        transition: all 0.2s;
    }
    .variable-form-card:hover { border-color: var(--primary-color); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1); }

    .variable-form-header {
        font-weight: 700; color: var(--dark-text); font-size: 0.875rem;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0.5rem 0.75rem; cursor: pointer; background: var(--light-bg); transition: all 0.2s; user-select: none;
    }
    .variable-form-header:hover { background: #f1f5f9; }

    .variable-form-body { padding: 0.75rem; transition: all 0.2s; overflow: hidden; max-height: 2000px; opacity: 1; }
    .variable-form-card.collapsed .variable-form-body { display: none !important; max-height: 0; padding: 0; opacity: 0; }

    .collapse-icon { transition: transform 0.2s; color: var(--primary-color); font-size: 0.75rem; }
    .variable-form-card.collapsed .collapse-icon { transform: rotate(-90deg); }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white; border: none; padding: 0.45rem 1rem; border-radius: 6px; font-weight: 600; font-size: 0.8125rem; cursor: pointer; transition: all 0.2s;
    }
    .btn-primary:hover { box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3); }
    .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

    .btn-preview { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; padding: 0.35rem 0.65rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; }
    .btn-search { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 0.35rem 0.65rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; }
    .btn-search:hover { box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3); }
    .btn-search:disabled { opacity: 0.6; cursor: not-allowed; }

    .address-search-wrapper { position: relative; }
    .address-search-results {
        position: absolute; top: 100%; left: 0; right: 0; z-index: 100;
        background: white; border: 1px solid var(--border-color); border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        max-height: 200px; overflow-y: auto; margin-top: 2px;
    }
    .address-search-result {
        padding: 0.5rem 0.75rem; cursor: pointer; font-size: 0.8125rem; border-bottom: 1px solid #f1f5f9;
    }
    .address-search-result:hover { background: rgba(99, 102, 241, 0.08); }
    .address-search-result:last-child { border-bottom: none; }

    .address-source-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 1rem; }
    @media (max-width: 480px) { .address-source-cards { grid-template-columns: 1fr; } }
    .address-source-card {
        position: relative;
        display: flex; align-items: flex-start; gap: 0.75rem;
        padding: 1rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .address-source-card:hover { border-color: rgba(99, 102, 241, 0.4); background: rgba(99, 102, 241, 0.03); box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1); }
    .address-source-card.selected { border-color: var(--primary-color); background: linear-gradient(135deg, rgba(99, 102, 241, 0.06) 0%, rgba(139, 92, 246, 0.04) 100%); box-shadow: 0 2px 12px rgba(99, 102, 241, 0.15); }
    .address-source-card.disabled { opacity: 0.6; cursor: not-allowed; pointer-events: none; }
    .address-source-card input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; }
    .address-source-card-icon {
        width: 44px; height: 44px; min-width: 44px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
        color: white;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    }
    .address-source-card.disabled .address-source-card-icon { background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%); }
    .address-source-card.selected .address-source-card-icon { box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4); }
    .address-source-card-body { flex: 1; min-width: 0; }
    .address-source-card-title { font-weight: 700; font-size: 0.875rem; color: var(--dark-text); margin-bottom: 0.2rem; }
    .address-source-card-desc { font-size: 0.75rem; color: #64748b; line-height: 1.35; }
    .address-source-panel { display: none; margin-top: 0.75rem; }
    .address-source-panel.show { display: block; }
    .address-book-select-wrap .form-control { font-size: 0.8125rem; }

    .btn-add-item-manually {
        display: inline-flex; align-items: center;
        padding: 0.5rem 1rem;
        font-size: 0.8125rem; font-weight: 600;
        color: var(--primary-color);
        background: transparent;
        border: 2px dashed rgba(99, 102, 241, 0.5);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-add-item-manually:hover {
        background: rgba(99, 102, 241, 0.08);
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .btn-remove-item {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px;
        padding: 0;
        color: #64748b;
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-remove-item:hover {
        color: #dc2626;
        border-color: #dc2626;
        background: rgba(220, 38, 38, 0.06);
    }

    .tab-container { display: flex; flex-direction: column; height: 100%; min-height: 0; }
    .tab-navigation { display: flex; gap: 0.25rem; border-bottom: 1px solid var(--border-color); margin-bottom: 0.75rem; flex-shrink: 0; }
    .tab-button { padding: 0.4rem 0.75rem; background: transparent; border: none; border-bottom: 2px solid transparent; color: #64748b; font-weight: 600; font-size: 0.8125rem; cursor: pointer; transition: all 0.3s; margin-bottom: -2px; }
    .tab-button:hover { color: var(--primary-color); background: rgba(99, 102, 241, 0.05); }
    .tab-button.active { color: var(--primary-color); border-bottom-color: var(--primary-color); background: rgba(99, 102, 241, 0.05); }
    .tab-content { display: none; flex: 1; min-height: 0; overflow-y: auto; overflow-x: hidden; padding-right: 0.5rem; }
    .tab-content.active { display: block; overflow-y: auto; }
    .tab-content::-webkit-scrollbar { width: 8px; }
    .tab-content::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .tab-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .tab-content::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    .print-details-section { display: flex; flex-direction: column; gap: 1rem; }
    .print-details-card { background: white; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .print-details-card-header { padding: 0.75rem 1rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid var(--border-color); font-weight: 600; font-size: 0.875rem; color: var(--dark-text); display: flex; align-items: center; gap: 0.5rem; }
    .print-details-card-body { padding: 1rem; }
    .print-details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem 1.5rem; }
    .print-details-form-group { margin-bottom: 1rem; }
    .print-details-form-label { font-size: 0.75rem; font-weight: 600; color: var(--dark-text); margin-bottom: 0.35rem; display: block; text-transform: uppercase; letter-spacing: 0.03em; }
    .print-details-select { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.8125rem; background: white; }
    .sheet-type-preview-section { margin-top: 1.25rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; }
    @media (max-width: 768px) { .sheet-type-preview-section { grid-template-columns: 1fr; } }
    .print-details-video-wrapper { border-radius: 10px; overflow: hidden; background: linear-gradient(135deg, #1e293b 0%, #334155 100%); aspect-ratio: 16/10; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .print-details-video-wrapper video { width: 100%; height: 100%; object-fit: cover; }
    .print-details-video-placeholder { padding: 2rem; text-align: center; color: rgba(255,255,255,0.7); font-size: 0.8125rem; }
    .print-details-video-placeholder i { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; opacity: 0.6; }
    .sheet-type-description-card { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid var(--border-color); border-radius: 10px; padding: 1rem; min-height: 120px; }
    .sheet-type-description-card .sheet-type-name { font-weight: 700; color: var(--dark-text); font-size: 0.875rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem; }
    .sheet-type-description-card .sheet-type-name i { color: var(--primary-color); font-size: 0.8rem; }
    .sheet-type-description-card .sheet-type-desc-text { font-size: 0.8125rem; color: #475569; line-height: 1.6; }
    .sheet-type-description-placeholder { color: #94a3b8; font-size: 0.8125rem; font-style: italic; }

    .csv-import-modal { display: none; position: fixed; z-index: 10001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
    .csv-import-modal.show { display: flex; }
    .csv-import-card { background: white; border-radius: 12px; padding: 1.5rem; max-width: 480px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.15); }
    .csv-import-card h4 { margin: 0 0 0.5rem 0; font-size: 1.1rem; color: var(--dark-text); }
    .csv-import-card p { margin: 0 0 1rem 0; font-size: 0.875rem; color: #64748b; }
    .csv-import-actions { display: flex; flex-direction: column; gap: 0.5rem; }
    .csv-import-btn { display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.65rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; border: 1px solid var(--border-color); background: white; color: var(--dark-text); transition: all 0.2s; }
    .csv-import-btn:hover { background: #f8fafc; border-color: var(--primary-color); color: var(--primary-color); }
    .csv-import-btn.btn-primary-csv { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white; border: none; }
    .csv-import-btn.btn-primary-csv:hover { box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4); }
    .csv-import-btn i { font-size: 1rem; }
    #csvFileInput { display: none; }

    .preview-modal { display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.9); overflow: auto; }
    .preview-modal-content { background-color: #1e293b; margin: 0; padding: 1.5rem; border-radius: 0; width: 100%; height: 100%; max-width: 100%; display: flex; flex-direction: column; }
    .preview-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.1); flex-wrap: wrap; gap: 0.5rem; }
    .preview-tool-btn { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; }
    .preview-tool-btn:hover { background: rgba(255,255,255,0.2); }
    .preview-zoom-controls { display: flex; align-items: center; gap: 0.35rem; }
    .preview-zoom-label { color: rgba(255,255,255,0.9); font-size: 0.8125rem; min-width: 3.5rem; text-align: center; }
    .preview-canvas-container { background: #f8fafc; border-radius: 8px; padding: 1rem; display: flex; justify-content: center; align-items: center; flex: 1; overflow: auto; min-height: 0; }
    .preview-canvas-wrapper { display: inline-block; flex-shrink: 0; transform-origin: center center; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 8px; background: white; }
    #previewCanvas { display: block; border-radius: 8px; }

    .send-letter-product-card { display: flex; gap: 0.75rem; padding: 0.75rem; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 0.75rem; align-items: flex-start; }
    .send-letter-product-card:last-child { margin-bottom: 0; }
    .send-letter-product-image { width: 80px; height: 80px; min-width: 80px; border-radius: 8px; overflow: hidden; background: white; display: flex; align-items: center; justify-content: center; }
    .send-letter-product-image img { width: 100%; height: 100%; object-fit: cover; }
    .send-letter-product-image .no-img { color: #cbd5e1; font-size: 1.5rem; }
    .send-letter-product-details { flex: 1; min-width: 0; }
    .send-letter-product-name { font-weight: 700; font-size: 0.875rem; color: var(--dark-text); margin-bottom: 0.25rem; }
    .send-letter-product-sku { font-size: 0.7rem; color: #64748b; margin-bottom: 0.35rem; }
    .send-letter-product-desc { font-size: 0.75rem; color: #475569; line-height: 1.4; margin-bottom: 0.5rem; }
    .send-letter-product-faq { margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--border-color); }
    .send-letter-product-faq-title { font-size: 0.7rem; font-weight: 700; color: var(--dark-text); margin-bottom: 0.35rem; }
    .send-letter-product-faq-item { padding: 0.25rem 0; font-size: 0.7rem; }
    .send-letter-product-faq-q { font-weight: 600; color: var(--dark-text); }
    .send-letter-product-faq-a { color: #64748b; margin: 0; }
    .send-letter-product-unit-price { font-size: 0.875rem; font-weight: 700; color: var(--primary-color); }

    /* Delivery address helper modals (checkout) */
    #deliveryAddressChoiceModal .modal-content, #deliveryAddressManualModal .modal-content {
        border-radius: 14px;
        border: none;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
    }
    .delivery-choice-option {
        display: flex; align-items: flex-start; gap: 0.85rem;
        width: 100%; text-align: left;
        padding: 1rem 1.1rem;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 0.65rem;
    }
    .delivery-choice-option:hover:not(:disabled) {
        border-color: rgba(99, 102, 241, 0.45);
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.03) 100%);
    }
    .delivery-choice-option:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
    .delivery-choice-option .opt-icon {
        width: 44px; height: 44px; min-width: 44px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        font-size: 1.1rem;
    }
    .delivery-choice-option .opt-title { font-weight: 700; font-size: 0.9rem; color: var(--dark-text); margin-bottom: 0.15rem; }
    .delivery-choice-option .opt-desc { font-size: 0.75rem; color: #64748b; line-height: 1.35; }
    .delivery-book-row {
        padding: 0.85rem;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        margin-bottom: 0.65rem;
    }
</style>
@endpush

@section('content')
<div class="send-letter-container">
    <div class="container">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="send-letter-sidebar">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-envelope me-2" style="color: var(--primary-color);"></i>Envelope Cover</label>
                        <select id="envelopeCover" class="form-control" onchange="calculateTotalCost()">
                            @include('design.templates.partials.envelope-type-options', ['envelopeTypes' => $envelopeTypes ?? collect()])
                        </select>
                        <small style="color: #64748b;">Select envelope style for your letter</small>
                    </div>

                    @if(isset($assignedProducts) && $assignedProducts->count() > 0)
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-box me-2" style="color: var(--primary-color);"></i>Attached Product</label>
                        <select id="attachedProductSelect" class="form-control" onchange="calculateTotalCost(); syncAttachedProductTabVisibility();">
                            <option value="">No product</option>
                            @php $templatePriceNum = (float) ($template->price ?? 0); @endphp
                            @foreach($assignedProducts as $p)
                                @php $unitTotal = $templatePriceNum + (float)($p->price ?? 0); @endphp
                                <option value="{{ $p->id }}" data-unit-price="{{ $unitTotal }}" data-product-price="{{ $p->price ?? 0 }}">{{ $p->name }} — {{ format_price($unitTotal) }}/item</option>
                            @endforeach
                        </select>
                        <small style="color: #64748b;">Optional. Template once + (Product × quantity)</small>
                    </div>
                    @endif
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-cube me-2" style="color: var(--primary-color);"></i>Total Quantity</label>
                        <input type="number" id="quantity" class="form-control" min="1" value="1" onchange="onQuantityChange();">
                        <small style="color: #64748b;">Total: <span id="totalQuantityDisplay">1</span> letter(s)</small>
                    </div>

                        <div class="form-group">
                        <div class="toggle-switch">
                            <input type="checkbox" id="sameDesign" checked onchange="updateVariableForms(); updateTotalQuantity(); calculateTotalCost(); toggleCsvImportSection();">
                            <label for="sameDesign" class="form-label mb-0" style="cursor: pointer;">Same Design Print</label>
                        </div>
                        <small style="color: #64748b;">Use same content & address for all items when enabled</small>
                    </div>

                    @auth
                    <div class="form-group mt-2 pt-2" style="border-top: 1px solid var(--border-color);">
                        <label class="form-label"><i class="fas fa-calendar-alt me-2" style="color: var(--primary-color);"></i>When to send</label>
                        <div class="d-flex flex-column gap-2" style="font-size: 0.8125rem;">
                            <label class="d-flex align-items-start gap-2 mb-0" style="cursor: pointer;">
                                <input type="radio" name="sendTiming" value="now" class="mt-1" checked onchange="toggleScheduleLetterFields()">
                                <span><strong>Send after payment</strong><br><span class="text-muted" style="font-size: 0.7rem;">Print job starts as soon as checkout completes (normal).</span></span>
                            </label>
                            <label class="d-flex align-items-start gap-2 mb-0" style="cursor: pointer;">
                                <input type="radio" name="sendTiming" value="schedule" class="mt-1" onchange="toggleScheduleLetterFields()">
                                <span><strong>Schedule for later</strong><br><span class="text-muted" style="font-size: 0.7rem;">Pay now with credits or card; we create the order and use materials on your date.</span></span>
                            </label>
                        </div>
                        <div id="scheduleLetterFields" class="mt-2" style="display: none;">
                            <label class="form-label" for="scheduleLetterDateTime">Date &amp; time</label>
                            <input type="datetime-local" id="scheduleLetterDateTime" class="form-control" onchange="scheduleSaveSendLetterDraft()">
                            <small class="text-muted d-block mt-1" style="font-size: 0.68rem;">Minimum ~10 minutes from now. Use Enterprise → Schedule mail to view or cancel. Only credits or Stripe on the next step.</small>
                        </div>
                    </div>
                    @else
                    <p class="small text-muted mb-0 mt-2"><a href="{{ route('login') }}">Sign in</a> to schedule a letter for a future date.</p>
                    @endauth

                    <!-- Cost Breakdown -->
                    <div class="mt-2 pt-2" style="border-top: 1px solid var(--border-color);">
                        <h4 style="font-weight: 700; color: var(--dark-text); font-size: 0.9375rem; margin-bottom: 0.5rem;">
                            <i class="fas fa-calculator me-1" style="color: var(--primary-color);"></i>Cost Breakdown
                        </h4>
                        <div id="breakdownTemplateOnceRow" style="display: none; justify-content: space-between; margin-bottom: 0.3rem; font-size: 0.75rem; color: #64748b;">
                            <span>Template (once):</span>
                            <span id="breakdownTemplateOncePrice">{{ format_price(0) }}</span>
                        </div>
                        <div id="breakdownProductQtyRow" style="display: none; justify-content: space-between; margin-bottom: 0.3rem; font-size: 0.75rem; color: #64748b;">
                            <span>Product (× qty):</span>
                            <span id="breakdownProductQtyPrice">{{ format_price(0) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem; font-size: 0.75rem; color: #64748b;">
                            <span id="breakdownTemplateLabel">Letter Cost:</span>
                            <span id="breakdownTemplate">{{ format_price(0) }}</span>
                        </div>
                        <div id="breakdownLetterFormula" style="display: none; margin-bottom: 0.3rem; font-size: 0.7rem; color: #94a3b8; text-align: right;">
                            <span id="breakdownLetterFormulaText"></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem; font-size: 0.75rem; color: #64748b;">
                            <span>Envelope (<span id="breakdownQuantity">1</span>):</span>
                            <span id="breakdownEnvelope">{{ format_price(0) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem; font-size: 0.75rem; color: #64748b;">
                            <span>Sheet Cost (<span id="breakdownSheetPrice">0</span> × <span id="breakdownQuantity2">1</span> qty):</span>
                            <span id="breakdownPagesCost">{{ format_price(0) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--border-color); font-size: 0.875rem; font-weight: 700;">
                            <span>Total:</span>
                            <span id="totalCost" style="color: var(--primary-color);">{{ format_price(0) }}</span>
                        </div>
                        <button type="button" onclick="proceedToCheckout()" class="btn-primary w-100 mt-2" id="checkoutBtn" style="width: 100%; margin-top: 0.5rem; padding: 0.4rem 0.75rem; font-size: 0.8125rem;">
                            <i class="fas fa-paper-plane me-2"></i>Proceed to Checkout
                        </button>
                    </div>

                    <div class="mt-2 pt-2" style="border-top: 1px solid var(--border-color); font-size: 0.75rem; color: #64748b;">
                        <strong style="color: var(--dark-text);">Letter:</strong> {{ $template->name }}
                        @if($template->page_count)
                        <br><strong style="color: var(--dark-text);">Pages:</strong> {{ $template->page_count }}
                        @endif
                    </div>

                    <form id="checkoutForm" action="{{ route('design.checkout.init') }}" method="POST" style="display: none;">
                        @csrf
                        <input type="hidden" name="template_id" value="{{ $template->id }}">
                        <input type="hidden" name="checkout_from" value="send-letter">
                        <input type="hidden" name="checkout_data" id="checkoutDataInput">
                    </form>
                </div>
            </div>

            <!-- Right Content -->
            <div class="col-lg-8">
                <div class="send-letter-content">
                    <div class="tab-container">
                        <div class="tab-navigation">
                            <button class="tab-button active" onclick="switchTab('variableForm')" id="tabVariableForm"><i class="fas fa-edit me-2"></i>Letter Content</button>
                            <button class="tab-button" onclick="switchTab('printDetails')" id="tabPrintDetails"><i class="fas fa-print me-2"></i>Print Details</button>
                            @if(isset($assignedProducts) && $assignedProducts->count() > 0)
                            <button class="tab-button" onclick="switchTab('attachedProduct')" id="tabAttachedProduct" style="display: none;"><i class="fas fa-box me-2"></i>Attached Product</button>
                            @endif
                        </div>

                        <!-- Tab: Letter Content (includes content + send address per item) -->
                        <div class="tab-content active" id="variableFormTab">
                            <h3 style="font-weight: 700; color: var(--dark-text); font-size: 1rem; margin: 0 0 1rem 0;"><i class="fas fa-edit me-1" style="color: var(--primary-color);"></i>Letter Content & Send Address</h3>
                            <p style="font-size: 0.8125rem; color: #64748b; margin-bottom: 1rem;">Fill in letter content and recipient address for each item.</p>
                            <!-- CSV Import Section (visible when qty > 20) -->
                            <div id="csvImportSection" style="display: none; margin-bottom: 1rem; padding: 1rem; background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border: 1px solid #e0e7ff; border-radius: 8px;">
                                <div style="font-weight: 600; color: var(--dark-text); font-size: 0.875rem; margin-bottom: 0.5rem;"><i class="fas fa-file-csv me-1" style="color: var(--primary-color);"></i>Bulk Update Addresses</div>
                                <p style="font-size: 0.75rem; color: #64748b; margin: 0 0 0.75rem 0;">Import or update addresses from CSV. <a href="#" onclick="downloadSampleCsv(); return false;" style="color: var(--primary-color); font-weight: 500;">Download sample</a></p>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <button type="button" class="csv-import-btn btn-primary-csv" onclick="document.getElementById('csvFileInputBulk').click()" style="padding: 0.4rem 0.75rem; font-size: 0.8125rem;">
                                        <i class="fas fa-upload"></i> Upload CSV
                                    </button>
                                    <input type="file" id="csvFileInputBulk" accept=".csv" style="display: none;" onchange="handleCsvUploadBulk(event)">
                                </div>
                            </div>
                            <div id="variableFormsContainer"></div>
                            <div class="mt-3 mb-2">
                                <button type="button" class="btn-add-item-manually" onclick="addItemManually()" id="addItemManuallyBtn">
                                    <i class="fas fa-plus me-2"></i>Add item manually
                                </button>
                            </div>
                        </div>

                        <!-- Tab: Print Details -->
                        <div class="tab-content" id="printDetailsTab">
                            <div class="print-details-section">
                                <div class="print-details-card">
                                    <div class="print-details-card-header"><i class="fas fa-file-alt"></i> Order Summary</div>
                                    <div class="print-details-card-body">
                                        <div class="print-details-grid">
                                            <div><span class="label" style="color: #64748b;">Template</span><br><span class="value" style="font-weight: 600;">{{ $template->name }}</span></div>
                                            <div><span class="label" style="color: #64748b;">Pages</span><br><span class="value" style="font-weight: 600;" id="printDetailPages">{{ $template->page_count ?? 0 }}</span></div>
                                            <div><span class="label" style="color: #64748b;">Quantity</span><br><span class="value" style="font-weight: 600;" id="printDetailQuantity">1</span></div>
                                            <div><span class="label" style="color: #64748b;">Envelope</span><br><span class="value" style="font-weight: 600;" id="printDetailEnvelope">{{ ($envelopeTypes ?? collect())->first()?->name ?? '—' }}</span></div>
                                        </div>
                                        <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-weight: 700;">
                                            Total: <span id="printDetailTotalCost" style="color: var(--primary-color);">{{ format_price(0) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="print-details-card">
                                    <div class="print-details-card-header"><i class="fas fa-layer-group"></i> Paper & Envelope</div>
                                    <div class="print-details-card-body">
                                        <div class="print-details-grid">
                                            <div class="print-details-form-group">
                                                <label class="print-details-form-label">Sheet Type</label>
                                                <select id="printDetailSheetTypeSelect" class="print-details-select" onchange="calculateTotalCost(); playSheetTypeVideo();">
                                                    @forelse($sheetTypes ?? [] as $sheetType)
                                                        <option value="{{ $sheetType->slug }}" data-multiplier="{{ $sheetType->multiplier }}" data-price="{{ $sheetType->price_per_sheet ?? 0 }}">{{ $sheetType->name }} ({{ format_price($sheetType->price_per_sheet ?? 0) }}/sheet)</option>
                                                    @empty
                                                        <option value="" disabled selected>No sheet types in stock</option>
                                                    @endforelse
                                                </select>
                                            </div>
                                            <div class="print-details-form-group">
                                                <label class="print-details-form-label">Envelope Cover</label>
                                                <select id="printDetailEnvelopeSelect" class="print-details-select" onchange="calculateTotalCost(); document.getElementById('envelopeCover').value = this.value;">
                                                    @include('design.templates.partials.envelope-type-options', ['envelopeTypes' => $envelopeTypes ?? collect()])
                                                </select>
                                            </div>
                                        </div>
                                        <!-- Sheet Type Video & Description Preview -->
                                        <div class="sheet-type-preview-section">
                                            <div id="sheetTypeVideoContainer" class="print-details-video-wrapper">
                                                <video id="sheetTypeVideo" playsinline muted loop preload="metadata" style="display: none;"></video>
                                                <div id="sheetTypeVideoPlaceholder" class="print-details-video-placeholder">
                                                    <i class="fas fa-play-circle"></i>
                                                    Select a sheet type to preview
                                                </div>
                                            </div>
                                            <div class="sheet-type-description-card">
                                                <div class="sheet-type-name" id="sheetTypeNameDisplay">
                                                    <i class="fas fa-layer-group"></i>
                                                    <span id="sheetTypeNameText">—</span>
                                                </div>
                                                <div id="sheetTypeDescriptionDisplay" class="sheet-type-desc-text sheet-type-description-placeholder">
                                                    Select a sheet type to view its description and video preview.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(isset($assignedProducts) && $assignedProducts->count() > 0)
                        <div class="tab-content" id="attachedProductTab" style="display: none;">
                            <h3 style="font-weight: 700; color: var(--dark-text); font-size: 1rem; margin: 0 0 0.75rem 0;"><i class="fas fa-box me-1" style="color: var(--primary-color);"></i>Attached Product</h3>
                            <p style="font-size: 0.8125rem; color: #64748b; margin-bottom: 1rem;">Products you can add to this letter. Select one in the sidebar to include its price (Template + Product) × quantity.</p>
                            @php $tPrice = (float) ($template->price ?? 0); @endphp
                            @foreach($assignedProducts as $p)
                                @php
                                    $pPrice = (float) ($p->price ?? 0);
                                    $unitTotal = $tPrice + $pPrice;
                                    $pImageUrl = $p->image_url ?? (($p->image && \Storage::disk('public')->exists($p->image)) ? \Storage::disk('public')->url($p->image) : null);
                                @endphp
                                <div class="send-letter-product-card">
                                    <div class="send-letter-product-image">
                                        @if($pImageUrl)
                                            <img src="{{ $pImageUrl }}" alt="{{ $p->name }}">
                                        @else
                                            <span class="no-img"><i class="fas fa-box-open"></i></span>
                                        @endif
                                    </div>
                                    <div class="send-letter-product-details">
                                        <div class="send-letter-product-name">{{ $p->name }}</div>
                                        @if($p->sku)<div class="send-letter-product-sku"><code>{{ $p->sku }}</code></div>@endif
                                        @if($p->description)<div class="send-letter-product-desc">{{ $p->description }}</div>@endif
                                        <div class="send-letter-product-unit-price">{{ format_price($unitTotal) }} <span style="font-weight: normal; font-size: 0.75rem; color: #64748b;">(Template {{ format_price($tPrice) }} + Product {{ format_price($pPrice) }})</span></div>
                                        @if($p->faqs && is_array($p->faqs) && count($p->faqs) > 0)
                                            <div class="send-letter-product-faq">
                                                <div class="send-letter-product-faq-title">FAQ</div>
                                                @foreach($p->faqs as $faq)
                                                    @if(!empty($faq['question']) || !empty($faq['answer']))
                                                        <div class="send-letter-product-faq-item">
                                                            @if(!empty($faq['question']))<div class="send-letter-product-faq-q">{{ $faq['question'] }}</div>@endif
                                                            @if(!empty($faq['answer']))<p class="send-letter-product-faq-a">{{ $faq['answer'] }}</p>@endif
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSV Import Modal (shown when quantity > 20) -->
<div id="csvImportModal" class="csv-import-modal">
    <div class="csv-import-card">
        <h4><i class="fas fa-file-csv me-2" style="color: var(--primary-color);"></i>Import Addresses from CSV</h4>
        <p>You're adding <strong id="csvImportQtyDisplay">21</strong> addresses. Import from CSV for faster bulk entry.</p>
        <div class="csv-import-actions">
            <button type="button" class="csv-import-btn btn-primary-csv" onclick="downloadSampleCsv()">
                <i class="fas fa-download"></i> Download Sample CSV
            </button>
            <button type="button" class="csv-import-btn" onclick="document.getElementById('csvFileInput').click()">
                <i class="fas fa-upload"></i> Upload CSV File
            </button>
            <input type="file" id="csvFileInput" accept=".csv" onchange="handleCsvUpload(event)">
            <button type="button" class="csv-import-btn" onclick="dismissCsvImportModal(true)">
                <i class="fas fa-edit"></i> Enter Manually
            </button>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="preview-modal" onclick="if(event.target === this) closePreview()">
    <div class="preview-modal-content" onclick="event.stopPropagation();">
        <div class="preview-header">
            <h3 style="color: white !important; font-size: 1rem !important; margin: 0 !important;"><i class="fas fa-eye me-2" style="color: var(--primary-color);"></i>Design Preview</h3>
            <div class="preview-zoom-controls">
                <button type="button" class="preview-tool-btn" onclick="previewZoomFit()" title="Fit to screen"><i class="fas fa-compress-arrows-alt"></i></button>
                <button type="button" class="preview-tool-btn" onclick="previewZoomOut()" title="Zoom out"><i class="fas fa-search-minus"></i></button>
                <span class="preview-zoom-label" id="previewZoomLabel">100%</span>
                <button type="button" class="preview-tool-btn" onclick="previewZoomIn()" title="Zoom in"><i class="fas fa-search-plus"></i></button>
            </div>
            <button class="preview-tool-btn" onclick="closePreview()" title="Close"><i class="fas fa-times"></i></button>
        </div>
        <div class="preview-canvas-container" id="previewCanvasContainer">
            <div class="preview-canvas-wrapper" id="previewCanvasWrapper">
                <canvas id="previewCanvas"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Delivery address required — choose source before checkout -->
<div class="modal fade" id="deliveryAddressChoiceModal" tabindex="-1" aria-labelledby="deliveryAddressChoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="deliveryAddressChoiceLabel"><i class="fas fa-truck me-2 text-primary"></i>Add delivery address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3">Recipient name and address line 1 are required for each letter. Choose how to fill them:</p>
                <button type="button" class="delivery-choice-option" id="deliveryOptProfile" onclick="applyDeliveryFromProfileAndCheckout()">
                    <span class="opt-icon"><i class="fas fa-user"></i></span>
                    <span class="flex-grow-1">
                        <div class="opt-title">Use my profile address</div>
                        <div class="opt-desc" id="deliveryOptProfileDesc">Use the phone and address saved in your account settings as the delivery address for all items.</div>
                    </span>
                </button>
                <div class="delivery-book-row">
                    <div class="opt-title mb-2"><i class="fas fa-address-book me-1 text-primary"></i> Address book</div>
                    <p class="small text-muted mb-2">Apply one saved contact to all recipient slots on this order.</p>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <select class="form-select form-select-sm" id="deliveryModalBookSelect" style="max-width: 280px;">
                            <option value="">— Select saved address —</option>
                            @if(isset($addressBook) && $addressBook->count())
                                @foreach($addressBook as $ab)
                                    <option value="{{ $ab->id }}">{{ $ab->contact_name }}{{ $ab->label ? ' · '.$ab->label : '' }}</option>
                                @endforeach
                            @endif
                        </select>
                        <button type="button" class="btn btn-sm btn-primary" onclick="applyDeliveryFromBookModalAndCheckout()"><i class="fas fa-check me-1"></i>Apply &amp; continue</button>
                    </div>
                </div>
                <button type="button" class="delivery-choice-option mb-0" onclick="openDeliveryManualModal()">
                    <span class="opt-icon"><i class="fas fa-pen-fancy"></i></span>
                    <span class="flex-grow-1">
                        <div class="opt-title">Enter address manually</div>
                        <div class="opt-desc">Open a form to type the delivery address, then apply to all items and continue.</div>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deliveryAddressManualModal" tabindex="-1" aria-labelledby="deliveryAddressManualLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="deliveryAddressManualLabel"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Delivery address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3">This will fill every recipient slot on this order with the same address.</p>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Recipient name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="deliveryManualName" placeholder="Full name">
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Address line 1 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="deliveryManualLine1" placeholder="Street address">
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Address line 2</label>
                    <input type="text" class="form-control form-control-sm" id="deliveryManualLine2" placeholder="Apt, suite (optional)">
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">City</label>
                        <input type="text" class="form-control form-control-sm" id="deliveryManualCity">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">State</label>
                        <input type="text" class="form-control form-control-sm" id="deliveryManualState">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">ZIP</label>
                        <input type="text" class="form-control form-control-sm" id="deliveryManualZip">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Country</label>
                    <input type="text" class="form-control form-control-sm" id="deliveryManualCountry" placeholder="Country">
                </div>
                <button type="button" class="btn btn-primary w-100 fw-semibold" onclick="applyDeliveryManualAndCheckout()"><i class="fas fa-check me-2"></i>Apply to all &amp; continue to checkout</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
<script>
    const templateVariables = @json($template->variables ?? []);
    const templatePages = @json($template->pages ?? []);
    const templatePrice = parseFloat({{ $template->price ?? 0 }});
    const templatePageCount = {{ $template->page_count ?? 0 }};
    @php
        $sheetTypesArray = [];
        if (isset($sheetTypes) && $sheetTypes) {
            foreach ($sheetTypes as $st) {
                $sheetTypesArray[$st->slug] = [
                    'slug' => $st->slug,
                    'name' => $st->name,
                    'multiplier' => (float)$st->multiplier,
                    'price_per_sheet' => (float)$st->price_per_sheet,
                    'video_url' => $st->video_url ?? null,
                    'image_url' => $st->image_url ?? null,
                    'description' => $st->description ?? ''
                ];
            }
        }
    @endphp
    const sheetTypesData = @json($sheetTypesArray);
    const pricingRulesData = @json($pricingRules ?? []);
    const currencySymbol = @json(\App\Models\Setting::get('currency_symbol') ?: '$');
    const currencyDecimals = parseInt(@json(\App\Models\Setting::get('price_decimal_places') ?: 2), 10);
    @php
        $addressBookForJs = isset($addressBook) ? $addressBook->map(function ($a) {
            return ['id' => $a->id, 'contact_name' => $a->contact_name, 'label' => $a->label, 'address_line1' => $a->address_line1, 'address_line2' => $a->address_line2, 'city' => $a->city, 'state' => $a->state, 'postal_code' => $a->postal_code, 'country' => $a->country];
        })->values() : [];
    @endphp
    const addressBookEntries = @json($addressBookForJs);
    @php
        $u = auth()->user();
        $profileDeliveryPayload = [
            'ready' => $u && !$u->needsContactDetails(),
            'name' => $u->name ?? '',
            'phone' => $u->phone ?? '',
            'address' => $u->address ?? '',
        ];
    @endphp
    const profileDelivery = @json($profileDeliveryPayload);

    const CSV_IMPORT_THRESHOLD = 20;
    const CSV_HEADERS = ['name', 'line1', 'line2', 'city', 'state', 'zip', 'country'];

    function formatPrice(amount) { return (currencySymbol || '$') + parseFloat(amount ?? 0).toFixed(currencyDecimals ?? 2); }

    let currentForms = [];
    let userChoseManualForCsv = false;
    let previewCanvas = null;
    let previewPages = [];
    let previewZoom = 1;
    let previewCanvasWidth = 800;
    let previewCanvasHeight = 1000;
    const PREVIEW_ZOOM_MIN = 0.2;
    const PREVIEW_ZOOM_MAX = 3;
    let addressSearchTimeout = null;

    const sendLetterTemplateId = {{ $template->id }};
    const SEND_LETTER_DRAFT_KEY = 'sendLetter_draft_' + sendLetterTemplateId;
    let sendLetterSaveDraftTimeout = null;

    function saveSendLetterDraft() {
        try {
            const quantity = parseInt(document.getElementById('quantity')?.value) || 1;
            const sameDesign = document.getElementById('sameDesign')?.checked ?? true;
            const sheetTypeSelect = document.getElementById('printDetailSheetTypeSelect');
            const envelopeSelect = document.getElementById('envelopeCover');
            const itemQuantities = {};
            const variables = {};
            const addresses = {};
            currentForms.forEach(function(form) {
                const qtyEl = document.getElementById('itemQuantity_' + form.index);
                if (qtyEl) itemQuantities[form.index] = parseInt(qtyEl.value) || 1;
                (templateVariables || []).forEach(function(v, vi) {
                    const el = document.getElementById('var_' + form.index + '_' + vi);
                    if (el) variables[form.index + '_' + vi] = el.value || '';
                });
            });
            const totalQty = parseInt(document.getElementById('totalQuantityDisplay')?.textContent) || parseInt(document.getElementById('quantity')?.value) || quantity;
            for (let i = 1; i <= totalQty; i++) {
                addresses[i] = {
                    name: document.getElementById('addrName_' + i)?.value || '',
                    line1: document.getElementById('addrLine1_' + i)?.value || '',
                    line2: document.getElementById('addrLine2_' + i)?.value || '',
                    city: document.getElementById('addrCity_' + i)?.value || '',
                    state: document.getElementById('addrState_' + i)?.value || '',
                    zip: document.getElementById('addrZip_' + i)?.value || '',
                    country: document.getElementById('addrCountry_' + i)?.value || ''
                };
            }
            const sendSchedRadio = document.querySelector('input[name="sendTiming"][value="schedule"]');
            const draft = {
                quantity: quantity,
                sameDesign: sameDesign,
                sheet_type: sheetTypeSelect?.value || 'standard',
                envelope_cover: envelopeSelect?.value ?? '',
                itemQuantities: itemQuantities,
                variables: variables,
                addresses: addresses,
                sendTiming: (sendSchedRadio && sendSchedRadio.checked) ? 'schedule' : 'now',
                scheduleLetterDateTime: document.getElementById('scheduleLetterDateTime')?.value || ''
            };
            sessionStorage.setItem(SEND_LETTER_DRAFT_KEY, JSON.stringify(draft));
        } catch (e) { console.warn('saveSendLetterDraft:', e); }
    }

    function scheduleSaveSendLetterDraft() {
        clearTimeout(sendLetterSaveDraftTimeout);
        sendLetterSaveDraftTimeout = setTimeout(saveSendLetterDraft, 500);
    }

    function restoreSendLetterDraft() {
        try {
            const raw = sessionStorage.getItem(SEND_LETTER_DRAFT_KEY);
            if (!raw) return null;
            return JSON.parse(raw);
        } catch (e) { return null; }
    }

    function applySendLetterDraftValues(draft) {
        if (!draft) return;
        try {
            const sheetTypeSelect = document.getElementById('printDetailSheetTypeSelect');
            const envelopeSelect = document.getElementById('envelopeCover');
            const envelopeSelectPrint = document.getElementById('printDetailEnvelopeSelect');
            if (sheetTypeSelect && draft.sheet_type) sheetTypeSelect.value = draft.sheet_type;
            if (envelopeSelect && 'envelope_cover' in draft) envelopeSelect.value = draft.envelope_cover;
            if (envelopeSelectPrint && 'envelope_cover' in draft) envelopeSelectPrint.value = draft.envelope_cover;
            if (draft.itemQuantities) {
                Object.keys(draft.itemQuantities).forEach(function(idx) {
                    const el = document.getElementById('itemQuantity_' + idx);
                    if (el) el.value = draft.itemQuantities[idx];
                });
            }
            if (draft.variables) {
                Object.keys(draft.variables).forEach(function(key) {
                    const el = document.getElementById('var_' + key);
                    if (el) el.value = draft.variables[key] || '';
                });
            }
            if (draft.addresses) {
                const addrIdMap = { name: 'addrName', line1: 'addrLine1', line2: 'addrLine2', city: 'addrCity', state: 'addrState', zip: 'addrZip', country: 'addrCountry' };
                Object.keys(draft.addresses).forEach(function(idx) {
                    const a = draft.addresses[idx];
                    if (!a) return;
                    Object.keys(addrIdMap).forEach(function(field) {
                        const el = document.getElementById(addrIdMap[field] + '_' + idx);
                        if (el) el.value = a[field] || '';
                    });
                });
            }
            if (draft.sendTiming === 'schedule') {
                document.querySelector('input[name="sendTiming"][value="schedule"]')?.click();
                const sdt = document.getElementById('scheduleLetterDateTime');
                if (sdt && draft.scheduleLetterDateTime) sdt.value = draft.scheduleLetterDateTime;
            } else {
                document.querySelector('input[name="sendTiming"][value="now"]')?.click();
            }
            toggleScheduleLetterFields();
            updateTotalQuantity();
            calculateTotalCost();
        } catch (e) { console.warn('applySendLetterDraftValues:', e); }
    }

    function updateScheduleLetterMinDate() {
        const input = document.getElementById('scheduleLetterDateTime');
        if (!input) return;
        const d = new Date();
        d.setMinutes(d.getMinutes() + 10);
        const pad = function(n) { return String(n).padStart(2, '0'); };
        input.min = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function toggleScheduleLetterFields() {
        const schedule = document.querySelector('input[name="sendTiming"][value="schedule"]')?.checked;
        const fields = document.getElementById('scheduleLetterFields');
        if (fields) fields.style.display = schedule ? 'block' : 'none';
        if (schedule) updateScheduleLetterMinDate();
    }

    function calculateTotalCost() {
        let totalQuantity = 0;
        const sameDesign = document.getElementById('sameDesign')?.checked ?? true;
        if (sameDesign) {
            totalQuantity = parseInt(document.getElementById('quantity')?.value) || 1;
        } else {
            currentForms.forEach(form => {
                const qtyInput = document.getElementById(`itemQuantity_${form.index}`);
                if (qtyInput) totalQuantity += parseInt(qtyInput.value) || 1;
            });
        }
        const quantity = totalQuantity || parseInt(document.getElementById('quantity').value) || 1;

        const envelopeSelect = document.getElementById('envelopeCover');
        const envelopePrice = parseFloat(envelopeSelect?.options[envelopeSelect?.selectedIndex]?.getAttribute('data-price')) || 0;
        const envelopeCost = envelopePrice * quantity;

        const sheetTypeSelect = document.getElementById('printDetailSheetTypeSelect');
        let sheetTypePrice = 0.50, sheetTypeMultiplier = 1.0;
        if (sheetTypeSelect && sheetTypeSelect.value && sheetTypesData && sheetTypesData[sheetTypeSelect.value]) {
            sheetTypePrice = parseFloat(sheetTypesData[sheetTypeSelect.value].price_per_sheet) || 0.50;
            sheetTypeMultiplier = sheetTypesData[sheetTypeSelect.value].multiplier || 1.0;
        }
        const sheetCost = sheetTypePrice * quantity;

        const productSelect = document.getElementById('attachedProductSelect');
        let letterCost = templatePrice;
        const templateOnceRow = document.getElementById('breakdownTemplateOnceRow');
        const templateOncePriceEl = document.getElementById('breakdownTemplateOncePrice');
        const productQtyRow = document.getElementById('breakdownProductQtyRow');
        const productQtyPriceEl = document.getElementById('breakdownProductQtyPrice');
        const formulaRow = document.getElementById('breakdownLetterFormula');
        const formulaTextEl = document.getElementById('breakdownLetterFormulaText');
        if (productSelect && productSelect.value) {
            const opt = productSelect.options[productSelect.selectedIndex];
            const productPrice = parseFloat(opt.getAttribute('data-product-price')) || 0;
            const productTotal = productPrice * quantity;
            letterCost = templatePrice + productTotal;
            if (templateOnceRow) { templateOnceRow.style.display = 'flex'; }
            if (templateOncePriceEl) templateOncePriceEl.textContent = formatPrice(templatePrice);
            if (productQtyRow) { productQtyRow.style.display = 'flex'; }
            if (productQtyPriceEl) productQtyPriceEl.textContent = formatPrice(productTotal);
            const labelEl = document.getElementById('breakdownTemplateLabel');
            if (labelEl) labelEl.textContent = 'Letter total:';
            if (formulaRow) formulaRow.style.display = 'block';
            if (formulaTextEl) formulaTextEl.textContent = formatPrice(templatePrice) + ' + ' + formatPrice(productPrice) + ' × ' + quantity + ' = ' + formatPrice(letterCost);
        } else {
            if (templateOnceRow) templateOnceRow.style.display = 'none';
            if (productQtyRow) productQtyRow.style.display = 'none';
            if (formulaRow) formulaRow.style.display = 'none';
            const labelEl = document.getElementById('breakdownTemplateLabel');
            if (labelEl) labelEl.textContent = 'Letter Cost:';
        }
        const totalCost = letterCost + envelopeCost + sheetCost;

        document.getElementById('breakdownTemplate').textContent = formatPrice(letterCost);
        document.getElementById('breakdownEnvelope').textContent = formatPrice(envelopeCost);
        document.getElementById('breakdownQuantity').textContent = quantity;
        if (document.getElementById('breakdownQuantity2')) document.getElementById('breakdownQuantity2').textContent = quantity;
        if (document.getElementById('breakdownSheetPrice')) document.getElementById('breakdownSheetPrice').textContent = parseFloat(sheetTypePrice).toFixed(currencyDecimals);
        document.getElementById('breakdownPagesCost').textContent = formatPrice(sheetCost);
        document.getElementById('totalCost').textContent = formatPrice(totalCost);

        if (document.getElementById('printDetailQuantity')) document.getElementById('printDetailQuantity').textContent = quantity;
        if (document.getElementById('printDetailTotalCost')) document.getElementById('printDetailTotalCost').textContent = formatPrice(totalCost);
        if (document.getElementById('printDetailEnvelope')) document.getElementById('printDetailEnvelope').textContent = envelopeSelect?.options[envelopeSelect?.selectedIndex]?.text || '—';
    }

    function playSheetTypeVideo() {
        const sheetTypeSelect = document.getElementById('printDetailSheetTypeSelect');
        const videoEl = document.getElementById('sheetTypeVideo');
        const placeholder = document.getElementById('sheetTypeVideoPlaceholder');
        const nameEl = document.getElementById('sheetTypeNameText');
        const descEl = document.getElementById('sheetTypeDescriptionDisplay');
        if (!sheetTypeSelect || !videoEl || !placeholder) return;
        const slug = sheetTypeSelect.value;
        const data = sheetTypesData && sheetTypesData[slug] ? sheetTypesData[slug] : null;
        const videoUrl = data && data.video_url ? data.video_url : null;
        const name = data && data.name ? data.name : (sheetTypeSelect.options[sheetTypeSelect.selectedIndex]?.text || '—');
        const description = data && data.description ? data.description : '';
        if (videoUrl) {
            videoEl.src = videoUrl;
            videoEl.style.display = 'block';
            placeholder.style.display = 'none';
            videoEl.muted = true;
            videoEl.loop = true;
            videoEl.play().catch(() => {});
        } else {
            videoEl.src = '';
            videoEl.style.display = 'none';
            placeholder.style.display = 'block';
        }
        if (nameEl) nameEl.textContent = name;
        if (descEl) {
            descEl.classList.remove('sheet-type-description-placeholder');
            if (description) {
                descEl.textContent = description;
            } else {
                descEl.classList.add('sheet-type-description-placeholder');
                descEl.textContent = slug ? 'No description available for this sheet type.' : 'Select a sheet type to view its description and video preview.';
            }
        }
    }

    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
        if (tabName === 'variableForm') { document.getElementById('variableFormTab').classList.add('active'); document.getElementById('tabVariableForm').classList.add('active'); }
        else if (tabName === 'printDetails') { document.getElementById('printDetailsTab').classList.add('active'); document.getElementById('tabPrintDetails').classList.add('active'); playSheetTypeVideo(); }
        else if (tabName === 'attachedProduct') { const el = document.getElementById('attachedProductTab'); const btn = document.getElementById('tabAttachedProduct'); if (el) { el.classList.add('active'); el.style.display = ''; } if (btn) btn.classList.add('active'); }
    }

    function syncAttachedProductTabVisibility() {
        const productSelect = document.getElementById('attachedProductSelect');
        const tabBtn = document.getElementById('tabAttachedProduct');
        const tabPane = document.getElementById('attachedProductTab');
        if (!productSelect || !tabBtn || !tabPane) return;
        const hasProduct = !!productSelect.value;
        if (hasProduct) {
            tabBtn.style.display = '';
            tabPane.style.display = '';
            switchTab('attachedProduct');
        } else {
            tabBtn.style.display = 'none';
            tabPane.style.display = 'none';
            if (tabPane.classList.contains('active')) switchTab('variableForm');
        }
    }

    function onQuantityChange() {
        const qty = parseInt(document.getElementById('quantity').value) || 1;
        if (qty > CSV_IMPORT_THRESHOLD && !userChoseManualForCsv) {
            document.getElementById('csvImportQtyDisplay').textContent = qty;
            document.getElementById('csvImportModal').classList.add('show');
            return;
        }
        updateVariableForms();
        updateAllItemQuantities();
        calculateTotalCost();
        toggleCsvImportSection();
    }

    function dismissCsvImportModal(manual) {
        document.getElementById('csvImportModal').classList.remove('show');
        if (manual) {
            userChoseManualForCsv = true;
            updateVariableForms();
            updateAllItemQuantities();
            calculateTotalCost();
            toggleCsvImportSection();
        }
    }

    function toggleCsvImportSection() {
        const qty = parseInt(document.getElementById('quantity').value) || parseInt(document.getElementById('totalQuantityDisplay')?.textContent) || 1;
        const section = document.getElementById('csvImportSection');
        if (section) section.style.display = qty > CSV_IMPORT_THRESHOLD ? 'block' : 'none';
    }

    function downloadSampleCsv() {
        const sample = [
            CSV_HEADERS.join(','),
            'John Doe,123 Main St,Apt 4,New York,NY,10001,USA',
            'Jane Smith,456 Oak Ave,,Los Angeles,CA,90001,USA'
        ].join('\n');
        const blob = new Blob([sample], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'letter-addresses-sample.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    }

    function parseCsvFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const text = e.target.result;
                    const lines = text.split(/\r?\n/).filter(l => l.trim());
                    if (lines.length < 2) { resolve([]); return; }
                    const headers = parseCsvLine(lines[0]).map(h => h.trim().toLowerCase().replace(/\s+/g, '_'));
                    const rows = [];
                    for (let i = 1; i < lines.length; i++) {
                        const values = parseCsvLine(lines[i]);
                        const row = {};
                        headers.forEach((h, j) => { row[h] = (values[j] || '').trim(); });
                        rows.push(row);
                    }
                    resolve(rows);
                } catch (err) { reject(err); }
            };
            reader.onerror = () => reject(new Error('Failed to read file'));
            reader.readAsText(file, 'UTF-8');
        });
    }

    function parseCsvLine(line) {
        const result = [];
        let current = '';
        let inQuotes = false;
        for (let i = 0; i < line.length; i++) {
            const c = line[i];
            if (c === '"') { inQuotes = !inQuotes; }
            else if (c === ',' && !inQuotes) { result.push(current.replace(/^"|"$/g, '')); current = ''; }
            else { current += c; }
        }
        result.push(current.replace(/^"|"$/g, ''));
        return result;
    }

    function mapCsvRowToAddress(row) {
        const get = (keys) => { for (const k of keys) { const v = row[k]; if (v !== undefined && v !== '') return String(v).trim(); } return ''; };
        return {
            name: get(['name', 'recipient_name', 'recipient']),
            line1: get(['line1', 'address_line1', 'address1', 'address']),
            line2: get(['line2', 'address_line2', 'address2']),
            city: get(['city']),
            state: get(['state', 'province']),
            zip: get(['zip', 'postal', 'postal_code']),
            country: get(['country'])
        };
    }

    function populateAddressesFromData(rows) {
        rows.forEach((row, i) => {
            const idx = i + 1;
            const addr = mapCsvRowToAddress(row);
            const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
            set('addrName_' + idx, addr.name);
            set('addrLine1_' + idx, addr.line1);
            set('addrLine2_' + idx, addr.line2);
            set('addrCity_' + idx, addr.city);
            set('addrState_' + idx, addr.state);
            set('addrZip_' + idx, addr.zip);
            set('addrCountry_' + idx, addr.country);
        });
    }

    async function handleCsvUpload(event) {
        const file = event.target.files?.[0];
        event.target.value = '';
        if (!file) return;
        try {
            const rows = await parseCsvFile(file);
            if (rows.length === 0) { alert('CSV file is empty or invalid.'); return; }
            userChoseManualForCsv = true;
            document.getElementById('quantity').value = rows.length;
            document.getElementById('csvImportModal').classList.remove('show');
            updateVariableForms();
            updateAllItemQuantities();
            calculateTotalCost();
            toggleCsvImportSection();
            setTimeout(() => populateAddressesFromData(rows), 50);
        } catch (err) {
            alert('Failed to parse CSV: ' + (err.message || 'Invalid file format'));
        }
    }

    async function handleCsvUploadBulk(event) {
        const file = event.target.files?.[0];
        event.target.value = '';
        if (!file) return;
        try {
            const rows = await parseCsvFile(file);
            if (rows.length === 0) { alert('CSV file is empty or invalid.'); return; }
            const currentQty = parseInt(document.getElementById('quantity').value) || parseInt(document.getElementById('totalQuantityDisplay')?.textContent) || 1;
            if (rows.length !== currentQty) {
                if (!confirm('CSV has ' + rows.length + ' rows but you have ' + currentQty + ' addresses. Update quantity to ' + rows.length + '?')) return;
                document.getElementById('quantity').value = rows.length;
                updateVariableForms();
                updateAllItemQuantities();
                calculateTotalCost();
            }
            setTimeout(() => populateAddressesFromData(rows), 50);
        } catch (err) {
            alert('Failed to parse CSV: ' + (err.message || 'Invalid file format'));
        }
    }

    function addItemManually() {
        const mainQty = document.getElementById('quantity');
        const n = (parseInt(mainQty?.value) || 1) + 1;
        mainQty.value = n;
        updateVariableForms();
        updateTotalQuantity();
        calculateTotalCost();
        toggleCsvImportSection();
        scheduleSaveSendLetterDraft();
    }

    function updateVariableForms() {
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const sameDesign = document.getElementById('sameDesign').checked;
        const container = document.getElementById('variableFormsContainer');
        container.innerHTML = '';
        currentForms = [];

        if (sameDesign) {
            container.innerHTML = generateVariableForm(1, true, quantity, quantity);
            currentForms.push({ index: 1, isShared: true });
        } else {
            for (let i = 1; i <= quantity; i++) {
                container.innerHTML += generateVariableForm(i, false, 1, quantity);
                currentForms.push({ index: i, isShared: false });
            }
        }
        updateTotalQuantity();
    }

    function generateAddressSection(index) {
        const hasAddressBook = addressBookEntries && addressBookEntries.length > 0;
        const bookOptions = (addressBookEntries || []).map(a => {
            const label = (a.contact_name || '') + (a.label ? ' · ' + a.label : '');
            return '<option value="' + a.id + '">' + (label || 'Address #' + a.id) + '</option>';
        }).join('');
        return `
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);" class="address-section" data-addr-index="${index}">
                <div style="font-weight: 600; color: var(--dark-text); font-size: 0.8125rem; margin-bottom: 0.5rem;"><i class="fas fa-map-marker-alt me-1" style="color: var(--primary-color);"></i>Send Address${index > 1 ? ' #' + index : ''}</div>
                <div class="address-source-cards">
                    <label class="address-source-card${!hasAddressBook ? ' disabled' : ''}" ${!hasAddressBook ? 'title="Sign in and add addresses in Enterprise → Address Book to use this"' : ''}>
                        <input type="radio" name="addrSource_${index}" value="address_book" onchange="toggleAddressSource(${index}, 'address_book')" ${!hasAddressBook ? 'disabled' : ''}>
                        <span class="address-source-card-icon"><i class="fas fa-address-book"></i></span>
                        <div class="address-source-card-body">
                            <div class="address-source-card-title">From Address Book</div>
                            <div class="address-source-card-desc">Pick a saved contact from your address book</div>
                        </div>
                    </label>
                    <label class="address-source-card selected">
                        <input type="radio" name="addrSource_${index}" value="manual" checked onchange="toggleAddressSource(${index}, 'manual')">
                        <span class="address-source-card-icon"><i class="fas fa-pen-fancy"></i></span>
                        <div class="address-source-card-body">
                            <div class="address-source-card-title">Enter Manually</div>
                            <div class="address-source-card-desc">Type or search for the recipient address</div>
                        </div>
                    </label>
                </div>
                <div id="addrBookPanel_${index}" class="address-source-panel address-book-select-wrap" style="${hasAddressBook ? '' : 'display:none;'}">
                    <div class="form-group">
                        <label class="form-label">Choose saved address</label>
                        <select id="addrBookSelect_${index}" class="form-control" onchange="applyAddressBookToFields(${index}, this.value)">
                            <option value="">— Select address —</option>
                            ${bookOptions}
                        </select>
                    </div>
                </div>
                <div id="addrManualPanel_${index}" class="address-source-panel show">
                    <div class="form-group">
                        <label class="form-label">Search Address</label>
                        <div class="address-search-wrapper">
                            <input type="text" id="addressSearch_${index}" class="form-control" placeholder="Type to search address..." oninput="searchAddress(${index}, this.value)" autocomplete="off">
                            <div id="addressSearchResults_${index}" class="address-search-results" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="form-group"><label class="form-label">Recipient Name</label><input type="text" id="addrName_${index}" class="form-control" placeholder="Full name"></div>
                    <div class="form-group"><label class="form-label">Address Line 1</label><input type="text" id="addrLine1_${index}" class="form-control" placeholder="Street address"></div>
                    <div class="form-group"><label class="form-label">Address Line 2</label><input type="text" id="addrLine2_${index}" class="form-control" placeholder="Apt, suite, etc. (optional)"></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem;">
                        <div class="form-group"><label class="form-label">City</label><input type="text" id="addrCity_${index}" class="form-control"></div>
                        <div class="form-group"><label class="form-label">State/Province</label><input type="text" id="addrState_${index}" class="form-control"></div>
                        <div class="form-group"><label class="form-label">ZIP/Postal</label><input type="text" id="addrZip_${index}" class="form-control"></div>
                    </div>
                    <div class="form-group"><label class="form-label">Country</label><input type="text" id="addrCountry_${index}" class="form-control" placeholder="Country"></div>
                </div>
            </div>
        `;
    }

    function toggleAddressSource(index, source) {
        const bookPanel = document.getElementById('addrBookPanel_' + index);
        const manualPanel = document.getElementById('addrManualPanel_' + index);
        if (!bookPanel || !manualPanel) return;
        var section = document.querySelector('.address-section[data-addr-index="' + index + '"]');
        if (section) {
            section.querySelectorAll('.address-source-card').forEach(function(card) { card.classList.remove('selected'); });
            var radio = section.querySelector('.address-source-card input[value="' + source + '"]');
            if (radio && radio.closest('.address-source-card')) radio.closest('.address-source-card').classList.add('selected');
        }
        if (source === 'address_book') {
            bookPanel.classList.add('show');
            manualPanel.classList.remove('show');
            const sel = document.getElementById('addrBookSelect_' + index);
            if (sel && sel.value) applyAddressBookToFields(index, sel.value);
        } else {
            bookPanel.classList.remove('show');
            manualPanel.classList.add('show');
        }
    }

    function applyAddressBookToFields(index, id) {
        const entry = (addressBookEntries || []).find(function(a) { return String(a.id) === String(id); });
        if (!entry) return;
        const set = function(elId, val) { var e = document.getElementById(elId); if (e) e.value = val || ''; };
        set('addrName_' + index, entry.contact_name);
        set('addrLine1_' + index, entry.address_line1);
        set('addrLine2_' + index, entry.address_line2);
        set('addrCity_' + index, entry.city);
        set('addrState_' + index, entry.state);
        set('addrZip_' + index, entry.postal_code);
        set('addrCountry_' + index, entry.country);
    }

    function removeItem(index) {
        const mainQty = document.getElementById('quantity');
        const current = parseInt(mainQty?.value) || 1;
        if (current <= 1) return;
        mainQty.value = current - 1;
        updateVariableForms();
        updateTotalQuantity();
        calculateTotalCost();
        toggleCsvImportSection();
        scheduleSaveSendLetterDraft();
    }

    function generateVariableForm(index, isShared, addressCount, totalQuantity) {
        totalQuantity = totalQuantity != null ? totalQuantity : (addressCount || 1);
        const showRemove = totalQuantity > 1;
        const removeBtn = showRemove ? `<button type="button" class="btn-remove-item" onclick="event.stopPropagation(); removeItem(${index})" title="Remove item"><i class="fas fa-trash-alt"></i></button>` : '';
        const vars = templateVariables || [];
        let addressSection = '';
        for (let a = 1; a <= (addressCount || 1); a++) {
            const addrIndex = isShared ? a : index;
            addressSection += generateAddressSection(addrIndex);
        }
        if (vars.length === 0) {
            return `<div class="variable-form-card" data-form-index="${index}">
                <div class="variable-form-header" onclick="toggleFormCollapse(${index}, 'variable')">
                    <div><i class="fas fa-chevron-down collapse-icon"></i> <span>${isShared ? 'Shared (All Items)' : 'Item #' + index}</span></div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="number" id="itemQuantity_${index}" class="form-control" min="1" value="1" onchange="event.stopPropagation(); updateTotalQuantity(); calculateTotalCost();" style="width: 70px;">
                        <button onclick="event.stopPropagation(); previewDesign(${index})" class="btn-preview"><i class="fas fa-eye"></i> Preview</button>
                        ${removeBtn}
                    </div>
                </div>
                <div class="variable-form-body"><p style="color: #64748b; margin: 0;">No variables in this template.</p>${addressSection}</div>
            </div>`;
        }
        let html = `<div class="variable-form-card" data-form-index="${index}">
            <div class="variable-form-header" onclick="toggleFormCollapse(${index}, 'variable')">
                <div><i class="fas fa-chevron-down collapse-icon"></i> <span>${isShared ? 'Shared (All Items)' : 'Item #' + index}</span></div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    ${!isShared ? `<input type="number" id="itemQuantity_${index}" class="form-control" min="1" value="1" onchange="event.stopPropagation(); updateTotalQuantity(); calculateTotalCost();" style="width: 70px;">` : ''}
                    <button onclick="event.stopPropagation(); previewDesign(${index})" class="btn-preview"><i class="fas fa-eye"></i> Preview</button>
                    ${removeBtn}
                </div>
            </div>
            <div class="variable-form-body">`;
        vars.forEach((v, vi) => {
            const id = `var_${index}_${vi}`;
            html += `<div class="form-group"><label class="form-label">${v.name} ${v.required ? '<span style="color:#ef4444">*</span>' : ''}</label>`;
            if (v.form_type === 'select' && v.options?.length) {
                html += `<select id="${id}" class="form-control" ${v.required ? 'required' : ''}><option value="">Select</option>`;
                v.options.forEach(o => html += `<option value="${o}">${o}</option>`);
                html += `</select>`;
            } else if (v.form_type === 'textarea') {
                html += `<textarea id="${id}" class="form-control" rows="3" ${v.required ? 'required' : ''}></textarea>`;
            } else {
                html += `<input type="text" id="${id}" class="form-control" ${v.required ? 'required' : ''}>`;
            }
            html += `</div>`;
        });
        html += addressSection + `</div></div>`;
        return html;
    }

    function searchAddress(index, query) {
        clearTimeout(addressSearchTimeout);
        const resultsEl = document.getElementById(`addressSearchResults_${index}`);
        if (!query || query.length < 3) { resultsEl.style.display = 'none'; return; }

        addressSearchTimeout = setTimeout(() => {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`, {
                headers: { 'Accept': 'application/json', 'User-Agent': 'FlipBook-Letter-Checkout/1.0' }
            })
            .then(r => r.json())
            .then(data => {
                resultsEl.innerHTML = '';
                if (data && data.length) {
                    data.forEach(addr => {
                        const div = document.createElement('div');
                        div.className = 'address-search-result';
                        div.textContent = addr.display_name;
                        div.onclick = () => {
                            const a = addr.address || {};
                            document.getElementById(`addrLine1_${index}`).value = [a.road, a.house_number].filter(Boolean).join(' ') || addr.display_name.split(',')[0] || '';
                            document.getElementById(`addrCity_${index}`).value = a.city || a.town || a.village || a.municipality || '';
                            document.getElementById(`addrState_${index}`).value = a.state || a.county || '';
                            document.getElementById(`addrZip_${index}`).value = a.postcode || '';
                            document.getElementById(`addrCountry_${index}`).value = a.country || '';
                            document.getElementById(`addressSearch_${index}`).value = addr.display_name;
                            resultsEl.style.display = 'none';
                        };
                        resultsEl.appendChild(div);
                    });
                    resultsEl.style.display = 'block';
                } else {
                    resultsEl.style.display = 'none';
                }
            })
            .catch(() => { resultsEl.style.display = 'none'; });
        }, 300);
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.address-search-wrapper')) {
            document.querySelectorAll('.address-search-results').forEach(el => el.style.display = 'none');
        }
    });

    function toggleFormCollapse(index, type) {
        const selector = `.variable-form-card[data-form-index="${index}"]`;
        const card = document.querySelector(selector);
        if (card) card.classList.toggle('collapsed');
    }

    function updateTotalQuantity() {
        const sameDesign = document.getElementById('sameDesign').checked;
        const mainQtyInput = document.getElementById('quantity');
        let total = 0;
        if (sameDesign) {
            const itemQtyEl = document.getElementById('itemQuantity_1');
            total = (itemQtyEl ? parseInt(itemQtyEl.value) : NaN) || parseInt(mainQtyInput?.value) || 1;
            if (mainQtyInput && total >= 1) mainQtyInput.value = total;
        } else {
            currentForms.forEach(f => {
                const q = document.getElementById(`itemQuantity_${f.index}`);
                if (q) total += parseInt(q.value) || 1;
            });
            if (mainQtyInput && total >= 1) mainQtyInput.value = total;
        }
        const el = document.getElementById('totalQuantityDisplay');
        if (el) el.textContent = total;
    }

    function updateAllItemQuantities() {
        const mainQty = parseInt(document.getElementById('quantity').value) || 1;
        const sameDesign = document.getElementById('sameDesign').checked;
        if (sameDesign) {
            const q = document.getElementById('itemQuantity_1');
            if (q) q.value = mainQty;
        } else {
            const perItem = Math.floor(mainQty / currentForms.length) || 1;
            currentForms.forEach((f, i) => {
                const q = document.getElementById(`itemQuantity_${f.index}`);
                if (q) q.value = perItem;
            });
        }
        updateTotalQuantity();
    }

    function previewDesign(itemIndex) {
        const variableValues = {};
        (templateVariables || []).forEach((v, vi) => {
            const input = document.getElementById(`var_${itemIndex}_${vi}`);
            if (input) variableValues[v.name] = input.value.trim();
        });
        previewPages = (templatePages || []).map(pd => {
            try {
                let p = typeof pd === 'string' ? JSON.parse(pd) : pd;
                if (p.objects && Array.isArray(p.objects)) {
                    p.objects = p.objects.map(obj => {
                        if (obj.type === 'text' || obj.type === 'i-text' || obj.type === 'textbox') {
                            let t = obj.text || '';
                            (templateVariables || []).forEach(v => {
                                t = t.replace(new RegExp('\\{\\{' + v.name + '\\}\\}', 'g'), variableValues[v.name] || '');
                            });
                            obj.text = t;
                        }
                        return obj;
                    });
                }
                return JSON.stringify(p);
            } catch (e) { return pd; }
        });
        if (!previewCanvas) previewCanvas = new fabric.Canvas('previewCanvas', { preserveObjectStacking: true, backgroundColor: '#ffffff' });
        document.getElementById('previewModal').style.display = 'block';
        if (previewPages.length) {
            const parsed = typeof previewPages[0] === 'string' ? JSON.parse(previewPages[0]) : previewPages[0];
            const w = parsed.width || 800, h = parsed.height || 1000;
            previewCanvasWidth = w;
            previewCanvasHeight = h;
            previewCanvas.setDimensions({ width: w, height: h });
            previewCanvas.setBackgroundColor(parsed.backgroundColor || '#ffffff', previewCanvas.renderAll.bind(previewCanvas));
            previewCanvas.loadFromJSON(parsed, function() {
                previewCanvas.renderAll();
                var wrapper = document.getElementById('previewCanvasWrapper');
                if (wrapper) {
                    wrapper.style.width = w + 'px';
                    wrapper.style.height = h + 'px';
                }
                requestAnimationFrame(function() { previewZoomFit(); });
            });
        }
    }

    function applyPreviewZoom() {
        var wrapper = document.getElementById('previewCanvasWrapper');
        var label = document.getElementById('previewZoomLabel');
        if (wrapper) {
            wrapper.style.transform = 'scale(' + previewZoom + ')';
        }
        if (label) {
            label.textContent = Math.round(previewZoom * 100) + '%';
        }
    }

    function previewZoomFit() {
        var container = document.getElementById('previewCanvasContainer');
        if (!container || !previewCanvasWidth) return;
        var pad = 24;
        var cw = container.clientWidth - pad;
        var ch = container.clientHeight - pad;
        if (cw < 20 || ch < 20) return;
        var scaleX = cw / previewCanvasWidth;
        var scaleY = ch / previewCanvasHeight;
        previewZoom = Math.min(scaleX, scaleY);
        previewZoom = Math.max(PREVIEW_ZOOM_MIN, Math.min(PREVIEW_ZOOM_MAX, previewZoom));
        applyPreviewZoom();
    }

    function previewZoomIn() {
        previewZoom = Math.min(PREVIEW_ZOOM_MAX, previewZoom * 1.25);
        applyPreviewZoom();
    }

    function previewZoomOut() {
        previewZoom = Math.max(PREVIEW_ZOOM_MIN, previewZoom / 1.25);
        applyPreviewZoom();
    }

    function closePreview() {
        document.getElementById('previewModal').style.display = 'none';
        if (previewCanvas) previewCanvas.clear();
    }

    window.addEventListener('resize', function() {
        if (document.getElementById('previewModal').style.display === 'block' && previewCanvasWidth) {
            previewZoomFit();
        }
    });

    function getAddressSlotIndicesForValidation() {
        const sameDesign = document.getElementById('sameDesign').checked;
        const totalQty = parseInt(document.getElementById('totalQuantityDisplay')?.textContent) || parseInt(document.getElementById('quantity')?.value) || 1;
        if (sameDesign) {
            const indices = [];
            for (let i = 1; i <= totalQty; i++) indices.push(i);
            return indices;
        }
        return currentForms.map(function(f) { return f.index; });
    }

    function isAddressSlotComplete(idx) {
        const name = document.getElementById('addrName_' + idx)?.value?.trim();
        const line1 = document.getElementById('addrLine1_' + idx)?.value?.trim();
        return !!(name && line1);
    }

    function allCheckoutAddressesComplete() {
        return getAddressSlotIndicesForValidation().every(isAddressSlotComplete);
    }

    function openDeliveryAddressChoiceModal() {
        var el = document.getElementById('deliveryAddressChoiceModal');
        if (el && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }

    function applyDeliveryFromProfileAndCheckout() {
        if (!profileDelivery || !profileDelivery.ready) {
            alert('Add your phone and address in Settings first.');
            return;
        }
        var lines = String(profileDelivery.address || '').split(/\r?\n/).map(function(s) { return s.trim(); }).filter(Boolean);
        var line1 = lines[0] || '';
        var line2 = lines.slice(1).join(', ');
        var name = profileDelivery.name || '';
        getAddressSlotIndicesForValidation().forEach(function(idx) {
            toggleAddressSource(idx, 'manual');
            var set = function(suffix, v) { var e = document.getElementById(suffix + idx); if (e) e.value = v || ''; };
            set('addrName_', name);
            set('addrLine1_', line1);
            set('addrLine2_', line2);
        });
        var c = document.getElementById('deliveryAddressChoiceModal');
        if (c && typeof bootstrap !== 'undefined') bootstrap.Modal.getInstance(c)?.hide();
        submitSendLetterCheckout();
    }

    function applyDeliveryFromBookModalAndCheckout() {
        var sel = document.getElementById('deliveryModalBookSelect');
        var id = sel && sel.value;
        if (!id) {
            alert('Please select an address from your address book.');
            return;
        }
        getAddressSlotIndicesForValidation().forEach(function(idx) {
            toggleAddressSource(idx, 'address_book');
            var s = document.getElementById('addrBookSelect_' + idx);
            if (s) s.value = id;
            applyAddressBookToFields(idx, id);
        });
        var c = document.getElementById('deliveryAddressChoiceModal');
        if (c && typeof bootstrap !== 'undefined') bootstrap.Modal.getInstance(c)?.hide();
        submitSendLetterCheckout();
    }

    function openDeliveryManualModal() {
        var choice = document.getElementById('deliveryAddressChoiceModal');
        if (choice && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getInstance(choice)?.hide();
        }
        setTimeout(function() {
            var m = document.getElementById('deliveryAddressManualModal');
            if (m && typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(m).show();
        }, 350);
    }

    function applyDeliveryManualAndCheckout() {
        var name = document.getElementById('deliveryManualName')?.value?.trim();
        var line1 = document.getElementById('deliveryManualLine1')?.value?.trim();
        if (!name || !line1) {
            alert('Recipient name and address line 1 are required.');
            return;
        }
        var line2 = document.getElementById('deliveryManualLine2')?.value?.trim() || '';
        var city = document.getElementById('deliveryManualCity')?.value?.trim() || '';
        var state = document.getElementById('deliveryManualState')?.value?.trim() || '';
        var zip = document.getElementById('deliveryManualZip')?.value?.trim() || '';
        var country = document.getElementById('deliveryManualCountry')?.value?.trim() || '';
        getAddressSlotIndicesForValidation().forEach(function(idx) {
            toggleAddressSource(idx, 'manual');
            var set = function(suffix, v) { var e = document.getElementById(suffix + idx); if (e) e.value = v || ''; };
            set('addrName_', name);
            set('addrLine1_', line1);
            set('addrLine2_', line2);
            set('addrCity_', city);
            set('addrState_', state);
            set('addrZip_', zip);
            set('addrCountry_', country);
        });
        var m = document.getElementById('deliveryAddressManualModal');
        if (m && typeof bootstrap !== 'undefined') bootstrap.Modal.getInstance(m)?.hide();
        submitSendLetterCheckout();
    }

    function validateSendLetterVariables() {
        const sameDesign = document.getElementById('sameDesign').checked;
        const vars = templateVariables || [];
        for (let i = 0; i < currentForms.length; i++) {
            const form = currentForms[i];
            for (let j = 0; j < vars.length; j++) {
                if (vars[j].required) {
                    const input = document.getElementById(`var_${form.index}_${j}`);
                    if (input && !input.value?.trim()) {
                        alert('Please fill in required field "' + vars[j].name + '" for ' + (sameDesign ? 'all items' : 'item #' + form.index));
                        return false;
                    }
                }
            }
        }
        return true;
    }

    function proceedToCheckout() {
        if (!validateSendLetterVariables()) return;
        if (!allCheckoutAddressesComplete()) {
            openDeliveryAddressChoiceModal();
            return;
        }
        submitSendLetterCheckout();
    }

    function submitSendLetterCheckout() {
        if (!validateSendLetterVariables()) return;
        if (!allCheckoutAddressesComplete()) {
            openDeliveryAddressChoiceModal();
            return;
        }
        const sameDesign = document.getElementById('sameDesign').checked;
        const vars = templateVariables || [];
        let totalQuantity = 0;
        const designs = [];
        if (sameDesign) {
            const values = {};
            vars.forEach((v, vi) => { const i = document.getElementById('var_1_' + vi); if (i) values[v.name] = i.value.trim(); });
            const qty = parseInt(document.getElementById('itemQuantity_1')?.value) || 1;
            totalQuantity = qty;
            for (let i = 1; i <= qty; i++) designs.push({ index: i, variables: { ...values }, quantity: 1 });
        } else {
            currentForms.forEach(form => {
                const values = {};
                vars.forEach((v, vi) => { const inp = document.getElementById('var_' + form.index + '_' + vi); if (inp) values[v.name] = inp.value.trim(); });
                const qty = parseInt(document.getElementById('itemQuantity_' + form.index)?.value) || 1;
                totalQuantity += qty;
                for (let i = 1; i <= qty; i++) designs.push({ index: form.index, instance: i, variables: values, quantity: 1 });
            });
        }
        const envelopeSelect = document.getElementById('envelopeCover');
        const sheetTypeSelect = document.getElementById('printDetailSheetTypeSelect');

        function getAddress(idx) {
            return {
                name: document.getElementById('addrName_' + idx)?.value || '',
                line1: document.getElementById('addrLine1_' + idx)?.value || '',
                line2: document.getElementById('addrLine2_' + idx)?.value || '',
                city: document.getElementById('addrCity_' + idx)?.value || '',
                state: document.getElementById('addrState_' + idx)?.value || '',
                zip: document.getElementById('addrZip_' + idx)?.value || '',
                country: document.getElementById('addrCountry_' + idx)?.value || ''
            };
        }

        const addresses = [];
        const totalQty = parseInt(document.getElementById('totalQuantityDisplay')?.textContent) || parseInt(document.getElementById('quantity')?.value) || 1;
        for (let i = 1; i <= totalQty; i++) {
            addresses.push(getAddress(i));
        }

        const items = [];
        if (sameDesign) {
            const addrCount = parseInt(document.getElementById('totalQuantityDisplay')?.textContent) || parseInt(document.getElementById('quantity')?.value) || 1;
            designs.forEach((d, i) => {
                const addrIdx = addrCount === 1 ? 1 : (i + 1);
                items.push({
                    variables: d.variables,
                    address: getAddress(addrIdx)
                });
            });
        } else {
            designs.forEach((d) => {
                items.push({
                    variables: d.variables,
                    address: getAddress(d.index)
                });
            });
        }

        const checkoutData = {
            quantity: totalQuantity,
            same_design: sameDesign,
            designs: designs,
            items: items,
            envelope_cover: envelopeSelect?.value ?? '',
            envelope_cover_name: envelopeSelect?.options[envelopeSelect?.selectedIndex]?.text || '—',
            addresses: addresses,
            sheet_type: sheetTypeSelect?.value || 'standard',
            sheet_type_name: sheetTypeSelect?.options[sheetTypeSelect?.selectedIndex]?.text || 'Standard',
            total_cost: document.getElementById('totalCost')?.textContent || '0',
            template_cost: document.getElementById('breakdownTemplate')?.textContent || '0',
            envelope_cost: document.getElementById('breakdownEnvelope')?.textContent || '0',
            sheet_cost: document.getElementById('breakdownPagesCost')?.textContent || '0',
            is_letter: true
        };
        const timingSchedule = document.querySelector('input[name="sendTiming"][value="schedule"]');
        if (timingSchedule && timingSchedule.checked) {
            const dt = document.getElementById('scheduleLetterDateTime')?.value;
            if (!dt) {
                alert('Please choose a date and time to schedule your letter.');
                const btn = document.getElementById('checkoutBtn');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Proceed to Checkout'; }
                return;
            }
            if (new Date(dt).getTime() <= Date.now() + 4.5 * 60 * 1000) {
                alert('Please choose a time at least 5 minutes from now.');
                const btn = document.getElementById('checkoutBtn');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Proceed to Checkout'; }
                return;
            }
            checkoutData.schedule_letter = true;
            checkoutData.schedule_send_at = dt;
        }
        const productSelect = document.getElementById('attachedProductSelect');
        if (productSelect && productSelect.value) {
            const opt = productSelect.options[productSelect.selectedIndex];
            checkoutData.product_id = parseInt(productSelect.value, 10);
            checkoutData.product_price = parseFloat(opt.getAttribute('data-product-price')) || 0;
        }
        document.getElementById('checkoutDataInput').value = JSON.stringify(checkoutData);
        const btn = document.getElementById('checkoutBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        document.getElementById('checkoutForm').submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        (function initDeliveryModalProfileBtn() {
            var btn = document.getElementById('deliveryOptProfile');
            var desc = document.getElementById('deliveryOptProfileDesc');
            if (!btn) return;
            if (!profileDelivery || !profileDelivery.ready) {
                btn.disabled = true;
                if (desc) desc.textContent = 'Add your phone and address in Settings to use this option.';
            }
        })();

        var draft = restoreSendLetterDraft();
        if (draft) {
            var qEl = document.getElementById('quantity');
            var sEl = document.getElementById('sameDesign');
            if (qEl && draft.quantity != null) qEl.value = draft.quantity;
            if (sEl && draft.sameDesign != null) sEl.checked = !!draft.sameDesign;
        }
        updateVariableForms();
        updateTotalQuantity();
        calculateTotalCost();
        toggleCsvImportSection();
        playSheetTypeVideo();
        syncAttachedProductTabVisibility();
        if (draft) setTimeout(function() { applySendLetterDraftValues(draft); syncAttachedProductTabVisibility(); }, 80);
        document.getElementById('envelopeCover').addEventListener('change', function() {
            const sel = document.getElementById('printDetailEnvelopeSelect');
            if (sel) sel.value = this.value;
            scheduleSaveSendLetterDraft();
        });
        document.getElementById('printDetailEnvelopeSelect')?.addEventListener('change', function() {
            const sel = document.getElementById('envelopeCover');
            if (sel) sel.value = this.value;
            scheduleSaveSendLetterDraft();
        });
        var container = document.getElementById('variableFormsContainer');
        if (container) {
            container.addEventListener('input', scheduleSaveSendLetterDraft);
            container.addEventListener('change', scheduleSaveSendLetterDraft);
        }
        document.getElementById('quantity')?.addEventListener('input', scheduleSaveSendLetterDraft);
        document.getElementById('quantity')?.addEventListener('change', scheduleSaveSendLetterDraft);
        document.getElementById('sameDesign')?.addEventListener('change', scheduleSaveSendLetterDraft);
        document.getElementById('printDetailSheetTypeSelect')?.addEventListener('change', scheduleSaveSendLetterDraft);
        document.querySelectorAll('input[name="sendTiming"]').forEach(function(r) {
            r.addEventListener('change', function() { toggleScheduleLetterFields(); scheduleSaveSendLetterDraft(); });
        });
        document.getElementById('scheduleLetterDateTime')?.addEventListener('change', scheduleSaveSendLetterDraft);
        updateScheduleLetterMinDate();
        window.addEventListener('beforeunload', saveSendLetterDraft);
    });
</script>
@endpush
