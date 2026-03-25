@extends('layouts.app')

@section('title', 'Design Flip Book - ' . $flipBook->title)
@section('page-title', 'Design Flip Book')

@push('styles')
<style>
    .design-preview {
        background: #f8fafc;
        border-radius: 8px;
        padding: 2rem;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px dashed #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .preview-page {
        background: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .color-picker-wrapper {
        position: relative;
    }
    
    .color-preview {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        border: 2px solid #e2e8f0;
        cursor: pointer;
        display: inline-block;
    }
    
    .design-section {
        margin-bottom: 2rem;
    }
    
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--dark-text);
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--border-color);
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .preview-controls {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        margin-top: 1rem;
    }
    
    .preview-controls button {
        padding: 0.5rem 1rem;
        border: 1px solid var(--border-color);
        background: white;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .preview-controls button:hover {
        background: var(--light-bg);
        border-color: var(--primary-color);
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <a href="{{ route('flipbooks.show', $flipBook->id) }}" class="text-decoration-none">
            <i class="fas fa-arrow-left me-2"></i>Back to Flip Book
        </a>
    </div>
</div>

<div class="row">
    <!-- Design Options -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-4">
                    <i class="fas fa-palette me-2"></i>Design Settings
                </h4>
                
                <form id="designForm" method="POST" action="{{ route('flipbooks.design.update', $flipBook->id) }}">
                    @csrf
                    
                    <!-- Colors Section -->
                    <div class="design-section">
                        <div class="section-title">
                            <i class="fas fa-fill-drip me-2"></i>Colors
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Background Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" 
                                       name="background_color" 
                                       id="background_color" 
                                       value="{{ $flipBook->settings['background_color'] ?? '#ffffff' }}"
                                       class="form-control form-control-color"
                                       style="width: 60px; height: 40px; cursor: pointer;">
                                <input type="text" 
                                       class="form-control" 
                                       value="{{ $flipBook->settings['background_color'] ?? '#ffffff' }}"
                                       id="background_color_text"
                                       placeholder="#ffffff"
                                       maxlength="7">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Text Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" 
                                       name="text_color" 
                                       id="text_color" 
                                       value="{{ $flipBook->settings['text_color'] ?? '#1e293b' }}"
                                       class="form-control form-control-color"
                                       style="width: 60px; height: 40px; cursor: pointer;">
                                <input type="text" 
                                       class="form-control" 
                                       value="{{ $flipBook->settings['text_color'] ?? '#1e293b' }}"
                                       id="text_color_text"
                                       placeholder="#1e293b"
                                       maxlength="7">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Primary Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" 
                                       name="primary_color" 
                                       id="primary_color" 
                                       value="{{ $flipBook->settings['primary_color'] ?? '#6366f1' }}"
                                       class="form-control form-control-color"
                                       style="width: 60px; height: 40px; cursor: pointer;">
                                <input type="text" 
                                       class="form-control" 
                                       value="{{ $flipBook->settings['primary_color'] ?? '#6366f1' }}"
                                       id="primary_color_text"
                                       placeholder="#6366f1"
                                       maxlength="7">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Typography Section -->
                    <div class="design-section">
                        <div class="section-title">
                            <i class="fas fa-font me-2"></i>Typography
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Font Family</label>
                            <select name="font_family" id="font_family" class="form-select">
                                <option value="Inter" {{ ($flipBook->settings['font_family'] ?? 'Inter') === 'Inter' ? 'selected' : '' }}>Inter</option>
                                <option value="Roboto" {{ ($flipBook->settings['font_family'] ?? '') === 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                <option value="Open Sans" {{ ($flipBook->settings['font_family'] ?? '') === 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                <option value="Lato" {{ ($flipBook->settings['font_family'] ?? '') === 'Lato' ? 'selected' : '' }}>Lato</option>
                                <option value="Montserrat" {{ ($flipBook->settings['font_family'] ?? '') === 'Montserrat' ? 'selected' : '' }}>Montserrat</option>
                                <option value="Poppins" {{ ($flipBook->settings['font_family'] ?? '') === 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                <option value="Playfair Display" {{ ($flipBook->settings['font_family'] ?? '') === 'Playfair Display' ? 'selected' : '' }}>Playfair Display</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Font Size (px)</label>
                            <input type="number" 
                                   name="font_size" 
                                   id="font_size" 
                                   class="form-control" 
                                   value="{{ $flipBook->settings['font_size'] ?? 16 }}"
                                   min="10" 
                                   max="24"
                                   step="1">
                        </div>
                    </div>
                    
                    <!-- Dimensions Section -->
                    <div class="design-section">
                        <div class="section-title">
                            <i class="fas fa-expand-arrows-alt me-2"></i>Dimensions
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Page Width (px)</label>
                            <input type="number" 
                                   name="page_width" 
                                   id="page_width" 
                                   class="form-control" 
                                   value="{{ $flipBook->settings['page_width'] ?? 800 }}"
                                   min="400" 
                                   max="2000"
                                   step="50">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Page Height (px)</label>
                            <input type="number" 
                                   name="page_height" 
                                   id="page_height" 
                                   class="form-control" 
                                   value="{{ $flipBook->settings['page_height'] ?? 1000 }}"
                                   min="400" 
                                   max="2000"
                                   step="50">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Border Radius (px)</label>
                            <input type="number" 
                                   name="border_radius" 
                                   id="border_radius" 
                                   class="form-control" 
                                   value="{{ $flipBook->settings['border_radius'] ?? 8 }}"
                                   min="0" 
                                   max="50"
                                   step="1">
                        </div>
                    </div>
                    
                    <!-- Effects Section -->
                    <div class="design-section">
                        <div class="section-title">
                            <i class="fas fa-magic me-2"></i>Effects
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Shadow Effect</label>
                            <select name="shadow_effect" id="shadow_effect" class="form-select">
                                <option value="none" {{ ($flipBook->settings['shadow_effect'] ?? 'medium') === 'none' ? 'selected' : '' }}>None</option>
                                <option value="small" {{ ($flipBook->settings['shadow_effect'] ?? '') === 'small' ? 'selected' : '' }}>Small</option>
                                <option value="medium" {{ ($flipBook->settings['shadow_effect'] ?? 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="large" {{ ($flipBook->settings['shadow_effect'] ?? '') === 'large' ? 'selected' : '' }}>Large</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Transition Effect</label>
                            <select name="transition_effect" id="transition_effect" class="form-select">
                                <option value="slide" {{ ($flipBook->settings['transition_effect'] ?? 'slide') === 'slide' ? 'selected' : '' }}>Slide</option>
                                <option value="flip" {{ ($flipBook->settings['transition_effect'] ?? '') === 'flip' ? 'selected' : '' }}>Flip</option>
                                <option value="fade" {{ ($flipBook->settings['transition_effect'] ?? '') === 'fade' ? 'selected' : '' }}>Fade</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Animation Speed</label>
                            <select name="animation_speed" id="animation_speed" class="form-select">
                                <option value="slow" {{ ($flipBook->settings['animation_speed'] ?? 'normal') === 'slow' ? 'selected' : '' }}>Slow</option>
                                <option value="normal" {{ ($flipBook->settings['animation_speed'] ?? 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="fast" {{ ($flipBook->settings['animation_speed'] ?? '') === 'fast' ? 'selected' : '' }}>Fast</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Design Settings
                        </button>
                        <a href="{{ route('flipbooks.preview', $flipBook->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-2"></i>Preview Changes
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Live Preview -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-4">
                    <i class="fas fa-eye me-2"></i>Live Preview
                </h4>
                
                <div class="design-preview" id="previewContainer">
                    <div class="preview-page" id="previewPage">
                        <h3 style="margin-bottom: 1rem;">{{ $flipBook->title }}</h3>
                        <p style="color: #64748b;">This is a preview of your flipbook design</p>
                        <div class="preview-controls">
                            <button><i class="fas fa-chevron-left"></i></button>
                            <button>Page 1 of {{ count($flipBook->pages ?? []) }}</button>
                            <button><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Sync color picker with text input
    function syncColorInputs(colorInput, textInput) {
        colorInput.addEventListener('input', function() {
            textInput.value = this.value;
            updatePreview();
        });
        
        textInput.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                colorInput.value = this.value;
                updatePreview();
            }
        });
    }
    
    syncColorInputs(document.getElementById('background_color'), document.getElementById('background_color_text'));
    syncColorInputs(document.getElementById('text_color'), document.getElementById('text_color_text'));
    syncColorInputs(document.getElementById('primary_color'), document.getElementById('primary_color_text'));
    
    // Update preview in real-time
    function updatePreview() {
        const previewContainer = document.getElementById('previewContainer');
        const previewPage = document.getElementById('previewPage');
        
        // Get current values
        const bgColor = document.getElementById('background_color').value;
        const textColor = document.getElementById('text_color').value;
        const primaryColor = document.getElementById('primary_color').value;
        const fontFamily = document.getElementById('font_family').value;
        const fontSize = document.getElementById('font_size').value + 'px';
        const pageWidth = document.getElementById('page_width').value + 'px';
        const pageHeight = document.getElementById('page_height').value + 'px';
        const borderRadius = document.getElementById('border_radius').value + 'px';
        const shadowEffect = document.getElementById('shadow_effect').value;
        
        // Apply styles
        previewContainer.style.backgroundColor = bgColor;
        previewPage.style.color = textColor;
        previewPage.style.fontFamily = fontFamily;
        previewPage.style.fontSize = fontSize;
        previewPage.style.width = pageWidth;
        previewPage.style.minHeight = pageHeight;
        previewPage.style.borderRadius = borderRadius;
        
        // Apply shadow
        const shadows = {
            'none': 'none',
            'small': '0 2px 4px rgba(0,0,0,0.1)',
            'medium': '0 4px 6px rgba(0,0,0,0.1)',
            'large': '0 10px 20px rgba(0,0,0,0.15)'
        };
        previewPage.style.boxShadow = shadows[shadowEffect];
        
        // Update button colors
        const buttons = previewPage.querySelectorAll('.preview-controls button');
        buttons.forEach(btn => {
            btn.style.borderColor = primaryColor;
            btn.addEventListener('mouseenter', function() {
                this.style.backgroundColor = primaryColor;
                this.style.color = 'white';
            });
            btn.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'white';
                this.style.color = 'inherit';
            });
        });
    }
    
    // Add event listeners to all form inputs
    const formInputs = document.querySelectorAll('#designForm input, #designForm select');
    formInputs.forEach(input => {
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
    });
    
    // Initialize preview
    updatePreview();
    
    // Form submission with AJAX
    document.getElementById('designForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.content-wrapper').insertBefore(alert, document.querySelector('.content-wrapper').firstChild);
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving. Please try again.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
</script>
@endpush









