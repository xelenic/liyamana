<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Item #{{ $idx + 1 }} - Order #{{ $order->id }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f1f5f9; }
        .export-box { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        .export-box h2 { margin: 0 0 0.5rem 0; font-size: 1.25rem; color: #1e293b; }
        .export-box p { margin: 0; color: #64748b; font-size: 0.875rem; }
        .spinner { display: inline-block; width: 24px; height: 24px; border: 3px solid #e2e8f0; border-top-color: #6366f1; border-radius: 50%; animation: spin 0.8s linear infinite; margin-bottom: 1rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .back-link { display: inline-block; margin-top: 1.5rem; color: #6366f1; text-decoration: none; font-size: 0.875rem; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="export-box">
        <div class="spinner" id="spinner"></div>
        <h2 id="statusText">Preparing export...</h2>
        <p id="statusDetail">Loading design data and generating PDF</p>
        <a href="{{ route('admin.orders.show', $order->id) }}" class="back-link" id="backLink" style="display: none;">← Back to Order</a>
    </div>

    <script>
    (function() {
        const templateVariables = @json($template->variables ?? []);
        const templatePages = @json($template->pages ?? []);
        const designVariables = @json($design['variables'] ?? []);
        const storageBaseUrl = @json(asset('storage'));
        const orderId = {{ $order->id }};
        const itemIndex = {{ $idx }};

        function resolveImageSrc(src) {
            if (!src) return src;
            if (src.startsWith('http://') || src.startsWith('https://') || src.startsWith('data:')) return src;
            if (src.startsWith('/')) return (window.location.origin || '') + src;
            return (storageBaseUrl || '') + '/' + src.replace(/^\//, '');
        }

        function applyVariablesToPages() {
            return templatePages.map(function(pageData) {
                try {
                    let page = typeof pageData === 'string' ? JSON.parse(pageData) : JSON.parse(JSON.stringify(pageData));
                    if (page.objects && Array.isArray(page.objects)) {
                        page.objects = page.objects.map(function(obj) {
                            if (obj.type === 'text' || obj.type === 'i-text' || obj.type === 'textbox') {
                                let text = obj.text || '';
                                templateVariables.forEach(function(variable) {
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
                    return page;
                } catch (e) {
                    console.error('Error processing page:', e);
                    return typeof pageData === 'string' ? JSON.parse(pageData) : pageData;
                }
            });
        }

        function setStatus(text, detail) {
            const el = document.getElementById('statusText');
            const det = document.getElementById('statusDetail');
            if (el) el.textContent = text;
            if (det) det.textContent = detail || '';
        }

        function done() {
            document.getElementById('spinner').style.display = 'none';
            document.getElementById('backLink').style.display = 'inline-block';
        }

        function runExport() {
            const pages = applyVariablesToPages();
            if (pages.length === 0) {
                setStatus('No pages to export', 'Template has no pages.');
                done();
                return;
            }

            setStatus('Generating PDF...', 'Processing ' + pages.length + ' page(s)');

            if (typeof window.jspdf === 'undefined' || typeof fabric === 'undefined') {
                setStatus('Error', 'Required libraries not loaded. Please refresh and try again.');
                done();
                return;
            }

            let pdfWidth = 800, pdfHeight = 1000;
            const firstPage = pages[0];
            if (firstPage.width && firstPage.height) {
                pdfWidth = firstPage.width;
                pdfHeight = firstPage.height;
            }

            const mmWidth = (pdfWidth / 96) * 25.4;
            const mmHeight = (pdfHeight / 96) * 25.4;

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: mmWidth > mmHeight ? 'landscape' : 'portrait',
                unit: 'mm',
                format: [mmWidth, mmHeight]
            });

            let pagesProcessed = 0;

            pages.forEach(function(pageData, index) {
                setTimeout(function() {
                    try {
                        let canvasWidth = pdfWidth, canvasHeight = pdfHeight;
                        if (pageData.width && pageData.height) {
                            canvasWidth = pageData.width;
                            canvasHeight = pageData.height;
                        }
                        const backgroundColor = pageData.background || pageData.backgroundColor || '#ffffff';
                        const pageMmWidth = (canvasWidth / 96) * 25.4;
                        const pageMmHeight = (canvasHeight / 96) * 25.4;

                        const tempCanvas = new fabric.Canvas(null, {
                            width: canvasWidth,
                            height: canvasHeight,
                            backgroundColor: backgroundColor
                        });

                        tempCanvas.loadFromJSON(pageData, function() {
                            tempCanvas.renderAll();
                            const dataURL = tempCanvas.toDataURL({
                                format: 'png',
                                quality: 1.0,
                                multiplier: 2
                            });

                            if (index > 0) {
                                pdf.addPage([pageMmWidth, pageMmHeight]);
                            } else {
                                pdf.setPage(1);
                            }

                            pdf.addImage(dataURL, 'PNG', 0, 0, pageMmWidth, pageMmHeight, undefined, 'FAST');
                            tempCanvas.dispose();

                            pagesProcessed++;
                            if (pagesProcessed === pages.length) {
                                pdf.save('order-' + orderId + '-item-' + (itemIndex + 1) + '.pdf');
                                setStatus('Export complete', 'PDF downloaded successfully.');
                                done();
                            }
                        });
                    } catch (e) {
                        console.error('Error exporting page:', e);
                        pagesProcessed++;
                        if (pagesProcessed === pages.length) {
                            setStatus('Export completed with errors', 'Some pages may be missing.');
                            done();
                        }
                    }
                }, index * 150);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runExport);
        } else {
            runExport();
        }
    })();
    </script>
</body>
</html>
