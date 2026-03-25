@extends('layouts.admin')

@section('title', 'Platform activity report')
@section('page-title', 'Platform activity report')

@section('content')
<div class="my-4">
    <form method="GET" action="{{ route('admin.reports.activity') }}" class="card border-0 shadow-sm mb-4">
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
                <a href="{{ route('admin.reports.activity') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">New user registrations</div>
                    <div class="h4 mb-0 fw-bold">{{ number_format($newUsers) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">AI content generations</div>
                    <div class="h4 mb-0 fw-bold">{{ number_format($aiTotal) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Scheduled mail created (range)</div>
                    <div class="h4 mb-0 fw-bold">{{ number_format($scheduledCreated) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">AI generations by day</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Date</th><th class="text-end">Count</th></tr></thead>
                        <tbody>
                            @forelse($aiByDay as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="text-end">{{ number_format($row['count']) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-muted text-center py-3">No AI generations in range.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Scheduled mail (created in range) by status</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Status</th><th class="text-end">Count</th></tr></thead>
                        <tbody>
                            @forelse($scheduledByStatus as $row)
                            <tr>
                                <td><span class="badge bg-light text-dark">{{ $row->status ?? '—' }}</span></td>
                                <td class="text-end">{{ number_format($row->cnt) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-muted text-center py-3">No rows</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Top AI content templates (by generation count)</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Template</th><th class="text-end">Generations</th></tr></thead>
                <tbody>
                    @forelse($topAiTemplates as $row)
                    <tr>
                        <td>
                            @if($row->ai_content_template_id)
                                {{ $templateNames[$row->ai_content_template_id] ?? ('#'.$row->ai_content_template_id) }}
                            @else
                                <span class="text-muted">(no template)</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($row->cnt) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-muted text-center py-4">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
