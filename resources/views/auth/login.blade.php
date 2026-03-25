@extends('layouts.app')

@section('title', 'Login - ' . site_name())

@push('styles')
<style>
    .footer {
        display: none !important;
    }
</style>
@endpush

@section('content')
<!-- Login Hero Section -->
<section class="hero-section" style="margin-top: 0px !important; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 50%, #f8fafc 100%); padding: 6rem 1rem 3rem; position: relative; overflow: hidden;">
    <!-- Decorative Elements -->
    <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%); border-radius: 50%; filter: blur(80px);"></div>
    <div style="position: absolute; bottom: -150px; left: -150px; width: 500px; height: 500px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(59, 130, 246, 0.05) 100%); border-radius: 50%; filter: blur(100px);"></div>

    <div class="container position-relative">
        <div class="row align-items-start g-0" style="min-height: 600px;">
            <!-- Left Side - Features & Tips -->
            <div class="col-lg-6 d-none d-lg-block" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 3rem; position: fixed; top: 0; left: 0; width: 50%; height: 100vh; overflow: hidden; display: flex; align-items: center; z-index: 10;">
                <!-- Decorative Elements -->
                <div style="position: absolute; top: -100px; left: -100px; width: 300px; height: 300px; background: rgba(255,255,255,0.1); border-radius: 50%; filter: blur(60px);"></div>
                <div style="position: absolute; bottom: -80px; right: -80px; width: 250px; height: 250px; background: rgba(255,255,255,0.08); border-radius: 50%; filter: blur(50px);"></div>

                <div style="position: relative; z-index: 1; color: white; width: 100%;margin-top: 50px;">
                    <!-- Logo/Brand -->
                    <div class="mb-4">
                        <h2 class="mb-2" style="font-weight: 800; font-size: 2.5rem; letter-spacing: -0.5px; color: white;">Welcome Back!</h2>
                        <p style="font-size: 1.1rem; color: rgba(255,255,255,0.9); line-height: 1.6;">Sign in to continue creating amazing flip books</p>
                    </div>

                    <!-- Features List -->
                    <div class="mt-5">
                        <div class="mb-4 d-flex align-items-start">
                            <div style="width: 45px; height: 45px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 1.25rem; flex-shrink: 0; backdrop-filter: blur(10px);">
                                <i class="fas fa-palette text-white" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <h5 style="font-weight: 700; font-size: 1.1rem; color: white; margin-bottom: 0.5rem;">Advanced Design Tools</h5>
                                <p style="font-size: 0.95rem; color: rgba(255,255,255,0.85); line-height: 1.6; margin: 0;">Create stunning flip books with our professional design tools and templates.</p>
                            </div>
                        </div>

                        <div class="mb-4 d-flex align-items-start">
                            <div style="width: 45px; height: 45px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 1.25rem; flex-shrink: 0; backdrop-filter: blur(10px);">
                                <i class="fas fa-cloud text-white" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <h5 style="font-weight: 700; font-size: 1.1rem; color: white; margin-bottom: 0.5rem;">Cloud Storage</h5>
                                <p style="font-size: 0.95rem; color: rgba(255,255,255,0.85); line-height: 1.6; margin: 0;">All your flip books are safely stored in the cloud. Access from anywhere.</p>
                            </div>
                        </div>

                        <div class="mb-4 d-flex align-items-start">
                            <div style="width: 45px; height: 45px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 1.25rem; flex-shrink: 0; backdrop-filter: blur(10px);">
                                <i class="fas fa-share-alt text-white" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <h5 style="font-weight: 700; font-size: 1.1rem; color: white; margin-bottom: 0.5rem;">Easy Sharing</h5>
                                <p style="font-size: 0.95rem; color: rgba(255,255,255,0.85); line-height: 1.6; margin: 0;">Share your flip books instantly with a simple link. Perfect for marketing.</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start">
                            <div style="width: 45px; height: 45px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 1.25rem; flex-shrink: 0; backdrop-filter: blur(10px);">
                                <i class="fas fa-chart-line text-white" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <h5 style="font-weight: 700; font-size: 1.1rem; color: white; margin-bottom: 0.5rem;">Analytics & Insights</h5>
                                <p style="font-size: 0.95rem; color: rgba(255,255,255,0.85); line-height: 1.6; margin: 0;">Track views, engagement, and performance of your flip books.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="mt-5 pt-4" style="border-top: 1px solid rgba(255,255,255,0.2);">
                        <div class="d-flex align-items-center gap-4 flex-wrap">
                            <div class="d-flex align-items-center">
                                <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                                    <i class="fas fa-shield-alt text-white" style="font-size: 0.9rem;"></i>
                                </div>
                                <span style="font-size: 0.9rem; font-weight: 500; color: white;">Secure</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                                    <i class="fas fa-lock text-white" style="font-size: 0.9rem;"></i>
                                </div>
                                <span style="font-size: 0.9rem; font-weight: 500; color: white;">Encrypted</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                                    <i class="fas fa-check-circle text-white" style="font-size: 0.9rem;"></i>
                                </div>
                                <span style="font-size: 0.9rem; font-weight: 500; color: white;">Trusted</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="col-lg-6" style="margin-left: 50%;">
                <div style="background: white; padding: 3rem; min-height: 100vh; display: flex; align-items: center;">
                    <div style="width: 100%; max-width: 450px; margin: 0 auto;">
                        <!-- Mobile Header (only visible on mobile) -->
                        <div class="text-center mb-4 d-lg-none">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                <i class="fas fa-lock text-white" style="font-size: 1.8rem;"></i>
                            </div>
                            <h2 class="mb-2" style="font-weight: 800; font-size: 1.75rem; letter-spacing: -0.5px; color: #1e293b;">Welcome Back</h2>
                            <p style="color: #64748b; font-size: 0.95rem;">Sign in to your {{ site_name() }} account</p>
                        </div>

                        <!-- Desktop Header -->
                        <div class="mb-4 d-none d-lg-block">
                            <h2 class="mb-2" style="font-weight: 800; font-size: 2rem; letter-spacing: -0.5px; color: #1e293b;">Sign In</h2>
                            <p style="color: #64748b; font-size: 0.95rem;">Enter your credentials to access your account</p>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger py-2 small mb-3" role="alert">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email Field -->
                            <div class="mb-3">
                                <label for="email" class="form-label mb-1" style="font-weight: 600; color: #1e293b; font-size: 0.85rem;">
                                    <i class="fas fa-envelope me-2" style="color: #6366f1; font-size: 0.8rem;"></i>Email Address
                                </label>
                                <div class="position-relative">
                                    <input
                                        type="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        autofocus
                                        placeholder="Enter your email"
                                        style="padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem; transition: all 0.3s;"
                                        onfocus="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 3px rgba(99, 102, 241, 0.1)'"
                                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"
                                    >
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block" style="font-size: 0.8rem; margin-top: 0.4rem; color: #ef4444;">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Password Field -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="password" class="form-label mb-0" style="font-weight: 600; color: #1e293b; font-size: 0.85rem;">
                                        <i class="fas fa-lock me-2" style="color: #6366f1; font-size: 0.8rem;"></i>Password
                                    </label>
                                    <a href="{{ route('password.request') }}" class="text-decoration-none" style="font-size: 0.8rem; color: #6366f1; font-weight: 500;" onmouseover="this.style.color='#8b5cf6'" onmouseout="this.style.color='#6366f1'">
                                        Forgot?
                                    </a>
                                </div>
                                <div class="position-relative">
                                    <input
                                        type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        id="password"
                                        name="password"
                                        required
                                        placeholder="Enter your password"
                                        style="padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem; transition: all 0.3s;"
                                        onfocus="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 3px rgba(99, 102, 241, 0.1)'"
                                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"
                                    >
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block" style="font-size: 0.8rem; margin-top: 0.4rem; color: #ef4444;">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Remember Me -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="remember"
                                        name="remember"
                                        style="width: 16px; height: 16px; border: 2px solid #e2e8f0; border-radius: 4px; cursor: pointer;"
                                    >
                                    <label class="form-check-label" for="remember" style="font-size: 0.85rem; color: #475569; cursor: pointer; margin-left: 0.5rem;">
                                        Remember me for 30 days
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn shadow-lg" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none; padding: 0.9rem 2rem; font-weight: 700; font-size: 0.95rem; border-radius: 10px; transition: all 0.3s; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(99, 102, 241, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(99, 102, 241, 0.3)'">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </div>

                            <!-- Divider -->
                            <div class="d-flex align-items-center mb-3">
                                <hr style="flex: 1; border-color: #e2e8f0; margin: 0;">
                                <span style="padding: 0 0.75rem; color: #94a3b8; font-size: 0.8rem;">OR</span>
                                <hr style="flex: 1; border-color: #e2e8f0; margin: 0;">
                            </div>

                            <!-- Social Login Buttons -->
                            <div class="row g-2 mb-4">
                                <div class="col-12">
                                    <a href="{{ route('auth.google.redirect') }}" class="btn w-100 d-inline-flex align-items-center justify-content-center" style="background: white; color: #475569; border: 2px solid #e2e8f0; padding: 0.7rem; font-weight: 600; font-size: 0.85rem; border-radius: 8px; transition: all 0.3s; text-decoration: none;" onmouseover="this.style.borderColor='#6366f1'; this.style.color='#6366f1'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#475569'; this.style.transform='translateY(0)'">
                                        <i class="fab fa-google me-2"></i>Sign in with Google
                                    </a>
                                </div>
                            </div>

                            @if(allow_registration())
                            <!-- Sign Up Link -->
                            <div class="text-center">
                                <p class="mb-0" style="color: #64748b; font-size: 0.85rem;">
                                    Don't have an account?
                                    <a href="{{ route('register') }}" class="text-decoration-none fw-bold" style="color: #6366f1; font-weight: 700; transition: all 0.3s;" onmouseover="this.style.color='#8b5cf6'" onmouseout="this.style.color='#6366f1'">
                                        Sign up here
                                    </a>
                                </p>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
