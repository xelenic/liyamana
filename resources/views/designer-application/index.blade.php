@extends('layouts.app')

@section('title', 'Become a Designer - ' . site_name())
@section('page-title', 'Become a Designer')

@push('styles')
<style>
    .designer-page { min-height: calc(100vh - 120px); }
    .designer-wizard {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 10px 15px -3px rgba(0,0,0,0.08), 0 0 0 1px rgba(0,0,0,0.04);
        overflow: hidden;
        width: 100%;
        border: 1px solid #e2e8f0;
    }
    .designer-tips-sidebar {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        position: sticky;
        top: 80px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
    }
    .designer-tips-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .designer-tips-title i {
        color: #4f46e5;
        font-size: 1.1rem;
    }
    .designer-tip-item {
        padding: 0.875rem 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
        line-height: 1.6;
        color: #475569;
    }
    .designer-tip-item:last-child { border-bottom: none; }
    .designer-tip-item strong {
        color: #1e293b;
        font-size: 0.8125rem;
        display: block;
        margin-bottom: 0.25rem;
    }
    .designer-tip-item i {
        color: #4f46e5;
        margin-right: 0.5rem;
        width: 18px;
        text-align: center;
    }
    .designer-wizard-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        padding: 2rem 2.5rem;
        position: relative;
        overflow: hidden;
    }
    .designer-wizard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 60%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
        pointer-events: none;
    }
    .designer-wizard-title {
        font-weight: 700;
        font-size: 1.5rem;
        letter-spacing: -0.025em;
        color: #fff;
        margin-bottom: 0.25rem;
        position: relative;
    }
    .designer-wizard-subtitle {
        font-size: 0.9375rem;
        color: rgba(255,255,255,0.9);
        position: relative;
    }
    /* Step Progress Bar */
    .designer-steps {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-top: 2rem;
        position: relative;
        z-index: 1;
    }
    .designer-steps::before {
        content: '';
        position: absolute;
        top: 18px;
        left: 0;
        right: 0;
        height: 2px;
        background: rgba(255,255,255,0.25);
        z-index: -1;
    }
    .designer-step-progress {
        position: absolute;
        top: 18px;
        left: 0;
        height: 2px;
        background: #fff;
        transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 0;
    }
    .designer-step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        max-width: 100px;
        position: relative;
        z-index: 1;
    }
    .designer-step-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        border: 2px solid rgba(255,255,255,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
        color: rgba(255,255,255,0.8);
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    .designer-step-item.completed .designer-step-circle {
        background: #10b981;
        border-color: #10b981;
        color: #fff;
    }
    .designer-step-item.completed .designer-step-circle span { display: none !important; }
    .designer-step-item.completed .designer-step-circle::after {
        content: '✓';
        font-size: 0.875rem;
        font-weight: 700;
    }
    .designer-step-item.active .designer-step-circle {
        background: #fff;
        border-color: #fff;
        color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(255,255,255,0.3);
    }
    .designer-step-label {
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: rgba(255,255,255,0.85);
        margin-top: 0.5rem;
        text-align: center;
        line-height: 1.2;
    }
    .designer-step-item.active .designer-step-label { color: #fff; }
    .designer-step-item.completed .designer-step-label { color: rgba(255,255,255,0.9); }
    @media (max-width: 768px) {
        .designer-step-label { font-size: 0.6rem; }
        .designer-step-item { max-width: 50px; }
    }
    @media (max-width: 991px) {
        .designer-tips-sidebar { position: static; max-height: none; }
    }
    /* Form Body */
    .designer-wizard-body {
        padding: 2.5rem 2.5rem 2rem;
    }
    .designer-step-content {
        display: none;
        animation: fadeSlideIn 0.35s ease-out;
    }
    .designer-step-content.active {
        display: block;
    }
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .designer-step-heading {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .designer-step-heading i {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
        color: #4f46e5;
        border-radius: 10px;
        font-size: 1rem;
    }
    .designer-wizard .form-control, .designer-wizard .form-select {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 0.625rem 0.875rem;
        font-size: 0.9375rem;
    }
    .designer-wizard .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }
    .designer-wizard .form-label {
        font-weight: 500;
        font-size: 0.875rem;
        color: #475569;
        margin-bottom: 0.375rem;
    }
    .designer-wizard .form-check-input:checked {
        background-color: #4f46e5;
        border-color: #4f46e5;
    }
    .designer-agreement-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        max-height: 220px;
        overflow-y: auto;
        font-size: 0.875rem;
        line-height: 1.7;
        color: #475569;
    }
    .designer-agreement-box strong { color: #1e293b; }
    .designer-upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #fafbfc;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .designer-upload-zone:hover, .designer-upload-zone.dragover {
        border-color: #4f46e5;
        background: rgba(79, 70, 229, 0.04);
    }
    .designer-upload-zone.has-file {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.04);
    }
    .designer-upload-zone i {
        font-size: 2rem;
        color: #94a3b8;
        margin-bottom: 0.75rem;
        display: block;
    }
    .designer-upload-zone.has-file i { color: #10b981; }
    .designer-nav-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }
    .designer-nav-btn {
        padding: 0.625rem 1.5rem;
        font-weight: 600;
        font-size: 0.9375rem;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .designer-nav-btn.btn-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border: none;
    }
    .designer-nav-btn.btn-primary:hover {
        background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        transform: translateY(-1px);
    }
    .designer-nav-btn.btn-success {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        border: none;
    }
    .designer-nav-btn.btn-success:hover {
        background: linear-gradient(135deg, #047857 0%, #0d9668 100%);
        transform: translateY(-1px);
    }
    .designer-step-hint {
        font-size: 0.8125rem;
        color: #64748b;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="designer-page">
    @if(session('success'))
        <div class="container-fluid">
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                <i class="fas fa-check-circle me-3" style="font-size: 1.25rem;"></i>
                <span class="flex-grow-1">{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif
    @if(session('info'))
        <div class="container-fluid">
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);">
                <i class="fas fa-info-circle me-3" style="font-size: 1.25rem;"></i>
                <span class="flex-grow-1">{{ session('info') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if($isApprovedDesigner ?? false)
    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-body text-center py-5" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                <i class="fas fa-check-circle mb-3" style="font-size: 4rem; opacity: 0.9;"></i>
                <h2 class="mb-2" style="font-weight: 700;">You're Already an Approved Designer</h2>
                <p class="mb-0" style="font-size: 1.1rem; opacity: 0.95;">You can save and publish public templates. Visit <a href="{{ route('design.templates.index') }}" class="text-white fw-bold" style="text-decoration: underline;">My Templates</a> to get started.</p>
            </div>
        </div>
    </div>
    @elseif($pendingApplication ?? false)
    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-body text-center py-5" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white;">
                <i class="fas fa-clock mb-3" style="font-size: 4rem; opacity: 0.9;"></i>
                <h2 class="mb-2" style="font-weight: 700;">Application Pending Review</h2>
                <p class="mb-0" style="font-size: 1.1rem; opacity: 0.95;">Your designer application has been submitted and is waiting for our team to review it. We will get back to you soon. Please do not submit another application.</p>
            </div>
        </div>
    </div>
    @else
    <div class="container-fluid px-0 py-4">
        <!-- Generate Experience Modal -->
        <div class="modal fade" id="generateExperienceModal" tabindex="-1" aria-labelledby="generateExperienceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
                    <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; padding: 1.25rem 1.5rem;">
                        <h5 class="modal-title" id="generateExperienceModalLabel" style="font-weight: 700; color: #1e293b;">
                            <i class="fas fa-magic me-2" style="color: #6366f1;"></i>Generate Design Experience
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding: 1.5rem;">
                        <p class="text-muted mb-3" style="font-size: 0.9rem;">Mention your skills, tools, years of experience, and project types. AI will generate a professional description for you.</p>
                        <div class="mb-3">
                            <label for="skillsInput" class="form-label fw-600">Your Skills & Background</label>
                            <textarea id="skillsInput" class="form-control" rows="4" placeholder="e.g., 5 years experience in graphic design, proficient in Figma, Adobe Illustrator, Canva. Specialize in UI/UX, brochures, social media graphics. Worked on branding projects for small businesses..."></textarea>
                            <small class="text-muted">Include: design tools, years of experience, project types, specializations</small>
                        </div>
                        <div id="generateExperienceError" class="alert alert-danger py-2 px-3 d-none" style="font-size: 0.875rem;"></div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem;">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="generateExperienceBtn" onclick="generateExperienceWithAI()">
                            <i class="fas fa-spinner fa-spin d-none" id="generateExperienceSpinner"></i>
                            <i class="fas fa-magic" id="generateExperienceIcon"></i>
                            <span class="ms-2">Generate</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row gx-4 gy-4">
            <div class="col-lg-8 col-xl-9">
                <div class="card designer-wizard border-0">
                <div class="designer-wizard-header">
                    <h1 class="designer-wizard-title"><i class="fas fa-palette me-2"></i>Designer Partnership Application</h1>
                    <p class="designer-wizard-subtitle mb-0">Join our designer community and showcase your templates to thousands of users</p>
                    <div class="designer-steps" id="stepIndicator">
                        <div class="designer-step-progress" id="stepProgress"></div>
                        @php $steps = ['Experience', 'Certifications', 'Agreement', 'Identity', 'Address', 'Account']; @endphp
                        @foreach($steps as $i => $label)
                            <div class="designer-step-item {{ $i === 0 ? 'active' : '' }}" data-step="{{ $i + 1 }}">
                                <div class="designer-step-circle"><span>{{ $i + 1 }}</span></div>
                                <span class="designer-step-label">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <form action="{{ route('designer-application.store') }}" method="POST" enctype="multipart/form-data" id="designerForm">
                    @csrf

                    <div class="designer-wizard-body">
                        <!-- Step 1: Experience & Basic Info -->
                        <div class="designer-step-content active" data-step="1">
                            <h2 class="designer-step-heading"><i class="fas fa-user-tie"></i>Your Experience & Contact</h2>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()?->name ?? '') }}" required>
                                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()?->email ?? '') }}" required>
                                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1 234 567 8900">
                                    @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label mb-0">Design Experience <span class="text-danger">*</span></label>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openGenerateExperienceModal()" title="Generate with AI">
                                            <i class="fas fa-magic me-1"></i>Generate
                                        </button>
                                    </div>
                                    <textarea name="experience" id="experience" class="form-control" rows="4" required placeholder="Tell us about your design experience, years in the field, types of projects, tools you use (Figma, Canva, Adobe, etc.)...">{{ old('experience') }}</textarea>
                                    @error('experience')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Certifications -->
                        <div class="designer-step-content" data-step="2">
                            <h2 class="designer-step-heading"><i class="fas fa-award"></i>Certifications & Qualifications</h2>
                            <div class="mb-3">
                                <label class="form-label">Certifications & Qualifications</label>
                                <textarea name="certifications" class="form-control" rows="4" placeholder="List any design certifications, courses, awards, or qualifications (e.g., Adobe Certified, UX Design Certificate, etc.)">{{ old('certifications') }}</textarea>
                                @error('certifications')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <!-- Step 3: Partnership Agreement -->
                        <div class="designer-step-content" data-step="3">
                            <h2 class="designer-step-heading"><i class="fas fa-file-contract"></i>Partnership Agreement</h2>
                            <div class="designer-agreement-box mb-4">
                                <strong>Designer Partnership Terms</strong><br><br>
                                By submitting this application, you agree to the following terms:<br>
                                • You will create original, high-quality templates for our platform.<br>
                                • You retain ownership of your designs but grant us a license to display and sell them.<br>
                                • You will receive a share of revenue from sales of your templates as per our partnership agreement.<br>
                                • You agree to comply with our content guidelines and intellectual property policies.<br>
                                • We reserve the right to review and approve all submissions before publication.<br>
                                • Either party may terminate the partnership with 30 days written notice.<br><br>
                                <em>Full terms will be provided upon approval of your application.</em>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="agreement_accepted" id="agreement_accepted" value="1" class="form-check-input" {{ old('agreement_accepted') ? 'checked' : '' }} required>
                                <label class="form-check-label" for="agreement_accepted">I have read and agree to the partnership terms <span class="text-danger">*</span></label>
                                @error('agreement_accepted')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <!-- Step 4: Identity Verification -->
                        <div class="designer-step-content" data-step="4">
                            <h2 class="designer-step-heading"><i class="fas fa-id-card"></i>Identity Verification</h2>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Identity Card / Passport Number</label>
                                    <input type="text" name="identity_card_number" class="form-control" value="{{ old('identity_card_number') }}" placeholder="e.g., A12345678">
                                    @error('identity_card_number')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Upload Identity Card Copy</label>
                                    <div class="designer-upload-zone" id="uploadZone" onclick="document.getElementById('identity_card').click()">
                                        <input type="file" name="identity_card" id="identity_card" accept=".jpg,.jpeg,.png,.pdf" class="d-none">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                        <p class="mb-0 text-muted">Click or drag file here (JPG, PNG, PDF - max 5MB)</p>
                                        <p class="mb-0 mt-1 small text-success" id="fileName" style="display:none;"></p>
                                    </div>
                                    @error('identity_card')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Step 5: Address -->
                        <div class="designer-step-content" data-step="5">
                            <h2 class="designer-step-heading"><i class="fas fa-map-marker-alt"></i>Address</h2>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Street Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="123 Main St, Apt 4">
                                    @error('address')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                                    @error('city')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">State / Province</label>
                                    <input type="text" name="state" class="form-control" value="{{ old('state') }}">
                                    @error('state')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code') }}">
                                    @error('postal_code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Country</label>
                                    <input type="text" name="country" class="form-control" value="{{ old('country') }}" placeholder="e.g., United States">
                                    @error('country')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Step 6: Account Details -->
                        <div class="designer-step-content" data-step="6">
                            <h2 class="designer-step-heading"><i class="fas fa-university"></i>Payment Account Details</h2>
                            <p class="designer-step-hint">For receiving payments from template sales. Your information is stored securely and encrypted.</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                                    @error('bank_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Holder Name</label>
                                    <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name') }}">
                                    @error('account_holder_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}">
                                    @error('account_number')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Routing Number (US) / Sort Code</label>
                                    <input type="text" name="routing_number" class="form-control" value="{{ old('routing_number') }}">
                                    @error('routing_number')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">SWIFT / BIC Code (International)</label>
                                    <input type="text" name="swift_code" class="form-control" value="{{ old('swift_code') }}" placeholder="e.g., CHASUS33">
                                    @error('swift_code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="designer-nav-footer">
                            <button type="button" class="btn btn-outline-secondary designer-nav-btn" id="prevBtn" style="display: none;">
                                <i class="fas fa-arrow-left me-2"></i>Previous
                            </button>
                            <div class="d-flex gap-2 ms-auto">
                                <button type="button" class="btn btn-primary designer-nav-btn" id="nextBtn">
                                    Next Step<i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                <button type="submit" class="btn btn-success designer-nav-btn" id="submitBtn" style="display: none;">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                </div>
            </div>
            <div class="col-lg-4 col-xl-3">
                <aside class="designer-tips-sidebar">
                    <h3 class="designer-tips-title"><i class="fas fa-lightbulb"></i>Tips for a Good Designer</h3>
                    <div class="designer-tip-item">
                        <strong><i class="fas fa-check-circle"></i>Quality Over Quantity</strong>
                        Focus on creating a few high-quality templates rather than many mediocre ones. Users value polished, professional designs.
                    </div>
                    <div class="designer-tip-item">
                        <strong><i class="fas fa-check-circle"></i>Know Your Audience</strong>
                        Research what industries and use cases are popular. Business, education, and creative portfolios tend to perform well.
                    </div>
                    <div class="designer-tip-item">
                        <strong><i class="fas fa-check-circle"></i>Consistent Branding</strong>
                        Use cohesive color schemes, typography, and spacing. Templates that feel unified across pages sell better.
                    </div>
                    <div class="designer-tip-item">
                        <strong><i class="fas fa-check-circle"></i>Mobile-First Thinking</strong>
                        Ensure your designs look great on all screen sizes. Responsive templates are essential for modern users.
                    </div>
                    <div class="designer-tip-item">
                        <strong><i class="fas fa-check-circle"></i>Clear Hierarchy</strong>
                        Use headings, subheadings, and visual weight to guide the eye. Good information architecture improves usability.
                    </div>
                    <div class="designer-tip-item">
                        <strong><i class="fas fa-check-circle"></i>Editable Variables</strong>
                        Design with placeholders for names, dates, and custom text. Flexible templates are more valuable to buyers.
                    </div>
                    <div class="designer-tip-item">
                        <strong><i class="fas fa-check-circle"></i>Stay Original</strong>
                        Create unique designs. Avoid copying existing templates—originality builds your reputation and avoids legal issues.
                    </div>
                    <div class="designer-tip-item">
                        <strong><i class="fas fa-check-circle"></i>Test Before Submitting</strong>
                        Preview your templates in the editor. Ensure all elements align correctly and fonts load properly.
                    </div>
                </aside>
            </div>
        </div>
    </div>
    @endif
</div>

@if(!($isApprovedDesigner ?? false) && !($pendingApplication ?? false))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 6;
    const stepContents = document.querySelectorAll('.designer-step-content');
    const stepItems = document.querySelectorAll('.designer-step-item');
    const stepProgress = document.getElementById('stepProgress');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    function showStep(step) {
        currentStep = step;
        stepContents.forEach((el) => {
            el.classList.toggle('active', parseInt(el.dataset.step) === step);
        });
        stepItems.forEach((el) => {
            const stepNum = parseInt(el.dataset.step);
            el.classList.remove('active', 'completed');
            if (stepNum < step) el.classList.add('completed');
            if (stepNum === step) el.classList.add('active');
        });
        stepProgress.style.width = ((step - 1) / (totalSteps - 1) * 100) + '%';
        prevBtn.style.display = step > 1 ? 'inline-flex' : 'none';
        nextBtn.style.display = step < totalSteps ? 'inline-flex' : 'none';
        submitBtn.style.display = step === totalSteps ? 'inline-flex' : 'none';
    }

    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) showStep(currentStep - 1);
    });

    nextBtn.addEventListener('click', () => {
        const activeContent = document.querySelector('.designer-step-content.active');
        const requiredInputs = activeContent.querySelectorAll('[required]');
        let valid = true;
        requiredInputs.forEach(inp => {
            if (!inp.value || (inp.type === 'checkbox' && !inp.checked)) valid = false;
        });
        if (valid && currentStep < totalSteps) {
            showStep(currentStep + 1);
        } else if (!valid) {
            const firstInvalid = activeContent.querySelector('[required]');
            if (firstInvalid) firstInvalid.focus();
            alert('Please complete all required fields before continuing.');
        }
    });

    // File upload zone
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('identity_card');
    const fileName = document.getElementById('fileName');

    fileInput.addEventListener('change', function() {
        if (this.files.length) {
            uploadZone.classList.add('has-file');
            fileName.textContent = this.files[0].name;
            fileName.style.display = 'block';
        } else {
            uploadZone.classList.remove('has-file');
            fileName.style.display = 'none';
        }
    });

    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });

    // Initialize progress bar
    showStep(1);
});

