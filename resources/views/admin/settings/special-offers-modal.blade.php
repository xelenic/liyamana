@extends('layouts.admin')

@section('title', 'Special Offers Modal')
@section('page-title', 'Special Offers Modal')

@section('content')
<div class="my-2 settings-page-compact">
    <form action="{{ route('admin.settings.special-offers-modal.update') }}" method="POST" enctype="multipart/form-data" id="specialOffersModalForm">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success py-2 small mb-3">
                                <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
                            </div>
                        @endif

                        <h5 class="mb-4" style="font-weight: 600; color: #1e293b;">
                            <i class="fas fa-gift text-primary me-2"></i>Special Offers Modal (Explore Page)
                        </h5>
                        <p class="text-muted mb-4" style="font-size: 0.8rem;">
                            This modal is shown to logged-in users on the Explore Templates page. Upload a banner image and choose how often it appears.
                        </p>

                        <div class="mb-4">
                            <label class="form-label">Banner Image</label>
                            @if($imageUrl ?? null)
                                <div class="mb-2">
                                    <img src="{{ $imageUrl }}" alt="Current banner" class="img-thumbnail" style="max-height: 180px; max-width: 100%; object-fit: contain;">
                                </div>
                                <div class="form-check mb-2">
                                    <input type="hidden" name="special_offers_modal_remove_image" value="0">
                                    <input class="form-check-input" type="checkbox" name="special_offers_modal_remove_image" id="removeImage" value="1">
                                    <label class="form-check-label" for="removeImage">Remove current image</label>
                                </div>
                            @endif
                            <input type="file" class="form-control" name="special_offers_modal_image" accept="image/*">
                            <small class="text-muted d-block mt-1">Recommended: landscape, max 2 MB. Leave empty to keep current image.</small>
                        </div>

                        <div class="mb-4">
                            <label for="special_offers_modal_frequency" class="form-label">When to show modal</label>
                            <select class="form-select" id="special_offers_modal_frequency" name="special_offers_modal_frequency">
                                <option value="once" {{ old('special_offers_modal_frequency', $frequency ?? 'once') === 'once' ? 'selected' : '' }}>Once per user (ever)</option>
                                <option value="daily" {{ old('special_offers_modal_frequency', $frequency ?? 'once') === 'daily' ? 'selected' : '' }}>Once per day</option>
                                <option value="on_login" {{ old('special_offers_modal_frequency', $frequency ?? 'once') === 'on_login' ? 'selected' : '' }}>Every time user logs in</option>
                                <option value="always" {{ old('special_offers_modal_frequency', $frequency ?? 'once') === 'always' ? 'selected' : '' }}>Always (every visit to Explore)</option>
                            </select>
                            <small class="text-muted d-block mt-1">
                                <strong>Once:</strong> Show once ever per user. <strong>Daily:</strong> Show at most once per day. <strong>On login:</strong> Show once per login session. <strong>Always:</strong> Show every time they open the Explore page.
                            </small>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-2" style="font-size: 0.9rem;">
                            <i class="fas fa-info-circle text-primary me-1"></i>About
                        </h5>
                        <p class="text-muted mb-2" style="font-size: 0.75rem;">
                            The special offers modal appears on the Design → Explore Templates page for logged-in users only.
                        </p>
                        <div class="mb-2">
                            <strong style="font-size: 0.8rem;">Image</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">Optional banner image shown at the top of the modal. Use a landscape image for best results.</p>
                        </div>
                        <div>
                            <strong style="font-size: 0.8rem;">Frequency</strong>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">Controls how often the modal is shown per user. "On login" = once per browser session after they sign in.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .settings-page-compact .form-label { font-size: 0.8rem; margin-bottom: 0.25rem; }
    .settings-page-compact .form-control, .settings-page-compact .form-select { font-size: 0.8rem; padding: 0.35rem 0.5rem; min-height: 32px; }
    .settings-page-compact .btn { font-size: 0.8rem; padding: 0.35rem 0.75rem; }
</style>
@endpush
@endsection
