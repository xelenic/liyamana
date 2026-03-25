@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="my-4">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="fw-bold mb-1" style="font-size: 1.5rem;">
                <i class="fas fa-tachometer-alt text-primary me-2"></i>Dashboard
            </h2>
            <p class="text-muted mb-3">Welcome back, {{ Auth::user()->name }}!</p>
        </div>
    </div>
    
    <div class="row">
        <!-- User Info Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user text-primary"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Profile</h5>
                            <p class="text-muted mb-0 small">Account Information</p>
                        </div>
                    </div>
                    <hr>
                    <p class="mb-1"><strong>Name:</strong> {{ Auth::user()->name }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ Auth::user()->email }}</p>
                    <p class="mb-0">
                        <strong>Role:</strong> 
                        <span class="badge bg-primary">
                            {{ Auth::user()->roles->pluck('name')->join(', ') ?: 'No role assigned' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Permissions Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success bg-opacity-10 rounded-circle p-2 me-2" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shield-alt text-success"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Permissions</h5>
                            <p class="text-muted mb-0 small">Your Access Rights</p>
                        </div>
                    </div>
                    <hr>
                    @if(Auth::user()->permissions->count() > 0)
                        <ul class="list-unstyled mb-0">
                            @foreach(Auth::user()->getAllPermissions()->take(5) as $permission)
                                <li class="mb-1">
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    {{ $permission->name }}
                                </li>
                            @endforeach
                            @if(Auth::user()->getAllPermissions()->count() > 5)
                                <li class="text-muted small">
                                    +{{ Auth::user()->getAllPermissions()->count() - 5 }} more
                                </li>
                            @endif
                        </ul>
                    @else
                        <p class="text-muted mb-0">No specific permissions assigned</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Quick Actions Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-bolt text-info"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Quick Actions</h5>
                            <p class="text-muted mb-0 small">Get Started</p>
                        </div>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="{{ route('enterprise') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-building me-1"></i>Enterprise hub
                        </a>
                        <a href="{{ route('flipbooks.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Create Flip Book
                        </a>
                        <a href="{{ route('flipbooks.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-folder me-1"></i>My Flip Books
                        </a>
                        <a href="{{ route('user.settings') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-cog me-1"></i>Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body" style="padding: 1.5rem !important;">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <h3 class="mb-2" style="font-size: 1.15rem;"><i class="fas fa-building text-primary me-2"></i>Enterprise tools</h3>
                            <p class="text-muted mb-0 small">
                                Track mail-related orders, keep a shared address book, and schedule template sends. Credits are charged when a scheduled send runs.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="{{ route('enterprise') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-right me-1"></i>Open Enterprise</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

