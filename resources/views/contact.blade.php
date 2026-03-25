@extends('layouts.app')

@section('title', 'Contact Us - ' . site_name())

@section('content')
<!-- Hero Section -->
<section class="hero-section" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 3rem 0; margin-top: -1.5rem;">
    <div class="container">
        <div class="text-center text-white">
            <h1 class="mb-3" style="font-size: 2.5rem; font-weight: 700;">Contact Us</h1>
            <p class="mb-0" style="font-size: 1.2rem; opacity: 0.9;">We'd love to hear from you. Get in touch with our team</p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-5" style="background: white;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="row mb-5">
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="text-center">
                            <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                <i class="fas fa-envelope text-white" style="font-size: 1.5rem;"></i>
                            </div>
                            <h5 class="mb-2" style="font-weight: 600;">Email Us</h5>
                            <p class="text-muted small mb-0">support@flipbook.com</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="text-center">
                            <div style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                <i class="fas fa-phone text-white" style="font-size: 1.5rem;"></i>
                            </div>
                            <h5 class="mb-2" style="font-weight: 600;">Call Us</h5>
                            <p class="text-muted small mb-0">+1 (555) 123-4567</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="text-center">
                            <div style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                <i class="fas fa-clock text-white" style="font-size: 1.5rem;"></i>
                            </div>
                            <h5 class="mb-2" style="font-weight: 600;">Business Hours</h5>
                            <p class="text-muted small mb-0">Mon - Fri: 9am - 6pm</p>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="mb-4" style="font-weight: 600; color: #1e293b;">Send us a Message</h3>
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" placeholder="Your name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" placeholder="your.email@example.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" placeholder="+1 (555) 123-4567">
                                </div>
                                <div class="col-md-6">
                                    <label for="subject" class="form-label">Subject</label>
                                    <select class="form-select" id="subject" required>
                                        <option value="">Select a subject</option>
                                        <option value="support">Technical Support</option>
                                        <option value="sales">Sales Inquiry</option>
                                        <option value="billing">Billing Question</option>
                                        <option value="feature">Feature Request</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" rows="5" placeholder="Tell us how we can help you..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg" style="padding: 0.75rem 2.5rem; font-weight: 600; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none;">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5" style="background: #f8fafc;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="mb-3" style="font-weight: 700; color: #1e293b; font-size: 2rem;">Frequently Asked Questions</h2>
            <p class="text-muted">Quick answers to common questions</p>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I get started with {{ site_name() }}?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Getting started is easy! Simply create a free account, choose a template or start from scratch, and begin designing your flip book. No credit card required.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Can I customize templates?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Yes! All templates are fully customizable. You can change colors, fonts, images, text, and layout to match your brand and needs.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How do I share my flip book?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Once your flip book is ready, you can share it using a simple link or embed code. Share via email, social media, or embed it on your website.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Is there a mobile app?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Our platform is fully responsive and works great on mobile browsers. You can create and view flip books on any device with an internet connection.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection






