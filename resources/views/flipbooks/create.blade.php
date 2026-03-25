@extends('layouts.app')

@section('title', 'Create Flip Book')
@section('page-title', 'Create Flip Book')

@push('styles')
<style>
    .create-options-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }
    
    .create-options-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        margin-top: 2rem;
    }
    
    @media (max-width: 768px) {
        .create-options-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
    }
    
    .create-option-card {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 250px;
    }
    
    .create-option-card:hover {
        border-color: #6366f1;
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(99, 102, 241, 0.15);
    }
    
    .create-option-card.disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background: #f8fafc;
    }
    
    .create-option-card.disabled:hover {
        transform: none;
        border-color: #e2e8f0;
        box-shadow: none;
    }
    
    .create-option-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
        color: white;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    
    .create-option-card.disabled .create-option-icon {
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
        box-shadow: none;
    }
    
    .create-option-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.75rem;
    }
    
    .create-option-description {
        font-size: 0.875rem;
        color: #64748b;
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    
    .create-option-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: #f1f5f9;
        color: #64748b;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        margin-top: auto;
    }
    
    .create-option-card.disabled .create-option-badge {
        background: #e2e8f0;
        color: #94a3b8;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .page-header h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    
    .page-header p {
        font-size: 1rem;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<div class="create-options-container">
    <div class="page-header">
        <h2>Create Your Flip Book</h2>
        <p>Choose how you'd like to create your flip book</p>
    </div>
    
    <div class="create-options-grid">
        <!-- Option 1: Design Flip Book -->
        <div class="create-option-card" onclick="window.location.href='{{ route('design.create', ['multi' => 'true', 'from_flipbook' => 'true']) }}'">
            <div class="create-option-icon">
                <i class="fas fa-paint-brush"></i>
            </div>
            <h3 class="create-option-title">Design Flip Book</h3>
            <p class="create-option-description">
                Create your flip book from scratch using our powerful design editor. Add text, images, shapes, and customize every page.
            </p>
            <span class="create-option-badge">Recommended</span>
        </div>
        
        <!-- Option 2: Convert Images to Flip Book -->
        <div class="create-option-card" onclick="window.location.href='{{ route('flipbooks.wizard') }}'">
            <div class="create-option-icon">
                <i class="fas fa-images"></i>
            </div>
            <h3 class="create-option-title">I Have Design Images</h3>
            <p class="create-option-description">
                Upload your existing design images and convert them into a flip book. Perfect if you already have your pages ready.
            </p>
            <span class="create-option-badge">Quick Setup</span>
        </div>
        
        <!-- Option 3: Coming Soon -->
        <div class="create-option-card disabled">
            <div class="create-option-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h3 class="create-option-title">Option 3</h3>
            <p class="create-option-description">
                This option will be available soon. Stay tuned for updates!
            </p>
            <span class="create-option-badge">Coming Soon</span>
        </div>
        
        <!-- Option 4: Coming Soon -->
        <div class="create-option-card disabled">
            <div class="create-option-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h3 class="create-option-title">Option 4</h3>
            <p class="create-option-description">
                This option will be available soon. Stay tuned for updates!
            </p>
            <span class="create-option-badge">Coming Soon</span>
        </div>
    </div>
</div>
@endsection

