@extends('layouts.app')
@php use App\Models\Setting; @endphp
@section('title', 'Top Up Credits - ' . site_name())
@section('page-title', 'Top Up Credits')

@push('styles')
<style>
    :root {
        --primary-color: #6366f1;
        --secondary-color: #8b5cf6;
        --success-color: #10b981;
        --light-bg: #f8fafc;
        --dark-text: #1e293b;
        --border-color: #e2e8f0;
    }
    .balance-card {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
    }
    .balance-amount {
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    .topup-card .form-control {
        border: 2px solid var(--border-color);
        border-radius: 8px;
        padding: 0.75rem 1rem;
    }
    .topup-card .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .amount-preset {
        display: inline-flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .amount-preset .btn {
        padding: 0.4rem 0.9rem;
        font-size: 0.875rem;
        border-radius: 8px;
    }
    #card-element {
        padding: 0.75rem;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        min-height: 44px;
    }
    #card-element:focus-within {
        border-color: var(--primary-color);
    }
    .payment-method-option {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .payment-method-option:hover {
        border-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.05);
    }
    .payment-method-option.selected {
        border-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.08);
    }
    .payment-method-option .icon-wrap {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .payment-method-option .content h6 { margin: 0 0 0.2rem 0; font-size: 0.9375rem; font-weight: 600; color: var(--dark-text); }
    .payment-method-option .content small { font-size: 0.8rem; color: #64748b; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-3"></i>
            <span class="flex-grow-1">{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-3"></i>
            <span class="flex-grow-1">{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-wallet me-2 text-primary"></i>Top Up Credits
            </h2>
            <p class="text-muted mb-0">Add credits to your account for purchases</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="balance-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 opacity-90" style="font-size: 0.9rem;">Current Balance</p>
                        <p class="balance-amount mb-0">{{ format_price($balance) }}</p>
                    </div>
                    <i class="fas fa-coins fa-3x opacity-50"></i>
                </div>
            </div>

            <div class="card border-0 shadow-sm topup-card mt-4">
                <div class="card-body">
                    <h5 class="mb-3" style="font-weight: 700; color: #1e293b;">
                        <i class="fas fa-plus-circle me-2 text-primary"></i>Add Credits
                    </h5>

                    @if(!($creditTopupEnabled ?? true))
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Credit top-up is currently disabled. Contact support if you need to add credits.
                    </div>
                    @elseif(!empty($paymentMethodsForTopup))
                    @php
                        $sym = Setting::get('currency_symbol') ?? '$';
                        $presets = [10, 25, 50, 100, 250];
                        $presets = array_filter($presets, fn($p) => $p >= ($minAmount ?? 5) && $p <= ($maxAmount ?? 10000));
                        if (empty($presets)) $presets = [max($minAmount ?? 5, 1)];
                    @endphp

                    @if(count($paymentMethodsForTopup) > 1)
                    <div class="mb-3">
                        <label class="form-label fw-600">Payment method</label>
                        @foreach($paymentMethodsForTopup as $pm)
                        <div class="payment-method-option {{ $loop->first ? 'selected' : '' }}" data-method="{{ $pm['id'] }}" onclick="selectTopupMethod('{{ $pm['id'] }}')">
                            <div class="icon-wrap"><i class="fas {{ $pm['icon'] }}"></i></div>
                            <div class="content">
                                <h6>{{ $pm['name'] }}</h6>
                                <small>{{ $pm['description'] }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-600">Amount ({{ $sym }})</label>
                        <input type="number" id="amountField" class="form-control" step="0.01" min="{{ $minAmount ?? 5 }}" max="{{ $maxAmount ?? 10000 }}" placeholder="Enter amount" value="">
                        <small class="text-muted">Min {{ format_price($minAmount ?? 5) }} — Max {{ format_price($maxAmount ?? 10000) }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Quick select</label>
                        <div class="amount-preset">
                            @foreach($presets as $p)
                            <button type="button" class="btn btn-outline-secondary" data-amount="{{ $p }}">{{ $sym }}{{ $p }}</button>
                            @endforeach
                        </div>
                    </div>

                    @if($stripeEnabled)
                    @php $firstMethod = $paymentMethodsForTopup[0]['id'] ?? 'stripe'; @endphp
                    <div id="stripeTopupSection" style="display: {{ $firstMethod === 'stripe' ? 'block' : 'none' }};">
                        <form id="topupForm" method="POST" action="{{ route('credits.process') }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="stripe">
                            <input type="hidden" name="payment_intent_id" id="paymentIntentId" value="">
                            <input type="hidden" name="amount" id="amountInput" value="">
                            <div class="mb-3">
                                <label class="form-label fw-600">Card Details</label>
                                <div id="card-element"></div>
                                <div id="card-errors" class="text-danger mt-1 small" role="alert"></div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="payBtn">
                                <i class="fas fa-credit-card me-2"></i>Pay & Add Credits
                            </button>
                        </form>
                    </div>
                    @endif

                    @if(in_array('payhere', array_column($paymentMethodsForTopup ?? [], 'id')))
                    <div id="payhereTopupSection" style="display: {{ (($paymentMethodsForTopup[0]['id'] ?? '') === 'payhere') ? 'block' : 'none' }};">
                        <form id="payhereTopupForm" method="POST" action="{{ route('credits.payhere.initiate') }}">
                            @csrf
                            <input type="hidden" name="amount" id="payhereAmountInput" value="">
                            <button type="submit" class="btn btn-primary w-100" id="payhereBtn">
                                <i class="fas fa-external-link-alt me-2"></i>Continue to PayHere
                            </button>
                        </form>
                    </div>
                    @endif
                    @else
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No payment methods are available for top-up. Please contact support.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <h5 class="mb-0 p-3" style="font-weight: 700; color: #1e293b; border-bottom: 1px solid #e2e8f0;">
                        <i class="fas fa-history me-2 text-primary"></i>Transaction History
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Date</th>
                                    <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Type</th>
                                    <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Amount</th>
                                    <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Balance After</th>
                                    <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $tx)
                                <tr>
                                    <td style="padding: 1rem; font-size: 0.9rem; color: #64748b;">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                    <td style="padding: 1rem;">
                                        <span class="badge {{ $tx->type === 'topup' ? 'bg-success' : ($tx->type === 'deduct' ? 'bg-danger' : 'bg-secondary') }}" style="font-size: 0.75rem;">
                                            {{ ucfirst($tx->type) }}
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; font-size: 0.9rem; font-weight: 600; color: {{ $tx->type === 'topup' ? '#059669' : '#dc2626' }};">
                                        {{ $tx->type === 'topup' ? '+' : '-' }}{{ format_price(abs((float)$tx->amount)) }}
                                    </td>
                                    <td style="padding: 1rem; font-size: 0.9rem; color: #475569;">{{ format_price($tx->balance_after) }}</td>
                                    <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">{{ ucfirst(str_replace('_', ' ', $tx->payment_method ?? '-')) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-wallet fa-3x mb-3 d-block opacity-50"></i>
                                        <p class="mb-0">No transactions yet. Top up to get started!</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($transactions->hasPages())
                <div class="card-footer">
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@if(($creditTopupEnabled ?? true) && !empty($paymentMethodsForTopup))
@push('scripts')
@if($stripeEnabled && !empty($stripePublishableKey))
<script src="https://js.stripe.com/v3/"></script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountField = document.getElementById('amountField');
    const minAmount = {{ ($minAmount ?? 5) }};
    const maxAmount = {{ ($maxAmount ?? 10000) }};

    document.querySelectorAll('.amount-preset .btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const amt = this.dataset.amount;
            if (amountField) amountField.value = amt;
        });
    });

    function selectTopupMethod(methodId) {
        document.querySelectorAll('.payment-method-option').forEach(function(el) {
            el.classList.toggle('selected', el.dataset.method === methodId);
        });
        var stripeSection = document.getElementById('stripeTopupSection');
        var payhereSection = document.getElementById('payhereTopupSection');
        if (stripeSection) stripeSection.style.display = methodId === 'stripe' ? 'block' : 'none';
        if (payhereSection) payhereSection.style.display = methodId === 'payhere' ? 'block' : 'none';
    }
    window.selectTopupMethod = selectTopupMethod;

    var payhereForm = document.getElementById('payhereTopupForm');
    if (payhereForm) {
        payhereForm.addEventListener('submit', function(e) {
            var amt = parseFloat(amountField && amountField.value);
            if (!amountField || !amt || amt < minAmount) {
                e.preventDefault();
                alert('Please enter an amount of at least ' + minAmount);
                return false;
            }
            if (amt > maxAmount) {
                e.preventDefault();
                alert('Maximum amount is ' + maxAmount);
                return false;
            }
            document.getElementById('payhereAmountInput').value = amt;
        });
    }

    var form = document.getElementById('topupForm');
    var amountInput = document.getElementById('amountInput');
    var payBtn = document.getElementById('payBtn');
    var stripePublishableKey = @json($stripePublishableKey ?? '');

    if (form && stripePublishableKey) {
    var stripe, elements, cardElement, clientSecret, paymentIntentId;

    function initStripe(retries) {
        if (typeof Stripe === 'undefined') {
            if (retries < 50) setTimeout(function() { initStripe(retries + 1); }, 100);
            return;
        }
        stripe = Stripe(stripePublishableKey);
        elements = stripe.elements();
        cardElement = elements.create('card', { style: { base: { fontSize: '14px', color: '#1e293b' } } });
        var cardEl = document.getElementById('card-element');
        if (cardEl) cardElement.mount('#card-element');
        cardElement.on('change', function(e) {
            var errEl = document.getElementById('card-errors');
            if (errEl) errEl.textContent = e.error ? e.error.message : '';
        });
    }
    initStripe(0);

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const amount = parseFloat(amountField.value);
        if (!amount || amount < minAmount) {
            document.getElementById('card-errors').textContent = 'Minimum amount is ' + minAmount;
            return;
        }
        if (amount > maxAmount) {
            document.getElementById('card-errors').textContent = 'Maximum amount is ' + maxAmount;
            return;
        }

        payBtn.disabled = true;
        payBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        document.getElementById('card-errors').textContent = '';

        try {
            const res = await fetch('{{ route("credits.createIntent") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ amount: amount })
            });
            const data = await res.json();

            if (!data.success || !data.clientSecret) {
                document.getElementById('card-errors').textContent = data.error || 'Failed to initialize payment';
                payBtn.disabled = false;
                payBtn.innerHTML = '<i class="fas fa-credit-card me-2"></i>Pay & Add Credits';
                return;
            }

            clientSecret = data.clientSecret;
            paymentIntentId = data.paymentIntentId;
            document.getElementById('paymentIntentId').value = paymentIntentId;
            document.getElementById('amountInput').value = amount;

            const { error } = await stripe.confirmCardPayment(clientSecret, {
                payment_method: { card: cardElement }
            });

            if (error) {
                document.getElementById('card-errors').textContent = error.message || 'Payment failed';
                payBtn.disabled = false;
                payBtn.innerHTML = '<i class="fas fa-credit-card me-2"></i>Pay & Add Credits';
            } else {
                form.submit();
            }
        } catch (err) {
            document.getElementById('card-errors').textContent = 'An error occurred. Please try again.';
            payBtn.disabled = false;
            payBtn.innerHTML = '<i class="fas fa-credit-card me-2"></i>Pay & Add Credits';
        }
    });
    }
});
</script>
@endpush
@endif
