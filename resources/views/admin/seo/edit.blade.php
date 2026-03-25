@extends('layouts.admin')

@section('title', 'Edit SEO: '.$page->label)
@section('page-title', 'SEO: '.$page->label)

@section('content')
<div class="my-2">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('admin.seo.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>All pages</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">{{ $page->label }}</h5>
                    <small class="text-muted"><code>{{ $page->page_key }}</code>@if($page->path_hint) · {{ $page->path_hint }}@endif</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.seo.update', $page) }}" method="POST" id="seoPageForm">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Meta title</label>
                            <input type="text" name="meta_title" id="fld_meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $page->meta_title) }}" maxlength="255" data-seo-field>
                            @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Browser tab / search title base (site suffix is added automatically when set).</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meta description</label>
                            <textarea name="meta_description" id="fld_meta_description" class="form-control @error('meta_description') is-invalid @enderror" rows="3" maxlength="2000" data-seo-field>{{ old('meta_description', $page->meta_description) }}</textarea>
                            @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meta keywords (optional)</label>
                            <input type="text" name="meta_keywords" id="fld_meta_keywords" class="form-control" value="{{ old('meta_keywords', $page->meta_keywords) }}" maxlength="500" data-seo-field>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Focus keyword (for score hints)</label>
                            <input type="text" name="focus_keyword" id="fld_focus_keyword" class="form-control" value="{{ old('focus_keyword', $page->focus_keyword) }}" maxlength="120" data-seo-field>
                        </div>
                        <hr>
                        <h6 class="text-muted mb-3">Open Graph / social</h6>
                        <div class="mb-3">
                            <label class="form-label">OG title (optional)</label>
                            <input type="text" name="og_title" class="form-control" value="{{ old('og_title', $page->og_title) }}" maxlength="255">
                            <small class="text-muted">Defaults to meta title or site name.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">OG description (optional)</label>
                            <textarea name="og_description" class="form-control" rows="2" maxlength="2000">{{ old('og_description', $page->og_description) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">OG image URL</label>
                            <input type="text" name="og_image" id="fld_og_image" class="form-control" value="{{ old('og_image', $page->og_image) }}" maxlength="2048" data-seo-field placeholder="https://… or /images/og.jpg">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Twitter card</label>
                            <select name="twitter_card" class="form-select">
                                @foreach(['summary_large_image' => 'Summary large image', 'summary' => 'Summary'] as $val => $lab)
                                    <option value="{{ $val }}" @selected(old('twitter_card', $page->twitter_card) === $val)>{{ $lab }}</option>
                                @endforeach
                            </select>
                        </div>
                        <hr>
                        <h6 class="text-muted mb-3">Advanced</h6>
                        <div class="mb-3">
                            <label class="form-label">Canonical URL (optional)</label>
                            <input type="text" name="canonical_url" id="fld_canonical_url" class="form-control" value="{{ old('canonical_url', $page->canonical_url) }}" maxlength="2048" data-seo-field>
                            <small class="text-muted">Leave empty to use the current URL when this route is visited.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Robots (optional)</label>
                            <input type="text" name="robots" id="fld_robots" class="form-control" value="{{ old('robots', $page->robots) }}" maxlength="120" data-seo-field placeholder="e.g. noindex, nofollow">
                            <small class="text-muted">Overrides global default for this route only.</small>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 1rem;">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>On-page score (estimate)</h6>
                    <small class="text-muted">Heuristic preview only — not a Google ranking guarantee.</small>
                </div>
                <div class="card-body" id="seoScorePanel">
                    <div class="text-center mb-3">
                        <div class="display-4 fw-bold text-primary" id="seoScoreValue">{{ $preview['score'] }}</div>
                        <div class="badge bg-secondary" id="seoScoreGrade">{{ $preview['grade'] }}</div>
                    </div>
                    <ul class="list-unstyled small mb-0" id="seoScoreChecks">
                        @foreach($preview['checks'] as $c)
                            <li class="mb-2 d-flex gap-2">
                                <span class="text-{{ $c['ok'] ? 'success' : 'warning' }}"><i class="fas fa-{{ $c['ok'] ? 'check' : 'exclamation-triangle' }}"></i></span>
                                <span><strong>{{ $c['label'] }}:</strong> {{ $c['detail'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const url = @json(route('admin.seo.score-preview'));
    let t = null;

    function collect() {
        return {
            meta_title: document.getElementById('fld_meta_title')?.value || '',
            meta_description: document.getElementById('fld_meta_description')?.value || '',
            meta_keywords: document.getElementById('fld_meta_keywords')?.value || '',
            og_image: document.getElementById('fld_og_image')?.value || '',
            canonical_url: document.getElementById('fld_canonical_url')?.value || '',
            robots: document.getElementById('fld_robots')?.value || '',
            focus_keyword: document.getElementById('fld_focus_keyword')?.value || ''
        };
    }

    function render(data) {
        const v = document.getElementById('seoScoreValue');
        const g = document.getElementById('seoScoreGrade');
        const ul = document.getElementById('seoScoreChecks');
        if (!v || !g || !ul) return;
        v.textContent = data.score;
        g.textContent = data.grade;
        g.className = 'badge ' + (data.score >= 85 ? 'bg-success' : (data.score >= 65 ? 'bg-primary' : (data.score >= 45 ? 'bg-warning text-dark' : 'bg-secondary')));
        ul.innerHTML = (data.checks || []).map(function(c) {
            const ok = c.ok;
            return '<li class="mb-2 d-flex gap-2"><span class="text-' + (ok ? 'success' : 'warning') + '"><i class="fas fa-' + (ok ? 'check' : 'exclamation-triangle') + '"></i></span><span><strong>' +
                escapeHtml(c.label) + ':</strong> ' + escapeHtml(c.detail) + '</span></li>';
        }).join('');
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function schedule() {
        clearTimeout(t);
        t = setTimeout(run, 400);
    }

    function run() {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(collect())
        }).then(function(r) { return r.json(); }).then(render).catch(function() {});
    }

    document.querySelectorAll('[data-seo-field]').forEach(function(el) {
        el.addEventListener('input', schedule);
        el.addEventListener('change', schedule);
    });
})();
</script>
@endpush
