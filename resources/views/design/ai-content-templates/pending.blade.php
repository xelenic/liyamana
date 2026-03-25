@extends('layouts.app')

@section('title', 'Generating your design')
@section('page-title', 'AI generation')

@push('styles')
<style>
    .ai-pending-page { min-height: 50vh; display: flex; align-items: center; }
    .ai-pending-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
        overflow: hidden;
        max-width: 520px;
        margin: 0 auto;
    }
    .ai-pending-card__top {
        background: linear-gradient(135deg, #6366f1 0%, #7c3aed 50%, #8b5cf6 100%);
        padding: 2rem 1.75rem 1.75rem;
        text-align: center;
        color: #fff;
    }
    .ai-pending-orb {
        width: 72px;
        height: 72px;
        margin: 0 auto 1.25rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .ai-pending-orb::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.35);
        border-top-color: #fff;
        animation: ai-pending-spin 0.9s linear infinite;
    }
    @keyframes ai-pending-spin {
        to { transform: rotate(360deg); }
    }
    .ai-pending-orb i { font-size: 1.75rem; opacity: 0.95; }
    .ai-pending-card__top h1 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0 0 0.5rem;
        letter-spacing: -0.02em;
    }
    .ai-pending-card__top p {
        margin: 0;
        font-size: 0.875rem;
        opacity: 0.92;
        line-height: 1.5;
    }
    .ai-pending-card__body {
        padding: 1.5rem 1.75rem 1.75rem;
    }
    .ai-pending-steps {
        list-style: none;
        padding: 0;
        margin: 0 0 1.25rem;
        font-size: 0.8125rem;
        color: #64748b;
    }
    .ai-pending-steps li {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .ai-pending-steps li:last-child { border-bottom: none; }
    .ai-pending-steps .num {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        background: #eef2ff;
        color: #4f46e5;
        font-weight: 700;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .ai-pending-error .ai-pending-card__top {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    }
    .ai-pending-error .ai-pending-orb::after { animation: none; border-color: rgba(255,255,255,0.4); }
</style>
@endpush

@section('content')
<div class="container py-5 ai-pending-page">
    <div class="ai-pending-card" id="pendingCard">
        <div class="ai-pending-card__top" id="pendingTop">
            <div class="ai-pending-orb" aria-hidden="true">
                <i class="fas fa-wand-magic-sparkles"></i>
            </div>
            <h1 id="pendingHeading">Creating your design</h1>
            <p id="pendingSub">Secure queue processing—this screen will open the editor when your file is ready. If you leave this tab, check the bell for a “AI design ready” notification with a direct link.</p>
        </div>
        <div class="ai-pending-card__body">
            @if(session('info'))
            <div class="alert alert-light border rounded-3 small mb-3" style="background: #f8fafc;">
                <i class="fas fa-info-circle text-primary me-2"></i>{{ session('info') }}
            </div>
            @endif

            <div id="pendingState">
                <ul class="ai-pending-steps">
                    <li><span class="num">1</span><span>Your request is in the job queue</span></li>
                    <li><span class="num">2</span><span>AI builds pages from the template</span></li>
                    <li><span class="num">3</span><span>We open the multi-page editor for you</span></li>
                </ul>
                <p class="small text-muted text-center mb-0" style="font-size: 0.75rem;">Do not close this tab. Typical runs complete within a few minutes.</p>
            </div>

            <div id="errorState" class="d-none">
                <p class="text-danger small mb-3 text-center" id="errorMessage"></p>
                <div class="d-grid gap-2">
                    <a href="{{ route('design.templates.index') }}" class="btn btn-primary rounded-3">Return to templates</a>
                    <a href="{{ route('design.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">Design home</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var token = @json($token);
    var resultUrl = @json(route('design.aiContentTemplates.result', ['token' => '__TOKEN__'])).replace('__TOKEN__', encodeURIComponent(token));
    var pollInterval = 2000;
    var maxPolls = 360;
    var pollCount = 0;
    var card = document.getElementById('pendingCard');
    var topBar = document.getElementById('pendingTop');

    function showError(msg) {
        document.getElementById('pendingState').classList.add('d-none');
        document.getElementById('errorState').classList.remove('d-none');
        document.getElementById('errorMessage').textContent = msg;
        if (card) card.classList.add('ai-pending-error');
        if (topBar) {
            document.getElementById('pendingHeading').textContent = 'Something went wrong';
            document.getElementById('pendingSub').textContent = 'You can go back and try again, or contact support if this persists.';
        }
    }

    function poll() {
        if (pollCount >= maxPolls) {
            showError('Generation is taking longer than expected. Please try again from the template form.');
            return;
        }
        pollCount++;
        fetch(resultUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'pending') {
                    setTimeout(poll, pollInterval);
                    return;
                }
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                showError(data.error || 'Something went wrong.');
            })
            .catch(function() {
                setTimeout(poll, pollInterval);
            });
    }
    setTimeout(poll, pollInterval);
})();
</script>
@endpush
@endsection
