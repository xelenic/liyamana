@extends('layouts.app')

@section('title', 'Home - ' . site_name())

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
    .home-featured-templates {
        scroll-margin-top: 76px;
    }
    .home-featured-templates .home-featured-browse {
        margin-top: 0;
    }
    /*
     * Swiper defaults: .swiper-wrapper { height: 100% }, .swiper-slide { height: 100% }
     * That chain can balloon the track height and leave a huge gap above pagination / Browse all.
     */
    .home-featured-templates .home-featured-swiper.swiper {
        height: auto !important;
        min-height: 0 !important;
    }
    .home-featured-templates .home-featured-swiper .swiper-wrapper {
        align-items: flex-start;
        height: auto !important;
        min-height: 0 !important;
    }
    .home-featured-templates .home-featured-swiper .swiper-slide {
        height: auto !important;
        min-height: 0 !important;
        box-sizing: border-box;
    }
    .home-featured-templates .home-featured-swiper-wrap {
        position: relative;
        padding: 0 2.25rem;
    }
    .home-featured-templates .home-featured-swiper {
        overflow: hidden;
        padding: 0 0 1.5rem;
    }
    .home-featured-templates .home-featured-card {
        width: 100%;
        height: auto !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 10px !important;
    }
    .home-featured-templates .home-featured-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(99, 102, 241, 0.12) !important;
    }
    .home-featured-templates .home-featured-card:hover .home-featured-thumbnail img {
        transform: scale(1.02);
    }
    /* Preview area: full image in frame (like explore), max height so wide slides don’t get huge */
    .home-featured-templates .home-featured-thumbnail {
        width: 100%;
        aspect-ratio: 4 / 3;
        max-height: 152px;
        min-height: 0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .home-featured-templates .home-featured-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 5px;
        transition: transform 0.3s ease;
    }
    .home-featured-templates .home-featured-card-body {
        padding: 0.5rem 0.6rem 0.6rem !important;
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }
    .home-featured-templates .home-featured-card .card-title {
        font-size: 0.78rem !important;
        font-weight: 700 !important;
        margin: 0 !important;
        line-height: 1.3 !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: #1e293b;
    }
    .home-featured-templates .home-featured-card .home-featured-desc {
        font-size: 0.65rem !important;
        line-height: 1.35 !important;
        margin: 0 !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 0 0 auto !important;
    }
    .home-featured-templates .home-featured-card .home-featured-meta {
        font-size: 0.62rem !important;
        margin: 0 !important;
        flex: 0 0 auto !important;
    }
    .home-featured-templates .home-featured-card .home-featured-btn {
        padding: 0.35rem 0.5rem !important;
        font-size: 0.68rem !important;
        border-radius: 8px !important;
        margin-top: 0.1rem;
    }
    .home-featured-templates .home-featured-card .badge {
        font-size: 0.55rem !important;
        padding: 0.1rem 0.35rem !important;
        margin: 0 !important;
        width: fit-content;
    }
    .home-featured-templates .home-featured-swiper .swiper-button-prev,
    .home-featured-templates .home-featured-swiper .swiper-button-next {
        color: #6366f1;
        background: white;
        width: 36px;
        height: 36px;
        margin-top: 0;
        border-radius: 50%;
        box-shadow: 0 2px 10px rgba(0,0,0,0.12);
        border: 1px solid #e2e8f0;
        top: 42%;
        z-index: 5;
    }
    .home-featured-templates .home-featured-swiper .swiper-button-prev:after,
    .home-featured-templates .home-featured-swiper .swiper-button-next:after {
        font-size: 0.8rem;
        font-weight: 700;
    }
    .home-featured-templates .home-featured-swiper .swiper-button-prev:hover,
    .home-featured-templates .home-featured-swiper .swiper-button-next:hover {
        background: #f8fafc;
        color: #8b5cf6;
    }
    .home-featured-templates .home-featured-swiper .swiper-pagination {
        bottom: 0 !important;
        left: 0;
        right: 0;
        margin-top: 0;
    }
    .home-featured-templates .home-featured-swiper .swiper-pagination-bullet {
        width: 6px;
        height: 6px;
        background: #6366f1;
        opacity: 0.35;
    }
    .home-featured-templates .home-featured-swiper .swiper-pagination-bullet-active {
        opacity: 1;
    }
    @media (max-width: 575px) {
        .home-featured-templates .home-featured-swiper-wrap {
            padding: 0 1.75rem;
        }
        .home-featured-templates .home-featured-thumbnail {
            max-height: 178px;
        }
    }
    @media (min-width: 992px) {
        .home-featured-templates .home-featured-thumbnail {
            max-height: 172px;
        }
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero-section" style="margin-top: 0px !important;padding: 6rem 1rem 5rem;position: relative;overflow: hidden;background: white;">
    <!-- Decorative Elements -->
    <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%); border-radius: 50%; filter: blur(80px);"></div>
    <div style="position: absolute; bottom: -150px; left: -150px; width: 500px; height: 500px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(59, 130, 246, 0.05) 100%); border-radius: 50%; filter: blur(100px);"></div>

    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <!-- Badge -->
                <div class="mb-3">
                    <span class="badge px-3 py-2" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 20px;">
                        <i class="fas fa-sparkles me-2"></i>Trusted by 10,000+ Creators Worldwide
                    </span>
                </div>

                <!-- Main Heading -->
                <h1 class="mb-4" style="font-size: 3.5rem; font-weight: 900; line-height: 1.1; letter-spacing: -0.5px; color: #0f172a;">
                    Turn Your Content Into<br>
                    <span style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; display: inline-block;">Stunning Flip Books</span>
                </h1>

                <!-- Subheading -->
                <p class="mb-5" style="font-size: 1.25rem; line-height: 1.7; font-weight: 400; color: #475569;">
                    Create professional, interactive flip books in minutes. No design experience required.
                    <span style="color: #6366f1; font-weight: 600;">Transform your PDFs, images, and content</span> into engaging digital publications.
                </p>

                <!-- CTA Buttons -->
                <div class="d-flex gap-3 flex-wrap align-items-center mb-5">
                    @if(allow_registration())
                    <a href="{{ route('register') }}" class="btn shadow-lg" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none; padding: 1rem 2.5rem; font-weight: 700; font-size: 1.05rem; border-radius: 12px; transition: all 0.3s; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(99, 102, 241, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(99, 102, 241, 0.3)'">
                        <i class="fas fa-rocket me-2"></i>Get Started Free
                    </a>
                    @else
                    <a href="{{ route('login') }}" class="btn shadow-lg" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none; padding: 1rem 2.5rem; font-weight: 700; font-size: 1.05rem; border-radius: 12px; transition: all 0.3s; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(99, 102, 241, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(99, 102, 241, 0.3)'">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    @endif
                    <a href="{{ route('templates') }}" class="btn" style="background: white; color: #6366f1; border: 2px solid #e2e8f0; padding: 1rem 2.5rem; font-weight: 600; font-size: 1.05rem; border-radius: 12px; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.05);" onmouseover="this.style.borderColor='#6366f1'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.15)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)'">
                        <i class="fas fa-images me-2"></i>Browse Templates
                    </a>
                </div>

                <!-- Trust Indicators -->
                <div class="d-flex align-items-center gap-4 flex-wrap" style="color: #64748b;">
                    <div class="d-flex align-items-center">
                        <div style="width: 28px; height: 28px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.5rem;">
                            <i class="fas fa-check text-white" style="font-size: 0.75rem;"></i>
                        </div>
                        <span style="font-size: 0.95rem; font-weight: 500;">No Credit Card</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div style="width: 28px; height: 28px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.5rem;">
                            <i class="fas fa-check text-white" style="font-size: 0.75rem;"></i>
                        </div>
                        <span style="font-size: 0.95rem; font-weight: 500;">Free Forever Plan</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div style="width: 28px; height: 28px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.5rem;">
                            <i class="fas fa-check text-white" style="font-size: 0.75rem;"></i>
                        </div>
                        <span style="font-size: 0.95rem; font-weight: 500;">Setup in 2 Minutes</span>
                    </div>
                </div>
            </div>

            <!-- Right Side - Image (parallax on mouse move) -->
            <div class="col-lg-6 text-center mt-5 mt-lg-0" id="heroParallaxZone">
                <div id="heroParallaxWrap" style="position: relative; background-image: url('{{ asset('design_actor/backgroun_hero.png') }}'); background-size: contain; background-position: center; background-repeat: no-repeat; min-height: 200px; transition: transform 0.1s ease-out;">
                    <img id="heroParallaxImg" src="{{ asset('design_actor/single_charactor.png') }}" alt="Flip Book Preview" style="width: 100%;height: auto;object-fit: cover; transition: transform 0.1s ease-out;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-4" style="background: white; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
    <div class="container">
        <div class="row">
            <div class="col-6 col-md-3">
                <div class="text-center">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <h3 class="mb-0" style="font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">10K+</h3>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; font-weight: 600; color: #64748b;">Active Users</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(52, 211, 153, 0.05) 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <h3 class="mb-0" style="font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">50K+</h3>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; font-weight: 600; color: #64748b;">Flip Books Created</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(251, 191, 36, 0.05) 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <h3 class="mb-0" style="font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">100+</h3>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; font-weight: 600; color: #64748b;">Templates</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(248, 113, 113, 0.05) 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <h3 class="mb-0" style="font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #ef4444 0%, #f87171 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">4.9/5</h3>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; font-weight: 600; color: #64748b;">User Rating</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5" style="background: linear-gradient(to bottom, #ffffff 0%, #f8fafc 100%); padding: 4rem 0 !important;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-3" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 20px;">
                POWERFUL FEATURES
            </span>
            <h2 class="mb-3" style="font-size: 2.5rem; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">
                Everything You Need to<br>Succeed
            </h2>
            <p class="text-muted mx-auto" style="font-size: 1.05rem; max-width: 650px; line-height: 1.7;">
                Professional-grade tools designed to help you create stunning flip books that engage and convert your audience
            </p>
        </div>

        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="text-align: center;border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="feature-card-img-wrap" style="width: 100%; height: 180px; overflow: hidden; background: #ffffff; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('feature_actor/premium_design.png') }}" alt="Premium Templates" class="feature-card-img" style="width: 160%;height: 160%;object-fit: contain;padding: 1rem;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" loading="lazy">
                    </div>
                    <div class="card-body p-4">
                        <h4 class="card-title mb-3" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">Premium Templates</h4>
                        <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                            Access a library of professionally designed templates for flip books, brochures, catalogs, and more. Start from a template and make it yours.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="text-align: center;border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="feature-card-img-wrap" style="width: 100%; height: 180px; overflow: hidden; background: #ffffff; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('feature_actor/design_your_own_idea.png') }}" alt="Design Your Own Idea" class="feature-card-img" style="width: 160%;height: 160%;object-fit: contain;padding: 1rem;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" loading="lazy">
                    </div>
                    <div class="card-body p-4">
                        <h4 class="card-title mb-3" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">Design Your Own Idea</h4>
                        <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                            Use our design tool to create exactly what you imagine. Drag, drop, and customize every element. No design experience required.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="text-align: center;border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="feature-card-img-wrap" style="width: 100%; height: 180px; overflow: hidden; background: #ffffff; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('feature_actor/ai_generated_letters.png') }}" alt="AI Generated Images" class="feature-card-img" style="width: 160%;height: 160%;object-fit: contain;padding: 1rem;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" loading="lazy">
                    </div>
                    <div class="card-body p-4">
                        <h4 class="card-title mb-3" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">AI Generated Images</h4>
                        <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                            Generate unique images with AI. Describe what you need and add high-quality visuals to your designs in seconds.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="text-align: center;border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="feature-card-img-wrap" style="width: 100%; height: 180px; overflow: hidden; background: #ffffff; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('feature_actor/relax_easy_send.png') }}" alt="Easy to Send Postal Letters" class="feature-card-img" style="width: 160%;height: 160%;object-fit: contain;padding: 1rem;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" loading="lazy">
                    </div>
                    <div class="card-body p-4">
                        <h4 class="card-title mb-3" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">Easy to Send Postal Letters</h4>
                        <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                            Design letters in the editor and send them as real postal mail. Simple, reliable delivery without leaving the platform.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="text-align: center;border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="feature-card-img-wrap" style="width: 100%; height: 180px; overflow: hidden; background: #ffffff; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('feature_actor/bussness_card.png') }}" alt="Business Card Print" class="feature-card-img" style="width: 190%;height: 121%;object-fit: contain;padding: 1rem;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" loading="lazy">
                    </div>
                    <div class="card-body p-4">
                        <h4 class="card-title mb-3" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">Business Card Print</h4>
                        <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                            Create and order professional business cards. Design online and get them printed and delivered to your door.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="text-align: center;border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="feature-card-img-wrap" style="width: 100%; height: 180px; overflow: hidden; background: #ffffff; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('feature_actor/bulk.png') }}" alt="Bulk Sending" class="feature-card-img" style="width: 160%;height: 160%;object-fit: contain;padding: 1rem;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" loading="lazy">
                    </div>
                    <div class="card-body p-4">
                        <h4 class="card-title mb-3" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">Bulk Sending</h4>
                        <p class="text-muted mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                            Send letters, postcards, or documents to many recipients at once. Perfect for campaigns, invitations, and direct mail.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


@if(isset($featuredTemplates) && $featuredTemplates->isNotEmpty())
<!-- Featured templates -->
<section class="home-featured-templates" style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 50%, #ffffff 100%); padding: 2.5rem 0 1.75rem !important; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-3" style="background: rgba(245, 158, 11, 0.12); color: #d97706; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 20px;">
                <i class="fas fa-star me-1"></i>Featured templates
            </span>
            <h2 class="mb-3" style="font-size: 2.5rem; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">
                Start from a <span style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">pro design</span>
            </h2>
            <p class="text-muted mx-auto mb-0" style="font-size: 1.05rem; max-width: 650px; line-height: 1.7;">
                Curated picks from our library—customize in the design tool after you sign in.
            </p>
        </div>

        @php
            $ftGradients = ['#6366f1 0%, #8b5cf6 100%', '#10b981 0%, #34d399 100%', '#f59e0b 0%, #fbbf24 100%', '#3b82f6 0%, #60a5fa 100%', '#8b5cf6 0%, #a78bfa 100%', '#ec4899 0%, #f472b6 100%', '#14b8a6 0%, #2dd4bf 100%'];
            $ftBadges = ['#6366f1', '#10b981', '#f59e0b', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#6366f1'];
        @endphp
        <div class="home-featured-swiper-wrap">
            <div class="swiper home-featured-swiper" id="homeFeaturedSwiper">
                <div class="swiper-wrapper">
                    @foreach($featuredTemplates as $index => $template)
                    <div class="swiper-slide">
                        <div class="card border-0 shadow-sm home-featured-card" style="overflow: hidden; background: white;">
                            <a href="@auth{{ route('design.templates.show', $template->id) }}@else{{ allow_registration() ? route('register') : route('login') }}@endauth" class="text-decoration-none d-block">
                                <div class="home-featured-thumbnail" @if(!$template->thumbnail_url) style="background: linear-gradient(135deg, {{ $ftGradients[$index % count($ftGradients)] }});" @endif>
                                    @if($template->thumbnail_url)
                                        <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}" loading="lazy">
                                    @else
                                        <i class="fas fa-file-alt text-white" style="font-size: 1.35rem; opacity: 0.9;"></i>
                                    @endif
                                    <span class="position-absolute top-0 start-0 m-1 badge rounded-pill" style="background: rgba(255,255,255,0.95); color: #d97706; font-size: 0.5rem; font-weight: 700; box-shadow: 0 1px 4px rgba(0,0,0,0.08); z-index: 2; padding: 0.15rem 0.35rem;">
                                        <i class="fas fa-star me-1" style="font-size:0.45rem;"></i>Featured
                                    </span>
                                </div>
                            </a>
                            <div class="card-body home-featured-card-body">
                                @if($template->category)
                                <span class="badge align-self-start" style="background: rgba(99, 102, 241, 0.1); color: {{ $ftBadges[$index % count($ftBadges)] }}; font-weight: 600;">{{ ucfirst(str_replace('-', ' ', $template->category)) }}</span>
                                @endif
                                <h3 class="card-title">{{ $template->name }}</h3>
                                @if($template->short_description)
                                <p class="text-muted small home-featured-desc mb-0">{{ \Illuminate\Support\Str::limit($template->short_description, 80) }}</p>
                                @endif
                                <div class="d-flex align-items-center justify-content-between home-featured-meta">
                                    @if($template->page_count)
                                    <span class="text-muted"><i class="fas fa-file me-1"></i>{{ $template->page_count }} {{ \Illuminate\Support\Str::plural('page', $template->page_count) }}</span>
                                    @else
                                    <span></span>
                                    @endif
                                    @if(isset($template->price) && (float) $template->price > 0)
                                    <span style="font-weight: 700; color: #6366f1;">{{ \App\Models\Setting::get('currency_symbol') ?? '$' }}{{ number_format((float) $template->price, 2) }}</span>
                                    @else
                                    <span style="font-weight: 700; color: #10b981;">Free</span>
                                    @endif
                                </div>
                                <a href="@auth{{ route('design.templates.show', $template->id) }}@else{{ allow_registration() ? route('register') : route('login') }}@endauth" class="btn w-100 home-featured-btn" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none; font-weight: 600;">
                                    <i class="fas fa-arrow-right me-1"></i>Use template
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="swiper-button-prev" aria-label="Previous"></div>
                <div class="swiper-button-next" aria-label="Next"></div>
                <div class="swiper-pagination"></div>
            </div>
            <div class="text-center home-featured-browse mt-1 pt-1">
                <a href="{{ route('templates') }}" class="btn" style="background: white; color: #6366f1; border: 2px solid #e2e8f0; padding: 0.45rem 1.35rem; font-weight: 600; font-size: 0.85rem; border-radius: 10px; transition: all 0.25s;" onmouseover="this.style.borderColor='#6366f1'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)'">
                    <i class="fas fa-th-large me-2"></i>Browse all templates
                </a>
            </div>
        </div>
    </div>
</section>
@endif

<!-- How It Works Section -->
<section class="py-5" style="background: white; padding: 4rem 0 !important;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-3" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 20px;">
                SIMPLE PROCESS
            </span>
            <h2 class="mb-3" style="font-size: 2.5rem; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">
                How It Works
            </h2>
            <p class="text-muted mx-auto" style="font-size: 1.05rem; max-width: 650px; line-height: 1.7;">
                Create professional flip books in just three simple steps. No technical knowledge required.
            </p>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); width: 90px; height: 90px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3); position: relative;">
                        <span style="position: absolute; top: -10px; right: -10px; background: #10b981; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);">1</span>
                        <i class="fas fa-pencil-alt text-white" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="mb-3" style="font-weight: 700; font-size: 1.3rem; color: #1e293b;">Design Your Content</h4>
                    <p class="text-muted" style="line-height: 1.7; font-size: 0.95rem;">
                        Use our intuitive design tool to create beautiful pages. Add images, text, and customize every element to match your style and brand.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-center">
                    <div style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); width: 90px; height: 90px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3); position: relative;">
                        <span style="position: absolute; top: -10px; right: -10px; background: #6366f1; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);">2</span>
                        <i class="fas fa-magic text-white" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="mb-3" style="font-weight: 700; font-size: 1.3rem; color: #1e293b;">Transform to Flip Book</h4>
                    <p class="text-muted" style="line-height: 1.7; font-size: 0.95rem;">
                        Convert your designs into an interactive flip book with smooth page transitions and engaging animations in just seconds.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-center">
                    <div style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); width: 90px; height: 90px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3); position: relative;">
                        <span style="position: absolute; top: -10px; right: -10px; background: #ef4444; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);">3</span>
                        <i class="fas fa-share text-white" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="mb-3" style="font-weight: 700; font-size: 1.3rem; color: #1e293b;">Share & Engage</h4>
                    <p class="text-muted" style="line-height: 1.7; font-size: 0.95rem;">
                        Share your flip book with a simple link. Your audience can view it on any device, anywhere in the world, instantly.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5" style="background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%); padding: 4rem 0 !important;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-3" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 20px;">
                TESTIMONIALS
            </span>
            <h2 class="mb-3" style="font-size: 2.5rem; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">
                Loved by Thousands
            </h2>
            <p class="text-muted mx-auto" style="font-size: 1.05rem; max-width: 650px; line-height: 1.7;">
                See what our customers are saying about their experience with {{ site_name() }}
            </p>
        </div>

        <div class="row mt-4">
            @forelse($testimonials as $testimonial)
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; padding: 2rem; background: white; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 30px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="mb-3">
                        @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= $testimonial->rating ? 'text-warning' : 'text-muted' }}" style="font-size: 0.9rem; opacity: {{ $i <= $testimonial->rating ? 1 : 0.4 }};"></i>
                        @endfor
                    </div>
                    <p class="text-muted mb-4" style="line-height: 1.7; font-size: 0.95rem; font-style: italic;">
                        "{{ $testimonial->content }}"
                    </p>
                    <div class="d-flex align-items-center">
                        @if($testimonial->avatar_url)
                        <img src="{{ asset($testimonial->avatar_url) }}" alt="{{ $testimonial->name }}" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; margin-right: 1rem;">
                        @else
                        <div style="width: 45px; height: 45px; border-radius: 50%; background: {{ $testimonial->getAvatarGradientStyle() }}; display: flex; align-items: center; justify-content: center; margin-right: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                            <span class="text-white fw-bold" style="font-size: 0.9rem;">{{ $testimonial->initials }}</span>
                        </div>
                        @endif
                        <div>
                            <h6 class="mb-0" style="font-weight: 700; font-size: 1rem; color: #1e293b;">{{ $testimonial->name }}</h6>
                            @if($testimonial->role)
                            <small class="text-muted" style="font-size: 0.85rem;">{{ $testimonial->role }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-4">
                <p class="text-muted mb-0">No testimonials to display yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 4rem 0 !important; position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.05\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E'); opacity: 0.3;"></div>
    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-10 mx-auto text-center">
                <h2 class="text-white mb-4" style="font-size: 2.5rem; font-weight: 800; letter-spacing: -0.5px;">
                    Ready to Create Your First<br>Flip Book?
                </h2>
                <p class="text-white mb-5" style="font-size: 1.15rem; opacity: 0.95; line-height: 1.7; max-width: 700px; margin: 0 auto;">
                    Join thousands of satisfied users creating beautiful flip books. Get started in minutes with our free plan - no credit card required.
                </p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    @if(allow_registration())
                    <a href="{{ route('register') }}" class="btn btn-light shadow-lg" style="padding: 1rem 2.5rem; font-weight: 700; font-size: 1.05rem; border-radius: 12px; transition: all 0.3s; box-shadow: 0 4px 14px rgba(0,0,0,0.2);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.25)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(0,0,0,0.2)'">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial
                    </a>
                    @else
                    <a href="{{ route('login') }}" class="btn btn-light shadow-lg" style="padding: 1rem 2.5rem; font-weight: 700; font-size: 1.05rem; border-radius: 12px; transition: all 0.3s; box-shadow: 0 4px 14px rgba(0,0,0,0.2);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.25)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(0,0,0,0.2)'">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    @endif
                    <a href="{{ route('contact') }}" class="btn btn-outline-light" style="padding: 1rem 2.5rem; font-weight: 600; font-size: 1.05rem; border-width: 2px; border-radius: 12px; transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='transparent'; this.style.transform='translateY(0)'">
                        <i class="fas fa-comments me-2"></i>Contact Sales
                    </a>
                </div>
                <div class="mt-4 text-white" style="opacity: 0.9;">
                    <small style="font-size: 0.875rem;"><i class="fas fa-shield-alt me-1"></i>Secure & Trusted</small>
                    <span class="mx-2">•</span>
                    <small style="font-size: 0.875rem;"><i class="fas fa-clock me-1"></i>Setup in 5 Minutes</small>
                    <span class="mx-2">•</span>
                    <small style="font-size: 0.875rem;"><i class="fas fa-headset me-1"></i>24/7 Support</small>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

<style>
    @media (max-width: 768px) {
        .hero-section h1 {
            font-size: 2.2rem !important;
        }
        .hero-section p {
            font-size: 1rem !important;
        }
        section h2 {
            font-size: 2rem !important;
        }
    }

    .card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card:hover {
        transform: translateY(-8px);
    }

    .feature-card-img-wrap {
        min-height: 140px;
    }
    .feature-card-img {
        object-fit: contain;
    }
    @media (max-width: 768px) {
        .feature-card-img-wrap {
            height: 160px !important;
        }
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
(function() {
    function initHomeFeaturedSwiper() {
        var el = document.getElementById('homeFeaturedSwiper');
        if (!el || typeof Swiper === 'undefined') return;
        var slides = el.querySelectorAll('.swiper-slide').length;
        if (slides === 0) return;
        var featuredSwiper = new Swiper(el, {
            slidesPerView: 2,
            spaceBetween: 10,
            watchOverflow: false,
            rewind: true,
            loop: slides >= 6,
            speed: 600,
            autoplay: slides >= 2 ? {
                delay: 3200,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            } : false,
            pagination: {
                el: el.querySelector('.swiper-pagination'),
                clickable: true,
            },
            navigation: {
                nextEl: el.querySelector('.swiper-button-next'),
                prevEl: el.querySelector('.swiper-button-prev'),
            },
            breakpoints: {
                0: { slidesPerView: 2, spaceBetween: 8 },
                576: { slidesPerView: 2, spaceBetween: 10 },
                768: { slidesPerView: 3, spaceBetween: 10 },
                992: { slidesPerView: 3, spaceBetween: 12 },
                1200: { slidesPerView: 4, spaceBetween: 14 },
            },
        });
        function syncFeaturedHeight() {
            featuredSwiper.update();
        }
        syncFeaturedHeight();
        requestAnimationFrame(syncFeaturedHeight);
        window.addEventListener('load', syncFeaturedHeight, { once: true });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHomeFeaturedSwiper);
    } else {
        initHomeFeaturedSwiper();
    }
})();
</script>
<script>
(function() {
    var wrap = document.getElementById('heroParallaxWrap');
    var img = document.getElementById('heroParallaxImg');
    var zone = document.getElementById('heroParallaxZone');
    if (!wrap || !img || !zone) return;

    var strength = 12;      // max movement in px for wrapper (background)
    var imgStrength = 20;   // max movement in px for character image (more depth)
    var smooth = 0.15;      // lerp factor (0–1), lower = smoother

    var currentX = 0, currentY = 0;
    var targetX = 0, targetY = 0;
    var currentImgX = 0, currentImgY = 0;
    var targetImgX = 0, targetImgY = 0;

    function lerp(a, b, t) { return a + (b - a) * t; }

    function updateTarget(e) {
        var rect = zone.getBoundingClientRect();
        var w = rect.width;
        var h = rect.height;
        var x = (e.clientX - rect.left - w / 2) / (w / 2);  // -1 to 1
        var y = (e.clientY - rect.top - h / 2) / (h / 2);
        targetX = x * strength;
        targetY = y * strength;
        targetImgX = x * imgStrength;
        targetImgY = y * imgStrength;
    }

    function tick() {
        currentX = lerp(currentX, targetX, smooth);
        currentY = lerp(currentY, targetY, smooth);
        currentImgX = lerp(currentImgX, targetImgX, smooth);
        currentImgY = lerp(currentImgY, targetImgY, smooth);
        wrap.style.transform = 'translate(' + currentX + 'px, ' + currentY + 'px)';
        img.style.transform = 'translate(' + currentImgX + 'px, ' + currentImgY + 'px)';
        requestAnimationFrame(tick);
    }

    zone.addEventListener('mousemove', updateTarget);
    zone.addEventListener('mouseleave', function() {
        targetX = targetY = targetImgX = targetImgY = 0;
    });

    requestAnimationFrame(tick);
})();
</script>
@endpush
