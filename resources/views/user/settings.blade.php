@extends('layouts.app')

@section('title', 'Settings - ' . site_name())
@section('page-title', 'Settings')

@push('styles')
<style>
    :root {
        --primary-color: #6366f1;
        --secondary-color: #8b5cf6;
        --light-bg: #f8fafc;
        --dark-text: #1e293b;
        --border-color: #e2e8f0;
    }
    .settings-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .settings-card-header {
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
        font-size: 0.9375rem;
        color: var(--dark-text);
    }
    .settings-card-body { padding: 1.25rem 1.5rem; }
    .settings-card-body .form-label { font-weight: 600; font-size: 0.8125rem; color: #475569; }
    .settings-card-body .form-control {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
    }
    .settings-card-body .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .btn-save {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        border: none;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .btn-save:hover { color: white; opacity: 0.95; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-3"></i>
            <span class="flex-grow-1">{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 list-unstyled">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-cog me-2 text-primary"></i>Settings
            </h2>
            <p class="text-muted mb-0">Manage your account profile and password</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('user.settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="settings-card mb-4">
                    <div class="settings-card-header">
                        <i class="fas fa-user me-2" style="color: var(--primary-color);"></i>Profile
                    </div>
                    <div class="settings-card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone number</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="40" placeholder="e.g. +1 234 567 8900">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" maxlength="2000" placeholder="Street, city, postal code, country">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="settings-card mb-4">
                    <div class="settings-card-header">
                        <i class="fas fa-lock me-2" style="color: var(--primary-color);"></i>Change Password
                    </div>
                    <div class="settings-card-body">
                        <p class="text-muted small mb-3">Leave blank to keep your current password.</p>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" autocomplete="current-password">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-save">
                    <i class="fas fa-save me-2"></i>Save Settings
                </button>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="settings-card">
                <div class="settings-card-header">
                    <i class="fas fa-info-circle me-2" style="color: var(--primary-color);"></i>Account Info
                </div>
                <div class="settings-card-body">
                    <p class="small text-muted mb-1">Member since</p>
                    <p class="mb-3">{{ $user->created_at->format('F j, Y') }}</p>
                    @if($user->balance !== null)
                    <p class="small text-muted mb-1">Credit balance</p>
                    <p class="mb-0 fw-semibold">{{ format_price($user->balance) }}</p>
                    <a href="{{ route('credits.index') }}" class="btn btn-sm btn-outline-primary mt-2">Top Up Credits</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
