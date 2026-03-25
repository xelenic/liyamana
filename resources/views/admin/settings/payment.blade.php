@extends('layouts.admin')

@section('title', 'Payment Gateway Settings')
@section('page-title', 'Payment Gateway Settings')

@section('content')
<div class="my-2 settings-page-compact">
    <form action="{{ route('admin.settings.payment.update') }}" method="POST" id="paymentSettingsForm">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-4" style="font-weight: 600; color: #1e293b;">
                            <i class="fas fa-credit-card text-primary me-2"></i>Payment Gateway Configuration
                        </h5>

                        <!-- Stripe Section -->
                        <div class="mb-4">
                            <h6 class="mb-3" style="font-weight: 600; color: #1e293b; font-size: 0.95rem;">
                                <i class="fab fa-stripe me-2" style="color: #635bff;"></i>Stripe (Card Payments)
                            </h6>
                            <p class="text-muted mb-3" style="font-size: 0.8rem;">Configure Stripe for secure credit/debit card payments. Get your API keys from <a href="https://dashboard.stripe.com/apikeys" target="_blank" rel="noopener">Stripe Dashboard</a>.</p>

                            @php $item = $settings['payment_stripe_enabled'] ?? null; @endphp
                            @if($item)
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="payment_stripe_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="payment_stripe_enabled" name="payment_stripe_enabled" value="1" {{ old('payment_stripe_enabled', $item['value']) == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="payment_stripe_enabled">{{ $item['label'] }}</label>
                                </div>
                                <small class="text-muted d-block mt-0">Enable Stripe for credit/debit card payments at checkout</small>
                            </div>
                            @endif

                            @php $item = $settings['payment_stripe_publishable_key'] ?? null; @endphp
                            @if($item)
                            <div class="mb-3">
                                <label for="payment_stripe_publishable_key" class="form-label">{{ $item['label'] }}</label>
                                <input type="text" class="form-control" id="payment_stripe_publishable_key" name="payment_stripe_publishable_key" value="{{ old('payment_stripe_publishable_key', $item['value']) }}" placeholder="pk_test_... or pk_live_...">
                                <small class="text-muted d-block mt-0">Publishable key (pk_test_... for test mode, pk_live_... for live)</small>
                            </div>
                            @endif

                            @php $item = $settings['payment_stripe_secret_key'] ?? null; @endphp
                            @if($item)
                            <div class="mb-3">
                                <label for="payment_stripe_secret_key" class="form-label">{{ $item['label'] }}</label>
                                <input type="password" class="form-control" id="payment_stripe_secret_key" name="payment_stripe_secret_key" value="" placeholder="{{ !empty($item['value']) ? '••••••••••••••••' : 'Enter secret key' }}" autocomplete="new-password">
                                <small class="text-muted d-block mt-0">Secret key (sk_...). Never share this. Leave blank to keep existing value.</small>
                            </div>
                            @endif
                        </div>

                        <hr class="my-4">

                        <!-- PayHere Section (from module) -->
                        @if(isset($settings['payment_payhere_enabled']))
                        <div class="mb-4">
                            <h6 class="mb-3" style="font-weight: 600; color: #1e293b; font-size: 0.95rem;">
                                <i class="fas fa-credit-card me-2" style="color: #10b981;"></i>PayHere (Sri Lanka)
                            </h6>
                            <p class="text-muted mb-3" style="font-size: 0.8rem;">Configure PayHere for card and mobile wallet payments. Get credentials from <a href="https://www.payhere.lk" target="_blank" rel="noopener">PayHere</a>.</p>

                            @php $item = $settings['payment_payhere_enabled'] ?? null; @endphp
                            @if($item)
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="payment_payhere_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="payment_payhere_enabled" name="payment_payhere_enabled" value="1" {{ old('payment_payhere_enabled', $item['value']) == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="payment_payhere_enabled">{{ $item['label'] }}</label>
                                </div>
                            </div>
                            @endif

                            @php $item = $settings['payment_payhere_merchant_id'] ?? null; @endphp
                            @if($item)
                            <div class="mb-3">
                                <label for="payment_payhere_merchant_id" class="form-label">{{ $item['label'] }}</label>
                                <input type="text" class="form-control" id="payment_payhere_merchant_id" name="payment_payhere_merchant_id" value="{{ old('payment_payhere_merchant_id', $item['value']) }}" placeholder="122XXXX">
                            </div>
                            @endif

                            @php $item = $settings['payment_payhere_merchant_secret'] ?? null; @endphp
                            @if($item)
                            <div class="mb-3">
                                <label for="payment_payhere_merchant_secret" class="form-label">{{ $item['label'] }}</label>
                                <input type="password" class="form-control" id="payment_payhere_merchant_secret" name="payment_payhere_merchant_secret" value="" placeholder="{{ !empty($item['value']) ? '••••••••••••••••' : 'Enter merchant secret' }}" autocomplete="new-password">
                                <small class="text-muted d-block mt-0">Leave blank to keep existing value.</small>
                            </div>
                            @endif

                            @php $item = $settings['payment_payhere_sandbox'] ?? null; @endphp
                            @if($item)
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="payment_payhere_sandbox" value="0">
                                    <input class="form-check-input" type="checkbox" id="payment_payhere_sandbox" name="payment_payhere_sandbox" value="1" {{ old('payment_payhere_sandbox', $item['value']) == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="payment_payhere_sandbox">{{ $item['label'] }}</label>
                                </div>
                            </div>
                            @endif
                        </div>
                        <hr class="my-4">
                        @endif

                        <!-- Other Payment Methods -->
                        <div>
                            <h6 class="mb-3" style="font-weight: 600; color: #1e293b; font-size: 0.95rem;">
                                <i class="fas fa-wallet me-2 text-primary"></i>Other Payment Methods
                            </h6>
                            <p class="text-muted mb-3" style="font-size: 0.8rem;">Enable or disable PayPal and Bank Transfer as checkout options.</p>

                            @foreach(['payment_paypal_enabled', 'payment_bank_transfer_enabled'] as $key)
                                @php $item = $settings[$key] ?? null; @endphp
                                @if($item)
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="{{ $key }}" value="0">
                                        <input class="form-check-input" type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1" {{ old($key, $item['value']) == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ $key }}">{{ $item['label'] }}</label>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Payment Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-2" style="font-size: 0.9rem;">
                            <i class="fas fa-info-circle text-primary me-1"></i>About Payment Gateways
                        </h5>
                        <p class="text-muted mb-2" style="font-size: 0.75rem;">
                            Configure which payment methods are available at checkout. Only enabled gateways will appear as options for customers.
                        </p>
                        <div class="mb-2">
                            <strong style="font-size: 0.8rem;">Stripe</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">Card payments via Stripe. Requires API keys. Use test keys (pk_test_, sk_test_) for development.</p>
                        </div>
                        <div class="mb-2">
                            <strong style="font-size: 0.8rem;">PayPal</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">PayPal integration (coming soon).</p>
                        </div>
                        <div>
                            <strong style="font-size: 0.8rem;">Bank Transfer</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">Manual bank transfer option. Customers provide bank details at checkout.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .settings-page-compact .form-label {
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }
    .settings-page-compact .form-control {
        font-size: 0.8rem;
        padding: 0.35rem 0.5rem;
        min-height: 32px;
    }
    .settings-page-compact .form-check-label {
        font-size: 0.8rem;
    }
    .settings-page-compact .form-check-input {
        width: 1.1rem;
        height: 1.1rem;
    }
    .settings-page-compact small.text-muted {
        font-size: 0.7rem;
        margin-top: 0.15rem;
    }
    .settings-page-compact .btn {
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
    }
</style>
@endpush
@endsection
