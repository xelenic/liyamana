@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
<div class="my-4">
    <p class="text-muted mb-4">Quick snapshot for the last <strong>{{ $periodDays }} days</strong> ({{ $from->format('M j, Y') }} – {{ $to->format('M j, Y') }}).</p>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Orders</div>
                    <div class="h3 mb-0 fw-bold">{{ number_format($stats['orders_count']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Revenue (completed)</div>
                    <div class="h3 mb-0 fw-bold">{{ format_price($stats['orders_revenue_completed']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">New users</div>
                    <div class="h3 mb-0 fw-bold">{{ number_format($stats['new_users']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">AI content generations</div>
                    <div class="h3 mb-0 fw-bold">{{ number_format($stats['ai_generations']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Credit top-ups (sum)</div>
                    <div class="h3 mb-0 fw-bold">{{ format_price($stats['credit_topups']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Scheduled mail (pending)</div>
                    <div class="h3 mb-0 fw-bold">{{ number_format($stats['scheduled_mail_pending']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <a href="{{ route('admin.reports.orders') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="h6 fw-bold mb-1 text-dark">Orders &amp; revenue</h3>
                            <p class="text-muted small mb-0">Breakdown by day, status, payment, top templates.</p>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('admin.reports.credits') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="h6 fw-bold mb-1 text-dark">Credits</h3>
                            <p class="text-muted small mb-0">Top-ups, spend, types, recent transactions.</p>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('admin.reports.activity') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="h6 fw-bold mb-1 text-dark">Platform activity</h3>
                            <p class="text-muted small mb-0">Signups, AI generations, scheduled mail.</p>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
