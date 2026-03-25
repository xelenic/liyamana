@extends('layouts.admin')

@section('title', isset($currency) ? 'Edit Currency' : 'Create Currency')
@section('page-title', isset($currency) ? 'Edit Currency' : 'Create Currency')

@section('content')
<div class="my-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ isset($currency) ? route('admin.currencies.update', $currency->id) : route('admin.currencies.store') }}" method="POST">
                        @csrf
                        @if(isset($currency))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $currency->code ?? '') }}" placeholder="USD" required maxlength="10">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">ISO code (e.g. USD, EUR, GBP)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $currency->name ?? '') }}" placeholder="US Dollar" required maxlength="64">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="symbol" class="form-label">Symbol</label>
                                    <input type="text" class="form-control @error('symbol') is-invalid @enderror" id="symbol" name="symbol" value="{{ old('symbol', $currency->symbol ?? '') }}" placeholder="$" maxlength="16">
                                    @error('symbol')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="decimal_places" class="form-label">Decimal places</label>
                                    <input type="number" min="0" max="6" class="form-control @error('decimal_places') is-invalid @enderror" id="decimal_places" name="decimal_places" value="{{ old('decimal_places', $currency->decimal_places ?? 2) }}">
                                    @error('decimal_places')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort order</label>
                                    <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $currency->sort_order ?? 0) }}">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <input type="hidden" name="is_active" value="0">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $currency->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <input type="hidden" name="is_default" value="0">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $currency->is_default ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default">Default currency</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.currencies') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ isset($currency) ? 'Update' : 'Create' }} Currency
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-info-circle text-primary me-2"></i>Information
                    </h5>
                    <p class="text-muted small mb-0">
                        Currencies can be used for pricing and display across the app. Set one as default. Code should be unique (e.g. USD, EUR). Symbol is used when formatting amounts.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
