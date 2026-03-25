@extends('layouts.app')

@section('title', 'Our Services - ' . site_name())

@section('content')
<!-- Hero Section -->
<section class="hero-section" style="margin-top: 0px !important; padding: 6rem 1rem 4rem; position: relative; overflow: hidden; min-height: 420px;">
    <!-- Background Video -->
    <div style="position: absolute; inset: 0; z-index: 0;">
        <video autoplay muted loop playsinline style="width: 100%; height: 100%; object-fit: cover;" aria-hidden="true">
            <source src="{{ asset('feature_actor/0222.mp4') }}" type="video/mp4">
        </video>
        <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15, 23, 42, 0.75) 0%, rgba(15, 23, 42, 0.5) 50%, rgba(15, 23, 42, 0.85) 100%);"></div>
    </div>

    <div class="container position-relative" style="z-index: 1;">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <!-- Badge -->
                <div class="mb-3">
                    <span class="badge px-3 py-2" style="background: rgba(255, 255, 255, 0.2); color: #fff; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 20px;">
                        <i class="fas fa-star me-2"></i>Comprehensive Solutions
                    </span>
                </div>

                <!-- Main Heading -->
                <h1 class="mb-4" style="font-size: 3.5rem; font-weight: 900; line-height: 1.1; letter-spacing: -0.5px; color: #fff;">
                    Our <span style="background: linear-gradient(135deg, #a5b4fc 0%, #c4b5fd 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; display: inline-block;">Services</span>
                </h1>

                <!-- Subheading -->
                <p class="mb-0 mx-auto" style="font-size: 1.25rem; line-height: 1.7; font-weight: 400; color: rgba(255, 255, 255, 0.9); max-width: 700px;">
                    Everything you need to create, customize, and share stunning flip books. Professional tools designed to bring your content to life.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5" style="background: linear-gradient(to bottom, #ffffff 0%, #f8fafc 100%); padding: 4rem 0 !important;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-3" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 20px;">
                WHAT WE OFFER
            </span>
            <h2 class="mb-3" style="font-size: 2.5rem; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">
                Complete Flip Book Solutions
            </h2>
            <p class="text-muted mx-auto" style="font-size: 1.05rem; max-width: 650px; line-height: 1.7;">
                From design to distribution, we provide all the tools and services you need to create professional flip books
            </p>
        </div>

        <div class="row mt-4">
            <!-- Service 1 -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4">
                        <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); width: 65px; height: 65px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);">
                            <i class="fas fa-palette text-white" style="font-size: 1.6rem;"></i>
                        </div>
                        <h4 class="card-title mb-3" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">Design & Customization</h4>
                        <p class="text-muted mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                            Create stunning designs with our powerful design tool. Customize every element, add your branding, and make your flip books truly unique.
                        </p>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Drag-and-drop design interface
                            </li>
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Custom branding and colors
                            </li>
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Image and text editing tools
                            </li>
                            <li class="mb-0 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Template customization
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Service 2 -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4">
                        <div style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); width: 65px; height: 65px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);">
                            <i class="fas fa-book text-white" style="font-size: 1.6rem;"></i>
                        </div>
                        <h4 class="card-title mb-3" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">Flip Book Creation</h4>
                        <p class="text-muted mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                            Transform your content into interactive flip books with smooth page transitions and engaging animations that captivate your audience.
                        </p>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Interactive page flipping
                            </li>
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Multiple page layouts
                            </li>
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Animation effects
                            </li>
                            <li class="mb-0 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Mobile-responsive design
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Service 3 -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4">
                        <div style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); width: 65px; height: 65px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);">
                            <i class="fas fa-share-alt text-white" style="font-size: 1.6rem;"></i>
                        </div>
                        <h4 class="card-title mb-3" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">Sharing & Distribution</h4>
                        <p class="text-muted mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                            Share your flip books easily with a simple link. Perfect for marketing, portfolios, catalogs, and more. Reach your audience anywhere.
                        </p>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Shareable links
                            </li>
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Embed codes
                            </li>
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Social media integration
                            </li>
                            <li class="mb-0 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Analytics tracking
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Service 4 -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4">
                        <div style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); width: 65px; height: 65px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);">
                            <i class="fas fa-folder-open text-white" style="font-size: 1.6rem;"></i>
                        </div>
                        <h4 class="card-title mb-3" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">Template Library</h4>
                        <p class="text-muted mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                            Access a wide range of professionally designed templates for various industries and use cases. Start with a template and customize it to your needs.
                        </p>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Professional templates
                            </li>
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Industry-specific designs
                            </li>
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Easy customization
                            </li>
                            <li class="mb-0 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Regular new additions
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Service 5 -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4">
                        <div style="background: linear-gradient(135deg, #ef4444 0%, #f87171 100%); width: 65px; height: 65px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);">
                            <i class="fas fa-images text-white" style="font-size: 1.6rem;"></i>
                        </div>
                        <h4 class="card-title mb-3" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">Image Management</h4>
                        <p class="text-muted mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                            Organize and manage all your images in one place with our built-in image library. Upload, organize, and access your assets effortlessly.
                        </p>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Cloud storage
                            </li>
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Image organization
                            </li>
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Quick access
                            </li>
                            <li class="mb-0 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Bulk upload support
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Service 6 -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: all 0.3s ease; overflow: hidden; background: white;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                    <div class="card-body p-4">
                        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); width: 65px; height: 65px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);">
                            <i class="fas fa-headset text-white" style="font-size: 1.6rem;"></i>
                        </div>
                        <h4 class="card-title mb-3" style="font-weight: 700; font-size: 1.25rem; color: #1e293b;">Support & Training</h4>
                        <p class="text-muted mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                            Get the help you need with comprehensive support and training resources. We're here to ensure your success every step of the way.
                        </p>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                24/7 customer support
                            </li>
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Video tutorials
                            </li>
                            <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Documentation
                            </li>
                            <li class="mb-0 d-flex align-items-center" style="font-size: 0.9rem; color: #64748b;">
                                <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">
                                    <i class="fas fa-check text-white" style="font-size: 0.6rem;"></i>
                                </div>
                                Community forum
                            </li>
                        </ul>
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
                    <h2 class="mb-3" style="font-size: 2.5rem; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">Ready to Get Started?</h2>
                    <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.7; max-width: 600px; margin: 0 auto;">
                        Experience all these services with a free account today. No credit card required, start creating in minutes.
                    </p>
                    <div class="d-flex gap-3 flex-wrap justify-content-center align-items-center">
                        @if(allow_registration())
                        <a href="{{ route('register') }}" class="btn shadow-lg" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border: none; padding: 1rem 2.5rem; font-weight: 700; font-size: 1.05rem; border-radius: 12px; transition: all 0.3s; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(99, 102, 241, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(99, 102, 241, 0.3)'">
                            <i class="fas fa-rocket me-2"></i>Start Free Trial
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
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
