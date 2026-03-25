@extends('layouts.admin')

@section('title', 'Flip Books Management')
@section('page-title', 'Flip Books Management')

@section('content')
<div class="my-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-book me-2 text-primary"></i>Flip Books Management
            </h2>
            <p class="text-muted mb-0">Manage all flip books created by users</p>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.flipbooks') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by title or slug..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
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

    <!-- Flip Books Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Title</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">User</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Slug</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Created</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flipbooks as $flipbook)
                        <tr>
                            <td style="padding: 1rem; font-size: 0.9rem; font-weight: 500;">{{ $flipbook->title }}</td>
                            <td style="padding: 1rem; font-size: 0.9rem; color: #64748b;">{{ $flipbook->user->name }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">
                                <code style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">{{ $flipbook->slug }}</code>
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">{{ $flipbook->created_at->format('M d, Y') }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <div class="btn-group btn-group-sm" role="group" style="font-size: 0.75rem;">
                                    <a href="{{ route('flipbooks.public', $flipbook->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-1" style="font-size: 0.7rem; min-width: 28px;" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.flipbooks.delete', $flipbook->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this flip book?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1 rounded-0 rounded-end" style="font-size: 0.7rem; min-width: 28px;" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No flip books found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($flipbooks->hasPages())
    <div class="mt-3">
        {{ $flipbooks->links() }}
    </div>
    @endif
</div>
@endsection





