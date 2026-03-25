@extends('layouts.admin')

@section('title', 'Pricing Rules Management')
@section('page-title', 'Pricing Rules Management')

@section('content')
<div class="my-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-percent me-2 text-primary"></i>Pricing Rules
            </h2>
            <p class="text-muted mb-0">Configure dynamic cost rules (volume discounts, same vs mixed design)</p>
        </div>
        <a href="{{ route('admin.pricing-rules.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Rule
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.pricing-rules') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or sheet type..." value="{{ request('search') }}">
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

    <!-- Rules Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Name</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Sheet Type</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Quantity</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Discount</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Applies To</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Sort</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                        <tr>
                            <td style="padding: 1rem;">
                                <span style="font-weight: 500; color: #1e293b;">{{ $rule->name ?: '—' }}</span>
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem;">
                                @if($rule->sheet_type_slug)
                                <code style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px;">{{ $rule->sheet_type_slug }}</code>
                                @else
                                <span class="text-muted">All</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem;">
                                {{ $rule->min_quantity }}+
                                @if($rule->max_quantity)
                                –{{ $rule->max_quantity }}
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #059669;">
                                {{ number_format($rule->discount_percent, 1) }}% off
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem;">
                                @if($rule->applies_to_design === 'same_design')
                                <span class="badge bg-info" style="font-size: 0.75rem;">Same Design</span>
                                @elseif($rule->applies_to_design === 'mixed_designs')
                                <span class="badge bg-secondary" style="font-size: 0.75rem;">Mixed Designs</span>
                                @else
                                <span class="badge bg-light text-dark" style="font-size: 0.75rem;">Any</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">{{ $rule->sort_order }}</td>
                            <td style="padding: 1rem;">
                                @if($rule->is_active)
                                <span class="badge bg-success" style="font-size: 0.75rem;">Active</span>
                                @else
                                <span class="badge bg-secondary" style="font-size: 0.75rem;">Inactive</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.pricing-rules.edit', $rule->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.pricing-rules.delete', $rule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this pricing rule?');">
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
                            <td colspan="8" class="text-center py-4 text-muted">
                                No pricing rules yet. <a href="{{ route('admin.pricing-rules.create') }}">Create one</a> to add dynamic cost rules.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($rules->hasPages())
    <div class="mt-3">
        {{ $rules->links() }}
    </div>
    @endif
</div>
@endsection