function openGenerateExperienceModal() {
    const modal = new bootstrap.Modal(document.getElementById('generateExperienceModal'));
    document.getElementById('skillsInput').value = '';
    document.getElementById('generateExperienceError').classList.add('d-none');
    modal.show();
}

async function generateExperienceWithAI() {
    const skillsInput = document.getElementById('skillsInput');
    const skills = skillsInput.value.trim();
    const errorEl = document.getElementById('generateExperienceError');
    const btn = document.getElementById('generateExperienceBtn');
    const spinner = document.getElementById('generateExperienceSpinner');
    const icon = document.getElementById('generateExperienceIcon');

    if (!skills) {
        errorEl.textContent = 'Please mention your skills (e.g., tools, years of experience, project types).';
        errorEl.classList.remove('d-none');
        return;
    }

    errorEl.classList.add('d-none');
    btn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');

    try {
        const response = await fetch('{{ route("designer-application.generateExperience") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ skills: skills })
        });
        const data = await response.json();

        if (data.success && data.description) {
            document.getElementById('experience').value = data.description;
            bootstrap.Modal.getInstance(document.getElementById('generateExperienceModal')).hide();
        } else {
            errorEl.textContent = data.message || 'Failed to generate description. Please try again.';
            errorEl.classList.remove('d-none');
        }
    } catch (err) {
        errorEl.textContent = 'An error occurred. Please try again.';
        errorEl.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
    }
}
</script>
@endpush
@endif
@endsection
