@extends('layouts.admin')

@section('title', 'Designer Applications')
@section('page-title', 'Designer Applications')

@section('content')
<div class="my-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-palette me-2 text-primary"></i>Designer Applications
            </h2>
            <p class="text-muted mb-0">Review and approve designer applications. Approved users can save public templates.</p>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.designer-applications') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Name</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Email</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">User Account</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Submitted</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                        <tr>
                            <td style="padding: 1rem; font-size: 0.9rem; font-weight: 500;">{{ $app->name }}</td>
                            <td style="padding: 1rem; font-size: 0.9rem; color: #64748b;">{{ $app->email }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem;">
                                @if($app->user)
                                    <a href="{{ route('admin.users.show', $app->user_id) }}">{{ $app->user->name }}</a>
                                    <span class="badge {{ $app->user->hasRole('designer') ? 'bg-success' : 'bg-secondary' }} ms-1">
                                        {{ $app->user->hasRole('designer') ? 'Designer' : 'User' }}
                                    </span>
                                @else
                                    <span class="text-muted">Guest (no account)</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                @if($app->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($app->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">{{ $app->created_at->format('M d, Y') }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <a href="{{ route('admin.designer-applications.show', $app->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($app->status === 'pending')
                                    <form action="{{ route('admin.designer-applications.approve', $app->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Approve this designer? They will be able to save public templates.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.designer-applications.show', $app->id) }}#reject-form" class="btn btn-sm btn-outline-danger" title="Reject">
                                        <i class="fas fa-times"></i> Reject
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No designer applications found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($applications->hasPages())
        <div class="card-footer d-flex justify-content-center py-3">
            {{ $applications->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
