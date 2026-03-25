@extends('layouts.admin')

@section('title', 'Envelope Types')
@section('page-title', 'Envelope Types')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-envelope-open-text me-2 text-primary"></i>Envelope Types
            </h2>
            <p class="text-muted mb-0">Manage envelope options and per-letter pricing for Send Letter checkout</p>
        </div>
        <a href="{{ route('admin.envelope-types.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Envelope Type
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.envelope-types') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, slug, or description..." value="{{ request('search') }}">
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
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Name</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Slug</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Price / letter</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: right;">Stock</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Sort</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Created</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($envelopeTypes as $et)
                        <tr>
                            <td style="padding: 1rem; font-weight: 500; color: #1e293b;">{{ $et->name }}</td>
                            <td style="padding: 1rem;"><code>{{ $et->slug }}</code></td>
                            <td style="padding: 1rem;">{{ format_price($et->price_per_letter) }}</td>
                            <td style="padding: 1rem; font-weight: 600; text-align: right;">{{ number_format($et->stock_quantity ?? 0) }}</td>
                            <td style="padding: 1rem;">{{ $et->sort_order }}</td>
                            <td style="padding: 1rem;">
                                @if($et->is_active)
                                    <span class="badge bg-success" style="font-size: 0.75rem;">Active</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 0.75rem;">Inactive</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">{{ $et->created_at->format('M d, Y') }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.envelope-types.edit', $et->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.envelope-types.delete', $et->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this envelope type?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No envelope types found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($envelopeTypes->hasPages())
    <div class="mt-3">
        {{ $envelopeTypes->links() }}
    </div>
    @endif
</div>
@endsection
