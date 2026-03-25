@extends('layouts.admin')

@section('title', 'Currencies')
@section('page-title', 'Currencies')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-coins me-2 text-primary"></i>Currencies
            </h2>
            <p class="text-muted mb-0">Manage currencies for pricing and display</p>
        </div>
        <a href="{{ route('admin.currencies.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Currency
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.currencies') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by code, name or symbol..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Code</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Name</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Symbol</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Decimals</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Default</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Sort</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currencies as $currency)
                        <tr>
                            <td style="padding: 1rem; font-weight: 600;">{{ $currency->code }}</td>
                            <td style="padding: 1rem;">{{ $currency->name }}</td>
                            <td style="padding: 1rem;">{{ $currency->symbol ?? '—' }}</td>
                            <td style="padding: 1rem;">{{ $currency->decimal_places }}</td>
                            <td style="padding: 1rem;">
                                @if($currency->is_default)
                                    <span class="badge bg-primary">Default</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                @if($currency->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">{{ $currency->sort_order }}</td>
                            <td style="padding: 1rem;">
                                <a href="{{ route('admin.currencies.edit', $currency->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.currencies.delete', $currency->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this currency?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No currencies yet. <a href="{{ route('admin.currencies.create') }}">Add one</a>.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($currencies->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $currencies->links() }}
    </div>
    @endif
</div>
@endsection
