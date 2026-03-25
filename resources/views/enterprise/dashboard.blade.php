@extends('layouts.app')
@section('title', 'Enterprise - ' . site_name())
@section('page-title', 'Enterprise')

@include('enterprise.partials.panel-styles')

@push('styles')
<style>
    .enterprise-dash-hero {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(139, 92, 246, 0.06) 100%);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
    }
    .enterprise-dash-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }
    .enterprise-dash-list-item:last-child { border-bottom: none; }
</style>
@endpush

@section('content')
<div class="enterprise-panel">
    <div class="enterprise-mailbox">
        @include('enterprise.partials.sidebar', ['activeSection' => 'dashboard'])

        <div class="mailbox-main">
            <div class="mailbox-toolbar">
                <div>
                    <span class="d-block fw-semibold text-dark">Dashboard</span>
                    <span class="text-muted small">Mail orders, contacts, and scheduled sends in one place.</span>
                </div>
                <div class="d-flex flex-wrap gap-2 ms-auto">
                    <a href="{{ route('credits.index') }}" class="btn btn-sm btn-outline-secondary" title="Credit balance">
                        <i class="fas fa-wallet me-1"></i>{{ format_price($balance) }}
                    </a>
                    <a href="{{ route('design.templates.explore') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-compass me-1"></i>Explore templates
                    </a>
                </div>
            </div>

            <div class="mailbox-list padded">
                <div class="enterprise-dash-hero">
                    <p class="mb-0 text-muted small">
                        Scheduled mail runs automatically every minute when the server scheduler is active. Each send uses your credit balance
                        (default {{ format_price($defaultCreditCost) }} per send unless you set another amount when scheduling).
                        <a href="{{ route('enterprise.schedule-mail') }}" class="text-decoration-none">Schedule mail</a>
                        &middot;
                        <a href="{{ route('enterprise.address-book') }}" class="text-decoration-none">Manage address book</a>
                    </p>
                </div>

                @if($failedScheduledCount > 0)
                    <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2 mb-3" role="alert">
                        <span><i class="fas fa-exclamation-triangle me-1"></i>{{ $failedScheduledCount }} scheduled {{ Str::plural('mail', $failedScheduledCount) }} failed (e.g. insufficient balance at send time).</span>
                        <a href="{{ route('enterprise.schedule-mail') }}" class="btn btn-sm btn-outline-dark ms-auto">Review</a>
                    </div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-3">
                        <div class="enterprise-stat-card">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <div class="stat-value">{{ $stats['pending_orders'] }}</div>
                                    <div class="stat-label">Pending orders</div>
                                </div>
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div>
                            </div>
                            <a href="{{ route('enterprise.mailbox', ['filter' => 'pending']) }}" class="small text-decoration-none d-inline-block mt-2">View mailbox →</a>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="enterprise-stat-card">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <div class="stat-value">{{ $stats['completed_orders'] }}</div>
                                    <div class="stat-label">Completed orders</div>
                                </div>
                                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                            </div>
                            <a href="{{ route('enterprise.mailbox', ['filter' => 'completed']) }}" class="small text-decoration-none d-inline-block mt-2">View mailbox →</a>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="enterprise-stat-card">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <div class="stat-value">{{ $stats['addresses'] }}</div>
                                    <div class="stat-label">Saved addresses</div>
                                </div>
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-address-book"></i></div>
                            </div>
                            <a href="{{ route('enterprise.address-book') }}" class="small text-decoration-none d-inline-block mt-2">Open address book →</a>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="enterprise-stat-card">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <div class="stat-value">{{ $stats['scheduled_pending'] }}</div>
                                    <div class="stat-label">Scheduled (pending)</div>
                                </div>
                                <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-paper-plane"></i></div>
                            </div>
                            <a href="{{ route('enterprise.schedule-mail') }}" class="small text-decoration-none d-inline-block mt-2">Schedule or cancel →</a>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                                <h6 class="mb-0">Recent orders</h6>
                                <a href="{{ route('enterprise.mailbox', ['filter' => 'pending']) }}" class="btn btn-sm btn-link text-decoration-none p-0">Full mailbox</a>
                            </div>
                            <div class="card-body py-2">
                                @forelse($recentOrders as $order)
                                    <div class="enterprise-dash-list-item">
                                        <div class="min-w-0">
                                            <span class="fw-semibold text-dark">#{{ $order->id }}</span>
                                            <span class="text-muted ms-1">{{ Str::limit($order->template_name, 42) }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                            <span class="small fw-semibold text-success">{{ format_price($order->total_amount) }}</span>
                                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2">View</a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted small mb-0 py-3 text-center">No orders yet. Explore templates to send letters or mail pieces.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                                <h6 class="mb-0">Upcoming sends</h6>
                                <a href="{{ route('enterprise.schedule-mail') }}" class="btn btn-sm btn-link text-decoration-none p-0">Manage</a>
                            </div>
                            <div class="card-body py-2">
                                @forelse($upcomingScheduled as $s)
                                    <div class="enterprise-dash-list-item">
                                        <div class="min-w-0">
                                            <div class="fw-medium text-dark">{{ Str::limit($s->template_name, 36) }}</div>
                                            <div class="small text-muted">{{ $s->send_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</div>
                                        </div>
                                        <span class="small text-muted flex-shrink-0">{{ format_price($s->credit_amount) }}</span>
                                    </div>
                                @empty
                                    <p class="text-muted small mb-0 py-3 text-center">Nothing scheduled. Use <strong>Schedule mail</strong> to queue a template send.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
