@extends('layouts.app')

@section('title', 'Select Payment Method')
@section('page-title', 'Select Payment Method')

@push('styles')
<style>
    :root {
        --primary-color: #6366f1;
        --secondary-color: #8b5cf6;
        --light-bg: #f8fafc;
        --dark-text: #1e293b;
        --border-color: #e2e8f0;
    }
    .checkout-container {
        min-height: 100vh;
        background: var(--light-bg);
        padding: 1rem 0;
        font-size: 0.8125rem;
    }
    .checkout-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .payment-option {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.65rem 0.9rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .payment-option:hover {
        border-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.05);
    }
    .payment-option--disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background: #f8fafc;
    }
    .payment-option--disabled:hover {
        border-color: var(--border-color);
        background: #f8fafc;
    }
    .payment-option.selected {
        border-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.08);
        box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.2);
    }
    .payment-option-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9375rem;
        flex-shrink: 0;
    }
    .payment-option-content {
        flex: 1;
    }
    .payment-option-content h5 {
        margin: 0 0 0.15rem 0;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--dark-text);
    }
    .payment-option-content p {
        margin: 0;
        font-size: 0.75rem;
        color: #64748b;
    }
    .btn-continue {
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
    .btn-continue:hover {
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
    }
    .btn-continue:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
</style>
@endpush

@section('content')
<div class="checkout-container">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="checkout-card p-3 mb-3" style="padding: 1rem !important;">
                    <h2 class="mb-3" style="font-weight: 700; font-size: 1.125rem; color: var(--dark-text);">
                        <i class="fas fa-credit-card me-1" style="color: var(--primary-color); font-size: 0.9rem;"></i>Select Payment Method
                    </h2>

                    <form action="{{ route('design.checkout.payment') }}" method="GET" id="paymentOptionsForm">
                        <input type="hidden" name="method" id="selectedMethod" value="">
                        @foreach($paymentMethods as $pm)
                        <div class="payment-option {{ ($pm['disabled'] ?? false) ? 'payment-option--disabled' : '' }}" data-method="{{ $pm['id'] }}" data-disabled="{{ ($pm['disabled'] ?? false) ? '1' : '0' }}" onclick="if (!this.dataset.disabled || this.dataset.disabled === '0') selectPaymentMethod('{{ $pm['id'] }}')">
                            <div class="payment-option-icon">
                                <i class="fas {{ $pm['icon'] }}"></i>
                            </div>
                            <div class="payment-option-content">
                                <h5>{{ $pm['name'] }}</h5>
                                <p>{{ $pm['description'] }}</p>
                            </div>
                            <div>
                                <i class="fas fa-check-circle text-success" style="font-size: 0.9rem; display: none;" data-check-icon="{{ $pm['id'] }}"></i>
                            </div>
                        </div>
                        @endforeach

                        <div class="mt-3">
                            <a href="{{ $checkoutBackUrl ?? route('design.templates.quickUse', $template->id) }}" class="btn btn-outline-secondary btn-sm me-2" style="font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn-continue" id="continueBtn" disabled>
                                <i class="fas fa-arrow-right me-2"></i>Continue to Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="checkout-card p-3" style="padding: 1rem !important;">
                    <h4 class="mb-2" style="font-weight: 700; font-size: 0.9375rem; color: var(--dark-text);">
                        <i class="fas fa-receipt me-1" style="color: var(--primary-color); font-size: 0.8rem;"></i>Order Summary
                    </h4>
                    <div style="margin-bottom: 0.4rem; font-size: 0.75rem; color: #64748b;">
                        <strong style="color: var(--dark-text);">Template:</strong> {{ $template->name }}
                    </div>
                    <div style="margin-bottom: 0.4rem; font-size: 0.75rem; color: #64748b;">
                        <strong style="color: var(--dark-text);">Quantity:</strong> {{ $checkout['checkout_data']['quantity'] ?? 1 }} item(s)
                    </div>
                    <div style="margin-bottom: 0.4rem; font-size: 0.75rem; color: #64748b;">
                        <strong style="color: var(--dark-text);">Pages:</strong> {{ $template->page_count ?? 0 }} per item
                    </div>
                    <div style="margin-top: 0.6rem; padding-top: 0.6rem; border-top: 1px solid var(--border-color); font-size: 0.9375rem; font-weight: 700; color: var(--primary-color);">
                        Total: {{ $checkout['checkout_data']['total_cost'] ?? format_price(0) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function selectPaymentMethod(method) {
        document.querySelectorAll('.payment-option').forEach(el => {
            el.classList.remove('selected');
            const icon = document.querySelector('[data-check-icon="' + el.dataset.method + '"]');
            if (icon) icon.style.display = 'none';
        });
        const selected = document.querySelector('.payment-option[data-method="' + method + '"]');
        if (selected) {
            selected.classList.add('selected');
            const icon = document.querySelector('[data-check-icon="' + method + '"]');
            if (icon) icon.style.display = 'block';
        }
        document.getElementById('selectedMethod').value = method;
        document.getElementById('continueBtn').disabled = false;
    }

    document.getElementById('paymentOptionsForm').addEventListener('submit', function(e) {
        if (!document.getElementById('selectedMethod').value) {
            e.preventDefault();
            alert('Please select a payment method.');
        }
    });
</script>
@endpush
@endsection
