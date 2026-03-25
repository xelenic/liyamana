@extends('layouts.admin')

@section('title', 'Orders & revenue report')
@section('page-title', 'Orders & revenue report')

@section('content')
<div class="my-4">
    <form method="GET" action="{{ route('admin.reports.orders') }}" class="card border-0 shadow-sm mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">From</label>
                <input type="date" name="from" class="form-control" value="{{ $from->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">To</label>
                <input type="date" name="to" class="form-control" value="{{ $to->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Apply</button>
                <a href="{{ route('admin.reports.orders') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Orders in range</div>
                    <div class="h4 mb-0 fw-bold">{{ number_format($summary['count']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Revenue (completed only)</div>
                    <div class="h4 mb-0 fw-bold text-success">{{ format_price($summary['revenue_completed']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Avg order value (all statuses)</div>
                    <div class="h4 mb-0 fw-bold">{{ format_price($avgOrder) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">By order status</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Status</th><th class="text-end">Count</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                            @forelse($byStatus as $row)
                            <tr>
                                <td><span class="badge bg-light text-dark">{{ $row->status ?? '—' }}</span></td>
                                <td class="text-end">{{ number_format($row->cnt) }}</td>
                                <td class="text-end">{{ format_price($row->total) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-muted text-center py-3">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">By payment method</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Method</th><th class="text-end">Count</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                            @forelse($byPayment as $row)
                            <tr>
                                <td>{{ $row->payment_method ?? '—' }}</td>
                                <td class="text-end">{{ number_format($row->cnt) }}</td>
                                <td class="text-end">{{ format_price($row->total) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-muted text-center py-3">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Daily orders</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Date</th><th class="text-end">Orders</th><th class="text-end">Revenue (sum)</th></tr></thead>
                <tbody>
                    @forelse($daily as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td class="text-end">{{ number_format($row['count']) }}</td>
                        <td class="text-end">{{ format_price($row['revenue']) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-muted text-center py-4">No orders in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Top templates by revenue</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Template</th><th class="text-end">Orders</th><th class="text-end">Revenue</th></tr></thead>
                <tbody>
                    @forelse($topTemplates as $row)
                    <tr>
                        <td>
                            <span class="text-muted small">#{{ $row->template_id }}</span>
                            {{ Str::limit($row->template_name ?? '—', 60) }}
                        </td>
                        <td class="text-end">{{ number_format($row->cnt) }}</td>
                        <td class="text-end">{{ format_price($row->total) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-muted text-center py-4">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
