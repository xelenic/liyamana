@extends('layouts.app')

@section('title', $design['name'] ?? 'Letter')
@section('page-title', $design['name'] ?? 'Letter')

@push('styles')
<style>
    .letter-show-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        color: white;
        margin-bottom: 1.5rem;
    }
    .letter-show-header .back-link {
        color: rgba(255,255,255,0.9);
        font-size: 0.8125rem;
        font-weight: 500;
        transition: color 0.2s;
    }
    .letter-show-header .back-link:hover {
        color: white;
    }
    .letter-show-header h1 {
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0.5rem 0 0.25rem 0;
        letter-spacing: -0.02em;
    }
    .letter-show-header .meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
        margin-top: 0.75rem;
        font-size: 0.8125rem;
        opacity: 0.95;
    }
    .letter-show-header .meta-row .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.6rem;
        font-weight: 600;
    }
    .letter-show-content {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 1.5rem;
    }
    @media (max-width: 991px) {
        .letter-show-content {
            grid-template-columns: 1fr;
        }
    }
    .letter-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .letter-cover-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .letter-cover-card .cover-img {
        aspect-ratio: 3/4;
        object-fit: cover;
        width: 100%;
    }
    .letter-cover-card .card-body {
        padding: 0.85rem 1rem;
        font-size: 0.8125rem;
        color: #64748b;
    }
    .letter-actions-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        padding: 1rem;
    }
    .letter-actions-card .btn {
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .letter-actions-card .btn:last-child {
        margin-bottom: 0;
    }
    .letter-main {
        min-width: 0;
    }
    .letter-section {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 1rem;
    }
    .letter-section .card-body {
        padding: 1.25rem 1.5rem;
    }
    .letter-section-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
@php
    $designId = $design['id'] ?? '';
    $designName = $design['name'] ?? 'Letter';
    $pageCount = $design['page_count'] ?? count($design['pages'] ?? []);
    $createdAt = $design['created_at'] ?? now()->toDateTimeString();
    $thumbnailUrl = null;
    if (!empty($design['thumbnail_path'])) {
        $thumbnailUrl = asset('storage/' . $design['thumbnail_path']);
    } elseif (!empty($design['thumbnail']) && str_starts_with($design['thumbnail'] ?? '', 'data:image')) {
        $thumbnailUrl = $design['thumbnail'];
    }
@endphp

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="letter-show-header">
    <a href="{{ route('design.index') }}" class="back-link text-decoration-none">
        <i class="fas fa-arrow-left me-2"></i>Back to My Designs
    </a>
    <h1>{{ $designName }}</h1>
    <div class="meta-row">
        <span class="badge bg-info">Letter</span>
        <span><i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($createdAt)->format('M d, Y') }}</span>
        <span><i class="fas fa-file-alt me-1"></i>{{ $pageCount }} page{{ $pageCount !== 1 ? 's' : '' }}</span>
    </div>
</div>

<div class="letter-show-content">
    <aside class="letter-sidebar">
        <div class="card letter-cover-card">
            @if($thumbnailUrl)
                <img src="{{ $thumbnailUrl }}" alt="Letter" class="cover-img">
            @else
                <div style="aspect-ratio: 3/4; background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-envelope" style="font-size: 3rem; color: #94a3b8;"></i>
                </div>
            @endif
            <div class="card-body text-center">
                Letter Preview
            </div>
        </div>

        <div class="card letter-actions-card">
            <a href="{{ route('design.letter.send', $designId) }}" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i>Send Letter
            </a>
            <a href="{{ route('design.create', ['load' => $designId, 'type' => 'letter']) }}" class="btn btn-outline-primary">
                <i class="fas fa-palette"></i>Edit Design
            </a>
            <a href="{{ route('design.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-th-large"></i>My Designs
            </a>
            <form action="{{ route('design.destroy', $designId) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this letter?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100">
                    <i class="fas fa-trash"></i>Delete
                </button>
            </form>
        </div>
    </aside>

    <main class="letter-main">
        <div class="card letter-section">
            <div class="card-body">
                <h5 class="letter-section-title"><i class="fas fa-file-alt me-2"></i>Letter Details</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.25rem;">Name</div>
                        <div style="font-size: 0.9375rem; font-weight: 600; color: #1e293b;">{{ $designName }}</div>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.25rem;">Pages</div>
                        <div style="font-size: 0.9375rem; font-weight: 600; color: #1e293b;">{{ $pageCount }}</div>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.25rem;">Created</div>
                        <div style="font-size: 0.9375rem; color: #475569;">{{ \Carbon\Carbon::parse($createdAt)->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.25rem;">Type</div>
                        <div style="font-size: 0.9375rem;"><span class="badge bg-info">Letter</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card letter-section">
            <div class="card-body">
                <h5 class="letter-section-title"><i class="fas fa-info-circle me-2"></i>About</h5>
                <p class="mb-0" style="font-size: 0.875rem; color: #64748b; line-height: 1.6;">
                    This is your saved letter design. Use <strong>Send Letter</strong> to proceed to checkout with envelope options and recipient addresses. Use <strong>Edit Design</strong> to make changes to the letter content.
                </p>
            </div>
        </div>
    </main>
</div>
@endsection
