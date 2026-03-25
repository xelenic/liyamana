@extends('layouts.admin')

@section('title', isset($envelopeType) ? 'Edit Envelope Type' : 'Create Envelope Type')
@section('page-title', isset($envelopeType) ? 'Edit Envelope Type' : 'Create Envelope Type')

@section('content')
<div class="my-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ isset($envelopeType) ? route('admin.envelope-types.update', $envelopeType->id) : route('admin.envelope-types.store') }}" method="POST">
                        @csrf
                        @if(isset($envelopeType))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $envelopeType->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Shown in the Send Letter envelope dropdown</small>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $envelopeType->slug ?? '') }}" required>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Stored with orders (e.g. standard, premium_cream). Lowercase, no spaces.</small>
                        </div>

                        <div class="mb-3">
                            <label for="price_per_letter" class="form-label">Extra price per letter <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" min="0" class="form-control @error('price_per_letter') is-invalid @enderror" id="price_per_letter" name="price_per_letter" value="{{ old('price_per_letter', isset($envelopeType) ? $envelopeType->price_per_letter : '0') }}" required>
                            @error('price_per_letter')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Added per mailed letter (same currency as the site).</small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $envelopeType->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Optional internal notes (not shown on checkout by default).</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort order</label>
                                    <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $envelopeType->sort_order ?? 0) }}">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label d-block">Status</label>
                                    <input type="hidden" name="is_active" value="0">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', isset($envelopeType) ? $envelopeType->is_active : true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active (visible on Send Letter)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ isset($envelopeType) ? 'Update' : 'Create' }}
                            </button>
                            <a href="{{ route('admin.envelope-types') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
