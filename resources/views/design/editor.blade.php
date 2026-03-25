@extends('layouts.app')

@section('title', 'Design Tool')
@section('page-title', 'Design Tool')

@push('styles')
<style>
    .design-editor {
        height: calc(100vh - 120px);
        display: flex;
        flex-direction: column;
        background: #f1f5f9;
    }

    .design-toolbar {
        background: white;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .toolbar-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .toolbar-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .toolbar-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        font-size: 0.875rem;
        border: none;
    }

    .toolbar-btn:hover {
        background: #f8fafc;
        border: 1px solid #6366f1;
    }

    .toolbar-btn.active {
        background: #6366f1;
        color: white;
        border: 1px solid #6366f1;
    }

    .design-workspace {
        flex: 1;
        display: flex;
        overflow: hidden;
    }

    .design-sidebar {
        width: 280px;
        background: white;
        border-right: 1px solid #e2e8f0;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .design-canvas-container {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        overflow: auto;
        background: #e2e8f0;
        background-image:
            linear-gradient(45deg, #f1f5f9 25%, transparent 25%),
            linear-gradient(-45deg, #f1f5f9 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, #f1f5f9 75%),
            linear-gradient(-45deg, transparent 75%, #f1f5f9 75%);
        background-size: 20px 20px;
        background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
    }

    .canvas-wrapper {
        background: white;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        padding: 20px;
        border-radius: 4px;
    }

    .properties-panel {
        width: 300px;
        background: white;
        border-left: 1px solid #e2e8f0;
        overflow-y: auto;
        padding: 1rem;
    }

    .panel-section {
        margin-bottom: 1.5rem;
    }

    .panel-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .element-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .element-item {
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .element-item:hover {
        background: #f8fafc;
        border-color: #6366f1;
    }

    .element-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        border-radius: 6px;
        color: #6366f1;
    }

    .color-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 0.5rem;
    }

    .color-swatch {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        cursor: pointer;
        border: 2px solid #e2e8f0;
        transition: transform 0.2s;
    }

    .color-swatch:hover {
        transform: scale(1.1);
    }

    .sidebar-tab-btn {
        flex: 1;
        padding: 0.5rem;
        border: none;
        background: transparent;
        cursor: pointer;
        border-radius: 4px;
        font-size: 0.75rem;
        color: #64748b;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
    }

    .sidebar-tab-btn:hover {
        background: #f1f5f9;
        color: #475569;
    }

    .sidebar-tab-btn.active {
        background: #6366f1;
        color: white;
    }

    .sidebar-tab-content {
        flex: 1;
        overflow-y: auto;
    }

    .layers-list {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .layer-item {
        padding: 0.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
    }

    .layer-item:hover {
        background: #f8fafc;
        border-color: #6366f1;
    }

    .layer-item.selected {
        background: #eef2ff;
        border-color: #6366f1;
        border-width: 2px;
    }

    .layer-item.locked {
        opacity: 0.6;
    }

    .layer-icon {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6366f1;
        font-size: 0.875rem;
    }

    .layer-info {
        flex: 1;
        min-width: 0;
    }

    .layer-name {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .layer-type {
        font-size: 0.7rem;
        color: #64748b;
    }

    .layer-actions {
        display: flex;
        gap: 0.25rem;
        align-items: center;
    }

    .layer-action-btn {
        width: 24px;
        height: 24px;
        border: none;
        background: transparent;
        cursor: pointer;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 0.75rem;
        transition: all 0.2s;
    }

    .layer-action-btn:hover {
        background: #f1f5f9;
        color: #6366f1;
    }

    .layer-order-controls {
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
        margin-right: 0.25rem;
    }

    .layer-order-btn {
        width: 20px;
        height: 14px;
        border: none;
        background: transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 0.6rem;
        padding: 0;
        line-height: 1;
    }

    .layer-order-btn:hover {
        color: #6366f1;
        background: #f1f5f9;
    }

    .layer-visibility-btn {
        color: #10b981;
    }

    .layer-visibility-btn.hidden {
        color: #94a3b8;
    }
</style>
@endpush

@section('content')
<div class="design-editor">
    <!-- Toolbar -->
    <div class="design-toolbar">
        <div class="toolbar-left">
            <a href="{{ route('design.index') }}" class="toolbar-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Back</span>
            </a>
            <button class="toolbar-btn" onclick="saveDesign()">
                <i class="fas fa-save"></i>
                <span>Save</span>
            </button>
            <button class="toolbar-btn" onclick="exportDesign()">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </button>
            <button class="toolbar-btn" onclick="deleteSelected()">
                <i class="fas fa-trash"></i>
                <span>Delete</span>
            </button>
        </div>
        <div class="toolbar-right">
            <button class="toolbar-btn" onclick="undo()">
                <i class="fas fa-undo"></i>
            </button>
            <button class="toolbar-btn" onclick="redo()">
                <i class="fas fa-redo"></i>
            </button>
            <button class="toolbar-btn" onclick="zoomOut()">
                <i class="fas fa-search-minus"></i>
            </button>
            <span id="zoomLevel" style="padding: 0 0.5rem; font-size: 0.875rem;">100%</span>
            <button class="toolbar-btn" onclick="zoomIn()">
                <i class="fas fa-search-plus"></i>
            </button>
        </div>
    </div>

    <!-- Workspace -->
    <div class="design-workspace">
        <!-- Left Sidebar - Elements -->
        <div class="design-sidebar">
            <div style="padding: 1rem; border-bottom: 1px solid #e2e8f0; display: flex; gap: 0.5rem;">
                <button class="sidebar-tab-btn active" onclick="switchSidebarTab('elements')" id="tabElements">
                    <i class="fas fa-shapes"></i> Elements
                </button>
                <button class="sidebar-tab-btn" onclick="switchSidebarTab('layers')" id="tabLayers">
                    <i class="fas fa-layer-group"></i> Layers
                </button>
            </div>

            <!-- Elements Tab -->
            <div id="elementsTab" class="sidebar-tab-content">

            <div style="padding: 1rem;">
                <div class="panel-section">
                    <div class="panel-title">Text</div>
                    <div class="element-list">
                        <div class="element-item" onclick="addText('Heading')">
                            <div class="element-icon"><i class="fas fa-heading"></i></div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Heading</div>
                                <div style="font-size: 0.75rem; color: #64748b;">Large text</div>
                            </div>
                        </div>
                        <div class="element-item" onclick="addText('Subheading')">
                            <div class="element-icon"><i class="fas fa-text-height"></i></div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Subheading</div>
                                <div style="font-size: 0.75rem; color: #64748b;">Medium text</div>
                            </div>
                        </div>
                        <div class="element-item" onclick="addText('Body')">
                            <div class="element-icon"><i class="fas fa-font"></i></div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Body Text</div>
                                <div style="font-size: 0.75rem; color: #64748b;">Regular text</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-section">
                    <div class="panel-title">Shapes</div>
                    <div class="element-list">
                        <div class="element-item" onclick="addShape('rect')">
                            <div class="element-icon"><i class="fas fa-square"></i></div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Rectangle</div>
                            </div>
                        </div>
                        <div class="element-item" onclick="addShape('circle')">
                            <div class="element-icon"><i class="fas fa-circle"></i></div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Circle</div>
                            </div>
                        </div>
                        <div class="element-item" onclick="addShape('line')">
                            <div class="element-icon"><i class="fas fa-minus"></i></div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Line</div>
                            </div>
                        </div>
                        <div class="element-item" onclick="addShape('triangle')">
                            <div class="element-icon"><i class="fas fa-play"></i></div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Triangle</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-section">
                    <div class="panel-title">Upload</div>
                    <div class="element-list">
                        <div class="element-item" onclick="document.getElementById('imageUpload').click()">
                            <div class="element-icon"><i class="fas fa-image"></i></div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Upload Image</div>
                                <div style="font-size: 0.75rem; color: #64748b;">JPG, PNG, GIF</div>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="imageUpload" accept="image/*" style="display: none;" onchange="handleImageUpload(event)">
                </div>
            </div>
            </div>

            <!-- Layers Tab -->
            <div id="layersTab" class="sidebar-tab-content" style="display: none;">
                <div style="padding: 1rem;">
                    <div class="panel-section">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="panel-title" style="margin: 0;">Layers</div>
                            <button class="btn btn-sm btn-outline-secondary" onclick="refreshLayers()" title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div id="layersList" class="layers-list">
                            <p class="text-muted small text-center" style="padding: 1rem 0;">No layers yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Canvas -->
        <div class="design-canvas-container" id="canvasContainer">
            <div class="canvas-wrapper">
                <canvas id="fabricCanvas"></canvas>
            </div>
        </div>

        <!-- Right Sidebar - Properties -->
        <div class="properties-panel" id="propertiesPanel">
            <div class="panel-section">
                <div class="panel-title">Properties</div>
                <p style="color: #94a3b8; font-size: 0.875rem;" id="noSelectionText">Select an element to edit properties</p>
            </div>

            <div id="elementProperties" style="display: none;">
                <div class="panel-section">
                    <div class="panel-title">Text Properties</div>
                    <div class="mb-2" id="textProps">
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Text Content</label>
                            <textarea id="propText" class="form-control form-control-sm" rows="3" onchange="updateTextContent(this.value)"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Font Size</label>
                            <input type="number" id="propFontSize" class="form-control form-control-sm" min="8" max="200" onchange="updateProperty('fontSize', this.value)">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Font Family</label>
                            <select id="propFontFamily" class="form-select form-select-sm" onchange="updateProperty('fontFamily', this.value)">
                                <option value="Arial">Arial</option>
                                <option value="Helvetica">Helvetica</option>
                                <option value="Times New Roman">Times New Roman</option>
                                <option value="Courier New">Courier New</option>
                                <option value="Verdana">Verdana</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Palatino">Palatino</option>
                                <option value="Garamond">Garamond</option>
                                <option value="Comic Sans MS">Comic Sans MS</option>
                                <option value="Impact">Impact</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Font Weight</label>
                            <select id="propFontWeight" class="form-select form-select-sm" onchange="updateProperty('fontWeight', this.value)">
                                <option value="normal">Normal</option>
                                <option value="bold">Bold</option>
                                <option value="300">Light</option>
                                <option value="600">Semi Bold</option>
                                <option value="700">Bold</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Text Align</label>
                            <select id="propTextAlign" class="form-select form-select-sm" onchange="updateProperty('textAlign', this.value)">
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                                <option value="justify">Justify</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="propWarpText" onchange="toggleWarpText(this.checked)">
                                <label class="form-check-label" style="font-size: 0.75rem;" for="propWarpText">
                                    Warp Text
                                </label>
                            </div>
                        </div>
                        <div id="warpControls" style="display: none;">
                            <div class="mb-2">
                                <label class="form-label" style="font-size: 0.75rem;">Warp Amount</label>
                                <input type="range" id="propWarpAmount" class="form-range" min="-100" max="100" value="0" step="5" oninput="updateWarpAmount(this.value)">
                                <div class="d-flex justify-content-between" style="font-size: 0.7rem; color: #64748b;">
                                    <span>Down</span>
                                    <span id="warpValue">0</span>
                                    <span>Up</span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label" style="font-size: 0.75rem;">Warp Style</label>
                                <select id="propWarpStyle" class="form-select form-select-sm" onchange="updateWarpStyle(this.value)">
                                    <option value="arc">Arc</option>
                                    <option value="arch">Arch</option>
                                    <option value="wave">Wave</option>
                                    <option value="fish">Fish</option>
                                    <option value="rise">Rise</option>
                                    <option value="fisheye">Fisheye</option>
                                    <option value="inflate">Inflate</option>
                                    <option value="squeeze">Squeeze</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-section">
                    <div class="panel-title">Position & Size</div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem;">X</label>
                        <input type="number" id="propX" class="form-control form-control-sm" onchange="updateProperty('left', this.value)">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem;">Y</label>
                        <input type="number" id="propY" class="form-control form-control-sm" onchange="updateProperty('top', this.value)">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem;">Width</label>
                        <input type="number" id="propWidth" class="form-control form-control-sm" onchange="updateProperty('width', this.value)">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem;">Height</label>
                        <input type="number" id="propHeight" class="form-control form-control-sm" onchange="updateProperty('height', this.value)">
                    </div>
                </div>

                <div class="panel-section">
                    <div class="panel-title">Color</div>
                    <div class="color-grid">
                        <div class="color-swatch" style="background: #000000;" onclick="setColor('#000000')"></div>
                        <div class="color-swatch" style="background: #ffffff; border: 2px solid #000;" onclick="setColor('#ffffff')"></div>
                        <div class="color-swatch" style="background: #ef4444;" onclick="setColor('#ef4444')"></div>
                        <div class="color-swatch" style="background: #10b981;" onclick="setColor('#10b981')"></div>
                        <div class="color-swatch" style="background: #3b82f6;" onclick="setColor('#3b82f6')"></div>
                        <div class="color-swatch" style="background: #f59e0b;" onclick="setColor('#f59e0b')"></div>
                        <div class="color-swatch" style="background: #8b5cf6;" onclick="setColor('#8b5cf6')"></div>
                        <div class="color-swatch" style="background: #ec4899;" onclick="setColor('#ec4899')"></div>
                        <div class="color-swatch" style="background: #6366f1;" onclick="setColor('#6366f1')"></div>
                        <div class="color-swatch" style="background: #14b8a6;" onclick="setColor('#14b8a6')"></div>
                        <div class="color-swatch" style="background: #f97316;" onclick="setColor('#f97316')"></div>
                        <div class="color-swatch" style="background: #64748b;" onclick="setColor('#64748b')"></div>
                    </div>
                    <input type="color" id="colorPicker" class="form-control form-control-color mt-2" onchange="setColor(this.value)">
                </div>

                <div class="panel-section">
                    <div class="panel-title">Layer</div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" onclick="sendToBack()">
                            <i class="fas fa-arrow-down"></i> Back
                        </button>
                        <button class="btn btn-sm btn-outline-secondary w-100" onclick="bringToFront()">
                            <i class="fas fa-arrow-up"></i> Front
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
<script>
    let canvas;
    let zoomLevel = 100;
    let currentObject = null;
    let history = [];
    let historyStep = -1;

    // Initialize Fabric.js canvas
    document.addEventListener('DOMContentLoaded', function() {
        canvas = new fabric.Canvas('fabricCanvas', {
            width: 800,
            height: 1000,
            backgroundColor: '#ffffff',
            selection: true,
            preserveObjectStacking: true
        });

        // Save state for undo/redo
        function saveState() {
            historyStep++;
            if (historyStep < history.length) {
                history = history.slice(0, historyStep);
            }
            history.push(JSON.stringify(canvas.toJSON()));
        }

        // Undo/Redo functions
        window.undo = function() {
            if (historyStep > 0) {
                historyStep--;
                canvas.loadFromJSON(history[historyStep], function() {
                    canvas.renderAll();
                });
            }
        };

        window.redo = function() {
            if (historyStep < history.length - 1) {
                historyStep++;
                canvas.loadFromJSON(history[historyStep], function() {
                    canvas.renderAll();
                });
            }
        };

        // Save state on changes
        canvas.on('object:added', saveState);
        canvas.on('object:removed', saveState);
        canvas.on('object:modified', saveState);
        canvas.on('path:created', saveState);

        // Initialize history
        saveState();

        // Update properties panel when object is selected
        canvas.on('selection:created', function(e) {
            updatePropertiesPanel(e.selected[0]);
            refreshLayers();
        });

        canvas.on('selection:updated', function(e) {
            updatePropertiesPanel(e.selected[0]);
            refreshLayers();
        });

        canvas.on('selection:cleared', function() {
            hidePropertiesPanel();
            refreshLayers();
        });

        // Update properties in real-time when object is modified
        canvas.on('object:modified', function(e) {
            if (e.target === currentObject) {
                updatePropertiesPanel(e.target);
            }
        });

        // Update layers when objects are added/removed
        canvas.on('object:added', function() {
            refreshLayers();
        });

        canvas.on('object:removed', function() {
            refreshLayers();
        });

        // Initial layers refresh
        refreshLayers();

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Delete key
            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    deleteSelected();
                }
            }
            // Ctrl+Z for undo
            if (e.ctrlKey && e.key === 'z' && !e.shiftKey) {
                e.preventDefault();
                undo();
            }
            // Ctrl+Shift+Z or Ctrl+Y for redo
            if ((e.ctrlKey && e.shiftKey && e.key === 'Z') || (e.ctrlKey && e.key === 'y')) {
                e.preventDefault();
                redo();
            }
        });

        // Load design if ID is provided
        const urlParams = new URLSearchParams(window.location.search);
        const loadId = urlParams.get('load');
        if (loadId) {
            loadDesign(loadId);
        }
    });

    function addText(type) {
        let text, fontSize, fontWeight;

        switch(type) {
            case 'Heading':
                text = 'Heading Text';
                fontSize = 48;
                fontWeight = 'bold';
                break;
            case 'Subheading':
                text = 'Subheading Text';
                fontSize = 32;
                fontWeight = '600';
                break;
            default:
                text = 'Body text';
                fontSize = 16;
                fontWeight = 'normal';
        }

        const fabricText = new fabric.Text(text, {
            left: 100,
            top: 100,
            fontSize: fontSize,
            fontFamily: 'Arial',
            fontWeight: fontWeight,
            fill: '#000000',
            textAlign: 'left'
        });

        canvas.add(fabricText);
        canvas.setActiveObject(fabricText);
        canvas.renderAll();
    }

    function addShape(shapeType) {
        let shape;
        const left = 100;
        const top = 100;

        switch(shapeType) {
            case 'rect':
                shape = new fabric.Rect({
                    left: left,
                    top: top,
                    width: 150,
                    height: 150,
                    fill: '#6366f1',
                    stroke: '',
                    strokeWidth: 0
                });
                break;
            case 'circle':
                shape = new fabric.Circle({
                    left: left,
                    top: top,
                    radius: 75,
                    fill: '#6366f1',
                    stroke: '',
                    strokeWidth: 0
                });
                break;
            case 'line':
                shape = new fabric.Line([0, 0, 200, 0], {
                    left: left,
                    top: top,
                    stroke: '#6366f1',
                    strokeWidth: 4
                });
                break;
            case 'triangle':
                shape = new fabric.Triangle({
                    left: left,
                    top: top,
                    width: 150,
                    height: 150,
                    fill: '#6366f1',
                    stroke: '',
                    strokeWidth: 0
                });
                break;
        }

        canvas.add(shape);
        canvas.setActiveObject(shape);
        canvas.renderAll();
    }

    function handleImageUpload(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                fabric.Image.fromURL(e.target.result, function(img) {
                    img.set({
                        left: 100,
                        top: 100,
                        scaleX: 0.5,
                        scaleY: 0.5
                    });
                    canvas.add(img);
                    canvas.setActiveObject(img);
                    canvas.renderAll();
                });
            };
            reader.readAsDataURL(file);
        }
    }

    function updatePropertiesPanel(obj) {
        if (!obj) return;

        currentObject = obj;
        document.getElementById('elementProperties').style.display = 'block';
        document.getElementById('noSelectionText').style.display = 'none';

        // Update position and size
        document.getElementById('propX').value = Math.round(obj.left);
        document.getElementById('propY').value = Math.round(obj.top);
        document.getElementById('propWidth').value = Math.round(obj.width * obj.scaleX);
        document.getElementById('propHeight').value = Math.round(obj.height * obj.scaleY);

        // Update color
        if (obj.fill) {
            document.getElementById('colorPicker').value = rgbToHex(obj.fill);
        }

        // Update text properties if it's a text object
        if (obj.type === 'text' || obj.type === 'textbox' || obj.type === 'i-text') {
            document.getElementById('textProps').style.display = 'block';
            document.getElementById('propText').value = obj.text;
            document.getElementById('propFontSize').value = obj.fontSize;
            document.getElementById('propFontFamily').value = obj.fontFamily || 'Arial';
            document.getElementById('propFontWeight').value = obj.fontWeight || 'normal';
            document.getElementById('propTextAlign').value = obj.textAlign || 'left';

            // Update warp text controls
            const isWarped = obj.warped || false;
            document.getElementById('propWarpText').checked = isWarped;
            document.getElementById('warpControls').style.display = isWarped ? 'block' : 'none';

            if (isWarped) {
                document.getElementById('propWarpAmount').value = obj.warpAmount || 0;
                document.getElementById('warpValue').textContent = obj.warpAmount || 0;
                document.getElementById('propWarpStyle').value = obj.warpStyle || 'arc';
            }
        } else {
            document.getElementById('textProps').style.display = 'none';
        }
    }

    function hidePropertiesPanel() {
        document.getElementById('elementProperties').style.display = 'none';
        document.getElementById('noSelectionText').style.display = 'block';
        currentObject = null;
    }

    function updateProperty(prop, value) {
        if (!currentObject) return;

        if (prop === 'width' || prop === 'height') {
            const scale = prop === 'width' ? value / currentObject.width : value / currentObject.height;
            if (prop === 'width') {
                currentObject.set('scaleX', scale);
            } else {
                currentObject.set('scaleY', scale);
            }
        } else {
            currentObject.set(prop, parseFloat(value) || value);
        }

        canvas.renderAll();
    }

    function updateTextContent(value) {
        if (!currentObject || currentObject.type !== 'text' && currentObject.type !== 'textbox' && currentObject.type !== 'i-text') return;
        currentObject.set('text', value);
        canvas.renderAll();
    }

    function setColor(color) {
        if (!currentObject) return;

        if (currentObject.type === 'text' || currentObject.type === 'textbox' || currentObject.type === 'i-text') {
            currentObject.set('fill', color);
        } else {
            currentObject.set('fill', color);
        }

        canvas.renderAll();
    }

    function deleteSelected() {
        const activeObjects = canvas.getActiveObjects();
        if (activeObjects.length) {
            activeObjects.forEach(function(obj) {
                canvas.remove(obj);
            });
            canvas.discardActiveObject();
            canvas.renderAll();
            hidePropertiesPanel();
        }
    }

    function bringToFront() {
        if (currentObject) {
            canvas.bringToFront(currentObject);
            canvas.renderAll();
        }
    }

    function sendToBack() {
        if (currentObject) {
            canvas.sendToBack(currentObject);
            canvas.renderAll();
        }
    }

    function zoomIn() {
        zoomLevel = Math.min(zoomLevel + 10, 200);
        updateZoom();
    }

    function zoomOut() {
        zoomLevel = Math.max(zoomLevel - 10, 50);
        updateZoom();
    }

    function updateZoom() {
        const container = document.getElementById('canvasContainer');
        const wrapper = container.querySelector('.canvas-wrapper');
        wrapper.style.transform = `scale(${zoomLevel / 100})`;
        wrapper.style.transformOrigin = 'center';
        document.getElementById('zoomLevel').textContent = zoomLevel + '%';
    }

    function rgbToHex(rgb) {
        if (rgb.startsWith('#')) return rgb;
        if (rgb.startsWith('rgb')) {
            const values = rgb.match(/\d+/g);
            return '#' + values.map(x => {
                const hex = parseInt(x).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            }).join('');
        }
        return rgb;
    }

    function saveDesign() {
        const name = prompt('Enter design name:');
        if (!name) return;

        // Export canvas as JSON
        const json = JSON.stringify(canvas.toJSON());

        // Create thumbnail
        canvas.renderAll();
        const dataURL = canvas.toDataURL({
            format: 'png',
            quality: 0.8,
            multiplier: 0.5
        });

        fetch('{{ route("design.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                name: name,
                design_data: json,
                thumbnail: dataURL
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Design saved successfully!');
            } else {
                alert('Failed to save design');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving design');
        });
    }

    function exportDesign() {
        canvas.renderAll();
        const dataURL = canvas.toDataURL({
            format: 'png',
            quality: 1.0
        });

        const link = document.createElement('a');
        link.download = 'design.png';
        link.href = dataURL;
        link.click();
    }

    // Layers Panel Functions
    function switchSidebarTab(tab) {
        // Update tab buttons
        document.getElementById('tabElements').classList.toggle('active', tab === 'elements');
        document.getElementById('tabLayers').classList.toggle('active', tab === 'layers');

        // Show/hide tab content
        document.getElementById('elementsTab').style.display = tab === 'elements' ? 'block' : 'none';
        document.getElementById('layersTab').style.display = tab === 'layers' ? 'block' : 'none';

        if (tab === 'layers') {
            refreshLayers();
        }
    }

    function refreshLayers() {
        const layersList = document.getElementById('layersList');
        const objects = canvas.getObjects();

        if (objects.length === 0) {
            layersList.innerHTML = '<p class="text-muted small text-center" style="padding: 1rem 0;">No layers yet</p>';
            return;
        }

        layersList.innerHTML = '';

        // Reverse order to show top layer first (Fabric.js renders bottom to top)
        const reversedObjects = objects.slice().reverse();

        reversedObjects.forEach(function(obj, index) {
            const layerItem = createLayerItem(obj, objects.length - index - 1);
            layersList.appendChild(layerItem);
        });
    }

    function createLayerItem(obj, index) {
        const item = document.createElement('div');
        item.className = 'layer-item';
        item.dataset.objectIndex = index;

        // Check if object is selected
        const isSelected = canvas.getActiveObject() === obj ||
                          (canvas.getActiveObjects().length > 0 && canvas.getActiveObjects().includes(obj));
        if (isSelected) {
            item.classList.add('selected');
        }

        // Get object type icon
        const icon = getObjectIcon(obj.type);
        const typeName = getObjectTypeName(obj.type);

        // Get object name
        const name = obj.name || obj.text || typeName || 'Layer ' + (index + 1);
        const displayName = typeof name === 'string' ? (name.length > 20 ? name.substring(0, 20) + '...' : name) : typeName;

        item.innerHTML = `
            <div class="layer-icon">
                <i class="${icon}"></i>
            </div>
            <div class="layer-info">
                <div class="layer-name">${displayName}</div>
                <div class="layer-type">${typeName}</div>
            </div>
            <div class="layer-actions">
                <div class="layer-order-controls">
                    <button class="layer-order-btn" onclick="event.stopPropagation(); bringLayerToFront(${index})" title="Bring to Front">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button class="layer-order-btn" onclick="event.stopPropagation(); moveLayerUp(${index})" title="Move Up">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                    <button class="layer-order-btn" onclick="event.stopPropagation(); moveLayerDown(${index})" title="Move Down">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <button class="layer-order-btn" onclick="event.stopPropagation(); sendLayerToBack(${index})" title="Send to Back">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                </div>
                <button class="layer-action-btn layer-visibility-btn ${obj.visible === false ? 'hidden' : ''}"
                        onclick="event.stopPropagation(); toggleLayerVisibility(${index})" title="Toggle Visibility">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="layer-action-btn" onclick="event.stopPropagation(); deleteLayer(${index})" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        // Click to select
        item.addEventListener('click', function(e) {
            if (e.target.closest('.layer-actions')) return;
            selectLayer(index);
        });

        return item;
    }

    function getObjectIcon(type) {
        const icons = {
            'text': 'fas fa-font',
            'textbox': 'fas fa-font',
            'i-text': 'fas fa-font',
            'rect': 'fas fa-square',
            'circle': 'fas fa-circle',
            'triangle': 'fas fa-play',
            'line': 'fas fa-minus',
            'path': 'fas fa-draw-polygon',
            'image': 'fas fa-image',
            'group': 'fas fa-layer-group'
        };
        return icons[type] || 'fas fa-shapes';
    }

    function getObjectTypeName(type) {
        const names = {
            'text': 'Text',
            'textbox': 'Text',
            'i-text': 'Text',
            'rect': 'Rectangle',
            'circle': 'Circle',
            'triangle': 'Triangle',
            'line': 'Line',
            'path': 'Path',
            'image': 'Image',
            'group': 'Group'
        };
        return names[type] || 'Object';
    }

    function selectLayer(index) {
        const objects = canvas.getObjects();
        const obj = objects[index];
        if (obj) {
            canvas.setActiveObject(obj);
            canvas.renderAll();
            refreshLayers();
        }
    }

    function moveLayerUp(index) {
        const objects = canvas.getObjects();
        if (index < objects.length - 1) {
            const obj = objects[index];
            canvas.bringForward(obj);
            canvas.renderAll();
            refreshLayers();
            saveState();
        }
    }

    function moveLayerDown(index) {
        const objects = canvas.getObjects();
        if (index > 0) {
            const obj = objects[index];
            canvas.sendBackwards(obj);
            canvas.renderAll();
            refreshLayers();
            saveState();
        }
    }

    function bringLayerToFront(index) {
        const objects = canvas.getObjects();
        const obj = objects[index];
        if (obj) {
            canvas.bringToFront(obj);
            canvas.renderAll();
            refreshLayers();
            saveState();
        }
    }

    function sendLayerToBack(index) {
        const objects = canvas.getObjects();
        const obj = objects[index];
        if (obj) {
            canvas.sendToBack(obj);
            canvas.renderAll();
            refreshLayers();
            saveState();
        }
    }

    function toggleLayerVisibility(index) {
        const objects = canvas.getObjects();
        const obj = objects[index];
        if (obj) {
            obj.set('visible', !obj.visible);
            canvas.renderAll();
            refreshLayers();
            saveState();
        }
    }

    function deleteLayer(index) {
        const objects = canvas.getObjects();
        const obj = objects[index];
        if (obj && confirm('Are you sure you want to delete this layer?')) {
            canvas.remove(obj);
            canvas.renderAll();
            refreshLayers();
            hidePropertiesPanel();
            saveState();
        }
    }

    function loadDesign(designId) {
        fetch('{{ route("design.load", ":id") }}'.replace(':id', designId), {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.design) {
                // Fabric.js JSON is stored as string
                const designData = data.design.design_data;

                canvas.loadFromJSON(designData, function() {
                    // Restore warp effects for text objects
                    canvas.getObjects().forEach(function(obj) {
                        if ((obj.type === 'text' || obj.type === 'textbox' || obj.type === 'i-text') && obj.warped) {
                            applyWarpEffect(obj, obj.warpStyle || 'arc', obj.warpAmount || 0);
                        }
                    });
                    canvas.renderAll();
                    // Reset history after loading
                    history = [JSON.stringify(canvas.toJSON())];
                    historyStep = 0;
                });
            }
        })
        .catch(error => {
            console.error('Error loading design:', error);
            alert('Failed to load design');
        });
    }

    // Warp Text Functions
    function toggleWarpText(enabled) {
        if (!currentObject || (currentObject.type !== 'text' && currentObject.type !== 'textbox' && currentObject.type !== 'i-text')) {
            return;
        }

        document.getElementById('warpControls').style.display = enabled ? 'block' : 'none';

        if (enabled) {
            currentObject.warped = true;
            currentObject.warpAmount = currentObject.warpAmount || 0;
            currentObject.warpStyle = currentObject.warpStyle || 'arc';
            applyWarpEffect(currentObject, currentObject.warpStyle, currentObject.warpAmount);
        } else {
            currentObject.warped = false;
            removeWarpEffect(currentObject);
        }

        canvas.renderAll();
        saveState();
    }

    function updateWarpAmount(value) {
        if (!currentObject || !currentObject.warped) return;

        currentObject.warpAmount = parseInt(value);
        document.getElementById('warpValue').textContent = value;
        applyWarpEffect(currentObject, currentObject.warpStyle, currentObject.warpAmount);
        canvas.renderAll();
        saveState();
    }

    function updateWarpStyle(style) {
        if (!currentObject || !currentObject.warped) return;

        currentObject.warpStyle = style;
        applyWarpEffect(currentObject, style, currentObject.warpAmount);
        canvas.renderAll();
        saveState();
    }

    function applyWarpEffect(textObj, style, amount) {
        if (!textObj || (textObj.type !== 'text' && textObj.type !== 'textbox' && textObj.type !== 'i-text')) {
            return;
        }

        const normalizedAmount = amount / 100; // Normalize to -1 to 1

        // Store warp properties
        textObj.customWarp = {
            enabled: true,
            style: style,
            amount: amount,
            normalizedAmount: normalizedAmount
        };

        // Apply warp using text path (Fabric.js supports textPath property)
        // Create an SVG path based on warp style
        const width = textObj.width * textObj.scaleX;
        const height = textObj.height * textObj.scaleY;
        const pathString = generateWarpPath(style, normalizedAmount, width, height);

        // Use Fabric.js textPath feature if available, otherwise use custom rendering
        if (fabric.Text.prototype.setPath) {
            textObj.setPath(pathString);
        } else {
            // Custom implementation: create text along path
            createTextAlongPath(textObj, pathString, style, normalizedAmount);
        }

        canvas.renderAll();
    }

    function generateWarpPath(style, amount, width, height) {
        const steps = 100;
        const stepWidth = width / steps;
        let pathString = '';
        const maxOffset = height * 0.4 * Math.abs(amount);

        switch(style) {
            case 'arc':
                // Upward arc
                pathString = 'M 0,' + (height / 2 + maxOffset);
                for (let i = 0; i <= steps; i++) {
                    const x = i * stepWidth;
                    const t = i / steps;
                    const y = (height / 2 + maxOffset) - maxOffset * 2 * Math.sin(t * Math.PI);
                    pathString += ' L ' + x + ',' + y;
                }
                break;

            case 'arch':
                // Downward arch
                pathString = 'M 0,' + (height / 2 - maxOffset);
                for (let i = 0; i <= steps; i++) {
                    const x = i * stepWidth;
                    const t = i / steps;
                    const y = (height / 2 - maxOffset) + maxOffset * 2 * Math.sin(t * Math.PI);
                    pathString += ' L ' + x + ',' + y;
                }
                break;

            case 'wave':
                // Wave pattern
                pathString = 'M 0,' + (height / 2);
                for (let i = 0; i <= steps; i++) {
                    const x = i * stepWidth;
                    const y = height / 2 + maxOffset * Math.sin((i / steps) * Math.PI * 4);
                    pathString += ' L ' + x + ',' + y;
                }
                break;

            case 'fish':
            case 'fisheye':
                // Fisheye bulge
                pathString = 'M 0,' + (height / 2);
                for (let i = 0; i <= steps; i++) {
                    const x = i * stepWidth;
                    const t = i / steps;
                    const distance = Math.abs(0.5 - t) * 2;
                    const bulge = 1 - distance;
                    const y = height / 2 - maxOffset * bulge;
                    pathString += ' L ' + x + ',' + y;
                }
                break;

            case 'rise':
                // Rise from left to right
                pathString = 'M 0,' + (height / 2 + maxOffset);
                for (let i = 0; i <= steps; i++) {
                    const x = i * stepWidth;
                    const t = i / steps;
                    const y = (height / 2 + maxOffset) - maxOffset * 2 * t;
                    pathString += ' L ' + x + ',' + y;
                }
                break;

            case 'inflate':
                // Inflate (bulge in middle)
                pathString = 'M 0,' + (height / 2);
                for (let i = 0; i <= steps; i++) {
                    const x = i * stepWidth;
                    const t = i / steps;
                    const curve = Math.sin(t * Math.PI);
                    const y = height / 2 - maxOffset * curve;
                    pathString += ' L ' + x + ',' + y;
                }
                break;

            case 'squeeze':
                // Squeeze (pinch in middle)
                pathString = 'M 0,' + (height / 2);
                for (let i = 0; i <= steps; i++) {
                    const x = i * stepWidth;
                    const t = i / steps;
                    const curve = 1 - Math.sin(t * Math.PI);
                    const y = height / 2 + maxOffset * curve;
                    pathString += ' L ' + x + ',' + y;
                }
                break;

            default:
                pathString = 'M 0,' + (height / 2) + ' L ' + width + ',' + (height / 2);
        }

        return pathString;
    }

    function createTextAlongPath(textObj, pathString, style, amount) {
        // Since Fabric.js doesn't natively support text along path,
        // we'll use a visual distortion approach
        // Store the path for rendering
        textObj.warpPath = pathString;

        // Apply visual transform based on warp style
        // This creates a visual warping effect
        const width = textObj.width * textObj.scaleX;
        const height = textObj.height * textObj.scaleY;

        // Store original transform
        if (!textObj.originalTransform) {
            textObj.originalTransform = {
                scaleX: textObj.scaleX,
                scaleY: textObj.scaleY,
                skewX: textObj.skewX || 0,
                skewY: textObj.skewY || 0
            };
        }

        // Apply visual warp using skew or scale transforms
        // This is a simplified visual effect
        switch(style) {
            case 'arc':
            case 'arch':
                // Apply slight skew for arc effect
                textObj.set('skewY', amount * 0.1);
                break;
            case 'wave':
                // Apply wave-like distortion
                textObj.set('skewX', Math.sin(Date.now() / 1000) * amount * 0.05);
                break;
            default:
                // For other styles, use a combination of transforms
                break;
        }
    }

    function removeWarpEffect(textObj) {
        if (textObj.customWarp) {
            delete textObj.customWarp;
        }
        if (textObj.warpPath) {
            delete textObj.warpPath;
        }
        if (textObj.originalTransform) {
            textObj.set('scaleX', textObj.originalTransform.scaleX);
            textObj.set('scaleY', textObj.originalTransform.scaleY);
            textObj.set('skewX', textObj.originalTransform.skewX);
            textObj.set('skewY', textObj.originalTransform.skewY);
            delete textObj.originalTransform;
        }
        // Remove path if it was set
        if (textObj.path) {
            textObj.setPath(null);
        }
    }

    // Enhanced text rendering with warp support
    // Override the render method to draw text along path
    const originalRender = fabric.Text.prototype.render;
    fabric.Text.prototype.render = function(ctx) {
        if (this.customWarp && this.customWarp.enabled && this.warpPath) {
            // Render text with warp effect
            ctx.save();

            // Create path from string
            const path = new Path2D(this.warpPath);

            // Draw text along path (simplified version)
            // For full implementation, you'd need to position each character along the path
            this._renderTextAlongPath(ctx, path);

            ctx.restore();
        } else {
            originalRender.call(this, ctx);
        }
    };

    // Helper method to render text along path
    fabric.Text.prototype._renderTextAlongPath = function(ctx, path) {
        // This is a simplified implementation
        // For full path support, you'd need to calculate character positions
        // For now, we'll use the original render with visual transforms
        const originalRender = fabric.Text.prototype._renderText;
        originalRender.call(this, ctx);
    };
</script>
@endpush
