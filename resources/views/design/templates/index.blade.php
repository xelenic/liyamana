@extends('layouts.app')

@section('title', 'My Templates')
@section('page-title', 'My Templates')

@push('styles')
<style>
    :root {
        --templates-primary: #6366f1;
        --templates-secondary: #8b5cf6;
        --templates-bg: #f8fafc;
        --templates-card-bg: #ffffff;
        --templates-border: #e2e8f0;
        --templates-text: #1e293b;
        --templates-muted: #64748b;
    }

    .templates-page-header {
        background: linear-gradient(135deg, var(--templates-primary) 0%, var(--templates-secondary) 100%);
        border-radius: 16px;
        padding: 2rem 2rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    .templates-page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 60%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        pointer-events: none;
    }
    .templates-page-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 0.35rem 0;
        letter-spacing: -0.03em;
    }
    .templates-page-header p {
        margin: 0;
        opacity: 0.95;
        font-size: 0.9375rem;
    }
    .templates-page-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.25rem;
        flex-wrap: wrap;
    }
    .templates-page-actions .btn {
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        border-radius: 10px;
        font-size: 0.875rem;
    }
    .templates-page-actions .btn-outline-light {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.4);
        color: white;
    }
    .templates-page-actions .btn-outline-light:hover {
        background: rgba(255,255,255,0.25);
        border-color: white;
        color: white;
    }
    .templates-page-actions .btn-light {
        background: white;
        color: var(--templates-primary);
        border: none;
    }
    .templates-page-actions .btn-light:hover {
        background: #f8fafc;
        color: var(--templates-secondary);
    }

    .templates-tabs-wrapper {
        background: white;
        border-radius: 8px;
        border: 1px solid var(--templates-border);
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .templates-tabs {
        display: flex;
    }
    .templates-tab {
        flex: 1;
        padding: 0.5rem 1rem;
        border: none;
        background: transparent;
        color: var(--templates-muted);
        font-weight: 600;
        font-size: 0.8125rem;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        border-bottom: 2px solid transparent;
    }
    .templates-tab i {
        font-size: 0.75rem;
        opacity: 0.9;
    }
    .templates-tab:hover {
        color: var(--templates-primary);
        background: #f8fafc;
    }
    .templates-tab.active {
        color: var(--templates-primary);
        background: rgba(99,102,241,0.06);
        border-bottom-color: var(--templates-primary);
    }

    .template-card {
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        height: 100%;
        cursor: pointer;
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background: white;
        border: 1px solid var(--templates-border);
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .template-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(99,102,241,0.1);
        border-color: rgba(99,102,241,0.2);
    }
    .template-card:hover .template-thumbnail img {
        transform: scale(1.02);
    }

    #privateTemplatesContainer .col-6,
    #privateTemplatesContainer .col-md-4,
    #privateTemplatesContainer .col-lg-3,
    #publicTemplatesContainer .col-6,
    #publicTemplatesContainer .col-md-4,
    #publicTemplatesContainer .col-lg-3 {
        margin-bottom: 0.75rem;
    }

    .template-thumbnail {
        height: 140px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .template-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.25s ease;
    }
    .template-thumbnail .no-thumbnail {
        color: #94a3b8;
        font-size: 2rem;
        opacity: 0.5;
    }

    .template-info {
        padding: 0.65rem 0.875rem 0.75rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .template-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        margin-bottom: 0.35rem;
    }
    .template-category {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        padding: 0.2rem 0.4rem;
        background: rgba(99,102,241,0.08);
        color: var(--templates-primary);
        border-radius: 4px;
        font-size: 0.625rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .template-category.public-badge {
        background: #eef2ff;
        color: var(--templates-primary);
    }

    .template-title {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.2rem;
        color: var(--templates-text);
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .template-description {
        font-size: 0.75rem;
        color: var(--templates-muted);
        margin-bottom: 0.35rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }

    .template-price {
        font-size: 0.875rem;
        font-weight: 700;
        color: #10b981;
        margin-bottom: 0.25rem;
    }

    .template-meta {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
        font-size: 0.6875rem;
        color: #94a3b8;
    }
    .template-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
    }

    .template-actions {
        display: flex;
        gap: 0.35rem;
        margin-top: auto;
        padding-top: 0.5rem;
        border-top: 1px solid #f1f5f9;
    }
    .template-actions .btn {
        flex: 1;
        font-size: 0.75rem;
        padding: 0.4rem 0.5rem;
        border-radius: 6px;
        font-weight: 600;
    }
    .template-actions .btn-primary {
        background: linear-gradient(135deg, var(--templates-primary) 0%, var(--templates-secondary) 100%) !important;
        border: none !important;
    }

    .template-share-row {
        margin-top: 0.35rem;
        padding-top: 0.5rem;
        border-top: 1px solid #f1f5f9;
    }
    .template-share-row .btn {
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 6px;
    }

    .empty-state-card {
        background: white;
        border: 1px solid var(--templates-border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .empty-state {
        text-align: center;
        padding: 3.5rem 2rem;
    }
    .empty-state-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 2rem;
    }
    .empty-state h4 {
        color: var(--templates-text);
        margin-bottom: 0.5rem;
        font-weight: 700;
        font-size: 1.25rem;
    }
    .empty-state p {
        color: var(--templates-muted);
        margin-bottom: 1.5rem;
        font-size: 0.9375rem;
        line-height: 1.6;
        max-width: 420px;
        margin-left: auto;
        margin-right: auto;
    }
    .empty-state .btn {
        font-weight: 600;
        padding: 0.6rem 1.5rem;
        border-radius: 10px;
    }

    .use-template-modal {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0,0,0,0.2);
    }
    .use-template-modal .modal-header {
        background: linear-gradient(135deg, var(--templates-primary) 0%, var(--templates-secondary) 100%);
        padding: 1.5rem;
        color: white;
    }
    .use-template-modal .modal-option {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: white;
        border: 2px solid var(--templates-border);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
        width: 100%;
    }
    .use-template-modal .modal-option:hover {
        border-color: var(--templates-primary);
        box-shadow: 0 4px 12px rgba(99,102,241,0.15);
    }

    .review-modal .modal-content { max-width: 520px; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.15); }
    .review-modal .modal-header { background: linear-gradient(135deg, var(--templates-primary) 0%, var(--templates-secondary) 100%); color: white; padding: 1.25rem 1.5rem; }
    .review-modal .modal-body { padding: 1.5rem 1.5rem; }
    .review-modal .review-section { margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--templates-border); }
    .review-modal .review-section:last-of-type { margin-bottom: 0; padding-bottom: 0; border-bottom: 0; }
    .review-modal .review-section h6 { font-weight: 700; color: var(--templates-text); margin-bottom: 0.5rem; font-size: 0.9375rem; }
    .review-modal .review-stars { display: flex; gap: 0.25rem; margin-bottom: 0.75rem; }
    .review-modal .review-stars .star { font-size: 1.5rem; color: #e2e8f0; cursor: pointer; transition: color 0.15s; }
    .review-modal .review-stars .star:hover, .review-modal .review-stars .star.filled { color: #f59e0b; }
    .review-modal .review-stars .star input { display: none; }
    .review-modal .btn-submit-review { font-size: 0.8125rem; padding: 0.4rem 0.75rem; }
    .review-modal .maybe-later { color: var(--templates-muted); font-size: 0.875rem; cursor: pointer; text-decoration: none; }
    .review-modal .maybe-later:hover { color: var(--templates-primary); }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="templates-page-header">
    <h1>My Templates</h1>
    <p>Manage your saved design templates</p>
    <div class="templates-page-actions">
        <a href="{{ route('design.templates.explore') }}" class="btn btn-outline-light">
            <i class="fas fa-compass me-2"></i>Explore Templates
        </a>
        <button class="btn btn-outline-light" onclick="loadTemplates()" title="Refresh">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </button>
        <a href="{{ route('design.create', ['multi' => 'true']) }}" class="btn btn-light">
            <i class="fas fa-plus me-2"></i>Create New Design
        </a>
    </div>
</div>

@include('design.partials.ai-content-templates')

<!-- Tabs -->
<div class="templates-tabs-wrapper">
    <div class="templates-tabs" id="templateTabs" role="tablist">
        <button class="templates-tab active" id="private-tab" data-bs-toggle="tab" data-bs-target="#private-templates" type="button" role="tab" onclick="switchTemplateTab('private')">
            <i class="fas fa-lock"></i>
            <span>Private</span>
        </button>
        <button class="templates-tab" id="public-tab" data-bs-toggle="tab" data-bs-target="#public-templates" type="button" role="tab" onclick="switchTemplateTab('public')">
            <i class="fas fa-globe"></i>
            <span>Public</span>
        </button>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content" id="templateTabContent">
    <div class="tab-pane fade show active" id="private-templates" role="tabpanel">
        <div class="row" id="privateTemplatesContainer"></div>
        <div id="privateEmptyState" class="empty-state-card mt-4" style="display: none;">
            <div class="empty-state">
                <div style="background: url('{{url('feature_actor/not_found.png')}}');background-position: center;background-repeat: no-repeat;height: 300px;background-size: contain;margin-bottom: 20px;"></div>
                <h4>No Private Templates Yet</h4>
                <p>Save designs as private templates to reuse them later. Private templates are only visible to you.</p>
                <a href="{{ route('design.create', ['multi' => 'true']) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Your First Design
                </a>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="public-templates" role="tabpanel">
        <div class="row" id="publicTemplatesContainer"></div>
        <div id="publicEmptyState" class="empty-state-card mt-4" style="display: none;">
            <div class="empty-state">
                <div style="background: url('{{url('feature_actor/not_found.png')}}');background-position: center;background-repeat: no-repeat;height: 300px;background-size: contain;margin-bottom: 20px;"></div>
                <h4>No Public Templates Yet</h4>
                <p>You haven't published any templates yet. Save a design as a public template to share it with others.</p>
                <a href="{{ route('design.create', ['multi' => 'true']) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Your First Design
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Use Template Modal -->
<div id="useTemplateModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);" onclick="if(event.target === this) closeUseTemplateModal();">
    <div class="modal-content use-template-modal" style="background: white; margin: 10% auto; width: 90%; max-width: 500px; overflow: hidden;" onclick="event.stopPropagation();">
        <div class="modal-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0" style="font-size: 1.35rem; font-weight: 700;">Use Template</h3>
            <button onclick="closeUseTemplateModal()" style="background: rgba(255,255,255,0.2); border: none; font-size: 1.5rem; cursor: pointer; color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">&times;</button>
        </div>
        <div style="padding: 2rem;">
            <p style="color: #64748b; margin-bottom: 1.5rem; font-size: 0.9375rem;">How would you like to use this template?</p>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <button onclick="useTemplateForDesign()" class="modal-option">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-palette text-white" style="font-size: 1.25rem;"></i>
                    </div>
                    <div style="flex: 1; text-align: left;">
                        <div style="font-weight: 600; color: #1e293b; font-size: 1rem; margin-bottom: 0.25rem;">Use Template for Design Page</div>
                        <div style="font-size: 0.8125rem; color: #64748b;">Open in multi-page design tool for editing</div>
                    </div>
                    <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                </button>
                <button onclick="quickUseTemplate()" class="modal-option">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-bolt text-white" style="font-size: 1.25rem;"></i>
                    </div>
                    <div style="flex: 1; text-align: left;">
                        <div style="font-weight: 600; color: #1e293b; font-size: 1rem; margin-bottom: 0.25rem;">Quick Use</div>
                        <div style="font-size: 0.8125rem; color: #64748b;">Quick use with variable forms</div>
                    </div>
                    <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                </button>
                <div id="manageTemplateOption" style="display: none;">
                    <button onclick="manageTemplate()" class="modal-option">
                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-cog text-white" style="font-size: 1.25rem;"></i>
                        </div>
                        <div style="flex: 1; text-align: left;">
                            <div style="font-weight: 600; color: #1e293b; font-size: 1rem; margin-bottom: 0.25rem;">Manage Template</div>
                            <div style="font-size: 0.8125rem; color: #64748b;">View details, stats and actions</div>
                        </div>
                        <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@include('design.partials.share-template-modal')

@if(!empty($orderReviewPrompt))
<!-- Post-order: Review template & testimonial modal -->
<div id="orderReviewModal" class="modal fade review-modal" tabindex="-1" aria-labelledby="orderReviewModalLabel" data-bs-backdrop="static" data-bs-keyboard="true" data-template-id="{{ $orderReviewPrompt['template_id'] }}" data-template-name="{{ e($orderReviewPrompt['template_name']) }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-heart me-2"></i>Thank you for your order!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="closeOrderReviewModal()"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Your feedback helps us and other users. You can leave one or both below.</p>

                <div id="templateReviewSection" class="review-section">
                    <h6>How was your experience with <strong id="reviewTemplateName">{{ $orderReviewPrompt['template_name'] }}</strong>?</h6>
                    <div class="review-stars" id="templateReviewStars">
                        @for($i = 1; $i <= 5; $i++)
                        <label class="star" title="{{ $i }} star"><input type="radio" name="template_rating" value="{{ $i }}"><i class="fas fa-star"></i></label>
                        @endfor
                    </div>
                    <textarea id="templateReviewText" class="form-control form-control-sm mb-2" rows="2" placeholder="Optional: write a short review..." maxlength="2000"></textarea>
                    <button type="button" class="btn btn-primary btn-submit-review" id="submitTemplateReviewBtn" onclick="submitTemplateReview()"><i class="fas fa-paper-plane me-1"></i>Submit review</button>
                    <span id="templateReviewDone" class="text-success small ms-2" style="display: none;"><i class="fas fa-check"></i> Thank you!</span>
                </div>

                <div id="testimonialSection" class="review-section">
                    <h6>Share your experience with our platform</h6>
                    <div class="review-stars" id="testimonialStars">
                        @for($i = 1; $i <= 5; $i++)
                        <label class="star" title="{{ $i }} star"><input type="radio" name="testimonial_rating" value="{{ $i }}"><i class="fas fa-star"></i></label>
                        @endfor
                    </div>
                    <textarea id="testimonialContent" class="form-control form-control-sm mb-2" rows="3" placeholder="Your testimonial (min 10 characters)..." maxlength="2000" required></textarea>
                    <button type="button" class="btn btn-primary btn-submit-review" id="submitTestimonialBtn" onclick="submitTestimonial()"><i class="fas fa-quote-right me-1"></i>Submit testimonial</button>
                    <span id="testimonialDone" class="text-success small ms-2" style="display: none;"><i class="fas fa-check"></i> Thank you!</span>
                </div>

                <div class="text-center pt-2">
                    <a href="#" class="maybe-later" onclick="closeOrderReviewModal(); return false;">Maybe later</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    window.currencySymbol = @json(\App\Models\Setting::get('currency_symbol') ?: '$');
    window.currencyDecimals = parseInt(@json(\App\Models\Setting::get('price_decimal_places') ?: 2), 10);
    function formatPrice(amount) {
        const sym = window.currencySymbol || '$';
        const dec = window.currencyDecimals ?? 2;
        return sym + parseFloat(amount ?? 0).toFixed(dec);
    }

    let currentTab = 'private';

    // Switch between tabs
    function switchTemplateTab(tab) {
        currentTab = tab;

        // Update tab buttons
        document.querySelectorAll('#templateTabs .templates-tab').forEach(btn => {
            btn.classList.remove('active');
        });

        if (tab === 'private') {
            document.getElementById('private-tab').classList.add('active');
            document.getElementById('private-templates').classList.add('show', 'active');
            document.getElementById('public-templates').classList.remove('show', 'active');
        } else {
            document.getElementById('public-tab').classList.add('active');
            document.getElementById('public-templates').classList.add('show', 'active');
            document.getElementById('private-templates').classList.remove('show', 'active');
        }

        // Load templates for the active tab
        loadTemplates();
    }

    // Load templates
    function loadTemplates() {
        // Load both private and public templates
        fetch('{{ route("design.templates.index") }}', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.templates && data.templates.length > 0) {
                // Separate templates by type
                const privateTemplates = data.templates.filter(t => !t.is_public);
                const publicTemplates = data.templates.filter(t => t.is_public);

                // Render private templates
                const privateContainer = document.getElementById('privateTemplatesContainer');
                privateContainer.innerHTML = '';

                if (privateTemplates.length > 0) {
                    privateTemplates.forEach(template => {
                        const templateCard = createTemplateCard(template);
                        privateContainer.appendChild(templateCard);
                    });
                    document.getElementById('privateEmptyState').style.display = 'none';
                } else {
                    document.getElementById('privateEmptyState').style.display = 'block';
                }

                // Render public templates
                const publicContainer = document.getElementById('publicTemplatesContainer');
                publicContainer.innerHTML = '';

                if (publicTemplates.length > 0) {
                    publicTemplates.forEach(template => {
                        const templateCard = createTemplateCard(template);
                        publicContainer.appendChild(templateCard);
                    });
                    document.getElementById('publicEmptyState').style.display = 'none';
                } else {
                    document.getElementById('publicEmptyState').style.display = 'block';
                }
            } else {
                // No templates at all
                document.getElementById('privateEmptyState').style.display = 'block';
                document.getElementById('publicEmptyState').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading templates:', error);
            document.getElementById('privateEmptyState').style.display = 'block';
            document.getElementById('publicEmptyState').style.display = 'block';
        });
    }

    function createTemplateCard(template) {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-4 col-lg-3';
        col.dataset.templateId = template.id;
        col.dataset.templateType = template.type || '';

        const card = document.createElement('div');
        card.className = 'card template-card';

        const thumbnail = document.createElement('div');
        thumbnail.className = 'template-thumbnail';
        if (template.thumbnail_url) {
            const img = document.createElement('img');
            img.src = template.thumbnail_url;
            img.alt = template.name;
            img.onerror = function() {
                this.style.display = 'none';
                const icon = document.createElement('div');
                icon.className = 'no-thumbnail';
                icon.innerHTML = '<i class="fas fa-layer-group"></i>';
                thumbnail.appendChild(icon);
            };
            thumbnail.appendChild(img);
        } else if (template.thumbnail) {
            const img = document.createElement('img');
            img.src = template.thumbnail;
            img.alt = template.name;
            img.onerror = function() {
                this.style.display = 'none';
                const icon = document.createElement('div');
                icon.className = 'no-thumbnail';
                icon.innerHTML = '<i class="fas fa-layer-group"></i>';
                thumbnail.appendChild(icon);
            };
            thumbnail.appendChild(img);
        } else {
            const icon = document.createElement('div');
            icon.className = 'no-thumbnail';
            icon.innerHTML = '<i class="fas fa-layer-group"></i>';
            thumbnail.appendChild(icon);
        }

        const info = document.createElement('div');
        info.className = 'template-info';

        const badges = document.createElement('div');
        badges.className = 'template-badges';
        if (template.is_public) {
            const publicBadge = document.createElement('span');
            publicBadge.className = 'template-category public-badge';
            publicBadge.innerHTML = '<i class="fas fa-globe"></i> Public';
            badges.appendChild(publicBadge);
        }
        if (template.category && template.category !== 'general') {
            const category = document.createElement('span');
            category.className = 'template-category';
            category.textContent = template.category.charAt(0).toUpperCase() + template.category.slice(1);
            badges.appendChild(category);
        }
        info.appendChild(badges);

        if (template.is_public && template.price !== null) {
            const price = document.createElement('div');
            price.className = 'template-price';
            price.innerHTML = '<i class="fas fa-dollar-sign me-1"></i>' + formatPrice(template.price);
            info.appendChild(price);
        }

        const title = document.createElement('h5');
        title.className = 'template-title';
        title.textContent = template.name || 'Untitled Template';

        // Show short description for public templates, or description for private
        const descriptionText = template.is_public ? (template.short_description || template.description) : template.description;
        if (descriptionText) {
            const description = document.createElement('div');
            description.className = 'template-description';
            description.textContent = descriptionText;
            info.appendChild(description);
        }

        const meta = document.createElement('div');
        meta.className = 'template-meta';

        if (template.page_count) {
            const pageCount = document.createElement('span');
            pageCount.innerHTML = '<i class="fas fa-file-alt me-1"></i>' + template.page_count + ' page' + (template.page_count > 1 ? 's' : '');
            meta.appendChild(pageCount);
        }

        if (template.created_at) {
            const createdAt = document.createElement('span');
            createdAt.innerHTML = '<i class="fas fa-calendar me-1"></i>' + new Date(template.created_at).toLocaleDateString();
            meta.appendChild(createdAt);
        }

        const actions = document.createElement('div');
        actions.className = 'template-actions';

        const editBtn = document.createElement('button');
        editBtn.className = 'btn btn-sm btn-outline-primary';
        editBtn.innerHTML = '<i class="fas fa-edit me-1"></i>Edit';
        editBtn.title = 'Edit template in design tool';
        editBtn.onclick = function(e) {
            e.stopPropagation();
            editTemplate(template.id, template.type || '');
        };

        const useBtn = document.createElement('button');
        useBtn.className = 'btn btn-sm btn-primary';
        useBtn.innerHTML = '<i class="fas fa-download me-1"></i>Use';
        useBtn.onclick = function(e) {
            e.stopPropagation();
            useTemplate(template.id, !!template.is_public);
        };

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn btn-sm btn-outline-danger';
        deleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete';
        deleteBtn.onclick = function(e) {
            e.stopPropagation();
            if (confirm('Are you sure you want to delete this template?')) {
                deleteTemplate(template.id, col);
            }
        };

        actions.appendChild(editBtn);
        actions.appendChild(useBtn);
        actions.appendChild(deleteBtn);

        const shareRow = document.createElement('div');
        shareRow.className = 'template-share-row';
        const shareBtn = document.createElement('button');
        shareBtn.type = 'button';
        shareBtn.className = 'btn btn-sm btn-outline-secondary w-100';
        shareBtn.innerHTML = '<i class="fas fa-share-alt me-1"></i>Share';
        shareBtn.title = 'Share template';
        shareBtn.onclick = function(e) {
            e.stopPropagation();
            if (typeof openShareTemplateModal === 'function') {
                openShareTemplateModal(template.id, template.name || 'Template', !!template.is_public);
            }
        };

        shareRow.appendChild(shareBtn);

        info.appendChild(title);
        info.appendChild(meta);
        info.appendChild(actions);
        info.appendChild(shareRow);

        card.appendChild(thumbnail);
        card.appendChild(info);

        card.onclick = function() {
            useTemplate(template.id, !!template.is_public);
        };

        col.appendChild(card);
        return col;
    }

    let currentTemplateId = null;
    let currentTemplateIsPublic = false;

    function useTemplate(templateId, isPublic) {
        currentTemplateId = templateId;
        currentTemplateIsPublic = !!isPublic;
        var manageOpt = document.getElementById('manageTemplateOption');
        if (manageOpt) manageOpt.style.display = currentTemplateIsPublic ? 'block' : 'none';
        document.getElementById('useTemplateModal').style.display = 'block';
    }

    function closeUseTemplateModal() {
        document.getElementById('useTemplateModal').style.display = 'none';
        currentTemplateId = null;
        currentTemplateIsPublic = false;
        var manageOpt = document.getElementById('manageTemplateOption');
        if (manageOpt) manageOpt.style.display = 'none';
    }

    function manageTemplate() {
        if (!currentTemplateId) return;
        window.location.href = '{{ route("design.templates.manage", ":id") }}'.replace(':id', currentTemplateId);
    }

    function editTemplate(templateId, templateType) {
        // Redirect to multi-page editor with template ID and type for editing
        const baseUrl = '{{ route("design.create") }}';
        let url = baseUrl + '?multi=true&template=' + encodeURIComponent(templateId);
        if (templateType) url += '&type=' + encodeURIComponent(templateType);
        window.location.href = url;
    }

    function useTemplateForDesign() {
        if (!currentTemplateId) return;
        const template = getCurrentTemplate();
        editTemplate(currentTemplateId, template ? template.type : '');
    }

    function getCurrentTemplate() {
        const privateCards = document.querySelectorAll('#privateTemplatesContainer .template-card');
        const publicCards = document.querySelectorAll('#publicTemplatesContainer .template-card');
        const allCards = [...privateCards, ...publicCards];
        for (const card of allCards) {
            const col = card.closest('.col-6, .col-md-4, .col-lg-3');
            if (col && col.dataset.templateId == currentTemplateId) {
                return col.dataset.templateType ? { type: col.dataset.templateType } : null;
            }
        }
        return null;
    }

    function quickUseTemplate() {
        if (!currentTemplateId) return;
        const template = getCurrentTemplate();
        const templateType = template ? template.type : '';
        // Letter type -> Send Letter checkout; others -> Quick Use
        if (templateType === 'letter') {
            window.location.href = '{{ route("design.templates.sendLetter", ":id") }}'.replace(':id', currentTemplateId);
        } else {
            window.location.href = '{{ route("design.templates.quickUse", ":id") }}'.replace(':id', currentTemplateId);
        }
    }

    function deleteTemplate(templateId, element) {
        fetch('{{ route("design.templates.destroy", ":id") }}'.replace(':id', encodeURIComponent(templateId)), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.style.transition = 'opacity 0.3s, transform 0.3s';
                element.style.opacity = '0';
                element.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    element.remove();

                    // Check which container this template was in
                    const isPrivate = element.closest('#privateTemplatesContainer') !== null;
                    const container = isPrivate ?
                        document.getElementById('privateTemplatesContainer') :
                        document.getElementById('publicTemplatesContainer');
                    const emptyState = isPrivate ?
                        document.getElementById('privateEmptyState') :
                        document.getElementById('publicEmptyState');

                    // Check if there are any template cards left in this container
                    const remainingTemplates = container.querySelectorAll('.template-card');
                    if (remainingTemplates.length === 0) {
                        emptyState.style.display = 'block';
                    }
                }, 300);
            } else {
                alert('Failed to delete template: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting template');
        });
    }

    // Load templates on page load
    document.addEventListener('DOMContentLoaded', loadTemplates);

    @if(!empty($orderReviewPrompt))
    // Post-order review modal: show on load and wire stars + submit
    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('orderReviewModal');
        if (!modalEl) return;
        // Star hover/click for template review
        modalEl.querySelectorAll('#templateReviewStars .star').forEach(function(star, i) {
            const value = i + 1;
            star.addEventListener('click', function() {
                star.querySelector('input').checked = true;
                modalEl.querySelectorAll('#templateReviewStars .star').forEach(function(s, j) {
                    s.classList.toggle('filled', j < value);
                });
            });
            star.addEventListener('mouseenter', function() {
                modalEl.querySelectorAll('#templateReviewStars .star').forEach(function(s, j) {
                    s.classList.toggle('filled', j < value);
                });
            });
            star.addEventListener('mouseleave', function() {
                const checked = modalEl.querySelector('input[name="template_rating"]:checked');
                const v = checked ? parseInt(checked.value, 10) : 0;
                modalEl.querySelectorAll('#templateReviewStars .star').forEach(function(s, j) {
                    s.classList.toggle('filled', j < v);
                });
            });
        });
        modalEl.querySelectorAll('#testimonialStars .star').forEach(function(star, i) {
            const value = i + 1;
            star.addEventListener('click', function() {
                star.querySelector('input').checked = true;
                modalEl.querySelectorAll('#testimonialStars .star').forEach(function(s, j) {
                    s.classList.toggle('filled', j < value);
                });
            });
            star.addEventListener('mouseenter', function() {
                modalEl.querySelectorAll('#testimonialStars .star').forEach(function(s, j) {
                    s.classList.toggle('filled', j < value);
                });
            });
            star.addEventListener('mouseleave', function() {
                const checked = modalEl.querySelector('input[name="testimonial_rating"]:checked');
                const v = checked ? parseInt(checked.value, 10) : 0;
                modalEl.querySelectorAll('#testimonialStars .star').forEach(function(s, j) {
                    s.classList.toggle('filled', j < v);
                });
            });
        });
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const m = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true });
            m.show();
        } else {
            modalEl.style.display = 'block';
        }
    });

    function closeOrderReviewModal() {
        const modalEl = document.getElementById('orderReviewModal');
        if (!modalEl) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const m = bootstrap.Modal.getInstance(modalEl);
            if (m) m.hide();
        } else {
            modalEl.style.display = 'none';
        }
    }

    function submitTemplateReview() {
        const modal = document.getElementById('orderReviewModal');
        const templateId = modal && modal.dataset.templateId;
        const ratingEl = modal && modal.querySelector('input[name="template_rating"]:checked');
        const rating = ratingEl ? parseInt(ratingEl.value, 10) : 0;
        const review = document.getElementById('templateReviewText') && document.getElementById('templateReviewText').value.trim();
        if (!templateId || !rating) {
            alert('Please select a star rating.');
            return;
        }
        const btn = document.getElementById('submitTemplateReviewBtn');
        if (btn) btn.disabled = true;
        fetch('{{ route("design.review.submitTemplate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ template_id: parseInt(templateId, 10), rating: rating, review: review || '' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('templateReviewDone').style.display = 'inline';
                document.getElementById('templateReviewSection').querySelector('textarea').disabled = true;
                modal.querySelectorAll('#templateReviewStars .star').forEach(s => s.style.pointerEvents = 'none');
            } else {
                alert(data.message || 'Could not submit review.');
                if (btn) btn.disabled = false;
            }
        })
        .catch(() => {
            alert('Something went wrong. Please try again.');
            if (btn) btn.disabled = false;
        });
    }

    function submitTestimonial() {
        const modal = document.getElementById('orderReviewModal');
        const ratingEl = modal && modal.querySelector('input[name="testimonial_rating"]:checked');
        const rating = ratingEl ? parseInt(ratingEl.value, 10) : 0;
        const content = document.getElementById('testimonialContent') && document.getElementById('testimonialContent').value.trim();
        if (!rating) {
            alert('Please select a star rating.');
            return;
        }
        if (!content || content.length < 10) {
            alert('Please write at least 10 characters for your testimonial.');
            return;
        }
        const btn = document.getElementById('submitTestimonialBtn');
        if (btn) btn.disabled = true;
        fetch('{{ route("design.testimonial.submit") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ content: content, rating: rating })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('testimonialDone').style.display = 'inline';
                document.getElementById('testimonialContent').disabled = true;
                modal.querySelectorAll('#testimonialStars .star').forEach(s => s.style.pointerEvents = 'none');
            } else {
                alert(data.message || 'Could not submit testimonial.');
                if (btn) btn.disabled = false;
            }
        })
        .catch(() => {
            alert('Something went wrong. Please try again.');
            if (btn) btn.disabled = false;
        });
    }
    @endif
</script>
@endpush

