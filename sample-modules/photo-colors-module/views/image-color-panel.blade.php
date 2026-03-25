<div id="photoColorsPanel" class="panel-section" style="display: none;">
    <div class="panel-title">Photo Color Adjustments</div>
    <p style="color: #94a3b8; font-size: 0.75rem; margin-bottom: 1rem;">Adjust color, brightness, and more for the selected image.</p>

    <!-- Black & White -->
    <div class="panel-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <label class="form-label" style="font-size: 0.75rem; margin: 0;">Black & White</label>
            <label class="form-check form-switch" style="margin: 0;">
                <input type="checkbox" id="photoColorGrayscale" class="form-check-input" onchange="photoColorsApplyGrayscale(this.checked)">
            </label>
        </div>
    </div>

    <!-- Brightness -->
    <div class="panel-section">
        <label class="form-label" style="font-size: 0.75rem;">Brightness</label>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <input type="range" id="photoColorBrightness" class="form-range" min="0" max="100" value="50" oninput="photoColorsApplyBrightness(this.value)" style="flex: 1;">
            <span id="photoColorBrightnessVal" style="font-size: 0.7rem; color: #64748b; min-width: 28px;">0</span>
        </div>
    </div>

    <!-- Contrast -->
    <div class="panel-section">
        <label class="form-label" style="font-size: 0.75rem;">Contrast</label>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <input type="range" id="photoColorContrast" class="form-range" min="0" max="100" value="50" oninput="photoColorsApplyContrast(this.value)" style="flex: 1;">
            <span id="photoColorContrastVal" style="font-size: 0.7rem; color: #64748b; min-width: 28px;">0</span>
        </div>
    </div>

    <!-- Saturation -->
    <div class="panel-section">
        <label class="form-label" style="font-size: 0.75rem;">Saturation</label>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <input type="range" id="photoColorSaturation" class="form-range" min="0" max="200" value="100" oninput="photoColorsApplySaturation(this.value)" style="flex: 1;">
            <span id="photoColorSaturationVal" style="font-size: 0.7rem; color: #64748b; min-width: 32px;">100%</span>
        </div>
    </div>

    <!-- Hue -->
    <div class="panel-section">
        <label class="form-label" style="font-size: 0.75rem;">Hue (Color Shift)</label>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <input type="range" id="photoColorHue" class="form-range" min="0" max="360" value="0" oninput="photoColorsApplyHue(this.value)" style="flex: 1;">
            <span id="photoColorHueVal" style="font-size: 0.7rem; color: #64748b; min-width: 36px;">0°</span>
        </div>
    </div>

    <!-- Color Balance (simulated via RGB curves - use brightness/contrast per channel or Sepia) -->
    <div class="panel-section">
        <label class="form-label" style="font-size: 0.75rem;">Sepia</label>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <input type="range" id="photoColorSepia" class="form-range" min="0" max="100" value="0" oninput="photoColorsApplySepia(this.value)" style="flex: 1;">
            <span id="photoColorSepiaVal" style="font-size: 0.7rem; color: #64748b; min-width: 32px;">0%</span>
        </div>
    </div>

    <!-- Reset -->
    <div class="panel-section">
        <button type="button" class="toolbar-btn" onclick="photoColorsReset()" style="width: 100%; padding: 0.5rem; justify-content: center; gap: 0.5rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
            <i class="fas fa-undo"></i>
            <span>Reset All</span>
        </button>
    </div>
</div>
