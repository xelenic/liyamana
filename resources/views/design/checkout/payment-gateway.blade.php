@extends('layouts.app')

@section('title', 'Payment - ' . ucfirst($method))
@section('page-title', 'Payment')

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
    .payment-method-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.65rem;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8125rem;
        margin-bottom: 0.75rem;
    }
    .checkout-card .form-control {
        width: 100%;
        padding: 0.4rem 0.65rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.8125rem;
    }
    .checkout-card .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
    }
    .checkout-card .form-label {
        font-size: 0.8125rem;
    }
    .btn-pay {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        padding: 0.45rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8125rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-pay:hover {
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }
</style>
@endpush

@section('content')
<div class="checkout-container">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="checkout-card p-3 mb-3" style="padding: 1rem !important;">
                    @php
                        $methodIcons = [
                            'stripe' => 'fa-credit-card',
                            'paypal' => 'fa-paypal',
                            'bank_transfer' => 'fa-university',
                            'platform_credit' => 'fa-wallet',
                            'payhere' => 'fa-credit-card'
                        ];
                        $methodNames = [
                            'stripe' => 'Credit / Debit Card',
                            'paypal' => 'PayPal',
                            'bank_transfer' => 'Bank Transfer',
                            'platform_credit' => 'Deduct from Credit',
                            'payhere' => 'PayHere'
                        ];
                        $icon = $methodIcons[$method] ?? 'fa-credit-card';
                        $methodName = $methodNames[$method] ?? ucfirst($method);
                    @endphp

                    <div class="payment-method-badge">
                        <i class="fas {{ $icon }}"></i>
                        {{ $methodName }}
                    </div>

                    <h2 class="mb-3" style="font-weight: 700; font-size: 1.125rem; color: var(--dark-text);">
                        <i class="fas fa-lock me-1" style="color: var(--primary-color); font-size: 0.9rem;"></i>Secure Payment
                    </h2>

                    <form action="{{ $method === 'payhere' ? route('payhere.initiate') : route('design.checkout.processPayment') }}" method="POST" id="paymentForm">
                        @csrf
                        <input type="hidden" name="payment_method" value="{{ $method }}">
                        @if($method === 'payhere')
                        <div class="alert alert-info py-2 px-3" style="font-size: 0.8125rem;">
                            <i class="fas fa-info-circle me-2"></i>
                            You will be redirected to PayHere to complete your payment securely (card, mobile wallet).
                        </div>
                        @elseif($method === 'stripe')
                        <input type="hidden" name="payment_intent_id" id="paymentIntentId" value="">
                        @endif

                        @if($method === 'stripe' && !empty($stripePublishableKey))
                        <div class="mb-2">
                            <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin-bottom: 0.25rem;">Card Details</label>
                            <div id="card-element" class="form-control" style="padding: 0.65rem; min-height: 42px;"></div>
                            <div id="card-errors" class="text-danger mt-1" style="font-size: 0.75rem;" role="alert"></div>
                        </div>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;"><i class="fas fa-info-circle me-1"></i>Your card details are secured by Stripe. Test card: 4242 4242 4242 4242</p>
                        @elseif($method === 'stripe')
                        <div class="alert alert-warning py-2 px-3" style="font-size: 0.8125rem;">
                            <i class="fas fa-exclamation-triangle me-2"></i>Stripe is not configured. Please contact the administrator or choose another payment method.
                        </div>
                        @elseif($method === 'paypal')
                        <div class="alert alert-info py-2 px-3" style="font-size: 0.8125rem;">
                            <i class="fas fa-info-circle me-2"></i>
                            You will be redirected to PayPal to complete your payment securely.
                        </div>
                        @elseif($method === 'platform_credit')
                        <div class="alert alert-success py-2 px-3" style="font-size: 0.8125rem;">
                            <i class="fas fa-wallet me-2"></i>
                            Pay {{ $checkout['checkout_data']['total_cost'] ?? '' }} using your platform credits. Your balance: <strong>{{ format_price(auth()->user()->balance ?? 0) }}</strong>
                        </div>
                        @elseif($method === 'bank_transfer')
                        <div class="mb-2">
                            <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin-bottom: 0.25rem;">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="Your Bank Name">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin-bottom: 0.25rem;">Account Number</label>
                            <input type="text" name="account_number" class="form-control" placeholder="Account Number">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin-bottom: 0.25rem;">Reference / Notes</label>
                            <textarea name="reference" class="form-control" rows="3" placeholder="Order reference or notes"></textarea>
                        </div>
                        @endif

                        <div class="mt-3">
                            <a href="{{ route('design.checkout.paymentOptions') }}" class="btn btn-outline-secondary btn-sm me-2" style="font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn-pay" id="payBtn">
                                <i class="fas fa-lock me-2"></i>Pay {{ $checkout['checkout_data']['total_cost'] ?? '' }}
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
                        <strong style="color: var(--dark-text);">Sheet Type:</strong> {{ $checkout['checkout_data']['sheet_type_name'] ?? 'Standard' }}
                    </div>
                    <div style="margin-bottom: 0.4rem; font-size: 0.75rem; color: #64748b;">
                        <strong style="color: var(--dark-text);">Material:</strong> {{ $checkout['checkout_data']['material_type_name'] ?? 'Paper' }}
                    </div>
                    @if(!empty($checkout['checkout_data']['is_letter']) && (isset($checkout['checkout_data']['template_cost']) || isset($checkout['checkout_data']['envelope_cost']) || isset($checkout['checkout_data']['sheet_cost'])))
                    <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--border-color);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem; font-size: 0.75rem; color: #64748b;">
                            <span>Letter Cost:</span>
                            <span>{{ $checkout['checkout_data']['template_cost'] ?? format_price(0) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem; font-size: 0.75rem; color: #64748b;">
                            <span>Envelope:</span>
                            <span>{{ $checkout['checkout_data']['envelope_cost'] ?? format_price(0) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem; font-size: 0.75rem; color: #64748b;">
                            <span>Sheet Cost:</span>
                            <span>{{ $checkout['checkout_data']['sheet_cost'] ?? format_price(0) }}</span>
                        </div>
                    </div>
                    @endif
                    <div style="margin-top: 0.6rem; padding-top: 0.6rem; border-top: 1px solid var(--border-color); font-size: 0.9375rem; font-weight: 700; color: var(--primary-color);">
                        Total: {{ $checkout['checkout_data']['total_cost'] ?? format_price(0) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($method === 'stripe' && !empty($stripePublishableKey))
@push('scripts')
<script src="https://js.stripe.com/v3/" id="stripe-js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('paymentForm');
    const payBtn = document.getElementById('payBtn');
    const stripePublishableKey = @json($stripePublishableKey ?? '');

    if (!stripePublishableKey) return;

    function initStripePayment(retries = 0) {
        if (typeof Stripe === 'undefined') {
            if (retries < 50) {
                setTimeout(function() { initStripePayment(retries + 1); }, 100);
            } else {
                document.getElementById('card-errors').textContent = 'Failed to load Stripe. Please check your connection and refresh.';
                payBtn.disabled = true;
            }
            return;
        }
        const stripe = Stripe(stripePublishableKey);
    let elements, cardElement, clientSecret, paymentIntentId;

    fetch('{{ route("design.checkout.createPaymentIntent") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.clientSecret) {
            document.getElementById('card-errors').textContent = data.error || 'Failed to initialize payment';
            payBtn.disabled = true;
            return;
        }
        clientSecret = data.clientSecret;
        paymentIntentId = data.paymentIntentId;
        document.getElementById('paymentIntentId').value = paymentIntentId;

        elements = stripe.elements();
        cardElement = elements.create('card', {
            style: { base: { fontSize: '14px', color: '#1e293b' } }
        });
        cardElement.mount('#card-element');

        cardElement.on('change', function(e) {
            const displayError = document.getElementById('card-errors');
            displayError.textContent = e.error ? e.error.message : '';
        });
    })
    .catch(err => {
        document.getElementById('card-errors').textContent = 'Failed to initialize payment. Please refresh and try again.';
        payBtn.disabled = true;
    });

    form.addEventListener('submit', async function(e) {
        if (document.getElementById('paymentIntentId').value) {
            e.preventDefault();
            payBtn.disabled = true;
            payBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

            const { error } = await stripe.confirmCardPayment(clientSecret, {
                payment_method: { card: cardElement }
            });

            if (error) {
                document.getElementById('card-errors').textContent = error.message || 'Payment failed';
                payBtn.disabled = false;
                payBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay {{ $checkout["checkout_data"]["total_cost"] ?? "" }}';
            } else {
                form.submit();
            }
        }
    });
    }
    initStripePayment();
});
</script>
@endpush
@endif
