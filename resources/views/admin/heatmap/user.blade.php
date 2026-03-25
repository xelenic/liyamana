@extends('layouts.admin')

@section('title', 'Heatmap: ' . $user->name)
@section('page-title', 'User heatmap')

@push('styles')
<style>
    .heatmap-stage {
        position: relative;
        width: 900px;
        height: 560px;
        line-height: 0;
        overflow: hidden;
        border-radius: 6px;
        background-color: #f1f5f9;
        background-image:
            linear-gradient(rgba(148, 163, 184, 0.22) 1px, transparent 1px),
            linear-gradient(90deg, rgba(148, 163, 184, 0.22) 1px, transparent 1px);
        background-size: 20px 20px;
        box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.08);
    }
    .heatmap-stage .heatmap-page-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
        z-index: 1;
        background: #fff;
    }
    .heatmap-stage .heatmap-overlay {
        position: absolute;
        inset: 0;
        z-index: 2;
        pointer-events: none;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/heatmap.js@2.0.5/build/heatmap.min.js" crossorigin="anonymous"></script>
@if(!$paths->isEmpty())
<script>
(function () {
    var baseDataUrl = @json(route('admin.heatmap.data', $user));
    var path = @json($selectedPath ?? '');
    document.addEventListener('DOMContentLoaded', function () {
        if (!path || typeof h337 === 'undefined') {
            return;
        }
        var url = baseDataUrl + '?path=' + encodeURIComponent(path);
        fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var stage = document.getElementById('heatmap-stage');
                var el = document.getElementById('heatmap-wrap');
                var frame = document.getElementById('heatmap-page-bg');
                var meta = document.getElementById('heatmap-meta');
                if (!el || !stage) return;

                var w = d.containerWidth || 900;
                var h = d.containerHeight || 560;
                stage.style.width = w + 'px';
                stage.style.height = h + 'px';
                if (frame) {
                    frame.style.width = '100%';
                    frame.style.height = '100%';
                }
                el.style.width = w + 'px';
                el.style.height = h + 'px';
                el.innerHTML = '';

                if (meta) {
                    meta.textContent = d.totalClicksLoaded
                        ? (d.points.length + ' buckets · ' + d.totalClicksLoaded + ' clicks loaded')
                        : 'No data';
                }
                if (!d.points || !d.points.length) {
                    el.innerHTML = '<div class="d-flex align-items-center justify-content-center small text-dark" style="width:' + w + 'px;height:' + h + 'px;background:rgba(255,255,255,0.55);">No clicks for this path.</div>';
                    return;
                }
                var inst = h337.create({
                    container: el,
                    radius: 42,
                    maxOpacity: 0.72,
                    minOpacity: 0.08,
                    blur: 0.88,
                    backgroundColor: 'rgba(0,0,0,0)'
                });
                inst.setData({ max: d.max, data: d.points });
            })
            .catch(function () {
                var meta = document.getElementById('heatmap-meta');
                if (meta) meta.textContent = 'Failed to load data';
            });
    });
})();
</script>
@endif
@endpush

@section('content')
<div class="my-4">
    <a href="{{ route('admin.heatmap.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-1"></i>All users
    </a>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="h5 mb-1" style="font-weight: 700; color: #1e293b;">
                    <i class="fas fa-user me-2 text-primary"></i>{{ $user->name }}
                </h2>
                <p class="text-muted small mb-0">{{ $user->email }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.session-recordings.user', $user) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-circle-play me-1"></i>Session recordings
                </a>
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-id-card me-1"></i>Profile
                </a>
            </div>
        </div>
    </div>

    @if($paths->isEmpty())
        <div class="alert alert-light border text-muted">No clicks stored for this user yet.</div>
    @else
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="get" action="{{ route('admin.heatmap.user', $user) }}" class="row g-2 align-items-end">
                    <div class="col-md-8 col-lg-6">
                        <label class="form-label small text-muted mb-1">Page path</label>
                        <select name="path" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach($paths as $p)
                                <option value="{{ $p->path }}" @selected($selectedPath === $p->path)>
                                    {{ \Illuminate\Support\Str::limit($p->path, 90) }} ({{ number_format($p->clicks_count) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <strong class="small">Page + click intensity</strong>
                <span class="text-muted small" id="heatmap-meta"></span>
            </div>
            <div class="card-body">
                <div class="d-inline-block shadow-sm rounded">
                    <div id="heatmap-stage" class="heatmap-stage">
                        @if($heatmapIframeSrc !== '')
                            <iframe
                                id="heatmap-page-bg"
                                class="heatmap-page-bg"
                                src="{{ $heatmapIframeSrc }}"
                                title="Page preview (loaded with your admin session)"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                            ></iframe>
                        @else
                            <div id="heatmap-page-bg" class="heatmap-page-bg d-flex align-items-center justify-content-center text-muted small p-3 text-center">
                                No safe preview URL for this path. Grid shows viewport-relative click positions only.
                            </div>
                        @endif
                        <div id="heatmap-wrap" class="heatmap-overlay"></div>
                    </div>
                </div>
                <p class="text-muted small mt-3 mb-0">
                    <strong>Background:</strong> same path in an iframe (your admin session), so layout may differ from the user’s view.
                    Clicks are mapped to the canvas size returned by the server (default 900×560) from recorded viewport %.
                </p>
                <p class="text-muted small mb-0">
                    If the iframe is blank, check <code>X-Frame-Options</code> / CSP <code>frame-ancestors</code> for your app.
                </p>
            </div>
        </div>
    @endif
</div>
@endsection
