@extends('layouts.app')

@section('title', 'Quick Use Template - ' . $template->name)

@push('styles')
<style>
    :root {
        --primary-color: #6366f1;
        --secondary-color: #8b5cf6;
        --light-bg: #f8fafc;
        --dark-text: #1e293b;
        --border-color: #e2e8f0;
    }

    .quick-use-container {
        min-height: 100vh;
        background: var(--light-bg);
        padding: 1rem 0;
    }

    .quick-use-sidebar {
        background: white;
        border-radius: 8px;
        padding: 0.85rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        height: fit-content;
        position: sticky;
        top: 1rem;
    }

    .quick-use-sidebar h2 {
        font-size: 1rem !important;
        margin-bottom: 0.5rem !important;
    }

    .quick-use-sidebar h4 {
        font-size: 0.9375rem !important;
        margin-bottom: 0.5rem !important;
    }

    .quick-use-sidebar .form-group {
        margin-bottom: 0.5rem;
    }

    .quick-use-sidebar .form-label {
        font-size: 0.75rem !important;
        margin-bottom: 0.2rem;
    }

    .quick-use-sidebar .form-control {
        padding: 0.35rem 0.5rem;
        font-size: 0.8125rem;
    }

    .quick-use-sidebar small {
        font-size: 0.7rem !important;
    }

    .quick-use-content {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        display: flex;
        flex-direction: column;
        height: calc(100vh - 6rem);
        max-height: 800px;
    }

    .form-group {
        margin-bottom: 0.5rem;
    }

    .form-label {
        font-weight: 600;
        color: var(--dark-text);
        margin-bottom: 0.2rem;
        display: block;
        font-size: 0.8125rem;
    }

    .form-control {
        width: 100%;
        padding: 0.4rem 0.6rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.8125rem;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .toggle-switch {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .toggle-switch input[type="checkbox"] {
        width: 36px;
        height: 18px;
        appearance: none;
        background: #cbd5e1;
        border-radius: 12px;
        position: relative;
        cursor: pointer;
        transition: background 0.3s;
    }

    .toggle-switch input[type="checkbox"]:checked {
        background: var(--primary-color);
    }

    .toggle-switch input[type="checkbox"]::before {
        content: '';
        position: absolute;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: white;
        top: 2px;
        left: 2px;
        transition: transform 0.2s;
    }

    .toggle-switch input[type="checkbox"]:checked::before {
        transform: translateX(18px);
    }

    .variable-form-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        margin-bottom: 0.5rem;
        overflow: hidden;
        transition: all 0.2s;
    }

    .variable-form-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
    }

    .variable-form-header {
        font-weight: 700;
        color: var(--dark-text);
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        background: var(--light-bg);
        transition: all 0.2s;
        user-select: none;
    }

    .variable-form-header:hover {
        background: #f1f5f9;
    }

    .variable-form-header-left {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
    }

    .variable-form-header-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .collapse-icon {
        transition: transform 0.2s;
        color: var(--primary-color);
        font-size: 0.75rem;
    }

    .variable-form-card.collapsed .collapse-icon {
        transform: rotate(-90deg);
    }

    .variable-form-body {
        padding: 0.75rem;
        transition: all 0.2s;
        overflow: hidden;
        max-height: 2000px;
        opacity: 1;
    }

    .variable-form-card.collapsed .variable-form-body {
        display: none !important;
        max-height: 0;
        padding: 0;
        opacity: 0;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        border: none;
        padding: 0.45rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8125rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .btn-preview {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .btn-preview:hover {
        box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);
    }

    .btn-expand-collapse {
        background: white;
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .btn-expand-collapse:hover {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 2px 6px rgba(99, 102, 241, 0.3);
    }

    .preview-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
        overflow: auto;
    }

    .preview-modal-content {
        background-color: #1e293b;
        margin: 0;
        padding: 1.5rem;
        border-radius: 0;
        width: 100%;
        height: 100%;
        max-width: 100%;
        box-shadow: none;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        gap: 1rem;
    }

    .preview-header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }

    .preview-header-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .preview-header h3 {
        color: white !important;
        font-size: 1rem !important;
        margin: 0 !important;
        font-weight: 600 !important;
    }

    .preview-tool-btn {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 0.875rem;
    }

    .preview-tool-btn:hover {
        background: rgba(255,255,255,0.2);
        border-color: rgba(255,255,255,0.3);
        transform: translateY(-1px);
    }

    .preview-tool-btn:active {
        transform: translateY(0);
    }

    .preview-tool-btn.active {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .preview-zoom-info {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8125rem;
        font-weight: 600;
        min-width: 60px;
        text-align: center;
    }

    .preview-canvas-container {
        background: #f8fafc;
        border-radius: 8px;
        padding: 1rem;
        display: flex;
        justify-content: center;
        align-items: center;
        flex: 1;
        overflow: auto;
        min-height: 0;
    }

    #previewCanvas {
        max-width: 100%;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-radius: 8px;
        background: white;
    }

    .preview-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 2px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
    }

    .preview-pagination span {
        color: white;
    }

    .tab-container {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .tab-navigation {
        display: flex;
        gap: 0.25rem;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 0.75rem;
    }

    .tab-button {
        padding: 0.4rem 0.75rem;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        color: #64748b;
        font-weight: 600;
        font-size: 0.8125rem;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
        margin-bottom: -2px;
    }

    .tab-button:hover {
        color: var(--primary-color);
        background: rgba(99, 102, 241, 0.05);
    }

    .tab-button.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.05);
    }

    .tab-content {
        display: none;
        flex: 1;
        overflow-y: auto;
        padding-right: 0.5rem;
    }

    .tab-content.active {
        display: block;
    }

    .tab-content::-webkit-scrollbar {
        width: 8px;
    }

    .tab-content::-webkit-scrollbar-track {
        background: var(--light-bg);
        border-radius: 4px;
    }

    .tab-content::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
        transition: background 0.3s;
    }

    .tab-content::-webkit-scrollbar-thumb:hover {
        background: var(--primary-color);
    }

    .print-details-section {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .print-details-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .print-details-card-header {
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--dark-text);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .print-details-card-header i {
        color: var(--primary-color);
        font-size: 0.8rem;
        width: 20px;
        text-align: center;
    }

    .print-details-card-body {
        padding: 1rem;
    }

    .print-details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem 1.5rem;
    }

    .print-details-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.4rem 0;
        font-size: 0.8125rem;
    }

    .print-details-info-row .label {
        color: #64748b;
        font-weight: 500;
    }

    .print-details-info-row .value {
        color: var(--dark-text);
        font-weight: 600;
    }

    .print-details-cost-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        font-size: 0.8125rem;
    }

    .print-details-cost-row .label {
        color: #64748b;
    }

    .print-details-cost-row .value {
        color: var(--dark-text);
        font-weight: 600;
    }

    .print-details-total-row {
        margin-top: 0.5rem;
        padding-top: 0.75rem;
        border-top: 2px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9375rem;
        font-weight: 700;
    }

    .print-details-total-row .value {
        color: var(--primary-color);
        font-size: 1.0625rem;
    }

    .print-details-form-group {
        margin-bottom: 1rem;
    }

    .print-details-form-group:last-child {
        margin-bottom: 0;
    }

    .print-details-form-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--dark-text);
        margin-bottom: 0.35rem;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .print-details-select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.8125rem;
        background: white;
        transition: all 0.2s;
    }

    .print-details-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    .sheet-type-preview-section {
        margin-top: 1.25rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        align-items: start;
    }
    @media (max-width: 768px) {
        .sheet-type-preview-section {
            grid-template-columns: 1fr;
        }
    }
    .print-details-video-wrapper {
        border-radius: 10px;
        overflow: hidden;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        aspect-ratio: 16/10;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .print-details-video-wrapper video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .print-details-video-placeholder {
        padding: 2rem;
        text-align: center;
        color: rgba(255,255,255,0.7);
        font-size: 0.8125rem;
    }

    .print-details-video-placeholder i {
        font-size: 2.5rem;
        display: block;
        margin-bottom: 0.5rem;
        opacity: 0.6;
    }

    .sheet-type-description-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 1rem;
        min-height: 120px;
    }
    .sheet-type-description-card .sheet-type-name {
        font-weight: 700;
        color: var(--dark-text);
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .sheet-type-description-card .sheet-type-name i {
        color: var(--primary-color);
        font-size: 0.8rem;
    }
    .sheet-type-description-card .sheet-type-desc-text {
        font-size: 0.8125rem;
        color: #475569;
        line-height: 1.6;
    }
    .sheet-type-description-placeholder {
        color: #94a3b8;
        font-size: 0.8125rem;
        font-style: italic;
    }

    @media (max-width: 768px) {
        .print-details-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="quick-use-container">
    <div class="container">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="quick-use-sidebar">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-cube me-2" style="color: var(--primary-color);"></i>Total Quantity
                        </label>
                        <input type="number" id="quantity" class="form-control" min="1" value="1" onchange="updateVariableForms(); updateAllItemQuantities(); calculateTotalCost();">
                        <small style="color: #64748b; font-size: 0.7rem; margin-top: 0.2rem; display: block;">
                            Total: <span id="totalQuantityDisplay">1</span> item(s)
                        </small>
                    </div>

                    <div class="form-group">
                        <div class="toggle-switch">
                            <input type="checkbox" id="sameDesign" checked onchange="updateVariableForms()">
                            <label for="sameDesign" class="form-label mb-0" style="cursor: pointer;">
                                Same Design Print
                            </label>
                        </div>
                        <small style="color: #64748b; font-size: 0.7rem; margin-top: 0.3rem; display: block;">
                            If enabled, use the same variable values for all items. If disabled, each item will have its own variable form.
                        </small>
                    </div>

                    <!-- Checkout Form: cost breakdown + submit (delivery form is in Variable Form tab) -->
                    <form id="checkoutForm" action="{{ route('design.checkout.init') }}" method="POST" class="mt-2 pt-2" style="border-top: 1px solid var(--border-color);">
                        @csrf
                        <input type="hidden" name="template_id" value="{{ $template->id }}">
                        <input type="hidden" name="checkout_from" value="quick-use">
                        <input type="hidden" name="checkout_data" id="checkoutDataInput">

                    <!-- Cost Breakdown -->
                    <div class="mt-2 pt-2" style="border-top: 1px solid var(--border-color);">
                        <h4 style="font-weight: 700; color: var(--dark-text); font-size: 0.9375rem; margin-bottom: 0.5rem;">
                            <i class="fas fa-calculator me-1" style="color: var(--primary-color); font-size: 0.8rem;"></i>Cost Breakdown
                        </h4>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem; font-size: 0.75rem; color: #64748b;">
                            <span>Template Cost:</span>
                            <span id="breakdownTemplate">{{ format_price(0) }}</span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem; font-size: 0.75rem; color: #64748b;">
                            <span>Sheet Cost (<span id="breakdownQuantity">1</span> × <span id="breakdownPages">{{ $template->page_count ?? 0 }}</span> pages):</span>
                            <span id="breakdownPagesCost">{{ format_price(0) }}</span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem; font-size: 0.75rem; color: #64748b;">
                            <span>Material Cost:</span>
                            <span id="breakdownMaterial">{{ format_price(0) }}</span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem; font-size: 0.75rem; color: #64748b;">
                            <span>Sheet Type Multiplier:</span>
                            <span><span id="breakdownMultiplier">1.0</span>x</span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--border-color); font-size: 0.875rem; font-weight: 700; color: var(--dark-text);">
                            <span>Total Cost:</span>
                            <span id="totalCost" style="color: var(--primary-color); font-size: 0.9375rem;">{{ format_price(0) }}</span>
                        </div>

                        <button type="button" onclick="proceedToCheckout()" class="btn-primary w-100 mt-2" id="checkoutBtn" style="width: 100%; margin-top: 0.5rem; padding: 0.4rem 0.75rem; font-size: 0.8125rem;">
                            <i class="fas fa-shopping-cart me-2"></i>Checkout
                        </button>
                    </div>
                    </form>

                    <!-- Template Info -->
                    <div class="mt-2 pt-2" style="border-top: 1px solid var(--border-color);">
                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.3rem;">
                            <strong style="color: var(--dark-text);">Template:</strong> {{ $template->name }}
                        </div>
                        @if($template->page_count)
                        <div style="font-size: 0.75rem; color: #64748b;">
                            <strong style="color: var(--dark-text);">Pages:</strong> <span id="templatePageCount">{{ $template->page_count }}</span>
                        </div>
                        @endif
                        @auth
                        @if(auth()->user()->hasRole('admin'))
                        <div class="mt-2">
                            <a href="{{ route('admin.orders') }}" class="btn btn-outline-secondary btn-sm w-100" style="font-size: 0.75rem; padding: 0.35rem 0.5rem;">
                                <i class="fas fa-shopping-cart me-1"></i>View Orders
                            </a>
                        </div>
                        @endif
                        @endauth
                    </div>

                </div>
            </div>

            <!-- Right Content -->
            <div class="col-lg-8">
                <div class="quick-use-content">
                    <div class="tab-container">
                        <!-- Tab Navigation -->
                        <div class="tab-navigation">
                            <button class="tab-button active" onclick="switchTab('variableForm')" id="tabVariableForm">
                                <i class="fas fa-edit me-2"></i>Variable Form
                            </button>
                            <button class="tab-button" onclick="switchTab('printDetails')" id="tabPrintDetails">
                                <i class="fas fa-print me-2"></i>Print Details
                            </button>
                            <button class="tab-button" onclick="switchTab('paperConfiguration')" id="tabPaperConfiguration">
                                <i class="fas fa-layer-group me-2"></i>Paper Configuration
                            </button>
                        </div>

                        <!-- Tab Content: Variable Forms -->
                        <div class="tab-content active" id="variableFormTab">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                                <h3 style="font-weight: 700; color: var(--dark-text); font-size: 1rem; margin: 0;">
                                    <i class="fas fa-edit me-1" style="color: var(--primary-color); font-size: 0.875rem;"></i>Variable Forms
                                </h3>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button onclick="expandAllForms()" class="btn-expand-collapse" id="expandAllBtn" style="display: none;">
                                        <i class="fas fa-chevron-down me-1"></i> Expand All
                                    </button>
                                    <button onclick="collapseAllForms()" class="btn-expand-collapse" id="collapseAllBtn">
                                        <i class="fas fa-chevron-up me-1"></i> Collapse All
                                    </button>
                                </div>
                            </div>

                            <div id="variableFormsContainer">
                                <!-- Variable forms will be generated here -->
                            </div>

                            <!-- Delivery Address (under Variable Form tab) -->
                            <div class="variable-form-card mt-3" id="deliveryAddressCard">
                                <div class="variable-form-header" onclick="toggleDeliveryAddressCollapse()">
                                    <div class="variable-form-header-left">
                                        <i class="fas fa-chevron-down collapse-icon" id="deliveryAddressCollapseIcon"></i>
                                        <i class="fas fa-truck" style="color: var(--primary-color);"></i>
                                        <span>Delivery Address</span>
                                    </div>
                                </div>
                                <div class="variable-form-body" id="deliveryAddressBody">
                                    <div class="form-group">
                                        <label class="form-label">Phone number <span class="text-danger">*</span></label>
                                        <input form="checkoutForm" type="text" name="phone" id="deliveryPhone" class="form-control" placeholder="e.g. +1 234 567 8900" maxlength="32" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="form-label d-block">Address</label>
                                        @if(isset($addresses) && $addresses->isNotEmpty())
                                        <label class="d-flex align-items-center gap-2 mb-1" style="cursor: pointer;">
                                            <input form="checkoutForm" type="radio" name="address_source" value="saved" checked onchange="toggleQuickUseAddressSource('saved')">
                                            <span style="font-size: 0.8125rem;">Select from address book</span>
                                        </label>
                                        @endif
                                        <label class="d-flex align-items-center gap-2 mb-1" style="cursor: pointer;">
                                            <input form="checkoutForm" type="radio" name="address_source" value="manual" {{ (isset($addresses) && $addresses->isEmpty()) ? 'checked' : '' }} onchange="toggleQuickUseAddressSource('manual')">
                                            <span style="font-size: 0.8125rem;">Enter address manually</span>
                                        </label>
                                    </div>
                                    <div id="quickUseSavedAddressBlock" class="form-group" style="{{ (isset($addresses) && $addresses->isEmpty()) ? 'display:none;' : '' }}">
                                        <select form="checkoutForm" name="address_book_id" id="quickUseAddressBookId" class="form-control form-select">
                                            <option value="">— Select an address —</option>
                                            @foreach($addresses ?? [] as $addr)
                                            <option value="{{ $addr->id }}" data-phone="{{ e($addr->phone ?? '') }}">{{ $addr->contact_name }}{{ $addr->label ? ' · ' . $addr->label : '' }} — {{ $addr->full_address }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="quickUseManualAddressFields" style="{{ (isset($addresses) && $addresses->isEmpty()) ? '' : 'display:none;' }}">
                                        <div class="form-group">
                                            <label class="form-label">Contact name</label>
                                            <input form="checkoutForm" type="text" name="contact_name" class="form-control" maxlength="255">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Email</label>
                                            <input form="checkoutForm" type="email" name="email" class="form-control" maxlength="255">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Address line 1</label>
                                            <input form="checkoutForm" type="text" name="address_line1" class="form-control" maxlength="255">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Address line 2</label>
                                            <input form="checkoutForm" type="text" name="address_line2" class="form-control" maxlength="255">
                                        </div>
                                        <div class="row g-1">
                                            <div class="col-6 form-group">
                                                <label class="form-label">City</label>
                                                <input form="checkoutForm" type="text" name="city" class="form-control" maxlength="64">
                                            </div>
                                            <div class="col-6 form-group">
                                                <label class="form-label">State</label>
                                                <input form="checkoutForm" type="text" name="state" class="form-control" maxlength="64">
                                            </div>
                                        </div>
                                        <div class="row g-1">
                                            <div class="col-6 form-group">
                                                <label class="form-label">Postal code</label>
                                                <input form="checkoutForm" type="text" name="postal_code" class="form-control" maxlength="20">
                                            </div>
                                            <div class="col-6 form-group">
                                                <label class="form-label">Country</label>
                                                <input form="checkoutForm" type="text" name="country" class="form-control" maxlength="2" placeholder="US">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content: Print Details -->
                        <div class="tab-content" id="printDetailsTab">
                            <div class="print-details-section">
                                <!-- Order Summary Card -->
                                <div class="print-details-card">
                                    <div class="print-details-card-header">
                                        <i class="fas fa-file-alt"></i>
                                        Order Summary
                                    </div>
                                    <div class="print-details-card-body">
                                        <div class="print-details-grid">
                                            <div class="print-details-info-row">
                                                <span class="label">Template</span>
                                                <span class="value">{{ $template->name }}</span>
                                            </div>
                                            <div class="print-details-info-row">
                                                <span class="label">Pages</span>
                                                <span class="value" id="printDetailPages">{{ $template->page_count ?? 0 }}</span>
                                            </div>
                                            <div class="print-details-info-row">
                                                <span class="label">Quantity</span>
                                                <span class="value" id="printDetailQuantity">1</span>
                                            </div>
                                            <div class="print-details-info-row">
                                                <span class="label">Sheet Type</span>
                                                <span class="value" id="printDetailSheetType">Standard (1.0x)</span>
                                            </div>
                                        </div>
                                        <div class="print-details-cost-row" style="margin-top: 0.75rem;">
                                            <span class="label">Template Cost</span>
                                            <span class="value" id="printDetailTemplateCost">{{ format_price(0) }}</span>
                                        </div>
                                        <div class="print-details-cost-row">
                                            <span class="label">Sheet Cost (<span id="printDetailQuantityBreakdown">1</span> × <span id="printDetailPagesBreakdown">{{ $template->page_count ?? 0 }}</span> pages)</span>
                                            <span class="value" id="printDetailPagesCost">{{ format_price(0) }}</span>
                                        </div>
                                        <div class="print-details-cost-row">
                                            <span class="label">Material Cost</span>
                                            <span class="value" id="printDetailMaterialCost">{{ format_price(0) }}</span>
                                        </div>
                                        <div class="print-details-total-row">
                                            <span class="label">Total</span>
                                            <span class="value" id="printDetailTotalCost">{{ format_price(0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content: Paper Configuration -->
                        <div class="tab-content" id="paperConfigurationTab">
                            <div class="print-details-section">
                                <div class="print-details-card">
                                    <div class="print-details-card-header">
                                        <i class="fas fa-layer-group"></i>
                                        Paper Configuration
                                    </div>
                                    <div class="print-details-card-body">
                                        <div class="print-details-grid">
                                            <div class="print-details-form-group">
                                                <label class="print-details-form-label">Sheet Type</label>
                                                <select id="printDetailSheetTypeSelect" class="print-details-select" onchange="calculateTotalCost(); playSheetTypeVideo();">
                                                    @forelse($sheetTypes ?? [] as $sheetType)
                                                        <option value="{{ $sheetType->slug }}" data-multiplier="{{ $sheetType->multiplier }}">{{ $sheetType->name }} ({{ number_format($sheetType->multiplier, 2) }}x)</option>
                                                    @empty
                                                        <option value="" disabled selected>No sheet types in stock</option>
                                                    @endforelse
                                                </select>
                                            </div>
                                            <div class="print-details-form-group">
                                                <label class="print-details-form-label">Material Type</label>
                                                <select id="printDetailMaterialTypeSelect" class="print-details-select" onchange="calculateTotalCost()">
                                                    <option value="paper" data-cost="0">Paper (Standard)</option>
                                                    <option value="cardstock" data-cost="0.10">Cardstock (+$0.10/page)</option>
                                                    <option value="photo" data-cost="0.25">Photo Paper (+$0.25/page)</option>
                                                    <option value="premium" data-cost="0.50">Premium Paper (+$0.50/page)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="print-details-info-row" style="margin-top: 0.5rem;">
                                            <span class="label">Selected Material</span>
                                            <span class="value" id="printDetailMaterial">Paper (Standard)</span>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="preview-modal" onclick="if(event.target === this) closePreview()">
    <div class="preview-modal-content" onclick="event.stopPropagation();">
        <div class="preview-header">
            <div class="preview-header-left">
                <h3>
                    <i class="fas fa-eye me-2" style="color: var(--primary-color);"></i>Design Preview
                </h3>
                
                <div style="height: 24px; width: 1px; background: rgba(255,255,255,0.2);"></div>
                
                <!-- Zoom Controls -->
                <button class="preview-tool-btn" onclick="zoomOutPreview()" title="Zoom Out">
                    <i class="fas fa-search-minus"></i>
                </button>
                <span class="preview-zoom-info" id="zoomLevel">100%</span>
                <button class="preview-tool-btn" onclick="zoomInPreview()" title="Zoom In">
                    <i class="fas fa-search-plus"></i>
                </button>
                <button class="preview-tool-btn" onclick="resetZoomPreview()" title="Reset Zoom">
                    <i class="fas fa-expand-arrows-alt"></i>
                </button>
                
                <div style="height: 24px; width: 1px; background: rgba(255,255,255,0.2);"></div>
                
                <!-- Fit Options -->
                <button class="preview-tool-btn" onclick="fitToScreenPreview()" title="Fit to Screen">
                    <i class="fas fa-compress-arrows-alt"></i>
                </button>
                <button class="preview-tool-btn" onclick="actualSizePreview()" title="Actual Size">
                    <i class="fas fa-arrows-alt"></i>
                </button>
            </div>
            
            <div class="preview-header-right">
                <button class="preview-tool-btn" onclick="closePreview()" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="preview-canvas-container">
            <canvas id="previewCanvas"></canvas>
        </div>

        <div class="preview-pagination" id="previewPagination" style="display: none;">
            <button onclick="previousPreviewPage()" class="btn-preview" id="prevPageBtn">
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            <span id="pageIndicator" style="font-weight: 600; color: var(--dark-text);">Page 1 of 1</span>
            <button onclick="nextPreviewPage()" class="btn-preview" id="nextPageBtn">
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Fabric.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>

<script>
    const templateVariables = @json($template->variables ?? []);
    const templatePages = @json($template->pages ?? []);
    const templatePrice = parseFloat({{ $template->price ?? 0 }});
    const templatePageCount = {{ $template->page_count ?? 0 }};
    // Sheet types with prices
    @php
        $sheetTypesArray = [];
        if (isset($sheetTypes) && $sheetTypes) {
            foreach ($sheetTypes as $st) {
                $sheetTypesArray[$st->slug] = [
                    'slug' => $st->slug,
                    'name' => $st->name,
                    'multiplier' => (float)$st->multiplier,
                    'price_per_sheet' => (float)$st->price_per_sheet,
                    'video_url' => $st->video_url,
                    'image_url' => $st->image_url,
                    'description' => $st->description ?? ''
                ];
            }
        }
    @endphp
    const sheetTypesData = @json($sheetTypesArray);
    const pricingRulesData = @json($pricingRules ?? []);
    const currencySymbol = @json(\App\Models\Setting::get('currency_symbol') ?: '$');
    const currencyDecimals = parseInt(@json(\App\Models\Setting::get('price_decimal_places') ?: 2), 10);
    function formatPrice(amount) {
        return (currencySymbol || '$') + parseFloat(amount ?? 0).toFixed(currencyDecimals ?? 2);
    }

    const quickUseTemplateId = {{ $template->id }};
    const QUICK_USE_DRAFT_KEY = 'quickUse_draft_' + quickUseTemplateId;
    let quickUseSaveDraftTimeout = null;

    function saveQuickUseDraft() {
        try {
            const quantity = parseInt(document.getElementById('quantity')?.value) || 1;
            const sameDesign = document.getElementById('sameDesign')?.checked ?? true;
            const sheetTypeSelect = document.getElementById('printDetailSheetTypeSelect');
            const materialTypeSelect = document.getElementById('printDetailMaterialTypeSelect');
            const itemQuantities = {};
            const variables = {};
            currentForms.forEach(function(form) {
                const qtyEl = document.getElementById('itemQuantity_' + form.index);
                if (qtyEl) itemQuantities[form.index] = parseInt(qtyEl.value) || 1;
                (templateVariables || []).forEach(function(v, vi) {
                    const el = document.getElementById('var_' + form.index + '_' + vi);
                    if (el) variables[form.index + '_' + vi] = el.value || '';
                });
            });
            const draft = {
                quantity: quantity,
                sameDesign: sameDesign,
                sheet_type: sheetTypeSelect?.value || 'standard',
                material_type: materialTypeSelect?.value || 'paper',
                itemQuantities: itemQuantities,
                variables: variables
            };
            sessionStorage.setItem(QUICK_USE_DRAFT_KEY, JSON.stringify(draft));
        } catch (e) { console.warn('saveQuickUseDraft:', e); }
    }

    function scheduleSaveQuickUseDraft() {
        clearTimeout(quickUseSaveDraftTimeout);
        quickUseSaveDraftTimeout = setTimeout(saveQuickUseDraft, 500);
    }

    function restoreQuickUseDraft() {
        try {
            const raw = sessionStorage.getItem(QUICK_USE_DRAFT_KEY);
            if (!raw) return false;
            const draft = JSON.parse(raw);
            const quantityEl = document.getElementById('quantity');
            const sameDesignEl = document.getElementById('sameDesign');
            if (quantityEl && draft.quantity != null) quantityEl.value = draft.quantity;
            if (sameDesignEl && draft.sameDesign != null) sameDesignEl.checked = !!draft.sameDesign;
            return draft;
        } catch (e) { return null; }
    }

    function applyQuickUseDraftValues(draft) {
        if (!draft) return;
        try {
            const sheetTypeSelect = document.getElementById('printDetailSheetTypeSelect');
            const materialTypeSelect = document.getElementById('printDetailMaterialTypeSelect');
            if (sheetTypeSelect && draft.sheet_type && draft.sheet_type !== sheetTypeSelect.value) {
                sheetTypeSelect.value = draft.sheet_type;
            }
            if (materialTypeSelect && draft.material_type && draft.material_type !== materialTypeSelect.value) {
                materialTypeSelect.value = draft.material_type;
            }
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
            updateTotalQuantity();
            calculateTotalCost();
        } catch (e) { console.warn('applyQuickUseDraftValues:', e); }
    }

    let currentForms = [];
    let previewCanvas = null;
    let previewPages = [];
    let currentPreviewPageIndex = 0;
    let currentZoom = 1.0;
    let canvasOriginalScale = 1.0;

    // Pricing calculation
    function calculateTotalCost() {
        // Calculate total quantity from individual item quantities
        let totalQuantity = 0;
        currentForms.forEach(form => {
            const qtyInput = document.getElementById(`itemQuantity_${form.index}`);
            if (qtyInput) {
                totalQuantity += parseInt(qtyInput.value) || 1;
            }
        });
        
        // Use total quantity for cost calculation
        const quantity = totalQuantity || parseInt(document.getElementById('quantity').value) || 1;
        // Get sheet type and material from Print Details tab
        const sheetTypeSelect = document.getElementById('printDetailSheetTypeSelect');
        const materialTypeSelect = document.getElementById('printDetailMaterialTypeSelect');
        
        // Default values if selects don't exist
        let sheetTypeSlug = 'standard';
        let sheetTypePrice = 0.50; // Default price per sheet
        let sheetTypeMultiplier = 1.0;
        let materialCostPerPage = 0;
        let sheetTypeText = 'Standard (1.0x)';
        let materialText = 'Paper (Standard)';
        
        if (sheetTypeSelect && sheetTypeSelect.value) {
            sheetTypeSlug = sheetTypeSelect.value;
            sheetTypeText = sheetTypeSelect.options[sheetTypeSelect.selectedIndex].text;
            
            // Get sheet type data from database
            if (sheetTypesData && sheetTypesData[sheetTypeSlug]) {
                const sheetTypeData = sheetTypesData[sheetTypeSlug];
                sheetTypePrice = sheetTypeData.price_per_sheet || 0.50;
                sheetTypeMultiplier = sheetTypeData.multiplier || 1.0;
            } else {
                // Fallback to data attribute if sheetTypesData not available
                sheetTypeMultiplier = parseFloat(sheetTypeSelect.options[sheetTypeSelect.selectedIndex].getAttribute('data-multiplier')) || 1.0;
                sheetTypePrice = 0.50; // Default fallback
            }
        }
        
        if (materialTypeSelect) {
            materialCostPerPage = parseFloat(materialTypeSelect.options[materialTypeSelect.selectedIndex].getAttribute('data-cost')) || 0;
            materialText = materialTypeSelect.options[materialTypeSelect.selectedIndex].text;
        }
        
        // Design type: same_design or mixed_designs (for pricing rules)
        const sameDesign = document.getElementById('sameDesign')?.checked ?? true;
        const designType = sameDesign ? 'same_design' : 'mixed_designs';
        
        // Base sheet cost = price_per_sheet * page_count * quantity
        let sheetCost = sheetTypePrice * templatePageCount * quantity;
        
        // Apply pricing rule discount (first matching rule wins, rules ordered by sort_order desc)
        if (pricingRulesData && pricingRulesData.length > 0) {
            for (let i = 0; i < pricingRulesData.length; i++) {
                const rule = pricingRulesData[i];
                const sheetMatch = !rule.sheet_type_slug || rule.sheet_type_slug === sheetTypeSlug;
                const qtyMinOk = quantity >= (rule.min_quantity || 1);
                const qtyMaxOk = !rule.max_quantity || quantity <= rule.max_quantity;
                const designMatch = rule.applies_to_design === 'any' || rule.applies_to_design === designType;
                if (sheetMatch && qtyMinOk && qtyMaxOk && designMatch) {
                    const discount = parseFloat(rule.discount_percent || 0) / 100;
                    sheetCost *= (1 - discount);
                    break;
                }
            }
        }
        
        // Template cost is charged once per order (not per quantity: e.g. 5 qty = 1× template, not 5×)
        const templateCost = templatePrice;
        const materialCost = materialCostPerPage * templatePageCount * quantity;
        
        // Total cost = template (once) + sheet + material
        const totalCost = templateCost + sheetCost + materialCost;
        
        // Update breakdown display
        document.getElementById('breakdownTemplate').textContent = formatPrice(templateCost);
        document.getElementById('breakdownQuantity').textContent = quantity;
        document.getElementById('breakdownPages').textContent = templatePageCount;
        document.getElementById('breakdownPagesCost').textContent = formatPrice(sheetCost);
        document.getElementById('breakdownMaterial').textContent = formatPrice(materialCost);
        document.getElementById('breakdownMultiplier').textContent = sheetTypeMultiplier.toFixed(2);
        document.getElementById('totalCost').textContent = formatPrice(totalCost);

        // Update print details tab
        if (document.getElementById('printDetailQuantity')) {
            document.getElementById('printDetailQuantity').textContent = quantity;
            if (document.getElementById('printDetailQuantityBreakdown')) {
                document.getElementById('printDetailQuantityBreakdown').textContent = quantity;
            }
            document.getElementById('printDetailPages').textContent = templatePageCount;
            if (document.getElementById('printDetailPagesBreakdown')) {
                document.getElementById('printDetailPagesBreakdown').textContent = templatePageCount;
            }
            document.getElementById('printDetailSheetType').textContent = sheetTypeText;
            document.getElementById('printDetailMaterial').textContent = materialText;
            if (document.getElementById('printDetailTemplateCost')) {
                document.getElementById('printDetailTemplateCost').textContent = formatPrice(templateCost);
            }
            if (document.getElementById('printDetailPagesCost')) {
                document.getElementById('printDetailPagesCost').textContent = formatPrice(sheetCost);
            }
            if (document.getElementById('printDetailMaterialCost')) {
                document.getElementById('printDetailMaterialCost').textContent = formatPrice(materialCost);
            }
            if (document.getElementById('printDetailMultiplier')) {
                document.getElementById('printDetailMultiplier').textContent = sheetTypeMultiplier.toFixed(2);
            }
            if (document.getElementById('printDetailTotalCost')) {
                document.getElementById('printDetailTotalCost').textContent = formatPrice(totalCost);
            }
        }
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

        // Update video
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

        // Update name and description
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
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected tab
        if (tabName === 'variableForm') {
            document.getElementById('variableFormTab').classList.add('active');
            document.getElementById('tabVariableForm').classList.add('active');
        } else if (tabName === 'printDetails') {
            document.getElementById('printDetailsTab').classList.add('active');
            document.getElementById('tabPrintDetails').classList.add('active');
        } else if (tabName === 'paperConfiguration') {
            document.getElementById('paperConfigurationTab').classList.add('active');
            document.getElementById('tabPaperConfiguration').classList.add('active');
            playSheetTypeVideo();
        }
    }

    function updateVariableForms() {
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const sameDesign = document.getElementById('sameDesign').checked;
        const container = document.getElementById('variableFormsContainer');
        
        // Store current item quantities before clearing
        const currentItemQuantities = {};
        currentForms.forEach(form => {
            const qtyInput = document.getElementById(`itemQuantity_${form.index}`);
            if (qtyInput) {
                currentItemQuantities[form.index] = parseInt(qtyInput.value) || 1;
            }
        });
        
        container.innerHTML = '';
        currentForms = [];

        if (sameDesign) {
            // Show one form for all items
            const formHtml = generateVariableForm(1, true);
            container.innerHTML = formHtml;
            currentForms.push({ index: 1, isShared: true });
            // Restore quantity if it existed
            const qtyInput = document.getElementById('itemQuantity_1');
            if (qtyInput && currentItemQuantities[1]) {
                qtyInput.value = currentItemQuantities[1];
            }
        } else {
            // Show form for each quantity
            for (let i = 1; i <= quantity; i++) {
                const formHtml = generateVariableForm(i, false);
                container.innerHTML += formHtml;
                currentForms.push({ index: i, isShared: false });
                // Restore quantity if it existed
                const qtyInput = document.getElementById(`itemQuantity_${i}`);
                if (qtyInput && currentItemQuantities[i]) {
                    qtyInput.value = currentItemQuantities[i];
                }
            }
        }
        
        // Update expand/collapse button visibility
        setTimeout(updateExpandCollapseButtons, 100);
        // Update total quantity display
        updateTotalQuantity();
    }
    
    function updateTotalQuantity() {
        const sameDesign = document.getElementById('sameDesign').checked;
        let total = 0;
        
        if (sameDesign) {
            // When same design is enabled, use main quantity field
            const mainQuantity = document.getElementById('quantity');
            total = mainQuantity ? parseInt(mainQuantity.value) || 1 : 1;
        } else {
            // When same design is disabled, sum all item quantities
            currentForms.forEach(form => {
                const qtyInput = document.getElementById(`itemQuantity_${form.index}`);
                if (qtyInput) {
                    total += parseInt(qtyInput.value) || 1;
                }
            });
        }
        
        // Update total quantity display
        const totalDisplay = document.getElementById('totalQuantityDisplay');
        if (totalDisplay) {
            totalDisplay.textContent = total;
        }
        
        // Update main quantity field to match total (only if not same design)
        if (!sameDesign) {
            const mainQuantity = document.getElementById('quantity');
            if (mainQuantity) {
                mainQuantity.value = total;
            }
        }
    }
    
    function updateAllItemQuantities() {
        const mainQuantity = parseInt(document.getElementById('quantity').value) || 1;
        const sameDesign = document.getElementById('sameDesign').checked;
        
        if (sameDesign) {
            // If same design, set the single item quantity to main quantity
            const qtyInput = document.getElementById('itemQuantity_1');
            if (qtyInput) {
                qtyInput.value = mainQuantity;
            }
        } else {
            // Distribute quantity evenly across all items
            const itemCount = currentForms.length;
            if (itemCount > 0) {
                const quantityPerItem = Math.floor(mainQuantity / itemCount);
                const remainder = mainQuantity % itemCount;
                
                currentForms.forEach((form, index) => {
                    const qtyInput = document.getElementById(`itemQuantity_${form.index}`);
                    if (qtyInput) {
                        // Distribute remainder to first items
                        qtyInput.value = quantityPerItem + (index < remainder ? 1 : 0);
                    }
                });
            }
        }
        
        updateTotalQuantity();
    }

    function generateVariableForm(index, isShared) {
        const variableCount = templateVariables ? templateVariables.length : 0;
        
        if (!templateVariables || templateVariables.length === 0) {
            return `
                <div class="variable-form-card" data-form-index="${index}">
                    <div class="variable-form-header" onclick="toggleFormCollapse(${index})">
                        <div class="variable-form-header-left">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                            <i class="fas fa-info-circle" style="color: #3b82f6;"></i>
                            <span>${isShared ? 'Shared Variables (Applied to All Items)' : `Item #${index}`}</span>
                        </div>
                    <div class="variable-form-header-right">
                        ${!isShared ? `
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-right: 0.75rem;">
                            <label style="font-size: 0.75rem; color: #64748b; font-weight: 600; white-space: nowrap; margin: 0;">
                                <i class="fas fa-cube me-1" style="color: var(--primary-color);"></i>Qty:
                            </label>
                            <input type="number" id="itemQuantity_${index}" class="form-control" min="1" value="1" onchange="event.stopPropagation(); updateTotalQuantity(); calculateTotalCost();" style="width: 70px; padding: 0.25rem 0.5rem; font-size: 0.8125rem; text-align: center;">
                        </div>
                        ` : ''}
                        <button onclick="event.stopPropagation(); previewDesign(${index})" class="btn-preview">
                            <i class="fas fa-eye"></i> Preview
                        </button>
                    </div>
                </div>
                <div class="variable-form-body">
                        <p style="color: #64748b; margin: 0;">No variables found in this template.</p>
                    </div>
                </div>
            `;
        }

        let formHtml = `
            <div class="variable-form-card" data-form-index="${index}">
                <div class="variable-form-header" onclick="toggleFormCollapse(${index})">
                    <div class="variable-form-header-left">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                        <i class="fas fa-edit" style="color: var(--primary-color);"></i>
                        <span>${isShared ? 'Shared Variables (Applied to All Items)' : `Item #${index}`}</span>
                        <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary-color); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;">
                            ${variableCount} variable${variableCount !== 1 ? 's' : ''}
                        </span>
                    </div>
                    <div class="variable-form-header-right">
                        ${!isShared ? `
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-right: 0.75rem;">
                            <label style="font-size: 0.75rem; color: #64748b; font-weight: 600; white-space: nowrap; margin: 0;">
                                <i class="fas fa-cube me-1" style="color: var(--primary-color);"></i>Qty:
                            </label>
                            <input type="number" id="itemQuantity_${index}" class="form-control" min="1" value="1" onchange="event.stopPropagation(); updateTotalQuantity(); calculateTotalCost();" style="width: 70px; padding: 0.25rem 0.5rem; font-size: 0.8125rem; text-align: center;">
                        </div>
                        ` : ''}
                        <button onclick="event.stopPropagation(); previewDesign(${index})" class="btn-preview">
                            <i class="fas fa-eye"></i> Preview
                        </button>
                    </div>
                </div>
                <div class="variable-form-body">
        `;

        templateVariables.forEach((variable, varIndex) => {
            const varId = `var_${index}_${varIndex}`;
            const varName = variable.name || 'variable';
            
            formHtml += `
                <div class="form-group">
                    <label class="form-label">
                        ${varName} ${variable.required ? '<span style="color: #ef4444;">*</span>' : ''}
                    </label>
            `;

            if (variable.form_type === 'select' && variable.options && variable.options.length > 0) {
                formHtml += `
                    <select id="${varId}" class="form-control" ${variable.required ? 'required' : ''}>
                        <option value="">Select ${varName}</option>
                `;
                variable.options.forEach(option => {
                    formHtml += `<option value="${option}">${option}</option>`;
                });
                formHtml += `</select>`;
            } else if (variable.form_type === 'textarea') {
                const maxLength = variable.length || 1000;
                formHtml += `
                    <textarea id="${varId}" class="form-control" rows="3" maxlength="${maxLength}" ${variable.required ? 'required' : ''} placeholder="Enter ${varName}"></textarea>
                    <small style="color: #64748b; font-size: 0.75rem;">Max ${maxLength} characters</small>
                `;
            } else {
                const maxLength = variable.length || 255;
                formHtml += `
                    <input type="text" id="${varId}" class="form-control" maxlength="${maxLength}" ${variable.required ? 'required' : ''} placeholder="Enter ${varName}">
                `;
            }

            formHtml += `</div>`;
        });

        formHtml += `
                </div>
            </div>
        `;
        return formHtml;
    }

    function toggleFormCollapse(index) {
        const card = document.querySelector(`.variable-form-card[data-form-index="${index}"]`);
        if (card) {
            const isCollapsed = card.classList.contains('collapsed');
            if (isCollapsed) {
                card.classList.remove('collapsed');
            } else {
                card.classList.add('collapsed');
            }
            updateExpandCollapseButtons();
        }
    }

    function toggleDeliveryAddressCollapse() {
        const card = document.getElementById('deliveryAddressCard');
        if (card) card.classList.toggle('collapsed');
    }

    function collapseAllForms() {
        const container = document.getElementById('variableFormsContainer');
        if (!container) return;
        
        const cards = container.querySelectorAll('.variable-form-card');
        cards.forEach(card => {
            if (!card.classList.contains('collapsed')) {
                card.classList.add('collapsed');
            }
        });
        updateExpandCollapseButtons();
    }

    function expandAllForms() {
        const container = document.getElementById('variableFormsContainer');
        if (!container) return;
        
        const cards = container.querySelectorAll('.variable-form-card');
        cards.forEach(card => {
            if (card.classList.contains('collapsed')) {
                card.classList.remove('collapsed');
            }
        });
        updateExpandCollapseButtons();
    }

    function updateExpandCollapseButtons() {
        const container = document.getElementById('variableFormsContainer');
        if (!container) return;
        
        const cards = container.querySelectorAll('.variable-form-card');
        const collapsedCards = container.querySelectorAll('.variable-form-card.collapsed');
        const expandAllBtn = document.getElementById('expandAllBtn');
        const collapseAllBtn = document.getElementById('collapseAllBtn');

        if (!expandAllBtn || !collapseAllBtn) return;

        if (cards.length === 0) {
            expandAllBtn.style.display = 'none';
            collapseAllBtn.style.display = 'none';
            return;
        }

        if (collapsedCards.length === 0) {
            // All expanded
            expandAllBtn.style.display = 'none';
            collapseAllBtn.style.display = 'inline-flex';
        } else if (collapsedCards.length === cards.length) {
            // All collapsed
            expandAllBtn.style.display = 'inline-flex';
            collapseAllBtn.style.display = 'none';
        } else {
            // Mixed state - show both
            expandAllBtn.style.display = 'inline-flex';
            collapseAllBtn.style.display = 'inline-flex';
        }
    }

    function previewDesign(itemIndex) {
        // Collect variable values for this item
        const variableValues = {};
        templateVariables.forEach((variable, varIndex) => {
            const varId = `var_${itemIndex}_${varIndex}`;
            const input = document.getElementById(varId);
            if (input) {
                variableValues[variable.name] = input.value.trim();
            }
        });

        // Replace variables in template pages
        previewPages = templatePages.map(pageData => {
            try {
                let page = typeof pageData === 'string' ? JSON.parse(pageData) : pageData;
                
                // Replace variables in text objects
                if (page.objects && Array.isArray(page.objects)) {
                    page.objects = page.objects.map(obj => {
                        if (obj.type === 'text' || obj.type === 'i-text' || obj.type === 'textbox') {
                            let text = obj.text || '';
                            // Replace variable placeholders with actual values
                            templateVariables.forEach(variable => {
                                const regex = new RegExp('\\{\\{' + variable.name + '\\}\\}', 'g');
                                const value = variableValues[variable.name] || '';
                                text = text.replace(regex, value);
                            });
                            obj.text = text;
                        }
                        return obj;
                    });
                }
                
                return JSON.stringify(page);
            } catch (e) {
                console.error('Error processing page:', e);
                return pageData;
            }
        });

        // Initialize preview canvas if not exists
        if (!previewCanvas) {
            previewCanvas = new fabric.Canvas('previewCanvas', {
                preserveObjectStacking: true,
                backgroundColor: '#ffffff'
            });
        }
        
        // Reset zoom when opening preview
        currentZoom = 1.0;

        // Show modal
        document.getElementById('previewModal').style.display = 'block';
        
        // Load first page
        currentPreviewPageIndex = 0;
        loadPreviewPage(0);
    }

    function loadPreviewPage(pageIndex) {
        if (!previewCanvas || !previewPages || previewPages.length === 0) return;
        
        if (pageIndex < 0 || pageIndex >= previewPages.length) return;

        const pageData = previewPages[pageIndex];
        try {
            const parsed = typeof pageData === 'string' ? JSON.parse(pageData) : pageData;
            
            // Set canvas size
            const width = parsed.width || 800;
            const height = parsed.height || 1000;
            
            // Store original dimensions for zoom calculations
            canvasOriginalScale = 1.0;
            
            // Scale canvas to fit container while maintaining aspect ratio (base scale)
            const container = document.querySelector('.preview-canvas-container');
            const maxWidth = container ? container.clientWidth - 40 : 800;
            const maxHeight = container ? container.clientHeight - 40 : 800;
            
            let baseScale = 1;
            if (width > maxWidth) {
                baseScale = maxWidth / width;
            }
            if (height * baseScale > maxHeight) {
                baseScale = maxHeight / height;
            }
            
            // Apply current zoom to base scale
            const finalScale = baseScale * currentZoom;
            const scaledWidth = width * finalScale;
            const scaledHeight = height * finalScale;
            
            previewCanvas.setDimensions({ 
                width: scaledWidth, 
                height: scaledHeight 
            });
            previewCanvas.setBackgroundColor(parsed.backgroundColor || '#ffffff', previewCanvas.renderAll.bind(previewCanvas));
            
            // Load objects with final scale
            previewCanvas.loadFromJSON(parsed, function() {
                // Scale all objects to fit the preview with current zoom
                previewCanvas.getObjects().forEach(obj => {
                    obj.scaleX = obj.scaleX * finalScale;
                    obj.scaleY = obj.scaleY * finalScale;
                    obj.left = obj.left * finalScale;
                    obj.top = obj.top * finalScale;
                });
                previewCanvas.renderAll();
            });

            // Update pagination
            updatePreviewPagination();
            
            // Update zoom level display
            const zoomLevel = document.getElementById('zoomLevel');
            if (zoomLevel) {
                zoomLevel.textContent = Math.round(currentZoom * 100) + '%';
            }
        } catch (e) {
            console.error('Error loading preview page:', e);
        }
    }

    function updatePreviewPagination() {
        const pagination = document.getElementById('previewPagination');
        const pageIndicator = document.getElementById('pageIndicator');
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');

        if (previewPages.length > 1) {
            pagination.style.display = 'flex';
            pageIndicator.textContent = `Page ${currentPreviewPageIndex + 1} of ${previewPages.length}`;
            prevBtn.disabled = currentPreviewPageIndex === 0;
            nextBtn.disabled = currentPreviewPageIndex === previewPages.length - 1;
        } else {
            pagination.style.display = 'none';
        }
    }

    function previousPreviewPage() {
        if (currentPreviewPageIndex > 0) {
            currentPreviewPageIndex--;
            loadPreviewPage(currentPreviewPageIndex);
        }
    }

    function nextPreviewPage() {
        if (currentPreviewPageIndex < previewPages.length - 1) {
            currentPreviewPageIndex++;
            loadPreviewPage(currentPreviewPageIndex);
        }
    }

    function closePreview() {
        document.getElementById('previewModal').style.display = 'none';
        if (previewCanvas) {
            previewCanvas.clear();
        }
        // Reset zoom when closing
        currentZoom = 1.0;
    }

    // Zoom functions
    function zoomInPreview() {
        if (!previewCanvas) return;
        currentZoom = Math.min(currentZoom * 1.2, 5.0); // Max 500% zoom
        applyZoom();
    }

    function zoomOutPreview() {
        if (!previewCanvas) return;
        currentZoom = Math.max(currentZoom / 1.2, 0.1); // Min 10% zoom
        applyZoom();
    }

    function resetZoomPreview() {
        if (!previewCanvas) return;
        currentZoom = 1.0;
        applyZoom();
    }

    function fitToScreenPreview() {
        if (!previewCanvas || previewPages.length === 0) return;
        
        const container = document.querySelector('.preview-canvas-container');
        if (!container) return;
        
        const pageData = previewPages[currentPreviewPageIndex];
        try {
            const parsed = typeof pageData === 'string' ? JSON.parse(pageData) : pageData;
            const width = parsed.width || 800;
            const height = parsed.height || 1000;
            
            const maxWidth = container.clientWidth - 40;
            const maxHeight = container.clientHeight - 40;
            
            const scaleX = maxWidth / width;
            const scaleY = maxHeight / height;
            currentZoom = Math.min(scaleX, scaleY);
            
            applyZoom();
        } catch (e) {
            console.error('Error fitting to screen:', e);
        }
    }

    function actualSizePreview() {
        if (!previewCanvas) return;
        currentZoom = 1.0;
        applyZoom();
    }

    function applyZoom() {
        if (!previewCanvas || previewPages.length === 0) return;
        
        const pageData = previewPages[currentPreviewPageIndex];
        try {
            const parsed = typeof pageData === 'string' ? JSON.parse(pageData) : pageData;
            const width = parsed.width || 800;
            const height = parsed.height || 1000;
            
            const scaledWidth = width * currentZoom;
            const scaledHeight = height * currentZoom;
            
            previewCanvas.setDimensions({ 
                width: scaledWidth, 
                height: scaledHeight 
            });
            
            // Update zoom level display
            const zoomLevel = document.getElementById('zoomLevel');
            if (zoomLevel) {
                zoomLevel.textContent = Math.round(currentZoom * 100) + '%';
            }
            
            // Reload the page content with new zoom
            loadPreviewPage(currentPreviewPageIndex);
        } catch (e) {
            console.error('Error applying zoom:', e);
        }
    }

    function proceedToCheckout() {
        const sameDesign = document.getElementById('sameDesign').checked;
        const checkoutBtn = document.getElementById('checkoutBtn');

        // Validate delivery: phone required; if saved address, must select one
        const phoneEl = document.getElementById('deliveryPhone');
        if (!phoneEl || !phoneEl.value.trim()) {
            alert('Please enter your phone number.');
            if (phoneEl) phoneEl.focus();
            return;
        }
        const addressSource = document.querySelector('input[name="address_source"]:checked');
        if (addressSource && addressSource.value === 'saved') {
            const abSelect = document.getElementById('quickUseAddressBookId');
            if (abSelect && !abSelect.value) {
                alert('Please select an address from your address book.');
                if (abSelect) abSelect.focus();
                return;
            }
        }

        // Validate required variable fields
        const vars = templateVariables || [];
        for (let i = 0; i < currentForms.length; i++) {
            const form = currentForms[i];
            for (let j = 0; j < vars.length; j++) {
                const variable = vars[j];
                if (variable.required) {
                    const varId = 'var_' + form.index + '_' + j;
                    const input = document.getElementById(varId);
                    if (input && (!input.value || input.value.trim() === '')) {
                        alert('Please fill in the required field "' + variable.name + '" for ' + (sameDesign ? 'all items' : 'item #' + form.index));
                        return;
                    }
                }
            }
        }

        // Collect checkout data
        const designs = [];
        let totalQuantity = 0;

        if (sameDesign) {
            const values = {};
            (templateVariables || []).forEach((variable, varIndex) => {
                const input = document.getElementById('var_1_' + varIndex);
                if (input) values[variable.name] = input.value.trim();
            });
            const qty = parseInt(document.getElementById('itemQuantity_1')?.value) || 1;
            totalQuantity = qty;
            for (let i = 1; i <= qty; i++) {
                designs.push({ index: i, variables: { ...values }, quantity: 1 });
            }
        } else {
            currentForms.forEach(form => {
                const values = {};
                (templateVariables || []).forEach((variable, varIndex) => {
                    const input = document.getElementById('var_' + form.index + '_' + varIndex);
                    if (input) values[variable.name] = input.value.trim();
                });
                const qty = parseInt(document.getElementById('itemQuantity_' + form.index)?.value) || 1;
                totalQuantity += qty;
                for (let i = 1; i <= qty; i++) {
                    designs.push({ index: form.index, instance: i, variables: values, quantity: 1 });
                }
            });
        }

        const sheetTypeSelect = document.getElementById('printDetailSheetTypeSelect');
        const materialTypeSelect = document.getElementById('printDetailMaterialTypeSelect');

        const checkoutData = {
            quantity: totalQuantity,
            same_design: sameDesign,
            designs: designs,
            sheet_type: sheetTypeSelect?.value || 'standard',
            sheet_type_name: sheetTypeSelect?.options[sheetTypeSelect?.selectedIndex]?.text || 'Standard',
            material_type: materialTypeSelect?.value || 'paper',
            material_type_name: materialTypeSelect?.options[materialTypeSelect?.selectedIndex]?.text || 'Paper',
            total_cost: document.getElementById('totalCost')?.textContent || '0',
            template_cost: document.getElementById('breakdownTemplate')?.textContent || '0',
            sheet_cost: document.getElementById('breakdownPagesCost')?.textContent || '0',
            material_cost: document.getElementById('breakdownMaterial')?.textContent || '0'
        };

        document.getElementById('checkoutDataInput').value = JSON.stringify(checkoutData);
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        document.getElementById('checkoutForm').submit();
    }

    function toggleQuickUseAddressSource(source) {
        var savedBlock = document.getElementById('quickUseSavedAddressBlock');
        var manualBlock = document.getElementById('quickUseManualAddressFields');
        if (source === 'saved') {
            if (savedBlock) savedBlock.style.display = 'block';
            if (manualBlock) manualBlock.style.display = 'none';
            var sel = document.getElementById('quickUseAddressBookId');
            if (sel && sel.value) {
                var opt = sel.options[sel.selectedIndex];
                if (opt && opt.dataset.phone) document.getElementById('deliveryPhone').value = opt.dataset.phone || '';
            }
        } else {
            if (savedBlock) savedBlock.style.display = 'none';
            if (manualBlock) manualBlock.style.display = 'block';
            var sel = document.getElementById('quickUseAddressBookId');
            if (sel) sel.value = '';
        }
    }

    (function() {
        var abSelect = document.getElementById('quickUseAddressBookId');
        if (abSelect) {
            abSelect.addEventListener('change', function() {
                var opt = this.options[this.selectedIndex];
                if (opt && opt.value && opt.dataset.phone) document.getElementById('deliveryPhone').value = opt.dataset.phone || '';
            });
        }
    })();

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        var draft = restoreQuickUseDraft();
        if (draft) {
            var qEl = document.getElementById('quantity');
            var sEl = document.getElementById('sameDesign');
            if (qEl && draft.quantity != null) qEl.value = draft.quantity;
            if (sEl && draft.sameDesign != null) sEl.checked = !!draft.sameDesign;
        }
        updateVariableForms();
        setTimeout(updateExpandCollapseButtons, 100);
        updateTotalQuantity();
        calculateTotalCost();
        playSheetTypeVideo();
        if (draft) {
            setTimeout(function() { applyQuickUseDraftValues(draft); }, 80);
        }
        // Persist form data so it survives back navigation / return
        var container = document.getElementById('variableFormsContainer');
        if (container) {
            container.addEventListener('input', scheduleSaveQuickUseDraft);
            container.addEventListener('change', scheduleSaveQuickUseDraft);
        }
        document.getElementById('quantity')?.addEventListener('input', scheduleSaveQuickUseDraft);
        document.getElementById('quantity')?.addEventListener('change', scheduleSaveQuickUseDraft);
        document.getElementById('sameDesign')?.addEventListener('change', scheduleSaveQuickUseDraft);
        document.getElementById('printDetailSheetTypeSelect')?.addEventListener('change', scheduleSaveQuickUseDraft);
        document.getElementById('printDetailMaterialTypeSelect')?.addEventListener('change', scheduleSaveQuickUseDraft);
        window.addEventListener('beforeunload', saveQuickUseDraft);
    });
</script>
@endpush

