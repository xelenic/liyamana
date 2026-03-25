@extends('layouts.admin')

@section('title', 'Credit Top-up Settings')
@section('page-title', 'Credit Top-up Settings')

@section('content')
<div class="my-2 settings-page-compact">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.settings.credit-topup.update') }}" method="POST" id="creditTopupSettingsForm">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-4" style="font-weight: 600; color: #1e293b;">
                            <i class="fas fa-wallet text-primary me-2"></i>Credit Top-up Configuration
                        </h5>
                        <p class="text-muted mb-4" style="font-size: 0.9rem;">Configure how users can add credits to their account. Uses the same currency as in Currency & Pricing. Card payments require Stripe to be enabled under Payment Gateway.</p>

                        @php $item = $settings['credit_topup_enabled'] ?? null; @endphp
                        @if($item)
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input type="hidden" name="credit_topup_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="credit_topup_enabled" name="credit_topup_enabled" value="1" {{ old('credit_topup_enabled', $item['value']) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="credit_topup_enabled">{{ $item['label'] }}</label>
                            </div>
                            <small class="text-muted d-block mt-1">When disabled, the Credits / Top-up page will show a message that top-up is unavailable.</small>
                        </div>
                        @endif

                        @php $item = $settings['credit_topup_min_amount'] ?? null; @endphp
                        @if($item)
                        <div class="mb-4">
                            <label for="credit_topup_min_amount" class="form-label">{{ $item['label'] }}</label>
                            <div class="input-group" style="max-width: 200px;">
                                <span class="input-group-text">{{ \App\Models\Setting::get('currency_symbol', '$') }}</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="credit_topup_min_amount" name="credit_topup_min_amount" value="{{ old('credit_topup_min_amount', $item['value']) }}" placeholder="5">
                            </div>
                            <small class="text-muted d-block mt-1">Minimum amount (in your default currency) a user can add in one top-up.</small>
                        </div>
                        @endif

                        @php $item = $settings['credit_topup_max_amount'] ?? null; @endphp
                        @if($item)
                        <div class="mb-4">
                            <label for="credit_topup_max_amount" class="form-label">{{ $item['label'] }}</label>
                            <div class="input-group" style="max-width: 200px;">
                                <span class="input-group-text">{{ \App\Models\Setting::get('currency_symbol', '$') }}</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="credit_topup_max_amount" name="credit_topup_max_amount" value="{{ old('credit_topup_max_amount', $item['value']) }}" placeholder="10000">
                            </div>
                            <small class="text-muted d-block mt-1">Maximum amount (in your default currency) a user can add in one top-up.</small>
                        </div>
                        @endif

                        <h6 class="mb-2 mt-4" style="font-weight: 600; color: #1e293b;">Payment methods for top-up</h6>
                        <p class="text-muted mb-3" style="font-size: 0.85rem;">Choose which payment methods users can use when adding credits.</p>

                        @php $item = $settings['credit_topup_stripe_enabled'] ?? null; @endphp
                        @if($item)
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="credit_topup_stripe_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="credit_topup_stripe_enabled" name="credit_topup_stripe_enabled" value="1" {{ old('credit_topup_stripe_enabled', $item['value']) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="credit_topup_stripe_enabled">{{ $item['label'] }}</label>
                            </div>
                            <small class="text-muted d-block mt-0">Requires Stripe to be enabled under Payment Gateway.</small>
                        </div>
                        @endif

                        @php $item = $settings['credit_topup_payhere_enabled'] ?? null; @endphp
                        @if($item)
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input type="hidden" name="credit_topup_payhere_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="credit_topup_payhere_enabled" name="credit_topup_payhere_enabled" value="1" {{ old('credit_topup_payhere_enabled', $item['value']) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="credit_topup_payhere_enabled">{{ $item['label'] }}</label>
                            </div>
                            <small class="text-muted d-block mt-0">Requires PayHere module enabled and configured under Payment Gateway.</small>
                        </div>
                        @endif

                        <div class="pt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save settings
                            </button>
                            <a href="{{ route('admin.settings', ['tab' => 'general']) }}" class="btn btn-outline-secondary ms-2">Back to Settings</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
