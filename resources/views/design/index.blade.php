@extends('layouts.app')

@section('title', 'My Designs')
@section('page-title', 'My Designs')

@push('styles')
<style>
    .design-card {
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
        cursor: pointer;
        border-radius: 6px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .design-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.12) !important;
    }

    #designsContainer .col-md-4,
    #designsContainer .col-lg-3 {
        margin-bottom: 0.75rem;
    }

    .design-thumbnail {
        height: 160px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .design-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .design-thumbnail .no-thumbnail {
        color: white;
        font-size: 2.25rem;
    }

    .design-info {
        padding: 0.65rem 0.75rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .design-title {
        font-size: 0.8125rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: var(--dark-text);
        line-height: 1.3;
    }

    .design-info .text-muted {
        font-size: 0.6875rem;
    }

    .design-actions {
        display: flex;
        gap: 0.35rem;
        margin-top: auto;
        padding-top: 0.5rem;
    }

    .design-actions .btn {
        flex: 1;
        font-size: 0.75rem;
        padding: 0.35rem 0.5rem;
    }

    .design-info .badge {
        font-size: 0.6rem;
        padding: 0.2rem 0.4rem;
        margin-bottom: 0.35rem;
    }

    @media (max-width: 768px) {
        #designsContainer .col-md-4,
        #designsContainer .col-lg-3 {
            margin-bottom: 0.75rem;
        }
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
    }

    .empty-state i {
        color: #cbd5e1;
        font-size: 2.5rem;
    }

    .empty-state h4 {
        font-size: 1rem;
    }

    .design-title.inline-edit {
        cursor: pointer;
        border-radius: 4px;
        padding: 0.15rem 0.25rem;
        margin: -0.15rem -0.25rem;
        transition: background 0.2s;
    }
    .design-title.inline-edit:hover {
        background: rgba(99, 102, 241, 0.1);
    }

    /* Generate Letter Section - Professional */
    .generate-letter-section {
        background: white;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .generate-letter-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .generate-letter-badge {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .generate-letter-header-text {
        min-width: 0;
    }
    .generate-letter-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 0.25rem 0;
        letter-spacing: -0.02em;
    }
    .generate-letter-subtitle {
        font-size: 0.8125rem;
        color: #64748b;
        margin: 0;
        line-height: 1.4;
    }
    .generate-letter-body {
        padding: 0;
        display: flex;
        flex-wrap: wrap;
        min-height: 0;
    }
    .generate-letter-branding {
        flex: 0 0 340px;
        min-height: 202px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .generate-letter-branding img {
        max-width: 100%;
        max-height: 320px;
        object-fit: contain;
        border-radius: 8px;
    }
    .generate-letter-form-wrap {
        flex: 1;
        min-width: 280px;
        padding: 1.5rem 1.5rem;
    }
    @media (max-width: 767px) {
        .generate-letter-branding {
            flex: 0 0 100%;
            min-height: 140px;
            border-right: none;
            border-bottom: 1px solid #e2e8f0;
        }
        .generate-letter-branding img {
            max-height: 140px;
        }
    }
    .generate-letter-form-group {
        margin-bottom: 1rem;
    }
    .generate-letter-label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.5rem;
    }
    .generate-letter-input {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9375rem;
        line-height: 1.5;
        color: #1e293b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        resize: vertical;
        min-height: 88px;
        transition: border-color 0.2s, background 0.2s;
    }
    .generate-letter-input:focus {
        outline: none;
        border-color: #6366f1;
        background: white;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .generate-letter-input::placeholder {
        color: #94a3b8;
    }
    .generate-letter-hint {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        font-size: 0.75rem;
        color: #64748b;
    }
    .generate-letter-hint i {
        color: #f59e0b;
        flex-shrink: 0;
    }
    .generate-letter-actions {
        display: flex;
        justify-content: flex-end;
    }
    .generate-letter-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        font-size: 0.9375rem;
        font-weight: 600;
        color: white;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .generate-letter-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
    }
    .generate-letter-btn:disabled {
        opacity: 0.85;
        cursor: not-allowed;
    }

    .design-title-edit-input {
        width: 100%;
        padding: 0.25rem 0.4rem;
        font-size: 0.8125rem;
        font-weight: 600;
        border: 1px solid var(--primary-color);
        border-radius: 4px;
        background: white;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">My Designs</h2>
        <p class="text-muted mb-0">Manage your designs and flip books</p>
    </div>
    <a href="{{ route('design.create', ['multi' => 'true']) }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Create New Design
    </a>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@include('design.partials.ai-content-templates')

<!-- Generate Letter Section - Professional AI Design -->
<div class="generate-letter-section">
    <div class="generate-letter-body">
        <div class="generate-letter-branding" aria-hidden="true">
            <img src="{{ asset('feature_actor/ai_generated_letters.png') }}" alt="" class="generate-letter-branding-img">
        </div>
        <div class="generate-letter-form-wrap">
            <div class="generate-letter-header-text">
                <h3 class="generate-letter-title">Generate Letter with AI</h3>
                <p class="generate-letter-subtitle">Create professional letter designs with vectors, gradients, and typography</p>
            </div><br>
            <form action="{{ route('design.generateLetter') }}" method="POST" id="generateLetterForm" onsubmit="document.getElementById('generateLetterBtn').disabled=true; document.getElementById('generateLetterBtn').innerHTML='<i class=\'fas fa-spinner fa-spin me-2\'></i>Generating...';">
                @csrf
                <div class="generate-letter-form-group">
                    <label for="letterPrompt" class="generate-letter-label">Describe your letter</label>
                    <textarea class="generate-letter-input" id="letterPrompt" name="prompt" rows="3" placeholder="e.g. Formal business letter with gradient blue header, company logo placeholder, elegant typography, date and recipient blocks, formal body text area, and signature line. Use subtle shadows and clean vector accents." required maxlength="2000"></textarea>
                    <div class="generate-letter-hint">
                        <i class="fas fa-lightbulb"></i>
                        <span>Be specific about colors, layout, and style for best results</span>
                    </div>
                </div>
                <div class="generate-letter-actions">
                    <button type="submit" class="generate-letter-btn" id="generateLetterBtn">
                        <i class="fas fa-wand-magic-sparkles"></i>
                        <span>Generate Design</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-2" id="designsContainer">
    <!-- Saved Designs and Flip Books will be loaded here -->
</div>

<div id="emptyState" class="card mt-4" style="display: none;">
    <div class="card-body">
        <div class="empty-state">
            <div style="background: url('{{url('feature_actor/relax_easy_send.png')}}');background-position: center;background-repeat: no-repeat;height: 300px;background-size: contain;margin-bottom: 20px;"></div>
            <h4 class="mb-2">No Designs Yet</h4>
            <p class="text-muted mb-4">You don't have any saved designs yet.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Load saved designs
    function loadDesigns() {
        fetch('{{ route("design.designs") }}', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.designs.length > 0) {
                const container = document.getElementById('designsContainer');
                // Clear any existing loaded designs (keep the create card)
                const existingCards = container.querySelectorAll('.col-md-4, .col-lg-3');
                existingCards.forEach((card, index) => {
                    // Skip the first card (create design card)
                    if (index > 0 || !card.querySelector('.create-design-card')) {
                        card.remove();
                    }
                });

                data.designs.forEach(design => {
                    const designCard = createDesignCard(design);
                    container.appendChild(designCard);
                });

                // Hide empty state if designs exist
                document.getElementById('emptyState').style.display = 'none';
            } else {
                // Show empty state only if no designs
                const container = document.getElementById('designsContainer');
                const designCards = container.querySelectorAll('.design-card:not(.create-design-card)');
                if (designCards.length === 0) {
                    document.getElementById('emptyState').style.display = 'block';
                } else {
                    document.getElementById('emptyState').style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error loading designs:', error);
        });
    }

    function createDesignCard(design) {
        const col = document.createElement('div');
        col.className = 'col-md-4 col-lg-3';

        const card = document.createElement('div');
        card.className = 'card design-card';

        const thumbnail = document.createElement('div');
        thumbnail.className = 'design-thumbnail';
        if (design.thumbnail) {
            const img = document.createElement('img');
            img.src = design.thumbnail;
            img.alt = design.name;
            thumbnail.appendChild(img);
        } else {
            const icon = document.createElement('div');
            icon.className = 'no-thumbnail';
            icon.innerHTML = '<i class="fas fa-palette"></i>';
            thumbnail.appendChild(icon);
        }

        const info = document.createElement('div');
        info.className = 'design-info';

        const isFlipbook = design.is_flipbook || design.flipbook_id;
        const isLetter = design.type === 'letter';
        if (isFlipbook) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-secondary';
            badge.textContent = 'Flip Book';
            info.appendChild(badge);
        } else if (isLetter) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-info';
            badge.textContent = 'Letter';
            info.appendChild(badge);
        }

        const title = document.createElement('h5');
        title.className = 'design-title inline-edit';
        title.textContent = design.name;
        title.dataset.designId = design.id;
        title.dataset.flipbookId = design.flipbook_id || '';
        title.dataset.value = design.name;
        title.title = 'Click to edit name';

        const meta = document.createElement('div');
        meta.className = 'text-muted small';
        meta.innerHTML = '<i class="fas fa-calendar me-1"></i>' + new Date(design.created_at).toLocaleDateString();

        const actions = document.createElement('div');
        actions.className = 'design-actions';

        const editUrl = isFlipbook
            ? '{{ route("design.create") }}?edit_flipbook=' + design.flipbook_id
            : '{{ route("design.create") }}?load=' + design.id + (isLetter ? '&type=letter' : '');

        const cardClickUrl = isFlipbook
            ? '{{ route("flipbooks.show", ":id") }}'.replace(':id', design.flipbook_id)
            : (isLetter ? '{{ route("design.show", ":id") }}'.replace(':id', design.id) : editUrl);

        const loadBtn = document.createElement('a');
        loadBtn.href = editUrl;
        loadBtn.className = 'btn btn-sm btn-primary';
        loadBtn.innerHTML = '<i class="fas fa-edit me-1"></i>Edit';
        loadBtn.onclick = function(e) {
            e.stopPropagation();
        };

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn btn-sm btn-outline-danger';
        deleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete';
        deleteBtn.onclick = function(e) {
            e.stopPropagation();
            const confirmMsg = isFlipbook ? 'Are you sure you want to delete this flipbook?' : 'Are you sure you want to delete this design?';
            if (confirm(confirmMsg)) {
                if (isFlipbook) {
                    deleteFlipbook(design.flipbook_id, card);
                } else {
                    deleteDesign(design.id, card);
                }
            }
        };

        actions.appendChild(loadBtn);
        if (isFlipbook) {
            const previewBtn = document.createElement('a');
            previewBtn.href = '{{ route("flipbooks.preview", ":id") }}'.replace(':id', design.flipbook_id);
            previewBtn.className = 'btn btn-sm btn-outline-primary';
            previewBtn.innerHTML = '<i class="fas fa-eye me-1"></i>Preview';
            previewBtn.onclick = function(e) {
                e.stopPropagation();
            };
            actions.appendChild(previewBtn);
        }
        actions.appendChild(deleteBtn);

        info.appendChild(title);
        info.appendChild(meta);
        info.appendChild(actions);

        card.appendChild(thumbnail);
        card.appendChild(info);

        card.onclick = function(e) {
            if (!e.target.closest('.inline-edit') && !e.target.closest('.design-actions')) {
                window.location.href = cardClickUrl;
            }
        };

        title.addEventListener('click', function(e) {
            e.stopPropagation();
            initInlineEditTitle(title, design);
        });

        col.appendChild(card);
        return col;
    }

    function deleteDesign(designId, element) {
        fetch('{{ route("design.destroy", ":id") }}'.replace(':id', designId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cardElement = element.closest('.col-md-4, .col-lg-3');
                if (cardElement) {
                    cardElement.style.transition = 'opacity 0.3s, transform 0.3s';
                    cardElement.style.opacity = '0';
                    cardElement.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        cardElement.remove();

                        // Check if there are any design cards left (excluding create card)
                        const remainingDesigns = document.querySelectorAll('.design-card:not(.create-design-card)');
                        if (remainingDesigns.length === 0) {
                            document.getElementById('emptyState').style.display = 'block';
                        }
                    }, 300);
                } else {
                    element.closest('.col-md-4, .col-lg-3')?.remove();
                }
            } else {
                alert('Failed to delete design');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting design');
        });
    }

    function deleteFlipbook(flipbookId, element) {
        fetch('{{ route("flipbooks.destroy", ":id") }}'.replace(':id', flipbookId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success !== false) {
                const cardElement = element.closest('.col-md-4, .col-lg-3');
                if (cardElement) {
                    cardElement.style.transition = 'opacity 0.3s, transform 0.3s';
                    cardElement.style.opacity = '0';
                    cardElement.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        cardElement.remove();
                        const remainingDesigns = document.querySelectorAll('.design-card:not(.create-design-card)');
                        if (remainingDesigns.length === 0) {
                            document.getElementById('emptyState').style.display = 'block';
                        }
                    }, 300);
                }
            } else {
                alert(data.message || 'Failed to delete flipbook');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting flipbook');
        });
    }

    function initInlineEditTitle(titleEl, design) {
        const value = titleEl.dataset.value || '';
        const input = document.createElement('input');
        input.type = 'text';
        input.value = value;
        input.className = 'design-title-edit-input';
        input.style.cssText = 'width: 100%;';

        const save = () => {
            const newVal = input.value.trim();
            if (newVal === value) {
                titleEl.style.display = '';
                input.remove();
                return;
            }
            const isFlipbook = design.is_flipbook || design.flipbook_id;
            const url = isFlipbook
                ? '{{ route("flipbooks.update", ":id") }}'.replace(':id', design.flipbook_id)
                : '{{ route("design.designs.updateName", ":id") }}'.replace(':id', design.id);
            const body = isFlipbook ? { title: newVal } : { name: newVal };

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    titleEl.textContent = newVal;
                    titleEl.dataset.value = newVal;
                } else {
                    alert(data.message || 'Failed to update');
                }
            })
            .catch(() => alert('Failed to update'));

            titleEl.style.display = '';
            titleEl.textContent = newVal;
            titleEl.dataset.value = newVal;
            input.remove();
        };

        input.addEventListener('blur', save);
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); save(); }
            if (e.key === 'Escape') {
                titleEl.style.display = '';
                input.remove();
            }
        });

        titleEl.style.display = 'none';
        titleEl.parentNode.insertBefore(input, titleEl);
        input.focus();
        input.select();
    }

    // Load designs on page load
    document.addEventListener('DOMContentLoaded', loadDesigns);
</script>
@endpush

