@extends('layouts.admin')

@section('title', 'OAuth Management')
@section('page-title', 'OAuth Management')

@section('content')
<div class="my-2 settings-page-compact">
    <form action="{{ route('admin.settings.oauth.update') }}" method="POST" id="oauthSettingsForm">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success py-2 small mb-3">
                                <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
                            </div>
                        @endif

                        <h5 class="mb-4" style="font-weight: 600; color: #1e293b;">
                            <i class="fab fa-google text-primary me-2"></i>Google OAuth (Login & Sign Up)
                        </h5>
                        <p class="text-muted mb-4" style="font-size: 0.8rem;">
                            Configure Google OAuth so users can sign in or sign up with their Google account. Create credentials in the
                            <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">Google Cloud Console</a>
                            (APIs & Services → Credentials → Create OAuth client ID → Web application). Add the redirect URI below to Authorized redirect URIs.
                        </p>

                        @php $item = $settings['google_client_id'] ?? null; @endphp
                        @if($item)
                        <div class="mb-3">
                            <label for="google_client_id" class="form-label">{{ $item['label'] }}</label>
                            <input type="text" class="form-control" id="google_client_id" name="google_client_id" value="{{ old('google_client_id', $item['value']) }}" placeholder="xxxxxx.apps.googleusercontent.com" autocomplete="off">
                            <small class="text-muted d-block mt-0">OAuth 2.0 Client ID from Google Cloud Console</small>
                        </div>
                        @endif

                        @php $item = $settings['google_client_secret'] ?? null; @endphp
                        @if($item)
                        <div class="mb-3">
                            <label for="google_client_secret" class="form-label">{{ $item['label'] }}</label>
                            <input type="password" class="form-control" id="google_client_secret" name="google_client_secret" value="" placeholder="{{ !empty($item['value']) ? '••••••••••••••••' : 'Enter client secret' }}" autocomplete="new-password">
                            <small class="text-muted d-block mt-0">OAuth 2.0 Client Secret. Leave blank to keep existing value.</small>
                        </div>
                        @endif

                        @php $item = $settings['google_redirect_uri'] ?? null; @endphp
                        @if($item)
                        <div class="mb-3">
                            <label for="google_redirect_uri" class="form-label">{{ $item['label'] }}</label>
                            <input type="url" class="form-control" id="google_redirect_uri" name="google_redirect_uri" value="{{ old('google_redirect_uri', $item['value']) }}" placeholder="https://yourdomain.com/auth/google/callback">
                            <small class="text-muted d-block mt-0">Must match exactly the redirect URI configured in your Google OAuth client. Default: <code>{{ url('/auth/google/callback') }}</code></small>
                        </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save OAuth Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-2" style="font-size: 0.9rem;">
                            <i class="fas fa-info-circle text-primary me-1"></i>About OAuth
                        </h5>
                        <p class="text-muted mb-2" style="font-size: 0.75rem;">
                            OAuth lets users sign in with Google instead of (or in addition to) email/password. Settings here override <code>GOOGLE_*</code> values in <code>.env</code>.
                        </p>
                        <div class="mb-2">
                            <strong style="font-size: 0.8rem;">Client ID & Secret</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">From Google Cloud Console → APIs & Services → Credentials → OAuth 2.0 Client IDs.</p>
                        </div>
                        <div>
                            <strong style="font-size: 0.8rem;">Redirect URI</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">Add this exact URL to Authorized redirect URIs in your Google OAuth client (e.g. <code>{{ url('/auth/google/callback') }}</code>).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .settings-page-compact .form-label {
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }
    .settings-page-compact .form-control {
        font-size: 0.8rem;
        padding: 0.35rem 0.5rem;
        min-height: 32px;
    }
    .settings-page-compact small.text-muted {
        font-size: 0.7rem;
        margin-top: 0.15rem;
    }
    .settings-page-compact .btn {
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
    }
    .settings-page-compact code {
        font-size: 0.7rem;
        word-break: break-all;
    }
</style>
@endpush
@endsection
