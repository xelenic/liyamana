@extends('layouts.admin')

@section('title', isset($rule) ? 'Edit Pricing Rule' : 'Create Pricing Rule')
@section('page-title', isset($rule) ? 'Edit Pricing Rule' : 'Create Pricing Rule')

@section('content')
<div class="my-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ isset($rule) ? route('admin.pricing-rules.update', $rule->id) : route('admin.pricing-rules.store') }}" method="POST">
                        @csrf
                        @if(isset($rule))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Name (optional)</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $rule->name ?? '') }}" placeholder="e.g., Matte 500+ same design">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Admin reference label for this rule</small>
                        </div>

                        <div class="mb-3">
                            <label for="sheet_type_slug" class="form-label">Sheet Type</label>
                            <select class="form-select @error('sheet_type_slug') is-invalid @enderror" id="sheet_type_slug" name="sheet_type_slug">
                                <option value="">All sheet types</option>
                                @foreach($sheetTypes as $st)
                                    <option value="{{ $st->slug }}" {{ old('sheet_type_slug', $rule->sheet_type_slug ?? '') == $st->slug ? 'selected' : '' }}>{{ $st->name }} ({{ $st->slug }})</option>
                                @endforeach
                            </select>
                            @error('sheet_type_slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave empty to apply to all sheet types</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="min_quantity" class="form-label">Min Quantity <span class="text-danger">*</span></label>
                                    <input type="number" min="1" class="form-control @error('min_quantity') is-invalid @enderror" id="min_quantity" name="min_quantity" value="{{ old('min_quantity', $rule->min_quantity ?? 1) }}" required>
                                    @error('min_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Rule applies when quantity ≥ this</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_quantity" class="form-label">Max Quantity (optional)</label>
                                    <input type="number" min="1" class="form-control @error('max_quantity') is-invalid @enderror" id="max_quantity" name="max_quantity" value="{{ old('max_quantity', $rule->max_quantity ?? '') }}" placeholder="Leave empty for no limit">
                                    @error('max_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave empty for no upper limit</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="discount_percent" class="form-label">Discount (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('discount_percent') is-invalid @enderror" id="discount_percent" name="discount_percent" value="{{ old('discount_percent', $rule->discount_percent ?? 0) }}" required>
                            @error('discount_percent')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Percent off sheet cost (e.g., 10 = 10% off). Use 0 for no discount (e.g., mixed designs same cost).</small>
                        </div>

                        <div class="mb-3">
                            <label for="applies_to_design" class="form-label">Applies To Design <span class="text-danger">*</span></label>
                            <select class="form-select @error('applies_to_design') is-invalid @enderror" id="applies_to_design" name="applies_to_design" required>
                                <option value="any" {{ old('applies_to_design', $rule->applies_to_design ?? 'any') == 'any' ? 'selected' : '' }}>Any (same or mixed)</option>
                                <option value="same_design" {{ old('applies_to_design', $rule->applies_to_design ?? '') == 'same_design' ? 'selected' : '' }}>Same Design Only</option>
                                <option value="mixed_designs" {{ old('applies_to_design', $rule->applies_to_design ?? '') == 'mixed_designs' ? 'selected' : '' }}>Mixed Designs Only</option>
                            </select>
                            @error('applies_to_design')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Same Design = all items use same variables. Mixed = each item different.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $rule->sort_order ?? 0) }}">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Higher = evaluated first (best discount wins)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input type="hidden" name="is_active" value="0">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                    <small class="text-muted d-block mt-1">Only active rules apply to checkout</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.pricing-rules') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ isset($rule) ? 'Update' : 'Create' }} Rule
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
                        <i class="fas fa-info-circle text-primary me-2"></i>Examples
                    </h5>
                    <div class="mb-3">
                        <strong>Matte 500+ same design:</strong>
                        <p class="text-muted small mb-0">Sheet: matte, Min: 500, Discount: 10%, Applies: Same Design</p>
                    </div>
                    <div class="mb-3">
                        <strong>Standard mixed designs:</strong>
                        <p class="text-muted small mb-0">Sheet: standard, Min: 1, Discount: 0%, Applies: Mixed Designs</p>
                    </div>
                    <div>
                        <strong>Sort Order:</strong>
                        <p class="text-muted small mb-0">Rules with higher sort order are checked first. Use higher values for more specific rules.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
