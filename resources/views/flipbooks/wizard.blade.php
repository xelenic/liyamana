@extends('layouts.app')

@section('title', 'Create Flip Book')
@section('page-title', 'Create Flip Book')

@push('styles')
<style>
    .wizard-container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .wizard-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
    }
    
    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: #e2e8f0;
        z-index: 0;
    }
    
    .wizard-step {
        flex: 1;
        text-align: center;
        position: relative;
        z-index: 1;
    }
    
    .wizard-step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        font-weight: 600;
        color: #64748b;
        transition: all 0.3s;
    }
    
    .wizard-step.active .wizard-step-circle {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-color: var(--primary-color);
        color: white;
    }
    
    .wizard-step.completed .wizard-step-circle {
        background: var(--success-color);
        border-color: var(--success-color);
        color: white;
    }
    
    .wizard-step.completed .wizard-step-circle::after {
        content: '✓';
    }
    
    .wizard-step-label {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
    }
    
    .wizard-step.active .wizard-step-label {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    .wizard-content {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        min-height: 400px;
    }
    
    .wizard-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }
    
    .file-upload-area {
        border: 2px dashed var(--border-color);
        border-radius: 8px;
        padding: 3rem;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        background: var(--light-bg);
    }
    
    .file-upload-area:hover {
        border-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.05);
    }
    
    .file-upload-area.dragover {
        border-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.1);
    }
    
    .uploaded-pages {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }
    
    .page-preview {
        position: relative;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .page-preview img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    
    .page-preview .remove-page {
        position: absolute;
        top: 5px;
        right: 5px;
        background: var(--danger-color);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.75rem;
    }
    
    .page-number {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 0.25rem;
        text-align: center;
        font-size: 0.75rem;
    }
    
    .step-content {
        display: none;
    }
    
    .step-content.active {
        display: block;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .preview-cover {
        max-width: 300px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .preview-cover img {
        width: 100%;
        height: auto;
    }
</style>
@endpush

@section('content')
<div class="wizard-container">
    <div class="wizard-steps">
        <div class="wizard-step active" data-step="1">
            <div class="wizard-step-circle">1</div>
            <div class="wizard-step-label">Basic Info</div>
        </div>
        <div class="wizard-step" data-step="2">
            <div class="wizard-step-circle">2</div>
            <div class="wizard-step-label">Upload Pages</div>
        </div>
        <div class="wizard-step" data-step="3">
            <div class="wizard-step-circle">3</div>
            <div class="wizard-step-label">Settings</div>
        </div>
        <div class="wizard-step" data-step="4">
            <div class="wizard-step-circle">4</div>
            <div class="wizard-step-label">Cover Image</div>
        </div>
        <div class="wizard-step" data-step="5">
            <div class="wizard-step-circle">5</div>
            <div class="wizard-step-label">Review</div>
        </div>
    </div>
    
    <div class="wizard-content">
        <!-- Step 1: Basic Information -->
        <div class="step-content active" id="step1">
            <h3 class="mb-4">Basic Information</h3>
            <form id="step1Form">
                <div class="form-group">
                    <label for="title" class="form-label">Flip Book Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required placeholder="Enter your flip book title">
                </div>
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe your flip book (optional)"></textarea>
                </div>
            </form>
        </div>
        
        <!-- Step 2: Upload Pages -->
        <div class="step-content" id="step2">
            <h3 class="mb-4">Upload Pages</h3>
            <div class="file-upload-area" id="uploadArea">
                <i class="fas fa-cloud-upload-alt text-primary mb-3" style="font-size: 3rem;"></i>
                <h5>Drag & Drop Images Here</h5>
                <p class="text-muted mb-3">or click to browse</p>
                <input type="file" id="pageFiles" name="pages[]" multiple accept="image/*" style="display: none;">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('pageFiles').click()">
                    <i class="fas fa-folder-open me-2"></i>Select Images
                </button>
                <p class="text-muted small mt-2 mb-0">Supported formats: JPEG, PNG, JPG, GIF (Max 10MB per image)</p>
            </div>
            <div class="uploaded-pages" id="uploadedPages"></div>
        </div>
        
        <!-- Step 3: Settings -->
        <div class="step-content" id="step3">
            <h3 class="mb-4">Settings</h3>
            <form id="step3Form">
                <div class="form-group">
                    <label for="transition_effect" class="form-label">Transition Effect <span class="text-danger">*</span></label>
                    <select class="form-select" id="transition_effect" name="transition_effect" required>
                        <option value="slide">Slide</option>
                        <option value="flip">Flip</option>
                        <option value="fade">Fade</option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="auto_play" name="auto_play">
                        <label class="form-check-label" for="auto_play">Enable Auto Play</label>
                    </div>
                </div>
                <div class="form-group" id="autoPlayIntervalGroup" style="display: none;">
                    <label for="auto_play_interval" class="form-label">Auto Play Interval (seconds)</label>
                    <input type="number" class="form-control" id="auto_play_interval" name="auto_play_interval" min="1" max="60" value="5">
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="show_controls" name="show_controls" checked>
                        <label class="form-check-label" for="show_controls">Show Navigation Controls</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="show_thumbnails" name="show_thumbnails" checked>
                        <label class="form-check-label" for="show_thumbnails">Show Thumbnail Navigation</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="background_color" class="form-label">Background Color</label>
                    <input type="color" class="form-control form-control-color" id="background_color" name="background_color" value="#ffffff">
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_public" name="is_public">
                        <label class="form-check-label" for="is_public">Make this flip book public</label>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Step 4: Cover Image -->
        <div class="step-content" id="step4">
            <h3 class="mb-4">Cover Image (Optional)</h3>
            <div class="file-upload-area" id="coverUploadArea" style="padding: 2rem;">
                <i class="fas fa-image text-primary mb-3" style="font-size: 2.5rem;"></i>
                <h5>Upload Cover Image</h5>
                <p class="text-muted mb-3">This will be displayed as the thumbnail</p>
                <input type="file" id="coverFile" name="cover_image" accept="image/*" style="display: none;">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('coverFile').click()">
                    <i class="fas fa-upload me-2"></i>Select Cover Image
                </button>
            </div>
            <div class="preview-cover mt-3" id="coverPreview" style="display: none;"></div>
        </div>
        
        <!-- Step 5: Review -->
        <div class="step-content" id="step5">
            <h3 class="mb-4">Review & Create</h3>
            <div id="reviewContent">
                <div class="mb-3">
                    <strong>Title:</strong> <span id="reviewTitle"></span>
                </div>
                <div class="mb-3">
                    <strong>Description:</strong> <span id="reviewDescription"></span>
                </div>
                <div class="mb-3">
                    <strong>Pages:</strong> <span id="reviewPages"></span>
                </div>
                <div class="mb-3">
                    <strong>Settings:</strong>
                    <ul class="list-unstyled ms-3">
                        <li>Transition: <span id="reviewTransition"></span></li>
                        <li>Auto Play: <span id="reviewAutoPlay"></span></li>
                        <li>Show Controls: <span id="reviewControls"></span></li>
                        <li>Show Thumbnails: <span id="reviewThumbnails"></span></li>
                        <li>Public: <span id="reviewPublic"></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="wizard-actions">
        <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="display: none;">
            <i class="fas fa-arrow-left me-2"></i>Previous
        </button>
        <div class="ms-auto">
            <button type="button" class="btn btn-outline-danger me-2" id="cancelBtn">
                <i class="fas fa-times me-2"></i>Cancel
            </button>
            <button type="button" class="btn btn-primary" id="nextBtn">
                Next<i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentStep = 1;
const totalSteps = 5;
let uploadedPages = [];
let coverImage = null;

// Helper function to handle fetch responses
async function handleFetchResponse(response) {
    const contentType = response.headers.get('content-type');
    
    // Check if response is JSON
    if (contentType && contentType.includes('application/json')) {
        const data = await response.json();
        
        if (!response.ok) {
            // Create error object with full data
            const error = new Error(data.message || 'Request failed');
            error.data = data;
            error.status = response.status;
            throw error;
        }
        
        return data;
    } else {
        // If not JSON, handle as error
        if (!response.ok) {
            const text = await response.text();
            const error = new Error(`Server error: ${response.status} ${response.statusText}`);
            error.status = response.status;
            throw error;
        } else {
            const text = await response.text();
            const error = new Error('Expected JSON response but received: ' + text.substring(0, 100));
            throw error;
        }
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // File upload handlers
    document.getElementById('pageFiles').addEventListener('change', handlePageUpload);
    document.getElementById('coverFile').addEventListener('change', handleCoverUpload);
    
    // Auto play toggle
    document.getElementById('auto_play').addEventListener('change', function() {
        document.getElementById('autoPlayIntervalGroup').style.display = this.checked ? 'block' : 'none';
    });
    
    // Drag and drop
    const uploadArea = document.getElementById('uploadArea');
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
        if (files.length > 0) {
            uploadPages(files);
        }
    });
    
    uploadArea.addEventListener('click', () => {
        document.getElementById('pageFiles').click();
    });
    
    // Navigation buttons
    document.getElementById('nextBtn').addEventListener('click', nextStep);
    document.getElementById('prevBtn').addEventListener('click', prevStep);
    document.getElementById('cancelBtn').addEventListener('click', cancelWizard);
});

function handlePageUpload(e) {
    const files = Array.from(e.target.files);
    if (files.length > 0) {
        uploadPages(files);
    }
}

function uploadPages(files) {
    const formData = new FormData();
    files.forEach(file => {
        formData.append('pages[]', file);
    });
    
    // Show loading
    const btn = document.getElementById('nextBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
    
    fetch('{{ route("flipbooks.wizard.step2") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(handleFetchResponse)
    .then(data => {
        if (data.success) {
            uploadedPages = uploadedPages.concat(data.pages);
            displayUploadedPages();
            showAlert('success', data.message);
        } else {
            let errorMsg = data.message || 'Upload failed';
            if (data.errors) {
                const errorList = Object.values(data.errors).flat().join(', ');
                errorMsg += ': ' + errorList;
            }
            showAlert('danger', errorMsg);
        }
    })
    .catch(error => {
        showAlert('danger', error.message || 'An error occurred during upload');
        console.error(error);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function displayUploadedPages() {
    const container = document.getElementById('uploadedPages');
    container.innerHTML = '';
    
    uploadedPages.forEach((page, index) => {
        const div = document.createElement('div');
        div.className = 'page-preview';
        div.innerHTML = `
            <img src="/storage/${page.path}" alt="Page ${index + 1}">
            <button type="button" class="remove-page" onclick="removePage(${index})">
                <i class="fas fa-times"></i>
            </button>
            <div class="page-number">Page ${index + 1}</div>
        `;
        container.appendChild(div);
    });
}

function removePage(index) {
    uploadedPages.splice(index, 1);
    displayUploadedPages();
}

function handleCoverUpload(e) {
    const file = e.target.files[0];
    if (file) {
        const formData = new FormData();
        formData.append('cover_image', file);
        
        fetch('{{ route("flipbooks.wizard.step4") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(handleFetchResponse)
        .then(data => {
            if (data.success) {
                coverImage = file;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('coverPreview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Cover">`;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
                showAlert('success', 'Cover image uploaded');
            } else {
                let errorMsg = data.message || 'Upload failed';
                if (data.errors) {
                    const errorList = Object.values(data.errors).flat().join(', ');
                    errorMsg += ': ' + errorList;
                }
                showAlert('danger', errorMsg);
            }
        })
        .catch(error => {
            showAlert('danger', error.message || 'Failed to upload cover image');
            console.error(error);
        });
    }
}

function nextStep() {
    if (currentStep < totalSteps) {
        // Validate current step
        if (validateStep(currentStep)) {
            saveStep(currentStep).then(() => {
                currentStep++;
                updateWizard();
            });
        }
    } else {
        // Complete wizard
        completeWizard();
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateWizard();
    }
}

function updateWizard() {
    // Update step indicators
    document.querySelectorAll('.wizard-step').forEach((step, index) => {
        const stepNum = index + 1;
        step.classList.remove('active', 'completed');
        if (stepNum < currentStep) {
            step.classList.add('completed');
        } else if (stepNum === currentStep) {
            step.classList.add('active');
        }
    });
    
    // Update content
    document.querySelectorAll('.step-content').forEach((content, index) => {
        content.classList.toggle('active', index + 1 === currentStep);
    });
    
    // Update buttons
    document.getElementById('prevBtn').style.display = currentStep > 1 ? 'block' : 'none';
    document.getElementById('nextBtn').innerHTML = currentStep === totalSteps 
        ? '<i class="fas fa-check me-2"></i>Create Flip Book'
        : 'Next<i class="fas fa-arrow-right ms-2"></i>';
    
    // Load review data if on step 5
    if (currentStep === 5) {
        loadReviewData();
    }
}

function validateStep(step) {
    switch(step) {
        case 1:
            const title = document.getElementById('title').value.trim();
            if (!title) {
                showAlert('danger', 'Please enter a title');
                return false;
            }
            return true;
        case 2:
            if (uploadedPages.length === 0) {
                showAlert('danger', 'Please upload at least one page');
                return false;
            }
            return true;
        case 3:
            return true; // Settings are optional
        case 4:
            return true; // Cover is optional
        default:
            return true;
    }
}

function saveStep(step) {
    return new Promise((resolve, reject) => {
        switch(step) {
            case 1:
                const formData1 = new FormData(document.getElementById('step1Form'));
                fetch('{{ route("flipbooks.wizard.step1") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData1
                })
                .then(handleFetchResponse)
                .then(data => {
                    if (data.success) {
                        resolve();
                    } else {
                        let errorMsg = data.message || 'Failed to save';
                        if (data.errors) {
                            const errorList = Object.values(data.errors).flat().join(', ');
                            errorMsg += ': ' + errorList;
                        }
                        showAlert('danger', errorMsg);
                        reject();
                    }
                })
                .catch(error => {
                    showAlert('danger', error.message || 'An error occurred');
                    reject(error);
                });
                break;
            case 3:
                const formData3 = new FormData(document.getElementById('step3Form'));
                fetch('{{ route("flipbooks.wizard.step3") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData3
                })
                .then(handleFetchResponse)
                .then(data => {
                    if (data.success) {
                        resolve();
                    } else {
                        let errorMsg = data.message || 'Failed to save';
                        if (data.errors) {
                            const errorList = Object.values(data.errors).flat().join(', ');
                            errorMsg += ': ' + errorList;
                        }
                        showAlert('danger', errorMsg);
                        reject(new Error(errorMsg));
                    }
                })
                .catch(error => {
                    let errorMsg = 'An error occurred';
                    if (error.message) {
                        errorMsg = error.message;
                    } else if (error.data) {
                        errorMsg = error.data.message || 'Validation failed';
                        if (error.data.errors) {
                            const errorList = Object.values(error.data.errors).flat().join(', ');
                            errorMsg += ': ' + errorList;
                        }
                    }
                    showAlert('danger', errorMsg);
                    reject(error);
                });
                break;
            default:
                resolve();
        }
    });
}

function loadReviewData() {
    document.getElementById('reviewTitle').textContent = document.getElementById('title').value;
    document.getElementById('reviewDescription').textContent = document.getElementById('description').value || 'No description';
    document.getElementById('reviewPages').textContent = uploadedPages.length + ' pages';
    document.getElementById('reviewTransition').textContent = document.getElementById('transition_effect').value;
    document.getElementById('reviewAutoPlay').textContent = document.getElementById('auto_play').checked ? 'Yes' : 'No';
    document.getElementById('reviewControls').textContent = document.getElementById('show_controls').checked ? 'Yes' : 'No';
    document.getElementById('reviewThumbnails').textContent = document.getElementById('show_thumbnails').checked ? 'Yes' : 'No';
    document.getElementById('reviewPublic').textContent = document.getElementById('is_public').checked ? 'Yes' : 'No';
}

function completeWizard() {
    // Ensure all steps are saved
    if (currentStep < totalSteps) {
        nextStep();
        return;
    }
    
    const btn = document.getElementById('nextBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
    
    fetch('{{ route("flipbooks.wizard.complete") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(handleFetchResponse)
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.href = '{{ route("design.templates.explore") }}';
                }
            }, 1500);
        } else {
            let errorMsg = data.message || 'Failed to create flip book';
            if (data.errors) {
                const errorList = Object.values(data.errors).flat().join(', ');
                errorMsg += ': ' + errorList;
            }
            showAlert('danger', errorMsg);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        showAlert('danger', error.message || 'An error occurred');
        btn.disabled = false;
        btn.innerHTML = originalText;
        console.error(error);
    });
}

function cancelWizard() {
    if (confirm('Are you sure you want to cancel? All progress will be lost.')) {
        fetch('{{ route("flipbooks.wizard.clear") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(handleFetchResponse)
        .then(() => {
            window.location.href = '{{ route("design.templates.explore") }}';
        })
        .catch(error => {
            console.error(error);
            // Still redirect even if clear fails
            window.location.href = '{{ route("design.templates.explore") }}';
        });
    }
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    const container = document.querySelector('.wizard-container');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>
@endpush

