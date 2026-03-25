@extends('layouts.admin')

@section('title', 'Sheet Types Management')
@section('page-title', 'Sheet Types Management')

@section('content')
<div class="my-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-layer-group me-2 text-primary"></i>Sheet Types Management
            </h2>
            <p class="text-muted mb-0">Manage sheet types and their pricing</p>
        </div>
        <a href="{{ route('admin.sheet-types.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add New Sheet Type
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sheet-types') }}" class="row g-3">
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

    <!-- Sheet Types Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; width: 60px;">Image</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Name</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Slug</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Price/Sheet</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: right;">Stock</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Multiplier</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Sort Order</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Created</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sheetTypes as $sheetType)
                        <tr>
                            <td style="padding: 1rem; vertical-align: middle;">
                                @if($sheetType->image_url)
                                <img src="{{ $sheetType->image_url }}" alt="{{ $sheetType->name }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;">
                                @else
                                <div style="width: 48px; height: 48px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                    <i class="fas fa-image"></i>
                                </div>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                <div style="font-size: 0.9rem; font-weight: 500; color: #1e293b;">
                                    {{ $sheetType->name }}
                                    @if($sheetType->video_url)
                                    <i class="fas fa-video ms-1 text-info" title="Has video"></i>
                                    @endif
                                </div>
                                @if($sheetType->description)
                                    <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.25rem;">{{ Str::limit($sheetType->description, 50) }}</div>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">
                                <code style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">{{ $sheetType->slug }}</code>
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #059669;">
                                {{ format_price($sheetType->price_per_sheet) }}
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; font-weight: 600; text-align: right;">
                                {{ number_format($sheetType->stock_quantity ?? 0) }}
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">
                                {{ number_format($sheetType->multiplier, 2) }}x
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">
                                {{ $sheetType->sort_order }}
                            </td>
                            <td style="padding: 1rem;">
                                @if($sheetType->is_active)
                                    <span class="badge bg-success" style="font-size: 0.75rem;">Active</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 0.75rem;">Inactive</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">{{ $sheetType->created_at->format('M d, Y') }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.sheet-types.edit', $sheetType->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.sheet-types.delete', $sheetType->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this sheet type?');">
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
                            <td colspan="10" class="text-center py-4 text-muted">No sheet types found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($sheetTypes->hasPages())
    <div class="mt-3">
        {{ $sheetTypes->links() }}
    </div>
    @endif
</div>
@endsection


