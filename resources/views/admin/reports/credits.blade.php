@extends('layouts.admin')

@section('title', 'Credits report')
@section('page-title', 'Credits report')

@section('content')
<div class="my-4">
    <form method="GET" action="{{ route('admin.reports.credits') }}" class="card border-0 shadow-sm mb-4">
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
                <a href="{{ route('admin.reports.credits') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Top-ups (positive)</div>
                    <div class="h5 mb-0 fw-bold text-success">{{ format_price($summary['topups']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Spend (negative abs)</div>
                    <div class="h5 mb-0 fw-bold text-danger">{{ format_price($summary['spend']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Net (sum)</div>
                    <div class="h5 mb-0 fw-bold">{{ format_price($summary['net']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Transactions</div>
                    <div class="h5 mb-0 fw-bold">{{ number_format($summary['count']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">By type</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Type</th><th class="text-end">Count</th><th class="text-end">Net amount</th></tr></thead>
                        <tbody>
                            @forelse($byType as $row)
                            <tr>
                                <td>{{ $row->type ?? '—' }}</td>
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
                        <thead><tr><th>Method</th><th class="text-end">Count</th><th class="text-end">Net amount</th></tr></thead>
                        <tbody>
                            @forelse($byPaymentRef as $row)
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
        <div class="card-header bg-white fw-bold">Daily net (credits)</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Date</th><th class="text-end">Txns</th><th class="text-end">Net</th></tr></thead>
                <tbody>
                    @forelse($daily as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td class="text-end">{{ number_format($row['count']) }}</td>
                        <td class="text-end">{{ format_price($row['net']) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-muted text-center py-4">No transactions in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Recent transactions (50)</div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th>Reference</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent as $t)
                    <tr>
                        <td>{{ $t->id }}</td>
                        <td>
                            @if($t->user)
                                <a href="{{ route('admin.users.show', $t->user_id) }}">{{ Str::limit($t->user->name, 24) }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $t->type }}</span></td>
                        <td class="text-end fw-semibold {{ (float) $t->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ format_price($t->amount) }}</td>
                        <td class="small text-muted">{{ Str::limit($t->reference ?? '—', 20) }}</td>
                        <td class="small text-muted">{{ $t->created_at->format('M j, H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
