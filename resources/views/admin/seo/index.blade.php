@extends('layouts.admin')

@section('title', 'SEO')
@section('page-title', 'SEO & meta')

@section('content')
<div class="my-2">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-0"><i class="fas fa-globe me-2 text-primary"></i>Site-wide defaults</h5>
                <small class="text-muted">Fallback description, default social image, verification tags, JSON-LD</small>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.seo.global') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title suffix</label>
                        <input type="text" name="seo_site_title_suffix" class="form-control" value="{{ old('seo_site_title_suffix', $global['seo_site_title_suffix']) }}" placeholder=" e.g. | {{ site_name() }}">
                        <small class="text-muted">Appended when a page has a custom meta title. Leave empty to use “ - {{ site_name() }}”.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Default robots</label>
                        <input type="text" name="seo_default_robots" class="form-control" value="{{ old('seo_default_robots', $global['seo_default_robots']) }}" placeholder="index, follow">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Default meta description</label>
                        <textarea name="seo_default_meta_description" class="form-control" rows="2" maxlength="2000">{{ old('seo_default_meta_description', $global['seo_default_meta_description']) }}</textarea>
                        <small class="text-muted">Used when a route has no page-specific description.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Default OG / Twitter image URL</label>
                        <input type="text" name="seo_default_og_image" class="form-control" value="{{ old('seo_default_og_image', $global['seo_default_og_image']) }}" placeholder="https://… or /path/to/image.jpg">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Twitter handle</label>
                        <input type="text" name="seo_twitter_handle" class="form-control" value="{{ old('seo_twitter_handle', $global['seo_twitter_handle']) }}" placeholder="yourbrand (without @)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Google site verification</label>
                        <input type="text" name="seo_google_site_verification" class="form-control" value="{{ old('seo_google_site_verification', $global['seo_google_site_verification']) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bing site verification</label>
                        <input type="text" name="seo_bing_site_verification" class="form-control" value="{{ old('seo_bing_site_verification', $global['seo_bing_site_verification']) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Organization JSON-LD (optional)</label>
                        <textarea name="seo_organization_json_ld" class="form-control font-monospace small" rows="6" maxlength="10000">{{ old('seo_organization_json_ld', $global['seo_organization_json_ld']) }}</textarea>
                        <small class="text-muted">Valid JSON object, output on all public pages using the main layout.</small>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save global SEO</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <form method="GET" action="{{ route('admin.seo.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
                <input type="search" name="q" value="{{ $q }}" class="form-control" style="min-width: 220px;" placeholder="Search route, label, path…">
                <button type="submit" class="btn btn-outline-secondary">Search</button>
                @if($q !== '')
                    <a href="{{ route('admin.seo.index') }}" class="btn btn-link">Clear</a>
                @endif
            </form>
            <form action="{{ route('admin.seo.sync-registry') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm" title="Add rows from config/seo.php for new routes">
                    <i class="fas fa-sync-alt me-1"></i>Sync pages from config
                </button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Page</th>
                            <th>Route key</th>
                            <th class="text-center">Score</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pages as $p)
                            @php
                                $sc = (int) ($p->seo_score_value ?? 0);
                                $badge = $sc >= 85 ? 'success' : ($sc >= 65 ? 'primary' : ($sc >= 45 ? 'warning' : 'secondary'));
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-600">{{ $p->label }}</div>
                                    @if($p->path_hint)
                                        <small class="text-muted">{{ $p->path_hint }}</small>
                                    @endif
                                </td>
                                <td><code class="small">{{ $p->page_key }}</code></td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $badge }}">{{ $sc }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.seo.edit', $p) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No SEO pages. Run migrations and seed, then “Sync pages from config”.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pages->hasPages())
                <div class="card-footer bg-white">{{ $pages->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
