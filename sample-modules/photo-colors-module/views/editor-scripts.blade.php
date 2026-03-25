<script>
/**
 * Photo Colors module - color adjustment panel for images in multi-page design editor.
 * Shows when an image is selected. Uses Fabric.js image filters.
 */
(function() {
    let photoColorsCurrentObj = null;

    window.initPhotoColorsPanel = function(obj) {
        if (!obj || obj.type !== 'image') return;
        photoColorsCurrentObj = obj;

        const panel = document.getElementById('photoColorsPanel');
        if (!panel) return;
        panel.style.display = 'block';

        // Read current filter values from object
        const filters = obj.filters || [];
        let brightness = 0, contrast = 0, saturation = 1, hue = 0, grayscale = false, sepia = 0;

        filters.forEach(function(f) {
            var t = f.type || (f.constructor && f.constructor.name);
            if (t === 'Brightness') brightness = ((f.brightness != null ? f.brightness : 0) + 1) * 50;
            else if (t === 'Contrast') contrast = ((f.contrast != null ? f.contrast : 0) + 1) * 50;
            else if (t === 'Saturation') saturation = ((f.saturation != null ? f.saturation : 0) + 1) * 100;
            else if (t === 'HueRotation') hue = f.rotation || 0;
            else if (t === 'Grayscale') grayscale = true;
            else if (t === 'Sepia') sepia = (f.amount || 0) * 100;
        });

        // Update UI
        const gs = document.getElementById('photoColorGrayscale');
        if (gs) gs.checked = grayscale;
        const b = document.getElementById('photoColorBrightness');
        if (b) { b.value = Math.round(brightness); document.getElementById('photoColorBrightnessVal').textContent = Math.round((brightness - 50) * 2); }
        const c = document.getElementById('photoColorContrast');
        if (c) { c.value = Math.round(contrast); document.getElementById('photoColorContrastVal').textContent = Math.round((contrast - 50) * 2); }
        const s = document.getElementById('photoColorSaturation');
        if (s) { s.value = Math.round(saturation); document.getElementById('photoColorSaturationVal').textContent = Math.round(saturation) + '%'; }
        const h = document.getElementById('photoColorHue');
        if (h) { h.value = Math.round(hue); document.getElementById('photoColorHueVal').textContent = Math.round(hue) + '°'; }
        const sep = document.getElementById('photoColorSepia');
        if (sep) { sep.value = Math.round(sepia); document.getElementById('photoColorSepiaVal').textContent = Math.round(sepia) + '%'; }
    };

    function getPhotoColorsObj() {
        return photoColorsCurrentObj || (typeof currentObject !== 'undefined' ? currentObject : null);
    }

    function applyPhotoColorFilters() {
        const obj = getPhotoColorsObj();
        if (!obj || obj.type !== 'image') return;
        if (typeof canvas === 'undefined') return;

        const grayscale = document.getElementById('photoColorGrayscale')?.checked || false;
        const brightnessVal = (parseFloat(document.getElementById('photoColorBrightness')?.value || 50) - 50) / 50;
        const contrastVal = (parseFloat(document.getElementById('photoColorContrast')?.value || 50) - 50) / 50;
        const saturationVal = (parseFloat(document.getElementById('photoColorSaturation')?.value || 100) / 100) - 1;
        const hueVal = parseFloat(document.getElementById('photoColorHue')?.value || 0);
        const sepiaVal = parseFloat(document.getElementById('photoColorSepia')?.value || 0) / 100;

        obj.filters = obj.filters || [];
        var filterTypes = ['Brightness','Contrast','Saturation','HueRotation','Grayscale','Sepia'];
        obj.filters = obj.filters.filter(function(f) {
            var t = f.type || (f.constructor && f.constructor.name);
            return filterTypes.indexOf(t) === -1;
        });

        if (grayscale && typeof fabric !== 'undefined' && fabric.Image && fabric.Image.filters && fabric.Image.filters.Grayscale) {
            obj.filters.push(new fabric.Image.filters.Grayscale());
        }
        if (brightnessVal !== 0 && fabric.Image.filters.Brightness) {
            obj.filters.push(new fabric.Image.filters.Brightness({ brightness: brightnessVal }));
        }
        if (contrastVal !== 0 && fabric.Image.filters.Contrast) {
            obj.filters.push(new fabric.Image.filters.Contrast({ contrast: contrastVal }));
        }
        if (saturationVal !== 0 && fabric.Image.filters.Saturation) {
            obj.filters.push(new fabric.Image.filters.Saturation({ saturation: saturationVal }));
        }
        if (hueVal !== 0 && fabric.Image.filters.HueRotation) {
            obj.filters.push(new fabric.Image.filters.HueRotation({ rotation: hueVal }));
        }
        if (sepiaVal > 0 && fabric.Image.filters.Sepia) {
            obj.filters.push(new fabric.Image.filters.Sepia({ amount: sepiaVal }));
        }

        obj.applyFilters();
        canvas.requestRenderAll();
        if (typeof designModified !== 'undefined') designModified = true;
    }

    window.photoColorsApplyGrayscale = function(checked) {
        applyPhotoColorFilters();
    };

    window.photoColorsApplyBrightness = function(val) {
        document.getElementById('photoColorBrightnessVal').textContent = Math.round((parseFloat(val) - 50) * 2);
        applyPhotoColorFilters();
    };

    window.photoColorsApplyContrast = function(val) {
        document.getElementById('photoColorContrastVal').textContent = Math.round((parseFloat(val) - 50) * 2);
        applyPhotoColorFilters();
    };

    window.photoColorsApplySaturation = function(val) {
        document.getElementById('photoColorSaturationVal').textContent = Math.round(val) + '%';
        applyPhotoColorFilters();
    };

    window.photoColorsApplyHue = function(val) {
        document.getElementById('photoColorHueVal').textContent = Math.round(val) + '°';
        applyPhotoColorFilters();
    };

    window.photoColorsApplySepia = function(val) {
        document.getElementById('photoColorSepiaVal').textContent = Math.round(val) + '%';
        applyPhotoColorFilters();
    };

    window.photoColorsReset = function() {
        document.getElementById('photoColorGrayscale').checked = false;
        document.getElementById('photoColorBrightness').value = 50;
        document.getElementById('photoColorBrightnessVal').textContent = '0';
        document.getElementById('photoColorContrast').value = 50;
        document.getElementById('photoColorContrastVal').textContent = '0';
        document.getElementById('photoColorSaturation').value = 100;
        document.getElementById('photoColorSaturationVal').textContent = '100%';
        document.getElementById('photoColorHue').value = 0;
        document.getElementById('photoColorHueVal').textContent = '0°';
        document.getElementById('photoColorSepia').value = 0;
        document.getElementById('photoColorSepiaVal').textContent = '0%';

        const obj = getPhotoColorsObj();
        if (obj && obj.type === 'image') {
            obj.filters = obj.filters || [];
            var filterTypes = ['Brightness','Contrast','Saturation','HueRotation','Grayscale','Sepia'];
            obj.filters = obj.filters.filter(function(f) {
                var t = f.type || (f.constructor && f.constructor.name);
                return filterTypes.indexOf(t) === -1;
            });
            obj.applyFilters();
            if (typeof canvas !== 'undefined') canvas.requestRenderAll();
            if (typeof designModified !== 'undefined') designModified = true;
        }
    };

    // Hide panel when selection is cleared (called from main editor)
    if (typeof document !== 'undefined') {
        document.addEventListener('DOMContentLoaded', function() {
            const panel = document.getElementById('photoColorsPanel');
            if (panel) {
                const observer = new MutationObserver(function() {
                    const noSel = document.getElementById('noSelectionText');
                    if (noSel && noSel.style.display !== 'none') {
                        panel.style.display = 'none';
                    }
                });
                const props = document.getElementById('elementProperties');
                if (props) observer.observe(props, { attributes: true, attributeFilter: ['style'] });
            }
        });
    }
})();
</script>
