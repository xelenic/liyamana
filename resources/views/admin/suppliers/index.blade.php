@extends('layouts.admin')

@section('title', 'Suppliers')
@section('page-title', 'Suppliers')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-truck me-2 text-primary"></i>Suppliers
            </h2>
            <p class="text-muted mb-0">Manage vendors used when recording stock purchases.</p>
        </div>
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add supplier
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.suppliers.index') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search name, code, email, phone..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button>
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
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Code</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Contact</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Sort</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr>
                            <td style="padding: 1rem; font-weight: 500; color: #1e293b;">{{ $supplier->name }}</td>
                            <td style="padding: 1rem;">@if($supplier->code)<code>{{ $supplier->code }}</code>@else<span class="text-muted">—</span>@endif</td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">
                                @if($supplier->email)<div><i class="fas fa-envelope me-1"></i>{{ $supplier->email }}</div>@endif
                                @if($supplier->phone)<div><i class="fas fa-phone me-1"></i>{{ $supplier->phone }}</div>@endif
                                @if(!$supplier->email && !$supplier->phone)<span class="text-muted">—</span>@endif
                            </td>
                            <td style="padding: 1rem;">{{ $supplier->sort_order }}</td>
                            <td style="padding: 1rem;">
                                @if($supplier->is_active)
                                    <span class="badge bg-success" style="font-size: 0.75rem;">Active</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 0.75rem;">Inactive</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this supplier?');">
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
                            <td colspan="6" class="text-center py-4 text-muted">No suppliers yet. <a href="{{ route('admin.suppliers.create') }}">Add one</a></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($suppliers->hasPages())
    <div class="mt-3">
        {{ $suppliers->links() }}
    </div>
    @endif
</div>
@endsection
