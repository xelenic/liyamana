@extends('layouts.app')

@section('title', 'About Us - ' . site_name())

@section('content')
<!-- Hero Section -->
<section class="hero-section" style="margin-top: 0px !important; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 50%, #f8fafc 100%); padding: 6rem 1rem 4rem; position: relative; overflow: hidden;">
    <!-- Decorative Elements -->
    <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%); border-radius: 50%; filter: blur(80px);"></div>
    <div style="position: absolute; bottom: -150px; left: -150px; width: 500px; height: 500px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(59, 130, 246, 0.05) 100%); border-radius: 50%; filter: blur(100px);"></div>

    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <!-- Badge -->
                <div class="mb-3">
                    <span class="badge px-3 py-2" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 20px;">
                        <i class="fas fa-info-circle me-2"></i>Our Story
                    </span>
                </div>

                <!-- Main Heading -->
                <h1 class="mb-4" style="font-size: 3.5rem; font-weight: 900; line-height: 1.1; letter-spacing: -0.5px; color: #0f172a;">
                    About <span style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; display: inline-block;">{{ site_name() }}</span>
                </h1>

                <!-- Subheading -->
                <p class="mb-0 mx-auto" style="font-size: 1.25rem; line-height: 1.7; font-weight: 400; color: #475569; max-width: 700px;">
                    Transforming content into beautiful, interactive experiences. We're on a mission to make professional design accessible to everyone.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Our Story Section -->
<section class="py-5" style="background: white; padding: 4rem 0 !important;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <span class="badge px-3 py-2 mb-3" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 20px;">
                    OUR JOURNEY
                </span>
                <h2 class="mb-4" style="font-weight: 800; color: #1e293b; font-size: 2.5rem; letter-spacing: -0.5px;">Our Story</h2>
                <p class="text-muted mb-4" style="line-height: 1.8; font-size: 1.05rem;">
                    {{ site_name() }} was founded with a simple mission: to make it easy for everyone to create beautiful, interactive flip books without the need for complex design skills or expensive software.
                </p>
                <p class="text-muted mb-4" style="line-height: 1.8; font-size: 1.05rem;">
                    We believe that great content deserves great presentation. Whether you're a business owner showcasing your products, a creative professional building a portfolio, or an educator sharing course materials, {{ site_name() }} provides the tools you need to create stunning digital publications.
                </p>
                <p class="text-muted mb-0" style="line-height: 1.8; font-size: 1.05rem;">
                    Our platform combines powerful design tools with intuitive user experience, making it accessible to users of all skill levels. We're constantly innovating and adding new features based on feedback from our community.
                </p>
            </div>
            <div class="col-lg-6 text-center mt-5 mt-lg-0">
                <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%); border-radius: 24px; padding: 4rem 3rem; border: 1px solid rgba(99, 102, 241, 0.1); position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%); border-radius: 50%; filter: blur(60px);"></div>
                    <div style="position: relative; z-index: 1;">
                        <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; box-shadow: 0 15px 40px rgba(99, 102, 241, 0.3);">
                            <i class="fas fa-lightbulb text-white" style="font-size: 3.5rem;"></i>
                        </div>
                        <h4 style="font-weight: 700; color: #1e293b; font-size: 1.5rem; margin-bottom: 1rem;">Innovation First</h4>
                        <p class="text-muted mb-0" style="line-height: 1.7; font-size: 1rem;">
                            We're committed to pushing the boundaries of what's possible in digital publishing
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission, Vision, Values Section -->
<section class="py-5" style="background: linear-gradient(to bottom, #ffffff 0%, #f8fafc 100%); padding: 4rem 0 !important;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-3" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 20px;">
                OUR CORE VALUES
            </span>
            <h2 class="mb-3" style="font-size: 2.5rem; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">
                What Drives Us
            </h2>
            <p class="text-muted mx-auto" style="font-size: 1.05rem; max-width: 650px; line-height: 1.7;">
                Our mission, vision, and values guide everything we do at {{ site_name() }}
            </p>
        </div>

        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4 text-center">
                        <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);">
                            <i class="fas fa-bullseye text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h4 class="mb-3" style="font-weight: 700; font-size: 1.3rem; color: #1e293b;">Our Mission</h4>
                        <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                            To empower individuals and businesses to create stunning digital publications that engage and inspire their audiences.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4 text-center">
                        <div style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);">
                            <i class="fas fa-eye text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h4 class="mb-3" style="font-weight: 700; font-size: 1.3rem; color: #1e293b;">Our Vision</h4>
                        <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                            To become the leading platform for creating interactive digital publications, making professional design accessible to everyone.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4 text-center">
                        <div style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);">
                            <i class="fas fa-heart text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h4 class="mb-3" style="font-weight: 700; font-size: 1.3rem; color: #1e293b;">Our Values</h4>
                        <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                            We value innovation, user experience, and customer satisfaction. Your success is our success.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Section -->
