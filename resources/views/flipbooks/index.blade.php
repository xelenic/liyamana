@extends('layouts.app')

@section('title', 'My Flip Books')
@section('page-title', 'My Flip Books')

@push('styles')
<style>
    .flipbook-card {
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
        cursor: pointer;
    }

    .flipbook-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }

    .flipbook-cover {
        height: 250px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .flipbook-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .flipbook-cover .no-cover {
        color: white;
        font-size: 3rem;
    }

    .flipbook-info {
        padding: 1rem;
    }

    .flipbook-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--dark-text);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .flipbook-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
    }

    .flipbook-status {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .flipbook-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.5rem;
        flex-wrap: wrap;
    }

    .flipbook-actions .btn {
        flex: 1;
        min-width: 80px;
        font-size: 0.8rem;
        padding: 0.375rem 0.75rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state i {
        color: #cbd5e1;
        display: block;
    }

    .empty-state h4 {
        color: #334155;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .empty-state p {
        color: #64748b;
        margin-bottom: 2rem;
        font-size: 1rem;
    }

    .empty-state .btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        text-decoration: none;
        background: var(--primary-color) !important;
        border: none !important;
        color: white !important;
    }

    .empty-state .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        text-decoration: none;
        background: var(--primary-color) !important;
        opacity: 0.9;
        color: white !important;
    }

    .empty-state .btn:active {
        transform: translateY(0);
        background: var(--primary-color) !important;
        color: white !important;
    }

    .empty-state .btn:focus {
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25) !important;
        background: var(--primary-color) !important;
        color: white !important;
    }

    .empty-state .btn i {
        font-size: 0.875rem;
    }

    .empty-state .btn span {
        display: inline-block;
        line-height: 1.5;
    }

    @media (max-width: 576px) {
        .empty-state {
            padding: 3rem 1.5rem;
        }

        .empty-state .btn {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
    }

    .page-count {
        font-size: 0.875rem;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">My Flip Books</h2>
        <p class="text-muted mb-0">Manage and view all your flip books</p>
    </div>
    <a href="{{ route('flipbooks.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Create New Flip Book
    </a>
</div>

@if($flipBooks->count() > 0)
    <div class="row">
        @foreach($flipBooks as $flipBook)
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card flipbook-card" onclick="window.location.href='{{ route('flipbooks.show', $flipBook->id) }}'">
                    <div class="flipbook-cover">
                        @if($flipBook->cover_image)
                            <img src="{{ asset('storage/' . $flipBook->cover_image) }}" alt="{{ $flipBook->title }}">
                        @elseif($flipBook->pages && count($flipBook->pages) > 0)
                            <img src="{{ asset('storage/' . $flipBook->pages[0]['path']) }}" alt="{{ $flipBook->title }}">
                        @else
                            <div class="no-cover">
                                <i class="fas fa-book-open"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flipbook-info">
                        <h5 class="flipbook-title">{{ $flipBook->title }}</h5>
                        @if($flipBook->description)
                            <p class="text-muted small mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $flipBook->description }}
                            </p>
                        @endif
                        <div class="flipbook-meta">
                            <div>
                                <span class="badge flipbook-status bg-{{ $flipBook->status === 'published' ? 'success' : ($flipBook->status === 'draft' ? 'secondary' : 'warning') }}">
                                    {{ ucfirst($flipBook->status) }}
                                </span>
                                <span class="page-count ms-2">
                                    <i class="fas fa-images"></i> {{ count($flipBook->pages ?? []) }} pages
                                </span>
                            </div>
                        </div>
                        <div class="flipbook-actions mt-2">
                            <a href="{{ route('flipbooks.preview', $flipBook->id) }}" class="btn btn-sm btn-primary" onclick="event.stopPropagation();">
                                <i class="fas fa-eye me-1"></i>Preview
                            </a>
                            @php
                                $hasDesign = false;
                                if ($flipBook->settings && is_array($flipBook->settings)) {
                                    $hasDesign = isset($flipBook->settings['created_from_design'])
                                        && $flipBook->settings['created_from_design']
                                        && isset($flipBook->settings['design_data'])
                                        && !empty($flipBook->settings['design_data']);
                                }
                            @endphp
                            @if($hasDesign)
                                <a href="{{ route('design.create', ['multi' => 'true', 'edit_flipbook' => $flipBook->id]) }}" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();" title="Edit Design">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                            @endif
                            <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteFlipbook({{ $flipBook->id }}, this);" title="Delete Flip Book">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $flipBook->created_at->format('M d, Y') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $flipBooks->links() }}
    </div>
@else
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h4>No Flip Books Yet</h4>
                <p>Get started by creating your first flip book!</p>
                <a href="{{ route('flipbooks.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Create Your First Flip Book</span>
                </a>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
function deleteFlipbook(flipbookId, button) {
    if (!confirm('Are you sure you want to delete this flip book? This action cannot be undone.')) {
        return;
    }

    // Disable button during deletion
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';

    fetch('{{ route("flipbooks.destroy", ":id") }}'.replace(':id', flipbookId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the card from the DOM
            const cardElement = button.closest('.col-md-4');
            if (cardElement) {
                cardElement.style.transition = 'opacity 0.3s, transform 0.3s';
                cardElement.style.opacity = '0';
                cardElement.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    cardElement.remove();

                    // Check if there are any flipbooks left
                    const remainingCards = document.querySelectorAll('.flipbook-card');
                    if (remainingCards.length === 0) {
                        // Reload page to show empty state
                        window.location.reload();
                    }
                }, 300);
            } else {
                // Fallback: reload page
                window.location.reload();
            }
        } else {
            alert('Failed to delete flip book: ' + (data.message || 'Unknown error'));
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-trash me-1"></i>Delete';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting flip book. Please try again.');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-trash me-1"></i>Delete';
    });
}
</script>
@endpush

