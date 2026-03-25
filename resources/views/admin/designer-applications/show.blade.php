@extends('layouts.admin')

@section('title', 'Designer Application Details')
@section('page-title', 'Designer Application Details')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-palette me-2 text-primary"></i>Designer Application
            </h2>
            <p class="text-muted mb-0">{{ $application->name }} — {{ $application->email }}</p>
        </div>
        <a href="{{ route('admin.designer-applications') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Applications
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600;"><i class="fas fa-user me-2 text-primary"></i>Contact & Experience</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Name</small>
                            <strong>{{ $application->name }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Email</small>
                            <strong>{{ $application->email }}</strong>
                        </div>
                    </div>
                    @if($application->phone)
                    <div class="mb-3">
                        <small class="text-muted d-block">Phone</small>
                        <strong>{{ $application->phone }}</strong>
                    </div>
                    @endif
                    @if($application->experience)
                    <div class="mb-3">
                        <small class="text-muted d-block">Design Experience</small>
                        <div class="bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $application->experience }}</div>
                    </div>
                    @endif
                    @if($application->certifications)
                    <div class="mb-3">
                        <small class="text-muted d-block">Certifications</small>
                        <div class="bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $application->certifications }}</div>
                    </div>
                    @endif
                </div>
            </div>

            @if($application->address || $application->city || $application->country)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600;"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Address</h6>
                    <p class="mb-0">
                        @if($application->address){{ $application->address }}<br>@endif
                        @if($application->city || $application->state || $application->postal_code)
                            {{ implode(', ', array_filter([$application->city, $application->state, $application->postal_code])) }}<br>
                        @endif
                        @if($application->country){{ $application->country }}@endif
                    </p>
                </div>
            </div>
            @endif

            @if($application->bank_name || $application->account_holder_name)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600;"><i class="fas fa-university me-2 text-primary"></i>Payment Account</h6>
                    <div class="row">
                        @if($application->bank_name)
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Bank</small>
                            <strong>{{ $application->bank_name }}</strong>
                        </div>
                        @endif
                        @if($application->account_holder_name)
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Account Holder</small>
                            <strong>{{ $application->account_holder_name }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if($application->identity_card_path)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600;"><i class="fas fa-id-card me-2 text-primary"></i>Identity Verification</h6>
                    @if($application->identity_card_number)
                    <p class="mb-2"><small class="text-muted">ID Number:</small> {{ $application->identity_card_number }}</p>
                    @endif
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($application->identity_card_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>View Identity Document
                    </a>
                </div>
            </div>
            @endif

            @if($application->status === 'pending')
            <div class="card border-0 shadow-sm mb-4" id="reject-form">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600;"><i class="fas fa-times-circle me-2 text-danger"></i>Reject Application</h6>
                    <form action="{{ route('admin.designer-applications.reject', $application->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this application?');">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Admin Notes (optional)</label>
                            <textarea name="admin_notes" class="form-control" rows="3" placeholder="Reason for rejection (visible to admin only)">{{ old('admin_notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-2"></i>Reject Application
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600;">Status & Actions</h6>
                    <div class="mb-3">
                        <small class="text-muted d-block">Status</small>
                        @if($application->status === 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif($application->status === 'approved')
                            <span class="badge bg-success">Approved</span>
                        @else
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Submitted</small>
                        <strong>{{ $application->created_at->format('M d, Y H:i') }}</strong>
                    </div>
                    @if($application->reviewed_at)
                    <div class="mb-3">
                        <small class="text-muted d-block">Reviewed</small>
                        <strong>{{ $application->reviewed_at->format('M d, Y H:i') }}</strong>
                        @if($application->reviewer)
                        <br><small>by {{ $application->reviewer->name }}</small>
                        @endif
                    </div>
                    @endif
                    @if($application->admin_notes)
                    <div class="mb-3">
                        <small class="text-muted d-block">Admin Notes</small>
                        <div class="bg-light p-2 rounded small">{{ $application->admin_notes }}</div>
                    </div>
                    @endif

                    @if($application->status === 'pending')
                    <hr>
                    <div class="d-grid gap-2">
                        @if($application->user_id)
                        <form action="{{ route('admin.designer-applications.approve', $application->id) }}" method="POST" onsubmit="return confirm('Approve this designer? They will be able to save public templates.');">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-2"></i>Approve Designer
                            </button>
                        </form>
                        @else
                        <div class="alert alert-warning mb-0 py-2">
                            <small><i class="fas fa-exclamation-triangle me-1"></i>No user account linked. User must register/login and re-apply to be approved.</small>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            @if($application->user)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600;"><i class="fas fa-user me-2 text-primary"></i>User Account</h6>
                    <p class="mb-2">
                        <a href="{{ route('admin.users.show', $application->user_id) }}">{{ $application->user->name }}</a><br>
                        <small class="text-muted">{{ $application->user->email }}</small>
                    </p>
                    <span class="badge {{ $application->user->hasRole('designer') ? 'bg-success' : 'bg-secondary' }}">
                        {{ $application->user->hasRole('designer') ? 'Designer' : 'User' }}
                    </span>
                </div>
            </div>
            @else
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="alert alert-info mb-0 py-2">
                        <small><i class="fas fa-info-circle me-1"></i>Guest application — no user account linked.</small>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