<section class="py-5" style="background: white; padding: 4rem 0 !important;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-3" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 20px;">
                WHY CHOOSE US
            </span>
            <h2 class="mb-3" style="font-size: 2.5rem; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">
                Why Choose {{ site_name() }}?
            </h2>
            <p class="text-muted mx-auto" style="font-size: 1.05rem; max-width: 650px; line-height: 1.7;">
                What makes us different and why thousands of users trust us with their digital publications
            </p>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); width: 60px; height: 60px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 1.5rem; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);">
                                <i class="fas fa-check text-white" style="font-size: 1.3rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-2" style="font-weight: 700; font-size: 1.2rem; color: #1e293b;">Easy to Use</h5>
                                <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                                    Intuitive interface that requires no design experience. Create professional flip books in minutes with our drag-and-drop editor.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); width: 60px; height: 60px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 1.5rem; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);">
                                <i class="fas fa-check text-white" style="font-size: 1.3rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-2" style="font-weight: 700; font-size: 1.2rem; color: #1e293b;">Powerful Features</h5>
                                <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                                    Comprehensive design tools, templates, and customization options to bring your vision to life with professional results.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); width: 60px; height: 60px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 1.5rem; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);">
                                <i class="fas fa-check text-white" style="font-size: 1.3rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-2" style="font-weight: 700; font-size: 1.2rem; color: #1e293b;">Affordable Pricing</h5>
                                <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                                    Competitive pricing plans that fit businesses of all sizes. Start free and upgrade as you grow with no hidden fees.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); width: 60px; height: 60px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 1.5rem; box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);">
                                <i class="fas fa-check text-white" style="font-size: 1.3rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-2" style="font-weight: 700; font-size: 1.2rem; color: #1e293b;">Great Support</h5>
                                <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                                    Dedicated customer support team ready to help you succeed with your flip book projects. We're here 24/7.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: white; padding: 4rem 0 !important;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%); border-radius: 24px; padding: 3rem 2rem; border: 1px solid rgba(99, 102, 241, 0.1);">
                    <h2 class="mb-3" style="font-size: 2.5rem; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">Join Our Community</h2>
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.7; max-width: 600px; margin: 0 auto;">
                        Start creating beautiful flip books today and join thousands of satisfied users who trust {{ site_name() }} for their digital publications.
                    </p>
                    <div class="d-flex gap-3 flex-wrap justify-content-center align-items-center">
                        @if(allow_registration())
                        <a href="{{ route('register') }}" class="btn shadow-lg" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none; padding: 1rem 2.5rem; font-weight: 700; font-size: 1.05rem; border-radius: 12px; transition: all 0.3s; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(99, 102, 241, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(99, 102, 241, 0.3)'">
                            <i class="fas fa-rocket me-2"></i>Get Started Free
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="btn shadow-lg" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none; padding: 1rem 2.5rem; font-weight: 700; font-size: 1.05rem; border-radius: 12px; transition: all 0.3s; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(99, 102, 241, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(99, 102, 241, 0.3)'">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        @endif
                        <a href="{{ route('services') }}" class="btn" style="background: white; color: #6366f1; border: 2px solid #e2e8f0; padding: 1rem 2.5rem; font-weight: 600; font-size: 1.05rem; border-radius: 12px; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.05);" onmouseover="this.style.borderColor='#6366f1'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.15)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)'">
                            <i class="fas fa-info-circle me-2"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
