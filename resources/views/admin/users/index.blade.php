@extends('layouts.admin')

@section('title', 'Users Management')
@section('page-title', 'Users Management')

@section('content')
<div class="my-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-users me-2 text-primary"></i>Users Management
            </h2>
            <p class="text-muted mb-0">Manage all users and their roles</p>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="designer" {{ request('role') == 'designer' ? 'selected' : '' }}>Designer</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
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

    <!-- Users Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Name</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Email</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Role</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Flip Books</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Joined</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td style="padding: 1rem; font-size: 0.9rem;">
                                <div class="d-flex align-items-center">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; color: white; font-weight: 600;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span style="font-weight: 500;">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td style="padding: 1rem; font-size: 0.9rem; color: #64748b;">{{ $user->email }}</td>
                            <td style="padding: 1rem;">
                                <span class="badge {{ $user->hasRole('admin') ? 'bg-danger' : ($user->hasRole('designer') ? 'bg-success' : 'bg-primary') }}">
                                    {{ $user->roles->pluck('name')->first() ?: 'user' }}
                                </span>
                            </td>
                            <td style="padding: 1rem; font-size: 0.9rem; color: #64748b;">{{ $user->flipBooks->count() }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">{{ $user->created_at->format('M d, Y') }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <div class="btn-group btn-group-sm" role="group" style="font-size: 0.75rem;">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-outline-primary py-0 px-1" style="font-size: 0.7rem; min-width: 28px;" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary py-0 px-1" style="font-size: 0.7rem; min-width: 28px;" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <a href="{{ route('admin.users.login-as', $user->id) }}" class="btn btn-sm btn-outline-primary py-0 px-1 rounded-0" style="font-size: 0.7rem; min-width: 28px;" title="Login as this user">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </a>
                                    @endif
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1 rounded-0 rounded-end" style="font-size: 0.7rem; min-width: 28px;" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No users found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="mt-3">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection





