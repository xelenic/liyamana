@extends('layouts.app')

@section('title', 'Reset Password - ' . site_name())

@push('styles')
<style>
    .footer { display: none !important; }
</style>
@endpush

@section('content')
<section class="hero-section" style="margin-top: 0 !important; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 50%, #f8fafc 100%); padding: 6rem 1rem 3rem; position: relative; overflow: hidden;">
    <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(139, 92, 246, 0.04) 100%); border-radius: 50%; filter: blur(80px);"></div>

    <div class="container position-relative">
        <div class="row justify-content-center" style="min-height: 600px;">
            <div class="col-12 col-md-8 col-lg-5">
                <div style="background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div class="text-center mb-4">
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <i class="fas fa-key text-white" style="font-size: 1.8rem;"></i>
                        </div>
                        <h2 class="mb-2" style="font-weight: 800; font-size: 1.75rem; letter-spacing: -0.5px; color: #1e293b;">Reset Password</h2>
                        <p style="color: #64748b; font-size: 0.95rem;">Enter your new password below</p>
                    </div>

                        @if($errors->has('email'))
                            <div class="alert alert-danger py-2 small mb-3" role="alert">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $errors->first('email') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="mb-3">
                                <label for="email" class="form-label mb-1" style="font-weight: 600; color: #1e293b; font-size: 0.85rem;">
                                    <i class="fas fa-envelope me-2" style="color: #6366f1;"></i>Email Address
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $email) }}" required autofocus style="padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem;">
                                @error('email')
                                    <div class="invalid-feedback d-block" style="font-size: 0.8rem;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label mb-1" style="font-weight: 600; color: #1e293b; font-size: 0.85rem;">
                                    <i class="fas fa-lock me-2" style="color: #6366f1;"></i>New Password
                                </label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="At least 8 characters" style="padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem;">
                                @error('password')
                                    <div class="invalid-feedback d-block" style="font-size: 0.8rem;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label mb-1" style="font-weight: 600; color: #1e293b; font-size: 0.85rem;">
                                    <i class="fas fa-lock me-2" style="color: #6366f1;"></i>Confirm Password
                                </label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Confirm your password" style="padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem;">
                            </div>
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn shadow-lg" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none; padding: 0.9rem 2rem; font-weight: 700; font-size: 0.95rem; border-radius: 10px;">
                                    <i class="fas fa-check me-2"></i>Reset Password
                                </button>
                            </div>
                        </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="text-decoration-none" style="font-size: 0.9rem; color: #6366f1; font-weight: 600;">
                            <i class="fas fa-arrow-left me-1"></i>Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
