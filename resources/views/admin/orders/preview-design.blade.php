@extends('layouts.admin')

@section('title', 'Preview Design - Order #' . $order->id)
@section('page-title', 'Preview Design - Order #' . $order->id)

@push('styles')
<style>
    .preview-page-wrapper {
        background: linear-gradient(160deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        min-height: calc(100vh - 120px);
        padding: 1.25rem;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.15);
    }
    .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.875rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .preview-header-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
        flex-wrap: wrap;
    }
    .preview-header-right {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .preview-header h3 {
        color: rgba(255,255,255,0.95) !important;
        font-size: 0.9rem !important;
        margin: 0 !important;
        font-weight: 600 !important;
        letter-spacing: 0.01em;
    }
    .preview-tool-btn {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        color: rgba(255,255,255,0.9);
        width: 26px;
        height: 26px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.7rem;
    }
    .preview-tool-btn:hover {
        background: rgba(255,255,255,0.15);
        border-color: rgba(255,255,255,0.25);
    }
    .preview-tool-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .preview-zoom-info {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        color: rgba(255,255,255,0.85);
        padding: 0.2rem 0.5rem;
        border-radius: 5px;
        font-size: 0.7rem;
        font-weight: 600;
        min-width: 48px;
        text-align: center;
    }
    .preview-canvas-container {
        background: #f1f5f9;
        border-radius: 10px;
        padding: 1.25rem;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 420px;
        border: 1px solid rgba(0,0,0,0.04);
    }
    #previewCanvas {
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border-radius: 6px;
        background: white;
    }
    .preview-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.875rem;
        padding-top: 0.875rem;
        border-top: 1px solid rgba(255,255,255,0.08);
    }
    .preview-pagination span {
        color: rgba(255,255,255,0.85);
        font-weight: 500;
        font-size: 0.8rem;
    }
    .btn-preview {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: rgba(255,255,255,0.9);
        padding: 0.25rem 0.5rem;
        border-radius: 5px;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-preview:hover:not(:disabled) {
        background: rgba(255,255,255,0.18);
    }
    .btn-preview:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .design-selector {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .design-selector label {
        font-size: 0.7rem !important;
        color: rgba(255,255,255,0.6) !important;
    }
    .design-selector select {
        padding: 0.2rem 0.45rem;
        border-radius: 5px;
        border: 1px solid rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.08);
        color: white;
        font-size: 0.7rem;
        max-width: 180px;
    }
    .btn-back {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
        border-radius: 5px;
    }
    .btn-export-pdf {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
        border-radius: 5px;
    }
    .preview-divider {
        height: 18px;
        width: 1px;
        background: rgba(255,255,255,0.18);
    }
</style>
@endpush

@section('content')
<div class="my-4">
    <div class="preview-page-wrapper">
        <div class="preview-header">
            <div class="preview-header-left">
                <h3>
                    <i class="fas fa-eye me-2 text-primary"></i>Design Preview — Order #{{ $order->id }}
                </h3>
                @if(count($designs) > 1)
                <div class="preview-divider"></div>
                <div class="design-selector">
                    <label>Item</label>
                    <select id="designSelector" onchange="switchDesign(this.value)">
                        @foreach($designs as $idx => $d)
                        <option value="{{ $idx }}" {{ $idx == $designIndex ? 'selected' : '' }}>
                            #{{ $idx + 1 }}@if(!empty($d['variables']))
                            — {{ \Illuminate\Support\Str::limit(collect($d['variables'])->filter()->map(fn($v, $k) => $k . ': ' . $v)->implode(', '), 28) }}
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="preview-divider"></div>
                <button class="preview-tool-btn" onclick="zoomOutPreview()" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                <span class="preview-zoom-info" id="zoomLevel">100%</span>
                <button class="preview-tool-btn" onclick="zoomInPreview()" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                <button class="preview-tool-btn" onclick="fitToScreenPreview()" title="Fit to Screen"><i class="fas fa-compress-arrows-alt"></i></button>
            </div>
            <div class="preview-header-right">
                <a href="{{ route('admin.orders.pdf.item', [$order->id, $designIndex]) }}" class="btn btn-outline-danger btn-export-pdf" target="_blank" title="Export this item as PDF">
                    <i class="fas fa-file-pdf me-1"></i>Export PDF
                </a>
                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-light btn-back">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        <div class="preview-canvas-container">
            <canvas id="previewCanvas"></canvas>
        </div>

        @if(count($template->pages ?? []) > 1)
        <div class="preview-pagination">
            <button onclick="previousPreviewPage()" class="btn-preview" id="prevPageBtn"><i class="fas fa-chevron-left me-1"></i>Prev</button>
            <span id="pageIndicator">Page 1 of {{ count($template->pages ?? []) }}</span>
            <button onclick="nextPreviewPage()" class="btn-preview" id="nextPageBtn">Next<i class="fas fa-chevron-right ms-1"></i></button>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
<script>
    const templateVariables = @json($template->variables ?? []);
    const templatePages = @json($template->pages ?? []);
    const designVariables = @json($design['variables'] ?? []);
    const storageBaseUrl = @json(asset('storage'));
    const designs = @json($designs);
    const orderId = {{ $order->id }};

    let previewCanvas = null;
    let previewPages = [];
    let currentPreviewPageIndex = 0;
    let currentZoom = 1.0;
    let canvasOriginalScale = 1.0;

    function resolveImageSrc(src) {
        if (!src) return src;
        if (src.startsWith('http://') || src.startsWith('https://') || src.startsWith('data:')) return src;
        if (src.startsWith('/')) return (window.location.origin || '') + src;
        return (storageBaseUrl || '') + '/' + src.replace(/^\//, '');
    }

    function applyVariablesToPages() {
        previewPages = templatePages.map(pageData => {
            try {
                let page = typeof pageData === 'string' ? JSON.parse(pageData) : { ...pageData };
                if (page.objects && Array.isArray(page.objects)) {
                    page.objects = page.objects.map(obj => {
                        if (obj.type === 'text' || obj.type === 'i-text' || obj.type === 'textbox') {
                            let text = obj.text || '';
                            templateVariables.forEach(variable => {
                                const regex = new RegExp('\\{\\{' + variable.name + '\\}\\}', 'g');
                                const value = designVariables[variable.name] || '';
                                text = text.replace(regex, value);
                            });
                            obj.text = text;
                        }
                        if (obj.type === 'image' && obj.src) {
                            obj.src = resolveImageSrc(obj.src);
                        }
                        return obj;
                    });
                }
                return JSON.stringify(page);
            } catch (e) {
                console.error('Error processing page:', e);
                return pageData;
            }
        });
    }

    function loadPreviewPage(pageIndex) {
        if (!previewCanvas || !previewPages || previewPages.length === 0) return;
        if (pageIndex < 0 || pageIndex >= previewPages.length) return;

        const pageData = previewPages[pageIndex];
        try {
            const parsed = typeof pageData === 'string' ? JSON.parse(pageData) : pageData;
            const width = parsed.width || 800;
            const height = parsed.height || 1000;

            const container = document.querySelector('.preview-canvas-container');
            const maxWidth = container ? container.clientWidth - 40 : 800;
            const maxHeight = container ? container.clientHeight - 40 : 600;

            let baseScale = 1;
            if (width > maxWidth) baseScale = maxWidth / width;
            if (height * baseScale > maxHeight) baseScale = Math.min(baseScale, maxHeight / height);

            const finalScale = baseScale * currentZoom;
            const scaledWidth = width * finalScale;
            const scaledHeight = height * finalScale;

            previewCanvas.setDimensions({ width: scaledWidth, height: scaledHeight });
            previewCanvas.setBackgroundColor(parsed.backgroundColor || '#ffffff', previewCanvas.renderAll.bind(previewCanvas));

            previewCanvas.loadFromJSON(parsed, function() {
                previewCanvas.getObjects().forEach(obj => {
                    obj.scaleX = (obj.scaleX || 1) * finalScale;
                    obj.scaleY = (obj.scaleY || 1) * finalScale;
                    obj.left = (obj.left || 0) * finalScale;
                    obj.top = (obj.top || 0) * finalScale;
                });
                previewCanvas.renderAll();
            });

            document.getElementById('pageIndicator').textContent = `Page ${pageIndex + 1} of ${previewPages.length}`;
            document.getElementById('prevPageBtn').disabled = pageIndex === 0;
            document.getElementById('nextPageBtn').disabled = pageIndex >= previewPages.length - 1;
            document.getElementById('zoomLevel').textContent = Math.round(currentZoom * 100) + '%';
        } catch (e) {
            console.error('Error loading preview page:', e);
        }
    }

    function previousPreviewPage() {
        if (currentPreviewPageIndex > 0) {
            currentPreviewPageIndex--;
            loadPreviewPage(currentPreviewPageIndex);
        }
    }

    function nextPreviewPage() {
        if (currentPreviewPageIndex < previewPages.length - 1) {
            currentPreviewPageIndex++;
            loadPreviewPage(currentPreviewPageIndex);
        }
    }

    function zoomInPreview() {
        currentZoom = Math.min(currentZoom * 1.2, 5.0);
        loadPreviewPage(currentPreviewPageIndex);
    }

    function zoomOutPreview() {
        currentZoom = Math.max(currentZoom / 1.2, 0.1);
        loadPreviewPage(currentPreviewPageIndex);
    }

    function fitToScreenPreview() {
        if (!previewPages.length) return;
        const pageData = previewPages[currentPreviewPageIndex];
        const parsed = typeof pageData === 'string' ? JSON.parse(pageData) : pageData;
        const width = parsed.width || 800;
        const height = parsed.height || 1000;
        const container = document.querySelector('.preview-canvas-container');
        if (!container) return;
        const maxWidth = container.clientWidth - 40;
        const maxHeight = container.clientHeight - 40;
        currentZoom = Math.min(maxWidth / width, maxHeight / height);
        loadPreviewPage(currentPreviewPageIndex);
    }

    function switchDesign(index) {
        window.location.href = '{{ route("admin.orders.preview", $order->id) }}?design=' + index;
    }

    document.addEventListener('DOMContentLoaded', function() {
        applyVariablesToPages();
        previewCanvas = new fabric.Canvas('previewCanvas', {
            preserveObjectStacking: true,
            backgroundColor: '#ffffff'
        });
        loadPreviewPage(0);
    });
</script>
@endpush
