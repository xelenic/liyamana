@extends('layouts.app')

@section('title', 'Multi-Page Design Tool')
@section('page-title', 'Multi-Page Design Tool')

@php
    $isVisitingCardsType = ($designType ?? '') === 'visiting-cards';
    $mpDefaultCanvasW = $isVisitingCardsType ? 1000 : (int) (\App\Models\Setting::get('editor_default_canvas_width') ?: 800);
    $mpDefaultCanvasH = $isVisitingCardsType ? 600 : (int) (\App\Models\Setting::get('editor_default_canvas_height') ?: 1000);
@endphp

@push('styles')
{{-- Cropper CSS loaded lazily when crop modal opens --}}
<style>
    .design-editor {
        height: calc(100vh - 120px);
        display: flex;
        flex-direction: column;
        background: #f1f5f9;
        position: relative;
    }

    .design-editor.fullscreen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        background: #f1f5f9;
    }

    /* Menu Bar Styles */
    .menu-bar {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0;
        display: flex;
        align-items: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        min-height: 28px;
        font-size: 0.8125rem;
    }

    .menu-item {
        position: relative;
        padding: 0.35rem 0.75rem;
        cursor: pointer;
        color: #1e293b;
        transition: background 0.2s;
        user-select: none;
        border: none;
        background: transparent;
        font-size: 0.8125rem;
    }

    .menu-item:hover {
        background: #e2e8f0;
    }

    .menu-item.active {
        background: #e2e8f0;
    }

    .menu-dropdown {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        min-width: 200px;
        z-index: 1001;
        padding: 0.25rem 0;
        margin-top: 2px;
    }

    .menu-dropdown.show {
        display: block;
    }

    .menu-dropdown-item {
        padding: 0.5rem 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.8125rem;
        color: #1e293b;
        transition: background 0.15s;
        border: none;
        width: 100%;
        text-align: left;
        background: white;
    }

    .menu-dropdown-item:hover {
        background: #f1f5f9;
    }

    .menu-dropdown-item:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .menu-dropdown-item i {
        font-size: 0.875rem;
        width: 18px;
        text-align: center;
        color: #64748b;
    }

    .menu-dropdown-item .shortcut {
        margin-left: auto;
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .menu-dropdown-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 0.25rem 0;
    }

    .design-toolbar {
        background: white;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.4rem 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        min-height: 40px;
    }

    .toolbar-left, .toolbar-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .toolbar-btn {
        padding: 0.35rem 0.65rem;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s;
        font-size: 0.75rem;
        border: none;
        white-space: nowrap;
    }

    .toolbar-btn:hover {
        background: #f8fafc;
        border: 1px solid #6366f1;
    }

    .toolbar-btn i {
        font-size: 0.8rem;
    }

    .toolbar-btn span {
        font-size: 0.75rem;
    }

    /* Checkout Design Button Styles */
    .checkout-design-btn {
        background: #f8fafc;
        color: #1e40af;
        border: 2px solid #3b82f6;
        padding: 0.35rem 1rem;
        border-radius: 0;
        font-weight: 600;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: none;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8125rem;
        white-space: nowrap;
        height: fit-content;
    }

    .checkout-design-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
        transition: left 0.6s ease;
    }

    .checkout-design-btn:hover::before {
        left: 100%;
    }

    .checkout-design-btn:hover {
        background: #f8fafc;
        color: #1e3a8a;
        border-color: #1e40af;
        transform: scale(1.02);
    }

    .checkout-design-btn:active {
        transform: scale(0.98);
        background: #f8fafc;
    }

    .checkout-design-btn i {
        font-size: 1rem;
        animation: bounce 2s infinite;
        position: relative;
        z-index: 1;
    }

    .checkout-design-btn span {
        position: relative;
        z-index: 1;
    }

    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-3px);
        }
    }

    /* Dropdown button styles */
    .toolbar-dropdown {
        position: relative;
        display: inline-block;
    }

    .toolbar-dropdown-btn {
        padding: 0.35rem 0.65rem;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        color: #1e293b;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .toolbar-dropdown-btn:hover {
        background: #f8fafc;
        border-color: #6366f1;
    }

    .toolbar-dropdown-btn i:first-child {
        font-size: 0.8rem;
    }

    .toolbar-dropdown-btn span {
        font-size: 0.75rem;
    }

    .toolbar-dropdown-btn i:last-child {
        font-size: 0.65rem;
        margin-left: 0.1rem;
    }

    .toolbar-dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 0.25rem;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        min-width: 180px;
        z-index: 1000;
        overflow: hidden;
    }

    .toolbar-dropdown-menu.show {
        display: block;
    }

    .toolbar-dropdown-item {
        padding: 0.5rem 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.75rem;
        color: #1e293b;
        transition: background 0.2s;
        border: none;
        width: 100%;
        text-align: left;
        background: white;
    }

    .toolbar-dropdown-item:hover {
        background: #f8fafc;
    }

    .toolbar-dropdown-item i {
        font-size: 0.8rem;
        width: 16px;
        text-align: center;
    }

    .toolbar-dropdown-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 0.25rem 0;
    }

    .left-sidebar {
        width: 265px;
        min-width: 265px;
        flex-shrink: 0;
        background: white;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: row;
        height: 100%;
        position: relative;
    }

    .vertical-tabs {
        width: 45px;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        padding: 0.35rem 0;
        flex-shrink: 0;
        gap: 0.15rem;
    }

    .vertical-tab-btn {
        width: 100%;
        padding: 0.5rem 0.25rem;
        border: none;
        background: transparent;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0;
        color: #64748b;
        transition: all 0.2s;
        border-left: 3px solid transparent;
        position: relative;
        text-align: center;
        min-height: 45px;
    }

    .vertical-tab-btn:hover {
        background: #f1f5f9;
        color: #475569;
    }

    .vertical-tab-btn.active {
        background: white;
        color: #6366f1;
        border-left-color: #6366f1;
    }

    .vertical-tab-btn i {
        font-size: 1rem;
    }

    .vertical-tab-btn span {
        display: none;
    }

    .vertical-tab-content {
        flex: 1;
        min-width: 180px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .tab-panel {
        display: none;
        flex: 1;
        overflow-y: auto;
        flex-direction: column;
    }

    .tab-panel.active {
        display: flex;
    }

    #elementsPanel .panel-section:last-child {
        margin-bottom: 0;
    }

    .pages-panel {
        width: 200px;
        background: white;
        border-right: 1px solid #e2e8f0;
        overflow-y: auto;
        padding: 0;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .tab-container {
        display: flex;
        flex-direction: column;
        height: 100%;
    }


    .properties-panel.collapsed .panel-collapse-btn {
        position: static;
        margin: 1rem auto 0;
    }

    .pages-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .pages-header h5 {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: #475569;
    }

    .add-page-btn {
        width: 32px;
        height: 32px;
        border: none;
        background: #6366f1;
        color: white;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .add-page-btn:hover {
        background: #4f46e5;
    }

    .pages-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .page-item {
        padding: 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        background: white;
        position: relative;
    }

    .page-item:hover {
        border-color: #6366f1;
    }

    .page-item.active {
        border-color: #6366f1;
        background: #eef2ff;
    }

    .page-thumbnail {
        width: 100%;
        height: 120px;
        background: #f8fafc;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.5rem;
        position: relative;
        overflow: hidden;
    }

    .page-thumbnail canvas {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .page-number {
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.25rem;
    }

    .page-actions {
        display: flex;
        gap: 0.25rem;
        margin-top: 0.5rem;
    }

    .page-action-btn {
        flex: 1;
        padding: 0.25rem;
        border: none;
        background: #f1f5f9;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.7rem;
        color: #64748b;
        transition: all 0.2s;
    }

    .page-action-btn:hover {
        background: #e2e8f0;
        color: #475569;
    }

    .page-action-btn.delete {
        color: #ef4444;
    }

    .page-action-btn.delete:hover {
        background: #fee2e2;
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
        position: relative;
        padding: 0;
    }

    .layers-list {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .layer-item {
        padding: 0.5rem 0.6rem;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        cursor: move;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        user-select: none;
    }

    .layer-item:hover {
        background: #f8fafc;
        border-color: #6366f1;
    }

    .layer-item.dragging {
        opacity: 0.5;
        border-color: #6366f1;
    }

    .layer-item.drag-over {
        border-top: 2px solid #6366f1;
    }

    .layer-actions {
        display: flex;
        align-items: center;
        gap: 0.2rem;
        margin-left: auto;
    }

    .layer-delete-btn, .layer-lock-btn {
        width: 20px;
        height: 20px;
        border: none;
        background: transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        transition: all 0.2s;
        padding: 0;
    }

    .layer-delete-btn {
        color: #ef4444;
    }

    .layer-delete-btn i, .layer-lock-btn i {
        font-size: 0.7rem;
    }

    .layer-delete-btn:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    .layer-lock-btn {
        color: #64748b;
    }

    .layer-lock-btn:hover {
        background: #f1f5f9;
        color: #475569;
    }

    .layer-lock-btn.locked {
        color: #f59e0b;
    }

    .layer-lock-btn.locked:hover {
        background: #fef3c7;
        color: #d97706;
    }

    .layer-item.locked {
        opacity: 0.7;
        background: #f8fafc;
    }

    .layer-item.locked .layer-icon {
        opacity: 0.6;
    }

    .layer-group {
        margin-left: 1rem;
        border-left: 2px solid #e2e8f0;
        padding-left: 0.5rem;
    }

    .layer-group-toggle {
        width: 16px;
        height: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #64748b;
        margin-right: 0.25rem;
        font-size: 0.7rem;
    }

    .layer-group-toggle:hover {
        color: #475569;
    }

    .layer-group-items {
        display: none;
        margin-top: 0.25rem;
        margin-left: 0.5rem;
    }

    .layer-group.expanded .layer-group-items {
        display: block;
    }

    .layer-drag-handle {
        width: 16px;
        height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        cursor: grab;
        flex-shrink: 0;
    }

    .layer-drag-handle:active {
        cursor: grabbing;
    }

    .layer-drag-handle i {
        font-size: 0.7rem;
    }

    .layer-icon {
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        border-radius: 3px;
        color: #6366f1;
        flex-shrink: 0;
    }

    .layer-icon i {
        font-size: 0.75rem;
    }

    .layer-info {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }

    .layer-name {
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        line-height: 1.2;
    }

    .layer-type {
        font-size: 0.65rem;
        color: #64748b;
        line-height: 1.2;
    }

    /* Ruler Styles */
    .ruler-container {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
        display: none;
        pointer-events: none;
        z-index: 100;
    }

    .ruler-container.show {
        display: block;
    }

    .ruler-horizontal {
        position: absolute;
        top: 0;
        left: 20px;
        height: 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        overflow: hidden;
        right: 0;
        width: calc(100% - 20px);
    }

    .ruler-vertical {
        position: absolute;
        top: 20px;
        left: 0;
        width: 20px;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        overflow: hidden;
        bottom: 0;
        height: calc(100% - 20px);
    }

    .ruler-horizontal canvas,
    .ruler-vertical canvas {
        display: block;
    }

    .ruler-corner {
        position: absolute;
        top: 0;
        left: 0;
        width: 20px;
        height: 20px;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        z-index: 101;
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
        position: relative;
    }

    .canvas-wrapper {
        background: white;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        padding: 20px;
        border-radius: 4px;
        position: relative;
    }

    .canvas-wrapper.with-rulers {
        margin-top: 20px;
        margin-left: 20px;
    }

    .properties-panel {
        width: 285px;
        background: white;
        border-left: 1px solid #e2e8f0;
        display: flex;
        flex-direction: row;
        height: 100%;
        position: relative;
        overflow: hidden;
    }


    .properties-vertical-tabs {
        width: 45px;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        padding: 0.35rem 0;
        flex-shrink: 0;
        gap: 0.15rem;
    }

    .properties-vertical-tab-btn {
        width: 100%;
        padding: 0.5rem 0.25rem;
        border: none;
        background: transparent;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0;
        color: #64748b;
        transition: all 0.2s;
        border-right: 3px solid transparent;
        position: relative;
        text-align: center;
        min-height: 45px;
    }

    .properties-vertical-tab-btn:hover {
        background: #f1f5f9;
        color: #475569;
    }

    .properties-vertical-tab-btn.active {
        background: white;
        color: #6366f1;
        border-right-color: #6366f1;
    }

    .properties-vertical-tab-btn i {
        font-size: 1rem;
    }

    .properties-vertical-tab-btn span {
        display: none;
    }

    .properties-tab-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .properties-tab-panel {
        display: none;
        flex: 1;
        overflow-y: auto;
        flex-direction: column;
        padding: 1rem;
    }

    .properties-tab-panel.active {
        display: flex;
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

    .variable-item {
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        margin-bottom: 0.5rem;
        background: #f8fafc;
        transition: all 0.2s;
    }

    .variable-item:hover {
        background: #f1f5f9;
        border-color: #6366f1;
    }

    .variable-name {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6366f1;
        margin-bottom: 0.25rem;
        font-family: 'Courier New', monospace;
        word-break: break-all;
    }

    .variable-count {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.25rem;
    }

    .variable-pages {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 0.25rem;
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

    .panel-tab-btn {
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

    .panel-tab-btn:hover {
        background: #f1f5f9;
        color: #475569;
    }

    .panel-tab-btn.active {
        background: #6366f1;
        color: white;
    }

    .panel-tab-content {
        flex: 1;
        overflow-y: auto;
    }

    .toolbar-btn.active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        background-color: white;
    }

    .form-control-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.25rem;
        font-weight: 500;
    }

    /* Modal Styles */
    .modal {
        animation: fadeIn 0.3s;
    }

    /* Flipbook Modal Wizard Styles */
    .wizard-step {
        transition: all 0.3s ease;
    }

    .wizard-step:hover .wizard-step-circle {
        transform: scale(1.1);
    }

    .wizard-step-circle {
        transition: all 0.3s ease;
    }

    .wizard-step-circle.active {
        background: #6366f1 !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4) !important;
    }

    .wizard-step-circle.completed {
        background: #10b981 !important;
        color: white !important;
    }

    .wizard-step-label.active {
        color: #6366f1 !important;
        font-weight: 600 !important;
    }

    .wizard-step-label.completed {
        color: #10b981 !important;
    }

    .flipbook-tab-content {
        display: none;
    }

    .flipbook-tab-content.active {
        display: grid;
    }

    .flipbook-tab-nav-btn {
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.875rem;
        color: #475569;
        transition: all 0.2s;
        display: flex;
        align-items: center;
    }

    .flipbook-tab-nav-btn:hover {
        background: #f8fafc;
        border-color: #6366f1;
        color: #6366f1;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .modal-content {
        animation: slideDown 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .mb-3 {
        margin-bottom: 1rem;
    }

    /* Image Library Styles */
    .image-library-grid {
        padding: 0.25rem;
    }

    /* Template Styles */
    .template-item {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .template-item:hover {
        border-color: #6366f1;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.15);
        transform: translateY(-2px);
    }

    .template-thumbnail {
        width: 100%;
        height: 120px;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.5rem;
        overflow: hidden;
        position: relative;
    }

    .template-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .template-thumbnail .no-thumbnail {
        color: #94a3b8;
        font-size: 1.5rem;
    }

    .template-name {
        font-size: 0.75rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .template-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.25rem;
    }

    .template-category {
        font-size: 0.65rem;
        color: #64748b;
        text-transform: capitalize;
    }

    .template-page-count {
        font-size: 0.65rem;
        color: #6366f1;
        font-weight: 500;
    }

    .template-page-badge {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        background: rgba(99, 102, 241, 0.9);
        color: white;
        font-size: 0.6rem;
        font-weight: 600;
        padding: 0.15rem 0.35rem;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .template-actions {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        display: flex;
        gap: 0.25rem;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .template-item:hover .template-actions {
        opacity: 1;
    }

    .template-delete-btn {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        border: none;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        transition: background 0.2s;
    }

    .template-delete-btn:hover {
        background: #ef4444;
    }

    .image-library-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 4px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
        background: #f8fafc;
    }

    .image-library-item:hover {
        border-color: #6366f1;
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
    }

    .image-library-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .image-library-item .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.75rem;
    }

    .image-library-item:hover .image-overlay {
        display: flex;
    }

    .image-library-item .delete-btn {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.75rem;
        z-index: 10;
    }

    .image-library-item:hover .delete-btn {
        display: flex;
    }

    .image-library-item .delete-btn:hover {
        background: rgba(239, 68, 68, 1);
    }

    /* Context Menu Styles */
    .context-menu {
        position: fixed;
        z-index: 10000;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        min-width: 200px;
        padding: 0.25rem 0;
        font-size: 0.8125rem;
    }

    .context-menu-item {
        width: 100%;
        padding: 0.5rem 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        border: none;
        background: white;
        text-align: left;
        color: #1e293b;
        transition: background 0.15s;
        font-size: 0.8125rem;
    }

    .context-menu-item:hover {
        background: #f1f5f9;
    }

    .context-menu-item:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .context-menu-item:disabled:hover {
        background: white;
    }

    .context-menu-item i {
        width: 18px;
        text-align: center;
        color: #64748b;
        font-size: 0.875rem;
    }

    .context-menu-item span:first-of-type {
        flex: 1;
    }

    .context-menu-shortcut {
        margin-left: auto;
        font-size: 0.75rem;
        color: #94a3b8;
        font-family: monospace;
    }

    .context-menu-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 0.25rem 0;
    }

    /* Template Toggle Switch (Public/Private and Template as Product) */
    #templateIsPublic:checked + .template-toggle-slider,
    #templateIsProduct:checked + .template-toggle-slider {
        background-color: #6366f1;
    }

    #templateIsPublic:checked + .template-toggle-slider span,
    #templateIsProduct:checked + .template-toggle-slider span {
        transform: translateX(22px);
    }

    .template-toggle-slider:hover {
        background-color: #94a3b8;
    }

    #templateIsPublic:checked + .template-toggle-slider:hover,
    #templateIsProduct:checked + .template-toggle-slider:hover {
        background-color: #4f46e5;
    }

    /* Save as Template modal – scrollable areas with visible scrollbar */
    .template-modal-scroll {
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    .template-modal-scroll::-webkit-scrollbar {
        width: 8px;
    }
    .template-modal-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .template-modal-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .template-modal-scroll::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Template Image Preview */
    .template-image-preview {
        position: relative;
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
    }

    .template-image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .template-image-preview .remove-image-btn {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        transition: background 0.2s;
    }

    .template-image-preview .remove-image-btn:hover {
        background: #ef4444;
    }

    /* Small toast notification */
    .design-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        padding: 0.5rem 1rem;
        background: #1e293b;
        color: white;
        font-size: 0.8125rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10000;
        opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.25s, transform 0.25s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        max-width: 320px;
    }
    .design-toast.show {
        opacity: 1;
        transform: translateY(0);
    }
    .design-toast i {
        color: #10b981;
        flex-shrink: 0;
    }

    /* Letter-type New Project Setup modal - mail/envelope theme */
    .project-name-modal--letter .project-name-modal__content--letter {
        border: 2px solid #e0e7ff;
        box-shadow: 0 8px 24px rgba(99, 102, 241, 0.12), 0 0 0 1px rgba(99, 102, 241, 0.05);
    }
    .project-name-modal--letter .project-name-modal__header--letter {
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
        border-bottom: 1px solid #e0e7ff;
    }
    .project-name-modal--letter .project-name-modal__header--letter h3 {
        color: #4f46e5;
    }

    /* ========== Mobile / App-like UI (Canva-style) ========== */
    @media (max-width: 768px) {
        .design-editor {
            height: calc(100vh - 56px);
            min-height: 0;
        }
        .design-editor.fullscreen { height: 100vh; }
        .menu-bar { display: none !important; }
        .design-toolbar {
            padding: 0.35rem 0.5rem;
            min-height: 44px;
            flex-wrap: wrap;
            gap: 0.35rem;
        }
        .design-toolbar .toolbar-left,
        .design-toolbar .toolbar-right {
            flex-wrap: wrap;
            gap: 0.35rem;
        }
        .design-toolbar .toolbar-btn span,
        .design-toolbar .toolbar-dropdown-btn span { display: none !important; }
        .design-toolbar .toolbar-btn,
        .design-toolbar .toolbar-dropdown-btn {
            padding: 0.4rem 0.6rem;
            min-width: 40px;
            min-height: 40px;
            font-size: 0.8rem;
        }
        .design-toolbar .toolbar-dropdown-btn i:last-child { margin-left: 0; }
        .design-toolbar .toolbar-right span#zoomLevel { display: none; }
        .design-workspace { position: relative; min-height: 0; }
        /* Left sidebar: bottom sheet on mobile */
        .left-sidebar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: 65vh;
            max-height: 65vh;
            min-width: 0;
            z-index: 1002;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 -4px 24px rgba(0,0,0,0.2);
            flex-direction: column;
            border-right: none;
        }
        .left-sidebar.mobile-open { transform: translateY(0); }
        .left-sidebar {
            padding-bottom: env(safe-area-inset-bottom);
        }
        .left-sidebar .vertical-tabs {
            width: 100%;
            height: auto;
            flex-direction: row;
            justify-content: space-around;
            padding: 0.5rem;
            border-right: none;
            border-bottom: 1px solid #e2e8f0;
            flex-shrink: 0;
        }
        .left-sidebar .vertical-tab-btn {
            flex: 1;
            min-height: 48px;
            border-left: none;
            border-bottom: 3px solid transparent;
        }
        .left-sidebar .vertical-tab-btn.active { border-left: none; border-bottom-color: #6366f1; }
        .left-sidebar .vertical-tab-btn span { display: block; font-size: 0.65rem; }
        .left-sidebar .vertical-tab-content { min-height: 0; }
        .left-sidebar .tab-container { min-height: 0; overflow: hidden; display: flex; flex-direction: column; }
        .left-sidebar .pages-panel { width: 100%; max-height: none; }
        .left-sidebar .page-thumbnail { height: 80px; }
        .design-canvas-container {
            padding: 0.5rem;
            min-height: 200px;
        }
        .canvas-wrapper { padding: 12px; }
        /* Properties panel: bottom sheet on mobile */
        .properties-panel {
            position: fixed;
            top: auto;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            max-width: 100%;
            height: 55vh;
            max-height: 55vh;
            z-index: 1003;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 -4px 24px rgba(0,0,0,0.2);
            border-left: none;
        }
        .properties-panel.mobile-open { transform: translateY(0); }
        .properties-panel {
            padding-bottom: env(safe-area-inset-bottom);
        }
        .properties-panel .properties-vertical-tabs {
            width: 100%;
            height: auto;
            flex-direction: row;
            justify-content: space-around;
            padding: 0.5rem;
            border-right: none;
            border-bottom: 1px solid #e2e8f0;
        }
        .properties-panel .properties-vertical-tab-btn {
            flex: 1;
            min-height: 44px;
            border-right: none;
            border-bottom: 3px solid transparent;
        }
        .properties-panel .properties-vertical-tab-btn.active { border-right: none; border-bottom-color: #6366f1; }
        .properties-panel .properties-vertical-tab-btn span { display: block; font-size: 0.65rem; }
        .properties-panel .properties-tab-content { min-height: 0; }
        .properties-panel .properties-tab-panel { padding: 0.75rem; }
        /* Mobile overlay */
        .design-mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1001;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .design-mobile-overlay.mobile-open {
            display: block;
            opacity: 1;
        }
        /* FAB to open tools panel */
        /* Mobile bottom bar: 3 items (design panels, items panel, options) */
        .design-mobile-bottom-bar {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            padding: 0.5rem;
            padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
            flex-direction: row;
            align-items: center;
            justify-content: space-around;
            box-shadow: 0 -2px 12px rgba(0,0,0,0.08);
            -webkit-tap-highlight-color: transparent;
        }
        .design-mobile-bottom-bar .design-mobile-bottom-btn {
            width: 48px;
            height: 48px;
            border: none;
            background: #f1f5f9;
            color: #475569;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, transform 0.15s;
        }
        .design-mobile-bottom-bar .design-mobile-bottom-btn:active { transform: scale(0.95); }
        .design-mobile-bottom-bar .design-mobile-bottom-btn.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        .design-mobile-bottom-bar .design-mobile-bottom-btn i { margin: 0; }
        /* Options panel (bottom sheet) */
        .design-mobile-options-panel {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1002;
            background: #fff;
            max-height: 50vh;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 -4px 24px rgba(0,0,0,0.2);
            transform: translateY(100%);
            transition: transform 0.3s ease;
            padding-bottom: env(safe-area-inset-bottom);
            overflow: hidden;
        }
        .design-mobile-options-panel.mobile-open { transform: translateY(0); display: block; }
        .design-mobile-options-panel .design-mobile-options-header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            font-size: 0.9rem;
            color: #1e293b;
        }
        .design-mobile-options-panel .design-mobile-options-body {
            padding: 0.5rem;
            overflow-y: auto;
            max-height: calc(50vh - 50px);
        }
        .design-mobile-options-panel .design-mobile-option-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-size: 0.9rem;
            color: #334155;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .design-mobile-options-panel .design-mobile-option-item:hover,
        .design-mobile-options-panel .design-mobile-option-item:active { background: #f1f5f9; }
        .design-mobile-options-panel .design-mobile-option-item i { width: 22px; color: #6366f1; }
    }
    @media (max-width: 768px) {
        .design-mobile-bottom-bar { display: flex; }
    }
    /* Hide all mobile-only UI on desktop */
    @media (min-width: 769px) {
        .design-mobile-overlay,
        .design-mobile-bottom-bar,
        .design-mobile-options-panel {
            display: none !important;
        }
    }
</style>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js@7/minified/introjs.min.css">
@endpush

@section('content')
<div class="design-editor" id="designEditorRoot">
    <!-- Mobile overlay (tap to close panels) -->
    <div class="design-mobile-overlay" id="designMobileOverlay" onclick="closeMobilePanels()" aria-hidden="true"></div>
    <!-- Mobile bottom bar: 3 items -->
    <nav class="design-mobile-bottom-bar" id="designMobileBottomBar" aria-label="Design tools">
        <button type="button" class="design-mobile-bottom-btn" id="mobileBtnDesignPanels" onclick="toggleMobileLeftSidebar()" title="Design panels" aria-label="Design panels (Pages, Elements, etc.)">
            <i class="fas fa-plus"></i>
        </button>
        <button type="button" class="design-mobile-bottom-btn" id="mobileBtnItemPanel" onclick="toggleMobileItemPanel()" title="Items" aria-label="Item panel">
            <i class="fas fa-list"></i>
        </button>
        <button type="button" class="design-mobile-bottom-btn" id="mobileBtnOptions" onclick="toggleMobileOptionsPanel()" title="Options" aria-label="More options">
            <i class="fas fa-ellipsis-h"></i>
        </button>
    </nav>
    <!-- Mobile options panel (bottom sheet) -->
    <div class="design-mobile-options-panel" id="designMobileOptionsPanel">
        <div class="design-mobile-options-header">Options</div>
        <div class="design-mobile-options-body">
            <button type="button" class="design-mobile-option-item" onclick="saveDesign(); closeMobilePanels();"><i class="fas fa-save"></i> Save</button>
            <button type="button" class="design-mobile-option-item" onclick="exportAllPagesPDF(); closeMobilePanels();"><i class="fas fa-file-pdf"></i> Export PDF</button>
            <button type="button" class="design-mobile-option-item" onclick="exportAllPages(); closeMobilePanels();"><i class="fas fa-image"></i> Export PNG</button>
            <button type="button" class="design-mobile-option-item" onclick="saveAsTemplate(); closeMobilePanels();"><i class="fas fa-layer-group"></i> Save as Template</button>
            <button type="button" class="design-mobile-option-item" onclick="saveAsFlipbook(); closeMobilePanels();"><i class="fas fa-book"></i> Save as Flipbook</button>
            <button type="button" class="design-mobile-option-item" onclick="toggleFullscreen(); closeMobilePanels();"><i class="fas fa-expand"></i> Fullscreen</button>
        </div>
    </div>
    <!-- Menu Bar -->
    @if(filter_var(\App\Models\Setting::get('editor_show_menu_bar', '1'), FILTER_VALIDATE_BOOLEAN))
    <div class="menu-bar" id="menuBar">
        <!-- File Menu -->
        <div class="menu-item" onmouseenter="showMenuDropdown('fileMenu')" onmouseleave="hideMenuDropdown('fileMenu')">
            File
            <div class="menu-dropdown" id="fileMenu" onmouseenter="showMenuDropdown('fileMenu')" onmouseleave="hideMenuDropdown('fileMenu')">
                <button class="menu-dropdown-item" onclick="saveDesign()">
                    <i class="fas fa-save"></i>
                    <span>Save All Pages</span>
                    <span class="shortcut">Ctrl+S</span>
                </button>
                <button class="menu-dropdown-item" onclick="saveAsFlipbook()">
                    <i class="fas fa-book"></i>
                    <span>Save as Flipbook</span>
                </button>
                <button class="menu-dropdown-item" onclick="saveAsTemplate()">
                    <i class="fas fa-layer-group"></i>
                    <span>Save as Template</span>
                </button>
                <div class="menu-dropdown-divider"></div>
                @if(!empty($canExportWatermark))
                <button class="menu-dropdown-item" onclick="exportAllPages()">
                    <i class="fas fa-image"></i>
                    <span>Export as PNG</span>
                </button>
                <button class="menu-dropdown-item" onclick="exportAllPagesPDF()">
                    <i class="fas fa-file-pdf"></i>
                    <span>Export as PDF</span>
                </button>
                <button class="menu-dropdown-item" onclick="openWatermarkModal()">
                    <i class="fas fa-water"></i>
                    <span>Export PDF with Watermark</span>
                </button>
                @endif
                @if(config('app.debug'))
                <div class="menu-dropdown-divider"></div>
                <button class="menu-dropdown-item" onclick="exportDesignJSON()">
                    <i class="fas fa-file-code"></i>
                    <span>Export JSON</span>
                </button>
                <button class="menu-dropdown-item" onclick="importDesignJSON()">
                    <i class="fas fa-file-import"></i>
                    <span>Import JSON</span>
                </button>
                @endif
                <div class="menu-dropdown-divider"></div>
                <button class="menu-dropdown-item" onclick="window.location.href='{{ route('design.index') }}'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Exit</span>
                </button>
            </div>
        </div>

        <!-- Edit Menu -->
        <div class="menu-item" onmouseenter="showMenuDropdown('editMenu')" onmouseleave="hideMenuDropdown('editMenu')">
            Edit
            <div class="menu-dropdown" id="editMenu" onmouseenter="showMenuDropdown('editMenu')" onmouseleave="hideMenuDropdown('editMenu')">
                <button class="menu-dropdown-item" onclick="undo()">
                    <i class="fas fa-undo"></i>
                    <span>Undo</span>
                    <span class="shortcut">Ctrl+Z</span>
                </button>
                <button class="menu-dropdown-item" onclick="redo()">
                    <i class="fas fa-redo"></i>
                    <span>Redo</span>
                    <span class="shortcut">Ctrl+Y</span>
                </button>
                <div class="menu-dropdown-divider"></div>
                <button class="menu-dropdown-item" onclick="copySelected()">
                    <i class="fas fa-copy"></i>
                    <span>Copy</span>
                    <span class="shortcut">Ctrl+C</span>
                </button>
                <button class="menu-dropdown-item" onclick="pasteSelected()" id="menuPasteBtn" disabled>
                    <i class="fas fa-paste"></i>
                    <span>Paste</span>
                    <span class="shortcut">Ctrl+V</span>
                </button>
                <button class="menu-dropdown-item" onclick="duplicateSelected()">
                    <i class="fas fa-clone"></i>
                    <span>Duplicate</span>
                    <span class="shortcut">Ctrl+D</span>
                </button>
                <div class="menu-dropdown-divider"></div>
                <button class="menu-dropdown-item" onclick="deleteSelected()">
                    <i class="fas fa-trash"></i>
                    <span>Delete</span>
                    <span class="shortcut">Del</span>
                </button>
                <div class="menu-dropdown-divider"></div>
                <button class="menu-dropdown-item" onclick="selectAll()">
                    <i class="fas fa-check-square"></i>
                    <span>Select All</span>
                    <span class="shortcut">Ctrl+A</span>
                </button>
                <button class="menu-dropdown-item" onclick="deselectAll()">
                    <i class="fas fa-square"></i>
                    <span>Deselect All</span>
                </button>
            </div>
        </div>

        <!-- View Menu -->
        <div class="menu-item" onmouseenter="showMenuDropdown('viewMenu')" onmouseleave="hideMenuDropdown('viewMenu')">
            View
            <div class="menu-dropdown" id="viewMenu" onmouseenter="showMenuDropdown('viewMenu')" onmouseleave="hideMenuDropdown('viewMenu')">
                <button class="menu-dropdown-item" onclick="zoomIn()">
                    <i class="fas fa-search-plus"></i>
                    <span>Zoom In</span>
                    <span class="shortcut">Ctrl++</span>
                </button>
                <button class="menu-dropdown-item" onclick="zoomOut()">
                    <i class="fas fa-search-minus"></i>
                    <span>Zoom Out</span>
                    <span class="shortcut">Ctrl+-</span>
                </button>
                <button class="menu-dropdown-item" onclick="resetZoom()">
                    <i class="fas fa-expand-arrows-alt"></i>
                    <span>Reset Zoom</span>
                    <span class="shortcut">Ctrl+0</span>
                </button>
                <button class="menu-dropdown-item" onclick="fitToScreen()">
                    <i class="fas fa-compress-arrows-alt"></i>
                    <span>Fit to Screen</span>
                </button>
                <div class="menu-dropdown-divider"></div>
                <button class="menu-dropdown-item" onclick="toggleRulers()" id="menuToggleRulers">
                    <i class="fas fa-ruler"></i>
                    <span id="menuRulersText">Show Rulers</span>
                </button>
                <button class="menu-dropdown-item" onclick="toggleGrid()">
                    <i class="fas fa-th"></i>
                    <span>Show Grid</span>
                </button>
                <button class="menu-dropdown-item" onclick="toggleGuides()">
                    <i class="fas fa-align-center"></i>
                    <span>Show Guides</span>
                </button>
            </div>
        </div>

        <!-- Layers Menu -->
        <div class="menu-item" onmouseenter="showMenuDropdown('layersMenu')" onmouseleave="hideMenuDropdown('layersMenu')">
            Layers
            <div class="menu-dropdown" id="layersMenu" onmouseenter="showMenuDropdown('layersMenu')" onmouseleave="hideMenuDropdown('layersMenu')">
                <button class="menu-dropdown-item" onclick="bringToFront()">
                    <i class="fas fa-arrow-up"></i>
                    <span>Bring to Front</span>
                    <span class="shortcut">Ctrl+]</span>
                </button>
                <button class="menu-dropdown-item" onclick="sendToBack()">
                    <i class="fas fa-arrow-down"></i>
                    <span>Send to Back</span>
                    <span class="shortcut">Ctrl+[</span>
                </button>
                <button class="menu-dropdown-item" onclick="bringForward()">
                    <i class="fas fa-chevron-up"></i>
                    <span>Bring Forward</span>
                </button>
                <button class="menu-dropdown-item" onclick="sendBackward()">
                    <i class="fas fa-chevron-down"></i>
                    <span>Send Backward</span>
                </button>
                <div class="menu-dropdown-divider"></div>
                <button class="menu-dropdown-item" onclick="groupSelected()">
                    <i class="fas fa-object-group"></i>
                    <span>Group</span>
                    <span class="shortcut">Ctrl+G</span>
                </button>
                <button class="menu-dropdown-item" onclick="ungroupSelected()">
                    <i class="fas fa-object-ungroup"></i>
                    <span>Ungroup</span>
                    <span class="shortcut">Ctrl+Shift+G</span>
                </button>
                <div class="menu-dropdown-divider"></div>
                <button class="menu-dropdown-item" onclick="lockSelected()">
                    <i class="fas fa-lock"></i>
                    <span>Lock</span>
                </button>
                <button class="menu-dropdown-item" onclick="unlockSelected()">
                    <i class="fas fa-unlock"></i>
                    <span>Unlock</span>
                </button>
            </div>
        </div>

        <!-- Page Menu -->
        <div class="menu-item" onmouseenter="showMenuDropdown('pageMenu')" onmouseleave="hideMenuDropdown('pageMenu')">
            Page
            <div class="menu-dropdown" id="pageMenu" onmouseenter="showMenuDropdown('pageMenu')" onmouseleave="hideMenuDropdown('pageMenu')">
                <button class="menu-dropdown-item" onclick="addNewPage()">
                    <i class="fas fa-plus"></i>
                    <span>Add New Page</span>
                </button>
                <button class="menu-dropdown-item" onclick="duplicateCurrentPage()">
                    <i class="fas fa-copy"></i>
                    <span>Duplicate Page</span>
                </button>
                <button class="menu-dropdown-item" onclick="deleteCurrentPage()">
                    <i class="fas fa-trash"></i>
                    <span>Delete Current Page</span>
                </button>
                <div class="menu-dropdown-divider"></div>
                <button class="menu-dropdown-item" onclick="previousPage()">
                    <i class="fas fa-chevron-left"></i>
                    <span>Previous Page</span>
                </button>
                <button class="menu-dropdown-item" onclick="nextPage()">
                    <i class="fas fa-chevron-right"></i>
                    <span>Next Page</span>
                </button>
            </div>
        </div>

        <!-- Help Menu -->
        <div class="menu-item" onmouseenter="showMenuDropdown('helpMenu')" onmouseleave="hideMenuDropdown('helpMenu')">
            Help
            <div class="menu-dropdown" id="helpMenu" onmouseenter="showMenuDropdown('helpMenu')" onmouseleave="hideMenuDropdown('helpMenu')">
                <button class="menu-dropdown-item" onclick="hideMenuDropdown('helpMenu'); startDesignIntro();">
                    <i class="fas fa-route"></i>
                    <span>Take a tour</span>
                </button>
                <button class="menu-dropdown-item" onclick="showKeyboardShortcuts()">
                    <i class="fas fa-keyboard"></i>
                    <span>Keyboard Shortcuts</span>
                </button>
                <button class="menu-dropdown-item" onclick="showHelp()">
                    <i class="fas fa-question-circle"></i>
                    <span>Help & Documentation</span>
                </button>
                <div class="menu-dropdown-divider"></div>
                <button class="menu-dropdown-item" onclick="showAbout()">
                    <i class="fas fa-info-circle"></i>
                    <span>About</span>
                </button>
            </div>
        </div>

        @php
            $menuBarLinks = json_decode(\App\Models\Setting::get('editor_menu_bar_links') ?: '[]', true) ?: [];
        @endphp
        @if(!empty($menuBarLinks))
        <!-- Custom Links Menu -->
        <div class="menu-item" onmouseenter="showMenuDropdown('linksMenu')" onmouseleave="hideMenuDropdown('linksMenu')">
            Links
            <div class="menu-dropdown" id="linksMenu" onmouseenter="showMenuDropdown('linksMenu')" onmouseleave="hideMenuDropdown('linksMenu')">
                @foreach($menuBarLinks as $link)
                    @if(!empty($link['label']) && !empty($link['url']) && preg_match('/^(https?:\/\/|\/)/', $link['url']))
                    @php
                        $icon = $link['icon'] ?? 'fa-link';
                        $icon = preg_replace('/[^a-zA-Z0-9_-]/', '', $icon) ?: 'fa-link';
                    @endphp
                    <a class="menu-dropdown-item" href="{{ e($link['url']) }}" target="_blank" rel="noopener noreferrer" style="text-decoration: none; color: inherit;">
                        <i class="fas {{ $icon }}"></i>
                        <span>{{ e($link['label']) }}</span>
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- Checkout Design / Send Letter Button in Menu Bar -->
        <div style="margin-left: auto; padding: 0 0.5rem; margin-bottom: 0.25rem;">
            @if(($designType ?? '') === 'letter')
            <button class="checkout-design-btn" onclick="sendLetter()" title="Send Letter" style="margin: 0;">
                <i class="fas fa-paper-plane"></i>
                <span>Send Letter</span>
            </button>
            @else
            <button class="checkout-design-btn" onclick="checkoutDesign()" title="Checkout Design - Quick Use" style="margin: 0;">
                <i class="fas fa-shopping-cart"></i>
                <span>Checkout Design</span>
            </button>
            @endif
        </div>
    </div>
    @endif

    <!-- Toolbar -->
    <div class="design-toolbar" id="designToolbar">
        <div class="toolbar-left">
            @if(isset($fromFlipbook) && $fromFlipbook)
                <a href="{{ route('flipbooks.create') }}" class="toolbar-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </a>
            @else
                <a href="{{ route('design.index') }}" class="toolbar-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </a>
            @endif
            <button class="toolbar-btn" onclick="saveDesign()" id="saveDesignBtn">
                <i class="fas fa-save"></i>
                <span>Save All Pages</span>
            </button>
            @if(isset($fromFlipbook) && $fromFlipbook)
                <!-- Show Save/Update Flipbook buttons directly when from flipbooks/create -->
                <button class="toolbar-btn" onclick="saveAsFlipbook()" title="Save as Flipbook" id="saveAsFlipbookBtn">
                    <i class="fas fa-book"></i>
                    <span id="saveAsFlipbookBtnText">Save as Flipbook</span>
                </button>
            @endif
            <div class="toolbar-dropdown">
                <button class="toolbar-dropdown-btn" onclick="toggleExportDropdown(event)" id="exportDropdownBtn">
                    <i class="fas fa-download"></i>
                    <span>Export</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="toolbar-dropdown-menu" id="exportDropdownMenu">
                    @if(!empty($canExportWatermark))
                    <button class="toolbar-dropdown-item" onclick="exportAllPages(); closeExportDropdown();">
                        <i class="fas fa-image"></i>
                        <span>Export PNG</span>
                    </button>
                    <button class="toolbar-dropdown-item" onclick="exportAllPagesPDF(); closeExportDropdown();">
                        <i class="fas fa-file-pdf"></i>
                        <span>Export PDF</span>
                    </button>
                    <button class="toolbar-dropdown-item" onclick="openWatermarkModal(); closeExportDropdown();">
                        <i class="fas fa-water"></i>
                        <span>Export PDF with Watermark</span>
                    </button>
                    @endif
                    @if(config('app.debug'))
                    <div class="toolbar-dropdown-divider"></div>
                    <button class="toolbar-dropdown-item" onclick="exportDesignJSON(); closeExportDropdown();">
                        <i class="fas fa-file-code"></i>
                        <span>Export JSON</span>
                    </button>
                    <button class="toolbar-dropdown-item" onclick="importDesignJSON(); closeExportDropdown();">
                        <i class="fas fa-file-import"></i>
                        <span>Import JSON</span>
                    </button>
                    @endif
                    @if(!isset($fromFlipbook) || !$fromFlipbook)
                        <!-- Only show Save as Flipbook in dropdown if not from flipbooks/create -->
                        <div class="toolbar-dropdown-divider"></div>
                        <button class="toolbar-dropdown-item" onclick="saveAsFlipbook(); closeExportDropdown();" id="saveAsFlipbookBtnDropdown">
                            <i class="fas fa-book"></i>
                            <span id="saveAsFlipbookBtnTextDropdown">Save as Flipbook</span>
                        </button>
                    @endif
                    <div class="toolbar-dropdown-divider"></div>
                    <button class="toolbar-dropdown-item" onclick="saveAsTemplate(); closeExportDropdown();">
                        <i class="fas fa-layer-group"></i>
                        <span>Save Template</span>
                    </button>
                </div>
            </div>
            <button class="toolbar-btn" onclick="openAIGenerateModal()" title="Generate Design by AI" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                <i class="fas fa-magic"></i>
                <span>Generate by AI</span>
            </button>
            <button class="toolbar-btn" onclick="copySelected()" title="Copy Selected">
                <i class="fas fa-copy"></i>
                <span>Copy</span>
            </button>
            <button class="toolbar-btn" onclick="pasteSelected()" title="Paste" id="pasteBtn" disabled>
                <i class="fas fa-paste"></i>
                <span>Paste</span>
            </button>
            <button class="toolbar-btn" onclick="deleteSelected()">
                <i class="fas fa-trash"></i>
                <span>Delete</span>
            </button>
        </div>
        <div class="toolbar-right">
            <span style="padding: 0 0.4rem; font-size: 0.7rem; color: #64748b;">
                Page <span id="currentPageNumber">1</span> of <span id="totalPages">1</span>
            </span>
            <button class="toolbar-btn" onclick="undo()" title="Undo">
                <i class="fas fa-undo"></i>
            </button>
            <button class="toolbar-btn" onclick="redo()" title="Redo">
                <i class="fas fa-redo"></i>
            </button>
            <button class="toolbar-btn" onclick="zoomOut()" title="Zoom Out">
                <i class="fas fa-search-minus"></i>
            </button>
            <span id="zoomLevel" style="padding: 0 0.4rem; font-size: 0.7rem; color: #64748b;">50%</span>
            <button class="toolbar-btn" onclick="zoomIn()" title="Zoom In">
                <i class="fas fa-search-plus"></i>
            </button>
            <button class="toolbar-btn" onclick="toggleFullscreen()" title="Toggle Fullscreen" id="fullscreenBtn">
                <i class="fas fa-expand" id="fullscreenIcon"></i>
            </button>
        </div>
    </div>

    <!-- Workspace -->
    <div class="design-workspace">
        <!-- Left Sidebar with Vertical Tabs -->
        <div class="left-sidebar" id="leftSidebar">
            <!-- Vertical Tabs -->
            <div class="vertical-tabs">
                <button class="vertical-tab-btn active" onclick="switchVerticalTab('pages')" id="verticalTabPages" title="Pages">
                    <i class="fas fa-file-alt"></i>
                    <span>Pages</span>
                </button>
                <button class="vertical-tab-btn" onclick="switchVerticalTab('templates')" id="verticalTabTemplates" title="Templates">
                    <i class="fas fa-layer-group"></i>
                    <span>Templates</span>
                </button>
                <button class="vertical-tab-btn" onclick="switchVerticalTab('elements')" id="verticalTabElements" title="Elements">
                    <i class="fas fa-shapes"></i>
                    <span>Elements</span>
                </button>
                <button class="vertical-tab-btn" onclick="switchVerticalTab('images')" id="verticalTabImages" title="Image Library">
                    <i class="fas fa-images"></i>
                    <span>Images</span>
                </button>
                <button class="vertical-tab-btn" onclick="switchVerticalTab('globalImages')" id="verticalTabGlobalImages" title="Global Image Parts">
                    <i class="fas fa-globe"></i>
                    <span>Global</span>
                </button>
                <button class="vertical-tab-btn" onclick="switchVerticalTab('layers')" id="verticalTabLayers" title="Layers">
                    <i class="fas fa-layer-group"></i>
                    <span>Layers</span>
                </button>
            </div>

            <!-- Tab Content Area -->
            <div class="vertical-tab-content">
                <!-- Pages Panel -->
                <div id="pagesPanel" class="tab-panel active">
                <div style="padding: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h5 style="margin: 0; font-size: 0.875rem; font-weight: 600; color: #475569;">Pages</h5>
                        <button class="add-page-btn" onclick="addNewPage()" title="Add New Page">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="pages-list" id="pagesList">
                        <!-- Pages will be dynamically added here -->
                </div>
            </div>
        </div>

                <!-- Templates Panel -->
                <div id="templatesPanel" class="tab-panel">
                <div style="padding: 1rem;">
                    <div class="panel-section">
                        <div class="panel-title">Templates</div>
                            <div id="templatesList" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; max-height: calc(100vh - 200px); overflow-y: auto; padding-top: 1rem;">
                                <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;" id="templatesEmptyState">
                                    <i class="fas fa-layer-group" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                                    <p style="margin: 0;">No templates available</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Elements Panel -->
                <div id="elementsPanel" class="tab-panel">
                <div style="padding: 1rem;">
                    @php
                        $showText = filter_var(\App\Models\Setting::get('editor_element_heading', '1'), FILTER_VALIDATE_BOOLEAN)
                            || filter_var(\App\Models\Setting::get('editor_element_subheading', '1'), FILTER_VALIDATE_BOOLEAN)
                            || filter_var(\App\Models\Setting::get('editor_element_body', '1'), FILTER_VALIDATE_BOOLEAN);
                        $showShapes = filter_var(\App\Models\Setting::get('editor_element_rectangle', '1'), FILTER_VALIDATE_BOOLEAN)
                            || filter_var(\App\Models\Setting::get('editor_element_circle', '1'), FILTER_VALIDATE_BOOLEAN)
                            || filter_var(\App\Models\Setting::get('editor_element_line', '1'), FILTER_VALIDATE_BOOLEAN)
                            || filter_var(\App\Models\Setting::get('editor_element_triangle', '1'), FILTER_VALIDATE_BOOLEAN);
                        $showTable = filter_var(\App\Models\Setting::get('editor_element_table', '1'), FILTER_VALIDATE_BOOLEAN);
                        $showUpload = filter_var(\App\Models\Setting::get('editor_element_upload_image', '1'), FILTER_VALIDATE_BOOLEAN);
                        $hasAnyElements = $showText || $showShapes || $showTable || $showUpload;
                    @endphp
                    @if(!$hasAnyElements)
                    <div class="panel-section" style="padding: 2rem 0; text-align: center;">
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">No elements enabled.</p>
                        <p class="text-muted mb-0 mt-1" style="font-size: 0.75rem;">Enable elements in Admin → Settings → Editor → Element Panel.</p>
                    </div>
                    @else
                    @if($showText)
                    <div class="panel-section">
                        <div class="panel-title">Text</div>
                        <div class="element-list">
                            @if(filter_var(\App\Models\Setting::get('editor_element_heading', '1'), FILTER_VALIDATE_BOOLEAN))
                            <div class="element-item" onclick="addText('Heading')">
                                <div class="element-icon"><i class="fas fa-heading"></i></div>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.875rem;">Heading</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Large text</div>
                                </div>
                            </div>
                            @endif
                            @if(filter_var(\App\Models\Setting::get('editor_element_subheading', '1'), FILTER_VALIDATE_BOOLEAN))
                            <div class="element-item" onclick="addText('Subheading')">
                                <div class="element-icon"><i class="fas fa-text-height"></i></div>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.875rem;">Subheading</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Medium text</div>
                                </div>
                            </div>
                            @endif
                            @if(filter_var(\App\Models\Setting::get('editor_element_body', '1'), FILTER_VALIDATE_BOOLEAN))
                            <div class="element-item" onclick="addText('Body')">
                                <div class="element-icon"><i class="fas fa-font"></i></div>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.875rem;">Body Text</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Regular text</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($showShapes)
                    <div class="panel-section">
                        <div class="panel-title">Shapes</div>
                        <div class="element-list">
                            @if(filter_var(\App\Models\Setting::get('editor_element_rectangle', '1'), FILTER_VALIDATE_BOOLEAN))
                            <div class="element-item" onclick="addShape('rect')">
                                <div class="element-icon"><i class="fas fa-square"></i></div>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.875rem;">Rectangle</div>
                                </div>
                            </div>
                            @endif
                            @if(filter_var(\App\Models\Setting::get('editor_element_circle', '1'), FILTER_VALIDATE_BOOLEAN))
                            <div class="element-item" onclick="addShape('circle')">
                                <div class="element-icon"><i class="fas fa-circle"></i></div>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.875rem;">Circle</div>
                                </div>
                            </div>
                            @endif
                            @if(filter_var(\App\Models\Setting::get('editor_element_line', '1'), FILTER_VALIDATE_BOOLEAN))
                            <div class="element-item" onclick="addShape('line')">
                                <div class="element-icon"><i class="fas fa-minus"></i></div>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.875rem;">Line</div>
                                </div>
                            </div>
                            @endif
                            @if(filter_var(\App\Models\Setting::get('editor_element_triangle', '1'), FILTER_VALIDATE_BOOLEAN))
                            <div class="element-item" onclick="addShape('triangle')">
                                <div class="element-icon"><i class="fas fa-play"></i></div>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.875rem;">Triangle</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($showTable)
                    <div class="panel-section">
                        <div class="panel-title">Table</div>
                        <div class="element-list">
                            <div class="element-item" onclick="addTable()">
                                <div class="element-icon"><i class="fas fa-table"></i></div>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.875rem;">Table</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Insert table</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($showUpload)
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
                    @endif

                    @foreach(app(\App\Services\Module\ModuleRegistry::class)->getEditorElements() as $el)
                    <div class="panel-section">
                        <div class="panel-title">{{ $el['label'] }}</div>
                        <div class="element-list">
                            <div class="element-item" onclick="var fn = window['{{ $el['handler'] }}']; typeof fn === 'function' ? fn() : console.warn('Module handler {{ $el['handler'] }} not loaded');">
                                <div class="element-icon"><i class="fas {{ $el['icon'] ?? 'fa-cube' }}"></i></div>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.875rem;">{{ $el['label'] }}</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">{{ $el['description'] ?? '' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>

                <!-- Image Library Panel -->
                <div id="imagesPanel" class="tab-panel">
                    <div style="padding: 1rem;">
                        <div class="panel-section">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <div class="panel-title" style="margin: 0;">Image Library</div>
                                <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('libraryImageUpload').click()" title="Upload Image" style="padding: 0.25rem 0.5rem; border: 1px solid #e2e8f0; background: white; border-radius: 4px; cursor: pointer; color: #64748b;">
                                    <i class="fas fa-upload"></i>
                                </button>
                            </div>
                            <input type="file" id="libraryImageUpload" accept="image/*" multiple style="display: none;" onchange="handleLibraryImageUpload(event)">
                            <div id="imageLibraryGrid" class="image-library-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; max-height: calc(100vh - 300px); overflow-y: auto;">
                                <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;">
                                    <i class="fas fa-images" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                                    <p style="margin: 0;">Loading images...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Global Image Parts Panel -->
                <div id="globalImagesPanel" class="tab-panel">
                    <div style="padding: 1rem; display: flex; flex-direction: column; height: 100%; overflow: hidden;">
                        <div class="panel-section" style="flex: 1; min-height: 0; display: flex; flex-direction: column;">
                            <div class="panel-title" style="margin-bottom: 0.5rem;">Global Image Parts</div>
                            <div id="globalImageCategoryTabs" style="display: flex; flex-wrap: wrap; gap: 0.35rem; margin-bottom: 0.75rem; flex-shrink: 0;"></div>
                            <div id="globalImageLibraryGrid" class="image-library-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; overflow-y: auto; flex: 1; min-height: 0;">
                                <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;">
                                    <i class="fas fa-globe" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                                    <p style="margin: 0;">Click tab to load</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Layers Panel -->
                <div id="layersPanel" class="tab-panel">
                <div style="padding: 1rem;">
                    <div class="panel-section">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <div class="panel-title" style="margin: 0;">Layers</div>
                                <button class="btn btn-sm btn-outline-secondary" onclick="refreshLayers()" title="Refresh" style="padding: 0.25rem 0.5rem; border: 1px solid #e2e8f0; background: white; border-radius: 4px; cursor: pointer; color: #64748b;">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <div id="layersList" class="layers-list">
                                <p class="text-muted small text-center" style="padding: 1rem 0; color: #94a3b8; font-size: 0.875rem;">No layers yet</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Canvas -->
        <div class="design-canvas-container" id="canvasContainer">
            <div class="ruler-container" id="rulerContainer">
                <div class="ruler-corner"></div>
                <div class="ruler-horizontal">
                    <canvas id="rulerHorizontal"></canvas>
                </div>
                <div class="ruler-vertical">
                    <canvas id="rulerVertical"></canvas>
                </div>
            </div>
            <div class="canvas-wrapper" id="canvasWrapper">
                <canvas id="fabricCanvas"></canvas>
            </div>
        </div>

        <!-- Right Sidebar - Properties -->
        <div class="properties-panel" id="propertiesPanel">
            <!-- Vertical Tabs -->
            <div class="properties-vertical-tabs">
                <button class="properties-vertical-tab-btn active" onclick="switchPropertiesVerticalTab('properties')" id="verticalTabProperties" title="Properties">
                <i class="fas fa-sliders-h"></i>
                    <span>Properties</span>
            </button>
                <button class="properties-vertical-tab-btn" onclick="switchPropertiesVerticalTab('variables')" id="verticalTabVariables" title="Variables">
                    <i class="fas fa-code"></i>
                    <span>Variables</span>
                </button>
                <button class="properties-vertical-tab-btn" onclick="switchPropertiesVerticalTab('styles')" id="verticalTabStyles" title="Styles">
                    <i class="fas fa-palette"></i>
                    <span>Styles</span>
                </button>
                @foreach(app(\App\Services\Module\ModuleRegistry::class)->getImagePropertiesPanels() as $imgPanel)
                <button class="properties-vertical-tab-btn" onclick="switchPropertiesVerticalTab('imageColor')" id="verticalTabImageColor" title="{{ $imgPanel['label'] }}" style="display: none;">
                    <i class="fas {{ $imgPanel['icon'] }}"></i>
                    <span>{{ $imgPanel['label'] }}</span>
                </button>
                @break
                @endforeach
                <button class="properties-vertical-tab-btn" onclick="switchPropertiesVerticalTab('settings')" id="verticalTabSettings" title="Settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </button>
            </div>

            <!-- Tab Content Area -->
            <div class="properties-tab-content">
            <!-- Properties Tab -->
                <div id="propertiesTab" class="properties-tab-panel active">
                    <div class="panel-section">
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
                        <!-- Rich Text Formatting Toolbar -->
                        <div class="mb-2" id="richTextToolbar" style="display: none; border: 1px solid #e2e8f0; border-radius: 4px; padding: 0.5rem; background: #f8fafc;">
                            <div style="display: flex; gap: 0.25rem; flex-wrap: wrap; align-items: center;">
                                <button class="toolbar-btn" onclick="applyTextFormat('bold')" id="formatBold" title="Bold (Ctrl+B)" style="padding: 0.4rem 0.6rem; font-size: 0.75rem;">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button class="toolbar-btn" onclick="applyTextFormat('italic')" id="formatItalic" title="Italic (Ctrl+I)" style="padding: 0.4rem 0.6rem; font-size: 0.75rem;">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button class="toolbar-btn" onclick="applyTextFormat('underline')" id="formatUnderline" title="Underline (Ctrl+U)" style="padding: 0.4rem 0.6rem; font-size: 0.75rem;">
                                    <i class="fas fa-underline"></i>
                                </button>
                                <div style="width: 1px; height: 24px; background: #e2e8f0; margin: 0 0.25rem;"></div>
                                <input type="color" id="textColorPicker" onchange="applyTextColor(this.value)" title="Text Color" style="width: 36px; height: 32px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer; padding: 2px;">
                                <button class="toolbar-btn" onclick="clearTextFormat()" title="Clear Formatting" style="padding: 0.4rem 0.6rem; font-size: 0.75rem;">
                                    <i class="fas fa-remove-format"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Font Size</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="number" id="propFontSize" class="form-control form-control-sm" min="8" max="200" onchange="updateProperty('fontSize', this.value)" style="flex: 1;">
                                <button class="toolbar-btn" onclick="adjustFontSize(-2)" style="padding: 0.25rem 0.5rem; min-width: 32px;" title="Decrease">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button class="toolbar-btn" onclick="adjustFontSize(2)" style="padding: 0.25rem 0.5rem; min-width: 32px;" title="Increase">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                <label class="form-label" style="font-size: 0.75rem; margin: 0;">Font Family</label>
                                <button type="button" class="toolbar-btn" onclick="openFontLibraryModal(event)" title="Import Fonts" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                                    <i class="fas fa-upload"></i> Import
                                </button>
                            </div>
                            <select id="propFontFamily" class="form-select form-select-sm" onchange="updateProperty('fontFamily', this.value)">
                                <option value="Arial">Arial</option>
                                <option value="Helvetica">Helvetica</option>
                                <option value="Times New Roman">Times New Roman</option>
                                <option value="Courier New">Courier New</option>
                                <option value="Verdana">Verdana</option>
                                <option value="Georgia">Georgia</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Font Weight</label>
                            <select id="propFontWeight" class="form-select form-select-sm" onchange="updateProperty('fontWeight', this.value)">
                                <option value="normal">Normal</option>
                                <option value="bold">Bold</option>
                                <option value="100">Thin</option>
                                <option value="300">Light</option>
                                <option value="400">Regular</option>
                                <option value="500">Medium</option>
                                <option value="600">Semi Bold</option>
                                <option value="700">Bold</option>
                                <option value="800">Extra Bold</option>
                                <option value="900">Black</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Line Height (Paragraph Spacing)</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="number" id="propLineHeight" class="form-control form-control-sm" min="0.5" max="5" step="0.1" onchange="updateProperty('lineHeight', this.value)" style="flex: 1;">
                                <button class="toolbar-btn" onclick="adjustLineHeight(-0.1)" style="padding: 0.25rem 0.5rem; min-width: 32px;" title="Decrease">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button class="toolbar-btn" onclick="adjustLineHeight(0.1)" style="padding: 0.25rem 0.5rem; min-width: 32px;" title="Increase">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Letter Spacing</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="number" id="propCharSpacing" class="form-control form-control-sm" min="-10" max="50" step="0.5" onchange="updateProperty('charSpacing', this.value)" style="flex: 1;">
                                <button class="toolbar-btn" onclick="adjustCharSpacing(-0.5)" style="padding: 0.25rem 0.5rem; min-width: 32px;" title="Decrease">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button class="toolbar-btn" onclick="adjustCharSpacing(0.5)" style="padding: 0.25rem 0.5rem; min-width: 32px;" title="Increase">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Text Align</label>
                            <div style="display: flex; gap: 0.25rem;">
                                <button class="toolbar-btn" onclick="updateProperty('textAlign', 'left')" id="alignLeft" style="flex: 1; padding: 0.5rem;" title="Left">
                                    <i class="fas fa-align-left"></i>
                                </button>
                                <button class="toolbar-btn" onclick="updateProperty('textAlign', 'center')" id="alignCenter" style="flex: 1; padding: 0.5rem;" title="Center">
                                    <i class="fas fa-align-center"></i>
                                </button>
                                <button class="toolbar-btn" onclick="updateProperty('textAlign', 'right')" id="alignRight" style="flex: 1; padding: 0.5rem;" title="Right">
                                    <i class="fas fa-align-right"></i>
                                </button>
                                <button class="toolbar-btn" onclick="updateProperty('textAlign', 'justify')" id="alignJustify" style="flex: 1; padding: 0.5rem;" title="Justify">
                                    <i class="fas fa-align-justify"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-2" id="convertToTextboxContainer" style="display: none;">
                            <button class="toolbar-btn" onclick="convertTextToTextbox()" style="width: 100%; padding: 0.5rem; justify-content: center; gap: 0.5rem; background: #6366f1; color: white; border-color: #6366f1;" title="Convert to Textbox">
                                <i class="fas fa-square"></i>
                                <span>Convert to Textbox</span>
                            </button>
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

                <div class="panel-section" id="imageActionsSection" style="display: none;">
                    <div class="panel-title">Image</div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <button type="button" class="toolbar-btn" onclick="if(canvas && canvas.getActiveObject() && canvas.getActiveObject().type === 'image') { openImageCropModal(canvas.getActiveObject()); }" style="width: 100%; padding: 0.5rem 0.75rem; justify-content: center; gap: 0.5rem;">
                            <i class="fas fa-crop-alt"></i>
                            <span>Edit & Crop Image</span>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="if(canvas && canvas.getActiveObject() && canvas.getActiveObject().type === 'image') { contextMenuClipImage(); }" style="width: 100%; padding: 0.5rem 0.75rem; justify-content: center; gap: 0.5rem; background: rgba(99, 102, 241, 0.08); color: #6366f1; border-color: #6366f1;">
                            <i class="fas fa-draw-polygon"></i>
                            <span>Polygon crop</span>
                        </button>
                    </div>
                </div>
                <div class="panel-section">
                    <div class="panel-title">Layer Style</div>
                    <button class="toolbar-btn" onclick="openLayerStyleModal()" style="width: 100%; padding: 0.75rem; margin-top: 0.5rem; justify-content: center; gap: 0.5rem;">
                        <i class="fas fa-palette"></i>
                        <span>Open Layer Style</span>
                    </button>
                </div>
            </div>
            </div>

            <!-- Variables Tab -->
                <div id="variablesTab" class="properties-tab-panel">
                    <div class="panel-section">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div class="panel-title" style="margin: 0;">Variables</div>
                            <button class="btn btn-sm btn-outline-secondary" onclick="refreshVariables()" title="Refresh" style="padding: 0.25rem 0.5rem; border: 1px solid #e2e8f0; background: white; border-radius: 4px; cursor: pointer; color: #64748b;">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <p style="color: #94a3b8; font-size: 0.75rem; margin-bottom: 1rem;">Variables found in text content (e.g., {&#123;variable_name&#125;})</p>
                        <div id="variablesList" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                            <p class="text-muted small text-center" style="padding: 1rem 0; color: #94a3b8; font-size: 0.875rem;">Scanning for variables...</p>
                        </div>
                    </div>
                </div>

            <!-- Styles Tab -->
                <div id="stylesTab" class="properties-tab-panel">
                    <div class="panel-section">
                        <p style="color: #94a3b8; font-size: 0.875rem;" id="noSelectionTextStyles">Select an element to edit styles</p>
                    </div>

                    <div id="elementStyles" style="display: none;">
                        <!-- Opacity -->
                        <div class="panel-section">
                            <div class="panel-title">Opacity</div>
                            <div class="mb-2">
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <input type="range" id="propOpacity" class="form-range" min="0" max="100" step="1" value="100" oninput="updateOpacity(this.value)" style="flex: 1;">
                                    <input type="number" id="propOpacityValue" class="form-control form-control-sm" min="0" max="100" step="1" value="100" onchange="updateOpacity(this.value)" style="width: 70px;">
                                    <span style="font-size: 0.75rem; color: #64748b; min-width: 20px;">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Blend Mode -->
                        <div class="panel-section">
                            <div class="panel-title">Blend Mode</div>
                            <div class="mb-2">
                                <select id="propBlendMode" class="form-select form-select-sm" onchange="updateBlendMode(this.value)">
                                    <option value="normal">Normal</option>
                                    <option value="multiply">Multiply</option>
                                    <option value="screen">Screen</option>
                                    <option value="overlay">Overlay</option>
                                    <option value="darken">Darken</option>
                                    <option value="lighten">Lighten</option>
                                    <option value="color-dodge">Color Dodge</option>
                                    <option value="color-burn">Color Burn</option>
                                    <option value="hard-light">Hard Light</option>
                                    <option value="soft-light">Soft Light</option>
                                    <option value="difference">Difference</option>
                                    <option value="exclusion">Exclusion</option>
                                </select>
                            </div>
                        </div>

                        <!-- Border Radius (for shapes and images) -->
                        <div class="panel-section" id="borderRadiusSection" style="display: none;">
                            <div class="panel-title">Border Radius</div>
                            <div class="mb-2">
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <input type="range" id="propBorderRadius" class="form-range" min="0" max="50" step="1" value="0" oninput="updateBorderRadius(this.value)" style="flex: 1;">
                                    <input type="number" id="propBorderRadiusValue" class="form-control form-control-sm" min="0" max="50" step="1" value="0" onchange="updateBorderRadius(this.value)" style="width: 70px;">
                                    <span style="font-size: 0.75rem; color: #64748b;">px</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stroke (Outline) -->
                        <div class="panel-section">
                            <div class="panel-title">Stroke (Outline)</div>
                            <div class="mb-2">
                                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                                    <label style="font-size: 0.75rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" id="propStrokeEnabled" onchange="toggleStroke()">
                                        <span>Enable Stroke</span>
                                    </label>
                                </div>
                                <div id="stylesStrokeControls" style="display: none;">
                                    <div class="mb-2">
                                        <label class="form-label" style="font-size: 0.75rem;">Stroke Width</label>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <input type="range" id="propStrokeWidth" class="form-range" min="0" max="20" step="0.5" value="1" oninput="updateStrokeWidth(this.value)" style="flex: 1;">
                                            <input type="number" id="propStrokeWidthValue" class="form-control form-control-sm" min="0" max="20" step="0.5" value="1" onchange="updateStrokeWidth(this.value)" style="width: 70px;">
                                            <span style="font-size: 0.75rem; color: #64748b;">px</span>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label" style="font-size: 0.75rem;">Stroke Color</label>
                                        <input type="color" id="propStrokeColor" class="form-control form-control-color" value="#000000" onchange="updateStrokeColor(this.value)">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label" style="font-size: 0.75rem;">Stroke Position</label>
                                        <select id="propStrokePosition" class="form-select form-select-sm" onchange="updateStrokePosition(this.value)">
                                            <option value="center">Center</option>
                                            <option value="inside">Inside</option>
                                            <option value="outside">Outside</option>
                                        </select>
                                        <small style="font-size: 0.7rem; color: #94a3b8; display: block; margin-top: 0.25rem;">Choose where the stroke is drawn</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shadow -->
                        <div class="panel-section">
                            <div class="panel-title">Shadow</div>
                            <div class="mb-2">
                                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                                    <label style="font-size: 0.75rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" id="propShadowEnabled" onchange="toggleShadow()">
                                        <span>Enable Shadow</span>
                                    </label>
                                </div>
                                <div id="stylesShadowControls" style="display: none;">
                                    <div class="mb-2">
                                        <label class="form-label" style="font-size: 0.75rem;">Offset X</label>
                                        <input type="number" id="propShadowOffsetX" class="form-control form-control-sm" min="-50" max="50" value="5" onchange="updateShadow()">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label" style="font-size: 0.75rem;">Offset Y</label>
                                        <input type="number" id="propShadowOffsetY" class="form-control form-control-sm" min="-50" max="50" value="5" onchange="updateShadow()">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label" style="font-size: 0.75rem;">Blur</label>
                                        <input type="number" id="propShadowBlur" class="form-control form-control-sm" min="0" max="50" value="10" onchange="updateShadow()">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label" style="font-size: 0.75rem;">Color</label>
                                        <input type="color" id="propShadowColor" class="form-control form-control-color" value="#000000" onchange="updateShadow()">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edge Feather -->
                        <div class="panel-section">
                            <div class="panel-title">Edge Feather</div>
                            <div class="mb-2">
                                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                                    <label style="font-size: 0.75rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" id="propEdgeFeatherEnabled" onchange="toggleEdgeFeather()">
                                        <span>Enable Edge Feather</span>
                                    </label>
                                </div>
                                <div id="edgeFeatherControls" style="display: none;">
                                    <div class="mb-2">
                                        <label class="form-label" style="font-size: 0.75rem;">Feather Amount</label>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <input type="range" id="propEdgeFeatherAmount" class="form-range" min="0" max="50" step="1" value="10" oninput="updateEdgeFeather(this.value)" style="flex: 1;">
                                            <input type="number" id="propEdgeFeatherAmountValue" class="form-control form-control-sm" min="0" max="50" step="1" value="10" onchange="updateEdgeFeather(this.value)" style="width: 70px;">
                                            <span style="font-size: 0.75rem; color: #64748b;">px</span>
                                        </div>
                                        <small style="font-size: 0.7rem; color: #94a3b8; display: block; margin-top: 0.25rem;">Softens the edges of the object</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Image Color Tab (from modules) -->
                @foreach(app(\App\Services\Module\ModuleRegistry::class)->getImagePropertiesPanels() as $imgPanel)
                @if(\Illuminate\Support\Facades\View::exists($imgPanel['module'] . '::' . $imgPanel['view']))
                <div id="imageColorTab" class="properties-tab-panel">
                    @include($imgPanel['module'] . '::' . $imgPanel['view'])
                </div>
                @endif
                @break
                @endforeach

            <!-- Settings Tab -->
                <div id="settingsTab" class="properties-tab-panel">
                    <div class="panel-section">
                        <div class="panel-title">Canvas Settings</div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Canvas Width</label>
                            <input type="number" id="canvasWidth" class="form-control form-control-sm" value="{{ $mpDefaultCanvasW }}" onchange="updateCanvasSize('width', this.value)">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Canvas Height</label>
                            <input type="number" id="canvasHeight" class="form-control form-control-sm" value="{{ $mpDefaultCanvasH }}" onchange="updateCanvasSize('height', this.value)">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem;">Background Color</label>
                            <input type="color" id="canvasBgColor" class="form-control form-control-color" value="{{ \App\Models\Setting::get('editor_default_bg_color') ?: '#ffffff' }}" onchange="updateCanvasBackground(this.value)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flipbook Save Modal -->
    <div id="flipbookModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);" onclick="if(event.target === this) closeFlipbookModal();">
        <div class="modal-content" style="background-color: white; margin: 1% auto; padding: 0; border-radius: 8px; width: 95%; max-width: 1100px; max-height: 95vh; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: flex; flex-direction: column;" onclick="event.stopPropagation();">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0;">
                <h3 id="flipbookModalTitle" style="margin: 0; font-size: 1.1rem; font-weight: 600;">Save as Flipbook</h3>
                <button onclick="closeFlipbookModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
            </div>

            <!-- Wizard Steps Indicator -->
            <div style="padding: 1rem 1.5rem; background: linear-gradient(to right, #f8fafc 0%, #ffffff 100%); border-bottom: 1px solid #e2e8f0;">
                <div style="display: flex; justify-content: space-between; align-items: center; position: relative;">
                    <!-- Progress Line -->
                    <div style="position: absolute; top: 18px; left: 35px; right: 35px; height: 2px; background: #e2e8f0; z-index: 1;"></div>
                    <div id="flipbookWizardProgress" style="position: absolute; top: 18px; left: 35px; height: 2px; background: #6366f1; z-index: 2; transition: width 0.3s ease; width: 0%;"></div>

                    <!-- Step 1: Basic Info -->
                    <div class="wizard-step" id="wizardStep1" onclick="switchFlipbookTab('basic')" style="position: relative; z-index: 3; cursor: pointer; flex: 1; display: flex; flex-direction: column; align-items: center;">
                        <div class="wizard-step-circle active" style="width: 36px; height: 36px; border-radius: 50%; background: #6366f1; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.8rem; margin-bottom: 0.35rem; box-shadow: 0 2px 4px rgba(99, 102, 241, 0.3);">
                            <span class="wizard-step-number">1</span>
                            <i class="wizard-step-check fas fa-check" style="display: none;"></i>
                        </div>
                        <div class="wizard-step-label" style="font-size: 0.7rem; font-weight: 600; color: #6366f1; text-align: center;">Basic Info</div>
                    </div>

                    <!-- Step 2: Print Settings -->
                    <div class="wizard-step" id="wizardStep2" onclick="switchFlipbookTab('print')" style="position: relative; z-index: 3; cursor: pointer; flex: 1; display: flex; flex-direction: column; align-items: center;">
                        <div class="wizard-step-circle" style="width: 36px; height: 36px; border-radius: 50%; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.8rem; margin-bottom: 0.35rem; transition: all 0.3s;">
                            <span class="wizard-step-number">2</span>
                            <i class="wizard-step-check fas fa-check" style="display: none;"></i>
                        </div>
                        <div class="wizard-step-label" style="font-size: 0.7rem; font-weight: 500; color: #64748b; text-align: center;">Print Settings</div>
                    </div>
                </div>
            </div>

            <form id="flipbookForm" onsubmit="submitFlipbook(event)" style="flex: 1; overflow-y: auto; padding: 0; display: flex;">
                <!-- Left Side: Pricing/Checkout -->
                <div style="width: 40%; border-right: 1px solid #e2e8f0; padding: 1rem 1.5rem; background: #f8fafc; overflow-y: auto;">
                    <h5 style="font-size: 0.9rem; font-weight: 600; margin-bottom: 1rem; color: #475569;">Order Summary</h5>

                    <!-- Saved print settings indicator (shown when flipbook has saved print config) -->
                    <div id="flipbookSavedPrintSettingsBadge" style="display: none; margin-bottom: 0.75rem; padding: 0.5rem 0.75rem; background: #dcfce7; border: 1px solid #86efac; border-radius: 6px; font-size: 0.75rem; color: #166534;">
                        <i class="fas fa-check-circle me-1"></i><span>Showing saved print settings</span>
                    </div>

                    <div id="flipbookPricingSummary" style="background: white; border-radius: 6px; padding: 1rem; border: 1px solid #e2e8f0;">
                        <div style="margin-bottom: 1rem;">
                            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                                <label for="bundleQuantity" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.5rem;">
                                    <i class="fas fa-cube me-1"></i>Bundle Quantity
                                </label>
                                <input type="number" id="bundleQuantity" name="bundleQuantity" min="1" value="1" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.85rem;" onchange="calculateFlipbookPricing()">
                                <small style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem; display: block;">Number of bundles to order</small>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.8rem; color: #64748b;">Pages:</span>
                                <span id="pricingPageCount" style="font-size: 0.8rem; font-weight: 600; color: #475569;">0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.8rem; color: #64748b;">Print Size:</span>
                                <span id="pricingPrintSize" style="font-size: 0.8rem; font-weight: 600; color: #475569;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.8rem; color: #64748b;">Sheet Type:</span>
                                <span id="pricingSheetType" style="font-size: 0.8rem; font-weight: 600; color: #475569;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.8rem; color: #64748b;">Print Quality:</span>
                                <span id="pricingPrintQuality" style="font-size: 0.8rem; font-weight: 600; color: #475569;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.8rem; color: #64748b;">Binding:</span>
                                <span id="pricingBinding" style="font-size: 0.8rem; font-weight: 600; color: #475569;">-</span>
                            </div>
                        </div>

                        <div style="border-top: 1px solid #e2e8f0; padding-top: 1rem; margin-top: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                                <span style="font-size: 0.85rem; color: #64748b;">Per Page Cost:</span>
                                <span id="pricingPerPage" style="font-size: 0.85rem; font-weight: 600; color: #475569;">{{ format_price(0) }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                                <span style="font-size: 0.85rem; color: #64748b;">Subtotal:</span>
                                <span id="pricingSubtotal" style="font-size: 0.85rem; font-weight: 600; color: #475569;">{{ format_price(0) }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                                <span style="font-size: 0.85rem; color: #64748b;">Binding Cost:</span>
                                <span id="pricingBindingCost" style="font-size: 0.85rem; font-weight: 600; color: #475569;">{{ format_price(0) }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                                <span style="font-size: 0.85rem; color: #64748b;">Shipping:</span>
                                <span id="pricingShipping" style="font-size: 0.85rem; font-weight: 600; color: #475569;">{{ format_price(0) }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                                <span style="font-size: 0.85rem; color: #64748b;">Bundle Quantity:</span>
                                <span id="pricingBundleQuantity" style="font-size: 0.85rem; font-weight: 600; color: #475569;">1</span>
                            </div>
                            <div style="border-top: 2px solid #e2e8f0; padding-top: 0.75rem; margin-top: 0.75rem;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="font-size: 1rem; font-weight: 700; color: #1e293b;">Total:</span>
                                    <span id="pricingTotal" style="font-size: 1.1rem; font-weight: 700; color: #6366f1;">{{ format_price(0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 1rem; padding: 0.75rem; background: #fef3c7; border-radius: 6px; border: 1px solid #fde68a;">
                        <p style="font-size: 0.75rem; color: #92400e; margin: 0; line-height: 1.4;">
                            <i class="fas fa-info-circle me-1"></i>
                            Pricing is calculated based on your selected options. Final cost may vary.
                        </p>
                    </div>
                </div>

                <!-- Right Side: Form -->
                <div style="width: 60%; padding: 1rem 1.5rem; overflow-y: auto;">
                <!-- Basic Info Tab -->
                <div id="flipbookTabBasicContent" class="flipbook-tab-content active" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="mb-2" style="grid-column: 1 / -1;">
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0.25rem; display: block;">Flipbook Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="flipbookTitle" class="form-control" required placeholder="Enter flipbook name" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.8rem;">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0.25rem; display: block;">Pages Count</label>
                        <div style="padding: 0.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; display: flex; align-items: center; gap: 0.4rem;">
                            <i class="fas fa-file-alt" style="color: #6366f1; font-size: 0.75rem;"></i>
                            <span id="flipbookPagesCount" style="font-weight: 600; color: #475569; font-size: 0.8rem;">0 pages</span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0.25rem; display: block;">Status</label>
                        <select id="flipbookStatus" class="form-control" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.8rem;">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                    <div class="mb-2" style="grid-column: 1 / -1;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <label class="form-label" style="font-size: 0.75rem; font-weight: 500; margin: 0;">Description</label>
                            <button type="button" id="generateDescriptionBtn" onclick="generateFlipbookDescription()" class="btn btn-sm btn-outline-primary" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                                <i class="fas fa-magic me-1"></i>Generate
                            </button>
                        </div>
                        <textarea id="flipbookDescription" class="form-control" rows="2" placeholder="Enter flipbook description (optional)" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px; resize: vertical; font-size: 0.8rem;"></textarea>
                    </div>
                    <div class="mb-2" style="grid-column: 1 / -1; display: flex; align-items: center; gap: 0.4rem; padding-top: 0.25rem;">
                        <input type="checkbox" id="flipbookPublic" style="width: 16px; height: 16px; cursor: pointer;">
                        <label for="flipbookPublic" style="font-size: 0.75rem; cursor: pointer; margin: 0;">Make this flipbook public</label>
                    </div>
                </div>

                <!-- Print Settings Tab -->
                <div id="flipbookTabPrintContent" class="flipbook-tab-content" style="display: none; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0.25rem; display: block;">Print Sheet Type <span style="color: #ef4444;">*</span></label>
                        <select id="flipbookPrintSheetType" class="form-control" required style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.8rem;" onchange="playFlipbookSheetTypeVideo(); calculateFlipbookPricing();">
                            <option value="">Select sheet type</option>
                            @forelse($sheetTypes ?? [] as $sheetType)
                                <option value="{{ $sheetType->slug }}">{{ $sheetType->name }}</option>
                            @empty
                                <option value="glossy">Glossy</option>
                                <option value="matte">Matte</option>
                                <option value="satin">Satin</option>
                                <option value="textured">Textured</option>
                                <option value="standard">Standard</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0.25rem; display: block;">Print Size <span style="color: #ef4444;">*</span></label>
                        <select id="flipbookPrintSize" class="form-control" required style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.8rem;">
                            <option value="">Select print size</option>
                            <option value="A4">A4 (210 × 297 mm)</option>
                            <option value="A5">A5 (148 × 210 mm)</option>
                            <option value="A3">A3 (297 × 420 mm)</option>
                            <option value="Letter">Letter (8.5 × 11 inches)</option>
                            <option value="Legal">Legal (8.5 × 14 inches)</option>
                            <option value="Custom">Custom Size</option>
                        </select>
                    </div>
                    <div id="flipbookCustomSizeContainer" style="display: none; grid-column: 1 / -1; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0.25rem; display: block;">Custom Width (mm)</label>
                            <input type="number" id="flipbookCustomWidth" class="form-control" min="50" max="1000" placeholder="Width in mm" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.8rem;">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0.25rem; display: block;">Custom Height (mm)</label>
                            <input type="number" id="flipbookCustomHeight" class="form-control" min="50" max="1000" placeholder="Height in mm" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.8rem;">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0.25rem; display: block;">Print Quality</label>
                        <select id="flipbookPrintQuality" class="form-control" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.8rem;">
                            <option value="standard">Standard (300 DPI)</option>
                            <option value="high">High (600 DPI)</option>
                            <option value="premium">Premium (1200 DPI)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0.25rem; display: block;">Binding Type</label>
                        <select id="flipbookBindingType" class="form-control" style="width: 100%; padding: 0.4rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.8rem;">
                            <option value="spiral">Spiral Binding</option>
                            <option value="perfect">Perfect Binding</option>
                            <option value="saddle">Saddle Stitch</option>
                            <option value="wire">Wire-O Binding</option>
                            <option value="none">No Binding</option>
                        </select>
                    </div>
                    <!-- Sheet Type Video & Description (spans full width) -->
                    <div style="grid-column: 1 / -1; margin-top: 0.75rem; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #f8fafc; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0; min-height: 200px;">
                            <div style="position: relative; background: linear-gradient(135deg, #1e293b 0%, #334155 100%); aspect-ratio: 16/10; min-height: 200px;">
                                <video id="flipbookSheetTypeVideo" playsinline muted loop preload="metadata" style="width: 100%; height: 100%; object-fit: cover; display: none;"></video>
                                <div id="flipbookSheetTypeVideoPlaceholder" style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.7); font-size: 0.8125rem;">
                                    <span><i class="fas fa-play-circle" style="font-size: 2.5rem; margin-right: 0.5rem; opacity: 0.6;"></i>Select a sheet type to preview</span>
                                </div>
                            </div>
                            <div style="padding: 1rem; overflow-y: auto; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 1px solid #e2e8f0;">
                                <div style="font-weight: 700; color: #1e293b; font-size: 0.875rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-layer-group" style="color: #6366f1; font-size: 0.8rem;"></i>
                                    <span id="flipbookSheetTypeName">—</span>
                                </div>
                                <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.03em;">About this sheet type</div>
                                <div id="flipbookSheetTypeDescription" style="font-size: 0.8125rem; color: #475569; line-height: 1.6;">Select a sheet type to view its description and video preview.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="flipbookProgress" style="display: none; margin-top: 0.75rem; padding: 0.75rem; background: #f8fafc; border-radius: 4px;">
                    <p style="margin: 0 0 0.4rem 0; font-size: 0.75rem; color: #64748b;">Processing...</p>
                    <div style="width: 100%; height: 3px; background: #e2e8f0; border-radius: 2px; overflow: hidden;">
                        <div id="flipbookProgressBar" style="height: 100%; background: #6366f1; width: 0%; transition: width 0.3s;"></div>
                    </div>
                    <p id="flipbookProgressText" style="margin: 0.4rem 0 0 0; font-size: 0.7rem; color: #64748b;"></p>
                </div>

                </div>
            </form>

            <!-- Footer buttons on right side inside modal -->
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; padding: 0.75rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" class="flipbook-tab-nav-btn" onclick="flipbookTabPrevious()" id="flipbookTabPrevBtn" style="display: none; padding: 0.5rem 1rem; font-weight: 500; font-size: 0.8rem;">
                    <i class="fas fa-arrow-left me-2"></i>Previous
                </button>
                <button type="button" onclick="closeFlipbookModal()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; font-size: 0.8rem;">Cancel</button>
                <button type="button" class="flipbook-tab-nav-btn" onclick="flipbookTabNext()" id="flipbookTabNextBtn" style="padding: 0.5rem 1rem; background: #6366f1; color: white; border-color: #6366f1; font-weight: 500; font-size: 0.8rem;">
                    Next <i class="fas fa-arrow-right ms-2"></i>
                </button>
                <button type="button" onclick="document.getElementById('flipbookForm').requestSubmit();" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #6366f1; color: white; border-color: #6366f1; font-weight: 500; display: none; font-size: 0.8rem;" id="flipbookSubmitBtn">
                    <i class="fas fa-save me-2"></i>Save Flipbook
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Template Save Modal - Large with Variables Sidebar -->
<div id="templateModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);" onclick="if(event.target === this) closeTemplateModal();">
    <div class="modal-content" style="background-color: white; margin: 2% auto; padding: 0; border-radius: 10px; width: 96%; max-width: 1100px; max-height: 92vh; box-shadow: 0 10px 40px rgba(0,0,0,0.2); display: flex; flex-direction: column; overflow: hidden;" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0; flex-shrink: 0;">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1e293b;">Save as Template</h3>
            <button onclick="closeTemplateModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b; padding: 0.15rem 0.35rem; line-height: 1;">&times;</button>
        </div>

        <!-- Modal Body - Split Layout -->
        <div style="display: flex; flex: 1; overflow: hidden; min-height: 0; max-height: calc(92vh - 60px);">
            <!-- Left Side - Form (scrollable) -->
            <div class="template-modal-scroll" style="flex: 1; min-height: 0; padding: 1rem 1.25rem; overflow-y: auto; overflow-x: hidden; border-right: 1px solid #e2e8f0;">
                <form id="templateForm" onsubmit="submitTemplate(event)">
                    <!-- Template Type: letter, document, flipbook, etc. -->
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: #1e293b;">Template Type</label>
                        <select id="templateTypeSelect" class="form-control" onchange="toggleTemplateTypeOptions()" style="width: 100%; padding: 0.5rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8125rem;">
                            <option value="document">Document</option>
                            <option value="letter">Letter</option>
                            <option value="flipbook">Flipbook</option>
                            <option value="brochure">Brochure</option>
                            <option value="catalog">Catalog</option>
                            <option value="presentation">Presentation</option>
                            <option value="other">Other</option>
                        </select>
                        <small style="font-size: 0.65rem; color: #64748b;">Affects checkout options (e.g. envelope for letters)</small>
                    </div>

                    <!-- Public/Private Toggle (only for approved designers) -->
                    <div class="mb-3" style="padding: 0.75rem; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <label style="font-size: 0.8125rem; font-weight: 600; color: #1e293b; margin-bottom: 0.15rem; display: block;">Visibility</label>
                        @if($canSavePublicTemplate ?? false)
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <p style="font-size: 0.7rem; color: #64748b; margin: 0;">Public (explore/sell) or Private (my templates only)</p>
                            <label style="position: relative; display: inline-block; width: 48px; height: 26px; margin: 0; cursor: pointer;">
                                <input type="checkbox" id="templateIsPublic" onchange="toggleTemplateType()" style="opacity: 0; width: 0; height: 0;">
                                <span class="template-toggle-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: 0.3s; border-radius: 26px;">
                                    <span style="position: absolute; content: ''; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: 0.3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></span>
                                </span>
                            </label>
                        </div>
                        <div id="templateTypeLabel" style="margin-top: 0.5rem; padding: 0.4rem 0.6rem; background: white; border-radius: 5px; font-size: 0.75rem; color: #64748b;">
                            <i class="fas fa-lock" style="margin-right: 0.4rem; color: #94a3b8;"></i>
                            <span id="templateTypeText">My Template (Private)</span>
                        </div>
                        @else
                        <div id="templateTypeLabel" style="margin-top: 0.35rem; padding: 0.5rem; background: white; border-radius: 5px; font-size: 0.75rem; color: #64748b;">
                            <i class="fas fa-lock" style="margin-right: 0.4rem; color: #94a3b8;"></i>
                            <span id="templateTypeText">My Template (Private)</span>
                        </div>
                        <p style="font-size: 0.7rem; color: #64748b; margin: 0.5rem 0 0 0;">Want to sell public templates? <a href="{{ route('designer-application.index') }}" target="_blank" style="color: #6366f1; font-weight: 500;">Apply to become a designer</a>.</p>
                        <input type="checkbox" id="templateIsPublic" style="display: none;">
                        @endif
                    </div>

                    <!-- Template as Product Toggle (only shown when Visibility = Public) -->
                    <div id="templateAsProductWrap" class="mb-3" style="display: none; padding: 0.75rem; background: #f0fdf4; border-radius: 6px; border: 1px solid #bbf7d0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <label style="font-size: 0.8125rem; font-weight: 600; color: #1e293b; margin-bottom: 0.15rem; display: block;">Template as Product</label>
                                <p style="font-size: 0.7rem; color: #64748b; margin: 0;">Sell this template as a product with price and stock</p>
                            </div>
                            <label style="position: relative; display: inline-block; width: 48px; height: 26px; margin: 0; cursor: pointer;">
                                <input type="checkbox" id="templateIsProduct" onchange="toggleTemplateAsProduct()" style="opacity: 0; width: 0; height: 0;">
                                <span class="template-toggle-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: 0.3s; border-radius: 26px;">
                                    <span style="position: absolute; content: ''; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: 0.3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></span>
                                </span>
                            </label>
                        </div>
                        <div id="templateProductFields" style="display: none; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #bbf7d0;">
                            <div class="mb-2">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <label style="font-size: 0.75rem; font-weight: 600; color: #1e293b;">Stock</label>
                                    <label style="position: relative; display: inline-flex; align-items: center; gap: 0.35rem; cursor: pointer; font-size: 0.75rem; color: #475569;">
                                        <input type="checkbox" id="templateStockEnabled" onchange="toggleTemplateStock()" style="width: 14px; height: 14px;">
                                        Enable stock
                                    </label>
                                </div>
                                <div id="templateStockQtyWrap" style="display: none;">
                                    <input type="number" id="templateStockQty" class="form-control" min="0" placeholder="Quantity" style="width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8125rem;">
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label style="font-size: 0.75rem; font-weight: 600; color: #1e293b; display: block; margin-bottom: 0.25rem;">Price ($) <span style="color: #ef4444;">*</span></label>
                                    <input type="number" id="templateSellingPrice" class="form-control" step="0.01" min="0" placeholder="0.00" style="width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8125rem;">
                                </div>
                                <div class="col-6">
                                    <label style="font-size: 0.75rem; font-weight: 600; color: #1e293b; display: block; margin-bottom: 0.25rem;">Cost ($)</label>
                                    <input type="number" id="templateCost" class="form-control" step="0.01" min="0" placeholder="0.00" style="width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8125rem;">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label style="font-size: 0.75rem; font-weight: 600; color: #1e293b; display: block; margin-bottom: 0.25rem;">Licence <span style="color: #ef4444;">*</span></label>
                                <select id="templateProductLicence" class="form-control" style="width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8125rem;">
                                    <option value="">Loading...</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label style="font-size: 0.75rem; font-weight: 600; color: #1e293b; display: block; margin-bottom: 0.25rem;">Product Description</label>
                                <textarea id="templateProductDescription" class="form-control" rows="2" placeholder="Optional product description" style="width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; resize: vertical; font-size: 0.8125rem;"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Template Name - Always Required -->
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: #1e293b;">Template Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="templateName" class="form-control" required placeholder="Enter template name" style="width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8125rem;">
                    </div>

                    <!-- Public Template Fields (Hidden by default) -->
                    <div id="publicTemplateFields" style="display: none;">
                        <div class="mb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <label class="form-label mb-0" style="font-size: 0.75rem; font-weight: 600; color: #1e293b;">Descriptions</label>
                            <button type="button" id="generateTemplateDescriptionsBtn" class="btn btn-sm btn-outline-primary" onclick="generateTemplateDescriptions()" title="Generate short and full description with AI">
                                <i class="fas fa-magic me-1"></i>Generate descriptions
                            </button>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: #1e293b;">Short Description <span style="color: #ef4444;">*</span></label>
                            <textarea id="templateShortDescription" class="form-control" rows="2" placeholder="Brief description (max 200 characters)" maxlength="200" oninput="updateShortDescCount()" style="width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; resize: vertical; font-size: 0.8125rem;"></textarea>
                            <small style="color: #64748b; font-size: 0.65rem;" id="shortDescCount">0/200 characters</small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: #1e293b;">Full Description</label>
                            <textarea id="templateDescription" class="form-control" rows="3" placeholder="Detailed description of the template" style="width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; resize: vertical; font-size: 0.8125rem;"></textarea>
                        </div>

                            <div class="row mb-2 g-2">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: #1e293b;">Category <span style="color: #ef4444;">*</span></label>
                                    <select id="templateCategory" class="form-control" style="width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8125rem;">
                                        <option value="">Select Category</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="publicTemplatePriceWrap">
                                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: #1e293b;">Price ($) <span style="color: #ef4444;">*</span></label>
                                    <input type="number" id="templatePrice" class="form-control" step="0.01" min="0" placeholder="0.00" style="width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8125rem;">
                                </div>
                            </div>

                        <div class="mb-2" id="publicTemplateLicenceWrap">
                            <label class="form-label" style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: #1e293b;">Licence <span style="color: #ef4444;">*</span></label>
                            <select id="templateLicence" class="form-control" style="width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8125rem;">
                                <option value="">Loading...</option>
                            </select>
                        </div>

                        <!-- Multiple Images Upload -->
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: #1e293b;">Template Images</label>
                            <p style="font-size: 0.7rem; color: #64748b; margin-bottom: 0.5rem;">Upload multiple images (optional)</p>
                            <input type="file" id="templateImages" multiple accept="image/*" onchange="handleTemplateImagesUpload(event)" style="display: none;">
                            <button type="button" onclick="document.getElementById('templateImages').click()" class="toolbar-btn" style="width: 100%; padding: 0.5rem; background: #f1f5f9; color: #475569; border: 2px dashed #e2e8f0; border-radius: 6px; font-weight: 500; cursor: pointer; font-size: 0.75rem;">
                                <i class="fas fa-upload" style="margin-right: 0.4rem;"></i>Upload Images
                            </button>
                            <div id="templateImagesPreview" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 0.5rem; margin-top: 0.5rem;"></div>
                        </div>
                    </div>

                    <!-- Advanced Options -->
                    <div class="mb-2 mt-2" style="border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                        <button type="button" id="templateAdvancedToggle" onclick="toggleTemplateAdvanced()" style="width: 100%; padding: 0.5rem 0.75rem; background: #f8fafc; border: none; text-align: left; font-size: 0.8125rem; font-weight: 600; color: #475569; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                            <span><i class="fas fa-cog me-2" style="color: #6366f1;"></i>Advanced Options</span>
                            <i class="fas fa-chevron-down" id="templateAdvancedIcon" style="font-size: 0.75rem; transition: transform 0.2s;"></i>
                        </button>
                        <div id="templateAdvancedContent" style="display: none; padding: 0.75rem; background: white; border-top: 1px solid #e2e8f0;">
                            <div class="mb-2">
                                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: #1e293b;">Thumbnail Page</label>
                                <select id="templateThumbnailPage" style="width: 100%; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8125rem;">
                                    <option value="1">Page 1</option>
                                </select>
                                <small style="font-size: 0.65rem; color: #64748b;">Which page to use for template preview</small>
                            </div>
                            <div class="mb-2">
                                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: #1e293b;">Tags</label>
                                <div id="templateTagsWrap" style="min-height: 38px; padding: 0.35rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem;">
                                    <div id="templateTagsChips" style="display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center;"></div>
                                    <input type="text" id="templateTagsInput" placeholder="Type and press comma to add tag" style="flex: 1; min-width: 120px; border: none; outline: none; padding: 0.2rem 0; font-size: 0.8125rem;">
                                </div>
                                <input type="hidden" id="templateTags" name="templateTags">
                                <small style="font-size: 0.65rem; color: #64748b;">Type a tag and press comma or Enter to add</small>
                            </div>
                            <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0;">
                                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem; display: block; color: #1e293b;">Checkout / Options</label>
                                <div class="mb-1" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="checkbox" id="templateDisableSheetSelection" style="width: 14px; height: 14px; cursor: pointer;">
                                    <label for="templateDisableSheetSelection" style="font-size: 0.75rem; font-weight: 500; color: #475569; cursor: pointer; margin: 0;">Disable sheet selection at checkout</label>
                                </div>
                                <div class="mb-1" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="checkbox" id="templateDisableMaterialSelection" style="width: 14px; height: 14px; cursor: pointer;">
                                    <label for="templateDisableMaterialSelection" style="font-size: 0.75rem; font-weight: 500; color: #475569; cursor: pointer; margin: 0;">Disable material selection at checkout</label>
                                </div>
                                <div id="templateEnvelopeOptionWrap" class="mb-0" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="checkbox" id="templateDisableEnvelopeOption" style="width: 14px; height: 14px; cursor: pointer;">
                                    <label for="templateDisableEnvelopeOption" style="font-size: 0.75rem; font-weight: 500; color: #475569; cursor: pointer; margin: 0;">Disable envelope option at checkout (letters)</label>
                                </div>
                                <small style="font-size: 0.65rem; color: #64748b;">Envelope option only applies when template type is Letter</small>
                            </div>
                            @if($canSavePublicTemplate ?? false)
                            <div class="mb-0 mt-2" style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" id="templateIsFeatured" style="width: 14px; height: 14px; cursor: pointer;">
                                <label for="templateIsFeatured" style="font-size: 0.75rem; font-weight: 500; color: #475569; cursor: pointer; margin: 0;">Mark as featured</label>
                            </div>
                            <small style="font-size: 0.65rem; color: #64748b;">Featured templates appear prominently on explore page</small>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                        <button type="button" onclick="closeTemplateModal()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 6px; font-weight: 600; font-size: 0.8125rem;">Cancel</button>
                        <button type="submit" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #6366f1; color: white; border: 1px solid #6366f1; border-radius: 6px; font-weight: 600; font-size: 0.8125rem;" id="templateSubmitBtn">Save Template</button>
                    </div>
                </form>
            </div>

            <!-- Right Side - Variables Panel (scrollable) -->
            <div class="template-modal-scroll" style="width: 220px; min-height: 0; background: #f8fafc; padding: 1rem; overflow-y: auto; overflow-x: hidden; flex-shrink: 0; border-left: 1px solid #e2e8f0;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 0.875rem; font-weight: 700; color: #1e293b; display: flex; align-items: center;">
                    <i class="fas fa-code me-1" style="color: #6366f1; font-size: 0.75rem;"></i>Template Variables
                </h4>
                <p style="font-size: 0.7rem; color: #64748b; margin-bottom: 0.75rem; line-height: 1.4;">Variables found in your template that can be customized:</p>
                <div id="templateVariablesList" style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <div style="padding: 0.6rem; background: white; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div style="font-size: 0.65rem; color: #64748b; margin-bottom: 0.25rem;">No variables detected yet</div>
                        <div style="font-size: 0.7rem; color: #94a3b8;">Variables will appear here after analyzing your template pages.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Watermark Configuration Modal -->
<div id="watermarkModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);" onclick="if(event.target === this) closeWatermarkModal();">
    <div class="modal-content" style="background-color: white; margin: 5% auto; padding: 2rem; border-radius: 8px; width: 90%; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto;" onclick="event.stopPropagation();">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Export PDF with Watermark</h3>
            <button onclick="closeWatermarkModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        <form id="watermarkForm" onsubmit="exportPDFWithWatermark(event)">
            <div class="mb-3">
                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; display: block;">Watermark Text <span style="color: #ef4444;">*</span></label>
                <input type="text" id="watermarkText" class="form-control" required placeholder="e.g., DRAFT, CONFIDENTIAL, © 2024 Company" value="DRAFT" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px;">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; display: block;">Font Size (pt)</label>
                <input type="number" id="watermarkFontSize" class="form-control" min="10" max="200" value="72" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px;">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; display: block;">Opacity (0-1)</label>
                <input type="number" id="watermarkOpacity" class="form-control" min="0" max="1" step="0.1" value="0.3" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px;">
                <small style="color: #64748b; font-size: 0.75rem;">0 = transparent, 1 = fully opaque</small>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; display: block;">Rotation (degrees)</label>
                <input type="number" id="watermarkRotation" class="form-control" min="-180" max="180" value="-45" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px;">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; display: block;">Position</label>
                <select id="watermarkPosition" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px;">
                    <option value="center">Center</option>
                    <option value="top-left">Top Left</option>
                    <option value="top-right">Top Right</option>
                    <option value="bottom-left">Bottom Left</option>
                    <option value="bottom-right">Bottom Right</option>
                    <option value="top-center">Top Center</option>
                    <option value="bottom-center">Bottom Center</option>
                    <option value="custom">Custom (X, Y coordinates)</option>
                </select>
            </div>
            <div class="mb-3" id="customPositionContainer" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; display: block;">X Position (mm)</label>
                        <input type="number" id="watermarkX" class="form-control" value="0" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; display: block;">Y Position (mm)</label>
                        <input type="number" id="watermarkY" class="form-control" value="0" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px;">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; display: block;">Color</label>
                <input type="color" id="watermarkColor" value="#000000" style="width: 100%; height: 40px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; display: block;">Font Style</label>
                <select id="watermarkFontStyle" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px;">
                    <option value="normal">Normal</option>
                    <option value="bold">Bold</option>
                    <option value="italic">Italic</option>
                    <option value="bolditalic">Bold Italic</option>
                </select>
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" onclick="closeWatermarkModal()" class="toolbar-btn" style="padding: 0.5rem 1rem;">Cancel</button>
                <button type="submit" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #6366f1; color: white; border-color: #6366f1;">Export PDF</button>
            </div>
        </form>
    </div>
</div>

<!-- Image Crop/Edit Modal -->
<div id="imageCropModal" class="modal" style="display: none; position: fixed; z-index: 10001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7);" onclick="if(event.target === this) closeImageCropModal();">
    <div class="modal-content" style="background-color: white; margin: 2% auto; padding: 0; border-radius: 8px; width: 95%; max-width: 1200px; height: 90vh; max-height: 900px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); display: flex; flex-direction: column; overflow: hidden;" onclick="event.stopPropagation();">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #e2e8f0;">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #1e293b;">
                <i class="fas fa-crop-alt" style="margin-right: 0.5rem; color: #6366f1;"></i>
                Edit & Crop Image
            </h3>
            <button onclick="closeImageCropModal()" style="background: none; border: none; font-size: 1.75rem; cursor: pointer; color: #64748b; padding: 0.25rem 0.5rem; line-height: 1;">&times;</button>
        </div>

        <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden; padding: 1.5rem;">
            <!-- Image Container with Cropper -->
            <div style="flex: 1; display: flex; align-items: center; justify-content: center; background: #f8fafc; border-radius: 8px; overflow: hidden; position: relative; min-height: 400px;">
                <img id="cropImage" style="max-width: 100%; max-height: 100%; display: block;">
            </div>

            <!-- Crop Controls -->
            <div style="margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                    <button type="button" onclick="resetCrop()" class="toolbar-btn" style="padding: 0.5rem 1rem;">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                    <button type="button" onclick="rotateCrop(-90)" class="toolbar-btn" style="padding: 0.5rem 1rem;">
                        <i class="fas fa-redo fa-flip-horizontal"></i> Rotate Left
                    </button>
                    <button type="button" onclick="rotateCrop(90)" class="toolbar-btn" style="padding: 0.5rem 1rem;">
                        <i class="fas fa-redo"></i> Rotate Right
                    </button>
                    <button type="button" onclick="flipCrop('horizontal')" class="toolbar-btn" style="padding: 0.5rem 1rem;">
                        <i class="fas fa-arrows-alt-h"></i> Flip H
                    </button>
                    <button type="button" onclick="flipCrop('vertical')" class="toolbar-btn" style="padding: 0.5rem 1rem;">
                        <i class="fas fa-arrows-alt-v"></i> Flip V
                    </button>
                    <div style="flex: 1;"></div>
                    <button type="button" onclick="closeImageCropModal()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #f1f5f9; color: #475569; border: 2px solid #e2e8f0;">Cancel</button>
                    <button type="button" onclick="applyCrop()" class="toolbar-btn" style="padding: 0.5rem 1.5rem; background: #6366f1; color: white; border: none; font-weight: 600;">
                        <i class="fas fa-check"></i> Apply Crop
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Editor Modal -->
<div id="tableEditorModal" class="modal" style="display: none; position: fixed; z-index: 10002; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7);" onclick="if(event.target === this) closeTableEditorModal();">
    <div class="modal-content" style="background-color: white; margin: 2% auto; padding: 0; border-radius: 8px; width: 95%; max-width: 1000px; height: 90vh; max-height: 800px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); display: flex; flex-direction: column; overflow: hidden;" onclick="event.stopPropagation();">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #e2e8f0;">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #1e293b;">
                <i class="fas fa-table" style="margin-right: 0.5rem; color: #6366f1;"></i>
                Edit Table
            </h3>
            <button onclick="closeTableEditorModal()" style="background: none; border: none; font-size: 1.75rem; cursor: pointer; color: #64748b; padding: 0.25rem 0.5rem; line-height: 1;">&times;</button>
        </div>

        <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden; padding: 1.5rem;">
            <!-- Table Controls -->
            <div style="margin-bottom: 1rem; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <label style="font-size: 0.875rem; color: #475569; font-weight: 500;">Rows:</label>
                        <input type="number" id="tableRowsInput" min="1" max="20" value="3" style="width: 60px; padding: 0.4rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.875rem;" onchange="updateTableStructure()">
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <label style="font-size: 0.875rem; color: #475569; font-weight: 500;">Columns:</label>
                        <input type="number" id="tableColsInput" min="1" max="20" value="3" style="width: 60px; padding: 0.4rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.875rem;" onchange="updateTableStructure()">
                    </div>
                    <div style="flex: 1;"></div>
                    <button type="button" onclick="addTableRow()" class="toolbar-btn" style="padding: 0.5rem 1rem;">
                        <i class="fas fa-plus"></i> Add Row
                    </button>
                    <button type="button" onclick="addTableColumn()" class="toolbar-btn" style="padding: 0.5rem 1rem;">
                        <i class="fas fa-plus"></i> Add Column
                    </button>
                    <button type="button" onclick="removeTableRow()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #f1f5f9; color: #475569;">
                        <i class="fas fa-minus"></i> Remove Row
                    </button>
                    <button type="button" onclick="removeTableColumn()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #f1f5f9; color: #475569;">
                        <i class="fas fa-minus"></i> Remove Column
                    </button>
                </div>
            </div>

            <!-- Table Editor -->
            <div style="flex: 1; overflow: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: white;">
                <table id="tableEditor" style="width: 100%; border-collapse: collapse; min-width: 100%;">
                    <thead id="tableEditorHeader" style="background: #f3f4f6;">
                        <!-- Header rows will be generated here -->
                    </thead>
                    <tbody id="tableEditorBody">
                        <!-- Table body rows will be generated here -->
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; justify-content: flex-end; padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            <button type="button" onclick="closeTableEditorModal()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #f1f5f9; color: #475569; border: 2px solid #e2e8f0;">Cancel</button>
            <button type="button" onclick="applyTableChanges()" class="toolbar-btn" style="padding: 0.5rem 1.5rem; background: #6366f1; color: white; border: none; font-weight: 600;">
                <i class="fas fa-check"></i> Apply Changes
            </button>
        </div>
    </div>
</div>

<!-- Image Clipping Path Modal (Polygon Image Cutter) -->
<div id="imageClippingModal" class="modal" style="display: none; position: fixed; z-index: 10003; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7);" onclick="if(event.target === this) closeImageClippingModal();">
    <div class="modal-content" style="background-color: white; margin: 2% auto; padding: 0; border-radius: 8px; width: 95%; max-width: 1200px; height: 90vh; max-height: 900px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); display: flex; flex-direction: column; overflow: hidden;" onclick="event.stopPropagation();">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #e2e8f0;">
            <div>
                <h3 style="margin: 0 0 0.25rem 0; font-size: 1.25rem; font-weight: 600; color: #1e293b;">
                    <i class="fas fa-draw-polygon" style="margin-right: 0.5rem; color: #6366f1;"></i>
                    Polygon Image Cutter
                </h3>
                <p style="margin: 0; font-size: 0.8125rem; color: #64748b;">Draw a shape to show only that part of the image. <strong>Polygon:</strong> click to add points, click the first point again to close. <strong>Pen:</strong> freehand draw.</p>
            </div>
            <button onclick="closeImageClippingModal()" style="background: none; border: none; font-size: 1.75rem; cursor: pointer; color: #64748b; padding: 0.25rem 0.5rem; line-height: 1;">&times;</button>
        </div>

        <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden; padding: 1.5rem;">
            <!-- Drawing Tools -->
            <div style="margin-bottom: 1rem; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                    <button type="button" onclick="setClippingTool('polygon')" id="clippingToolPolygon" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #6366f1; color: white;">
                        <i class="fas fa-draw-polygon"></i> Polygon
                    </button>
                    <button type="button" onclick="setClippingTool('pen')" id="clippingToolPen" class="toolbar-btn" style="padding: 0.5rem 1rem;">
                        <i class="fas fa-pen"></i> Pen Tool
                    </button>
                    <button type="button" onclick="clearClippingPath()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #f1f5f9; color: #475569;">
                        <i class="fas fa-undo"></i> Clear
                    </button>
                    <div style="flex: 1;"></div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <label style="font-size: 0.875rem; color: #475569; font-weight: 500;">Brush Size:</label>
                        <input type="range" id="clippingBrushSize" min="1" max="20" value="3" oninput="updateClippingBrushSize(this.value)" style="width: 100px;">
                        <span id="clippingBrushSizeValue" style="font-size: 0.875rem; color: #64748b; min-width: 30px;">3px</span>
                    </div>
                </div>
            </div>

            <!-- Canvas Container -->
            <div style="flex: 1; display: flex; align-items: center; justify-content: center; background: #f8fafc; border-radius: 8px; overflow: hidden; position: relative; min-height: 400px;">
                <canvas id="clippingCanvas" style="max-width: 100%; max-height: 100%; cursor: crosshair; border: 1px solid #e2e8f0;"></canvas>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; justify-content: flex-end; padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            <button type="button" onclick="closeImageClippingModal()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #f1f5f9; color: #475569; border: 2px solid #e2e8f0;">Cancel</button>
            <button type="button" onclick="applyClippingPath()" class="toolbar-btn" style="padding: 0.5rem 1.5rem; background: #6366f1; color: white; border: none; font-weight: 600;">
                <i class="fas fa-check"></i> Apply Clipping
            </button>
        </div>
    </div>
</div>

<!-- AI Generate Design Modal -->
<div id="aiGenerateModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);" onclick="if(event.target === this) closeAIGenerateModal();">
    <div class="modal-content" style="background-color: white; margin: 5% auto; padding: 2rem; border-radius: 8px; width: 90%; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto;" onclick="event.stopPropagation();">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Generate Design by AI</h3>
            <button onclick="closeAIGenerateModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form id="aiGenerateForm" onsubmit="generateAIDesign(event); return false;">
            <div style="margin-bottom: 1.5rem;">
                <label for="aiPrompt" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b;">Describe your design:</label>
                <textarea
                    id="aiPrompt"
                    name="prompt"
                    rows="5"
                    placeholder="Example: Create a modern business card with company logo, name, title, email, and phone number. Use blue and white colors with a professional layout."
                    required
                    style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.875rem; font-family: inherit; resize: vertical;"
                ></textarea>
                <small style="display: block; margin-top: 0.5rem; color: #64748b; font-size: 0.75rem;">
                    Be as descriptive as possible. Include details about colors, layout, text content, and style.
                </small>
            </div>

            <div id="aiGenerateStatus" style="display: none; padding: 1rem; background: #f1f5f9; border-radius: 4px; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div class="spinner" style="border: 2px solid #e2e8f0; border-top: 2px solid #6366f1; border-radius: 50%; width: 20px; height: 20px; animation: spin 1s linear infinite;"></div>
                    <span id="aiGenerateStatusText">Generating your design...</span>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeAIGenerateModal()" class="toolbar-btn" style="padding: 0.5rem 1rem;">Cancel</button>
                <button type="submit" class="toolbar-btn" style="padding: 0.5rem 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;" id="aiGenerateSubmitBtn">
                    <i class="fas fa-magic"></i> Generate Design
                </button>
            </div>
        </form>
    </div>
</div>

<!-- AI Generate Text Content Modal (for selected text element) -->
<div id="aiGenerateTextModal" class="modal" style="display: none; position: fixed; z-index: 10004; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);" onclick="if(event.target === this) closeAIGenerateTextModal();">
    <div class="modal-content" style="background-color: white; margin: 5% auto; padding: 2rem; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto;" onclick="event.stopPropagation();">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Generate content using AI</h3>
            <button onclick="closeAIGenerateTextModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form id="aiGenerateTextForm" onsubmit="generateTextContentForElement(event); return false;">
            <div style="margin-bottom: 1rem;">
                <label for="aiGenerateTextPrompt" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b;">Describe what you want to generate:</label>
                <textarea
                    id="aiGenerateTextPrompt"
                    name="prompt"
                    rows="4"
                    placeholder="Example: Write a catchy headline for a coffee shop, Write a short product description for organic soap"
                    required
                    style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.875rem; font-family: inherit; resize: vertical;"
                ></textarea>
            </div>
            <div id="aiGenerateTextCurrentText" style="display: none; margin-bottom: 1rem; padding: 0.5rem; background: #f8fafc; border-radius: 4px; font-size: 0.8rem; color: #64748b;">
                <strong>Current text:</strong> <span id="aiGenerateTextCurrentTextVal"></span>
            </div>

            <div id="aiGenerateTextStatus" style="display: none; padding: 1rem; background: #f1f5f9; border-radius: 4px; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div class="spinner" style="border: 2px solid #e2e8f0; border-top: 2px solid #6366f1; border-radius: 50%; width: 20px; height: 20px; animation: spin 1s linear infinite;"></div>
                    <span id="aiGenerateTextStatusText">Generating content...</span>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeAIGenerateTextModal()" class="toolbar-btn" style="padding: 0.5rem 1rem;">Cancel</button>
                <button type="submit" class="toolbar-btn" style="padding: 0.5rem 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;" id="aiGenerateTextSubmitBtn">
                    <i class="fas fa-magic"></i> Generate
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

    <!-- Layer Style Modal -->
    <div id="layerStyleModal" class="modal" style="display: none; position: fixed; z-index: 10001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);" onclick="if(event.target === this) closeLayerStyleModal();">
        <div class="modal-content" style="background-color: white; margin: 2% auto; padding: 0; border-radius: 8px; width: 90%; max-width: 600px; max-height: 90vh; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: flex; flex-direction: column; overflow: hidden;" onclick="event.stopPropagation();">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Layer Style</h3>
                <button onclick="closeLayerStyleModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
            </div>

            <div style="flex: 1; overflow-y: auto; padding: 1.5rem;">
                <!-- Drop Shadow Section -->
                <div class="panel-section" style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div class="panel-title" style="margin: 0;">Drop Shadow</div>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" id="shadowEnabled" onchange="toggleShadow()">
                            <span style="font-size: 0.875rem; color: #475569;">Enable</span>
                        </label>
                    </div>
                    <div id="shadowControls" style="display: none;">
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Offset X</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="range" id="shadowOffsetX" min="-50" max="50" value="5" step="1" oninput="updateShadowPreview()" style="flex: 1;">
                                <input type="number" id="shadowOffsetXValue" min="-50" max="50" value="5" onchange="updateShadowFromInput('offsetX')" style="width: 60px; padding: 0.25rem;">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Offset Y</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="range" id="shadowOffsetY" min="-50" max="50" value="5" step="1" oninput="updateShadowPreview()" style="flex: 1;">
                                <input type="number" id="shadowOffsetYValue" min="-50" max="50" value="5" onchange="updateShadowFromInput('offsetY')" style="width: 60px; padding: 0.25rem;">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Blur</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="range" id="shadowBlur" min="0" max="50" value="10" step="1" oninput="updateShadowPreview()" style="flex: 1;">
                                <input type="number" id="shadowBlurValue" min="0" max="50" value="10" onchange="updateShadowFromInput('blur')" style="width: 60px; padding: 0.25rem;">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Color</label>
                            <input type="color" id="shadowColor" value="#000000" onchange="updateShadowPreview()" style="width: 100%; height: 40px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Opacity</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="range" id="shadowOpacity" min="0" max="1" value="0.5" step="0.01" oninput="updateShadowPreview()" style="flex: 1;">
                                <span id="shadowOpacityValue" style="width: 60px; text-align: right; font-size: 0.875rem;">50%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fill Section -->
                <div class="panel-section" style="margin-bottom: 1.5rem;">
                    <div class="panel-title" style="margin-bottom: 1rem;">Fill</div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Fill Type</label>
                        <select id="fillType" class="form-select form-select-sm" onchange="toggleFillType()">
                            <option value="solid">Solid Color</option>
                            <option value="gradient">Gradient</option>
                        </select>
                    </div>
                    <div id="solidFillControls">
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Color</label>
                            <input type="color" id="fillColor" value="#000000" onchange="updateFillPreview()" style="width: 100%; height: 40px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Opacity</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="range" id="fillOpacity" min="0" max="1" value="1" step="0.01" oninput="updateFillPreview()" style="flex: 1;">
                                <span id="fillOpacityValue" style="width: 60px; text-align: right; font-size: 0.875rem;">100%</span>
                            </div>
                        </div>
                    </div>
                    <div id="gradientFillControls" style="display: none;">
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Gradient Type</label>
                            <select id="gradientType" class="form-select form-select-sm" onchange="updateGradientPreview()">
                                <option value="linear">Linear</option>
                                <option value="radial">Radial</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Angle (Linear)</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="range" id="gradientAngle" min="0" max="360" value="0" step="1" oninput="updateGradientPreview()" style="flex: 1;">
                                <input type="number" id="gradientAngleValue" min="0" max="360" value="0" onchange="updateGradientFromInput('angle')" style="width: 60px; padding: 0.25rem;">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Color Stop 1</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="color" id="gradientColor1" value="#000000" onchange="updateGradientPreview()" style="flex: 1; height: 40px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
                                <input type="range" id="gradientStop1" min="0" max="100" value="0" step="1" oninput="updateGradientPreview()" style="width: 100px;">
                                <span id="gradientStop1Value" style="width: 40px; text-align: right; font-size: 0.875rem;">0%</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Color Stop 2</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="color" id="gradientColor2" value="#ffffff" onchange="updateGradientPreview()" style="flex: 1; height: 40px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
                                <input type="range" id="gradientStop2" min="0" max="100" value="100" step="1" oninput="updateGradientPreview()" style="width: 100px;">
                                <span id="gradientStop2Value" style="width: 40px; text-align: right; font-size: 0.875rem;">100%</span>
                            </div>
                        </div>
                        <button class="toolbar-btn" onclick="addGradientStop()" style="width: 100%; padding: 0.5rem; margin-top: 0.5rem;">
                            <i class="fas fa-plus"></i> Add Color Stop
                        </button>
                        <div id="additionalGradientStops"></div>
                    </div>
                </div>

                <!-- Stroke Section -->
                <div class="panel-section" style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div class="panel-title" style="margin: 0;">Stroke</div>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" id="strokeEnabled" onchange="toggleStroke()">
                            <span style="font-size: 0.875rem; color: #475569;">Enable</span>
                        </label>
                    </div>
                    <div id="strokeControls" style="display: none;">
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Width</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="range" id="strokeWidth" min="0" max="50" value="1" step="0.5" oninput="updateStrokePreview()" style="flex: 1;">
                                <input type="number" id="strokeWidthValue" min="0" max="50" value="1" step="0.5" onchange="updateStrokeFromInput('width')" style="width: 60px; padding: 0.25rem;">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Color</label>
                            <input type="color" id="strokeColor" value="#000000" onchange="updateStrokePreview()" style="width: 100%; height: 40px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Dash Array (comma-separated, e.g., 5,5)</label>
                            <input type="text" id="strokeDashArray" placeholder="5,5" onchange="updateStrokePreview()" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Line Cap</label>
                            <select id="strokeLineCap" class="form-select form-select-sm" onchange="updateStrokePreview()">
                                <option value="butt">Butt</option>
                                <option value="round">Round</option>
                                <option value="square">Square</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Line Join</label>
                            <select id="strokeLineJoin" class="form-select form-select-sm" onchange="updateStrokePreview()">
                                <option value="miter">Miter</option>
                                <option value="round">Round</option>
                                <option value="bevel">Bevel</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Blur Section -->
                <div class="panel-section" style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div class="panel-title" style="margin: 0;">Blur</div>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" id="blurEnabled" onchange="toggleBlur()">
                            <span style="font-size: 0.875rem; color: #475569;">Enable</span>
                        </label>
                    </div>
                    <div id="blurControls" style="display: none;">
                        <div class="mb-2">
                            <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Blur Value</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="range" id="blurValue" min="0" max="50" value="5" step="0.5" oninput="updateBlurPreview()" style="flex: 1;">
                                <input type="number" id="blurValueInput" min="0" max="50" value="5" step="0.5" onchange="updateBlurFromInput()" style="width: 60px; padding: 0.25rem;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opacity Section -->
                <div class="panel-section">
                    <div class="panel-title" style="margin-bottom: 1rem;">Opacity</div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.75rem; display: block; margin-bottom: 0.5rem;">Overall Opacity</label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="range" id="objectOpacity" min="0" max="1" value="1" step="0.01" oninput="updateObjectOpacityPreview()" style="flex: 1;">
                            <span id="objectOpacityValue" style="width: 60px; text-align: right; font-size: 0.875rem;">100%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer buttons -->
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; padding: 0.75rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" onclick="closeLayerStyleModal()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; font-size: 0.8rem;">Cancel</button>
                <button type="button" onclick="resetLayerStyle()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; font-size: 0.8rem;">Reset</button>
                <button type="button" onclick="applyLayerStyle()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #6366f1; color: white; border-color: #6366f1; font-weight: 500; font-size: 0.8rem;">Apply</button>
            </div>
        </div>
    </div>

    <!-- Project Name Modal -->
    @php $isLetterType = ($designType ?? '') === 'letter'; @endphp
    <div id="projectNameModal" class="modal project-name-modal {{ $isLetterType ? 'project-name-modal--letter' : '' }}" style="display: none; position: fixed; z-index: 10002; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);" onclick="if(event.target === this) return;">
        <div class="modal-content {{ $isLetterType ? 'project-name-modal__content--letter' : '' }}" style="background-color: white; margin: 5% auto; padding: 0; border-radius: 8px; width: 90%; max-width: 700px; max-height: 90vh; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: flex; flex-direction: column; overflow: hidden;" onclick="event.stopPropagation();">
            <div class="project-name-modal__header {{ $isLetterType ? 'project-name-modal__header--letter' : '' }}" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                    @if($isLetterType)
                    <i class="fas fa-envelope" style="color: #6366f1;"></i>
                    New Letter Setup
                    @else
                    New Project Setup
                    @endif
                </h3>
                <button onclick="closeProjectNameModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">&times;</button>
            </div>
            <div style="padding: 1.5rem; overflow-y: auto; flex: 1;">
                <p style="margin: 0 0 1.5rem 0; color: #64748b; font-size: 0.875rem;">
                    @if($isLetterType)
                    Configure your letter before sending. Choose a name, template, and page size.
                    @else
                    Configure your project settings before starting.
                    @endif
                </p>

                <!-- Project Name -->
                <div class="mb-3">
                    <label class="form-label" style="font-size: 0.875rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">{{ $isLetterType ? 'Letter Name' : 'Project Name' }} <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="projectNameInput" class="form-control" placeholder="{{ $isLetterType ? 'e.g. Thank You Letter, Invitation' : 'e.g. Document 1, Document 2' }}" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.875rem;" autofocus>
                    <small style="font-size: 0.75rem; color: #94a3b8; display: block; margin-top: 0.25rem;">{{ $isLetterType ? 'This name will be used when saving your letter.' : 'This name will be used when saving your design.' }}</small>
                </div>

                <!-- Page Layout Template -->
                <div class="mb-3">
                    <label class="form-label" style="font-size: 0.875rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">{{ $isLetterType ? 'Letter Template' : 'Page Layout Template' }}</label>
                    <select id="projectPageTemplate" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.875rem;">
                        <option value="">Start from blank</option>
                        <option value="loading" disabled>Loading templates...</option>
                    </select>
                    <small style="font-size: 0.75rem; color: #94a3b8; display: block; margin-top: 0.25rem;">{{ $isLetterType ? 'Optional: Choose a letter template to start with.' : 'Optional: Choose a template to start with.' }}</small>
                </div>

                <!-- Page Size -->
                <div class="mb-3">
                    <label class="form-label" style="font-size: 0.875rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">Page Size</label>
                    <select id="projectPageSize" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.875rem;">
                        <option value="800x1000" {{ ($isVisitingCardsType || $isLetterType) ? '' : 'selected' }}>Custom (800 × 1000 px)</option>
                        <option value="VisitingCard" {{ $isVisitingCardsType ? 'selected' : '' }}>Visiting card (1000 × 600 px)</option>
                        <option value="A4">A4 (210 × 297 mm / 794 × 1123 px)</option>
                        <option value="Letter" {{ $isLetterType ? 'selected' : '' }}>Letter (8.5 × 11 in / 816 × 1056 px)</option>
                        <option value="A3">A3 (297 × 420 mm / 1123 × 1587 px)</option>
                        <option value="A5">A5 (148 × 210 mm / 559 × 794 px)</option>
                        <option value="Legal">Legal (8.5 × 14 in / 816 × 1344 px)</option>
                        <option value="Tabloid">Tabloid (11 × 17 in / 1056 × 1632 px)</option>
                        <option value="Square">Square (1000 × 1000 px)</option>
                        <option value="Instagram">Instagram Post (1080 × 1080 px)</option>
                        <option value="Facebook">Facebook Post (1200 × 630 px)</option>
                        <option value="custom">Custom Size</option>
                    </select>
                    <small style="font-size: 0.75rem; color: #94a3b8; display: block; margin-top: 0.25rem;">{{ $isVisitingCardsType ? 'Visiting cards use a 1000 × 600 px artboard by default.' : ($isLetterType ? 'Select the page size for your letter.' : 'Select the page size for your design.') }}</small>
                </div>

                <!-- Custom Size Inputs (Hidden by default) -->
                <div id="customSizeInputs" style="display: none; margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: 4px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label class="form-label" style="font-size: 0.875rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">Width (px)</label>
                            <input type="number" id="customWidth" class="form-control" value="{{ $mpDefaultCanvasW }}" min="100" max="5000" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.875rem;">
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 0.875rem; font-weight: 600; display: block; margin-bottom: 0.5rem;">Height (px)</label>
                            <input type="number" id="customHeight" class="form-control" value="{{ $mpDefaultCanvasH }}" min="100" max="5000" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.875rem;">
                        </div>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; padding: 0.75rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" onclick="closeProjectNameModal()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-weight: 500; font-size: 0.8rem;">Cancel</button>
                <button type="button" onclick="confirmProjectName()" class="toolbar-btn" style="padding: 0.5rem 1rem; background: #6366f1; color: white; border-color: #6366f1; font-weight: 500; font-size: 0.8rem;">
                    @if($isLetterType)
                    <i class="fas fa-paper-plane me-1"></i>Create Letter
                    @else
                    Create Project
                    @endif
                </button>
            </div>
        </div>
    </div>

<!-- Font Library Modal -->
<div id="fontLibraryModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;" onclick="if(event.target === this) closeFontLibraryModal();">
    <div style="background: white; border-radius: 8px; width: 90%; max-width: 600px; max-height: 80vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);" onclick="event.stopPropagation();">
        <div style="padding: 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Font Library</h3>
            <button onclick="closeFontLibraryModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        <div style="padding: 1.25rem; overflow-y: auto; flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="font-size: 0.875rem; color: #64748b;">Site fonts (read-only) and your uploads. You can add personal fonts below (TTF, OTF, WOFF, WOFF2).</div>
                <button class="toolbar-btn" onclick="document.getElementById('fontFileUpload').click()" style="padding: 0.5rem 1rem; background: #6366f1; color: white; border-color: #6366f1;">
                    <i class="fas fa-upload"></i> Upload Fonts
                </button>
            </div>
            <input type="file" id="fontFileUpload" accept=".ttf,.otf,.woff,.woff2,.eot" multiple style="display: none;" onchange="handleFontUpload(event)">
            <div id="fontLibraryList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;">
                    <i class="fas fa-font" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                    <p style="margin: 0;">Loading fonts...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Context Menu for Selected Elements -->
<div id="contextMenu" class="context-menu" style="display: none; position: fixed; z-index: 10000; background: white; border: 1px solid #e2e8f0; border-radius: 6px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); min-width: 200px; padding: 0.25rem 0;">
    <button class="context-menu-item" onclick="contextMenuCut()">
        <i class="fas fa-cut"></i>
        <span>Cut</span>
        <span class="context-menu-shortcut">Ctrl+X</span>
    </button>
    <button class="context-menu-item" onclick="contextMenuCopy()">
        <i class="fas fa-copy"></i>
        <span>Copy</span>
        <span class="context-menu-shortcut">Ctrl+C</span>
    </button>
    <button class="context-menu-item" onclick="contextMenuPaste()" id="contextMenuPasteBtn" disabled>
        <i class="fas fa-paste"></i>
        <span>Paste</span>
        <span class="context-menu-shortcut">Ctrl+V</span>
    </button>
    <button class="context-menu-item" onclick="contextMenuDuplicate()">
        <i class="fas fa-clone"></i>
        <span>Duplicate</span>
        <span class="context-menu-shortcut">Ctrl+D</span>
    </button>
    <div class="context-menu-divider"></div>
    <button class="context-menu-item" id="contextMenuGenerateText" onclick="contextMenuGenerateTextContent()" style="display: none;">
        <i class="fas fa-magic"></i>
        <span>Generate content using AI</span>
    </button>
    <div class="context-menu-divider" id="contextMenuGenerateTextDivider" style="display: none;"></div>
    <button class="context-menu-item" id="contextMenuEditImage" onclick="contextMenuEditImage()" style="display: none;">
        <i class="fas fa-crop-alt"></i>
        <span>Edit & Crop Image</span>
    </button>
    <button class="context-menu-item" id="contextMenuClipImage" onclick="contextMenuClipImage()" style="display: none;">
        <i class="fas fa-draw-polygon"></i>
        <span>Polygon crop / Clip image</span>
    </button>
    <div class="context-menu-divider" id="contextMenuEditImageDivider" style="display: none;"></div>
    <button class="context-menu-item" onclick="contextMenuDelete()">
        <i class="fas fa-trash"></i>
        <span>Delete</span>
        <span class="context-menu-shortcut">Del</span>
    </button>
    <div class="context-menu-divider"></div>
    <button class="context-menu-item" onclick="contextMenuBringToFront()">
        <i class="fas fa-arrow-up"></i>
        <span>Bring to Front</span>
        <span class="context-menu-shortcut">Ctrl+]</span>
    </button>
    <button class="context-menu-item" onclick="contextMenuSendToBack()">
        <i class="fas fa-arrow-down"></i>
        <span>Send to Back</span>
        <span class="context-menu-shortcut">Ctrl+[</span>
    </button>
    <button class="context-menu-item" onclick="contextMenuBringForward()">
        <i class="fas fa-chevron-up"></i>
        <span>Bring Forward</span>
    </button>
    <button class="context-menu-item" onclick="contextMenuSendBackward()">
        <i class="fas fa-chevron-down"></i>
        <span>Send Backward</span>
    </button>
    <div class="context-menu-divider" id="contextMenuCustomLinksDivider" style="display: none;"></div>
    <div id="contextMenuCustomLinks"></div>
</div>

<!-- Toast container for success messages -->
<div id="designToast" class="design-toast" role="status" aria-live="polite">
    <i class="fas fa-check-circle"></i>
    <span id="designToastMessage"></span>
</div>
@endsection

@push('scripts')
@php
    $editorElements = app(\App\Services\Module\ModuleRegistry::class)->getEditorElements();
    $moduleScriptsLoaded = [];
@endphp
@foreach($editorElements as $el)
@if(!in_array($el['module'], $moduleScriptsLoaded) && \Illuminate\Support\Facades\View::exists($el['module'] . '::editor-scripts'))
@include($el['module'] . '::editor-scripts')
@php $moduleScriptsLoaded[] = $el['module']; @endphp
@endif
@endforeach
@foreach(app(\App\Services\Module\ModuleRegistry::class)->getImagePropertiesPanels() as $imgPanel)
@if(!in_array($imgPanel['module'], $moduleScriptsLoaded) && \Illuminate\Support\Facades\View::exists($imgPanel['module'] . '::editor-scripts'))
@include($imgPanel['module'] . '::editor-scripts')
@php $moduleScriptsLoaded[] = $imgPanel['module']; @endphp
@endif
@endforeach
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/intro.js@7/minified/intro.min.js"></script>
<script>
window.designIntroShowMode = @json($introShowMode ?? 'first_time');
window.designIntroAlreadySeenForAccount = @json($introAlreadySeenForAccount ?? false);
window.designIntroSteps = @json($introSteps ?? []);
var DESIGN_INTRO_STORAGE_KEY = 'design_intro_seen_multi_page_editor';
var DESIGN_INTRO_MARK_SEEN_URL = @json(route('design.intro.markSeen'));

// Intro.js tour for Multi-Page Design Tool (uses DB steps if any, else built-in defaults)
function startDesignIntro() {
    if (typeof introJs === 'undefined') return;
    var steps = [];
    if (window.designIntroSteps && window.designIntroSteps.length > 0) {
        window.designIntroSteps.forEach(function(s) {
            var el = (s.element_selector && document.querySelector(s.element_selector)) || null;
            var intro = (s.title ? '<strong>' + s.title + '</strong><br><br>' : '') + (s.intro_text || '');
            steps.push(el ? { element: el, intro: intro } : { intro: intro });
        });
    } else {
        steps = [
            { intro: '<strong>Multi-Page Design Tool</strong><br><br>This quick tour shows the main areas. Use <strong>Help → Take a tour</strong> or the <strong>Tour</strong> button anytime to see this again.' },
            { element: document.getElementById('designToolbar'), intro: '<strong>Toolbar</strong><br><br>Save your work, export (PDF/images), copy/paste, undo/redo, and zoom. Use Export for flipbook, template, or JSON.' },
            { element: document.getElementById('leftSidebar'), intro: '<strong>Left sidebar</strong><br><br><strong>Pages</strong> – add/duplicate/reorder pages.<br><strong>Templates</strong> – apply saved templates.<br><strong>Elements</strong> – add text, shapes, tables, upload images.<br><strong>Images</strong> – your image library.<br><strong>Global</strong> – shared image parts.<br><strong>Layers</strong> – manage object order.' },
            { element: document.getElementById('canvasContainer'), intro: '<strong>Canvas</strong><br><br>Design your page here. Drag elements from the left, select to edit in the right panel. Rulers and zoom help with layout.' },
            { element: document.getElementById('propertiesPanel'), intro: '<strong>Properties panel</strong><br><br>When you select an object, edit its properties here: position, size, colors, text, and more.' },
            { intro: '<strong>You\'re all set</strong><br><br>Try adding a text or shape from the Elements tab, then select it to tweak in the Properties panel. Have fun designing!' }
        ];
    }
    var filtered = steps.filter(function(s) { return !s.element || (s.element && document.body.contains(s.element)); });
    if (filtered.length === 0) return;
    var mode = window.designIntroShowMode || 'first_time';
    if (mode !== 'first_time_account') try { localStorage.setItem(DESIGN_INTRO_STORAGE_KEY, '1'); } catch (e) {}
    var inst = introJs().setOptions({
        steps: filtered,
        exitOnOverlayClick: true,
        showStepNumbers: true,
        showBullets: true
    });
    if (mode === 'first_time_account') {
        var markSeen = function() {
            fetch(DESIGN_INTRO_MARK_SEEN_URL, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ tour: 'multi_page' })
            }).catch(function() {});
        };
        inst.oncomplete(markSeen).onexit(markSeen);
    }
    inst.start();
}

function runDesignIntroIfNeeded() {
    if (typeof introJs === 'undefined') return;
    if (window._projectNameModalShownOnLoad && !window._runningIntroAfterModalClose) return;
    var mode = window.designIntroShowMode || 'first_time';
    if (mode === 'never') return;
    if (mode === 'always') {
        setTimeout(function() { startDesignIntro(); }, 600);
        return;
    }
    if (mode === 'first_time_account') {
        if (!window.designIntroAlreadySeenForAccount) setTimeout(function() { startDesignIntro(); }, 600);
        return;
    }
    if (mode === 'first_time') {
        try {
            if (!localStorage.getItem(DESIGN_INTRO_STORAGE_KEY)) {
                setTimeout(function() { startDesignIntro(); }, 600);
            }
        } catch (e) {}
    }
}
// Do not run intro on page load – only after New Letter/Project Setup modal is closed

function runDesignIntroAfterModalClose() {
    window._runningIntroAfterModalClose = true;
    setTimeout(function() {
        runDesignIntroIfNeeded();
        window._runningIntroAfterModalClose = false;
    }, 500);
}

// Small toast for design save success (no alert)
function showDesignToast(message) {
    const toast = document.getElementById('designToast');
    const msgEl = document.getElementById('designToastMessage');
    if (!toast || !msgEl) return;
    msgEl.textContent = message;
    toast.classList.add('show');
    clearTimeout(window._designToastTimer);
    window._designToastTimer = setTimeout(function() {
        toast.classList.remove('show');
    }, 2500);
}
// Lazy-load Cropper (only when user crops an image) - saves ~50KB + CSS on initial load
function loadCropperLib() {
    return new Promise((resolve) => {
        if (typeof Cropper !== 'undefined') { resolve(); return; }
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css';
        document.head.appendChild(link);
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js';
        script.onload = () => resolve();
        script.onerror = () => resolve(); // Continue even if load fails
        document.body.appendChild(script);
    });
}
// Lazy-load jsPDF (only when user exports PDF) - saves ~100KB on initial load
function loadJsPDF() {
    return new Promise((resolve) => {
        if (typeof window.jspdf !== 'undefined') { resolve(); return; }
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
        script.onload = () => resolve();
        script.onerror = () => resolve();
        document.body.appendChild(script);
    });
}
</script>
<script defer>
    let canvas;
    let zoomLevel = 50;
    let currentObject = null;
    let pages = [];
    let currentPageIndex = 0;
    let history = [];
    let historyStep = -1;
        let currentDesignId = null;
        let currentFlipbookId = null; // Track current flipbook ID for updates
        let existingFlipbookFormData = null; // Pre-filled form data when updating flipbook
    let copiedObjects = null; // Store copied objects for pasting
    let alignmentGuides = []; // Store alignment guide lines
    let projectName = ''; // Store project name
    let snapThreshold = 5; // Pixels threshold for snapping (0 = disabled when grid snap off)
    let sourceTemplateId = null; // Template ID when loaded from My Templates (skip create on checkout if unchanged)
    let sourceTemplateType = ''; // Template type (letter, flipbook, etc.) - used for checkout redirect
    let designModified = false; // True when user has changed the design (add/edit/remove)
    let skipDesignModifiedDuringLoad = false; // Skip marking modified during initial template load

    // Editor settings from admin (dynamic, remembered)
    @php
        $contextMenuLinks = json_decode(\App\Models\Setting::get('editor_context_menu_links') ?: '[]', true) ?: [];
        $editorSettingsConfig = [
            'canvasWidth' => $mpDefaultCanvasW,
            'canvasHeight' => $mpDefaultCanvasH,
            'backgroundColor' => \App\Models\Setting::get('editor_default_bg_color') ?: '#ffffff',
            'showMenuBar' => filter_var(\App\Models\Setting::get('editor_show_menu_bar', '1'), FILTER_VALIDATE_BOOLEAN),
            'showContextMenu' => filter_var(\App\Models\Setting::get('editor_show_context_menu', '1'), FILTER_VALIDATE_BOOLEAN),
            'showRulers' => filter_var(\App\Models\Setting::get('editor_show_rulers', '1'), FILTER_VALIDATE_BOOLEAN),
            'gridSnap' => filter_var(\App\Models\Setting::get('editor_grid_snap', '1'), FILTER_VALIDATE_BOOLEAN),
            'autoSave' => filter_var(\App\Models\Setting::get('editor_auto_save', '1'), FILTER_VALIDATE_BOOLEAN),
            'customContextMenuLinks' => $contextMenuLinks,
            'imageReduceOnAdd' => filter_var(\App\Models\Setting::get('editor_image_reduce_on_add', '0'), FILTER_VALIDATE_BOOLEAN),
            'imageMaxDimension' => (int) (\App\Models\Setting::get('editor_image_max_dimension') ?: 2000),
            'imageQuality' => min(1, max(0.1, (float) (\App\Models\Setting::get('editor_image_quality') ?: 0.8))),
        ];
    @endphp
    const editorSettings = @json($editorSettingsConfig);
    const canExportWatermark = @json(!empty($canExportWatermark ?? false));
    const designType = @json($designType ?? '');
    if (!editorSettings.gridSnap) snapThreshold = -1; // Disable snapping

    // Dropdown functions
    function toggleExportDropdown(event) {
        if (event) {
            event.stopPropagation();
        }
        const menu = document.getElementById('exportDropdownMenu');
        if (menu) {
            menu.classList.toggle('show');
        }
    }

    function closeExportDropdown() {
        const menu = document.getElementById('exportDropdownMenu');
        if (menu) {
            menu.classList.remove('show');
        }
    }

    // Menu Bar Functions
    function showMenuDropdown(menuId) {
        // Close all other menus first
        document.querySelectorAll('.menu-dropdown').forEach(menu => {
            if (menu.id !== menuId) {
                menu.classList.remove('show');
            }
        });
        const menu = document.getElementById(menuId);
        if (menu) {
            menu.classList.add('show');
        }
    }

    function hideMenuDropdown(menuId) {
        const menu = document.getElementById(menuId);
        if (menu) {
            // Small delay to allow moving to dropdown
            setTimeout(() => {
                if (!menu.matches(':hover')) {
                    menu.classList.remove('show');
                }
            }, 100);
        }
    }

    // Menu Action Functions
    function duplicateSelected() {
        if (canvas && canvas.getActiveObject()) {
            const activeObject = canvas.getActiveObject();
            activeObject.clone(function(cloned) {
                cloned.set({
                    left: cloned.left + 10,
                    top: cloned.top + 10
                });
                canvas.add(cloned);
                canvas.setActiveObject(cloned);
                canvas.renderAll();
            });
        }
    }

    function selectAll() {
        if (canvas) {
            canvas.discardActiveObject();
            const allObjects = canvas.getObjects();
            if (allObjects.length > 0) {
                const selection = new fabric.ActiveSelection(allObjects, {
                    canvas: canvas
                });
                canvas.setActiveObject(selection);
                canvas.renderAll();
            }
        }
    }

    function deselectAll() {
        if (canvas) {
            canvas.discardActiveObject();
            canvas.renderAll();
        }
    }

    function resetZoom() {
        if (canvas) {
            currentZoom = 1.0;
            updateZoom();
        }
    }

    function fitToScreen() {
        if (canvas) {
            const container = document.querySelector('.canvas-container');
            if (container) {
                const containerWidth = container.clientWidth - 40;
                const containerHeight = container.clientHeight - 40;
                const canvasWidth = canvas.getWidth();
                const canvasHeight = canvas.getHeight();
                const scaleX = containerWidth / canvasWidth;
                const scaleY = containerHeight / canvasHeight;
                currentZoom = Math.min(scaleX, scaleY);
                updateZoom();
            }
        }
    }

    let rulersVisible = false;
    let rulerHorizontalCanvas = null;
    let rulerVerticalCanvas = null;

    function toggleRulers() {
        const rulerContainer = document.getElementById('rulerContainer');
        const canvasWrapper = document.getElementById('canvasWrapper');

        if (!rulerContainer) return;

        rulersVisible = !rulersVisible;

        if (rulersVisible) {
            rulerContainer.classList.add('show');
            if (canvasWrapper) {
                canvasWrapper.classList.add('with-rulers');
            }
            initRulers();
            updateRulers();

            // Update menu text
            const menuRulersText = document.getElementById('menuRulersText');
            if (menuRulersText) {
                menuRulersText.textContent = 'Hide Rulers';
            }
        } else {
            rulerContainer.classList.remove('show');
            if (canvasWrapper) {
                canvasWrapper.classList.remove('with-rulers');
            }

            // Update menu text
            const menuRulersText = document.getElementById('menuRulersText');
            if (menuRulersText) {
                menuRulersText.textContent = 'Show Rulers';
            }
        }
    }

    function initRulers() {
        const horizontalCanvas = document.getElementById('rulerHorizontal');
        const verticalCanvas = document.getElementById('rulerVertical');
        const canvasContainer = document.getElementById('canvasContainer');
        const rulerContainer = document.getElementById('rulerContainer');

        if (!horizontalCanvas || !verticalCanvas || !canvasContainer || !rulerContainer) return;

        rulerHorizontalCanvas = horizontalCanvas;
        rulerVerticalCanvas = verticalCanvas;

        // Ensure ruler container covers the full container
        const updateRulerContainerSize = () => {
            const containerRect = canvasContainer.getBoundingClientRect();
            rulerContainer.style.width = containerRect.width + 'px';
            rulerContainer.style.height = containerRect.height + 'px';
        };

        updateRulerContainerSize();

        // Update rulers on scroll, resize, and zoom
        canvasContainer.addEventListener('scroll', updateRulers);
        window.addEventListener('resize', function() {
            updateRulerContainerSize();
            setTimeout(updateRulers, 100);
        });

        // Use ResizeObserver for better resize detection
        if (window.ResizeObserver) {
            const resizeObserver = new ResizeObserver(() => {
                updateRulerContainerSize();
                setTimeout(updateRulers, 50);
            });
            resizeObserver.observe(canvasContainer);
        }

        // Update rulers when canvas changes
        if (canvas) {
            canvas.on('object:added', function() {
                if (rulersVisible) setTimeout(updateRulers, 10);
            });
            canvas.on('object:removed', function() {
                if (rulersVisible) setTimeout(updateRulers, 10);
            });
            canvas.on('object:modified', function() {
                if (rulersVisible) setTimeout(updateRulers, 10);
            });
        }

        // Initial update with a small delay to ensure layout is complete
        setTimeout(updateRulers, 50);
    }

    function updateRulers() {
        if (!rulersVisible || !rulerHorizontalCanvas || !rulerVerticalCanvas || !canvas) return;

        const canvasContainer = document.getElementById('canvasContainer');
        const canvasWrapper = document.getElementById('canvasWrapper');
        const rulerContainer = document.getElementById('rulerContainer');

        if (!canvasContainer || !canvasWrapper || !rulerContainer) return;

        const containerRect = canvasContainer.getBoundingClientRect();
        const wrapperRect = canvasWrapper.getBoundingClientRect();
        const scrollLeft = canvasContainer.scrollLeft;
        const scrollTop = canvasContainer.scrollTop;

        // Update canvas sizes - match ruler container sizes
        const horizontalRuler = document.querySelector('.ruler-horizontal');
        const verticalRuler = document.querySelector('.ruler-vertical');

        if (horizontalRuler) {
            const rulerRect = horizontalRuler.getBoundingClientRect();
            // Use device pixel ratio for crisp rendering
            const dpr = window.devicePixelRatio || 1;
            const width = Math.max(rulerRect.width, containerRect.width - 20);
            rulerHorizontalCanvas.width = width * dpr;
            rulerHorizontalCanvas.height = 20 * dpr;
            rulerHorizontalCanvas.style.width = width + 'px';
            rulerHorizontalCanvas.style.height = '20px';
        }

        if (verticalRuler) {
            const rulerRect = verticalRuler.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            // Ensure we have a valid height - use container height minus horizontal ruler height
            const height = Math.max(rulerRect.height || (containerRect.height - 20), containerRect.height - 20);
            if (height > 0) {
                rulerVerticalCanvas.width = 20 * dpr;
                rulerVerticalCanvas.height = height * dpr;
                rulerVerticalCanvas.style.width = '20px';
                rulerVerticalCanvas.style.height = height + 'px';
                // Ensure canvas is visible
                rulerVerticalCanvas.style.display = 'block';
            }
        }

        // Get canvas dimensions and zoom
        const canvasWidth = canvas.getWidth();
        const canvasHeight = canvas.getHeight();
        const zoomFactor = zoomLevel / 100;

        // Calculate the wrapper's position relative to the container viewport
        // This accounts for scrolling and the wrapper's position
        const wrapperOffsetX = wrapperRect.left - containerRect.left;
        const wrapperOffsetY = wrapperRect.top - containerRect.top;

        // Calculate the actual scroll position relative to the canvas
        // The scroll position in the container needs to be adjusted for the wrapper's position
        const effectiveScrollX = scrollLeft - wrapperOffsetX;
        const effectiveScrollY = scrollTop - wrapperOffsetY;

        // Account for wrapper padding (20px on all sides)
        const wrapperPadding = 20;
        const canvasScrollX = effectiveScrollX - wrapperPadding;
        const canvasScrollY = effectiveScrollY - wrapperPadding;

        // Draw horizontal ruler (accounting for canvas position, scroll, and zoom)
        drawHorizontalRuler(rulerHorizontalCanvas, canvasScrollX, zoomFactor, canvasWidth);

        // Draw vertical ruler (accounting for canvas position, scroll, and zoom)
        drawVerticalRuler(rulerVerticalCanvas, canvasScrollY, zoomFactor, canvasHeight);
    }

    function drawHorizontalRuler(canvas, scrollLeft, zoomFactor, canvasWidth) {
        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const width = canvas.width;
        const height = canvas.height;
        const displayWidth = width / dpr;
        const displayHeight = height / dpr;

        // Clear canvas
        ctx.clearRect(0, 0, width, height);

        // Scale context for high DPI
        ctx.scale(dpr, dpr);

        // Background
        ctx.fillStyle = '#f8fafc';
        ctx.fillRect(0, 0, displayWidth, displayHeight);

        // Border
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, displayWidth, displayHeight);

        // Draw ruler marks
        ctx.strokeStyle = '#94a3b8';
        ctx.fillStyle = '#475569';
        ctx.font = '10px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        // Calculate pixel positions accounting for zoom
        // scrollLeft is in container pixels, we need to convert to canvas pixels
        // Handle negative scroll values
        const canvasScrollLeft = Math.max(0, scrollLeft / zoomFactor);
        const visibleWidth = displayWidth / zoomFactor;

        // Determine step size based on zoom level
        let step = 10;
        if (zoomFactor < 0.5) step = 50;
        else if (zoomFactor < 1) step = 20;
        else if (zoomFactor > 2) step = 5;

        const startPos = Math.max(0, Math.floor(canvasScrollLeft / step) * step);
        const endPos = Math.min(canvasWidth, Math.ceil((canvasScrollLeft + visibleWidth) / step) * step);

        for (let i = startPos; i <= endPos; i += step) {
            const x = (i * zoomFactor) - (canvasScrollLeft * zoomFactor);

            // Only draw if within visible area
            if (x < 0 || x > displayWidth) continue;

            if (i % 100 === 0) {
                // Major mark every 100px
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, displayHeight);
                ctx.stroke();

                ctx.fillText(i + 'px', x, displayHeight / 2);
            } else if (i % 50 === 0) {
                // Medium mark every 50px
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, displayHeight * 0.6);
                ctx.stroke();
            } else if (step <= 10) {
                // Minor mark every 10px (or step size)
                ctx.lineWidth = 0.5;
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, displayHeight * 0.4);
                ctx.stroke();
            }
        }
    }

    function drawVerticalRuler(canvas, scrollTop, zoomFactor, canvasHeight) {
        if (!canvas || canvas.width === 0 || canvas.height === 0) {
            return; // Canvas not properly initialized
        }

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const dpr = window.devicePixelRatio || 1;
        const width = canvas.width;
        const height = canvas.height;
        const displayWidth = width / dpr;
        const displayHeight = height / dpr;

        // Clear canvas
        ctx.clearRect(0, 0, width, height);

        // Reset transform
        ctx.setTransform(1, 0, 0, 1, 0, 0);

        // Scale context for high DPI
        ctx.scale(dpr, dpr);

        // Background
        ctx.fillStyle = '#f8fafc';
        ctx.fillRect(0, 0, displayWidth, displayHeight);

        // Border
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, displayWidth, displayHeight);

        // Draw ruler marks
        ctx.strokeStyle = '#94a3b8';
        ctx.fillStyle = '#475569';
        ctx.font = '10px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        // Calculate pixel positions accounting for zoom
        // Handle negative scroll values
        const canvasScrollTop = Math.max(0, scrollTop / zoomFactor);
        const visibleHeight = displayHeight / zoomFactor;

        // Determine step size based on zoom level
        let step = 10;
        if (zoomFactor < 0.5) step = 50;
        else if (zoomFactor < 1) step = 20;
        else if (zoomFactor > 2) step = 5;

        const startPos = Math.max(0, Math.floor(canvasScrollTop / step) * step);
        const endPos = Math.min(canvasHeight, Math.ceil((canvasScrollTop + visibleHeight) / step) * step);

        for (let i = startPos; i <= endPos; i += step) {
            const y = (i * zoomFactor) - (canvasScrollTop * zoomFactor);

            // Only draw if within visible area
            if (y < 0 || y > displayHeight) continue;

            if (i % 100 === 0) {
                // Major mark every 100px
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(displayWidth, y);
                ctx.stroke();

                // Draw text rotated - save context, rotate, draw text, restore
                ctx.save();
                ctx.translate(displayWidth / 2, y);
                ctx.rotate(-Math.PI / 2);
                ctx.fillText(i + 'px', 0, 0);
                ctx.restore();
            } else if (i % 50 === 0) {
                // Medium mark every 50px
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(displayWidth * 0.6, y);
                ctx.stroke();
            } else if (step <= 10) {
                // Minor mark every 10px (or step size)
                ctx.lineWidth = 0.5;
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(displayWidth * 0.4, y);
                ctx.stroke();
            }
        }
    }

    function toggleGrid() {
        alert('Grid feature coming soon!');
    }

    function toggleGuides() {
        alert('Guides feature coming soon!');
    }

    function bringToFront() {
        if (canvas && canvas.getActiveObject()) {
            canvas.bringToFront(canvas.getActiveObject());
            canvas.renderAll();
        }
    }

    function sendToBack() {
        if (canvas && canvas.getActiveObject()) {
            canvas.sendToBack(canvas.getActiveObject());
            canvas.renderAll();
        }
    }

    function bringForward() {
        if (canvas && canvas.getActiveObject()) {
            canvas.bringForward(canvas.getActiveObject());
            canvas.renderAll();
        }
    }

    function sendBackward() {
        if (canvas && canvas.getActiveObject()) {
            canvas.sendBackwards(canvas.getActiveObject());
            canvas.renderAll();
        }
    }

    function groupSelected() {
        if (canvas && canvas.getActiveObject() && canvas.getActiveObject().type === 'activeSelection') {
            canvas.getActiveObject().toGroup();
            canvas.renderAll();
        }
    }

    function ungroupSelected() {
        if (canvas && canvas.getActiveObject() && canvas.getActiveObject().type === 'group') {
            canvas.getActiveObject().toUngroup();
            canvas.renderAll();
        }
    }

    function lockSelected() {
        if (canvas && canvas.getActiveObject()) {
            canvas.getActiveObject().set('selectable', false);
            canvas.getActiveObject().set('evented', false);
            canvas.renderAll();
        }
    }

    function unlockSelected() {
        if (canvas && canvas.getActiveObject()) {
            canvas.getActiveObject().set('selectable', true);
            canvas.getActiveObject().set('evented', true);
            canvas.renderAll();
        }
    }

    function duplicateCurrentPage() {
        if (pages.length > 0 && currentPageIndex >= 0) {
            const currentPage = pages[currentPageIndex];
            const newPage = {
                id: 'page_' + Date.now(),
                index: pages.length,
                name: 'Document ' + (pages.length + 1),
                data: currentPage.data
            };
            pages.push(newPage);
            renderPagesList();
            switchToPage(pages.length - 1);
        }
    }

    function deleteCurrentPage() {
        if (pages.length > 1 && currentPageIndex >= 0) {
            if (confirm('Are you sure you want to delete this page?')) {
                pages.splice(currentPageIndex, 1);
                if (currentPageIndex >= pages.length) {
                    currentPageIndex = pages.length - 1;
                }
                renderPagesList();
                if (pages.length > 0) {
                    switchToPage(currentPageIndex);
                }
            }
        } else {
            alert('Cannot delete the last page. Add another page first.');
        }
    }

    function previousPage() {
        if (currentPageIndex > 0) {
            switchToPage(currentPageIndex - 1);
        }
    }

    function nextPage() {
        if (currentPageIndex < pages.length - 1) {
            switchToPage(currentPageIndex + 1);
        }
    }

    function showKeyboardShortcuts() {
        alert('Keyboard Shortcuts:\n\n' +
            'Ctrl+S - Save\n' +
            'Ctrl+C - Copy\n' +
            'Ctrl+V - Paste\n' +
            'Ctrl+Z - Undo\n' +
            'Ctrl+Y - Redo\n' +
            'Ctrl+A - Select All\n' +
            'Ctrl+G - Group\n' +
            'Ctrl+Shift+G - Ungroup\n' +
            'Ctrl+] - Bring to Front\n' +
            'Ctrl+[ - Send to Back\n' +
            'Delete - Delete Selected\n' +
            'Ctrl++ - Zoom In\n' +
            'Ctrl+- - Zoom Out\n' +
            'Ctrl+0 - Reset Zoom');
    }

    function showHelp() {
        alert('Help documentation coming soon!');
    }

    function showAbout() {
        alert('Multi-Page Design Tool\n\nVersion 1.0\nA powerful design tool for creating multi-page flipbooks.');
    }

    // Close menu dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.menu-item') && !event.target.closest('.menu-dropdown')) {
            document.querySelectorAll('.menu-dropdown').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.querySelector('.toolbar-dropdown');
        const menu = document.getElementById('exportDropdownMenu');
        if (dropdown && menu && !dropdown.contains(event.target)) {
            menu.classList.remove('show');
        }

        // Close font library modal when clicking outside
        const fontModal = document.getElementById('fontLibraryModal');
        if (fontModal && fontModal.style.display === 'flex' && event.target === fontModal) {
            closeFontLibraryModal();
        }
    });

    // Initialize
    // Pricing calculation
    // Sheet types data from database (with prices, multipliers, video, description)
    @php
        $sheetTypesArray = [];
        if (isset($sheetTypes) && $sheetTypes) {
            foreach ($sheetTypes as $st) {
                $sheetTypesArray[$st->slug] = [
                    'slug' => $st->slug,
                    'name' => $st->name,
                    'multiplier' => (float)$st->multiplier,
                    'price_per_sheet' => (float)$st->price_per_sheet,
                    'video_url' => $st->video_url,
                    'description' => $st->description ?? ''
                ];
            }
        }
    @endphp
    const sheetTypesData = @json($sheetTypesArray);
    const currencySymbol = @json(\App\Models\Setting::get('currency_symbol') ?: '$');
    const currencyDecimals = parseInt(@json(\App\Models\Setting::get('price_decimal_places') ?: 2), 10);
    function formatPrice(amount) {
        return (currencySymbol || '$') + parseFloat(amount ?? 0).toFixed(currencyDecimals ?? 2);
    }

    // Sheet type multipliers (for backward compatibility)
    const sheetTypeMultipliers = {};
    Object.keys(sheetTypesData).forEach(slug => {
        sheetTypeMultipliers[slug] = sheetTypesData[slug].multiplier;
    });

    const pricingRates = {
        // Base per page cost by size
        size: {
            'A5': 0.50,
            'A4': 0.75,
            'A3': 1.25,
            'Letter': 0.80,
            'Legal': 0.90,
            'Custom': 1.00
        },
        // Sheet type multipliers (from database)
        sheetType: Object.keys(sheetTypeMultipliers).length > 0 ? sheetTypeMultipliers : {
            'standard': 1.0,
            'glossy': 1.2,
            'matte': 1.15,
            'satin': 1.25,
            'textured': 1.3
        },
        // Print quality multipliers
        quality: {
            'standard': 1.0,
            'high': 1.3,
            'premium': 1.6
        },
        // Binding costs
        binding: {
            'none': 0,
            'spiral': 2.50,
            'perfect': 3.00,
            'saddle': 1.50,
            'wire': 2.75
        },
        // Shipping cost
        shipping: 5.00
    };

    function calculateFlipbookPricing() {
        const pageCount = pages.length || 0;
        const printSize = document.getElementById('flipbookPrintSize')?.value || '';
        const sheetType = document.getElementById('flipbookPrintSheetType')?.value || '';
        const printQuality = document.getElementById('flipbookPrintQuality')?.value || 'standard';
        const bindingType = document.getElementById('flipbookBindingType')?.value || 'none';
        const bundleQuantity = parseInt(document.getElementById('bundleQuantity')?.value || 1) || 1;

        // Update summary display
        document.getElementById('pricingPageCount').textContent = pageCount;
        document.getElementById('pricingPrintSize').textContent = printSize || '-';
        document.getElementById('pricingSheetType').textContent = sheetType ? sheetType.charAt(0).toUpperCase() + sheetType.slice(1) : '-';
        document.getElementById('pricingPrintQuality').textContent = printQuality ? printQuality.charAt(0).toUpperCase() + printQuality.slice(1) : '-';
        document.getElementById('pricingBinding').textContent = bindingType !== 'none' ? bindingType.charAt(0).toUpperCase() + bindingType.slice(1) : 'None';
        document.getElementById('pricingBundleQuantity').textContent = bundleQuantity;

        // Calculate costs using sheet price
        let sheetCostPerPage = 0;

        // Get sheet price from database
        if (sheetType && sheetTypesData && sheetTypesData[sheetType]) {
            // Use price_per_sheet from database
            sheetCostPerPage = sheetTypesData[sheetType].price_per_sheet || 0;
        } else {
            // Fallback: calculate from base cost and multiplier
            let baseCost = 0;
            if (printSize && pricingRates.size[printSize]) {
                baseCost = pricingRates.size[printSize];
            } else if (printSize === 'Custom') {
                baseCost = pricingRates.size['Custom'];
            }

            // Apply sheet type multiplier if available
            if (sheetType && pricingRates.sheetType[sheetType]) {
                sheetCostPerPage = baseCost * pricingRates.sheetType[sheetType];
            } else {
                sheetCostPerPage = baseCost;
            }
        }

        // Apply quality multiplier
        if (printQuality && pricingRates.quality[printQuality]) {
            sheetCostPerPage *= pricingRates.quality[printQuality];
        }

        // Calculate subtotal (sheet cost * page count)
        const subtotal = sheetCostPerPage * pageCount;

        // Get binding cost
        const bindingCost = pricingRates.binding[bindingType] || 0;

        // Shipping cost
        const shipping = pageCount > 0 ? pricingRates.shipping : 0;

        // Calculate base total (before bundle quantity)
        const baseTotal = subtotal + bindingCost + shipping;

        // Total = base total * bundle quantity
        const total = baseTotal * bundleQuantity;

        // Update display
        document.getElementById('pricingPerPage').textContent = formatPrice(sheetCostPerPage);
        document.getElementById('pricingSubtotal').textContent = formatPrice(subtotal);
        document.getElementById('pricingBindingCost').textContent = formatPrice(bindingCost);
        document.getElementById('pricingShipping').textContent = formatPrice(shipping);
        document.getElementById('pricingTotal').textContent = formatPrice(total);
    }

    // Handle print size change to show/hide custom size fields and update pricing
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'flipbookPrintSize') {
            const customSizeContainer = document.getElementById('flipbookCustomSizeContainer');
            if (customSizeContainer) {
                if (e.target.value === 'Custom') {
                    customSizeContainer.style.display = 'grid';
                } else {
                    customSizeContainer.style.display = 'none';
                }
            }
            calculateFlipbookPricing();
        }

        // Update pricing when other fields change
        if (e.target && (
            e.target.id === 'flipbookPrintSheetType' ||
            e.target.id === 'flipbookPrintQuality' ||
            e.target.id === 'flipbookBindingType'
        )) {
            calculateFlipbookPricing();
        }
    });

    // Make functions globally accessible
    window.openFontLibraryModal = function(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        const modal = document.getElementById('fontLibraryModal');
        if (modal) {
            modal.style.display = 'flex';
            if (typeof loadFontLibrary === 'function') {
                loadFontLibrary();
            }
        } else {
            console.error('Font library modal not found');
        }
    };

    // Layer Style Modal Functions
    function openLayerStyleModal() {
        if (!canvas || !currentObject) {
            alert('Please select an object first');
            return;
        }

        const modal = document.getElementById('layerStyleModal');
        if (modal) {
            modal.style.display = 'flex';
            loadCurrentObjectStyles();
        }
    }

    function closeLayerStyleModal() {
        const modal = document.getElementById('layerStyleModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function loadCurrentObjectStyles() {
        if (!currentObject) return;

        // Load shadow
        if (currentObject.shadow) {
            document.getElementById('shadowEnabled').checked = true;
            document.getElementById('shadowControls').style.display = 'block';
            document.getElementById('shadowOffsetX').value = currentObject.shadow.offsetX || 5;
            document.getElementById('shadowOffsetXValue').value = currentObject.shadow.offsetX || 5;
            document.getElementById('shadowOffsetY').value = currentObject.shadow.offsetY || 5;
            document.getElementById('shadowOffsetYValue').value = currentObject.shadow.offsetY || 5;
            document.getElementById('shadowBlur').value = currentObject.shadow.blur || 10;
            document.getElementById('shadowBlurValue').value = currentObject.shadow.blur || 10;
            document.getElementById('shadowColor').value = rgbToHex(currentObject.shadow.color || '#000000');
            const opacity = currentObject.shadow.opacity || 0.5;
            document.getElementById('shadowOpacity').value = opacity;
            document.getElementById('shadowOpacityValue').textContent = Math.round(opacity * 100) + '%';
        } else {
            document.getElementById('shadowEnabled').checked = false;
            document.getElementById('shadowControls').style.display = 'none';
        }

        // Load fill
        if (currentObject.fill && typeof currentObject.fill === 'object' && currentObject.fill.colorStops) {
            // Gradient
            document.getElementById('fillType').value = 'gradient';
            toggleFillType();
            // Load gradient properties
        } else {
            // Solid color
            document.getElementById('fillType').value = 'solid';
            toggleFillType();
            if (currentObject.fill) {
                document.getElementById('fillColor').value = rgbToHex(currentObject.fill);
            }
        }
        document.getElementById('fillOpacity').value = currentObject.opacity || 1;
        document.getElementById('fillOpacityValue').textContent = Math.round((currentObject.opacity || 1) * 100) + '%';

        // Load stroke
        if (currentObject.stroke && currentObject.strokeWidth > 0) {
            document.getElementById('strokeEnabled').checked = true;
            document.getElementById('strokeControls').style.display = 'block';
            document.getElementById('strokeWidth').value = currentObject.strokeWidth || 1;
            document.getElementById('strokeWidthValue').value = currentObject.strokeWidth || 1;
            document.getElementById('strokeColor').value = rgbToHex(currentObject.stroke || '#000000');
            if (currentObject.strokeDashArray) {
                document.getElementById('strokeDashArray').value = currentObject.strokeDashArray.join(',');
            }
            document.getElementById('strokeLineCap').value = currentObject.strokeLineCap || 'butt';
            document.getElementById('strokeLineJoin').value = currentObject.strokeLineJoin || 'miter';
        } else {
            document.getElementById('strokeEnabled').checked = false;
            document.getElementById('strokeControls').style.display = 'none';
        }

        // Load blur
        if (currentObject.filters && currentObject.filters.length > 0 && currentObject.filters.some(f => f.type === 'Blur')) {
            document.getElementById('blurEnabled').checked = true;
            document.getElementById('blurControls').style.display = 'block';
            const blurFilter = currentObject.filters.find(f => f.type === 'Blur');
            document.getElementById('blurValue').value = blurFilter.blur || 5;
            document.getElementById('blurValueInput').value = blurFilter.blur || 5;
        } else {
            document.getElementById('blurEnabled').checked = false;
            document.getElementById('blurControls').style.display = 'none';
        }

        // Load opacity
        document.getElementById('objectOpacity').value = currentObject.opacity || 1;
        document.getElementById('objectOpacityValue').textContent = Math.round((currentObject.opacity || 1) * 100) + '%';
    }

    function toggleShadow() {
        const enabled = document.getElementById('shadowEnabled').checked;
        document.getElementById('shadowControls').style.display = enabled ? 'block' : 'none';
        if (enabled) {
            updateShadowPreview();
        } else {
            if (currentObject) {
                currentObject.shadow = null;
                canvas.renderAll();
            }
        }
    }

    function updateShadowPreview() {
        if (!currentObject) return;

        const offsetX = parseInt(document.getElementById('shadowOffsetX').value);
        const offsetY = parseInt(document.getElementById('shadowOffsetY').value);
        const blur = parseInt(document.getElementById('shadowBlur').value);
        const color = document.getElementById('shadowColor').value;
        const opacity = parseFloat(document.getElementById('shadowOpacity').value);

        document.getElementById('shadowOffsetXValue').value = offsetX;
        document.getElementById('shadowOffsetYValue').value = offsetY;
        document.getElementById('shadowBlurValue').value = blur;
        document.getElementById('shadowOpacityValue').textContent = Math.round(opacity * 100) + '%';

        if (document.getElementById('shadowEnabled').checked) {
            currentObject.setShadow({
                offsetX: offsetX,
                offsetY: offsetY,
                blur: blur,
                color: color,
                opacity: opacity
            });
            canvas.renderAll();
        }
    }

    function updateShadowFromInput(type) {
        if (type === 'offsetX') {
            const input = document.getElementById('shadowOffsetXValue');
            document.getElementById('shadowOffsetX').value = input.value;
        } else if (type === 'offsetY') {
            const input = document.getElementById('shadowOffsetYValue');
            document.getElementById('shadowOffsetY').value = input.value;
        } else if (type === 'blur') {
            const input = document.getElementById('shadowBlurValue');
            document.getElementById('shadowBlur').value = input.value;
        }
        updateShadowPreview();
    }

    function toggleFillType() {
        const fillType = document.getElementById('fillType').value;
        if (fillType === 'solid') {
            document.getElementById('solidFillControls').style.display = 'block';
            document.getElementById('gradientFillControls').style.display = 'none';
        } else {
            document.getElementById('solidFillControls').style.display = 'none';
            document.getElementById('gradientFillControls').style.display = 'block';
        }
        updateFillPreview();
    }

    function updateFillPreview() {
        if (!currentObject) return;

        const fillType = document.getElementById('fillType').value;

        if (fillType === 'solid') {
            const color = document.getElementById('fillColor').value;
            const opacity = parseFloat(document.getElementById('fillOpacity').value);
            document.getElementById('fillOpacityValue').textContent = Math.round(opacity * 100) + '%';

            // Apply color with opacity
            const rgba = hexToRgba(color, opacity);
            currentObject.set('fill', rgba);
        } else {
            updateGradientPreview();
        }

        canvas.renderAll();
    }

    function updateGradientPreview() {
        if (!currentObject) return;

        const gradientType = document.getElementById('gradientType').value;
        const angle = parseInt(document.getElementById('gradientAngle').value);
        const color1 = document.getElementById('gradientColor1').value;
        const color2 = document.getElementById('gradientColor2').value;
        const stop1 = parseInt(document.getElementById('gradientStop1').value) / 100;
        const stop2 = parseInt(document.getElementById('gradientStop2').value) / 100;

        document.getElementById('gradientAngleValue').value = angle;
        document.getElementById('gradientStop1Value').textContent = Math.round(stop1 * 100) + '%';
        document.getElementById('gradientStop2Value').textContent = Math.round(stop2 * 100) + '%';

        if (gradientType === 'linear') {
            const radians = (angle * Math.PI) / 180;
            const coords = {
                x1: Math.cos(radians) * currentObject.width * 0.5,
                y1: Math.sin(radians) * currentObject.height * 0.5,
                x2: -Math.cos(radians) * currentObject.width * 0.5,
                y2: -Math.sin(radians) * currentObject.height * 0.5
            };

            const gradient = new fabric.Gradient({
                type: 'linear',
                coords: coords,
                colorStops: [
                    { offset: stop1, color: color1 },
                    { offset: stop2, color: color2 }
                ]
            });
            currentObject.set('fill', gradient);
        } else {
            const gradient = new fabric.Gradient({
                type: 'radial',
                coords: {
                    x1: currentObject.width * 0.5,
                    y1: currentObject.height * 0.5,
                    r1: 0,
                    x2: currentObject.width * 0.5,
                    y2: currentObject.height * 0.5,
                    r2: Math.max(currentObject.width, currentObject.height) * 0.5
                },
                colorStops: [
                    { offset: stop1, color: color1 },
                    { offset: stop2, color: color2 }
                ]
            });
            currentObject.set('fill', gradient);
        }

        canvas.renderAll();
    }

    function updateGradientFromInput(type) {
        if (type === 'angle') {
            const input = document.getElementById('gradientAngleValue');
            document.getElementById('gradientAngle').value = input.value;
        }
        updateGradientPreview();
    }

    function addGradientStop() {
        // Implementation for adding more gradient stops
        alert('Multiple gradient stops feature coming soon!');
    }

    function toggleStroke() {
        const enabled = document.getElementById('strokeEnabled').checked;
        document.getElementById('strokeControls').style.display = enabled ? 'block' : 'none';
        if (enabled) {
            updateStrokePreview();
        } else {
            if (currentObject) {
                currentObject.set('stroke', '');
                currentObject.set('strokeWidth', 0);
                canvas.renderAll();
            }
        }
    }

    function updateStrokePreview() {
        if (!currentObject) return;

        const width = parseFloat(document.getElementById('strokeWidth').value);
        const color = document.getElementById('strokeColor').value;
        const dashArray = document.getElementById('strokeDashArray').value;
        const lineCap = document.getElementById('strokeLineCap').value;
        const lineJoin = document.getElementById('strokeLineJoin').value;

        document.getElementById('strokeWidthValue').value = width;

        if (document.getElementById('strokeEnabled').checked) {
            currentObject.set('stroke', color);
            currentObject.set('strokeWidth', width);
            if (['rect', 'circle', 'triangle', 'line'].indexOf(currentObject.type) !== -1) {
                currentObject.set('strokeUniform', true);
            }
            if (dashArray) {
                currentObject.set('strokeDashArray', dashArray.split(',').map(v => parseFloat(v.trim())));
            } else {
                currentObject.set('strokeDashArray', []);
            }
            currentObject.set('strokeLineCap', lineCap);
            currentObject.set('strokeLineJoin', lineJoin);
            canvas.renderAll();
        }
    }

    function updateStrokeFromInput(type) {
        if (type === 'width') {
            const input = document.getElementById('strokeWidthValue');
            document.getElementById('strokeWidth').value = input.value;
        }
        updateStrokePreview();
    }

    function toggleBlur() {
        const enabled = document.getElementById('blurEnabled').checked;
        document.getElementById('blurControls').style.display = enabled ? 'block' : 'none';
        if (enabled) {
            updateBlurPreview();
        } else {
            if (currentObject && currentObject.filters) {
                currentObject.filters = currentObject.filters.filter(f => f.type !== 'Blur');
                currentObject.applyFilters();
                canvas.renderAll();
            }
        }
    }

    function updateBlurPreview() {
        if (!currentObject) return;

        const blurValue = parseFloat(document.getElementById('blurValue').value);
        document.getElementById('blurValueInput').value = blurValue;

        if (document.getElementById('blurEnabled').checked) {
            if (!currentObject.filters) {
                currentObject.filters = [];
            }

            // Remove existing blur filter
            currentObject.filters = currentObject.filters.filter(f => f.type !== 'Blur');

            // Add new blur filter
            const blurFilter = new fabric.Image.filters.Blur({ blur: blurValue });
            currentObject.filters.push(blurFilter);
            currentObject.applyFilters();
            canvas.renderAll();
        }
    }

    function updateBlurFromInput() {
        const input = document.getElementById('blurValueInput');
        document.getElementById('blurValue').value = input.value;
        updateBlurPreview();
    }

    function updateObjectOpacityPreview() {
        if (!currentObject) return;

        const opacity = parseFloat(document.getElementById('objectOpacity').value);
        document.getElementById('objectOpacityValue').textContent = Math.round(opacity * 100) + '%';

        currentObject.set('opacity', opacity);
        canvas.renderAll();
    }

    function applyLayerStyle() {
        // All styles are already applied in real-time via preview functions
        closeLayerStyleModal();
        saveState();
    }

    function resetLayerStyle() {
        if (!currentObject) return;

        // Reset all styles to defaults
        currentObject.setShadow(null);
        currentObject.set('fill', '#000000');
        currentObject.set('opacity', 1);
        currentObject.set('stroke', '');
        currentObject.set('strokeWidth', 0);
        currentObject.set('strokeDashArray', []);
        if (currentObject.filters) {
            currentObject.filters = currentObject.filters.filter(f => f.type !== 'Blur');
            currentObject.applyFilters();
        }

        canvas.renderAll();
        loadCurrentObjectStyles();
        saveState();
    }

    function hexToRgba(hex, opacity) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${opacity})`;
    }

    window.closeFontLibraryModal = function() {
        const modal = document.getElementById('fontLibraryModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    function getDefaultDocumentName() {
        let n = 1;
        try {
            const stored = sessionStorage.getItem('flipbook_document_count');
            n = (parseInt(stored, 10) || 0) + 1;
        } catch (e) {}
        return 'Document ' + n;
    }

    // Project Name Modal Functions
    function openProjectNameModal() {
        const modal = document.getElementById('projectNameModal');
        if (modal) {
            const input = document.getElementById('projectNameInput');
            if (input) {
                input.value = projectName || getDefaultDocumentName();
                input.focus();
            }

            // Load templates into dropdown
            loadTemplatesForModal();

            // Show custom size inputs if custom is selected
            const pageSizeSelect = document.getElementById('projectPageSize');
            if (pageSizeSelect) {
                pageSizeSelect.addEventListener('change', handlePageSizeChange);
                handlePageSizeChange(); // Initial check
            }

            modal.style.display = 'block';
        }
    }

    function closeProjectNameModal() {
        const modal = document.getElementById('projectNameModal');
        if (modal) {
            modal.style.display = 'none';
        }
        runDesignIntroAfterModalClose();
    }

    function handlePageSizeChange() {
        const pageSizeSelect = document.getElementById('projectPageSize');
        const customSizeInputs = document.getElementById('customSizeInputs');

        if (pageSizeSelect && customSizeInputs) {
            if (pageSizeSelect.value === 'custom') {
                customSizeInputs.style.display = 'block';
            } else {
                customSizeInputs.style.display = 'none';
            }
        }
    }

    function loadTemplatesForModal() {
        const templateSelect = document.getElementById('projectPageTemplate');
        if (!templateSelect) return;

        // Clear existing options except the first one
        templateSelect.innerHTML = '<option value="">Start from blank</option>';

        // Fetch templates
        fetch('{{ route("design.templates.index") }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.templates && data.templates.length > 0) {
                data.templates.forEach(template => {
                    const option = document.createElement('option');
                    option.value = template.id;
                    option.textContent = template.name + (template.page_count ? ` (${template.page_count} pages)` : '');
                    templateSelect.appendChild(option);
                });
            } else {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No templates available';
                option.disabled = true;
                templateSelect.appendChild(option);
            }
        })
        .catch(error => {
            console.error('Error loading templates:', error);
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Error loading templates';
            option.disabled = true;
            templateSelect.appendChild(option);
        });
    }

    function getPageSizeDimensions(sizeValue) {
        const sizeMap = {
            '800x1000': { width: 800, height: 1000 },
            'VisitingCard': { width: 1000, height: 600 },
            'A4': { width: 794, height: 1123 },
            'Letter': { width: 816, height: 1056 },
            'A3': { width: 1123, height: 1587 },
            'A5': { width: 559, height: 794 },
            'Legal': { width: 816, height: 1344 },
            'Tabloid': { width: 1056, height: 1632 },
            'Square': { width: 1000, height: 1000 },
            'Instagram': { width: 1080, height: 1080 },
            'Facebook': { width: 1200, height: 630 }
        };

        if (sizeValue === 'custom') {
            const customWidth = parseInt(document.getElementById('customWidth')?.value) || editorSettings.canvasWidth;
            const customHeight = parseInt(document.getElementById('customHeight')?.value) || editorSettings.canvasHeight;
            return { width: customWidth, height: customHeight };
        }

        return sizeMap[sizeValue] || (designType === 'visiting-cards' ? sizeMap['VisitingCard'] : sizeMap['800x1000']);
    }

    function confirmProjectName() {
        const input = document.getElementById('projectNameInput');
        if (!input || input.value.trim() === '') {
            alert('Please enter a project name.');
            if (input) input.focus();
            return;
        }

        projectName = input.value.trim();

        try {
            const stored = sessionStorage.getItem('flipbook_document_count');
            const n = (parseInt(stored, 10) || 0) + 1;
            sessionStorage.setItem('flipbook_document_count', String(n));
        } catch (e) {}

        // Get selected page size
        const pageSizeSelect = document.getElementById('projectPageSize');
        const defaultPageSize = designType === 'visiting-cards' ? 'VisitingCard' : '800x1000';
        const pageSize = pageSizeSelect ? pageSizeSelect.value : defaultPageSize;
        const dimensions = getPageSizeDimensions(pageSize);

        // Get selected template
        const templateSelect = document.getElementById('projectPageTemplate');
        const selectedTemplateId = templateSelect ? templateSelect.value : '';

        // Close modal
        const modal = document.getElementById('projectNameModal');
        if (modal) {
            modal.style.display = 'none';
        }
        runDesignIntroAfterModalClose();

        // Apply page size to canvas if it exists
        if (canvas && pages.length > 0) {
            canvas.setWidth(dimensions.width);
            canvas.setHeight(dimensions.height);
            canvas.renderAll();

            // Update current page data with new dimensions
            if (currentPageIndex >= 0 && pages[currentPageIndex]) {
                try {
                    const currentPageData = JSON.parse(pages[currentPageIndex].data);
                    currentPageData.width = dimensions.width;
                    currentPageData.height = dimensions.height;
                    pages[currentPageIndex].data = JSON.stringify(currentPageData);
                } catch (e) {
                    console.error('Error updating page dimensions:', e);
                }
            }

            // Update settings panel
            const widthInput = document.getElementById('canvasWidth');
            const heightInput = document.getElementById('canvasHeight');
            if (widthInput) widthInput.value = dimensions.width;
            if (heightInput) heightInput.value = dimensions.height;
        }

        // Load template if selected
        if (selectedTemplateId) {
            setTimeout(() => {
                loadTemplateSilently(selectedTemplateId);
            }, 300);
        }

        // If save was triggered, retry it
        if (typeof pendingSave === 'function') {
            pendingSave();
            pendingSave = null;
        }
    }

    // Allow Enter key to confirm project name
    document.addEventListener('DOMContentLoaded', function() {
        const projectNameInput = document.getElementById('projectNameInput');
        if (projectNameInput) {
            projectNameInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    confirmProjectName();
                }
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize vertical tabs - show Pages by default
        switchVerticalTab('pages');

        // Preload image library (loads in background)
        setTimeout(() => {
            if (typeof loadImageLibrary === 'function') {
                loadImageLibrary();
            }
        }, 1000);

        // Load custom fonts on page load
        setTimeout(() => {
            if (typeof loadFontsOnInit === 'function') {
                loadFontsOnInit();
            }
        }, 1500);

        // Initialize canvas first (use dynamic editor settings from admin)
        canvas = new fabric.Canvas('fabricCanvas', {
            width: editorSettings.canvasWidth,
            height: editorSettings.canvasHeight,
            backgroundColor: editorSettings.backgroundColor,
            selection: true,
            preserveObjectStacking: true
        });

        // Override Textbox to support fixed adjustable height
        // Store original methods
        const originalTextboxInit = fabric.Textbox.prototype.initialize;
        const originalTextboxCalcDimensions = fabric.Textbox.prototype._calcDimensions;

        // Override initialize to add fixed height support
        fabric.Textbox.prototype.initialize = function(text, options) {
            const result = originalTextboxInit.call(this, text, options);
            this._fixedHeight = options && options.fixedHeight ? options.fixedHeight : null;
            this._hasFixedHeight = options && options.hasFixedHeight ? true : false;
            return result;
        };

        // Override _calcDimensions to respect fixed height
        fabric.Textbox.prototype._calcDimensions = function() {
            // Call original to calculate width and other dimensions
            if (originalTextboxCalcDimensions) {
                originalTextboxCalcDimensions.call(this);
            }

            // If this textbox has a fixed height, use it instead of calculated height
            if (this._hasFixedHeight && this._fixedHeight) {
                this.height = this._fixedHeight;
                this.scaleY = 1;
            }
        };

        // Save state for undo/redo
        function saveState() {
            if (pages.length > 0 && currentPageIndex >= 0) {
                try {
                    // Get canvas JSON and filter out guide lines
                    const canvasData = canvas.toJSON(['width', 'height', 'backgroundColor']);
                    if (canvasData.objects) {
                        canvasData.objects = canvasData.objects.filter(obj => obj.name !== 'alignmentGuide');
                    }
                    // Ensure width and height are always stored
                    canvasData.width = canvas.getWidth();
                    canvasData.height = canvas.getHeight();
                    canvasData.backgroundColor = canvas.backgroundColor || '#ffffff';
                    pages[currentPageIndex].data = JSON.stringify(canvasData);
                    updatePageThumbnail(currentPageIndex);
                } catch (e) {
                    console.error('Error saving state:', e);
                }
            }
            // Mark design as modified when user makes changes (skip during initial template load)
            if (sourceTemplateId && !skipDesignModifiedDuringLoad) designModified = true;
        }

        // Canvas event handlers
        canvas.on('object:added', saveState);
        canvas.on('object:removed', saveState);
        canvas.on('object:modified', saveState);

        // Prevent moving/scaling locked objects and add alignment guides
        canvas.on('object:moving', function(e) {
            if (e.target.locked) {
                e.target.setCoords();
                return false;
            }
            // Show alignment guides
            showAlignmentGuides(e.target);
        });

        canvas.on('object:scaling', function(e) {
            if (e.target.locked) {
                e.target.setCoords();
                return false;
            }

            // For textboxes, convert scaling to width/height changes
            if (e.target.type === 'textbox') {
                const obj = e.target;
                const newWidth = obj.width * obj.scaleX;
                const newHeight = obj.height * obj.scaleY;

                // Update width and height directly, reset scales
                obj.set({
                    width: newWidth,
                    height: newHeight,
                    scaleX: 1,
                    scaleY: 1
                });

                // Update fixed height and mark as having fixed height
                obj._fixedHeight = newHeight;
                obj._hasFixedHeight = true;

                // Force recalculation to apply fixed height
                obj._calcDimensions();
            }

            // Show alignment guides
            showAlignmentGuides(e.target);
        });

        // Clear guides when object movement ends and handle textbox/shape scaling conversion
        canvas.on('object:modified', function(e) {
            clearAlignmentGuides();

            // For textboxes, ensure scales are reset after modification
            if (e.target && e.target.type === 'textbox') {
                const obj = e.target;
                // If scales are not 1, convert them to width/height
                if (obj.scaleX !== 1 || obj.scaleY !== 1) {
                    const newWidth = obj.width * obj.scaleX;
                    const newHeight = obj.height * obj.scaleY;

                    // Update fixed height and mark as having fixed height
                    obj._fixedHeight = newHeight;
                    obj._hasFixedHeight = true;

                    obj.set({
                        width: newWidth,
                        height: newHeight,
                        scaleX: 1,
                        scaleY: 1
                    });

                    // Force recalculation to apply fixed height
                    obj._calcDimensions();

                    // Update properties panel if this is the current object
                    if (currentObject === obj) {
                        updatePropertiesPanel(obj);
                    }
                }
            }

            // For shapes: bake scale into dimensions so border radius and stroke stay correct
            const shapeTypes = ['rect', 'circle', 'triangle', 'line'];
            if (e.target && shapeTypes.indexOf(e.target.type) !== -1) {
                bakeShapeScaleIntoDimensions(e.target);
                if (currentObject === e.target) {
                    updatePropertiesPanel(e.target);
                }
            }
        });

        canvas.on('mouse:up', function(e) {
            clearAlignmentGuides();
        });

        canvas.on('selection:cleared', function() {
            clearAlignmentGuides();
        });

        canvas.on('object:rotating', function(e) {
            if (e.target.locked) {
                e.target.setCoords();
                return false;
            }
        });

        // Prevent editing locked text
        canvas.on('text:editing:entered', function(e) {
            if (e.target.locked) {
                e.target.exitEditing();
                return false;
            }
        });

        canvas.on('selection:created', function(e) {
            if (e.selected && e.selected.length > 0) {
                const selected = e.selected[0];
                // Prevent selection of locked objects
                if (selected.locked) {
                    canvas.discardActiveObject();
                    return false;
                }
                updatePropertiesPanel(selected);
            }
            refreshLayers();
        });

        canvas.on('selection:updated', function(e) {
            if (e.selected && e.selected.length > 0) {
                updatePropertiesPanel(e.selected[0]);
            }
            refreshLayers();
        });

        canvas.on('selection:cleared', function() {
            hidePropertiesPanel();
            refreshLayers();
        });

        canvas.on('object:added', function() {
            refreshLayers();
        });

        canvas.on('object:removed', function() {
            refreshLayers();
        });

        // Context menu event handlers
        let contextMenuVisible = false;
        let contextMenuX = 0;
        let contextMenuY = 0;

        // Hide context menu on left-click (but not right-click)
        canvas.on('mouse:down', function(e) {
            // Only hide on left-click (button 0), not right-click (button 2)
            if (e.e && e.e.button === 0) {
                hideContextMenu();
            }
        });

        // Handle right-click on canvas
        function handleContextMenu(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!editorSettings.showContextMenu) return;

            const pointer = canvas.getPointer(e);
            const target = canvas.findTarget(e, false);
            const activeObject = canvas.getActiveObject();

            console.log('Context menu triggered', { target, activeObject, pointer });

            // Check if we clicked on an object or if there's already a selected object
            if (target && !target.locked) {
                // Select the object if not already selected
                if (activeObject !== target) {
                    canvas.setActiveObject(target);
                    canvas.renderAll();
                }

                // Show context menu at cursor position
                showContextMenu(e.clientX, e.clientY);
            } else if (activeObject && !activeObject.locked) {
                // If there's an active object but we clicked on empty space, still show menu
                showContextMenu(e.clientX, e.clientY);
            } else {
                // Right-click on empty canvas - hide menu
                hideContextMenu();
            }
        }

        // Attach to canvas element
        const canvasElement = canvas.getElement();
        if (canvasElement) {
            canvasElement.addEventListener('contextmenu', handleContextMenu, true);
        }

        // Also attach to canvas wrapper as fallback
        const canvasWrapper = document.getElementById('canvasWrapper');
        if (canvasWrapper) {
            canvasWrapper.addEventListener('contextmenu', handleContextMenu, true);
        }

        // Hide context menu when clicking outside
        document.addEventListener('click', function(e) {
            const contextMenu = document.getElementById('contextMenu');
            if (contextMenu && !contextMenu.contains(e.target)) {
                hideContextMenu();
            }
        });

        // Hide context menu on scroll
        window.addEventListener('scroll', hideContextMenu);
        window.addEventListener('resize', hideContextMenu);

        // Enable inline text editing on double-click, or image cropping for images
        canvas.on('mouse:dblclick', function(e) {
            // Prevent default behavior
            e.e.preventDefault();
            e.e.stopPropagation();

            // Get the target object from the event, or use active object
            const target = e.target;
            const activeObject = canvas.getActiveObject();
            const object = target || activeObject;

            console.log('Double-click event:', {
                target: target,
                activeObject: activeObject,
                targetType: target ? target.type : 'none',
                activeType: activeObject ? activeObject.type : 'none'
            });

            if (!object) {
                console.log('No object found on double-click');
                return;
            }

            console.log('Double-click detected on object type:', object.type, object);

            // Handle table double-click - open table editor modal
            if (object.type === 'group' && object.tableType === 'table') {
                console.log('Opening table editor modal for table object');
                // Make sure object is selected
                if (activeObject !== object) {
                    canvas.setActiveObject(object);
                    canvas.renderAll();
                }
                // Use setTimeout to ensure selection is complete
                setTimeout(function() {
                    openTableEditorModal(object);
                }, 50);
                return;
            }

            // Handle image double-click - open crop modal
            if (object.type === 'image') {
                console.log('Opening image crop modal for image object');
                // Make sure object is selected
                if (activeObject !== object) {
                    canvas.setActiveObject(object);
                    canvas.renderAll();
                }
                // Use setTimeout to ensure selection is complete
                setTimeout(function() {
                    openImageCropModal(object);
                }, 50);
                return;
            }

            // Handle text double-click - enter editing mode
            if (object.type === 'text' || object.type === 'i-text' || object.type === 'textbox') {
                // Make sure object is selected
                if (activeObject !== object) {
                    canvas.setActiveObject(object);
                    canvas.renderAll();
                }

                // If it's a regular text object, convert it to IText for editing
                if (object.type === 'text' && !object.enterEditing) {
                    const iText = convertTextToIText(object);
                    if (iText) {
                        canvas.remove(object);
                        canvas.add(iText);
                        canvas.setActiveObject(iText);
                        canvas.renderAll();
                    }
                }

                // Enter editing mode
                const iText = canvas.getActiveObject();
                if (iText && iText.enterEditing) {
                    iText.enterEditing();
                    iText.selectAll();
                }
            }
        });

        // Update properties panel when text is being edited
        canvas.on('text:editing:entered', function(e) {
            if (e.target) {
                updatePropertiesPanel(e.target);
                // Show rich text toolbar when editing starts
                showRichTextToolbar(e.target);
            }
        });

        canvas.on('text:editing:exited', function(e) {
            // Hide rich text toolbar when editing ends
            hideRichTextToolbar();
        });

        // Listen for text changes to update toolbar state
        canvas.on('text:changed', function(e) {
            if (e.target && e.target.isEditing) {
                // Use setTimeout to allow selection to update
                setTimeout(() => {
                    updateRichTextToolbarState(e.target);
                }, 10);
            }
        });

        canvas.on('text:changed', function(e) {
            if (e.target) {
                updatePropertiesPanel(e.target);
                saveState();
            }
        });

        // Apply default zoom
        updateZoom();

        // Touchpad/Trackpad Zoom Support
        const canvasContainer = document.getElementById('canvasContainer');
        if (canvasContainer) {
            let isZooming = false;
            let lastWheelTime = 0;
            let wheelTimeout;

            // Handle wheel events for trackpad/touchpad zoom
            canvasContainer.addEventListener('wheel', function(e) {
                // Check if Ctrl/Cmd key is pressed (standard zoom gesture)
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();

                    // Calculate zoom delta
                    const delta = e.deltaY;
                    const zoomFactor = 0.1; // Adjust zoom sensitivity

                    // Calculate new zoom level
                    let newZoom = zoomLevel;
                    if (delta < 0) {
                        // Zoom in
                        newZoom = Math.min(zoomLevel + (zoomLevel * zoomFactor), 200);
                    } else {
                        // Zoom out
                        newZoom = Math.max(zoomLevel - (zoomLevel * zoomFactor), 50);
                    }

                    // Smooth zoom with throttling
                    const now = Date.now();
                    if (now - lastWheelTime > 16) { // ~60fps throttling
                        zoomLevel = newZoom;
                        updateZoom();
                        lastWheelTime = now;
                    } else {
                        // Debounce rapid wheel events
                        clearTimeout(wheelTimeout);
                        wheelTimeout = setTimeout(() => {
                            zoomLevel = newZoom;
                            updateZoom();
                        }, 16);
                    }
                }
            }, { passive: false });

            // Touch gesture support for pinch-to-zoom
            let initialDistance = 0;
            let initialZoom = 50;
            let touchStartTime = 0;

            canvasContainer.addEventListener('touchstart', function(e) {
                if (e.touches.length === 2) {
                    e.preventDefault();
                    const touch1 = e.touches[0];
                    const touch2 = e.touches[1];
                    initialDistance = Math.hypot(
                        touch2.clientX - touch1.clientX,
                        touch2.clientY - touch1.clientY
                    );
                    initialZoom = zoomLevel;
                    touchStartTime = Date.now();
                    isZooming = true;
                }
            }, { passive: false });

            canvasContainer.addEventListener('touchmove', function(e) {
                if (e.touches.length === 2 && isZooming) {
                    e.preventDefault();
                    const touch1 = e.touches[0];
                    const touch2 = e.touches[1];
                    const currentDistance = Math.hypot(
                        touch2.clientX - touch1.clientX,
                        touch2.clientY - touch1.clientY
                    );

                    // Calculate zoom based on distance change
                    const distanceRatio = currentDistance / initialDistance;
                    const newZoom = Math.max(50, Math.min(200, initialZoom * distanceRatio));

                    // Throttle updates for performance
                    const now = Date.now();
                    if (now - lastWheelTime > 16) {
                        zoomLevel = newZoom;
                        updateZoom();
                        lastWheelTime = now;
                    }
                }
            }, { passive: false });

            canvasContainer.addEventListener('touchend', function(e) {
                if (isZooming && e.touches.length < 2) {
                    isZooming = false;
                    initialDistance = 0;
                }
            }, { passive: false });

            // Prevent default scroll behavior when zooming
            canvasContainer.addEventListener('gesturestart', function(e) {
                e.preventDefault();
            }, { passive: false });

            canvasContainer.addEventListener('gesturechange', function(e) {
                e.preventDefault();
                if (e.scale !== 1) {
                    const newZoom = Math.max(50, Math.min(200, initialZoom * e.scale));
                    zoomLevel = newZoom;
                    updateZoom();
                }
            }, { passive: false });

            canvasContainer.addEventListener('gestureend', function(e) {
                e.preventDefault();
                isZooming = false;
            }, { passive: false });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Only handle shortcuts when not typing in input/select
            const tag = document.activeElement.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || document.activeElement.isContentEditable) {
                return;
            }

            // Arrow keys: move selected object(s) - skip when editing text
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].indexOf(e.key) >= 0 && canvas) {
                const obj = canvas.getActiveObject();
                if (obj && !obj.isEditing) {
                    e.preventDefault();
                    const step = e.shiftKey ? 25 : 5;
                    let dx = 0, dy = 0;
                    if (e.key === 'ArrowLeft') dx = -step;
                    else if (e.key === 'ArrowRight') dx = step;
                    else if (e.key === 'ArrowUp') dy = -step;
                    else if (e.key === 'ArrowDown') dy = step;
                    if (obj.type === 'activeSelection') {
                        obj.forEachObject(function(o) {
                            o.set({ left: o.left + dx, top: o.top + dy });
                        });
                        obj.setCoords();
                    } else {
                        obj.set({ left: obj.left + dx, top: obj.top + dy });
                    }
                    canvas.requestRenderAll();
                    designModified = true;
                    return;
                }
            }

            // Ctrl+C or Cmd+C for copy
            if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
                    e.preventDefault();
                copySelected();
            }
            // Ctrl+V or Cmd+V for paste
            else if ((e.ctrlKey || e.metaKey) && e.key === 'v') {
                e.preventDefault();
                pasteSelected();
            }
            // Text formatting shortcuts (only when not in input fields and text is being edited)
            else if ((e.ctrlKey || e.metaKey) && canvas.getActiveObject() && canvas.getActiveObject().isEditing) {
                if (e.key === 'b' || e.key === 'B') {
                    e.preventDefault();
                    applyTextFormat('bold');
                } else if (e.key === 'i' || e.key === 'I') {
                    e.preventDefault();
                    applyTextFormat('italic');
                } else if (e.key === 'u' || e.key === 'U') {
                    e.preventDefault();
                    applyTextFormat('underline');
                }
            }
            // Delete or Backspace for delete (only when not editing text)
            else if ((e.key === 'Delete' || e.key === 'Backspace') && (!canvas.getActiveObject() || !canvas.getActiveObject().isEditing)) {
                e.preventDefault();
                deleteSelected();
            }
        });

        // Initialize rulers from editor settings (show by default if enabled in admin)
        if (editorSettings.showRulers) {
            setTimeout(function() { toggleRulers(); }, 300);
        }

        // Auto-save from editor settings (save every 60s when project has a name)
        if (editorSettings.autoSave) {
            // First save after 15s so refresh works sooner
            setTimeout(function() {
                if (projectName && projectName.trim() !== '' && pages.length > 0 && !currentDesignId) {
                    saveDesign();
                }
            }, 15000);
            setInterval(function() {
                if (projectName && projectName.trim() !== '' && pages.length > 0) {
                    saveDesign();
                }
            }, 60000);
        }

        // Check if editing a flipbook design
        @if(isset($editFlipbookId) && isset($flipbookData))
            const editFlipbookId = {{ $editFlipbookId }};
            const flipbookData = @json($flipbookData);
            const ps = flipbookData && flipbookData.print_settings ? flipbookData.print_settings : null;
            existingFlipbookFormData = flipbookData ? {
                title: flipbookData.title || '',
                description: flipbookData.description || '',
                status: flipbookData.status || 'draft',
                is_public: flipbookData.is_public || false,
                print_sheet_type: ps ? ps.print_sheet_type : null,
                print_size: ps ? ps.print_size : null,
                print_custom_width: ps ? ps.print_custom_width : null,
                print_custom_height: ps ? ps.print_custom_height : null,
                print_quality: ps ? ps.print_quality : null,
                binding_type: ps ? ps.binding_type : null,
                bundle_quantity: ps ? ps.bundle_quantity : 1
            } : null;

            // Wait a moment to ensure canvas is fully initialized
            setTimeout(function() {
                if (flipbookData && flipbookData.design_data && Array.isArray(flipbookData.design_data)) {
                    loadFlipbookDesign(flipbookData.design_data, editFlipbookId);
                } else {
                    addNewPage();
                }
            }, 100);
        @else
            // Check if loading an existing design or template
            const urlParams = new URLSearchParams(window.location.search);
            const loadId = urlParams.get('load');
            let templateId = urlParams.get('template');
            const generatedImageUrl = urlParams.get('generated_image') || @json($generatedImage ?? null);
            const aiTitle = urlParams.get('ai_title') || @json($aiTitle ?? '');
            const aiSubtitle = urlParams.get('ai_subtitle') || @json($aiSubtitle ?? '');
            const aiTemplateId = urlParams.get('ai_template') || @json(isset($aiContentTemplate) ? $aiContentTemplate->id : null);

            // Decode template ID if it's URL encoded
            if (templateId) {
                templateId = decodeURIComponent(templateId);
                console.log('Template ID from URL:', templateId);
            }

            // Wait a moment to ensure canvas is fully initialized
            setTimeout(function() {
                if (aiTemplateId) {
                    loadAiContentTemplate(aiTemplateId);
                } else if (generatedImageUrl) {
                    // Nano Banana generated image - create page with image and text layout
                    loadGeneratedImage(generatedImageUrl, aiTitle, aiSubtitle);
                } else if (templateId) {
                    sourceTemplateId = templateId; // Track source - skip template create on checkout if unchanged
                    console.log('Template ID found, loading template:', templateId);
                    // Load template (silently, without confirmation)
                    // Check if canvas is ready, if not wait a bit more
                    if (!canvas) {
                        console.log('Canvas not ready, waiting 200ms...');
                        setTimeout(() => {
                            console.log('Retrying template load, canvas ready:', !!canvas);
                            loadTemplateSilently(templateId);
                        }, 200);
                    } else {
                        console.log('Canvas ready, loading template immediately');
                        loadTemplateSilently(templateId);
                    }
                } else if (loadId) {
                    loadDesign(loadId);
                    // Don't show project name modal when loading existing design
                } else {
                    // Create first page
                    addNewPage();
                    // Show project name modal for new designs (skip when we have generated image - already handled)
                    const generatedImage = @json($generatedImage ?? null);
                    if (generatedImage && typeof addImageFromLibrary === 'function') {
                        setTimeout(() => addImageFromLibrary(generatedImage), 400);
                    }
                    // Show project name modal for new designs
                    setTimeout(() => {
                        window._projectNameModalShownOnLoad = true;
                        openProjectNameModal();
                    }, 500);
                }
            }, 150);
        @endif
    });

    // Page Management
    function loadGeneratedImage(imageUrl, titleText, subtitleText) {
        if (!canvas || !imageUrl) return;
        const w = editorSettings.canvasWidth || 800;
        const h = editorSettings.canvasHeight || 1000;
        const padding = 50;
        const imgMaxW = w - padding * 2;
        const imgMaxH = Math.floor(h * 0.65) - padding;
        titleText = titleText || 'Your Title';
        subtitleText = subtitleText || 'Add your caption here';

        fabric.Image.fromURL(imageUrl, function(img) {
            if (!img) {
                addNewPage();
                return;
            }
            const scale = Math.min(imgMaxW / img.width, imgMaxH / img.height, 1);
            const imgW = img.width * scale;
            const imgH = img.height * scale;
            const imgLeft = (w - imgW) / 2;
            const imgTop = padding;

            img.set({
                scaleX: scale,
                scaleY: scale,
                left: imgLeft,
                top: imgTop,
                shadow: new fabric.Shadow({
                    color: 'rgba(0,0,0,0.15)',
                    blur: 20,
                    offsetX: 0,
                    offsetY: 8
                }),
                selectable: true,
                evented: true
            });

            var imgFrame = new fabric.Rect({
                left: imgLeft - 8,
                top: imgTop - 8,
                width: imgW + 16,
                height: imgH + 16,
                fill: 'transparent',
                stroke: 'rgba(0,0,0,0.08)',
                strokeWidth: 1,
                selectable: false,
                evented: false
            });

            const title = new fabric.IText(titleText, {
                left: padding,
                top: imgTop + imgH + 24,
                width: w - padding * 2,
                fontSize: 36,
                fontFamily: 'Arial',
                fontWeight: 'bold',
                fill: '#1e293b',
                textAlign: 'center',
                originX: 'center',
                originY: 'top',
                editable: true,
                selectable: true,
                evented: true
            });
            title.set({ left: w / 2 });

            const subtitle = new fabric.IText(subtitleText, {
                left: padding,
                top: imgTop + imgH + 24 + 70,
                width: w - padding * 2,
                fontSize: 18,
                fontFamily: 'Arial',
                fontWeight: 'normal',
                fill: '#64748b',
                textAlign: 'center',
                originX: 'center',
                originY: 'top',
                editable: true,
                selectable: true,
                evented: true
            });
            subtitle.set({ left: w / 2 });

            const pageData = {
                version: '5.3.0',
                objects: [imgFrame.toObject(), img.toObject(), title.toObject(), subtitle.toObject()],
                background: editorSettings.backgroundColor || '#ffffff',
                backgroundColor: editorSettings.backgroundColor || '#ffffff',
                width: w,
                height: h
            };
            pages = [{
                id: 'page_' + Date.now() + '_0',
                index: 0,
                name: 'Page 1',
                data: JSON.stringify(pageData)
            }];
            renderPagesList();
            switchToPage(0);
            setTimeout(() => {
                window._projectNameModalShownOnLoad = true;
                openProjectNameModal();
            }, 500);
        }, { crossOrigin: 'anonymous' });
    }

    function addNewPage() {
        if (!canvas) {
            console.error('Canvas not initialized');
            return;
        }

        // Get current canvas size and background
        const currentWidth = canvas.getWidth();
        const currentHeight = canvas.getHeight();
        const currentBackground = canvas.backgroundColor || '#ffffff';

        const pageIndex = pages.length;
        const page = {
            id: 'page_' + Date.now() + '_' + pageIndex,
            index: pageIndex,
            name: 'Document ' + (pageIndex + 1),
            data: JSON.stringify({
                version: '5.3.0',
                objects: [],
                background: currentBackground,
                backgroundColor: currentBackground,
                width: currentWidth,
                height: currentHeight
            })
        };

        pages.push(page);
        renderPagesList();
        if (sourceTemplateId && !skipDesignModifiedDuringLoad) designModified = true;

        // Switch to the new page
        setTimeout(() => {
            switchToPage(pageIndex);
        }, 50);
    }

    /**
     * Fabric.js 5.3 gradients require colorStops as [{offset,color}] and numeric coords.
     * AI / legacy JSON often uses object stops or invalid coords → addColorStop on undefined.
     */
    function finiteNum(v, d) {
        const n = parseFloat(v);
        return Number.isFinite(n) ? n : d;
    }

    function normalizeFillGradientForFabric(fill, w, h) {
        const out = { type: fill.type, gradientUnits: 'pixels', coords: {}, colorStops: [] };
        let stops = fill.colorStops;
        if (Array.isArray(stops) && stops.length > 0 && typeof stops[0] === 'string') {
            stops.forEach((color, i) => {
                out.colorStops.push({
                    offset: stops.length <= 1 ? 0 : i / (stops.length - 1),
                    color: String(color)
                });
            });
        } else if (Array.isArray(stops)) {
            stops.forEach(s => {
                if (!s || typeof s !== 'object') return;
                if (typeof s.offset === 'number' && s.color != null) {
                    out.colorStops.push({ offset: Math.min(1, Math.max(0, s.offset)), color: String(s.color) });
                } else if (s.pos != null && s.hex != null) {
                    out.colorStops.push({ offset: Math.min(1, Math.max(0, +s.pos)), color: String(s.hex) });
                }
            });
        } else if (stops && typeof stops === 'object') {
            Object.keys(stops).sort((a, b) => +a - +b).forEach(k => {
                if (typeof stops[k] === 'string') {
                    out.colorStops.push({ offset: Math.min(1, Math.max(0, +k)), color: stops[k] });
                }
            });
        }
        if (out.colorStops.length < 2) {
            out.colorStops = [{ offset: 0, color: '#64748b' }, { offset: 1, color: '#0f172a' }];
        }
        const c = fill.coords || {};
        if (fill.type === 'linear') {
            out.coords = {
                x1: finiteNum(c.x1, 0),
                y1: finiteNum(c.y1, 0),
                x2: finiteNum(c.x2, w),
                y2: finiteNum(c.y2, 0)
            };
        } else {
            out.coords = {
                x1: finiteNum(c.x1, w / 2),
                y1: finiteNum(c.y1, h / 2),
                r1: finiteNum(c.r1, 0),
                x2: finiteNum(c.x2, w / 2),
                y2: finiteNum(c.y2, h / 2),
                r2: finiteNum(c.r2, Math.max(w, h) / 2) || Math.max(w, h) / 2
            };
        }
        return out;
    }

    function sanitizeFabricPageDataForLoad(pageData) {
        if (!pageData || !Array.isArray(pageData.objects)) return;
        pageData.objects.forEach(obj => {
            if (!obj || typeof obj !== 'object') return;
            const w = parseFloat(obj.width) || 200;
            const h = parseFloat(obj.height) || 100;
            const r = parseFloat(obj.radius) || 50;
            const gw = obj.type === 'circle' ? r * 2 : w;
            const gh = obj.type === 'circle' ? r * 2 : h;
            if (obj.stroke && typeof obj.stroke === 'object') {
                obj.stroke = '#64748b';
            }
            if (obj.fill && typeof obj.fill === 'object' && (obj.fill.type === 'linear' || obj.fill.type === 'radial')) {
                obj.fill = normalizeFillGradientForFabric(obj.fill, gw, gh);
            } else if (obj.fill && typeof obj.fill === 'object') {
                obj.fill = '#94a3b8';
            }
        });
    }

    function switchToPage(index) {
        if (index < 0 || index >= pages.length) return;

        console.log('Switching to page', index, 'of', pages.length);

        // Clear alignment guides when switching pages
        clearAlignmentGuides();

        // Save current page
        if (pages.length > 0 && currentPageIndex >= 0 && currentPageIndex !== index && canvas) {
            try {
                const currentPageData = canvas.toJSON();
                // Filter out alignment guides
                if (currentPageData.objects) {
                    currentPageData.objects = currentPageData.objects.filter(obj => obj.name !== 'alignmentGuide');
                }
                pages[currentPageIndex].data = JSON.stringify(currentPageData);
                console.log('Saved current page', currentPageIndex, 'with', currentPageData.objects ? currentPageData.objects.length : 0, 'objects');
                updatePageThumbnail(currentPageIndex);
            } catch (e) {
                console.error('Error saving current page:', e);
            }
        }

        // Switch to new page
        currentPageIndex = index;
        const page = pages[index];

        if (!canvas) {
            console.error('Canvas not initialized');
            return;
        }

        if (!page || !page.data) {
            console.error('Page data is missing');
            return;
        }

        try {
            console.log('Loading page data:', page.data.substring(0, 100) + '...');

            // Parse the page data to check it's valid
            let pageData;
            if (typeof page.data === 'string') {
                pageData = JSON.parse(page.data);
            } else {
                pageData = page.data;
            }

            console.log('Page data parsed. Objects:', pageData.objects ? pageData.objects.length : 0, 'Background:', pageData.background);
            console.log('Page canvas size:', pageData.width, 'x', pageData.height);

            sanitizeFabricPageDataForLoad(pageData);
            const pageDataJson = JSON.stringify(pageData);

            // Set canvas size BEFORE loading from JSON to ensure it's applied
            // Use stored size, or current canvas size, or default
            const canvasWidth = pageData.width || canvas.getWidth() || editorSettings.canvasWidth;
            const canvasHeight = pageData.height || canvas.getHeight() || editorSettings.canvasHeight;
            const canvasBg = pageData.backgroundColor || pageData.background || canvas.backgroundColor || '#ffffff';

            console.log('Setting canvas size to:', canvasWidth, 'x', canvasHeight);

            canvas.setWidth(canvasWidth);
            canvas.setHeight(canvasHeight);
            canvas.setBackgroundColor(canvasBg);

            // Load from JSON - this will clear and reload the canvas
            canvas.loadFromJSON(pageDataJson, function() {
                console.log('Canvas loaded from JSON. Objects on canvas:', canvas.getObjects().length);

                // Ensure canvas size is set (loadFromJSON might override it)
                canvas.setWidth(canvasWidth);
                canvas.setHeight(canvasHeight);
                canvas.setBackgroundColor(canvasBg, function() {
                    // Force canvas to recalculate dimensions
                    canvas.calcOffset();
                canvas.renderAll();

                // Update rulers if visible
                if (rulersVisible) {
                    setTimeout(updateRulers, 100);
                }
                });

                // Update settings panel inputs with current canvas size
                document.getElementById('canvasWidth').value = canvasWidth;
                document.getElementById('canvasHeight').value = canvasHeight;
                document.getElementById('canvasBgColor').value = rgbToHex(canvasBg);

                // Force render to ensure size is applied
                canvas.calcOffset();
                        canvas.renderAll();

                // Convert regular Text objects to IText for inline editing
                const objectsToConvert = [];
                canvas.getObjects().forEach(function(obj) {
                    if (obj.type === 'text' && (obj.constructor.name === 'Text' || !obj.enterEditing)) {
                        objectsToConvert.push(obj);
                    } else if (obj.type === 'i-text' || obj.type === 'textbox') {
                        // Ensure editable is enabled
                        obj.set('editable', true);
                    }
                });

                objectsToConvert.forEach(function(obj) {
                    const iText = convertTextToIText(obj);
                    if (iText) {
                        canvas.remove(obj);
                        canvas.add(iText);
                    }
                });

                // Force render to ensure everything is visible
                        canvas.renderAll();
                        refreshLayers();
                console.log('Page', index, 'loaded successfully');
            }, function(o, object) {
                // Called for each object loaded - just return it as-is
                console.log('Loading object:', object.type);
                return object;
            });

            renderPagesList();
            updatePageCounter();
        } catch (e) {
            console.error('Error loading page:', e);
            console.error('Page data:', page.data);
            alert('Error loading page: ' + e.message);
        }
    }

    function deletePage(index) {
        if (pages.length <= 1) {
            alert('You must have at least one page');
            return;
        }

        if (confirm('Are you sure you want to delete this page?')) {
            pages.splice(index, 1);
            if (sourceTemplateId) designModified = true;

            // Reindex pages
            pages.forEach((page, i) => {
                page.index = i;
                page.name = 'Document ' + (i + 1);
            });

            // Switch to appropriate page
            if (currentPageIndex >= pages.length) {
                currentPageIndex = pages.length - 1;
            }

            if (currentPageIndex === index && index > 0) {
                currentPageIndex = index - 1;
            }

            renderPagesList();
            switchToPage(currentPageIndex);
        }
    }

    function duplicatePage(index) {
        const page = pages[index];
        const newPage = {
            id: 'page_' + Date.now() + '_' + pages.length,
            index: pages.length,
            name: 'Document ' + (pages.length + 1),
            data: page.data
        };

        pages.push(newPage);
        if (sourceTemplateId) designModified = true;
        renderPagesList();
        switchToPage(newPage.index);
    }

    function renderPagesList() {
        const container = document.getElementById('pagesList');
        container.innerHTML = '';

        pages.forEach((page, index) => {
            const pageItem = document.createElement('div');
            pageItem.className = 'page-item' + (index === currentPageIndex ? ' active' : '');
            pageItem.onclick = () => switchToPage(index);

            const thumbnail = document.createElement('div');
            thumbnail.className = 'page-thumbnail';
            thumbnail.id = 'thumbnail_' + index;

            const pageNumber = document.createElement('div');
            pageNumber.className = 'page-number';
            pageNumber.textContent = page.name;

            const actions = document.createElement('div');
            actions.className = 'page-actions';
            actions.onclick = (e) => e.stopPropagation();

            const duplicateBtn = document.createElement('button');
            duplicateBtn.className = 'page-action-btn';
            duplicateBtn.innerHTML = '<i class="fas fa-copy"></i>';
            duplicateBtn.onclick = () => duplicatePage(index);
            duplicateBtn.title = 'Duplicate';

            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'page-action-btn delete';
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
            deleteBtn.onclick = () => deletePage(index);
            deleteBtn.title = 'Delete';

            actions.appendChild(duplicateBtn);
            actions.appendChild(deleteBtn);

            pageItem.appendChild(thumbnail);
            pageItem.appendChild(pageNumber);
            pageItem.appendChild(actions);
            container.appendChild(pageItem);

            updatePageThumbnail(index);
        });

        updatePageCounter();
    }

    function updatePageThumbnail(index) {
        if (index < 0 || index >= pages.length) return;

        const thumbnailContainer = document.getElementById('thumbnail_' + index);
        if (!thumbnailContainer) return;

        const page = pages[index];
        try {
            const canvasData = JSON.parse(page.data);
            const tempCanvas = new fabric.Canvas(null, {
                width: 150,
                height: 200,
                backgroundColor: canvasData.background || '#ffffff'
            });

            fabric.util.enlivenObjects(canvasData.objects || [], function(objects) {
                objects.forEach(obj => {
                    // Scale down for thumbnail
                    obj.scaleX = obj.scaleX * 0.2;
                    obj.scaleY = obj.scaleY * 0.2;
                    obj.left = obj.left * 0.2;
                    obj.top = obj.top * 0.2;
                    tempCanvas.add(obj);
                });
                tempCanvas.renderAll();

                const dataURL = tempCanvas.toDataURL();
                thumbnailContainer.innerHTML = '<img src="' + dataURL + '" style="max-width: 100%; max-height: 100%; object-fit: contain;">';
            });
        } catch (e) {
            thumbnailContainer.innerHTML = '<div style="color: #94a3b8; font-size: 0.75rem;">Empty</div>';
        }
    }

    function updatePageCounter() {
        document.getElementById('currentPageNumber').textContent = currentPageIndex + 1;
        document.getElementById('totalPages').textContent = pages.length;
    }

    // Helper function to convert Text to IText for inline editing
    function convertTextToIText(textObj) {
        if (!textObj || textObj.type !== 'text') return textObj;

        const iText = new fabric.IText(textObj.text || '', {
            left: textObj.left,
            top: textObj.top,
            fontSize: textObj.fontSize,
            fontFamily: textObj.fontFamily || 'Arial',
            fontWeight: textObj.fontWeight || 'normal',
            fill: textObj.fill || '#000000',
            textAlign: textObj.textAlign || 'left',
            lineHeight: textObj.lineHeight || 1.2,
            charSpacing: textObj.charSpacing || 0,
            width: textObj.width,
            scaleX: textObj.scaleX || 1,
            scaleY: textObj.scaleY || 1,
            angle: textObj.angle || 0,
            editable: true
        });

        return iText;
    }

    // Helper function to convert Text/IText to Textbox
    function convertTextToTextbox() {
        if (!currentObject) return;

        // Check if it's a text element that can be converted
        if (currentObject.type !== 'text' && currentObject.type !== 'i-text') {
            return;
        }

        // Don't convert if it's already a textbox
        if (currentObject.type === 'textbox') {
            return;
        }

        // Get current text content
        const textContent = currentObject.text || '';

        // Calculate width - use existing width or a default
        const width = currentObject.width || 200;

        // Calculate height - use existing height or calculate from text
        let height = currentObject.height || 100;
        if (currentObject.scaleY && currentObject.scaleY !== 1) {
            height = height * currentObject.scaleY;
        }

        // Calculate actual height from text if available
        if (currentObject.type === 'i-text' || currentObject.type === 'text') {
            // Get the actual rendered height
            const actualHeight = currentObject.height * (currentObject.scaleY || 1);
            if (actualHeight > 0) {
                height = actualHeight;
            }
        }

        // Ensure minimum height
        if (height < 20) {
            height = Math.max(20, (currentObject.fontSize || 16) * (currentObject.lineHeight || 1.2) * 2);
        }

        // Create new Textbox with all properties from the original text object
        const textbox = new fabric.Textbox(textContent, {
            left: currentObject.left,
            top: currentObject.top,
            width: width,
            fontSize: currentObject.fontSize || 16,
            fontFamily: currentObject.fontFamily || 'Arial',
            fontWeight: currentObject.fontWeight || 'normal',
            fill: currentObject.fill || '#000000',
            textAlign: currentObject.textAlign || 'left',
            lineHeight: currentObject.lineHeight || 1.2,
            charSpacing: currentObject.charSpacing || 0,
            scaleX: 1, // Reset scaleX to 1, use width directly
            scaleY: 1, // Reset scaleY to 1, use height directly
            angle: currentObject.angle || 0,
            editable: true,
            splitByGrapheme: currentObject.splitByGrapheme || false,
            fixedHeight: height, // Pass fixed height to initialize
            hasFixedHeight: true // Mark as having fixed height
        });

        // Override height calculation methods to use fixed height
        const originalCalcDimensions = textbox._calcDimensions;
        textbox._calcDimensions = function() {
            // Call original to calculate width
            if (originalCalcDimensions) {
                originalCalcDimensions.call(this);
            }
            // Override height if we have a fixed height
            if (this._hasFixedHeight && this._fixedHeight) {
                this.height = this._fixedHeight;
            }
        };

        // Override _getTextHeight if it exists
        if (textbox._getTextHeight) {
            const originalGetTextHeight = textbox._getTextHeight;
            textbox._getTextHeight = function() {
                if (this._hasFixedHeight && this._fixedHeight) {
                    return this._fixedHeight;
                }
                return originalGetTextHeight.call(this);
            };
        }

        // Set fixed height - this makes the textbox have a fixed adjustable height
        textbox.set({
            height: height,
            scaleY: 1
        });

        // Force recalculation with fixed height
        textbox._calcDimensions();

        // Copy additional properties if they exist
        if (currentObject.shadow) {
            textbox.shadow = currentObject.shadow;
        }
        if (currentObject.opacity !== undefined) {
            textbox.opacity = currentObject.opacity;
        }
        if (currentObject.filters && currentObject.filters.length > 0) {
            textbox.filters = currentObject.filters;
        }

        // Store the fixed height so we can maintain it
        const fixedHeight = height;

        // Replace the old object with the new textbox
        const objects = canvas.getObjects();
        const index = objects.indexOf(currentObject);

        canvas.remove(currentObject);
        canvas.insertAt(textbox, index);
        canvas.setActiveObject(textbox);

        // Set height immediately and store as fixed height
        textbox.set({
            height: fixedHeight,
            scaleX: 1,
            scaleY: 1
        });

        // Store fixed height on the object for reference
        textbox._fixedHeight = fixedHeight;
        textbox._hasFixedHeight = true;

        // Listen for text changes to maintain fixed height if it was manually set
        textbox.on('changed', function() {
            if (textbox._hasFixedHeight && textbox._fixedHeight) {
                // Force height to stay fixed
                if (textbox.height !== textbox._fixedHeight) {
                    textbox.height = textbox._fixedHeight;
                    textbox.scaleY = 1;
                    canvas.renderAll();
                }
            }
        });

        // Also listen for modified event to maintain height
        textbox.on('modified', function() {
            if (textbox._hasFixedHeight && textbox._fixedHeight) {
                if (textbox.height !== textbox._fixedHeight) {
                    textbox.height = textbox._fixedHeight;
                    textbox.scaleY = 1;
                }
            }
        });

        canvas.renderAll();

        // Update properties panel
        currentObject = textbox;
        updatePropertiesPanel(textbox);

        // Save state for undo/redo
        saveState();
    }

    // Design Functions (similar to single-page editor)
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

        const fabricText = new fabric.IText(text, {
            left: 100,
            top: 100,
            fontSize: fontSize,
            fontFamily: 'Arial',
            fontWeight: fontWeight,
            fill: '#000000',
            textAlign: 'left',
            lineHeight: 1.2,
            charSpacing: 0,
            editable: true
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
                    strokeUniform: true
                });
                break;
            case 'circle':
                shape = new fabric.Circle({
                    left: left,
                    top: top,
                    radius: 75,
                    fill: '#6366f1',
                    strokeUniform: true
                });
                break;
            case 'line':
                shape = new fabric.Line([0, 0, 200, 0], {
                    left: left,
                    top: top,
                    stroke: '#6366f1',
                    strokeWidth: 4,
                    strokeUniform: true
                });
                break;
            case 'triangle':
                shape = new fabric.Triangle({
                    left: left,
                    top: top,
                    width: 150,
                    height: 150,
                    fill: '#6366f1',
                    strokeUniform: true
                });
                break;
        }

        canvas.add(shape);
        canvas.setActiveObject(shape);
        canvas.renderAll();
    }

    function bakeShapeScaleIntoDimensions(obj) {
        if (!obj || obj.locked) return;
        const type = obj.type;
        const scaleX = obj.scaleX || 1;
        const scaleY = obj.scaleY || 1;
        if (scaleX === 1 && scaleY === 1) return;

        if (type === 'rect') {
            const w = (obj.width || 0) * scaleX;
            const h = (obj.height || 0) * scaleY;
            const rx = (obj.rx || 0) * Math.min(scaleX, scaleY);
            const ry = (obj.ry || 0) * Math.min(scaleX, scaleY);
            obj.set({ width: w, height: h, rx: rx, ry: ry, scaleX: 1, scaleY: 1 });
            obj.setCoords();
        } else if (type === 'circle') {
            const r = (obj.radius || 0) * Math.min(scaleX, scaleY);
            obj.set({ radius: r, scaleX: 1, scaleY: 1 });
            obj.setCoords();
        } else if (type === 'triangle') {
            const w = (obj.width || 0) * scaleX;
            const h = (obj.height || 0) * scaleY;
            obj.set({ width: w, height: h, scaleX: 1, scaleY: 1 });
            obj.setCoords();
        } else if (type === 'line') {
            const x1 = obj.x1 != null ? obj.x1 : 0;
            const y1 = obj.y1 != null ? obj.y1 : 0;
            const x2 = obj.x2 != null ? obj.x2 : 200;
            const y2 = obj.y2 != null ? obj.y2 : 0;
            const newX2 = x1 + (x2 - x1) * scaleX;
            const newY2 = y1 + (y2 - y1) * scaleY;
            obj.set({ x1: x1, y1: y1, x2: newX2, y2: newY2, scaleX: 1, scaleY: 1 });
            obj.setCoords();
        }
    }

    function addTable() {
        if (!canvas) return;

        const rows = 3;
        const cols = 3;
        const cellWidth = 100;
        const cellHeight = 40;
        const borderWidth = 1;
        const borderColor = '#000000';
        const headerBgColor = '#f3f4f6';
        const cellBgColor = '#ffffff';
        const textColor = '#000000';
        const fontSize = 14;

        const left = 100;
        const top = 100;
        const tableWidth = cols * cellWidth;
        const tableHeight = rows * cellHeight;

        // Create table cells
        const tableObjects = [];

        for (let row = 0; row < rows; row++) {
            for (let col = 0; col < cols; col++) {
                const cellLeft = left + (col * cellWidth);
                const cellTop = top + (row * cellHeight);
                const isHeader = row === 0;

                // Create cell background rectangle
                const cellRect = new fabric.Rect({
                    left: cellLeft,
                    top: cellTop,
                    width: cellWidth,
                    height: cellHeight,
                    fill: isHeader ? headerBgColor : cellBgColor,
                    stroke: borderColor,
                    strokeWidth: borderWidth,
                    selectable: false,
                    evented: false
                });

                // Create cell text
                const cellText = new fabric.Text((isHeader ? 'Header' : 'Cell') + (col + 1), {
                    left: cellLeft + cellWidth / 2,
                    top: cellTop + cellHeight / 2,
                    fontSize: fontSize,
                    fill: textColor,
                    textAlign: 'center',
                    originX: 'center',
                    originY: 'center',
                    selectable: false,
                    evented: false
                });

                tableObjects.push(cellRect, cellText);
            }
        }

        // Create a group for the entire table
        const tableGroup = new fabric.Group(tableObjects, {
            left: left,
            top: top,
            selectable: true,
            hasControls: true,
            hasBorders: true,
            lockUniScaling: false
        });

        // Add custom property to identify as table
        tableGroup.set('tableType', 'table');
        tableGroup.set('tableRows', rows);
        tableGroup.set('tableCols', cols);

        canvas.add(tableGroup);
        canvas.setActiveObject(tableGroup);
        canvas.renderAll();
        saveState();
    }

    function reduceImageSize(imageSrc, callback) {
        if (!editorSettings.imageReduceOnAdd || !imageSrc) {
            callback(imageSrc);
            return;
        }
        const maxDim = editorSettings.imageMaxDimension || 2000;
        const quality = editorSettings.imageQuality || 0.8;
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function() {
            try {
                let w = img.width, h = img.height;
                if (w <= maxDim && h <= maxDim) {
                    callback(imageSrc);
                    return;
                }
                const scale = Math.min(maxDim / w, maxDim / h);
                w = Math.round(w * scale);
                h = Math.round(h * scale);
                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, w, h);
                const mime = imageSrc.startsWith('data:image/') ? (imageSrc.match(/^data:image\/(\w+)/) || [null, 'png'])[1] : 'png';
                const format = mime === 'jpeg' || mime === 'jpg' ? 'image/jpeg' : 'image/png';
                const q = format === 'image/jpeg' ? quality : 1;
                callback(canvas.toDataURL(format, q));
            } catch (e) {
                console.warn('Image reduce failed, using original:', e);
                callback(imageSrc);
            }
        };
        img.onerror = function() { callback(imageSrc); };
        img.src = imageSrc;
    }

    function handleImageUpload(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const dataUrl = e.target.result;
                reduceImageSize(dataUrl, function(processedUrl) {
                    fabric.Image.fromURL(processedUrl, function(img) {
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
                });
            };
            reader.readAsDataURL(file);
        }
    }

    // Image Library Functions
    function loadGlobalImageLibrary() {
        const grid = document.getElementById('globalImageLibraryGrid');
        const tabsContainer = document.getElementById('globalImageCategoryTabs');
        if (!grid) return;

        grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i><p style="margin: 0;">Loading image parts...</p></div>';
        if (tabsContainer) tabsContainer.innerHTML = '';

        fetch('{{ route("design.globalImageLibrary.index") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.categories && data.categories.length > 0) {
                renderGlobalImageLibraryByCategory(data.categories, grid, tabsContainer);
            } else if (data.success && data.images && data.images.length > 0) {
                renderGlobalImageLibrary(data.images, grid);
            } else {
                grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;"><i class="fas fa-globe" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i><p style="margin: 0;">No global image parts yet.</p><p style="margin: 0.5rem 0 0; font-size: 0.75rem;">Manage in Admin → Global Images</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading global image library:', error);
            grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #ef4444; font-size: 0.875rem;"><i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i><p style="margin: 0;">Error loading image parts.</p></div>';
        });
    }

    function renderGlobalImageLibraryByCategory(categories, grid, tabsContainer) {
        if (!grid) return;

        tabsContainer.innerHTML = '';
        categories.forEach((cat, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm ' + (idx === 0 ? 'btn-primary' : 'btn-outline-secondary');
            btn.style.cssText = 'padding: 0.25rem 0.5rem; font-size: 0.75rem;';
            btn.textContent = cat.name + (cat.images && cat.images.length ? ' (' + cat.images.length + ')' : '');
            btn.onclick = function() {
                tabsContainer.querySelectorAll('button').forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-secondary');
                });
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-primary');
                renderGlobalImageLibrary(cat.images || [], grid);
            };
            tabsContainer.appendChild(btn);
        });

        const firstCat = categories[0];
        renderGlobalImageLibrary(firstCat.images || [], grid);
    }

    function renderGlobalImageLibrary(images, gridEl) {
        const grid = gridEl || document.getElementById('globalImageLibraryGrid');
        if (!grid) return;

        grid.innerHTML = '';

        if (!images || images.length === 0) {
            grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;"><i class="fas fa-images" style="font-size: 1.5rem; opacity: 0.5;"></i><p style="margin: 0.5rem 0 0;">No images in this category</p></div>';
            return;
        }

        images.forEach((image, index) => {
            const item = document.createElement('div');
            item.className = 'image-library-item';
            item.onclick = () => addImageFromLibrary(image.url);

            item.innerHTML = `
                <img src="${image.url}" alt="${(image.name || 'Image part ' + (index + 1)).replace(/"/g, '&quot;')}" loading="lazy">
                <div class="image-overlay">
                    <i class="fas fa-plus"></i> Click to Add
                </div>
            `;

            grid.appendChild(item);
        });
    }

    function loadImageLibrary() {
        const grid = document.getElementById('imageLibraryGrid');
        if (!grid) return;

        // Show loading state
        grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i><p style="margin: 0;">Loading images...</p></div>';

        // Fetch images from server
        fetch('{{ route("design.imageLibrary.index") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.images && data.images.length > 0) {
                renderImageLibrary(data.images);
            } else {
                grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;"><i class="fas fa-images" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i><p style="margin: 0;">No images in library yet.<br>Upload images to get started.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading image library:', error);
            grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #ef4444; font-size: 0.875rem;"><i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i><p style="margin: 0;">Error loading images.<br>Please try again.</p></div>';
        });
    }

    function renderImageLibrary(images) {
        const grid = document.getElementById('imageLibraryGrid');
        if (!grid) return;

        grid.innerHTML = '';

        images.forEach((image, index) => {
            const item = document.createElement('div');
            item.className = 'image-library-item';
            item.onclick = (e) => {
                // Don't trigger if clicking delete button
                if (e.target.closest('.delete-btn')) return;
                addImageFromLibrary(image.url);
            };

            item.innerHTML = `
                <img src="${image.url}" alt="Library Image ${index + 1}" loading="lazy">
                <div class="image-overlay">
                    <i class="fas fa-plus"></i> Click to Add
                </div>
                <button class="delete-btn" onclick="event.stopPropagation(); deleteLibraryImage('${image.path || image.id}')" title="Delete Image">
                    <i class="fas fa-times"></i>
                </button>
            `;

            grid.appendChild(item);
        });
    }

    function addImageFromLibrary(imageUrl) {
        if (!canvas) return;

        const addToCanvas = function(src) {
            fabric.Image.fromURL(src, function(img) {
                const canvasWidth = canvas.getWidth();
                const canvasHeight = canvas.getHeight();
                img.set({
                    left: (canvasWidth / 2) - (img.width * img.scaleX / 2),
                    top: (canvasHeight / 2) - (img.height * img.scaleY / 2),
                    scaleX: Math.min(1, (canvasWidth * 0.6) / img.width),
                    scaleY: Math.min(1, (canvasHeight * 0.6) / img.height)
                });
                canvas.add(img);
                canvas.setActiveObject(img);
                canvas.renderAll();
                refreshLayers();
            }, { crossOrigin: 'anonymous' });
        };

        if (editorSettings.imageReduceOnAdd) {
            reduceImageSize(imageUrl, function(processedUrl) {
                addToCanvas(processedUrl);
            });
        } else {
            addToCanvas(imageUrl);
        }
    }

    function handleLibraryImageUpload(event) {
        const files = event.target.files;
        if (!files || files.length === 0) return;

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('images[]', files[i]);
        }

        // Show loading
        const grid = document.getElementById('imageLibraryGrid');
        const loadingMsg = document.createElement('div');
        loadingMsg.style.cssText = 'grid-column: 1 / -1; text-align: center; padding: 1rem; color: #6366f1; font-size: 0.875rem;';
        loadingMsg.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading images...';
        grid.insertBefore(loadingMsg, grid.firstChild);

        fetch('{{ route("design.imageLibrary.upload") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the library
                loadImageLibrary();
            } else {
                alert('Error uploading images: ' + (data.message || 'Unknown error'));
                loadingMsg.remove();
            }
        })
        .catch(error => {
            console.error('Error uploading images:', error);
            alert('Error uploading images. Please try again.');
            loadingMsg.remove();
        });

        // Reset file input
        event.target.value = '';
    }

    // Font Library Functions
    let customFonts = []; // Store loaded custom fonts

    // Font modal functions (defined above as window functions for global access)

    function loadFontLibrary() {
        const list = document.getElementById('fontLibraryList');
        if (!list) return;

        // Show loading state
        list.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i><p style="margin: 0;">Loading fonts...</p></div>';

        // Fetch fonts from server
        fetch('{{ route("design.fontLibrary.index") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.fonts && data.fonts.length > 0) {
                renderFontLibrary(data.fonts);
                customFonts = data.fonts;
                updateFontFamilySelector();
            } else {
                list.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.875rem;"><i class="fas fa-font" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i><p style="margin: 0;">No fonts in library yet.<br>Upload font files to get started.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading font library:', error);
            list.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #ef4444; font-size: 0.875rem;"><i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i><p style="margin: 0;">Error loading fonts.<br>Please try again.</p></div>';
        });
    }

    function renderFontLibrary(fonts) {
        const list = document.getElementById('fontLibraryList');
        if (!list) return;

        list.innerHTML = '';

        fonts.forEach((font, index) => {
            const item = document.createElement('div');
            item.style.cssText = 'border: 1px solid #e2e8f0; border-radius: 6px; padding: 1rem; background: white; position: relative;';

            // Load font and create preview
            loadCustomFont(font);

            const isGlobal = font.deletable === false;
            const deleteOrBadge = isGlobal
                ? '<span style="font-size: 0.65rem; color: #475569; font-weight: 600;">Site font</span>'
                : `<button type="button" onclick="event.stopPropagation(); deleteLibraryFont(${JSON.stringify(String(font.path || font.id))})" title="Delete Font" style="background: #ef4444; color: white; border: none; border-radius: 4px; padding: 0.25rem 0.5rem; cursor: pointer; font-size: 0.7rem;">
                        <i class="fas fa-times"></i>
                    </button>`;

            item.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; font-family: '${font.name.replace(/'/g, "\\'")}', sans-serif;">${escapeHtml(font.name)}</div>
                        <div style="font-size: 0.7rem; color: #64748b;">${String(font.extension || '').toUpperCase()}</div>
                    </div>
                    ${deleteOrBadge}
                </div>
                <div style="font-family: '${font.name.replace(/'/g, "\\'")}', sans-serif; font-size: 1.2rem; padding: 0.5rem; background: #f8fafc; border-radius: 4px; text-align: center; margin-top: 0.5rem;">
                    Aa Bb Cc
                </div>
            `;

            list.appendChild(item);
        });
    }

    function getFontFaceElementId(font) {
        const key = font.id != null ? String(font.id) : (font.path || font.name || 'font');
        return 'ff-' + key.replace(/[^a-zA-Z0-9_-]/g, '_');
    }

    function loadCustomFont(font) {
        if (!font || !font.url) return;
        const elId = getFontFaceElementId(font);
        if (document.getElementById(elId)) {
            return;
        }

        let fontFormat = 'truetype';
        const ext = String(font.extension || '').toLowerCase();
        if (ext === 'woff') fontFormat = 'woff';
        else if (ext === 'woff2') fontFormat = 'woff2';
        else if (ext === 'otf') fontFormat = 'opentype';
        else if (ext === 'eot') fontFormat = 'embedded-opentype';

        const family = String(font.name || 'CustomFont').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        const style = document.createElement('style');
        style.id = elId;
        style.textContent =
            "@font-face { font-family: '" + family + "'; src: url('" + String(font.url).replace(/'/g, "%27") + "') format('" + fontFormat + "'); font-weight: normal; font-style: normal; font-display: swap; }";
        document.head.appendChild(style);
    }

    function updateFontFamilySelector() {
        const selector = document.getElementById('propFontFamily');
        if (!selector) return;

        // Store current value
        const currentValue = selector.value;

        // Clear existing options (keep default fonts)
        const defaultFonts = ['Arial', 'Helvetica', 'Times New Roman', 'Courier New', 'Verdana', 'Georgia'];
        selector.innerHTML = '';

        // Add default fonts
        defaultFonts.forEach(font => {
            const option = document.createElement('option');
            option.value = font;
            option.textContent = font;
            if (font === currentValue) option.selected = true;
            selector.appendChild(option);
        });

        const addFontOptgroup = (label, list) => {
            if (!list || list.length === 0) return;
            const og = document.createElement('optgroup');
            og.label = label;
            list.forEach(font => {
                const option = document.createElement('option');
                option.value = font.name;
                option.textContent = font.name;
                option.style.fontFamily = font.name;
                if (font.name === currentValue) option.selected = true;
                og.appendChild(option);
            });
            selector.appendChild(og);
        };

        const siteFonts = customFonts.filter(f => f.deletable === false);
        const myFonts = customFonts.filter(f => f.deletable !== false);
        addFontOptgroup('Site fonts', siteFonts);
        addFontOptgroup('My fonts', myFonts);
    }

    function handleFontUpload(event) {
        const files = event.target.files;
        if (!files || files.length === 0) return;

        // Client-side validation
        const allowedExtensions = ['ttf', 'otf', 'woff', 'woff2', 'eot'];
        const maxSize = 10 * 1024 * 1024; // 10MB
        const validFiles = [];
        const errors = [];

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const extension = file.name.split('.').pop().toLowerCase();

            // Check extension
            if (!allowedExtensions.includes(extension)) {
                errors.push(`"${file.name}" has invalid extension. Allowed: ${allowedExtensions.join(', ').toUpperCase()}`);
                continue;
            }

            // Check file size
            if (file.size > maxSize) {
                errors.push(`"${file.name}" is too large (${(file.size / 1024 / 1024).toFixed(2)}MB). Maximum size is 10MB.`);
                continue;
            }

            validFiles.push(file);
        }

        // Show errors if any
        if (errors.length > 0) {
            let errorMessage = 'Some files are invalid:\n\n' + errors.join('\n');
            if (validFiles.length === 0) {
                alert(errorMessage + '\n\nPlease select valid font files and try again.');
                event.target.value = '';
                return;
            } else {
                if (!confirm(errorMessage + '\n\nOnly valid files will be uploaded. Continue?')) {
                    event.target.value = '';
                    return;
                }
            }
        }

        if (validFiles.length === 0) {
            alert('No valid font files selected. Please select files with extensions: ' + allowedExtensions.join(', ').toUpperCase());
            event.target.value = '';
            return;
        }

        const formData = new FormData();
        for (let i = 0; i < validFiles.length; i++) {
            formData.append('fonts[]', validFiles[i]);
        }

        // Show loading
        const list = document.getElementById('fontLibraryList');
        if (!list) {
            console.error('Font library list not found');
            return;
        }
        const loadingMsg = document.createElement('div');
        loadingMsg.style.cssText = 'grid-column: 1 / -1; text-align: center; padding: 1rem; color: #6366f1; font-size: 0.875rem;';
        loadingMsg.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading fonts...';
        list.insertBefore(loadingMsg, list.firstChild);

        fetch('{{ route("design.fontLibrary.upload") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => {
            return response.json().then(data => {
                // Check if response status indicates an error
                if (!response.ok) {
                    throw { ...data, status: response.status };
                }
                return data;
            });
        })
        .then(data => {
            loadingMsg.remove();

            if (data.success) {
                // Show success message
                let message = data.message || 'Font(s) uploaded successfully';

                // If partial success, show warnings
                if (data.partial && data.errors && data.errors.length > 0) {
                    message += '\n\nWarnings:\n' + data.errors.join('\n');
                }

                if (data.partial) {
                    alert(message);
                }

                // Reload the library
                loadFontLibrary();
                // Reset file input
                event.target.value = '';
            } else {
                // Show detailed error messages
                let errorMessage = data.message || 'Unknown error';

                if (data.errors) {
                    if (typeof data.errors === 'object' && !Array.isArray(data.errors)) {
                        // Laravel validation errors format
                        const errorArray = [];
                        for (const key in data.errors) {
                            if (Array.isArray(data.errors[key])) {
                                errorArray.push(...data.errors[key]);
                            } else {
                                errorArray.push(data.errors[key]);
                            }
                        }
                        errorMessage += '\n\n' + errorArray.join('\n');
                    } else if (Array.isArray(data.errors)) {
                        errorMessage += '\n\n' + data.errors.join('\n');
                    }
                }

                alert('Error uploading fonts:\n\n' + errorMessage);
            }
        })
        .catch(error => {
            loadingMsg.remove();
            console.error('Error uploading fonts:', error);

            let errorMessage = 'Error uploading fonts. Please try again.';
            if (error.message) {
                errorMessage = error.message;
            } else if (error.errors) {
                if (Array.isArray(error.errors)) {
                    errorMessage = error.errors.join('\n');
                } else if (typeof error.errors === 'object') {
                    const errorArray = [];
                    for (const key in error.errors) {
                        if (Array.isArray(error.errors[key])) {
                            errorArray.push(...error.errors[key]);
                        }
                    }
                    errorMessage = errorArray.join('\n');
                }
            } else if (error.status === 422) {
                errorMessage = 'Validation failed. Please check your font files.';
            }

            alert('Error uploading fonts:\n\n' + errorMessage);
        });
    }

    function deleteLibraryFont(fontPath) {
        if (String(fontPath).startsWith('design-font:')) {
            alert('This font is managed in the admin panel (Design fonts) and cannot be removed here.');
            return;
        }
        if (!confirm('Are you sure you want to delete this font?')) {
            return;
        }

        // Find font before deletion to remove style
        const fontToDelete = customFonts.find(f => f.path === fontPath || f.id === fontPath);

        fetch('{{ route("design.fontLibrary.delete") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: fontPath })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove font-face style if font was found
                if (fontToDelete) {
                    const styleId = getFontFaceElementId(fontToDelete);
                    const style = document.getElementById(styleId);
                    if (style) style.remove();
                }
                // Remove font from customFonts array
                customFonts = customFonts.filter(font => font.path !== fontPath && font.id !== fontPath);
                // Reload the library
                loadFontLibrary();
                // Update font selector
                updateFontFamilySelector();
            } else {
                alert('Error deleting font: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting font:', error);
            alert('Error deleting font. Please try again.');
        });
    }

    // Load fonts on page load
    function loadFontsOnInit() {
        fetch('{{ route("design.fontLibrary.index") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.fonts && data.fonts.length > 0) {
                customFonts = data.fonts;
                data.fonts.forEach(font => loadCustomFont(font));
                updateFontFamilySelector();
            }
        })
        .catch(error => {
            console.error('Error loading fonts on init:', error);
        });
    }

    function deleteLibraryImage(imageId) {
        if (!confirm('Are you sure you want to delete this image from the library?')) {
            return;
        }

        fetch('{{ route("design.imageLibrary.delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id: imageId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the library
                loadImageLibrary();
            } else {
                alert('Error deleting image: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting image:', error);
            alert('Error deleting image. Please try again.');
        });
    }

    function updatePropertiesPanel(obj) {
        if (!obj) return;

        if (window.innerWidth <= 768 && typeof openMobilePropertiesPanel === 'function') {
            openMobilePropertiesPanel();
        }
        currentObject = obj;
        document.getElementById('elementProperties').style.display = 'block';
        document.getElementById('noSelectionText').style.display = 'none';

        // Update Styles tab
        updateStylesPanel(obj);

        document.getElementById('propX').value = Math.round(obj.left);
        document.getElementById('propY').value = Math.round(obj.top);

        // For textboxes, use actual width/height (scale should be 1)
        if (obj.type === 'textbox') {
            document.getElementById('propWidth').value = Math.round(obj.width || (obj.width * obj.scaleX));
            document.getElementById('propHeight').value = Math.round(obj.height || (obj.height * obj.scaleY));
        } else {
            document.getElementById('propWidth').value = Math.round(obj.width * obj.scaleX);
            document.getElementById('propHeight').value = Math.round(obj.height * obj.scaleY);
        }

        if (obj.fill) {
            document.getElementById('colorPicker').value = rgbToHex(obj.fill);
        }

        // Show/hide Image Color tab and Image actions (crop, polygon) when image selected
        const imageColorTabBtn = document.getElementById('verticalTabImageColor');
        if (imageColorTabBtn) {
            imageColorTabBtn.style.display = obj.type === 'image' ? 'flex' : 'none';
            if (obj.type === 'image' && typeof window.initPhotoColorsPanel === 'function') {
                window.initPhotoColorsPanel(obj);
            }
        }
        const imageActionsSection = document.getElementById('imageActionsSection');
        if (imageActionsSection) {
            imageActionsSection.style.display = obj.type === 'image' ? 'block' : 'none';
        }

        if (obj.type === 'text' || obj.type === 'textbox' || obj.type === 'i-text') {
            document.getElementById('textProps').style.display = 'block';
            document.getElementById('propText').value = obj.text;
            document.getElementById('propFontSize').value = obj.fontSize;
            document.getElementById('propFontFamily').value = obj.fontFamily || 'Arial';
            document.getElementById('propFontWeight').value = obj.fontWeight || 'normal';
            document.getElementById('propLineHeight').value = obj.lineHeight || 1.2;
            document.getElementById('propCharSpacing').value = obj.charSpacing || 0;

            // Update text alignment buttons
            const alignButtons = ['alignLeft', 'alignCenter', 'alignRight', 'alignJustify'];
            alignButtons.forEach(btnId => {
                document.getElementById(btnId).classList.remove('active');
            });
            const alignValue = obj.textAlign || 'left';
            if (alignValue === 'left') document.getElementById('alignLeft').classList.add('active');
            else if (alignValue === 'center') document.getElementById('alignCenter').classList.add('active');
            else if (alignValue === 'right') document.getElementById('alignRight').classList.add('active');
            else if (alignValue === 'justify') document.getElementById('alignJustify').classList.add('active');

            // Show/hide convert to textbox button
            const convertButtonContainer = document.getElementById('convertToTextboxContainer');
            if (convertButtonContainer) {
                // Show button only if it's text or i-text (not already a textbox)
                if (obj.type === 'text' || obj.type === 'i-text') {
                    convertButtonContainer.style.display = 'block';
                } else {
                    convertButtonContainer.style.display = 'none';
                }
            }
        } else {
            document.getElementById('textProps').style.display = 'none';
            // Hide convert button when not a text element
            const convertButtonContainer = document.getElementById('convertToTextboxContainer');
            if (convertButtonContainer) {
                convertButtonContainer.style.display = 'none';
            }
        }
    }

    function hidePropertiesPanel() {
        document.getElementById('elementProperties').style.display = 'none';
        document.getElementById('noSelectionText').style.display = 'block';
        const imageColorTabBtn = document.getElementById('verticalTabImageColor');
        if (imageColorTabBtn) imageColorTabBtn.style.display = 'none';
        const imageActionsSection = document.getElementById('imageActionsSection');
        if (imageActionsSection) imageActionsSection.style.display = 'none';
        const imageColorTab = document.getElementById('imageColorTab');
        if (imageColorTab && imageColorTab.classList.contains('active')) {
            switchPropertiesVerticalTab('properties');
        }
        const photoColorsPanel = document.getElementById('photoColorsPanel');
        if (photoColorsPanel) photoColorsPanel.style.display = 'none';
        const convertButtonContainer = document.getElementById('convertToTextboxContainer');
        if (convertButtonContainer) {
            convertButtonContainer.style.display = 'none';
        }

        // Hide styles panel
        const elementStyles = document.getElementById('elementStyles');
        const noSelectionTextStyles = document.getElementById('noSelectionTextStyles');
        if (elementStyles) elementStyles.style.display = 'none';
        if (noSelectionTextStyles) noSelectionTextStyles.style.display = 'block';

        currentObject = null;
    }

    // Update Styles Panel
    function updateStylesPanel(obj) {
        if (!obj) return;

        const elementStyles = document.getElementById('elementStyles');
        const noSelectionTextStyles = document.getElementById('noSelectionTextStyles');

        if (elementStyles && noSelectionTextStyles) {
            elementStyles.style.display = 'block';
            noSelectionTextStyles.style.display = 'none';
        }

        // Update Opacity
        const opacity = obj.opacity !== undefined ? Math.round(obj.opacity * 100) : 100;
        const propOpacity = document.getElementById('propOpacity');
        const propOpacityValue = document.getElementById('propOpacityValue');
        if (propOpacity) propOpacity.value = opacity;
        if (propOpacityValue) propOpacityValue.value = opacity;

        // Update Blend Mode (if supported)
        const propBlendMode = document.getElementById('propBlendMode');
        if (propBlendMode && obj.globalCompositeOperation) {
            propBlendMode.value = obj.globalCompositeOperation;
        }

        // Update Border Radius (for rect, circle, image)
        const borderRadiusSection = document.getElementById('borderRadiusSection');
        if (borderRadiusSection) {
            if (obj.type === 'rect' || obj.type === 'image' || obj.rx !== undefined || obj.ry !== undefined) {
                borderRadiusSection.style.display = 'block';
                const rx = obj.rx || 0;
                const propBorderRadius = document.getElementById('propBorderRadius');
                const propBorderRadiusValue = document.getElementById('propBorderRadiusValue');
                if (propBorderRadius) propBorderRadius.value = rx;
                if (propBorderRadiusValue) propBorderRadiusValue.value = rx;
            } else {
                borderRadiusSection.style.display = 'none';
            }
        }

        // Update Stroke
        const propStrokeEnabled = document.getElementById('propStrokeEnabled');
        const strokeControls = document.getElementById('stylesStrokeControls');
        if (propStrokeEnabled && strokeControls) {
            const hasStroke = obj.stroke && obj.strokeWidth > 0;
            propStrokeEnabled.checked = hasStroke;
            strokeControls.style.display = hasStroke ? 'block' : 'none';

            if (hasStroke) {
                const propStrokeWidth = document.getElementById('propStrokeWidth');
                const propStrokeWidthValue = document.getElementById('propStrokeWidthValue');
                const propStrokeColor = document.getElementById('propStrokeColor');
                const propStrokePosition = document.getElementById('propStrokePosition');
                if (propStrokeWidth) propStrokeWidth.value = obj.strokeWidth || 1;
                if (propStrokeWidthValue) propStrokeWidthValue.value = obj.strokeWidth || 1;
                if (propStrokeColor && obj.stroke) {
                    propStrokeColor.value = rgbToHex(obj.stroke);
                }
                if (propStrokePosition) {
                    propStrokePosition.value = obj.strokePosition || 'center';
                }
            }
        }

        // Update Shadow
        const propShadowEnabled = document.getElementById('propShadowEnabled');
        const shadowControls = document.getElementById('stylesShadowControls');
        if (propShadowEnabled && shadowControls) {
            const hasShadow = obj.shadow && obj.shadow.offsetX !== undefined;
            propShadowEnabled.checked = hasShadow;
            shadowControls.style.display = hasShadow ? 'block' : 'none';

            if (hasShadow) {
                const propShadowOffsetX = document.getElementById('propShadowOffsetX');
                const propShadowOffsetY = document.getElementById('propShadowOffsetY');
                const propShadowBlur = document.getElementById('propShadowBlur');
                const propShadowColor = document.getElementById('propShadowColor');
                if (propShadowOffsetX) propShadowOffsetX.value = obj.shadow.offsetX || 5;
                if (propShadowOffsetY) propShadowOffsetY.value = obj.shadow.offsetY || 5;
                if (propShadowBlur) propShadowBlur.value = obj.shadow.blur || 10;
                if (propShadowColor && obj.shadow.color) {
                    propShadowColor.value = rgbToHex(obj.shadow.color);
                }
            }
        }

        // Update Edge Feather
        const propEdgeFeatherEnabled = document.getElementById('propEdgeFeatherEnabled');
        const edgeFeatherControls = document.getElementById('edgeFeatherControls');
        if (propEdgeFeatherEnabled && edgeFeatherControls) {
            const hasEdgeFeather = obj.edgeFeather !== undefined && obj.edgeFeather > 0;
            propEdgeFeatherEnabled.checked = hasEdgeFeather;
            edgeFeatherControls.style.display = hasEdgeFeather ? 'block' : 'none';

            if (hasEdgeFeather) {
                const propEdgeFeatherAmount = document.getElementById('propEdgeFeatherAmount');
                const propEdgeFeatherAmountValue = document.getElementById('propEdgeFeatherAmountValue');
                if (propEdgeFeatherAmount) propEdgeFeatherAmount.value = obj.edgeFeather || 10;
                if (propEdgeFeatherAmountValue) propEdgeFeatherAmountValue.value = obj.edgeFeather || 10;
            }
        }
    }

    function updateProperty(prop, value) {
        if (!currentObject) return;

        if (prop === 'width' || prop === 'height') {
            // For textboxes, handle width and height differently
            if (currentObject.type === 'textbox') {
                if (prop === 'width') {
                    // Set width directly and reset scaleX
                    currentObject.set({
                        width: parseFloat(value),
                        scaleX: 1
                    });
                    // Recalculate dimensions
                    currentObject._calcDimensions();
                } else {
                    // Set height directly and reset scaleY, and store as fixed height
                    const newHeight = parseFloat(value);
                    currentObject._fixedHeight = newHeight;
                    currentObject._hasFixedHeight = true;
                    currentObject.set({
                        height: newHeight,
                        scaleY: 1
                    });
                    // Force recalculation to apply fixed height
                    currentObject._calcDimensions();
                }
            } else {
                // For other objects, use scaling
                const scale = prop === 'width' ? value / currentObject.width : value / currentObject.height;
                if (prop === 'width') {
                    currentObject.set('scaleX', scale);
                } else {
                    currentObject.set('scaleY', scale);
                }
            }
        } else {
            currentObject.set(prop, parseFloat(value) || value);
        }

        // Update alignment button states
        if (prop === 'textAlign') {
            const alignButtons = ['alignLeft', 'alignCenter', 'alignRight', 'alignJustify'];
            alignButtons.forEach(btnId => {
                document.getElementById(btnId).classList.remove('active');
            });
            if (value === 'left') document.getElementById('alignLeft').classList.add('active');
            else if (value === 'center') document.getElementById('alignCenter').classList.add('active');
            else if (value === 'right') document.getElementById('alignRight').classList.add('active');
            else if (value === 'justify') document.getElementById('alignJustify').classList.add('active');
        }

        canvas.renderAll();
    }

    function adjustFontSize(delta) {
        if (!currentObject || (currentObject.type !== 'text' && currentObject.type !== 'textbox' && currentObject.type !== 'i-text')) return;
        const newSize = Math.max(8, Math.min(200, currentObject.fontSize + delta));
        currentObject.set('fontSize', newSize);
        document.getElementById('propFontSize').value = newSize;
        canvas.renderAll();
    }

    function adjustLineHeight(delta) {
        if (!currentObject || (currentObject.type !== 'text' && currentObject.type !== 'textbox' && currentObject.type !== 'i-text')) return;
        const currentLineHeight = currentObject.lineHeight || 1.2;
        const newLineHeight = Math.max(0.5, Math.min(5, currentLineHeight + delta));
        currentObject.set('lineHeight', newLineHeight);
        document.getElementById('propLineHeight').value = newLineHeight;
        canvas.renderAll();
    }

    function adjustCharSpacing(delta) {
        if (!currentObject || (currentObject.type !== 'text' && currentObject.type !== 'textbox' && currentObject.type !== 'i-text')) return;
        const currentSpacing = currentObject.charSpacing || 0;
        const newSpacing = Math.max(-10, Math.min(50, currentSpacing + delta));
        currentObject.set('charSpacing', newSpacing);
        document.getElementById('propCharSpacing').value = newSpacing;
        canvas.renderAll();
    }

    function updateTextContent(value) {
        if (!currentObject || currentObject.type !== 'text' && currentObject.type !== 'textbox' && currentObject.type !== 'i-text') return;
        currentObject.set('text', value);
        canvas.renderAll();
    }

    // Rich Text Formatting Functions
    function showRichTextToolbar(textObj) {
        const toolbar = document.getElementById('richTextToolbar');
        if (toolbar && (textObj.type === 'i-text' || textObj.type === 'textbox')) {
            toolbar.style.display = 'block';
            updateRichTextToolbarState(textObj);
        }
    }

    function hideRichTextToolbar() {
        const toolbar = document.getElementById('richTextToolbar');
        if (toolbar) {
            toolbar.style.display = 'none';
        }
    }

    function updateRichTextToolbarState(textObj) {
        if (!textObj || !textObj.isEditing) return;

        const selectionStart = textObj.selectionStart || 0;
        const selectionEnd = textObj.selectionEnd || selectionStart;

        // Get styles for selected text range
        let isBold = false;
        let isItalic = false;
        let isUnderline = false;
        let textColor = '#000000';

        if (textObj._textStyles && selectionStart < selectionEnd) {
            // Check styles for each character in selection
            for (let i = selectionStart; i < selectionEnd && i < textObj.text.length; i++) {
                const charStyle = textObj._textStyles[i];
                if (charStyle) {
                    if (charStyle.fontWeight === 'bold' || charStyle.fontWeight === '700') isBold = true;
                    if (charStyle.fontStyle === 'italic') isItalic = true;
                    if (charStyle.underline === true) isUnderline = true;
                    if (charStyle.fill) textColor = charStyle.fill;
                }
            }
        } else {
            // Fallback: check object-level styles
            if (textObj.fontWeight === 'bold' || textObj.fontWeight === '700') isBold = true;
            if (textObj.fontStyle === 'italic') isItalic = true;
            if (textObj.underline === true) isUnderline = true;
            if (textObj.fill) textColor = textObj.fill;
        }

        // Update button states
        const boldBtn = document.getElementById('formatBold');
        const italicBtn = document.getElementById('formatItalic');
        const underlineBtn = document.getElementById('formatUnderline');

        if (boldBtn) boldBtn.classList.toggle('active', isBold);
        if (italicBtn) italicBtn.classList.toggle('active', isItalic);
        if (underlineBtn) underlineBtn.classList.toggle('active', isUnderline);

        // Update color picker - use existing rgbToHex function if available
        const colorPicker = document.getElementById('textColorPicker');
        if (colorPicker) {
            try {
                if (typeof rgbToHex === 'function') {
                    colorPicker.value = rgbToHex(textColor);
                } else {
                    // Fallback: simple hex conversion
                    colorPicker.value = textColor.startsWith('#') ? textColor : '#000000';
                }
            } catch (e) {
                colorPicker.value = '#000000';
            }
        }
    }

    function applyTextFormat(format) {
        const activeObject = canvas.getActiveObject();
        if (!activeObject || (activeObject.type !== 'i-text' && activeObject.type !== 'textbox') || !activeObject.isEditing) {
            return;
        }

        const selectionStart = activeObject.selectionStart || 0;
        let selectionEnd = activeObject.selectionEnd || selectionStart;

        // If no selection, select the current word or entire text
        if (selectionStart === selectionEnd) {
            // Try to select current word
            const text = activeObject.text;
            let wordStart = selectionStart;
            let wordEnd = selectionStart;

            // Find word boundaries
            while (wordStart > 0 && text[wordStart - 1] !== ' ' && text[wordStart - 1] !== '\n') {
                wordStart--;
            }
            while (wordEnd < text.length && text[wordEnd] !== ' ' && text[wordEnd] !== '\n') {
                wordEnd++;
            }

            if (wordStart < wordEnd) {
                selectionEnd = wordEnd;
                activeObject.setSelectionStart(wordStart);
                activeObject.setSelectionEnd(wordEnd);
            } else {
                // If no word found, select entire text
                selectionEnd = text.length;
                activeObject.setSelectionStart(0);
                activeObject.setSelectionEnd(text.length);
            }
        }

        // Determine if currently formatted by checking first character of selection
        let isCurrentlyFormatted = false;
        if (activeObject._textStyles && activeObject._textStyles[selectionStart]) {
            const charStyle = activeObject._textStyles[selectionStart];
            if (format === 'bold') {
                isCurrentlyFormatted = charStyle.fontWeight === 'bold' || charStyle.fontWeight === '700';
            } else if (format === 'italic') {
                isCurrentlyFormatted = charStyle.fontStyle === 'italic';
            } else if (format === 'underline') {
                isCurrentlyFormatted = charStyle.underline === true;
            }
        }

        // Toggle format
        const newStyle = {};
        if (format === 'bold') {
            newStyle.fontWeight = isCurrentlyFormatted ? 'normal' : 'bold';
        } else if (format === 'italic') {
            newStyle.fontStyle = isCurrentlyFormatted ? 'normal' : 'italic';
        } else if (format === 'underline') {
            newStyle.underline = !isCurrentlyFormatted;
        }

        // Apply style to selection using setSelectionStyles
        activeObject.setSelectionStyles(newStyle, selectionStart, selectionEnd);
        canvas.renderAll();

        // Update toolbar state after a brief delay
        setTimeout(() => {
            updateRichTextToolbarState(activeObject);
        }, 50);
    }

    function applyTextColor(color) {
        const activeObject = canvas.getActiveObject();
        if (!activeObject || (activeObject.type !== 'i-text' && activeObject.type !== 'textbox') || !activeObject.isEditing) {
            return;
        }

        const selectionStart = activeObject.selectionStart || 0;
        const selectionEnd = activeObject.selectionEnd || activeObject.text.length;

        if (selectionStart === selectionEnd) {
            // No selection, apply to entire text
            activeObject.set('fill', color);
        } else {
            // Apply color to selected text only
            activeObject.setSelectionStyles({ fill: color }, selectionStart, selectionEnd);
        }

        canvas.renderAll();
        updateRichTextToolbarState(activeObject);
    }

    function clearTextFormat() {
        const activeObject = canvas.getActiveObject();
        if (!activeObject || (activeObject.type !== 'i-text' && activeObject.type !== 'textbox') || !activeObject.isEditing) {
            return;
        }

        const selectionStart = activeObject.selectionStart || 0;
        const selectionEnd = activeObject.selectionEnd || activeObject.text.length;

        if (selectionStart === selectionEnd) {
            return;
        }

        // Clear all formatting from selection
        activeObject.setSelectionStyles({
            fontWeight: 'normal',
            fontStyle: 'normal',
            underline: false
        }, selectionStart, selectionEnd);

        canvas.renderAll();
        updateRichTextToolbarState(activeObject);
    }

    function setColor(color) {
        if (!currentObject) return;
        currentObject.set('fill', color);
        canvas.renderAll();
    }

    function groupSelected() {
        if (!canvas) return;

        const activeObjects = canvas.getActiveObjects();
        if (activeObjects.length < 2) {
            alert('Please select at least 2 objects to group');
            return;
        }

        // Check if any selected object is locked
        const hasLocked = activeObjects.some(obj => obj.locked);
        if (hasLocked) {
            alert('Cannot group locked objects. Please unlock them first.');
            return;
        }

        // Calculate bounding box to determine group position
        // Get positions BEFORE removing from canvas
        let minX = Infinity, minY = Infinity;

        activeObjects.forEach(obj => {
            const rect = obj.getBoundingRect();
            minX = Math.min(minX, rect.left);
            minY = Math.min(minY, rect.top);
        });

        // Group position will be at the top-left of bounding box
        const groupLeft = minX;
        const groupTop = minY;

        // Store original positions BEFORE removing from canvas
        const originalPositions = activeObjects.map(obj => ({
            obj: obj,
            left: obj.left,
            top: obj.top
        }));

        // Calculate relative positions BEFORE removing from canvas
        const objectsWithRelativePos = activeObjects.map(obj => ({
            obj: obj,
            relativeLeft: obj.left - groupLeft,
            relativeTop: obj.top - groupTop
        }));

        // Remove objects from canvas
        activeObjects.forEach(obj => {
            canvas.remove(obj);
        });

        // Adjust objects to be relative to group origin (0,0)
        objectsWithRelativePos.forEach(({obj, relativeLeft, relativeTop}) => {
            obj.set({
                left: relativeLeft,
                top: relativeTop
            });
        });

        // Create group with objects that have relative positions
        const group = new fabric.Group(activeObjects, {
            left: groupLeft,
            top: groupTop,
            type: 'group' // Explicitly set type
        });

        // Ensure group type is set
        if (group) {
            group.set('type', 'group');
        }

        // Verify group was created
        if (!group) {
            console.error('Failed to create group');
            // Restore original objects if group creation failed
            originalPositions.forEach(({obj, left, top}) => {
                obj.set({ left, top });
                canvas.add(obj);
            });
            canvas.renderAll();
            return;
        }

        // Add group to canvas
        canvas.add(group);
        canvas.setActiveObject(group);
        canvas.renderAll();

        // Debug: Verify group was added
        console.log('Group created:', group.type, 'Objects in group:', group.getObjects ? group.getObjects().length : 0);
        console.log('Canvas objects:', canvas.getObjects().length);

        // Refresh layers after a brief delay to ensure canvas is updated
        setTimeout(() => {
            refreshLayers();
        }, 50);
    }

    function ungroupSelected() {
        if (!canvas) return;

        const activeObject = canvas.getActiveObject();
        if (!activeObject) {
            alert('Please select a group to ungroup');
            return;
        }

        if (activeObject.type !== 'group') {
            alert('Selected object is not a group');
            return;
        }

        // Check if group is locked
        if (activeObject.locked) {
            alert('Cannot ungroup a locked group. Please unlock it first.');
            return;
        }

        // Get objects from group
        const objects = activeObject.getObjects();
        const groupLeft = activeObject.left;
        const groupTop = activeObject.top;

        // Remove group
        canvas.remove(activeObject);

        // Add objects back to canvas with correct positions
        objects.forEach(obj => {
            obj.set({
                left: obj.left + groupLeft,
                top: obj.top + groupTop
            });
            canvas.add(obj);
        });

        canvas.discardActiveObject();
        canvas.renderAll();
        refreshLayers();
    }

    function copySelected() {
        if (!canvas) return;

        const activeObjects = canvas.getActiveObjects();
        if (activeObjects.length === 0) {
            alert('Please select an object to copy');
            return;
        }

        // Store the JSON representation of selected objects
        const objectsToCopy = activeObjects.map(obj => obj.toObject(['id', 'name', 'data']));
        copiedObjects = {
            objects: objectsToCopy,
            offset: { x: 20, y: 20 } // Default offset for pasted objects
        };

        // Enable paste button
        const pasteBtn = document.getElementById('pasteBtn');
        const menuPasteBtn = document.getElementById('menuPasteBtn');
        const contextMenuPasteBtn = document.getElementById('contextMenuPasteBtn');
        if (pasteBtn) {
            pasteBtn.disabled = false;
        }
        if (menuPasteBtn) {
            menuPasteBtn.disabled = false;
        }
        if (contextMenuPasteBtn) {
            contextMenuPasteBtn.disabled = false;
        }

        console.log('Copied', activeObjects.length, 'object(s)');
    }

    function cutSelected() {
        if (!canvas) return;

        const activeObjects = canvas.getActiveObjects();
        if (activeObjects.length === 0) {
            alert('Please select an object to cut');
            return;
        }

        // Store the JSON representation of selected objects for paste
        const objectsToCopy = activeObjects.map(obj => obj.toObject(['id', 'name', 'data']));
        copiedObjects = {
            objects: objectsToCopy,
            offset: { x: 20, y: 20 } // Default offset for pasted objects
        };

        // Enable paste buttons
        const pasteBtn = document.getElementById('pasteBtn');
        const menuPasteBtn = document.getElementById('menuPasteBtn');
        const contextMenuPasteBtn = document.getElementById('contextMenuPasteBtn');
        if (pasteBtn) {
            pasteBtn.disabled = false;
        }
        if (menuPasteBtn) {
            menuPasteBtn.disabled = false;
        }
        if (contextMenuPasteBtn) {
            contextMenuPasteBtn.disabled = false;
        }

        // Delete the objects
        activeObjects.forEach(function(obj) {
            canvas.remove(obj);
        });
        canvas.discardActiveObject();
        canvas.renderAll();
        hidePropertiesPanel();
        hideContextMenu();

        console.log('Cut', activeObjects.length, 'object(s)');
    }

    function pasteSelected() {
        if (!canvas || !copiedObjects || !copiedObjects.objects) {
            alert('No objects copied. Please copy an object first.');
            return;
        }

        try {
            // Deselect current objects
            canvas.discardActiveObject();

            const offsetX = copiedObjects.offset.x;
            const offsetY = copiedObjects.offset.y;
            const pastedObjects = [];
            let processedCount = 0;

            // Use fabric.util.enlivenObjects to recreate objects from JSON
            fabric.util.enlivenObjects(copiedObjects.objects, function(objects) {
                objects.forEach((obj, index) => {
                    // Clone the object
                    obj.clone(function(clonedObj) {
                        if (!clonedObj) {
                            processedCount++;
                            if (processedCount === objects.length) {
                                selectAndRenderPastedObjects(pastedObjects);
                            }
                            return;
                        }

                        // Offset position for pasted objects
                        clonedObj.set({
                            left: clonedObj.left + offsetX,
                            top: clonedObj.top + offsetY
                        });

                        // For text objects, convert to IText for inline editing if needed
                        if (clonedObj.type === 'text' || clonedObj.type === 'i-text' || clonedObj.type === 'textbox') {
                            // If it's already IText, just add it
                            if (clonedObj.type === 'i-text' || clonedObj.type === 'textbox') {
                                canvas.add(clonedObj);
                                pastedObjects.push(clonedObj);
                            } else {
                                // Convert Text to IText
                                const iText = new fabric.IText(clonedObj.text, {
                                    left: clonedObj.left,
                                    top: clonedObj.top,
                                    fontFamily: clonedObj.fontFamily,
                                    fontSize: clonedObj.fontSize,
                                    fill: clonedObj.fill,
                                    width: clonedObj.width,
                                    textAlign: clonedObj.textAlign,
                                    fontStyle: clonedObj.fontStyle,
                                    fontWeight: clonedObj.fontWeight,
                                    underline: clonedObj.underline,
                                    linethrough: clonedObj.linethrough,
                                    textBackgroundColor: clonedObj.textBackgroundColor,
                                    angle: clonedObj.angle,
                                    scaleX: clonedObj.scaleX,
                                    scaleY: clonedObj.scaleY
                                });
                                canvas.add(iText);
                                pastedObjects.push(iText);
                            }
                        } else {
                            // For other object types, add directly
                            canvas.add(clonedObj);
                            pastedObjects.push(clonedObj);
                        }

                        processedCount++;

                        // After all objects are processed, select them
                        if (processedCount === objects.length) {
                            selectAndRenderPastedObjects(pastedObjects);
                        }
                    });
                });
            }, 'fabric');
        } catch (error) {
            console.error('Error pasting objects:', error);
            alert('Error pasting objects. Please try again.');
        }
    }

    function selectAndRenderPastedObjects(pastedObjects) {
        if (pastedObjects.length === 0) return;

        if (pastedObjects.length === 1) {
            canvas.setActiveObject(pastedObjects[0]);
        } else if (pastedObjects.length > 1) {
            const selection = new fabric.ActiveSelection(pastedObjects, {
                canvas: canvas
            });
            canvas.setActiveObject(selection);
        }

        canvas.renderAll();
        refreshLayers();
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

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Context Menu Functions
    function showContextMenu(x, y) {
        const contextMenu = document.getElementById('contextMenu');
        if (!contextMenu) {
            console.error('Context menu element not found');
            return;
        }

        // Populate custom links from editor settings
        const customLinksContainer = document.getElementById('contextMenuCustomLinks');
        const customLinksDivider = document.getElementById('contextMenuCustomLinksDivider');
        if (customLinksContainer && editorSettings.customContextMenuLinks && editorSettings.customContextMenuLinks.length > 0) {
            customLinksContainer.innerHTML = '';
            editorSettings.customContextMenuLinks.forEach(function(link) {
                if (link.label && link.url) {
                    const url = String(link.url).trim();
                    if (!/^(https?:\/\/|\/)/.test(url)) return;
                    const btn = document.createElement('button');
                    btn.className = 'context-menu-item';
                    btn.type = 'button';
                    const iconPart = (link.icon || 'fa-link').trim().split(/\s+/)[0].replace(/[^a-zA-Z0-9_-]/g, '') || 'fa-link';
                    btn.innerHTML = '<i class="fas ' + iconPart + '"></i><span>' + escapeHtml(link.label) + '</span>';
                    btn.onclick = function() {
                        window.open(url, '_blank', 'noopener,noreferrer');
                        hideContextMenu();
                    };
                    customLinksContainer.appendChild(btn);
                }
            });
            if (customLinksDivider) customLinksDivider.style.display = 'block';
        } else {
            if (customLinksDivider) customLinksDivider.style.display = 'none';
        }

        console.log('Showing context menu at', x, y);

        // Update paste button state
        const contextMenuPasteBtn = document.getElementById('contextMenuPasteBtn');
        if (contextMenuPasteBtn) {
            contextMenuPasteBtn.disabled = !copiedObjects || !copiedObjects.objects;
        }

        // Show/hide "Edit & Crop Image" and "Clipping Path" options based on selected object type
        const activeObject = canvas.getActiveObject();
        const editImageBtn = document.getElementById('contextMenuEditImage');
        const clipImageBtn = document.getElementById('contextMenuClipImage');
        const editImageDivider = document.getElementById('contextMenuEditImageDivider');

        const isTextElement = activeObject && ['text', 'textbox', 'i-text'].indexOf(activeObject.type) !== -1;
        const generateTextBtn = document.getElementById('contextMenuGenerateText');
        const generateTextDivider = document.getElementById('contextMenuGenerateTextDivider');
        if (isTextElement) {
            if (generateTextBtn) generateTextBtn.style.display = 'block';
            if (generateTextDivider) generateTextDivider.style.display = 'block';
        } else {
            if (generateTextBtn) generateTextBtn.style.display = 'none';
            if (generateTextDivider) generateTextDivider.style.display = 'none';
        }

        if (activeObject && activeObject.type === 'image') {
            // Show edit image and clipping options for images
            if (editImageBtn) editImageBtn.style.display = 'block';
            if (clipImageBtn) clipImageBtn.style.display = 'block';
            if (editImageDivider) editImageDivider.style.display = 'block';
        } else {
            // Hide edit image and clipping options for non-images
            if (editImageBtn) editImageBtn.style.display = 'none';
            if (clipImageBtn) clipImageBtn.style.display = 'none';
            if (editImageDivider) editImageDivider.style.display = 'none';
        }

        // Position the menu
        contextMenu.style.display = 'block';
        // Force reflow to get actual dimensions
        contextMenu.offsetHeight;

        const menuWidth = contextMenu.offsetWidth || 200;
        const menuHeight = contextMenu.offsetHeight || 300;
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        // Adjust position if menu would go off screen
        let posX = x;
        let posY = y;

        if (x + menuWidth > windowWidth) {
            posX = windowWidth - menuWidth - 10;
        }
        if (y + menuHeight > windowHeight) {
            posY = windowHeight - menuHeight - 10;
        }
        if (posX < 10) posX = 10;
        if (posY < 10) posY = 10;

        contextMenu.style.left = posX + 'px';
        contextMenu.style.top = posY + 'px';
        contextMenuVisible = true;
    }

    function hideContextMenu() {
        const contextMenu = document.getElementById('contextMenu');
        if (contextMenu) {
            contextMenu.style.display = 'none';
            contextMenuVisible = false;
        }
    }

    // Context menu action wrappers
    function contextMenuCut() {
        cutSelected();
        hideContextMenu();
    }

    function contextMenuCopy() {
        copySelected();
        hideContextMenu();
    }

    function contextMenuPaste() {
        pasteSelected();
        hideContextMenu();
    }

    function contextMenuDuplicate() {
        duplicateSelected();
        hideContextMenu();
    }

    function contextMenuDelete() {
        deleteSelected();
        hideContextMenu();
    }

    function contextMenuGenerateTextContent() {
        const activeObject = canvas.getActiveObject();
        if (!activeObject || ['text', 'textbox', 'i-text'].indexOf(activeObject.type) === -1) {
            hideContextMenu();
            return;
        }
        hideContextMenu();
        openAIGenerateTextModal(activeObject);
    }

    function contextMenuEditImage() {
        console.log('contextMenuEditImage called');
        const activeObject = canvas.getActiveObject();
        console.log('Active object:', activeObject);

        if (!activeObject) {
            console.error('No active object found');
            alert('Please select an image first.');
            hideContextMenu();
            return;
        }

        if (activeObject.type !== 'image') {
            console.error('Selected object is not an image, type is:', activeObject.type);
            alert('Please select an image to edit.');
            hideContextMenu();
            return;
        }

        console.log('Opening image crop modal from context menu');
        hideContextMenu();

        // Small delay to ensure context menu is hidden first
        setTimeout(function() {
            console.log('Checking for openImageCropModal function...');
            console.log('window.openImageCropModal:', typeof window.openImageCropModal);
            console.log('openImageCropModal:', typeof openImageCropModal);

            // Use window.openImageCropModal to ensure we get the global function
            if (typeof window.openImageCropModal === 'function') {
                console.log('Calling window.openImageCropModal');
                try {
                    window.openImageCropModal(activeObject);
                } catch (e) {
                    console.error('Error calling openImageCropModal:', e);
                    alert('Error opening image crop modal: ' + e.message);
                }
            } else if (typeof openImageCropModal === 'function') {
                console.log('Calling openImageCropModal');
                try {
                    openImageCropModal(activeObject);
                } catch (e) {
                    console.error('Error calling openImageCropModal:', e);
                    alert('Error opening image crop modal: ' + e.message);
                }
            } else {
                console.error('openImageCropModal function not found');
                console.log('Available functions:', Object.keys(window).filter(k => k.includes('Image') || k.includes('Crop')));
                alert('Error: Image crop function not available. Please refresh the page.');
            }
        }, 100);
    }

    function contextMenuBringToFront() {
        bringToFront();
        hideContextMenu();
    }

    function contextMenuSendToBack() {
        sendToBack();
        hideContextMenu();
    }

    function contextMenuBringForward() {
        bringForward();
        hideContextMenu();
    }

    function contextMenuSendBackward() {
        sendBackward();
        hideContextMenu();
    }

    function zoomIn() {
        zoomLevel = Math.min(zoomLevel + 10, 200);
        updateZoom();
    }

    function zoomOut() {
        zoomLevel = Math.max(zoomLevel - 10, 50);
        updateZoom();
    }

    function toggleFullscreen() {
        const designEditor = document.querySelector('.design-editor');
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        const fullscreenIcon = document.getElementById('fullscreenIcon');

        if (!designEditor) return;

        if (designEditor.classList.contains('fullscreen')) {
            // Exit fullscreen
            designEditor.classList.remove('fullscreen');
            if (fullscreenIcon) {
                fullscreenIcon.classList.remove('fa-compress');
                fullscreenIcon.classList.add('fa-expand');
            }
            if (fullscreenBtn) {
                fullscreenBtn.title = 'Enter Fullscreen';
            }
        } else {
            // Enter fullscreen
            designEditor.classList.add('fullscreen');
            if (fullscreenIcon) {
                fullscreenIcon.classList.remove('fa-expand');
                fullscreenIcon.classList.add('fa-compress');
            }
            if (fullscreenBtn) {
                fullscreenBtn.title = 'Exit Fullscreen';
            }
        }
    }

    // Handle ESC key to exit fullscreen
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const designEditor = document.querySelector('.design-editor');
            if (designEditor && designEditor.classList.contains('fullscreen')) {
                toggleFullscreen();
            }
        }
    });

    function updateZoom() {
        const container = document.getElementById('canvasContainer');
        const wrapper = container.querySelector('.canvas-wrapper');
        wrapper.style.transform = `scale(${zoomLevel / 100})`;
        wrapper.style.transformOrigin = 'center';
        document.getElementById('zoomLevel').textContent = zoomLevel + '%';

        // Update rulers if visible
        if (rulersVisible) {
            setTimeout(updateRulers, 50);
        }
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

    // Alignment Guides Functions
    function showAlignmentGuides(activeObj) {
        if (!canvas || !activeObj || activeObj.locked) return;

        // Clear existing guides
        clearAlignmentGuides();

        const objects = canvas.getObjects();
        const activeBounds = activeObj.getBoundingRect();
        const canvasWidth = canvas.getWidth();
        const canvasHeight = canvas.getHeight();

        // Alignment positions to check
        const activePositions = {
            left: activeBounds.left,
            centerX: activeBounds.left + activeBounds.width / 2,
            right: activeBounds.left + activeBounds.width,
            top: activeBounds.top,
            centerY: activeBounds.top + activeBounds.height / 2,
            bottom: activeBounds.top + activeBounds.height
        };

        // Canvas center positions
        const canvasCenterX = canvasWidth / 2;
        const canvasCenterY = canvasHeight / 2;

        let hasSnapped = false;

        // Check alignment with other objects
        objects.forEach(obj => {
            if (obj === activeObj || obj.locked || obj.name === 'alignmentGuide') return;

            const objBounds = obj.getBoundingRect();
            const objPositions = {
                left: objBounds.left,
                centerX: objBounds.left + objBounds.width / 2,
                right: objBounds.left + objBounds.width,
                top: objBounds.top,
                centerY: objBounds.top + objBounds.height / 2,
                bottom: objBounds.top + objBounds.height
            };

            // Check horizontal alignments (top, center, bottom)
            ['top', 'centerY', 'bottom'].forEach(alignType => {
                const diff = Math.abs(activePositions[alignType] - objPositions[alignType]);
                if (diff <= snapThreshold) {
                    // Snap to alignment
                    const targetY = objPositions[alignType];
                    if (alignType === 'top') {
                        activeObj.set('top', targetY);
                    } else if (alignType === 'centerY') {
                        activeObj.set('top', targetY - activeBounds.height / 2);
                    } else if (alignType === 'bottom') {
                        activeObj.set('top', targetY - activeBounds.height);
                    }
                    activeObj.setCoords();
                    hasSnapped = true;

                    // Draw horizontal guide line
                    drawGuideLine(0, objPositions[alignType], canvasWidth, objPositions[alignType], 'horizontal');
                }
            });

            // Check vertical alignments (left, center, right)
            ['left', 'centerX', 'right'].forEach(alignType => {
                const diff = Math.abs(activePositions[alignType] - objPositions[alignType]);
                if (diff <= snapThreshold) {
                    // Snap to alignment
                    const targetX = objPositions[alignType];
                    if (alignType === 'left') {
                        activeObj.set('left', targetX);
                    } else if (alignType === 'centerX') {
                        activeObj.set('left', targetX - activeBounds.width / 2);
                    } else if (alignType === 'right') {
                        activeObj.set('left', targetX - activeBounds.width);
                    }
                    activeObj.setCoords();
                    hasSnapped = true;

                    // Draw vertical guide line
                    drawGuideLine(objPositions[alignType], 0, objPositions[alignType], canvasHeight, 'vertical');
                }
            });
        });

        // Check alignment with canvas center (only if not already snapped to object)
        if (!hasSnapped) {
            const centerXDiff = Math.abs(activePositions.centerX - canvasCenterX);
            const centerYDiff = Math.abs(activePositions.centerY - canvasCenterY);

            if (centerXDiff <= snapThreshold) {
                activeObj.set('left', canvasCenterX - activeBounds.width / 2);
                activeObj.setCoords();
                drawGuideLine(canvasCenterX, 0, canvasCenterX, canvasHeight, 'vertical');
            }

            if (centerYDiff <= snapThreshold) {
                activeObj.set('top', canvasCenterY - activeBounds.height / 2);
                activeObj.setCoords();
                drawGuideLine(0, canvasCenterY, canvasWidth, canvasCenterY, 'horizontal');
            }
        }

        canvas.renderAll();
    }

    function drawGuideLine(x1, y1, x2, y2, orientation) {
        if (!canvas) return;

        // Create guide line
        const line = new fabric.Line([x1, y1, x2, y2], {
            stroke: '#6366f1',
            strokeWidth: 1,
            strokeDashArray: [5, 5],
            selectable: false,
            evented: false,
            excludeFromExport: true,
            name: 'alignmentGuide',
            opacity: 0.8
        });

        // Add to canvas and bring to front so it's visible
        canvas.add(line);
        canvas.bringToFront(line);
        alignmentGuides.push(line);
    }

    function clearAlignmentGuides() {
        if (!canvas) return;

        // Remove all guide lines
        alignmentGuides.forEach(guide => {
            canvas.remove(guide);
        });
        alignmentGuides = [];
        canvas.renderAll();
    }

    function switchVerticalTab(tab) {
        // Remove active class from all vertical tab buttons
        document.querySelectorAll('.vertical-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Hide all tab panels
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.remove('active');
        });

        // Activate selected tab button
        const tabButton = document.getElementById('verticalTab' + tab.charAt(0).toUpperCase() + tab.slice(1));
        if (tabButton) {
            tabButton.classList.add('active');
        }

        // Show selected tab panel
        const tabPanel = document.getElementById(tab + 'Panel');
        if (tabPanel) {
            tabPanel.classList.add('active');
        }

        // Special handling for layers tab
        if (tab === 'layers' && typeof refreshLayers === 'function') {
                    refreshLayers();
        }

        // Special handling for images tab
        if (tab === 'images' && typeof loadImageLibrary === 'function') {
            loadImageLibrary();
        }

        // Special handling for global images tab
        if (tab === 'globalImages' && typeof loadGlobalImageLibrary === 'function') {
            loadGlobalImageLibrary();
        }

        // Load templates when templates tab is opened
        if (tab === 'templates' && typeof loadTemplates === 'function') {
            loadTemplates();
        }
    }

    // Keep old functions for backward compatibility
    function switchTab(tab) {
        switchVerticalTab(tab);
    }

    function switchSidebarTab(tab) {
        switchVerticalTab(tab === 'elements' ? 'elements' : 'layers');
    }

    function switchPagesTab(tab) {
        switchVerticalTab(tab === 'pages' ? 'pages' : 'templates');
    }

    function switchPropertiesVerticalTab(tab) {
        // Remove active class from all vertical tab buttons
        document.querySelectorAll('.properties-vertical-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Hide all tab panels
        document.querySelectorAll('.properties-tab-panel').forEach(panel => {
            panel.classList.remove('active');
        });

        // Activate selected tab button
        const tabButton = document.getElementById('verticalTab' + tab.charAt(0).toUpperCase() + tab.slice(1));
        if (tabButton) {
            tabButton.classList.add('active');
        }

        // Show selected tab panel
        const tabPanel = document.getElementById(tab + 'Tab');
        if (tabPanel) {
            tabPanel.classList.add('active');
            // Refresh variables when switching to variables tab
            if (tab === 'variables') {
                refreshVariables();
            }
        }
    }

    // Mobile app-style panel toggles (3-item bottom bar)
    function setMobileBarActive(activeId) {
        ['mobileBtnDesignPanels', 'mobileBtnItemPanel', 'mobileBtnOptions'].forEach(function(id) {
            var btn = document.getElementById(id);
            if (btn) btn.classList.toggle('active', id === activeId);
        });
    }
    function toggleMobileLeftSidebar() {
        const left = document.getElementById('leftSidebar');
        const overlay = document.getElementById('designMobileOverlay');
        const optionsPanel = document.getElementById('designMobileOptionsPanel');
        if (!left || !overlay) return;
        const isOpen = left.classList.toggle('mobile-open');
        overlay.classList.toggle('mobile-open', isOpen);
        if (optionsPanel) optionsPanel.classList.remove('mobile-open');
        const props = document.getElementById('propertiesPanel');
        if (props) props.classList.remove('mobile-open');
        setMobileBarActive(isOpen ? 'mobileBtnDesignPanels' : null);
    }
    function toggleMobileItemPanel() {
        if (window.innerWidth > 768) return;
        const props = document.getElementById('propertiesPanel');
        const overlay = document.getElementById('designMobileOverlay');
        const left = document.getElementById('leftSidebar');
        const optionsPanel = document.getElementById('designMobileOptionsPanel');
        if (!props || !overlay) return;
        const isOpen = props.classList.toggle('mobile-open');
        overlay.classList.toggle('mobile-open', isOpen);
        if (left) left.classList.remove('mobile-open');
        if (optionsPanel) optionsPanel.classList.remove('mobile-open');
        setMobileBarActive(isOpen ? 'mobileBtnItemPanel' : null);
    }
    function toggleMobileOptionsPanel() {
        const optionsPanel = document.getElementById('designMobileOptionsPanel');
        const overlay = document.getElementById('designMobileOverlay');
        const left = document.getElementById('leftSidebar');
        const props = document.getElementById('propertiesPanel');
        if (!optionsPanel || !overlay) return;
        const isOpen = optionsPanel.classList.toggle('mobile-open');
        overlay.classList.toggle('mobile-open', isOpen);
        if (left) left.classList.remove('mobile-open');
        if (props) props.classList.remove('mobile-open');
        setMobileBarActive(isOpen ? 'mobileBtnOptions' : null);
    }
    function closeMobilePanels() {
        const left = document.getElementById('leftSidebar');
        const props = document.getElementById('propertiesPanel');
        const optionsPanel = document.getElementById('designMobileOptionsPanel');
        const overlay = document.getElementById('designMobileOverlay');
        if (left) left.classList.remove('mobile-open');
        if (props) props.classList.remove('mobile-open');
        if (optionsPanel) optionsPanel.classList.remove('mobile-open');
        if (overlay) overlay.classList.remove('mobile-open');
        setMobileBarActive(null);
    }
    function openMobilePropertiesPanel() {
        if (window.innerWidth > 768) return;
        const props = document.getElementById('propertiesPanel');
        const overlay = document.getElementById('designMobileOverlay');
        const left = document.getElementById('leftSidebar');
        const optionsPanel = document.getElementById('designMobileOptionsPanel');
        if (props) props.classList.add('mobile-open');
        if (overlay) overlay.classList.add('mobile-open');
        if (left) left.classList.remove('mobile-open');
        if (optionsPanel) optionsPanel.classList.remove('mobile-open');
        setMobileBarActive('mobileBtnItemPanel');
    }

    // Variables Management
    function extractFontsFromPages(pagesArray) {
        const fonts = [];
        const defaultFonts = ['Arial', 'Helvetica', 'Times New Roman', 'Courier New', 'Verdana', 'Georgia'];
        const fontMap = new Map(); // To avoid duplicates

        pagesArray.forEach((page, pageIndex) => {
            try {
                const pageData = typeof page.data === 'string' ? JSON.parse(page.data) : page.data;

                if (pageData && pageData.objects && Array.isArray(pageData.objects)) {
                    pageData.objects.forEach(obj => {
                        const textTypes = ['text', 'textbox', 'i-text'];
                        if (textTypes.includes(obj.type) && obj.fontFamily) {
                            const fontFamily = obj.fontFamily;

                            // Skip default fonts
                            if (!defaultFonts.includes(fontFamily) && !fontMap.has(fontFamily)) {
                                // Try to find font in customFonts array
                                const customFont = customFonts.find(f => f.name === fontFamily);

                                if (customFont) {
                                    fontMap.set(fontFamily, customFont);
                                } else {
                                    // Font name exists but not in library - still add it
                                    fontMap.set(fontFamily, {
                                        name: fontFamily,
                                        path: null,
                                        url: null,
                                        filename: null,
                                        extension: null
                                    });
                                }
                            }
                        }
                    });
                }
            } catch (e) {
                console.error('Error extracting fonts from page:', e);
            }
        });

        return Array.from(fontMap.values());
    }

    function extractVariablesFromPages(pagesArray) {
        const variablesMap = new Map();

        // Scan all pages
        pagesArray.forEach((page, pageIndex) => {
            try {
                if (!page.data) return;
                const pageData = JSON.parse(page.data);
                if (!pageData.objects) return;

                // Extract text from all text objects
                pageData.objects.forEach(obj => {
                    if (obj.type === 'text' || obj.type === 'i-text' || obj.type === 'textbox') {
                        const text = obj.text || '';
                        // Find all variable patterns (format: double braces with variable name)
                        const regex = new RegExp('\\{\\{([^}]+)\\}\\}', 'g');
                        let match;
                        while ((match = regex.exec(text)) !== null) {
                            const variableName = match[1].trim();
                            if (variableName) {
                                if (!variablesMap.has(variableName)) {
                                    variablesMap.set(variableName, { count: 0, pages: [] });
                                }
                                const variableData = variablesMap.get(variableName);
                                variableData.count++;
                                if (!variableData.pages.includes(pageIndex + 1)) {
                                    variableData.pages.push(pageIndex + 1);
                                }
                            }
                        }
                    }
                });
            } catch (e) {
                console.error('Error parsing page data:', e);
            }
        });

        // Convert to array format
        const variablesArray = Array.from(variablesMap.entries()).map(([name, data]) => ({
            name: name,
            count: data.count,
            pages: data.pages.sort((a, b) => a - b)
        }));

        return variablesArray;
    }

    function refreshVariables() {
        if (!pages || pages.length === 0) {
            const variablesList = document.getElementById('variablesList');
            if (variablesList) {
                variablesList.innerHTML = '<p class="text-muted small text-center" style="padding: 1rem 0; color: #94a3b8; font-size: 0.875rem;">No pages to scan</p>';
            }
            return;
        }

        const variablesMap = new Map(); // variableName -> {count: number, pages: Set}

        // Scan all pages
        pages.forEach((page, pageIndex) => {
            try {
                if (!page.data) return;
                const pageData = JSON.parse(page.data);
                if (!pageData.objects) return;

                // Extract text from all text objects
                pageData.objects.forEach(obj => {
                    if (obj.type === 'text' || obj.type === 'i-text' || obj.type === 'textbox') {
                        const text = obj.text || '';
                        // Find all variable patterns (format: double braces with variable name)
                        const regex = new RegExp('\\{\\{([^}]+)\\}\\}', 'g');
                        let match;
                        while ((match = regex.exec(text)) !== null) {
                            const variableName = match[1].trim();
                            if (variableName) {
                                if (!variablesMap.has(variableName)) {
                                    variablesMap.set(variableName, { count: 0, pages: new Set() });
                                }
                                const variableData = variablesMap.get(variableName);
                                variableData.count++;
                                variableData.pages.add(pageIndex + 1); // Page numbers start from 1
                            }
                        }
                    }
                });
            } catch (e) {
                console.error('Error parsing page data:', e);
            }
        });

        // Display variables
        const variablesList = document.getElementById('variablesList');
        if (!variablesList) return;

        if (variablesMap.size === 0) {
            variablesList.innerHTML = '<p class="text-muted small text-center" style="padding: 1rem 0; color: #94a3b8; font-size: 0.875rem;">No variables found. Use {&#123;variable_name&#125;} in text content.</p>';
            return;
        }

        // Sort variables alphabetically
        const sortedVariables = Array.from(variablesMap.entries()).sort((a, b) => a[0].localeCompare(b[0]));

        variablesList.innerHTML = '';
        sortedVariables.forEach(([variableName, data]) => {
            const variableItem = document.createElement('div');
            variableItem.className = 'variable-item';

            const nameDiv = document.createElement('div');
            nameDiv.className = 'variable-name';
            nameDiv.textContent = '{' + '{' + variableName + '}' + '}';

            const countDiv = document.createElement('div');
            countDiv.className = 'variable-count';
            countDiv.textContent = `Found ${data.count} time${data.count > 1 ? 's' : ''}`;

            const pagesDiv = document.createElement('div');
            pagesDiv.className = 'variable-pages';
            const pagesArray = Array.from(data.pages).sort((a, b) => a - b);
            pagesDiv.textContent = `Page${pagesArray.length > 1 ? 's' : ''}: ${pagesArray.join(', ')}`;

            variableItem.appendChild(nameDiv);
            variableItem.appendChild(countDiv);
            variableItem.appendChild(pagesDiv);

            variablesList.appendChild(variableItem);
        });
    }

    // Keep old function for backward compatibility
    function switchPropertiesTab(tab) {
        switchPropertiesVerticalTab(tab);
    }

    function updateCanvasSize(dimension, value) {
        if (!canvas) return;
        const sizeValue = parseInt(value);

        if (dimension === 'width') {
            canvas.setWidth(sizeValue);
        } else {
            canvas.setHeight(sizeValue);
        }

        canvas.renderAll();

        // Save canvas size to current page data
        if (pages.length > 0 && currentPageIndex >= 0) {
            try {
                const currentPageData = canvas.toJSON(['width', 'height', 'backgroundColor']);
                // Filter out alignment guides
                if (currentPageData.objects) {
                    currentPageData.objects = currentPageData.objects.filter(obj => obj.name !== 'alignmentGuide');
                }
                // Ensure width and height are stored
                currentPageData.width = canvas.getWidth();
                currentPageData.height = canvas.getHeight();
                pages[currentPageIndex].data = JSON.stringify(currentPageData);
                updatePageThumbnail(currentPageIndex);
            } catch (e) {
                console.error('Error saving canvas size:', e);
            }
        }
    }

    function updateCanvasBackground(color) {
        if (!canvas) return;
        canvas.setBackgroundColor(color, function() {
            canvas.renderAll();

            // Save background color to current page data
            if (pages.length > 0 && currentPageIndex >= 0) {
                try {
                    const currentPageData = canvas.toJSON(['width', 'height', 'backgroundColor']);
                    // Filter out alignment guides
                    if (currentPageData.objects) {
                        currentPageData.objects = currentPageData.objects.filter(obj => obj.name !== 'alignmentGuide');
                    }
                    // Ensure background color is stored
                    currentPageData.backgroundColor = color;
                    pages[currentPageIndex].data = JSON.stringify(currentPageData);
                    updatePageThumbnail(currentPageIndex);
                } catch (e) {
                    console.error('Error saving background color:', e);
                }
            }
        });
    }

    // Panel toggle functions - removed collapse functionality for properties panel

    function refreshLayers() {
        const layersList = document.getElementById('layersList');
        if (!canvas) {
            layersList.innerHTML = '<p class="text-muted small text-center" style="padding: 1rem 0;">Canvas not initialized</p>';
            return;
        }

        const objects = canvas.getObjects();

        if (objects.length === 0) {
            layersList.innerHTML = '<p class="text-muted small text-center" style="padding: 1rem 0;">No layers yet</p>';
            return;
        }

        layersList.innerHTML = '';
        // Display layers in reverse order (top layer first), but use actual canvas indices
        const reversedObjects = objects.slice().reverse();

        reversedObjects.forEach(function(obj, displayIndex) {
            // Calculate actual canvas index (reverse of display index)
            const actualIndex = objects.length - 1 - displayIndex;
            const layerItem = document.createElement('div');
            layerItem.className = 'layer-item';
            layerItem.draggable = true;
            layerItem.dataset.objectIndex = actualIndex;

            // Store object reference
            layerItem._fabricObject = obj;

            // Click to select
            layerItem.onclick = (e) => {
                // Don't trigger if clicking delete button or drag handle
                if (e.target.closest('.layer-delete-btn') || e.target.closest('.layer-drag-handle')) {
                    return;
                }
                canvas.setActiveObject(obj);
                canvas.renderAll();
                refreshLayers();
            };

            // Drag and drop handlers
            layerItem.addEventListener('dragstart', function(e) {
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', this.innerHTML);
                e.dataTransfer.setData('text/plain', actualIndex.toString());
            });

            layerItem.addEventListener('dragend', function(e) {
                this.classList.remove('dragging');
                // Remove drag-over class from all items
                document.querySelectorAll('.layer-item').forEach(item => {
                    item.classList.remove('drag-over');
                });
            });

            layerItem.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                // Remove drag-over from all items
                document.querySelectorAll('.layer-item').forEach(item => {
                    item.classList.remove('drag-over');
                });

                // Add drag-over to this item
                this.classList.add('drag-over');
            });

            layerItem.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');

                const draggedIndex = parseInt(e.dataTransfer.getData('text/plain'));
                const targetIndex = parseInt(this.dataset.objectIndex);

                if (draggedIndex !== targetIndex) {
                    moveLayer(draggedIndex, targetIndex);
                }
            });

            layerItem.addEventListener('dragleave', function(e) {
                this.classList.remove('drag-over');
            });

            // Check if it's a group - check multiple ways
            let isGroup = false;
            let groupItemCount = 0;

            // First check type property
            if (obj.type === 'group') {
                isGroup = true;
            }
            // Check if it has getObjects method (which groups have)
            else if (typeof obj.getObjects === 'function') {
                try {
                    const groupObjs = obj.getObjects();
                    if (Array.isArray(groupObjs) && groupObjs.length > 0) {
                        isGroup = true;
                        groupItemCount = groupObjs.length;
                    }
                } catch (e) {
                    // Not a group if getObjects fails
                    isGroup = false;
                }
            }
            // Check instanceof as last resort
            else if (obj instanceof fabric.Group) {
                isGroup = true;
                try {
                    if (typeof obj.getObjects === 'function') {
                        groupItemCount = obj.getObjects().length;
                    }
                } catch (e) {
                    console.error('Error getting group objects:', e);
                }
            }

            // If we determined it's a group, get the count if we don't have it
            if (isGroup && groupItemCount === 0) {
                try {
                    if (typeof obj.getObjects === 'function') {
                        groupItemCount = obj.getObjects().length;
                    }
                } catch (e) {
                    console.error('Error getting group item count:', e);
                }
            }

            const icon = isGroup ? 'fas fa-object-group' :
                        obj.type === 'text' || obj.type === 'textbox' || obj.type === 'i-text' ? 'fas fa-font' :
                        obj.type === 'rect' ? 'fas fa-square' :
                        obj.type === 'circle' ? 'fas fa-circle' :
                        obj.type === 'image' ? 'fas fa-image' : 'fas fa-shapes';

            const name = isGroup ? `Group (${groupItemCount} items)` : (obj.text || obj.type || 'Layer');
            const isLocked = obj.locked || false;

            // Add locked class to layer item if locked
            if (isLocked) {
                layerItem.classList.add('locked');
            }

            // Add group class if it's a group
            if (isGroup) {
                layerItem.classList.add('layer-group');
                layerItem.dataset.groupId = 'group_' + actualIndex;
            }

            // Build group toggle if it's a group
            const groupToggle = isGroup ? `
                <span class="layer-group-toggle" onclick="event.stopPropagation(); toggleGroupExpand('group_${actualIndex}')">
                    <i class="fas fa-chevron-right"></i>
                </span>
            ` : '';

            layerItem.innerHTML = `
                <div class="layer-drag-handle">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                ${groupToggle}
                <div class="layer-icon"><i class="${icon}"></i></div>
                <div class="layer-info" style="flex: 1;">
                    <div class="layer-name">${name}</div>
                    <div class="layer-type">${obj.type}</div>
                </div>
                <div class="layer-actions">
                    <button class="layer-lock-btn ${isLocked ? 'locked' : ''}" onclick="event.stopPropagation(); toggleLayerLock(${actualIndex})" title="${isLocked ? 'Unlock Layer' : 'Lock Layer'}">
                        <i class="fas ${isLocked ? 'fa-lock' : 'fa-unlock'}"></i>
                    </button>
                    <button class="layer-delete-btn" onclick="event.stopPropagation(); deleteLayer(${actualIndex})" title="Delete Layer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;

            // Add group items container if it's a group
            if (isGroup && groupItemCount > 0) {
                const groupItemsContainer = document.createElement('div');
                groupItemsContainer.className = 'layer-group-items';
                groupItemsContainer.id = 'group_items_' + actualIndex;

                // Add nested objects with error handling
                let groupObjects = [];
                try {
                    if (typeof obj.getObjects === 'function') {
                        groupObjects = obj.getObjects();
                    }
                } catch (e) {
                    console.error('Error getting group objects for display:', e);
                    groupObjects = [];
                }

                groupObjects.forEach(function(groupObj, groupObjIndex) {
                    const nestedItem = document.createElement('div');
                    nestedItem.className = 'layer-item';
                    nestedItem.style.padding = '0.4rem 0.5rem';
                    nestedItem.style.marginLeft = '0.5rem';

                    const nestedIcon = groupObj.type === 'text' || groupObj.type === 'textbox' || groupObj.type === 'i-text' ? 'fas fa-font' :
                                     groupObj.type === 'rect' ? 'fas fa-square' :
                                     groupObj.type === 'circle' ? 'fas fa-circle' :
                                     groupObj.type === 'image' ? 'fas fa-image' : 'fas fa-shapes';

                    const nestedName = groupObj.text || groupObj.type || 'Item';

                    nestedItem.innerHTML = `
                        <div class="layer-icon" style="width: 22px; height: 22px;"><i class="${nestedIcon}" style="font-size: 0.65rem;"></i></div>
                        <div class="layer-info" style="flex: 1;">
                            <div class="layer-name" style="font-size: 0.7rem;">${nestedName}</div>
                            <div class="layer-type" style="font-size: 0.6rem;">${groupObj.type}</div>
                        </div>
                    `;

                    groupItemsContainer.appendChild(nestedItem);
                });

                layerItem.appendChild(groupItemsContainer);
            }

            layersList.appendChild(layerItem);
        });
    }

    function toggleGroupExpand(groupId) {
        const groupItem = document.querySelector(`[data-group-id="${groupId}"]`);
        if (groupItem) {
            groupItem.classList.toggle('expanded');
            const toggleIcon = groupItem.querySelector('.layer-group-toggle i');
            if (toggleIcon) {
                toggleIcon.classList.toggle('fa-chevron-right');
                toggleIcon.classList.toggle('fa-chevron-down');
            }
        }
    }

    function ungroupLayer(index) {
        if (!canvas) return;

        const objects = canvas.getObjects();
        if (index >= 0 && index < objects.length) {
            const obj = objects[index];

            if (obj.type !== 'group') {
                alert('Selected object is not a group');
                return;
            }

            // Check if group is locked
            if (obj.locked) {
                alert('Cannot ungroup a locked group. Please unlock it first.');
                return;
            }

            // Get objects from group
            const groupObjects = obj.getObjects();
            const groupLeft = obj.left;
            const groupTop = obj.top;

            // Remove group
            canvas.remove(obj);

            // Add objects back to canvas with correct positions
            groupObjects.forEach(groupObj => {
                groupObj.set({
                    left: groupObj.left + groupLeft,
                    top: groupObj.top + groupTop
                });
                canvas.add(groupObj);
            });

            canvas.discardActiveObject();
            canvas.renderAll();
            refreshLayers();
        }
    }

    function toggleLayerLock(index) {
        if (!canvas) return;

        const objects = canvas.getObjects();
        if (index >= 0 && index < objects.length) {
            const obj = objects[index];
            const isLocked = obj.locked || false;
            const newLockState = !isLocked;

            // Toggle lock state - set all properties at once
            obj.set({
                'locked': newLockState,
                'selectable': !newLockState,
                'evented': !newLockState,
                'hasControls': !newLockState,
                'hasBorders': !newLockState
            });

            // For text objects, also set editable
            if (obj.type === 'i-text' || obj.type === 'textbox' || obj.type === 'text') {
                obj.set('editable', !newLockState);
            }

            // Update coordinates to ensure proper rendering
            obj.setCoords();

            // If locking, deselect the object
            if (newLockState) {
                // Locking - deselect if currently selected
                if (canvas.getActiveObject() === obj) {
                    canvas.discardActiveObject();
                    if (currentObject === obj) {
                        hidePropertiesPanel();
                    }
                }
            } else {
                // Unlocking - ensure object is fully functional
                // Force object to be recognized as interactive
                obj.setCoords();
                // Make sure the object can be selected and manipulated
                if (canvas.getActiveObject() === obj) {
                    // If it's currently selected, refresh the selection
                    canvas.setActiveObject(obj);
                }
            }

            // Force canvas to update
            canvas.renderAll();
            refreshLayers();
        }
    }

    function deleteLayer(index) {
        if (!canvas) return;

        const objects = canvas.getObjects();
        if (index >= 0 && index < objects.length) {
            const obj = objects[index];

            // Don't delete if locked
            if (obj.locked) {
                alert('Cannot delete a locked layer. Please unlock it first.');
                return;
            }

            canvas.remove(obj);
            canvas.renderAll();
            refreshLayers();

            // Hide properties panel if deleted object was selected
            if (currentObject === obj) {
                hidePropertiesPanel();
            }
        }
    }

    function moveLayer(fromIndex, toIndex) {
        if (!canvas) return;

        const objects = canvas.getObjects();
        if (fromIndex < 0 || fromIndex >= objects.length || toIndex < 0 || toIndex >= objects.length) {
            return;
        }

        if (fromIndex === toIndex) {
            return; // No move needed
        }

        // Get the object to move
        const objectToMove = objects[fromIndex];

        // Don't move if locked
        if (objectToMove.locked) {
            alert('Cannot move a locked layer. Please unlock it first.');
            return;
        }

        // Remove it from canvas
        canvas.remove(objectToMove);

        // Get updated objects array (after removal)
        const updatedObjects = canvas.getObjects();

        // Calculate new index (accounting for removal)
        // When we remove an item, indices shift
        let newIndex = toIndex;
        if (fromIndex < toIndex) {
            // Moving down: after removal, target index decreases by 1
            newIndex = toIndex;
        } else {
            // Moving up: target index stays the same
            newIndex = toIndex;
        }

        // Ensure newIndex is within bounds
        if (newIndex < 0) newIndex = 0;
        if (newIndex > updatedObjects.length) newIndex = updatedObjects.length;

        // Insert at new position
        if (newIndex >= updatedObjects.length) {
            // Add to end
            canvas.add(objectToMove);
        } else {
            // Insert at specific position
            canvas.insertAt(objectToMove, newIndex, false);
        }

        canvas.renderAll();
        refreshLayers();
    }

    function undo() {
        // Simple undo - reload current page
        if (pages.length > 0 && currentPageIndex >= 0) {
            canvas.loadFromJSON(pages[currentPageIndex].data, function() {
                canvas.renderAll();
                refreshLayers();
            });
        }
    }

    function redo() {
        // Simple redo - same as undo for now
        undo();
    }

    function saveDesign() {
        // Save current page first
        if (pages.length > 0 && currentPageIndex >= 0 && canvas) {
            try {
                const currentPageJSON = canvas.toJSON(['width', 'height', 'backgroundColor']);
                // Filter out alignment guides
                if (currentPageJSON.objects) {
                    currentPageJSON.objects = currentPageJSON.objects.filter(obj => obj.name !== 'alignmentGuide');
                }
                // Ensure width, height, and backgroundColor are always stored
                currentPageJSON.width = canvas.getWidth();
                currentPageJSON.height = canvas.getHeight();
                currentPageJSON.backgroundColor = canvas.backgroundColor || '#ffffff';
                if (!currentPageJSON.background) {
                    currentPageJSON.background = currentPageJSON.backgroundColor;
                }
                pages[currentPageIndex].data = JSON.stringify(currentPageJSON);
                console.log('Saved current page', currentPageIndex, 'with', currentPageJSON.objects ? currentPageJSON.objects.length : 0, 'objects', 'size:', currentPageJSON.width, 'x', currentPageJSON.height);
            } catch (e) {
                console.error('Error saving current page:', e);
            }
        }

        // Use project name if set, otherwise ask for it
        let name;
        if (projectName && projectName.trim() !== '') {
            // Use stored project name
            name = projectName.trim();
        } else {
            // If no project name set, ask for it
            openProjectNameModal();
            // Store the save function to retry after name is set
            window.pendingSave = function() {
                if (projectName && projectName.trim() !== '') {
                    saveDesign();
                }
            };
            return; // Exit and wait for user to set project name
        }

        // Prepare pages data - ensure all pages have valid data with width/height
        const pagesData = pages.map((page, index) => {
            if (!page.data || page.data.trim() === '') {
                console.warn('Page', index, 'has empty data, using default');
                return JSON.stringify({
                    version: '5.3.0',
                    objects: [],
                    background: editorSettings.backgroundColor,
                    backgroundColor: editorSettings.backgroundColor,
                    width: editorSettings.canvasWidth,
                    height: editorSettings.canvasHeight
                });
            }

            // Ensure width and height are present in page data
            try {
                const parsed = JSON.parse(page.data);
                if (!parsed.width) {
                    parsed.width = editorSettings.canvasWidth;
                }
                if (!parsed.height) {
                    parsed.height = editorSettings.canvasHeight;
                }
                if (!parsed.backgroundColor && parsed.background) {
                    parsed.backgroundColor = parsed.background;
                } else if (!parsed.backgroundColor && !parsed.background) {
                    parsed.backgroundColor = '#ffffff';
                    parsed.background = '#ffffff';
                }
                return JSON.stringify(parsed);
            } catch (e) {
                console.error('Error parsing page', index, 'data:', e);
                return page.data; // Return as-is if parsing fails
            }
        });

        console.log('Saving design with', pagesData.length, 'pages');
        pagesData.forEach((data, index) => {
            try {
                const parsed = JSON.parse(data);
                console.log('Page', index + 1, 'has', parsed.objects ? parsed.objects.length : 0, 'objects');
            } catch (e) {
                console.error('Page', index + 1, 'has invalid JSON:', e);
            }
        });

        // Create thumbnail from first page
        if (canvas) {
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
                    pages: pagesData,
                    thumbnail: dataURL,
                    design_id: currentDesignId, // Include design_id if updating
                    type: designType || '' // Store design type (letter, document, etc.)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store the design ID for future updates
                    currentDesignId = data.design_id;

                    // Update URL so refresh loads this design
                    try {
                        const url = new URL(window.location.href);
                        const currentLoad = url.searchParams.get('load');
                        if (currentLoad !== String(data.design_id)) {
                            url.searchParams.set('load', data.design_id);
                            window.history.replaceState({}, document.title, url.toString());
                        }
                    } catch (e) { console.warn('Could not update URL:', e); }

                    // Update button text
                    const saveBtn = document.getElementById('saveDesignBtn');
                    if (saveBtn) {
                        const span = saveBtn.querySelector('span');
                        if (span) {
                            span.textContent = 'Update';
                        }
                    }

                    showDesignToast(data.message || 'Design saved successfully!');
                    console.log('Design saved with ID:', data.design_id);

                    // Update project name if it was changed
                    if (data.design && data.design.name) {
                        projectName = data.design.name;
                    }
                } else {
                    alert('Failed to save design: ' + (data.message || 'Unknown error'));
                    console.error('Save failed:', data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving design: ' + error.message);
            });
        } else {
            alert('Canvas not initialized. Cannot save design.');
        }
    }

    function loadDesign(designId) {
        console.log('Loading design:', designId);

        if (!canvas) {
            console.error('Canvas not initialized, waiting...');
            setTimeout(() => loadDesign(designId), 100);
            return;
        }

        fetch('{{ route("design.load", ":id") }}'.replace(':id', designId), {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Design loaded:', data);

            if (data.success && data.design) {
                const design = data.design;
                console.log('Design data:', design);

                // Store the design ID for updates
                currentDesignId = design.id || designId;

                // Store the project name from loaded design
                if (design.name) {
                    projectName = design.name;
                }

                // Update button text to show "Update"
                const saveBtn = document.getElementById('saveDesignBtn');
                if (saveBtn) {
                    const span = saveBtn.querySelector('span');
                    if (span) {
                        span.textContent = 'Update';
                    }
                }

                // Check if it's a multi-page design
                if (design.is_multi_page && design.pages && Array.isArray(design.pages)) {
                    console.log('Loading multi-page design with', design.pages.length, 'pages');

                    // Load all pages
                    pages = [];
                    design.pages.forEach((pageData, index) => {
                        // Ensure pageData is a string (Fabric.js JSON format)
                        let parsed;
                        let pageDataString;
                        if (typeof pageData === 'string') {
                            // Try to parse and re-stringify to ensure valid JSON
                            try {
                                parsed = JSON.parse(pageData);
                            } catch (e) {
                                console.error('Error parsing page', index + 1, ':', e);
                                // If parsing fails, use as is
                                pageDataString = pageData;
                                parsed = null;
                            }
                        } else {
                            // Convert object to JSON
                            parsed = pageData;
                        }

                        if (parsed) {
                            // Ensure width and height are stored in the page data
                            // Use stored values, or extract from canvas if available, or use defaults
                            if (!parsed.width) {
                                parsed.width = 800; // Default width
                            }
                            if (!parsed.height) {
                                parsed.height = 1000; // Default height
                            }
                            // Ensure backgroundColor is set
                            if (!parsed.backgroundColor && parsed.background) {
                                parsed.backgroundColor = parsed.background;
                            } else if (!parsed.backgroundColor && !parsed.background) {
                                parsed.backgroundColor = '#ffffff';
                                parsed.background = '#ffffff';
                            }

                            pageDataString = JSON.stringify(parsed);
                            console.log('Page', index + 1, 'parsed successfully, objects:', parsed.objects ? parsed.objects.length : 0, 'size:', parsed.width, 'x', parsed.height);
                        }

                        const page = {
                            id: 'page_' + Date.now() + '_' + index,
                            index: index,
                            name: 'Document ' + (index + 1),
                            data: pageDataString
                        };
                        pages.push(page);
                    });

                    // Render pages list first
                    renderPagesList();

                    // Wait a bit to ensure canvas is ready, then switch to first page
                    setTimeout(() => {
                        if (pages.length > 0 && canvas) {
                            console.log('Switching to page 0, canvas ready:', !!canvas);
                            console.log('First page data length:', pages[0].data ? pages[0].data.length : 0);
                            switchToPage(0);
                        } else {
                            console.error('Cannot switch to page 0: pages.length =', pages.length, 'canvas =', !!canvas);
                        }
                    }, 200);
                } else {
                    console.log('Loading single-page design, converting to multi-page');

                    // Single page design - convert to multi-page
                    let designDataString;
                    let parsedDesignData;
                    if (design.design_data) {
                        if (typeof design.design_data === 'string') {
                            try {
                                parsedDesignData = JSON.parse(design.design_data);
                            } catch (e) {
                                console.error('Error parsing design_data:', e);
                                designDataString = design.design_data;
                                parsedDesignData = null;
                            }
                        } else {
                            parsedDesignData = design.design_data;
                        }
                    } else {
                        console.warn('No design_data found, creating empty page');
                        parsedDesignData = { version: '5.3.0', objects: [], background: '#ffffff' };
                    }

                    if (parsedDesignData) {
                        // Ensure width, height, and backgroundColor are set
                        if (!parsedDesignData.width) {
                            parsedDesignData.width = 800;
                        }
                        if (!parsedDesignData.height) {
                            parsedDesignData.height = 1000;
                        }
                        if (!parsedDesignData.backgroundColor && parsedDesignData.background) {
                            parsedDesignData.backgroundColor = parsedDesignData.background;
                        } else if (!parsedDesignData.backgroundColor && !parsedDesignData.background) {
                            parsedDesignData.backgroundColor = '#ffffff';
                            parsedDesignData.background = '#ffffff';
                        }

                        designDataString = JSON.stringify(parsedDesignData);
                        console.log('Design data parsed, objects:', parsedDesignData.objects ? parsedDesignData.objects.length : 0, 'size:', parsedDesignData.width, 'x', parsedDesignData.height);
                    }

                    const page = {
                        id: 'page_' + Date.now() + '_0',
                        index: 0,
                        name: 'Page 1',
                        data: designDataString
                    };
                    pages = [page];
                    renderPagesList();

                    // Wait a bit to ensure canvas is ready
                    setTimeout(() => {
                        if (canvas) {
                            console.log('Switching to page 0 (single page), canvas ready');
                            switchToPage(0);
                        } else {
                            console.error('Canvas not ready for single page load');
                            setTimeout(() => switchToPage(0), 100);
                        }
                    }, 200);
                }
            } else {
                console.error('Failed to load design:', data);
                alert('Failed to load design: ' + (data.message || 'Unknown error'));
                // Create first page as fallback
                addNewPage();
            }
        })
        .catch(error => {
            console.error('Error loading design:', error);
            alert('Error loading design: ' + error.message);
            // Create first page as fallback
            addNewPage();
        });
    }

    function exportDesignJSON() {
        if (!canvas) {
            alert('Canvas not initialized. Cannot export.');
            return;
        }
        // Save current page first
        if (pages.length > 0 && currentPageIndex >= 0) {
            try {
                const currentPageJSON = canvas.toJSON(['width', 'height', 'backgroundColor']);
                if (currentPageJSON.objects) {
                    currentPageJSON.objects = currentPageJSON.objects.filter(obj => obj.name !== 'alignmentGuide');
                }
                pages[currentPageIndex].data = JSON.stringify(currentPageJSON);
            } catch (e) {
                console.error('Error saving current page before export:', e);
            }
        }
        const exportData = {
            version: 1,
            exportedAt: new Date().toISOString(),
            designType: designType,
            projectName: projectName,
            pages: pages.map(p => ({
                id: p.id,
                index: p.index,
                name: p.name,
                data: typeof p.data === 'string' ? p.data : JSON.stringify(p.data)
            }))
        };
        const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = (projectName || 'design') + '-export.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function importDesignJSON() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.json,application/json';
        input.style.display = 'none';
        input.onchange = function(e) {
            const file = e.target.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(ev) {
                try {
                    const data = JSON.parse(ev.target?.result || '{}');
                    if (!data.pages || !Array.isArray(data.pages) || data.pages.length === 0) {
                        alert('Invalid design JSON: missing or empty pages array.');
                        return;
                    }
                    // Save current page before replacing
                    if (pages.length > 0 && currentPageIndex >= 0 && canvas) {
                        try {
                            const currentPageJSON = canvas.toJSON(['width', 'height', 'backgroundColor']);
                            if (currentPageJSON.objects) {
                                currentPageJSON.objects = currentPageJSON.objects.filter(obj => obj.name !== 'alignmentGuide');
                            }
                            pages[currentPageIndex].data = JSON.stringify(currentPageJSON);
                        } catch (err) {
                            console.error('Error saving current page before import:', err);
                        }
                    }
                    pages = [];
                    if (canvas) {
                        canvas.clear();
                        canvas.renderAll();
                    }
                    data.pages.forEach((pageData, index) => {
                        let pageDataString;
                        if (typeof pageData === 'string') {
                            pageDataString = pageData;
                        } else if (pageData && typeof pageData.data === 'string') {
                            pageDataString = pageData.data;
                        } else if (pageData && pageData.data) {
                            pageDataString = JSON.stringify(pageData.data);
                        } else {
                            pageDataString = JSON.stringify(pageData);
                        }
                        try {
                            const parsed = JSON.parse(pageDataString);
                            if (!parsed.width) parsed.width = 800;
                            if (!parsed.height) parsed.height = 1000;
                            if (!parsed.backgroundColor && parsed.background) {
                                parsed.backgroundColor = parsed.background;
                            } else if (!parsed.backgroundColor && !parsed.background) {
                                parsed.backgroundColor = '#ffffff';
                                parsed.background = '#ffffff';
                            }
                            pageDataString = JSON.stringify(parsed);
                        } catch (parseErr) {
                            console.error('Error normalizing page data:', parseErr);
                        }
                        const page = {
                            id: 'page_' + Date.now() + '_' + index,
                            index: index,
                            name: (pageData && pageData.name) || ('Document ' + (index + 1)),
                            data: pageDataString
                        };
                        pages.push(page);
                    });
                    if (data.projectName) projectName = data.projectName;
                    renderPagesList();
                    if (pages.length > 0) {
                        try {
                            const first = typeof pages[0].data === 'string' ? JSON.parse(pages[0].data) : pages[0].data;
                            const w = first.width || editorSettings.canvasWidth;
                            const h = first.height || editorSettings.canvasHeight;
                            const bg = first.backgroundColor || first.background || '#ffffff';
                            if (canvas) {
                                canvas.setWidth(w);
                                canvas.setHeight(h);
                                canvas.setBackgroundColor(bg, canvas.renderAll.bind(canvas));
                                canvas.calcOffset();
                            }
                        } catch (err) {
                            console.error('Error setting canvas from import:', err);
                        }
                        setTimeout(() => switchToPage(0), 100);
                        alert('Design imported successfully! (' + pages.length + ' page(s))');
                    } else {
                        alert('No valid pages in JSON.');
                    }
                } catch (err) {
                    console.error('Import error:', err);
                    alert('Error importing JSON: ' + (err.message || 'Invalid file format'));
                }
                input.remove();
            };
            reader.readAsText(file);
        };
        document.body.appendChild(input);
        input.value = '';
        input.click();
    }

    function exportAllPages() {
        if (!canExportWatermark) {
            alert('Export is only available for admin accounts.');
            return;
        }
        if (!canvas) {
            alert('Canvas not initialized. Cannot export pages.');
            return;
        }

        // Save current page first
        if (pages.length > 0 && currentPageIndex >= 0) {
            try {
                const currentPageJSON = canvas.toJSON(['width', 'height', 'backgroundColor']);
                // Filter out alignment guides
                if (currentPageJSON.objects) {
                    currentPageJSON.objects = currentPageJSON.objects.filter(obj => obj.name !== 'alignmentGuide');
                }
                pages[currentPageIndex].data = JSON.stringify(currentPageJSON);
            } catch (e) {
                console.error('Error saving current page before export:', e);
            }
        }

        if (pages.length === 0) {
            alert('No pages to export. Please add at least one page.');
            return;
        }

        // Show progress message
        const exportMessage = document.createElement('div');
        exportMessage.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 10000; text-align: center;';
        exportMessage.innerHTML = `<p>Exporting ${pages.length} page(s)...</p><p style="font-size: 0.875rem; color: #64748b;">Please wait...</p>`;
        document.body.appendChild(exportMessage);

        // Export each page with delay to avoid browser blocking multiple downloads
        pages.forEach((page, index) => {
            setTimeout(() => {
                try {
                    // Parse page data
                    let pageData;
                    if (typeof page.data === 'string') {
                        pageData = JSON.parse(page.data);
                    } else {
                        pageData = page.data;
                    }

                    // Get canvas dimensions from page data or use defaults
                    // Fabric.js stores dimensions in the JSON, but we need to check multiple places
                    let canvasWidth = 800;
                    let canvasHeight = 1000;

                    if (pageData.width && pageData.height) {
                        canvasWidth = pageData.width;
                        canvasHeight = pageData.height;
                    } else if (canvas) {
                        canvasWidth = canvas.getWidth();
                        canvasHeight = canvas.getHeight();
                    }

                    const backgroundColor = pageData.background || pageData.backgroundColor || '#ffffff';

                    // Create temporary canvas with proper dimensions
                    const tempCanvas = new fabric.Canvas(null, {
                        width: canvasWidth,
                        height: canvasHeight,
                        backgroundColor: backgroundColor
                    });

                    // Load objects from page data
            tempCanvas.loadFromJSON(page.data, function() {
                        // Set background color explicitly
                        if (backgroundColor) {
                            tempCanvas.setBackgroundColor(backgroundColor, function() {
                tempCanvas.renderAll();

                                // Export as image
                const dataURL = tempCanvas.toDataURL({
                    format: 'png',
                                    quality: 1.0,
                                    multiplier: 2 // Higher resolution
                });

                                // Create download link
                const link = document.createElement('a');
                link.download = `page-${index + 1}.png`;
                link.href = dataURL;

                                // Append to body, click, then remove
                                document.body.appendChild(link);
                link.click();
                                document.body.removeChild(link);

                                // Clean up canvas
                                tempCanvas.dispose();
                            });
                        } else {
                            tempCanvas.renderAll();

                            // Export as image
                            const dataURL = tempCanvas.toDataURL({
                                format: 'png',
                                quality: 1.0,
                                multiplier: 2 // Higher resolution
                            });

                            // Create download link
                            const link = document.createElement('a');
                            link.download = `page-${index + 1}.png`;
                            link.href = dataURL;

                            // Append to body, click, then remove
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);

                            // Clean up canvas
                            tempCanvas.dispose();
                        }
                    }, function(o, object) {
                        // Called for each object - return as-is
                        return object;
                    });
                } catch (e) {
                    console.error(`Error exporting page ${index + 1}:`, e);
                    alert(`Error exporting page ${index + 1}: ${e.message}`);
                }
            }, index * 500); // 500ms delay between each export to avoid browser blocking
        });

        // Show completion message and remove progress indicator
        setTimeout(() => {
            if (exportMessage && exportMessage.parentNode) {
                exportMessage.parentNode.removeChild(exportMessage);
            }
            alert(`Successfully exported ${pages.length} page(s) as PNG. Please check your downloads folder.`);
        }, (pages.length * 500) + 1000);
    }

    // Flipbook Save Functions
    function loadFlipbookDesign(designDataArray, flipbookId) {
        console.log('Loading flipbook design:', flipbookId, designDataArray);

        if (!canvas) {
            console.error('Canvas not initialized, waiting...');
            setTimeout(() => loadFlipbookDesign(designDataArray, flipbookId), 100);
            return;
        }

        // Set current flipbook ID for updating
        currentFlipbookId = flipbookId;

        // Clear current pages
        pages = [];

        // Load flipbook design pages
        if (designDataArray && Array.isArray(designDataArray)) {
            designDataArray.forEach((pageData, index) => {
                let pageDataString;
                if (typeof pageData === 'string') {
                    pageDataString = pageData;
                } else {
                    // Ensure proper structure
                    const parsed = pageData;
                    if (!parsed.width) parsed.width = 800;
                    if (!parsed.height) parsed.height = 1000;
                    if (!parsed.backgroundColor && parsed.background) {
                        parsed.backgroundColor = parsed.background;
                    } else if (!parsed.backgroundColor && !parsed.background) {
                        parsed.backgroundColor = '#ffffff';
                        parsed.background = '#ffffff';
                    }
                    // Filter out alignment guides
                    if (parsed.objects) {
                        parsed.objects = parsed.objects.filter(obj => obj.name !== 'alignmentGuide');
                    }
                    pageDataString = JSON.stringify(parsed);
                }

                const page = {
                    id: 'page_' + Date.now() + '_' + index,
                    index: index,
                    name: 'Document ' + (index + 1),
                    data: pageDataString
                };
                pages.push(page);
            });
        }

        // Update button text (handle both toolbar button and dropdown button)
        const btnText = document.getElementById('saveAsFlipbookBtnText');
        const btnTextDropdown = document.getElementById('saveAsFlipbookBtnTextDropdown');
        if (btnText) {
            btnText.textContent = 'Update Flipbook';
        }
        if (btnTextDropdown) {
            btnTextDropdown.textContent = 'Update Flipbook';
        }

        // Switch to first page
        if (pages.length > 0) {
            renderPagesList();
            setTimeout(() => {
                switchToPage(0);
            }, 200);
        } else {
            addNewPage();
        }
    }

    function saveAsFlipbook() {
        if (!canvas) {
            alert('Canvas not initialized. Cannot save as flipbook.');
            return;
        }

        // Save current page first
        if (pages.length > 0 && currentPageIndex >= 0) {
            try {
                const currentPageJSON = canvas.toJSON(['width', 'height', 'backgroundColor']);
                if (currentPageJSON.objects) {
                    currentPageJSON.objects = currentPageJSON.objects.filter(obj => obj.name !== 'alignmentGuide');
                }
                currentPageJSON.width = canvas.getWidth();
                currentPageJSON.height = canvas.getHeight();
                currentPageJSON.backgroundColor = canvas.backgroundColor || '#ffffff';
                pages[currentPageIndex].data = JSON.stringify(currentPageJSON);
            } catch (e) {
                console.error('Error saving current page before export:', e);
            }
        }

        if (pages.length === 0) {
            alert('No pages to export. Please add at least one page.');
            return;
        }

        // Update modal title and button text based on whether flipbook exists
        const modalTitle = document.getElementById('flipbookModalTitle');
        const submitBtn = document.getElementById('flipbookSubmitBtn');

        // Update toolbar button text if exists
        const btnText = document.getElementById('saveAsFlipbookBtnText');
        const btnTextDropdown = document.getElementById('saveAsFlipbookBtnTextDropdown');

        if (currentFlipbookId) {
            modalTitle.textContent = 'Update Flipbook';
            submitBtn.textContent = 'Update Flipbook';
            if (btnText) {
                btnText.textContent = 'Update Flipbook';
            }
            if (btnTextDropdown) {
                btnTextDropdown.textContent = 'Update Flipbook';
            }
        } else {
            modalTitle.textContent = 'Save as Flipbook';
            submitBtn.textContent = 'Save Flipbook';
            if (btnText) {
                btnText.textContent = 'Save as Flipbook';
            }
            if (btnTextDropdown) {
                btnTextDropdown.textContent = 'Save as Flipbook';
            }
        }

        // Update page count
        const pagesCountEl = document.getElementById('flipbookPagesCount');
        if (pagesCountEl) {
            const pageCount = pages.length;
            pagesCountEl.textContent = pageCount + ' page' + (pageCount !== 1 ? 's' : '');
        }

        // Pre-fill form when updating flipbook (don't ask for details again)
        let hasSavedPrintSettings = false;
        if (currentFlipbookId && existingFlipbookFormData) {
            const titleEl = document.getElementById('flipbookTitle');
            const descEl = document.getElementById('flipbookDescription');
            const statusEl = document.getElementById('flipbookStatus');
            const publicEl = document.getElementById('flipbookPublic');
            if (titleEl) titleEl.value = existingFlipbookFormData.title || '';
            if (descEl) descEl.value = existingFlipbookFormData.description || '';
            if (statusEl) statusEl.value = existingFlipbookFormData.status || 'draft';
            if (publicEl) publicEl.checked = existingFlipbookFormData.is_public || false;
            // Pre-fill print settings (if already saved)
            const psEl = id => document.getElementById(id);
            const ps = existingFlipbookFormData;
            hasSavedPrintSettings = !!(ps.print_sheet_type || ps.print_size || ps.print_quality || ps.binding_type);
            if (ps.print_sheet_type && psEl('flipbookPrintSheetType')) psEl('flipbookPrintSheetType').value = ps.print_sheet_type;
            if (ps.print_size && psEl('flipbookPrintSize')) psEl('flipbookPrintSize').value = ps.print_size;
            if (ps.print_custom_width != null && ps.print_custom_width !== '' && psEl('flipbookCustomWidth')) psEl('flipbookCustomWidth').value = ps.print_custom_width;
            if (ps.print_custom_height != null && ps.print_custom_height !== '' && psEl('flipbookCustomHeight')) psEl('flipbookCustomHeight').value = ps.print_custom_height;
            if (ps.print_quality && psEl('flipbookPrintQuality')) psEl('flipbookPrintQuality').value = ps.print_quality;
            if (ps.binding_type && psEl('flipbookBindingType')) psEl('flipbookBindingType').value = ps.binding_type;
            if (ps.bundle_quantity && psEl('bundleQuantity')) psEl('bundleQuantity').value = ps.bundle_quantity;
            if (ps.print_size === 'Custom' && psEl('flipbookCustomSizeContainer')) psEl('flipbookCustomSizeContainer').style.display = 'grid';
        }
        // Show "Saved print settings" badge when we have pre-filled print config (sheet type or other print options)
        const savedBadge = document.getElementById('flipbookSavedPrintSettingsBadge');
        if (savedBadge) savedBadge.style.display = (currentFlipbookId && hasSavedPrintSettings) ? 'block' : 'none';

        // Reset to first tab
        switchFlipbookTab('basic');

        // Initialize custom size container visibility
        const printSize = document.getElementById('flipbookPrintSize');
        const customSizeContainer = document.getElementById('flipbookCustomSizeContainer');
        if (printSize && customSizeContainer) {
            if (printSize.value === 'Custom') {
                customSizeContainer.style.display = 'grid';
            } else {
                customSizeContainer.style.display = 'none';
            }
        }

        // Calculate initial pricing
        calculateFlipbookPricing();

        // Update sheet type video/description when modal opens (if on print tab or when switching)
        playFlipbookSheetTypeVideo();

        // Show modal
        document.getElementById('flipbookModal').style.display = 'block';
    }

    function playFlipbookSheetTypeVideo() {
        const sheetTypeSelect = document.getElementById('flipbookPrintSheetType');
        const videoEl = document.getElementById('flipbookSheetTypeVideo');
        const placeholder = document.getElementById('flipbookSheetTypeVideoPlaceholder');
        const nameEl = document.getElementById('flipbookSheetTypeName');
        const descEl = document.getElementById('flipbookSheetTypeDescription');
        if (!sheetTypeSelect || !videoEl || !placeholder || !descEl) return;

        const slug = sheetTypeSelect.value;
        const data = sheetTypesData && sheetTypesData[slug] ? sheetTypesData[slug] : null;
        const videoUrl = data && data.video_url ? data.video_url : null;
        const name = data && data.name ? data.name : (sheetTypeSelect.options[sheetTypeSelect.selectedIndex]?.text || '—');
        const description = data && data.description ? data.description : '';

        if (videoUrl) {
            videoEl.src = videoUrl;
            videoEl.style.display = 'block';
            placeholder.style.display = 'none';
            videoEl.muted = true;
            videoEl.loop = true;
            videoEl.play().catch(() => {});
        } else {
            videoEl.src = '';
            videoEl.style.display = 'none';
            placeholder.style.display = 'flex';
        }
        if (nameEl) nameEl.textContent = name;
        descEl.textContent = description || (slug ? 'No description available for this sheet type.' : 'Select a sheet type to view its description and video preview.');
    }

    // Flipbook Modal Tab Navigation
    let currentFlipbookTab = 'basic';
    const flipbookTabs = ['basic', 'print'];

    function switchFlipbookTab(tabName) {
        currentFlipbookTab = tabName;

        const tabIndex = flipbookTabs.indexOf(tabName);

        // Update tab content
        flipbookTabs.forEach((tab, index) => {
            const tabContent = document.getElementById('flipbookTab' + tab.charAt(0).toUpperCase() + tab.slice(1) + 'Content');

            if (tabContent) {
                if (tab === tabName) {
                    tabContent.classList.add('active');
                    tabContent.style.display = 'grid';
                    if (tabName === 'print') playFlipbookSheetTypeVideo();
                } else {
                    tabContent.classList.remove('active');
                    tabContent.style.display = 'none';
                }
            }
        });

        // Update wizard step indicators
        for (let i = 1; i <= 2; i++) {
            const stepCircle = document.querySelector('#wizardStep' + i + ' .wizard-step-circle');
            const stepLabel = document.querySelector('#wizardStep' + i + ' .wizard-step-label');
            const stepNumber = document.querySelector('#wizardStep' + i + ' .wizard-step-number');
            const stepCheck = document.querySelector('#wizardStep' + i + ' .wizard-step-check');

            if (stepCircle && stepLabel && stepNumber && stepCheck) {
                if (i === tabIndex + 1) {
                    // Current step
                    stepCircle.classList.add('active');
                    stepCircle.classList.remove('completed');
                    stepLabel.classList.add('active');
                    stepLabel.classList.remove('completed');
                    stepNumber.style.display = 'block';
                    stepCheck.style.display = 'none';
                } else if (i < tabIndex + 1) {
                    // Completed step
                    stepCircle.classList.remove('active');
                    stepCircle.classList.add('completed');
                    stepLabel.classList.remove('active');
                    stepLabel.classList.add('completed');
                    stepNumber.style.display = 'none';
                    stepCheck.style.display = 'block';
                } else {
                    // Future step
                    stepCircle.classList.remove('active', 'completed');
                    stepLabel.classList.remove('active', 'completed');
                    stepNumber.style.display = 'block';
                    stepCheck.style.display = 'none';
                }
            }
        }

        // Update progress bar
        const progressBar = document.getElementById('flipbookWizardProgress');
        if (progressBar) {
            const progress = (tabIndex / (flipbookTabs.length - 1)) * 100;
            progressBar.style.width = progress + '%';
        }

        // Update navigation buttons
        updateFlipbookTabNavigation();
    }

    function updateFlipbookTabNavigation() {
        const prevBtn = document.getElementById('flipbookTabPrevBtn');
        const nextBtn = document.getElementById('flipbookTabNextBtn');
        const submitBtn = document.getElementById('flipbookSubmitBtn');

        const currentIndex = flipbookTabs.indexOf(currentFlipbookTab);

        // Show/hide Previous button
        if (prevBtn) {
            if (currentIndex > 0) {
                prevBtn.style.display = 'flex';
            } else {
                prevBtn.style.display = 'none';
            }
        }

        // Show/hide Next and Submit buttons
        if (nextBtn && submitBtn) {
            if (currentIndex < flipbookTabs.length - 1) {
                nextBtn.style.display = 'flex';
                submitBtn.style.display = 'none';
            } else {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'block';
            }
        }
    }

    function flipbookTabNext() {
        const currentIndex = flipbookTabs.indexOf(currentFlipbookTab);
        if (currentIndex < flipbookTabs.length - 1) {
            // Validate current tab before moving to next
            if (validateFlipbookTab(currentFlipbookTab)) {
                switchFlipbookTab(flipbookTabs[currentIndex + 1]);
            }
        }
    }

    function flipbookTabPrevious() {
        const currentIndex = flipbookTabs.indexOf(currentFlipbookTab);
        if (currentIndex > 0) {
            switchFlipbookTab(flipbookTabs[currentIndex - 1]);
        }
    }

    function validateFlipbookTab(tabName) {
        if (tabName === 'basic') {
            const title = document.getElementById('flipbookTitle');
            if (title && !title.value.trim()) {
                alert('Please enter a flipbook name.');
                title.focus();
                return false;
            }
        } else if (tabName === 'print') {
            const sheetType = document.getElementById('flipbookPrintSheetType');
            const printSize = document.getElementById('flipbookPrintSize');

            if (sheetType && !sheetType.value) {
                alert('Please select a print sheet type.');
                sheetType.focus();
                return false;
            }

            if (printSize && !printSize.value) {
                alert('Please select a print size.');
                printSize.focus();
                return false;
            }

            // Validate custom size if selected
            if (printSize && printSize.value === 'Custom') {
                const customWidth = document.getElementById('flipbookCustomWidth');
                const customHeight = document.getElementById('flipbookCustomHeight');

                if (!customWidth || !customWidth.value || customWidth.value < 50 || customWidth.value > 1000) {
                    alert('Please enter a valid custom width (50-1000 mm).');
                    if (customWidth) customWidth.focus();
                    return false;
                }

                if (!customHeight || !customHeight.value || customHeight.value < 50 || customHeight.value > 1000) {
                    alert('Please enter a valid custom height (50-1000 mm).');
                    if (customHeight) customHeight.focus();
                    return false;
                }
            }
        }

        return true;
    }

    function closeFlipbookModal() {
        document.getElementById('flipbookModal').style.display = 'none';
        document.getElementById('flipbookForm').reset();
        // Reset to first tab
        switchFlipbookTab('basic');
        document.getElementById('flipbookProgress').style.display = 'none';
        document.getElementById('flipbookProgressBar').style.width = '0%';
        document.getElementById('flipbookSubmitBtn').disabled = false;
    }

    // Template Management Functions
    function saveAsTemplate() {
        if (!canvas) {
            alert('Canvas not initialized. Cannot save as template.');
            return;
        }

        // Save current page first
        if (pages.length > 0 && currentPageIndex >= 0) {
            try {
                const currentPageJSON = canvas.toJSON(['width', 'height', 'backgroundColor']);
                if (currentPageJSON.objects) {
                    currentPageJSON.objects = currentPageJSON.objects.filter(obj => obj.name !== 'alignmentGuide');
                }
                currentPageJSON.width = canvas.getWidth();
                currentPageJSON.height = canvas.getHeight();
                currentPageJSON.backgroundColor = canvas.backgroundColor || '#ffffff';
                pages[currentPageIndex].data = JSON.stringify(currentPageJSON);
            } catch (e) {
                console.error('Error saving current page before template save:', e);
            }
        }

        if (pages.length === 0) {
            alert('No pages to save as template. Please add at least one page.');
            return;
        }

        // Extract and display variables
        const variables = extractVariablesFromPages(pages);
        displayTemplateVariables(variables);

        // Populate thumbnail page selector
        const thumbSelect = document.getElementById('templateThumbnailPage');
        if (thumbSelect) {
            thumbSelect.innerHTML = '';
            pages.forEach((p, i) => {
                const opt = document.createElement('option');
                opt.value = String(i + 1);
                opt.textContent = 'Page ' + (i + 1);
                thumbSelect.appendChild(opt);
            });
        }

        // Reset advanced section
        document.getElementById('templateAdvancedContent').style.display = 'none';
        document.getElementById('templateAdvancedIcon').style.transform = '';

        // Load licenses and categories dynamically when modal opens
        loadTemplateLicenses();
        loadTemplateCategories();

        // Set template type from current design type
        const typeSelect = document.getElementById('templateTypeSelect');
        if (typeSelect && designType) {
            const opt = typeSelect.querySelector('option[value="' + designType + '"]');
            if (opt) typeSelect.value = designType;
        }
        toggleTemplateTypeOptions();

        // Show template modal
        document.getElementById('templateModal').style.display = 'block';
    }

    function toggleTemplateAsProduct() {
        const isProduct = document.getElementById('templateIsProduct').checked;
        document.getElementById('templateProductFields').style.display = isProduct ? 'block' : 'none';

        // When Template as Product is on: hide and disable public template Price and Licence (use product section instead)
        const priceWrap = document.getElementById('publicTemplatePriceWrap');
        const licenceWrap = document.getElementById('publicTemplateLicenceWrap');
        const priceEl = document.getElementById('templatePrice');
        const licenceEl = document.getElementById('templateLicence');
        if (priceWrap) priceWrap.style.display = isProduct ? 'none' : '';
        if (licenceWrap) licenceWrap.style.display = isProduct ? 'none' : '';
        if (priceEl) {
            priceEl.disabled = isProduct;
            priceEl.required = !isProduct && document.getElementById('templateIsPublic').checked;
        }
        if (licenceEl) {
            licenceEl.disabled = isProduct;
            licenceEl.required = !isProduct && document.getElementById('templateIsPublic').checked;
        }

        if (!isProduct) {
            document.getElementById('templateStockEnabled').checked = false;
            document.getElementById('templateStockQtyWrap').style.display = 'none';
        } else {
            toggleTemplateStock();
        }
    }

    function toggleTemplateStock() {
        const enabled = document.getElementById('templateStockEnabled').checked;
        document.getElementById('templateStockQtyWrap').style.display = enabled ? 'block' : 'none';
    }

    function toggleTemplateTypeOptions() {
        const type = (document.getElementById('templateTypeSelect')?.value || '').toLowerCase();
        const envelopeWrap = document.getElementById('templateEnvelopeOptionWrap');
        if (envelopeWrap) {
            envelopeWrap.style.display = type === 'letter' ? 'flex' : 'none';
            if (type !== 'letter') document.getElementById('templateDisableEnvelopeOption').checked = false;
        }
    }

    function toggleTemplateAdvanced() {
        const content = document.getElementById('templateAdvancedContent');
        const icon = document.getElementById('templateAdvancedIcon');
        if (content.style.display === 'none') {
            content.style.display = 'block';
            icon.style.transform = 'rotate(180deg)';
        } else {
            content.style.display = 'none';
            icon.style.transform = '';
        }
    }

    function loadTemplateLicenses() {
        const select = document.getElementById('templateLicence');
        const productLicenceSelect = document.getElementById('templateProductLicence');
        const selects = [select, productLicenceSelect].filter(Boolean);
        if (selects.length === 0) return;
        selects.forEach(s => { s.innerHTML = '<option value="">Loading...</option>'; });
        fetch('{{ route("design.templates.licenses") }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }
        })
        .then(r => r.json())
        .then(data => {
            selects.forEach(s => {
                s.innerHTML = '<option value="">Select Licence</option>';
                if (data.success && data.licenses && data.licenses.length) {
                    data.licenses.forEach(lic => {
                        const opt = document.createElement('option');
                        opt.value = lic.slug;
                        opt.textContent = lic.name;
                        s.appendChild(opt);
                    });
                }
            });
        })
        .catch(() => {
            selects.forEach(s => { s.innerHTML = '<option value="">Select Licence</option>'; });
        });
    }

    function loadTemplateCategories() {
        const select = document.getElementById('templateCategory');
        if (!select) return;
        select.innerHTML = '<option value="">Loading...</option>';
        fetch('{{ route("design.templates.categories") }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }
        })
        .then(r => r.json())
        .then(data => {
            select.innerHTML = '<option value="">Select Category</option>';
            if (data.success && data.categories && data.categories.length) {
                data.categories.forEach(cat => {
                    const opt = document.createElement('option');
                    opt.value = cat.value;
                    opt.textContent = cat.label;
                    select.appendChild(opt);
                });
            }
        })
        .catch(() => {
            select.innerHTML = '<option value="">Select Category</option>';
        });
    }

    async function generateTemplateDescriptions() {
        const name = document.getElementById('templateName')?.value?.trim();
        if (!name) {
            alert('Please enter a template name first.');
            return;
        }
        const typeSelect = document.getElementById('templateTypeSelect');
        const type = typeSelect ? typeSelect.value : (typeof designType !== 'undefined' ? designType : '');
        const categorySelect = document.getElementById('templateCategory');
        const category = categorySelect ? categorySelect.value : '';
        const pageCount = Array.isArray(pages) ? pages.length : 1;

        const btn = document.getElementById('generateTemplateDescriptionsBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
        }
        try {
            const response = await fetch('{{ route("design.templates.generateDescriptions") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: name, type: type || null, category: category || null, page_count: pageCount })
            });
            const data = await response.json();
            if (data.success) {
                const shortEl = document.getElementById('templateShortDescription');
                const fullEl = document.getElementById('templateDescription');
                if (shortEl) {
                    shortEl.value = data.short_description || '';
                    updateShortDescCount();
                }
                if (fullEl) fullEl.value = data.description || '';
            } else {
                alert(data.message || 'Failed to generate descriptions.');
            }
        } catch (err) {
            console.error('Generate template descriptions:', err);
            alert('Failed to generate descriptions. Please try again.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-magic me-1"></i>Generate descriptions';
            }
        }
    }

    function displayTemplateVariables(variables) {
        const variablesList = document.getElementById('templateVariablesList');
        if (!variablesList) return;

        if (!variables || variables.length === 0) {
            const varExample = '{' + '{' + 'variable_name' + '}' + '}';
            variablesList.innerHTML = `
                <div style="padding: 0.6rem; background: white; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.65rem; color: #64748b; margin-bottom: 0.25rem;">No variables detected</div>
                    <div style="font-size: 0.7rem; color: #94a3b8;">Use ${varExample} format in text objects to create variables.</div>
                </div>
            `;
            return;
        }

        let html = '';
        variables.forEach((variable, index) => {
            const pagesText = variable.pages.length === 1
                ? `Page ${variable.pages[0]}`
                : `Pages ${variable.pages.join(', ')}`;
            const varDisplay = '{' + '{' + variable.name + '}' + '}';
            const varId = `var_${index}_${variable.name.replace(/[^a-zA-Z0-9]/g, '_')}`;

            html += `
                <div style="padding: 0.6rem; background: white; border-radius: 6px; border: 1px solid #e2e8f0; border-left: 3px solid #6366f1; margin-bottom: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                        <div>
                            <div style="font-weight: 600; color: #1e293b; font-size: 0.75rem; margin-bottom: 0.15rem;">${varDisplay}</div>
                            <div style="font-size: 0.65rem; color: #64748b;">
                                <i class="fas fa-file-alt" style="margin-right: 0.2rem;"></i>${pagesText} • <span style="background: #f1f5f9; padding: 0.1rem 0.25rem; border-radius: 3px;">${variable.count}x</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div>
                            <label style="font-size: 0.65rem; font-weight: 600; color: #475569; margin-bottom: 0.15rem; display: block;">Max Length</label>
                            <input type="number" id="${varId}_length" class="var-config-input" min="1" value="255" style="width: 100%; padding: 0.35rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 5px; font-size: 0.75rem;">
                        </div>

                        <div>
                            <label style="font-size: 0.65rem; font-weight: 600; color: #475569; margin-bottom: 0.15rem; display: block;">Form Type</label>
                            <select id="${varId}_type" class="var-config-select" onchange="toggleSelectOptions('${varId}')" style="width: 100%; padding: 0.35rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 5px; font-size: 0.75rem;">
                                <option value="textbox">Textbox</option>
                                <option value="textarea">Textarea</option>
                                <option value="select">Select Option</option>
                            </select>
                        </div>

                        <div id="${varId}_options_container" style="display: none;">
                            <label style="font-size: 0.65rem; font-weight: 600; color: #475569; margin-bottom: 0.15rem; display: block;">Select Options</label>
                            <div id="${varId}_options_list" style="margin-bottom: 0.35rem; display: flex; flex-direction: column; gap: 0.35rem;">
                                <div style="display: flex; gap: 0.35rem; align-items: center;">
                                    <input type="text" class="var-option-input" placeholder="Option 1" style="flex: 1; padding: 0.35rem 0.5rem; border: 1px solid #e2e8f0; border-radius: 5px; font-size: 0.75rem;">
                                    <button type="button" onclick="removeOption(this)" style="padding: 0.35rem 0.5rem; background: #ef4444; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 0.65rem;" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" onclick="addOption('${varId}')" style="width: 100%; padding: 0.35rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 5px; cursor: pointer; font-size: 0.75rem; font-weight: 500;">
                                <i class="fas fa-plus me-1"></i>Add Option
                            </button>
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <input type="checkbox" id="${varId}_required" checked style="width: 14px; height: 14px; cursor: pointer;">
                            <label for="${varId}_required" style="font-size: 0.7rem; color: #475569; cursor: pointer; margin: 0;">Required</label>
                        </div>
                    </div>
                </div>
            `;
        });
        variablesList.innerHTML = html;
    }

    function toggleSelectOptions(varId) {
        const select = document.getElementById(varId + '_type');
        const optionsContainer = document.getElementById(varId + '_options_container');
        if (select && optionsContainer) {
            if (select.value === 'select') {
                optionsContainer.style.display = 'block';
            } else {
                optionsContainer.style.display = 'none';
            }
        }
    }

    function addOption(varId) {
        const optionsList = document.getElementById(varId + '_options_list');
        if (!optionsList) return;
        const optionCount = optionsList.children.length + 1;
        const optionDiv = document.createElement('div');
        optionDiv.style.cssText = 'display: flex; gap: 0.5rem; align-items: center;';
        optionDiv.innerHTML = `
            <input type="text" class="var-option-input" placeholder="Option ${optionCount}" style="flex: 1; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.8125rem;">
            <button type="button" onclick="removeOption(this)" style="padding: 0.5rem 0.75rem; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem;" title="Remove">
                <i class="fas fa-times"></i>
            </button>
        `;
        optionsList.appendChild(optionDiv);
    }

    function removeOption(button) {
        const optionsList = button.closest('div').parentElement;
        if (optionsList && optionsList.children.length > 1) {
            button.closest('div').remove();
        } else {
            alert('At least one option is required for select fields.');
        }
    }

    function collectVariableConfigurations(variables) {
        const configurations = [];
        variables.forEach((variable, index) => {
            const varId = `var_${index}_${variable.name.replace(/[^a-zA-Z0-9]/g, '_')}`;
            const lengthEl = document.getElementById(varId + '_length');
            const typeEl = document.getElementById(varId + '_type');
            const requiredEl = document.getElementById(varId + '_required');

            const length = lengthEl ? parseInt(lengthEl.value) || 255 : 255;
            const formType = typeEl ? typeEl.value : 'textbox';
            const required = requiredEl ? requiredEl.checked : false;

            let options = [];
            if (formType === 'select') {
                const optionsContainer = document.getElementById(varId + '_options_list');
                if (optionsContainer) {
                    const optionInputs = optionsContainer.querySelectorAll('.var-option-input');
                    optionInputs.forEach(input => {
                        if (input.value.trim()) {
                            options.push(input.value.trim());
                        }
                    });
                }
            }

            configurations.push({
                name: variable.name,
                count: variable.count,
                pages: variable.pages,
                length: length,
                form_type: formType,
                options: options,
                required: required
            });
        });
        return configurations;
    }

    // Template images array
    let templateImages = [];

    function toggleTemplateType() {
        const isPublic = document.getElementById('templateIsPublic').checked;
        const isProduct = document.getElementById('templateIsProduct').checked;
        const publicFields = document.getElementById('publicTemplateFields');
        const typeLabel = document.getElementById('templateTypeLabel');
        const typeText = document.getElementById('templateTypeText');
        const asProductWrap = document.getElementById('templateAsProductWrap');

        if (isPublic) {
            publicFields.style.display = 'block';
            typeLabel.style.background = '#eef2ff';
            typeLabel.style.border = '1px solid #6366f1';
            typeText.innerHTML = '<i class="fas fa-globe" style="margin-right: 0.5rem; color: #6366f1;"></i>Public Template';

            document.getElementById('templateShortDescription').required = true;
            document.getElementById('templateCategory').required = true;
            document.getElementById('templatePrice').required = !isProduct;
            document.getElementById('templateLicence').required = !isProduct;

            if (asProductWrap) asProductWrap.style.display = 'block';
        } else {
            publicFields.style.display = 'none';
            typeLabel.style.background = 'white';
            typeLabel.style.border = 'none';
            typeText.innerHTML = '<i class="fas fa-lock" style="margin-right: 0.5rem; color: #94a3b8;"></i>My Template (Private)';

            document.getElementById('templateShortDescription').required = false;
            document.getElementById('templateCategory').required = false;
            document.getElementById('templatePrice').required = false;
            document.getElementById('templateLicence').required = false;

            if (asProductWrap) asProductWrap.style.display = 'none';
            document.getElementById('templateIsProduct').checked = false;
            document.getElementById('templateProductFields').style.display = 'none';
            document.getElementById('templateStockEnabled').checked = false;
            const qtyWrap = document.getElementById('templateStockQtyWrap');
            if (qtyWrap) qtyWrap.style.display = 'none';
        }
    }

    function handleTemplateImagesUpload(event) {
        const files = Array.from(event.target.files);
        const previewContainer = document.getElementById('templateImagesPreview');

        files.forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageData = {
                        file: file,
                        dataUrl: e.target.result
                    };
                    templateImages.push(imageData);
                    updateTemplateImagesPreview();
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function updateTemplateImagesPreview() {
        const previewContainer = document.getElementById('templateImagesPreview');
        previewContainer.innerHTML = '';

        templateImages.forEach((imageData, index) => {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'template-image-preview';
            previewDiv.innerHTML = `
                <img src="${imageData.dataUrl}" alt="Template preview ${index + 1}">
                <button type="button" class="remove-image-btn" onclick="removeTemplateImage(${index})" title="Remove image">
                    <i class="fas fa-times"></i>
                </button>
            `;
            previewContainer.appendChild(previewDiv);
        });
    }

    function removeTemplateImage(index) {
        templateImages.splice(index, 1);
        updateTemplateImagesPreview();
    }

    function closeTemplateModal() {
        document.getElementById('templateModal').style.display = 'none';
        document.getElementById('templateForm').reset();
        document.getElementById('templateIsPublic').checked = false;
        const asProductWrapEl = document.getElementById('templateAsProductWrap');
        if (asProductWrapEl) asProductWrapEl.style.display = 'none';
        document.getElementById('publicTemplateFields').style.display = 'none';
        const typeSelect = document.getElementById('templateTypeSelect');
        if (typeSelect && designType) {
            const opt = typeSelect.querySelector('option[value="' + designType + '"]');
            typeSelect.value = opt ? designType : 'document';
        }
        document.getElementById('templateIsProduct').checked = false;
        document.getElementById('templateProductFields').style.display = 'none';
        document.getElementById('templateStockEnabled').checked = false;
        document.getElementById('templateStockQtyWrap').style.display = 'none';
        const productLicenceEl = document.getElementById('templateProductLicence');
        if (productLicenceEl) productLicenceEl.value = '';
        document.getElementById('templateDisableSheetSelection').checked = false;
        document.getElementById('templateDisableMaterialSelection').checked = false;
        document.getElementById('templateDisableEnvelopeOption').checked = false;
        const featuredEl = document.getElementById('templateIsFeatured');
        if (featuredEl) featuredEl.checked = false;
        if (typeof templateTagsList !== 'undefined') {
            templateTagsList.length = 0;
            renderTemplateTagsChips();
        }
        const tagsEl = document.getElementById('templateTags');
        if (tagsEl) tagsEl.value = '';
        const tagsInputEl = document.getElementById('templateTagsInput');
        if (tagsInputEl) tagsInputEl.value = '';
        toggleTemplateType();
        toggleTemplateTypeOptions();
        templateImages = [];
        document.getElementById('templateImagesPreview').innerHTML = '';
        document.getElementById('templateSubmitBtn').disabled = false;
        document.getElementById('templateSubmitBtn').textContent = 'Save Template';
        const shortDescEl = document.getElementById('shortDescCount');
        if (shortDescEl) shortDescEl.textContent = '0/200 characters';
        document.getElementById('templateAdvancedContent').style.display = 'none';
        document.getElementById('templateAdvancedIcon').style.transform = '';
    }

    function updateShortDescCount() {
        const textarea = document.getElementById('templateShortDescription');
        const count = document.getElementById('shortDescCount');
        if (textarea && count) {
            const length = textarea.value.length;
            count.textContent = `${length}/200 characters`;
            if (length > 200) {
                count.style.color = '#ef4444';
            } else {
                count.style.color = '#64748b';
            }
        }
    }

    // Template tags as chips (comma or Enter adds tag)
    let templateTagsList = [];
    function renderTemplateTagsChips() {
        const container = document.getElementById('templateTagsChips');
        const hidden = document.getElementById('templateTags');
        if (!container || !hidden) return;
        container.innerHTML = '';
        templateTagsList.forEach((tag, index) => {
            const span = document.createElement('span');
            span.style.cssText = 'display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.2rem 0.5rem; background: #e0e7ff; color: #4338ca; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;';
            span.textContent = tag;
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.setAttribute('aria-label', 'Remove tag');
            removeBtn.innerHTML = '&times;';
            removeBtn.style.cssText = 'background: none; border: none; cursor: pointer; padding: 0; margin-left: 0.15rem; font-size: 1rem; line-height: 1; color: #4338ca; opacity: 0.8;';
            removeBtn.onclick = function() { removeTemplateTag(index); };
            span.appendChild(removeBtn);
            container.appendChild(span);
        });
        hidden.value = templateTagsList.join(',');
    }
    function addTemplateTag(tag) {
        const t = (tag || '').trim().replace(/,/g, '');
        if (!t || templateTagsList.indexOf(t) !== -1) return;
        if (t.length > 50) return;
        templateTagsList.push(t);
        renderTemplateTagsChips();
    }
    function removeTemplateTag(index) {
        templateTagsList.splice(index, 1);
        renderTemplateTagsChips();
    }
    function initTemplateTagsInput() {
        const input = document.getElementById('templateTagsInput');
        const hidden = document.getElementById('templateTags');
        if (!input || !hidden) return;
        input.addEventListener('keydown', function(e) {
            if (e.key === ',' || e.key === 'Enter') {
                e.preventDefault();
                addTemplateTag(input.value);
                input.value = '';
            } else if (e.key === 'Backspace' && !input.value && templateTagsList.length > 0) {
                removeTemplateTag(templateTagsList.length - 1);
            }
        });
        input.addEventListener('paste', function(e) {
            setTimeout(function() {
                const val = input.value;
                if (val.indexOf(',') !== -1) {
                    const parts = val.split(',').map(function(p) { return p.trim(); }).filter(Boolean);
                    input.value = '';
                    parts.forEach(function(p) { addTemplateTag(p); });
                }
            }, 0);
        });
        input.addEventListener('input', function() {
            const val = this.value;
            if (val.indexOf(',') !== -1) {
                const parts = val.split(',').map(function(p) { return p.trim(); }).filter(Boolean);
                this.value = parts.pop() || '';
                parts.forEach(function(p) { addTemplateTag(p); });
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTemplateTagsInput);
    } else {
        initTemplateTagsInput();
    }

    function submitTemplate(event) {
        event.preventDefault();

        const isPublic = document.getElementById('templateIsPublic').checked;
        const name = document.getElementById('templateName').value.trim();

        // Validation - Name is always required
        if (!name) {
            alert('Please enter a template name.');
            return;
        }

        // Validation for public templates
        if (isPublic) {
            const shortDescription = document.getElementById('templateShortDescription').value.trim();
            const category = document.getElementById('templateCategory').value;
            const price = document.getElementById('templatePrice').value;
            const licence = document.getElementById('templateLicence').value;

            if (!shortDescription) {
                alert('Please enter a short description.');
                return;
            }
            if (!category) {
                alert('Please select a category.');
                return;
            }
            if (!price || parseFloat(price) < 0) {
                alert('Please enter a valid price (0 or greater).');
                return;
            }
            if (!licence) {
                alert('Please select a licence.');
                return;
            }
        }

        // Validation for template as product
        const isProduct = document.getElementById('templateIsProduct').checked;
        if (isProduct) {
            const stockEnabled = document.getElementById('templateStockEnabled').checked;
            if (stockEnabled) {
                const qty = document.getElementById('templateStockQty').value;
                if (qty === '' || parseInt(qty, 10) < 0) {
                    alert('Please enter a valid stock quantity.');
                    return;
                }
            }
            const sellingPrice = document.getElementById('templateSellingPrice').value;
            if (!sellingPrice || parseFloat(sellingPrice) < 0) {
                alert('Please enter a valid price (0 or greater) for the product.');
                return;
            }
            const productLicence = document.getElementById('templateProductLicence')?.value?.trim();
            if (!productLicence) {
                alert('Please select a licence for the product.');
                return;
            }
        }

        // Save current page first
        if (pages.length > 0 && currentPageIndex >= 0 && canvas) {
            try {
                const currentPageJSON = canvas.toJSON(['width', 'height', 'backgroundColor']);
                if (currentPageJSON.objects) {
                    currentPageJSON.objects = currentPageJSON.objects.filter(obj => obj.name !== 'alignmentGuide');
                }
                currentPageJSON.width = canvas.getWidth();
                currentPageJSON.height = canvas.getHeight();
                currentPageJSON.backgroundColor = canvas.backgroundColor || '#ffffff';
                pages[currentPageIndex].data = JSON.stringify(currentPageJSON);
            } catch (e) {
                console.error('Error saving current page:', e);
            }
        }

        // Prepare template data
        const templatePages = pages.map(page => page.data);

        // Extract variables from all pages
        const rawVariables = extractVariablesFromPages(pages);

        // Collect variable configurations from the form
        const variableConfigurations = collectVariableConfigurations(rawVariables);

        // Extract custom fonts from pages
        const templateFonts = extractFontsFromPages(pages);

        // Validate select options
        for (let i = 0; i < variableConfigurations.length; i++) {
            const config = variableConfigurations[i];
            if (config.form_type === 'select' && (!config.options || config.options.length === 0)) {
                alert(`Variable "${config.name}" is set as "Select Option" but has no options. Please add at least one option.`);
                document.getElementById('templateSubmitBtn').disabled = false;
                document.getElementById('templateSubmitBtn').textContent = 'Save Template';
                return;
            }
        }

        // Disable submit button
        document.getElementById('templateSubmitBtn').disabled = true;
        document.getElementById('templateSubmitBtn').textContent = 'Creating thumbnail...';

        const templateTypeSelect = document.getElementById('templateTypeSelect');
        const templateType = templateTypeSelect ? templateTypeSelect.value : (designType || '');
        const stockEnabled = document.getElementById('templateStockEnabled').checked;
        const stockQtyEl = document.getElementById('templateStockQty');
        const sellingPriceEl = document.getElementById('templateSellingPrice');
        const costEl = document.getElementById('templateCost');
        const productDescEl = document.getElementById('templateProductDescription');

        // Build all form data so every field is stored in the template table
        const shortDescEl = document.getElementById('templateShortDescription');
        const descEl = document.getElementById('templateDescription');
        const categoryEl = document.getElementById('templateCategory');
        const priceEl = document.getElementById('templatePrice');
        const licenceEl = document.getElementById('templateLicence');
        const featuredEl = document.getElementById('templateIsFeatured');
        const tagsVal = document.getElementById('templateTags')?.value?.trim();

        const templateData = {
            name: name,
            is_public: isPublic,
            pages: templatePages,
            variables: variableConfigurations,
            fonts: templateFonts,
            type: templateType || null,
            is_product: isProduct,
            stock_enabled: isProduct && stockEnabled,
            stock_qty: (isProduct && stockEnabled && stockQtyEl?.value) ? parseInt(stockQtyEl.value, 10) : null,
            selling_price: (isProduct && sellingPriceEl?.value) ? parseFloat(sellingPriceEl.value) : null,
            cost: (isProduct && costEl?.value) ? parseFloat(costEl.value) : null,
            product_description: (isProduct && productDescEl?.value) ? productDescEl.value.trim() : null,
            price: isPublic && priceEl?.value ? parseFloat(priceEl.value) : (isProduct && sellingPriceEl?.value ? parseFloat(sellingPriceEl.value) : null),
            licence: isPublic && licenceEl?.value ? licenceEl.value : (isProduct && document.getElementById('templateProductLicence')?.value ? document.getElementById('templateProductLicence').value : null),
            short_description: shortDescEl ? shortDescEl.value.trim() : null,
            description: descEl ? descEl.value.trim() : null,
            category: categoryEl ? categoryEl.value : null,
            is_featured: featuredEl ? featuredEl.checked : false,
            images: isPublic && templateImages.length > 0 ? templateImages.map(img => img.dataUrl) : null,
            tags: tagsVal ? tagsVal.split(',').map(t => t.trim()).filter(Boolean) : [],
            disable_sheet_selection: document.getElementById('templateDisableSheetSelection')?.checked ?? false,
            disable_material_selection: document.getElementById('templateDisableMaterialSelection')?.checked ?? false,
            disable_envelope_option: document.getElementById('templateDisableEnvelopeOption')?.checked ?? false
        };

        // Debug: Log the template data being sent
        console.log('Submitting template data:', {
            ...templateData,
            pages: `[${templatePages.length} pages]`,
            variables: `[${variableConfigurations.length} variables]`,
            fonts: `[${templateFonts.length} fonts]`
        });

        // Get thumbnail page (1-based index from dropdown)
        const thumbPageNum = parseInt(document.getElementById('templateThumbnailPage')?.value || '1', 10) - 1;
        const thumbPageData = pages.length > 0 && thumbPageNum >= 0 && thumbPageNum < pages.length ? pages[thumbPageNum].data : (pages[0]?.data || null);

        // Create thumbnail from selected page asynchronously
        createTemplateThumbnail(thumbPageData).then(thumbnail => {
            templateData.thumbnail = thumbnail;

            document.getElementById('templateSubmitBtn').textContent = 'Saving...';

            // Submit to server
            fetch('{{ route("design.templates.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(templateData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Template saved successfully!');
                    closeTemplateModal();
                    loadTemplates(); // Reload templates list
                } else {
                    throw new Error(data.message || 'Failed to save template');
                }
            })
            .catch(error => {
                console.error('Error saving template:', error);
                alert('Error saving template: ' + error.message);
                document.getElementById('templateSubmitBtn').disabled = false;
                document.getElementById('templateSubmitBtn').textContent = 'Save Template';
            });
        }).catch(error => {
            console.error('Error creating thumbnail:', error);
            // Continue without thumbnail
            templateData.thumbnail = null;

            document.getElementById('templateSubmitBtn').textContent = 'Saving...';

            // Submit to server
            fetch('{{ route("design.templates.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(templateData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Template saved successfully!');
                    closeTemplateModal();
                    loadTemplates();
                } else {
                    throw new Error(data.message || 'Failed to save template');
                }
            })
            .catch(err => {
                console.error('Error saving template:', err);
                alert('Error saving template: ' + err.message);
                document.getElementById('templateSubmitBtn').disabled = false;
                document.getElementById('templateSubmitBtn').textContent = 'Save Template';
            });
        });
    }

    // Send Letter - Save as template and navigate to send letter checkout page
    function sendLetter() {
        if (!canvas || pages.length === 0) {
            alert('No content to send. Please add at least one page.');
            return;
        }
        checkoutDesignForLetter();
    }

    // Checkout Design For Letter - Create or update template and open send letter checkout page
    function checkoutDesignForLetter() {
        if (!canvas || pages.length === 0) {
            alert('No pages to send. Please add at least one page.');
            return;
        }
        if (pages.length > 0 && currentPageIndex >= 0) {
            try {
                const currentPageJSON = canvas.toJSON(['width', 'height', 'backgroundColor']);
                if (currentPageJSON.objects) {
                    currentPageJSON.objects = currentPageJSON.objects.filter(obj => obj.name !== 'alignmentGuide');
                }
                currentPageJSON.width = canvas.getWidth();
                currentPageJSON.height = canvas.getHeight();
                currentPageJSON.backgroundColor = canvas.backgroundColor || '#ffffff';
                pages[currentPageIndex].data = JSON.stringify(currentPageJSON);
            } catch (e) {
                console.error('Error saving current page before send letter:', e);
            }
        }
        const checkoutBtn = document.querySelector('.checkout-design-btn');
        const originalText = checkoutBtn ? checkoutBtn.innerHTML : '';
        if (checkoutBtn) {
            checkoutBtn.disabled = true;
            checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Preparing...</span>';
        }
        const rawVariables = extractVariablesFromPages(pages);
        const variableConfigurations = collectVariableConfigurations(rawVariables);
        const templateFonts = extractFontsFromPages(pages);
        const templateName = 'Letter - ' + new Date().toLocaleString();
        const templateData = {
            design_id: currentDesignId || null,
            name: templateName,
            pages: pages.map(page => page.data),
            variables: variableConfigurations,
            fonts: templateFonts
        };
        const doSubmit = (thumbnail) => {
            templateData.thumbnail = thumbnail;
            fetch('{{ route("design.letter.prepareFromEditor") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify(templateData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.template_id) {
                    const sendLetterUrl = '{{ route("design.templates.sendLetter", ":id") }}'.replace(':id', data.template_id);
                    window.location.href = sendLetterUrl;
                } else {
                    throw new Error(data.message || 'Failed to save letter for checkout');
                }
            })
            .catch(err => {
                console.error('Error during send letter:', err);
                alert('Error preparing letter for checkout: ' + err.message);
                if (checkoutBtn) { checkoutBtn.disabled = false; checkoutBtn.innerHTML = originalText; }
            });
        };
        createTemplateThumbnail(pages.length > 0 ? pages[0].data : null).then(thumb => doSubmit(thumb)).catch(() => doSubmit(null));
    }

    // Checkout Design - Save as template and open quick use page
    function checkoutDesign() {
        if (!canvas) {
            alert('Canvas not initialized. Cannot checkout design.');
            return;
        }

        // Save current page first
        if (pages.length > 0 && currentPageIndex >= 0) {
            try {
                const currentPageJSON = canvas.toJSON(['width', 'height', 'backgroundColor']);
                if (currentPageJSON.objects) {
                    currentPageJSON.objects = currentPageJSON.objects.filter(obj => obj.name !== 'alignmentGuide');
                }
                currentPageJSON.width = canvas.getWidth();
                currentPageJSON.height = canvas.getHeight();
                currentPageJSON.backgroundColor = canvas.backgroundColor || '#ffffff';
                pages[currentPageIndex].data = JSON.stringify(currentPageJSON);
            } catch (e) {
                console.error('Error saving current page before checkout:', e);
            }
        }

        if (pages.length === 0) {
            alert('No pages to checkout. Please add at least one page.');
            return;
        }

        // If loaded from template and nothing changed - go directly to quick-use/send-letter (no duplicate template)
        if (sourceTemplateId && !designModified) {
            if (sourceTemplateType === 'letter') {
                window.location.href = '{{ route("design.templates.sendLetter", ":id") }}'.replace(':id', sourceTemplateId);
            } else {
                window.location.href = '{{ route("design.templates.quickUse", ":id") }}'.replace(':id', sourceTemplateId);
            }
            return;
        }

        // Show loading state
        const checkoutBtn = document.querySelector('.checkout-design-btn');
        const originalText = checkoutBtn.innerHTML;
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Preparing...</span>';

        // Prepare template data
        const templatePages = pages.map(page => page.data);
        const rawVariables = extractVariablesFromPages(pages);
        const variableConfigurations = collectVariableConfigurations(rawVariables);
        const templateFonts = extractFontsFromPages(pages);

        // Create a default template name with timestamp
        const templateName = 'Checkout Design - ' + new Date().toLocaleString();

        // Prepare template data (private template for quick use)
        const templateData = {
            name: templateName,
            is_public: false, // Private template for quick use
            type: designType || sourceTemplateType || '', // Preserve type for redirect (letter -> sendLetter)
            pages: templatePages,
            variables: variableConfigurations,
            fonts: templateFonts
        };

        // Create thumbnail from first page
        createTemplateThumbnail(pages.length > 0 ? pages[0].data : null).then(thumbnail => {
            templateData.thumbnail = thumbnail;

            // Submit to server
            fetch('{{ route("design.templates.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(templateData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.template_id) {
                    const templateType = data.template_type ?? designType ?? sourceTemplateType ?? '';
                    if (templateType === 'letter') {
                        window.location.href = '{{ route("design.templates.sendLetter", ":id") }}'.replace(':id', data.template_id);
                    } else {
                        window.location.href = '{{ route("design.templates.quickUse", ":id") }}'.replace(':id', data.template_id);
                    }
                } else {
                    throw new Error(data.message || 'Failed to save template for checkout');
                }
            })
            .catch(error => {
                console.error('Error during checkout:', error);
                alert('Error preparing design for checkout: ' + error.message);
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = originalText;
            });
        }).catch(error => {
            console.error('Error creating thumbnail:', error);
            // Continue without thumbnail
            templateData.thumbnail = null;

            // Submit to server
            fetch('{{ route("design.templates.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(templateData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.template_id) {
                    const templateType = data.template_type ?? designType ?? sourceTemplateType ?? '';
                    if (templateType === 'letter') {
                        window.location.href = '{{ route("design.templates.sendLetter", ":id") }}'.replace(':id', data.template_id);
                    } else {
                        window.location.href = '{{ route("design.templates.quickUse", ":id") }}'.replace(':id', data.template_id);
                    }
                } else {
                    throw new Error(data.message || 'Failed to save template for checkout');
                }
            })
            .catch(err => {
                console.error('Error during checkout:', err);
                alert('Error preparing design for checkout: ' + err.message);
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = originalText;
            });
        });
    }

    function createTemplateThumbnail(pageData) {
        return new Promise((resolve, reject) => {
            if (!pageData) {
                resolve(null);
                return;
            }

            try {
                const parsedData = JSON.parse(pageData);
                const canvasWidth = parsedData.width || editorSettings.canvasWidth;
                const canvasHeight = parsedData.height || editorSettings.canvasHeight;
                const backgroundColor = parsedData.backgroundColor || '#ffffff';

                const tempCanvas = new fabric.Canvas(null, {
                    width: canvasWidth,
                    height: canvasHeight,
                    backgroundColor: backgroundColor
                });

                tempCanvas.loadFromJSON(pageData, function() {
                    // Filter out alignment guides
                    const objects = tempCanvas.getObjects();
                    objects.forEach(obj => {
                        if (obj.name === 'alignmentGuide') {
                            tempCanvas.remove(obj);
                        }
                    });

                    tempCanvas.renderAll();

                    // High-quality thumbnail: full resolution, max quality for crisp display
                    const thumbnail = tempCanvas.toDataURL({
                        format: 'png',
                        quality: 1,
                        multiplier: 1
                    });

                    tempCanvas.dispose();
                    resolve(thumbnail);
                }, function(o, object) {
                    return object;
                });
            } catch (e) {
                console.error('Error creating template thumbnail:', e);
                reject(e);
            }
        });
    }

    function loadTemplates(showAll = false) {
        // Build URL with query parameter
        let url = '{{ route("design.templates.index") }}';
        if (showAll) {
            url += '?all=1';
        }

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.templates) {
                renderTemplates(data.templates);
            } else {
                renderTemplates([]);
            }
        })
        .catch(error => {
            console.error('Error loading templates:', error);
            renderTemplates([]);
        });
    }

    function renderTemplates(templates) {
        const templatesList = document.getElementById('templatesList');
        const emptyState = document.getElementById('templatesEmptyState');

        if (!templatesList) {
            console.error('Templates list container not found');
            return;
        }

        // Remove only template items, not the empty state
        const existingTemplates = templatesList.querySelectorAll('.template-item');
        existingTemplates.forEach(item => {
            item.remove();
        });

        if (templates.length === 0) {
            if (emptyState) {
                emptyState.style.display = 'block';
            }
            return;
        }

        // Hide empty state if templates exist
        if (emptyState) {
            emptyState.style.display = 'none';
        }

        templates.forEach(template => {
            const templateItem = document.createElement('div');
            templateItem.className = 'template-item';

            let thumbnailHtml = '<div class="no-thumbnail"><i class="fas fa-layer-group"></i></div>';
            const templateName = template.name || 'Untitled Template';

            if (template.thumbnail) {
                // Handle base64 thumbnail
                if (template.thumbnail.startsWith('data:')) {
                    thumbnailHtml = `<img src="${template.thumbnail}" alt="${templateName}">`;
                } else if (template.thumbnail_url) {
                    // Handle thumbnail URL from server
                    thumbnailHtml = `<img src="${template.thumbnail_url}" alt="${templateName}">`;
                } else if (template.thumbnail_path) {
                    // Handle thumbnail path
                    thumbnailHtml = `<img src="{{ asset('storage') }}/${template.thumbnail_path}" alt="${templateName}">`;
                } else {
                    // Try to use thumbnail as-is
                    thumbnailHtml = `<img src="${template.thumbnail}" alt="${templateName}">`;
                }
            } else if (template.thumbnail_url) {
                thumbnailHtml = `<img src="${template.thumbnail_url}" alt="${templateName}">`;
            } else if (template.thumbnail_path) {
                thumbnailHtml = `<img src="{{ asset('storage') }}/${template.thumbnail_path}" alt="${templateName}">`;
            }

            // Escape template ID for JavaScript (handle special characters)
            const templateId = String(template.id).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const escapedTemplateName = templateName.replace(/"/g, '&quot;').replace(/'/g, "&#39;");

            // Get page count
            const pageCount = template.page_count || (template.pages ? template.pages.length : 1);
            const pageCountText = pageCount === 1 ? '1 page' : `${pageCount} pages`;

            templateItem.innerHTML = `
                <div class="template-actions" onclick="event.stopPropagation();">
                    <button class="template-delete-btn" onclick="event.stopPropagation(); deleteTemplate('${templateId}', this);" title="Delete Template">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="template-thumbnail">
                    ${thumbnailHtml}
                    ${pageCount > 1 ? `<div class="template-page-badge">${pageCount}</div>` : ''}
                </div>
                <div class="template-name">${escapedTemplateName}</div>
                <div class="template-info">
                    <span class="template-category">${template.category || 'general'}</span>
                    <span class="template-page-count">${pageCountText}</span>
                </div>
            `;

            // Store template ID in data attribute for reliable access
            const templateIdForLoading = template.id; // Store in closure to avoid issues

            // Attach click event after innerHTML is set to avoid event handler conflicts
            templateItem.addEventListener('click', function(e) {
                // Don't trigger if clicking on delete button or actions area
                if (e.target.closest('.template-actions') || e.target.closest('.template-delete-btn')) {
                    e.stopPropagation();
                    return;
                }

                // Use the stored template ID from closure
                if (templateIdForLoading) {
                    loadTemplate(templateIdForLoading);
                }
            }, false);

            templatesList.appendChild(templateItem);
        });
    }

    function loadTemplate(templateId) {
        if (!confirm('Loading this template will replace your current design. Continue?')) {
            return;
        }

        // Save current page before loading template
        if (pages.length > 0 && currentPageIndex >= 0 && canvas) {
            try {
                const currentPageJSON = canvas.toJSON(['width', 'height', 'backgroundColor']);
                if (currentPageJSON.objects) {
                    currentPageJSON.objects = currentPageJSON.objects.filter(obj => obj.name !== 'alignmentGuide');
                }
                currentPageJSON.width = canvas.getWidth();
                currentPageJSON.height = canvas.getHeight();
                currentPageJSON.backgroundColor = canvas.backgroundColor || '#ffffff';
                pages[currentPageIndex].data = JSON.stringify(currentPageJSON);
            } catch (e) {
                console.error('Error saving current page:', e);
            }
        }

        // Encode template ID for URL
        const encodedId = encodeURIComponent(templateId);

        fetch('{{ route("design.templates.show", ":id") }}'.replace(':id', encodedId), {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.template) {
                const template = data.template;
                console.log('Template loaded successfully:', template.name);
                console.log('Template has', template.pages ? template.pages.length : 0, 'pages');

                // Load template fonts if any
                if (template.fonts && Array.isArray(template.fonts) && template.fonts.length > 0) {
                    console.log('Loading', template.fonts.length, 'custom font(s) from template');
                    template.fonts.forEach(font => {
                        if (font.url && font.name) {
                            // Add font to customFonts array if not already present
                            const fontExists = customFonts.some(f => f.name === font.name);
                            if (!fontExists) {
                                customFonts.push(font);
                                loadCustomFont(font);
                            }
                        }
                    });
                    updateFontFamilySelector();
                }

                // Clear current pages and canvas
                pages = [];
                if (canvas) {
                    canvas.clear();
                    canvas.renderAll();
                }

                // Load template pages
                if (template.pages && Array.isArray(template.pages)) {
                    template.pages.forEach((pageData, index) => {
                        let pageDataString;
                        if (typeof pageData === 'string') {
                            pageDataString = pageData;
                        } else {
                            pageDataString = JSON.stringify(pageData);
                        }

                        // Ensure width and height are stored in page data
                        try {
                            const parsed = JSON.parse(pageDataString);
                            if (!parsed.width) parsed.width = 800;
                            if (!parsed.height) parsed.height = 1000;
                            if (!parsed.backgroundColor && parsed.background) {
                                parsed.backgroundColor = parsed.background;
                            } else if (!parsed.backgroundColor && !parsed.background) {
                                parsed.backgroundColor = '#ffffff';
                                parsed.background = '#ffffff';
                            }
                            pageDataString = JSON.stringify(parsed);
                        } catch (e) {
                            console.error('Error parsing template page data:', e);
                        }

                        const page = {
                            id: 'page_' + Date.now() + '_' + index,
                            index: index,
                            name: 'Document ' + (index + 1),
                            data: pageDataString
                        };
                        pages.push(page);
                    });
                }

                // Set canvas size from first page and switch to first page
                if (pages.length > 0) {
                    // Get canvas dimensions from first page
                    try {
                        const firstPageData = typeof pages[0].data === 'string' ? JSON.parse(pages[0].data) : pages[0].data;
                        const initialWidth = firstPageData.width || editorSettings.canvasWidth;
                        const initialHeight = firstPageData.height || editorSettings.canvasHeight;
                        const initialBgColor = firstPageData.backgroundColor || firstPageData.background || '#ffffff';

                        // Set canvas dimensions before loading
                        if (canvas) {
                            canvas.setWidth(initialWidth);
                            canvas.setHeight(initialHeight);
                            canvas.setBackgroundColor(initialBgColor, canvas.renderAll.bind(canvas));
                            canvas.calcOffset();

                            // Update settings panel inputs
                            const widthInput = document.getElementById('canvasWidth');
                            const heightInput = document.getElementById('canvasHeight');
                            const bgColorInput = document.getElementById('canvasBgColor');
                            if (widthInput) widthInput.value = initialWidth;
                            if (heightInput) heightInput.value = initialHeight;
                            if (bgColorInput) bgColorInput.value = rgbToHex(initialBgColor);
                        }
                    } catch (e) {
                        console.error('Error setting canvas size from template:', e);
                    }

                    renderPagesList();
                    sourceTemplateId = templateId; // Track source - skip template create on checkout if unchanged
                    sourceTemplateType = (template.type || ''); // Track type for checkout redirect (letter -> sendLetter)
                    designModified = false; // Design now matches this template
                    setTimeout(() => {
                        switchToPage(0);
                        alert(`Template loaded successfully! (${pages.length} ${pages.length === 1 ? 'page' : 'pages'})`);
                    }, 100);
                } else {
                    alert('Template has no pages.');
                }
            } else {
                throw new Error(data.message || 'Failed to load template');
            }
        })
        .catch(error => {
            console.error('Error loading template:', error);
            alert('Error loading template: ' + error.message);
        });
    }

    // Load template silently (without confirmation) - used when loading from URL parameter
    function loadTemplateSilently(templateId) {
        if (!templateId) {
            console.error('No template ID provided');
            addNewPage();
            return;
        }

        skipDesignModifiedDuringLoad = true; // Don't mark modified during initial load
        console.log('Loading template with ID:', templateId);

        // Encode template ID for URL
        const encodedId = encodeURIComponent(templateId);
        const url = '{{ route("design.templates.show", ":id") }}'.replace(':id', encodedId);
        console.log('Template fetch URL:', url);

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Template fetch response status:', response.status, response.statusText);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response body:', text);
                    try {
                        const err = JSON.parse(text);
                        throw new Error(err.message || 'Failed to fetch template: ' + response.status);
                    } catch (e) {
                        throw new Error('Failed to fetch template: ' + response.status + ' - ' + text.substring(0, 100));
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Template data received:', data);
            if (data.success && data.template) {
                const template = data.template;
                console.log('Loading template:', template.name, 'with', template.pages ? template.pages.length : 0, 'pages');

                // Load template fonts if any
                if (template.fonts && Array.isArray(template.fonts) && template.fonts.length > 0) {
                    console.log('Loading', template.fonts.length, 'custom font(s) from template');
                    template.fonts.forEach(font => {
                        if (font.url && font.name) {
                            // Add font to customFonts array if not already present
                            const fontExists = customFonts.some(f => f.name === font.name);
                            if (!fontExists) {
                                customFonts.push(font);
                                loadCustomFont(font);
                            }
                        }
                    });
                    updateFontFamilySelector();
                }

                // Clear current pages
                pages = [];

                // Load template pages
                if (template.pages && Array.isArray(template.pages)) {
                    template.pages.forEach((pageData, index) => {
                        let pageDataString;
                        if (typeof pageData === 'string') {
                            pageDataString = pageData;
                        } else {
                            pageDataString = JSON.stringify(pageData);
                        }

                        // Ensure width and height are stored in page data
                        try {
                            const parsed = JSON.parse(pageDataString);
                            if (!parsed.width) parsed.width = 800;
                            if (!parsed.height) parsed.height = 1000;
                            if (!parsed.backgroundColor && parsed.background) {
                                parsed.backgroundColor = parsed.background;
                            } else if (!parsed.backgroundColor && !parsed.background) {
                                parsed.backgroundColor = '#ffffff';
                                parsed.background = '#ffffff';
                            }
                            pageDataString = JSON.stringify(parsed);
                        } catch (e) {
                            console.error('Error parsing template page data:', e);
                        }

                        const page = {
                            id: 'page_' + Date.now() + '_' + index,
                            index: index,
                            name: 'Document ' + (index + 1),
                            data: pageDataString
                        };
                        pages.push(page);
                    });
                }

                // Set canvas size from first page and switch to first page
                if (pages.length > 0) {
                    // Get canvas dimensions from first page
                    try {
                        const firstPageData = typeof pages[0].data === 'string' ? JSON.parse(pages[0].data) : pages[0].data;
                        const initialWidth = firstPageData.width || editorSettings.canvasWidth;
                        const initialHeight = firstPageData.height || editorSettings.canvasHeight;
                        const initialBgColor = firstPageData.backgroundColor || firstPageData.background || '#ffffff';

                        // Set canvas dimensions before loading
                        if (canvas) {
                            canvas.setWidth(initialWidth);
                            canvas.setHeight(initialHeight);
                            canvas.setBackgroundColor(initialBgColor, canvas.renderAll.bind(canvas));
                            canvas.calcOffset();

                            // Update settings panel inputs
                            const widthInput = document.getElementById('canvasWidth');
                            const heightInput = document.getElementById('canvasHeight');
                            const bgColorInput = document.getElementById('canvasBgColor');
                            if (widthInput) widthInput.value = initialWidth;
                            if (heightInput) heightInput.value = initialHeight;
                            if (bgColorInput) bgColorInput.value = rgbToHex(initialBgColor);
                        }
                    } catch (e) {
                        console.error('Error setting canvas size from template:', e);
                    }

                    renderPagesList();
                    console.log('Pages rendered, switching to page 0. Total pages:', pages.length);
                    setTimeout(() => {
                        if (canvas && pages.length > 0) {
                            console.log('Calling switchToPage(0)');
                            switchToPage(0);
                        } else {
                            console.error('Canvas not ready or no pages:', {canvas: !!canvas, pagesLength: pages.length});
                            setTimeout(() => {
                                if (canvas && pages.length > 0) {
                                    switchToPage(0);
                                }
                            }, 200);
                        }
                        setTimeout(() => { skipDesignModifiedDuringLoad = false; }, 800);
                    }, 200);
                } else {
                    // If template has no pages, create a default page
                    addNewPage();
                    setTimeout(() => { skipDesignModifiedDuringLoad = false; }, 500);
                }
            } else {
                console.error('Failed to load template:', data.message || 'Unknown error');
                alert('Failed to load template: ' + (data.message || 'Unknown error'));
                addNewPage();
                setTimeout(() => { skipDesignModifiedDuringLoad = false; }, 500);
            }
        })
        .catch(error => {
            console.error('Error loading template:', error);
            alert('Error loading template: ' + (error.message || 'Please check the console for details'));
            addNewPage();
            setTimeout(() => { skipDesignModifiedDuringLoad = false; }, 500);
        });
    }

    function loadAiContentTemplate(templateId) {
        if (!templateId) {
            addNewPage();
            return;
        }
        skipDesignModifiedDuringLoad = true;
        const url = '{{ route("design.aiContentTemplates.show", ":id") }}'.replace(':id', encodeURIComponent(templateId));
        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => {
            if (!r.ok) throw new Error('AI template not found');
            return r.json();
        })
        .then(data => {
            const editorJson = data.editor_json;
            if (editorJson && editorJson.pages && Array.isArray(editorJson.pages) && editorJson.pages.length > 0) {
                pages = [];
                if (canvas) { canvas.clear(); canvas.renderAll(); }
                editorJson.pages.forEach((pageData, index) => {
                    let pageDataString;
                    if (typeof pageData === 'string') {
                        pageDataString = pageData;
                    } else if (pageData && typeof pageData.data === 'string') {
                        pageDataString = pageData.data;
                    } else {
                        pageDataString = JSON.stringify(pageData.data || pageData);
                    }
                    try {
                        const parsed = JSON.parse(pageDataString);
                        if (!parsed.width) parsed.width = 800;
                        if (!parsed.height) parsed.height = 1000;
                        if (!parsed.backgroundColor && parsed.background) parsed.backgroundColor = parsed.background;
                        else if (!parsed.backgroundColor && !parsed.background) {
                            parsed.backgroundColor = '#ffffff';
                            parsed.background = '#ffffff';
                        }
                        pageDataString = JSON.stringify(parsed);
                    } catch (e) {}
                    pages.push({
                        id: 'page_' + Date.now() + '_' + index,
                        index: index,
                        name: (pageData && pageData.name) || ('Document ' + (index + 1)),
                        data: pageDataString
                    });
                });
                renderPagesList();
                if (pages.length > 0) {
                    try {
                        const first = JSON.parse(pages[0].data);
                        if (canvas) {
                            canvas.setWidth(first.width || 800);
                            canvas.setHeight(first.height || 1000);
                            canvas.setBackgroundColor(first.backgroundColor || first.background || '#ffffff', canvas.renderAll.bind(canvas));
                            canvas.calcOffset();
                        }
                    } catch (e) {}
                    setTimeout(() => { switchToPage(0); }, 100);
                }
            } else {
                addNewPage();
            }
            if (data.prompt && typeof openAIGenerateModal === 'function') {
                const promptEl = document.getElementById('aiPrompt');
                if (promptEl) {
                    promptEl.value = data.prompt;
                    setTimeout(openAIGenerateModal, 300);
                }
            }
            setTimeout(() => { skipDesignModifiedDuringLoad = false; }, 500);
        })
        .catch(err => {
            console.error('Error loading AI content template:', err);
            addNewPage();
            setTimeout(() => { skipDesignModifiedDuringLoad = false; }, 500);
        });
    }

    function deleteTemplate(templateId, button) {
        if (!confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
            return;
        }

        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // Encode template ID for URL
        const encodedId = encodeURIComponent(templateId);

        fetch('{{ route("design.templates.destroy", ":id") }}'.replace(':id', encodedId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove template item from DOM
                const templateItem = button.closest('.template-item');
                if (templateItem) {
                    templateItem.style.transition = 'opacity 0.3s, transform 0.3s';
                    templateItem.style.opacity = '0';
                    templateItem.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        templateItem.remove();
                        // Check if there are any templates left
                        const remainingTemplates = document.querySelectorAll('.template-item');
                        if (remainingTemplates.length === 0) {
                            loadTemplates(); // Reload to show empty state
                        }
                    }, 300);
                } else {
                    // Fallback: reload templates
                    loadTemplates();
                }
            } else {
                throw new Error(data.message || 'Failed to delete template');
            }
        })
        .catch(error => {
            console.error('Error deleting template:', error);
            alert('Error deleting template: ' + error.message);
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-trash"></i>';
        });
    }

    async function generateFlipbookDescription() {
        const btn = document.getElementById('generateDescriptionBtn');
        const textarea = document.getElementById('flipbookDescription');
        if (!btn || !textarea) return;

        const title = document.getElementById('flipbookTitle')?.value?.trim() || 'Flipbook';
        const pageCount = pages?.length || 1;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';

        try {
            const response = await fetch('{{ route("flipbooks.generateDescription") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title: title, page_count: pageCount })
            });

            const data = await response.json();

            if (data.success && data.description) {
                textarea.value = data.description;
            } else {
                alert(data.message || 'Failed to generate description');
            }
        } catch (error) {
            console.error('Error generating description:', error);
            alert('Failed to generate description. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-magic me-1"></i>Generate';
        }
    }

    function submitFlipbook(event) {
        event.preventDefault();

        // Validate all tabs before submission
        if (!validateFlipbookTab('basic') || !validateFlipbookTab('print')) {
            // Switch to the first invalid tab
            if (!validateFlipbookTab('basic')) {
                switchFlipbookTab('basic');
            } else if (!validateFlipbookTab('print')) {
                switchFlipbookTab('print');
            }
            return;
        }

        const title = document.getElementById('flipbookTitle').value.trim();
        if (!title) {
            alert('Please enter a flipbook name.');
            return;
        }

        // Show progress
        document.getElementById('flipbookProgress').style.display = 'block';
        document.getElementById('flipbookSubmitBtn').disabled = true;
        updateFlipbookProgress(10, 'Exporting pages...');

        // Export all pages as base64 data URLs
        exportPagesAsDataURLs().then(pageImages => {
            updateFlipbookProgress(50, 'Uploading to server...');

            // Prepare design data (Fabric.js JSON for each page)
            const designData = pages.map(page => {
                try {
                    // Ensure page data is valid JSON
                    if (typeof page.data === 'string') {
                        const parsed = JSON.parse(page.data);
                        // Filter out alignment guides
                        if (parsed.objects) {
                            parsed.objects = parsed.objects.filter(obj => obj.name !== 'alignmentGuide');
                        }
                        return parsed;
                    }
                    return page.data;
                } catch (e) {
                    console.error('Error processing page data:', e);
                    return null;
                }
            }).filter(data => data !== null);

            // Get print settings
            const printSheetType = document.getElementById('flipbookPrintSheetType').value;
            const printSize = document.getElementById('flipbookPrintSize').value;
            let customWidth = null;
            let customHeight = null;

            if (printSize === 'Custom') {
                customWidth = document.getElementById('flipbookCustomWidth').value;
                customHeight = document.getElementById('flipbookCustomHeight').value;
            }

            const printQuality = document.getElementById('flipbookPrintQuality').value;
            const bindingType = document.getElementById('flipbookBindingType').value;
            const bundleQuantity = parseInt(document.getElementById('bundleQuantity')?.value || 1) || 1;

            // Prepare data for submission
            const formData = {
                title: title,
                description: document.getElementById('flipbookDescription').value.trim(),
                status: document.getElementById('flipbookStatus').value,
                is_public: document.getElementById('flipbookPublic').checked,
                pages: pageImages,
                design_data: designData, // Include original design data for editing
                // Print settings
                print_sheet_type: printSheetType,
                print_size: printSize,
                print_custom_width: customWidth,
                print_custom_height: customHeight,
                print_quality: printQuality,
                binding_type: bindingType,
                bundle_quantity: bundleQuantity,
                pages_count: pages.length
            };

            // Determine if this is an update or create
            const isUpdate = currentFlipbookId !== null;
            const url = isUpdate
                ? '{{ route("flipbooks.updateFromDesign", ":id") }}'.replace(':id', currentFlipbookId)
                : '{{ route("flipbooks.createFromDesign") }}';
            const method = isUpdate ? 'PUT' : 'POST';

            // Add flipbook_id to formData if updating
            if (isUpdate) {
                formData.flipbook_id = currentFlipbookId;
            }

            // Submit to server
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const message = isUpdate ? 'Flipbook updated successfully!' : 'Flipbook created successfully!';
                    updateFlipbookProgress(100, message);

                    // Store flipbook ID and form data for future updates (pre-fill on next open)
                    if (data.flipbook_id) {
                        currentFlipbookId = data.flipbook_id;
                        existingFlipbookFormData = {
                            title: formData.title,
                            description: formData.description || '',
                            status: formData.status || 'draft',
                            is_public: formData.is_public || false,
                            print_sheet_type: formData.print_sheet_type || null,
                            print_size: formData.print_size || null,
                            print_custom_width: formData.print_custom_width || null,
                            print_custom_height: formData.print_custom_height || null,
                            print_quality: formData.print_quality || null,
                            binding_type: formData.binding_type || null,
                            bundle_quantity: formData.bundle_quantity || 1
                        };
                        // Update button text (handle both toolbar button and dropdown button)
                        const btnText = document.getElementById('saveAsFlipbookBtnText');
                        const btnTextDropdown = document.getElementById('saveAsFlipbookBtnTextDropdown');
                        if (btnText) {
                            btnText.textContent = 'Update Flipbook';
                        }
                        if (btnTextDropdown) {
                            btnTextDropdown.textContent = 'Update Flipbook';
                        }
                    }

                    setTimeout(() => {
                        closeFlipbookModal();
                        alert(message);
                    }, 1000);
                } else {
                    throw new Error(data.message || (isUpdate ? 'Failed to update flipbook' : 'Failed to create flipbook'));
                }
            })
            .catch(error => {
                console.error('Error ' + (isUpdate ? 'updating' : 'creating') + ' flipbook:', error);
                alert('Error ' + (isUpdate ? 'updating' : 'creating') + ' flipbook: ' + error.message);
                document.getElementById('flipbookSubmitBtn').disabled = false;
                document.getElementById('flipbookProgress').style.display = 'none';
            });
        })
        .catch(error => {
            console.error('Error exporting pages:', error);
            alert('Error exporting pages: ' + error.message);
            document.getElementById('flipbookSubmitBtn').disabled = false;
            document.getElementById('flipbookProgress').style.display = 'none';
        });
    }

    function exportPagesAsDataURLs() {
        return new Promise((resolve, reject) => {
            const pageImages = [];
            let processedCount = 0;

            pages.forEach((page, index) => {
                setTimeout(() => {
                    try {
                        // Parse page data
                        let pageData;
                        if (typeof page.data === 'string') {
                            pageData = JSON.parse(page.data);
                        } else {
                            pageData = page.data;
                        }

                        // Get canvas dimensions
                        let canvasWidth = 800;
                        let canvasHeight = 1000;

                        if (pageData.width && pageData.height) {
                            canvasWidth = pageData.width;
                            canvasHeight = pageData.height;
                        } else if (canvas) {
                            canvasWidth = canvas.getWidth();
                            canvasHeight = canvas.getHeight();
                        }

                        const backgroundColor = pageData.background || pageData.backgroundColor || '#ffffff';

                        // Create temporary canvas
                        const tempCanvas = new fabric.Canvas(null, {
                            width: canvasWidth,
                            height: canvasHeight,
                            backgroundColor: backgroundColor
                        });

                        // Load objects from page data
                        tempCanvas.loadFromJSON(page.data, function() {
                            // Set background color
                            if (backgroundColor) {
                                tempCanvas.setBackgroundColor(backgroundColor, function() {
                                    tempCanvas.renderAll();

                                    // Export as base64 data URL
                                    const dataURL = tempCanvas.toDataURL({
                                        format: 'png',
                                        quality: 1.0,
                                        multiplier: 2
                                    });

                                    pageImages.push({
                                        index: index,
                                        image: dataURL,
                                        page_number: index + 1
                                    });

                                    tempCanvas.dispose();
                                    processedCount++;

                                    // Check if all pages are processed
                                    if (processedCount === pages.length) {
                                        // Sort by index to maintain order
                                        pageImages.sort((a, b) => a.index - b.index);
                                        resolve(pageImages.map(p => p.image));
                                    }
                                });
                            } else {
                                tempCanvas.renderAll();

                                const dataURL = tempCanvas.toDataURL({
                                    format: 'png',
                                    quality: 1.0,
                                    multiplier: 2
                                });

                                pageImages.push({
                                    index: index,
                                    image: dataURL,
                                    page_number: index + 1
                                });

                                tempCanvas.dispose();
                                processedCount++;

                                if (processedCount === pages.length) {
                                    pageImages.sort((a, b) => a.index - b.index);
                                    resolve(pageImages.map(p => p.image));
                                }
                            }
                        }, function(o, object) {
                            return object;
                        });
                    } catch (e) {
                        console.error(`Error exporting page ${index + 1}:`, e);
                        processedCount++;
                        if (processedCount === pages.length) {
                            if (pageImages.length > 0) {
                                pageImages.sort((a, b) => a.index - b.index);
                                resolve(pageImages.map(p => p.image));
                            } else {
                                reject(new Error(`Failed to export pages: ${e.message}`));
                            }
                        }
                    }
                }, index * 200); // Smaller delay for server submission
            });
        });
    }

    function updateFlipbookProgress(percentage, text) {
        document.getElementById('flipbookProgressBar').style.width = percentage + '%';
        document.getElementById('flipbookProgressText').textContent = text;
    }

    async function exportAllPagesPDF() {
        if (!canExportWatermark) {
            alert('Export is only available for admin accounts.');
            return;
        }
        if (!canvas) {
            alert('Canvas not initialized. Cannot export pages.');
            return;
        }

        // Lazy-load jsPDF only when exporting
        await loadJsPDF();

        // Save current page first
        if (pages.length > 0 && currentPageIndex >= 0) {
            try {
                const currentPageJSON = canvas.toJSON(['width', 'height', 'backgroundColor']);
                pages[currentPageIndex].data = JSON.stringify(currentPageJSON);
            } catch (e) {
                console.error('Error saving current page before export:', e);
            }
        }

        if (pages.length === 0) {
            alert('No pages to export. Please add at least one page.');
            return;
        }

        // Check if jsPDF is available
        if (typeof window.jspdf === 'undefined') {
            alert('PDF library not loaded. Please refresh the page and try again.');
            return;
        }

        // Show progress message
        const exportMessage = document.createElement('div');
        exportMessage.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 10000; text-align: center;';
        exportMessage.innerHTML = `<p>Exporting ${pages.length} page(s) as PDF...</p><p style="font-size: 0.875rem; color: #64748b;">Please wait...</p>`;
        document.body.appendChild(exportMessage);

        // Get canvas dimensions (use first page or current canvas)
        let pdfWidth = 800;
        let pdfHeight = 1000;

        if (pages.length > 0) {
            try {
                const firstPageData = typeof pages[0].data === 'string' ? JSON.parse(pages[0].data) : pages[0].data;
                if (firstPageData.width && firstPageData.height) {
                    pdfWidth = firstPageData.width;
                    pdfHeight = firstPageData.height;
                } else if (canvas) {
                    pdfWidth = canvas.getWidth();
                    pdfHeight = canvas.getHeight();
                }
            } catch (e) {
                console.error('Error getting canvas dimensions:', e);
            }
        }

        // Convert pixels to mm (assuming 96 DPI: 1 inch = 96 pixels, 1 inch = 25.4 mm)
        const mmWidth = (pdfWidth / 96) * 25.4;
        const mmHeight = (pdfHeight / 96) * 25.4;

        // Create PDF
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({
            orientation: mmWidth > mmHeight ? 'landscape' : 'portrait',
            unit: 'mm',
            format: [mmWidth, mmHeight]
        });

        // Export each page as image and add to PDF
        let pagesProcessed = 0;

        pages.forEach((page, index) => {
            setTimeout(() => {
                try {
                    // Parse page data
                    let pageData;
                    if (typeof page.data === 'string') {
                        pageData = JSON.parse(page.data);
                    } else {
                        pageData = page.data;
                    }

                    // Get canvas dimensions for this page
                    let canvasWidth = pdfWidth;
                    let canvasHeight = pdfHeight;

                    if (pageData.width && pageData.height) {
                        canvasWidth = pageData.width;
                        canvasHeight = pageData.height;
                    }

                    const backgroundColor = pageData.background || pageData.backgroundColor || '#ffffff';

                    // Create temporary canvas with proper dimensions
                    const tempCanvas = new fabric.Canvas(null, {
                        width: canvasWidth,
                        height: canvasHeight,
                        backgroundColor: backgroundColor
                    });

                    // Load objects from page data
                    tempCanvas.loadFromJSON(page.data, function() {
                        // Set background color explicitly
                        const renderAndAdd = () => {
                            tempCanvas.renderAll();

                            // Export canvas as image
                            const dataURL = tempCanvas.toDataURL({
                                format: 'png',
                                quality: 1.0,
                                multiplier: 2 // Higher resolution
                            });

                            // Convert this page's dimensions to mm
                            const pageMmWidth = (canvasWidth / 96) * 25.4;
                            const pageMmHeight = (canvasHeight / 96) * 25.4;

                            // Add new page if not the first page
                            if (index > 0) {
                                pdf.addPage([pageMmWidth, pageMmHeight]);
                            } else {
                                // Set first page size
                                pdf.setPage(1);
                            }

                            // Add image to PDF (full page)
                            pdf.addImage(dataURL, 'PNG', 0, 0, pageMmWidth, pageMmHeight, undefined, 'FAST');

                            pagesProcessed++;

                            // Clean up canvas
                            tempCanvas.dispose();

                            // If all pages processed, save PDF
                            if (pagesProcessed === pages.length) {
                                // Save PDF
                                pdf.save('design-pages.pdf');

                                // Remove progress message
                                if (exportMessage && exportMessage.parentNode) {
                                    exportMessage.parentNode.removeChild(exportMessage);
                                }

                                alert(`Successfully exported ${pages.length} page(s) as PDF!`);
                            }
                        };

                        if (backgroundColor) {
                            tempCanvas.setBackgroundColor(backgroundColor, function() {
                                renderAndAdd();
                            });
                        } else {
                            renderAndAdd();
                        }
                    }, function(o, object) {
                        // Called for each object - return as-is
                        return object;
                    });
                } catch (e) {
                    console.error(`Error exporting page ${index + 1} to PDF:`, e);
                    pagesProcessed++;

                    // If all pages processed (even with errors), try to save PDF
                    if (pagesProcessed === pages.length) {
                        if (exportMessage && exportMessage.parentNode) {
                            exportMessage.parentNode.removeChild(exportMessage);
                        }
                        alert(`Error exporting some pages. PDF may be incomplete.`);
                    }
                }
            }, index * 300); // Smaller delay for PDF since we're not triggering downloads
        });
    }

    // Watermark Modal Functions (admin only)
    function openWatermarkModal() {
        if (!canExportWatermark) {
            alert('Export PDF with Watermark is only available for admin accounts.');
            return;
        }
        document.getElementById('watermarkModal').style.display = 'block';

        // Show/hide custom position fields based on selection
        const positionSelect = document.getElementById('watermarkPosition');
        const customContainer = document.getElementById('customPositionContainer');

        // Check initial state
        if (positionSelect.value === 'custom') {
            customContainer.style.display = 'block';
        } else {
            customContainer.style.display = 'none';
        }

        // Handle changes
        positionSelect.onchange = function() {
            if (this.value === 'custom') {
                customContainer.style.display = 'block';
            } else {
                customContainer.style.display = 'none';
            }
        };
    }

    function closeWatermarkModal() {
        document.getElementById('watermarkModal').style.display = 'none';
        document.getElementById('watermarkForm').reset();
        document.getElementById('watermarkText').value = 'DRAFT';
        document.getElementById('watermarkFontSize').value = '72';
        document.getElementById('watermarkOpacity').value = '0.3';
        document.getElementById('watermarkRotation').value = '-45';
        document.getElementById('watermarkPosition').value = 'center';
        document.getElementById('watermarkColor').value = '#000000';
        document.getElementById('watermarkFontStyle').value = 'normal';
        document.getElementById('customPositionContainer').style.display = 'none';
    }

    // AI Generate Design Functions
    function openAIGenerateModal() {
        document.getElementById('aiGenerateModal').style.display = 'block';
        document.getElementById('aiPrompt').focus();
    }

    function closeAIGenerateModal() {
        document.getElementById('aiGenerateModal').style.display = 'none';
        document.getElementById('aiGenerateForm').reset();
        document.getElementById('aiGenerateStatus').style.display = 'none';
        document.getElementById('aiGenerateSubmitBtn').disabled = false;
    }

    async function generateAIDesign(event) {
        event.preventDefault();

        const prompt = document.getElementById('aiPrompt').value.trim();
        if (!prompt) {
            alert('Please enter a description for your design.');
            return;
        }

        // Show loading state
        const statusDiv = document.getElementById('aiGenerateStatus');
        const statusText = document.getElementById('aiGenerateStatusText');
        const submitBtn = document.getElementById('aiGenerateSubmitBtn');

        statusDiv.style.display = 'block';
        statusText.textContent = 'Generating your design...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('{{ route("design.generateAI") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    prompt: prompt,
                    canvasWidth: canvas ? canvas.getWidth() : 800,
                    canvasHeight: canvas ? canvas.getHeight() : 1000
                })
            });

            const data = await response.json();

            if (data.success && data.design_data) {
                // Load the generated design onto the canvas
                if (canvas) {
                    // Save current page state before loading new design
                    if (pages.length > 0 && currentPageIndex >= 0) {
                        try {
                            const currentPageData = canvas.toJSON();
                            if (currentPageData.objects) {
                                currentPageData.objects = currentPageData.objects.filter(obj => obj.name !== 'alignmentGuide');
                            }
                            pages[currentPageIndex].data = JSON.stringify(currentPageData);
                            updatePageThumbnail(currentPageIndex);
                        } catch (e) {
                            console.error('Error saving current page:', e);
                        }
                    }

                    // Clear current canvas
                    canvas.clear();

                    // Load the AI-generated design
                    canvas.loadFromJSON(data.design_data, function() {
                        canvas.renderAll();

                        // Update current page data
                        if (pages.length > 0 && currentPageIndex >= 0) {
                            const newPageData = canvas.toJSON();
                            if (newPageData.objects) {
                                newPageData.objects = newPageData.objects.filter(obj => obj.name !== 'alignmentGuide');
                            }
                            pages[currentPageIndex].data = JSON.stringify(newPageData);
                            updatePageThumbnail(currentPageIndex);
                        }

                        closeAIGenerateModal();

                        // Show success message
                        alert('Design generated successfully!');
                    });
                } else {
                    throw new Error('Canvas not initialized');
                }
            } else {
                throw new Error(data.message || 'Failed to generate design');
            }
        } catch (error) {
            console.error('Error generating AI design:', error);
            statusText.textContent = 'Error: ' + error.message;
            statusText.style.color = '#ef4444';
            submitBtn.disabled = false;

            setTimeout(() => {
                statusDiv.style.display = 'none';
                statusText.style.color = '';
            }, 5000);
        }
    }

    // AI Generate Text Content for selected text element
    let aiGenerateTextTargetObject = null;

    function openAIGenerateTextModal(textObject) {
        aiGenerateTextTargetObject = textObject;
        const modal = document.getElementById('aiGenerateTextModal');
        const promptInput = document.getElementById('aiGenerateTextPrompt');
        const currentTextDiv = document.getElementById('aiGenerateTextCurrentText');
        const currentTextVal = document.getElementById('aiGenerateTextCurrentTextVal');
        const statusDiv = document.getElementById('aiGenerateTextStatus');
        const form = document.getElementById('aiGenerateTextForm');

        if (!modal || !promptInput) return;

        const currentText = (textObject && textObject.text) ? String(textObject.text) : '';
        if (currentText) {
            currentTextDiv.style.display = 'block';
            currentTextVal.textContent = currentText.length > 80 ? currentText.substring(0, 80) + '...' : currentText;
        } else {
            currentTextDiv.style.display = 'none';
        }

        form.reset();
        statusDiv.style.display = 'none';
        document.getElementById('aiGenerateTextSubmitBtn').disabled = false;
        modal.style.display = 'block';
        setTimeout(function() { promptInput.focus(); }, 100);
    }

    function closeAIGenerateTextModal() {
        aiGenerateTextTargetObject = null;
        const modal = document.getElementById('aiGenerateTextModal');
        if (modal) modal.style.display = 'none';
        document.getElementById('aiGenerateTextForm').reset();
        document.getElementById('aiGenerateTextStatus').style.display = 'none';
        document.getElementById('aiGenerateTextSubmitBtn').disabled = false;
    }

    async function generateTextContentForElement(event) {
        event.preventDefault();
        const prompt = document.getElementById('aiGenerateTextPrompt').value.trim();
        if (!prompt) {
            alert('Please enter a prompt describing what you want to generate.');
            return;
        }
        if (!aiGenerateTextTargetObject || !canvas) {
            alert('No text element selected.');
            closeAIGenerateTextModal();
            return;
        }

        const statusDiv = document.getElementById('aiGenerateTextStatus');
        const statusText = document.getElementById('aiGenerateTextStatusText');
        const submitBtn = document.getElementById('aiGenerateTextSubmitBtn');

        statusDiv.style.display = 'block';
        statusText.textContent = 'Generating content...';
        statusText.style.color = '';
        submitBtn.disabled = true;

        try {
            const currentText = aiGenerateTextTargetObject.text ? String(aiGenerateTextTargetObject.text) : '';
            const response = await fetch('{{ route("design.generateTextAI") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    prompt: prompt,
                    current_text: currentText
                })
            });

            const data = await response.json();

            if (data.success && data.content) {
                aiGenerateTextTargetObject.set('text', data.content);
                canvas.renderAll();
                if (pages.length > 0 && currentPageIndex >= 0) {
                    try {
                        const pageData = canvas.toJSON();
                        if (pageData.objects) {
                            pageData.objects = pageData.objects.filter(obj => obj.name !== 'alignmentGuide');
                        }
                        pages[currentPageIndex].data = JSON.stringify(pageData);
                        updatePageThumbnail(currentPageIndex);
                    } catch (e) {
                        console.error('Error saving page:', e);
                    }
                }
                closeAIGenerateTextModal();
            } else {
                throw new Error(data.message || 'Failed to generate content');
            }
        } catch (error) {
            console.error('Error generating text:', error);
            statusText.textContent = 'Error: ' + error.message;
            statusText.style.color = '#ef4444';
            submitBtn.disabled = false;
        }
    }

    async function exportPDFWithWatermark(event) {
        event.preventDefault();
        if (!canExportWatermark) {
            alert('Export PDF with Watermark is only available for admin accounts.');
            return;
        }
        if (!canvas) {
            alert('Canvas not initialized. Cannot export pages.');
            return;
        }

        // Lazy-load jsPDF only when exporting
        await loadJsPDF();

        // Save current page first
        if (pages.length > 0 && currentPageIndex >= 0) {
            try {
                const currentPageJSON = canvas.toJSON(['width', 'height', 'backgroundColor']);
                if (currentPageJSON.objects) {
                    currentPageJSON.objects = currentPageJSON.objects.filter(obj => obj.name !== 'alignmentGuide');
                }
                pages[currentPageIndex].data = JSON.stringify(currentPageJSON);
            } catch (e) {
                console.error('Error saving current page before export:', e);
            }
        }

        if (pages.length === 0) {
            alert('No pages to export. Please add at least one page.');
            return;
        }

        // Check if jsPDF is available
        if (typeof window.jspdf === 'undefined') {
            alert('PDF library not loaded. Please refresh the page and try again.');
            return;
        }

        // Get watermark settings from form
        const watermarkText = document.getElementById('watermarkText').value;
        const watermarkFontSize = parseFloat(document.getElementById('watermarkFontSize').value) || 72;
        const watermarkOpacity = parseFloat(document.getElementById('watermarkOpacity').value) || 0.3;
        const watermarkRotation = parseFloat(document.getElementById('watermarkRotation').value) || -45;
        const watermarkPosition = document.getElementById('watermarkPosition').value;
        const watermarkColor = document.getElementById('watermarkColor').value;
        const watermarkFontStyle = document.getElementById('watermarkFontStyle').value;
        const watermarkX = parseFloat(document.getElementById('watermarkX').value) || 0;
        const watermarkY = parseFloat(document.getElementById('watermarkY').value) || 0;

        // Close modal
        closeWatermarkModal();

        // Show progress message
        const exportMessage = document.createElement('div');
        exportMessage.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 10000; text-align: center;';
        exportMessage.innerHTML = `<p>Exporting ${pages.length} page(s) as PDF with watermark...</p><p style="font-size: 0.875rem; color: #64748b;">Please wait...</p>`;
        document.body.appendChild(exportMessage);

        // Get canvas dimensions (use first page or current canvas)
        let pdfWidth = 800;
        let pdfHeight = 1000;

        if (pages.length > 0) {
            try {
                const firstPageData = typeof pages[0].data === 'string' ? JSON.parse(pages[0].data) : pages[0].data;
                if (firstPageData.width && firstPageData.height) {
                    pdfWidth = firstPageData.width;
                    pdfHeight = firstPageData.height;
                } else if (canvas) {
                    pdfWidth = canvas.getWidth();
                    pdfHeight = canvas.getHeight();
                }
            } catch (e) {
                console.error('Error getting canvas dimensions:', e);
            }
        }

        // Convert pixels to mm (assuming 96 DPI: 1 inch = 96 pixels, 1 inch = 25.4 mm)
        const mmWidth = (pdfWidth / 96) * 25.4;
        const mmHeight = (pdfHeight / 96) * 25.4;

        // Create PDF
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({
            orientation: mmWidth > mmHeight ? 'landscape' : 'portrait',
            unit: 'mm',
            format: [mmWidth, mmHeight]
        });

        // Helper function to add watermark to a page using canvas overlay
        function addWatermarkToImage(dataURL, watermarkSettings, callback) {
            const { text, fontSize, opacity, rotation, position, color, fontStyle, customX, customY } = watermarkSettings;

            // Create an image from the data URL
            const img = new Image();
            img.onload = function() {
                // Create a temporary canvas for watermark
                const watermarkCanvas = document.createElement('canvas');
                watermarkCanvas.width = img.width;
                watermarkCanvas.height = img.height;
                const ctx = watermarkCanvas.getContext('2d');

                // Draw the original image
                ctx.drawImage(img, 0, 0);

                // Set up watermark text styling
                ctx.save();
                ctx.globalAlpha = opacity;
                ctx.fillStyle = color;
                ctx.font = `${fontStyle === 'bold' ? 'bold ' : ''}${fontStyle === 'italic' ? 'italic ' : ''}${fontSize}px Arial, sans-serif`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                // Calculate position in pixels
                const pixelWidth = img.width;
                const pixelHeight = img.height;
                let x, y;

                switch (position) {
                    case 'center':
                        x = pixelWidth / 2;
                        y = pixelHeight / 2;
                        break;
                    case 'top-left':
                        x = pixelWidth * 0.25;
                        y = pixelHeight * 0.25;
                        break;
                    case 'top-right':
                        x = pixelWidth * 0.75;
                        y = pixelHeight * 0.25;
                        break;
                    case 'bottom-left':
                        x = pixelWidth * 0.25;
                        y = pixelHeight * 0.75;
                        break;
                    case 'bottom-right':
                        x = pixelWidth * 0.75;
                        y = pixelHeight * 0.75;
                        break;
                    case 'top-center':
                        x = pixelWidth / 2;
                        y = pixelHeight * 0.25;
                        break;
                    case 'bottom-center':
                        x = pixelWidth / 2;
                        y = pixelHeight * 0.75;
                        break;
                    case 'custom':
                        // Convert mm to pixels (assuming 96 DPI)
                        x = (customX / 25.4) * 96;
                        y = (customY / 25.4) * 96;
                        break;
                    default:
                        x = pixelWidth / 2;
                        y = pixelHeight / 2;
                }

                // Apply rotation
                ctx.translate(x, y);
                ctx.rotate(rotation * Math.PI / 180);
                ctx.translate(-x, -y);

                // Draw watermark text
                ctx.fillText(text, x, y);

                ctx.restore();

                // Convert to data URL and call callback
                const watermarkedDataURL = watermarkCanvas.toDataURL('image/png', 1.0);
                callback(watermarkedDataURL);
            };
            img.src = dataURL;
        }

        // Export each page as image and add to PDF with watermark
        let pagesProcessed = 0;

        pages.forEach((page, index) => {
            setTimeout(() => {
                try {
                    // Parse page data
                    let pageData;
                    if (typeof page.data === 'string') {
                        pageData = JSON.parse(page.data);
                    } else {
                        pageData = page.data;
                    }

                    // Get canvas dimensions for this page
                    let canvasWidth = pdfWidth;
                    let canvasHeight = pdfHeight;

                    if (pageData.width && pageData.height) {
                        canvasWidth = pageData.width;
                        canvasHeight = pageData.height;
                    }

                    const backgroundColor = pageData.background || pageData.backgroundColor || '#ffffff';

                    // Create temporary canvas with proper dimensions
                    const tempCanvas = new fabric.Canvas(null, {
                        width: canvasWidth,
                        height: canvasHeight,
                        backgroundColor: backgroundColor
                    });

                    // Load objects from page data
                    tempCanvas.loadFromJSON(page.data, function() {
                        // Set background color explicitly
                        const renderAndAdd = () => {
                            tempCanvas.renderAll();

                            // Export canvas as image
                            const dataURL = tempCanvas.toDataURL({
                                format: 'png',
                                quality: 1.0,
                                multiplier: 2 // Higher resolution
                            });

                            // Convert this page's dimensions to mm
                            const pageMmWidth = (canvasWidth / 96) * 25.4;
                            const pageMmHeight = (canvasHeight / 96) * 25.4;

                            // Calculate font size in pixels (convert from pt to pixels, then scale for multiplier)
                            // 1pt = 1.33px at 96 DPI, and we're using multiplier 2, so we need to account for that
                            const fontSizePx = (watermarkFontSize * 1.33) * (canvasWidth / 800); // Scale relative to default 800px width

                            // Add watermark to image before adding to PDF
                            addWatermarkToImage(dataURL, {
                                text: watermarkText,
                                fontSize: fontSizePx,
                                opacity: watermarkOpacity,
                                rotation: watermarkRotation,
                                position: watermarkPosition,
                                color: watermarkColor,
                                fontStyle: watermarkFontStyle,
                                customX: watermarkX,
                                customY: watermarkY
                            }, function(watermarkedDataURL) {
                                // Add new page if not the first page
                                if (index > 0) {
                                    pdf.addPage([pageMmWidth, pageMmHeight]);
                                } else {
                                    // Set first page size
                                    pdf.setPage(1);
                                }

                                // Add watermarked image to PDF (full page)
                                pdf.addImage(watermarkedDataURL, 'PNG', 0, 0, pageMmWidth, pageMmHeight, undefined, 'FAST');

                                pagesProcessed++;

                                // Clean up canvas
                                tempCanvas.dispose();

                                // If all pages processed, save PDF
                                if (pagesProcessed === pages.length) {
                                    pdf.save('design-pages-watermarked.pdf');

                                    // Remove progress message
                                    if (exportMessage && exportMessage.parentNode) {
                                        exportMessage.parentNode.removeChild(exportMessage);
                                    }

                                    alert(`Successfully exported ${pages.length} page(s) as PDF with watermark!`);
                                }
                            });

                        };

                        if (backgroundColor) {
                            tempCanvas.setBackgroundColor(backgroundColor, function() {
                                renderAndAdd();
                            });
                        } else {
                            renderAndAdd();
                        }
                    }, function(o, object) {
                        // Called for each object - return as-is
                        return object;
                    });
                } catch (e) {
                    console.error(`Error exporting page ${index + 1} to PDF:`, e);
                    pagesProcessed++;

                    // If all pages processed (even with errors), try to save PDF
                    if (pagesProcessed === pages.length) {
                        if (exportMessage && exportMessage.parentNode) {
                            exportMessage.parentNode.removeChild(exportMessage);
                        }
                        alert(`Error exporting some pages. PDF may be incomplete.`);
                    }
                }
            }, index * 300); // Smaller delay for PDF since we're not triggering downloads
        });
    }

    // Image Crop/Edit Functions
    let imageCropper = null;
    let currentCropImageObject = null;

    // Make function globally accessible
    async function openImageCropModal(imageObject) {
        console.log('openImageCropModal called with:', imageObject);

        if (!imageObject) {
            console.error('No image object provided');
            alert('Error: No image object provided.');
            return;
        }

        if (imageObject.type !== 'image') {
            console.error('Object is not an image, type is:', imageObject.type);
            alert('Error: Selected object is not an image.');
            return;
        }

        // Lazy-load Cropper.js only when crop modal is opened
        await loadCropperLib();

        currentCropImageObject = imageObject;
        const cropImageElement = document.getElementById('cropImage');
        const modal = document.getElementById('imageCropModal');

        console.log('Looking for elements:', {
            cropImageElement: !!cropImageElement,
            modal: !!modal,
            modalElement: modal
        });

        if (!cropImageElement) {
            console.error('Crop image element not found');
            alert('Error: Crop image element not found. Please refresh the page.');
            return;
        }

        if (!modal) {
            console.error('Image crop modal not found');
            alert('Error: Image crop modal not found. Please refresh the page.');
            return;
        }

        console.log('Elements found, proceeding to load image...');

        // Show modal immediately, even before image loads
        console.log('Showing modal immediately...');
        modal.style.setProperty('display', 'block', 'important');
        modal.style.setProperty('visibility', 'visible', 'important');
        modal.style.setProperty('opacity', '1', 'important');
        console.log('Modal should now be visible');

        console.log('Getting image element...');

        // Try multiple methods to get the image source
        let imageSrc = null;

        // Method 1: Try to get from _element directly
        if (imageObject._element) {
            const element = imageObject._element;
            if (element.src) {
                imageSrc = element.src;
                console.log('Using _element.src:', imageSrc.substring(0, 100) + '...');
            } else if (element instanceof HTMLImageElement && element.src) {
                imageSrc = element.src;
                console.log('Using HTMLImageElement.src:', imageSrc.substring(0, 100) + '...');
            }
        }

        // Method 2: Use Fabric.js toDataURL method (most reliable)
        if (!imageSrc) {
            try {
                console.log('Trying toDataURL method...');
                // Create a temporary canvas to export the image
                const tempCanvas = document.createElement('canvas');
                const actualWidth = imageObject.width * imageObject.scaleX;
                const actualHeight = imageObject.height * imageObject.scaleY;
                tempCanvas.width = actualWidth;
                tempCanvas.height = actualHeight;

                // Use Fabric.js to export the image
                imageObject.toDataURL({
                    format: 'png',
                    quality: 1,
                    multiplier: 1
                }, function(dataUrl) {
                    if (dataUrl) {
                        imageSrc = dataUrl;
                        console.log('Got image from toDataURL, length:', imageSrc.length);
                        loadImageIntoCropper(imageSrc);
                    } else {
                        // Fallback to getElement method
                        tryGetElementImage();
                    }
                });
                return; // Exit early, loadImageIntoCropper will be called from callback
            } catch (e) {
                console.error('Error with toDataURL:', e);
                // Fallback to getElement method
            }
        }

        // Method 3: Use getElement as fallback
        function tryGetElementImage() {
            imageObject.getElement(function(img) {
                console.log('Got image element from getElement:', img);

                if (!img) {
                    console.error('Unable to get image element');
                    alert('Unable to load image for editing. Please try again.');
                    return;
                }

                // Try to get src from the image element
                if (img.src) {
                    imageSrc = img.src;
                    console.log('Using img.src from getElement:', imageSrc.substring(0, 100) + '...');
                } else {
                    // Last resort: draw to canvas
                    try {
                        console.log('Exporting image to canvas as last resort...');
                        const tempCanvas = document.createElement('canvas');
                        const actualWidth = imageObject.width * imageObject.scaleX;
                        const actualHeight = imageObject.height * imageObject.scaleY;
                        tempCanvas.width = actualWidth;
                        tempCanvas.height = actualHeight;
                        const ctx = tempCanvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, tempCanvas.width, tempCanvas.height);
                        imageSrc = tempCanvas.toDataURL('image/png');
                        console.log('Exported to canvas, data URL length:', imageSrc.length);
                    } catch (e) {
                        console.error('Error extracting image:', e);
                        alert('Unable to load image for editing. Please try again.');
                        return;
                    }
                }

                if (!imageSrc) {
                    console.error('No image source found');
                    alert('Unable to load image for editing. Please try again.');
                    return;
                }

                loadImageIntoCropper(imageSrc);
            });
        }

        // If we already have imageSrc, load it directly
        if (imageSrc) {
            loadImageIntoCropper(imageSrc);
        } else {
            // Otherwise try getElement method
            tryGetElementImage();
        }

        function loadImageIntoCropper(src) {
            console.log('Loading image into cropper, src length:', src ? src.length : 0);

            if (!src) {
                console.error('No image source to load');
                alert('Unable to load image for editing. Please try again.');
                return;
            }

            // Modal should already be visible, but ensure it's still visible
            // Load image into cropper
            cropImageElement.src = src;

            // Initialize cropper after image loads
            const initCropper = function() {
                console.log('Initializing cropper...');

                // Destroy existing cropper if any
                if (imageCropper) {
                    imageCropper.destroy();
                    imageCropper = null;
                }

                // Check if Cropper is available
                if (typeof Cropper === 'undefined') {
                    console.error('Cropper.js library not loaded');
                    alert('Error: Cropper.js library not loaded. Please refresh the page.');
                    return;
                }

                try {
                    // Initialize cropper
                    imageCropper = new Cropper(cropImageElement, {
                        aspectRatio: NaN, // Free aspect ratio
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                        responsive: true,
                        ready: function() {
                            console.log('Cropper is ready');
                        }
                    });
                    console.log('Cropper initialized successfully');
                } catch (e) {
                    console.error('Error initializing cropper:', e);
                    alert('Error initializing image cropper. Please try again.');
                }
            };

            // Set up load handler
            cropImageElement.onload = initCropper;
            cropImageElement.onerror = function() {
                console.error('Error loading image');
                alert('Error loading image. Please try again.');
            };

            // Trigger load if image is already loaded
            if (cropImageElement.complete) {
                console.log('Image already loaded, initializing cropper immediately');
                setTimeout(initCropper, 100);
            }
        }
    }

    // Make it globally accessible
    window.openImageCropModal = openImageCropModal;

    function closeImageCropModal() {
        const modal = document.getElementById('imageCropModal');
        if (modal) {
            modal.style.display = 'none';
        }

        if (imageCropper) {
            imageCropper.destroy();
            imageCropper = null;
        }

        currentCropImageObject = null;
    }

    function resetCrop() {
        if (imageCropper) {
            imageCropper.reset();
        }
    }

    function rotateCrop(degrees) {
        if (imageCropper) {
            imageCropper.rotate(degrees);
        }
    }

    function flipCrop(direction) {
        if (imageCropper) {
            if (direction === 'horizontal') {
                imageCropper.scaleX(imageCropper.getImageData().scaleX * -1);
            } else if (direction === 'vertical') {
                imageCropper.scaleY(imageCropper.getImageData().scaleY * -1);
            }
        }
    }

    function applyCrop() {
        console.log('applyCrop called');
        console.log('imageCropper:', imageCropper);
        console.log('currentCropImageObject:', currentCropImageObject);

        if (!imageCropper) {
            console.error('Image cropper not initialized');
            alert('Error: Image cropper not initialized. Please try again.');
            return;
        }

        if (!currentCropImageObject) {
            console.error('No image object to crop');
            alert('Error: No image object found. Please try again.');
            return;
        }

        try {
            // Get cropped canvas - don't specify width/height to get the actual cropped size
            const croppedCanvas = imageCropper.getCroppedCanvas({
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
                fillColor: '#fff'
            });

            console.log('Cropped canvas:', croppedCanvas);

            if (!croppedCanvas) {
                console.error('Failed to get cropped canvas');
                alert('Error cropping image. Please try again.');
                return;
            }

            // Convert canvas to data URL
            const croppedDataUrl = croppedCanvas.toDataURL('image/png');
            console.log('Cropped data URL length:', croppedDataUrl.length);

            if (!croppedDataUrl || croppedDataUrl === 'data:,') {
                console.error('Failed to generate data URL from cropped canvas');
                alert('Error: Failed to generate cropped image. Please try again.');
                return;
            }

            console.log('Creating fabric image from cropped data...');

            // Optionally reduce image size if setting is enabled
            const addCroppedImage = function(dataUrl) {
            fabric.Image.fromURL(dataUrl, function(img) {
                console.log('Fabric image created:', img);

                if (!img) {
                    console.error('Failed to create fabric image');
                    alert('Error loading cropped image. Please try again.');
                    return;
                }

                // Preserve original position and properties
                const originalLeft = currentCropImageObject.left;
                const originalTop = currentCropImageObject.top;
                const originalAngle = currentCropImageObject.angle || 0;
                const originalOpacity = currentCropImageObject.opacity !== undefined ? currentCropImageObject.opacity : 1;
                const originalShadow = currentCropImageObject.shadow;
                const originalFilters = currentCropImageObject.filters || [];

                console.log('Original properties:', {
                    left: originalLeft,
                    top: originalTop,
                    angle: originalAngle,
                    opacity: originalOpacity
                });

                // Set position and properties
                img.set({
                    left: originalLeft,
                    top: originalTop,
                    angle: originalAngle,
                    opacity: originalOpacity,
                    shadow: originalShadow
                });

                // Apply filters if any
                if (originalFilters.length > 0) {
                    img.filters = originalFilters;
                    img.applyFilters();
                }

                // Calculate scale - use the actual cropped canvas dimensions
                const croppedWidth = croppedCanvas.width;
                const croppedHeight = croppedCanvas.height;

                // Scale to match the cropped dimensions
                const scaleX = croppedWidth / img.width;
                const scaleY = croppedHeight / img.height;

                img.set({
                    scaleX: scaleX,
                    scaleY: scaleY
                });

                console.log('Replacing image on canvas...');

                // Replace the old image with the new cropped one
                canvas.remove(currentCropImageObject);
                canvas.add(img);
                canvas.setActiveObject(img);
                canvas.renderAll();

                console.log('Image replaced successfully');

                // Save state for undo/redo
                if (typeof saveState === 'function') {
                    saveState();
                }

                // Update properties panel
                if (typeof updatePropertiesPanel === 'function') {
                    updatePropertiesPanel(img);
                }

                // Close modal
                closeImageCropModal();

                // Show success message
                console.log('Image cropped successfully!');
            }, {
                crossOrigin: 'anonymous'
            });
            };
            if (typeof reduceImageSize === 'function') {
                reduceImageSize(croppedDataUrl, addCroppedImage);
            } else {
                addCroppedImage(croppedDataUrl);
            }
        } catch (e) {
            console.error('Error in applyCrop:', e);
            alert('Error applying crop: ' + e.message);
        }
    }

    // Make function globally accessible
    window.applyCrop = applyCrop;

    // Style Update Functions
    function updateOpacity(value) {
        if (!currentObject) return;
        const opacity = parseFloat(value) / 100;
        currentObject.set('opacity', opacity);
        canvas.renderAll();

        // Update the number input to stay in sync
        const propOpacityValue = document.getElementById('propOpacityValue');
        if (propOpacityValue) propOpacityValue.value = value;

        saveState();
    }

    function updateBlendMode(value) {
        if (!currentObject) return;
        currentObject.set('globalCompositeOperation', value);
        canvas.renderAll();
        saveState();
    }

    function updateBorderRadius(value) {
        if (!currentObject) return;
        const radius = parseFloat(value);
        if (currentObject.type === 'rect') {
            currentObject.set({
                rx: radius,
                ry: radius
            });
        } else if (currentObject.type === 'image') {
            // For images, we can use clipPath to create rounded corners
            if (radius > 0) {
                const clipPath = new fabric.Rect({
                    width: currentObject.width * currentObject.scaleX,
                    height: currentObject.height * currentObject.scaleY,
                    rx: radius,
                    ry: radius,
                    originX: 'center',
                    originY: 'center'
                });
                currentObject.set('clipPath', clipPath);
            } else {
                currentObject.set('clipPath', null);
            }
        }
        canvas.renderAll();

        // Update the number input
        const propBorderRadiusValue = document.getElementById('propBorderRadiusValue');
        if (propBorderRadiusValue) propBorderRadiusValue.value = value;

        saveState();
    }

    function toggleStroke() {
        if (!currentObject) return;
        const enabled = document.getElementById('propStrokeEnabled').checked;
        const strokeControls = document.getElementById('stylesStrokeControls');

        if (enabled) {
            strokeControls.style.display = 'block';
            const strokeWidth = parseFloat(document.getElementById('propStrokeWidth').value) || 1;
            const strokeColor = document.getElementById('propStrokeColor').value || '#000000';
            const strokePosition = document.getElementById('propStrokePosition') ? document.getElementById('propStrokePosition').value : 'center';
            const opts = { stroke: strokeColor, strokeWidth: strokeWidth, strokePosition: strokePosition };
            if (['rect', 'circle', 'triangle', 'line'].indexOf(currentObject.type) !== -1) {
                opts.strokeUniform = true;
            }
            currentObject.set(opts);
            // Apply stroke position effect
            updateStrokePosition(strokePosition);
        } else {
            strokeControls.style.display = 'none';
            currentObject.set({
                stroke: '',
                strokeWidth: 0
            });
        }
        canvas.renderAll();
        saveState();
    }

    function updateStrokeWidth(value) {
        if (!currentObject) return;
        const width = parseFloat(value);
        currentObject.set('strokeWidth', width);
        if (['rect', 'circle', 'triangle', 'line'].indexOf(currentObject.type) !== -1) {
            currentObject.set('strokeUniform', true);
        }

        // Reapply stroke position if set
        const strokePosition = currentObject.strokePosition || 'center';
        if (strokePosition !== 'center') {
            updateStrokePosition(strokePosition);
        } else {
            canvas.renderAll();
        }

        // Update the number input
        const propStrokeWidthValue = document.getElementById('propStrokeWidthValue');
        if (propStrokeWidthValue) propStrokeWidthValue.value = value;

        saveState();
    }

    function updateStrokeColor(value) {
        if (!currentObject) return;
        currentObject.set('stroke', value);
        canvas.renderAll();
        saveState();
    }

    function updateStrokePosition(value) {
        if (!currentObject) return;

        // Store stroke position as custom property
        currentObject.set('strokePosition', value);

        // Apply stroke position effect by adjusting dimensions
        const strokeWidth = currentObject.strokeWidth || 0;

        if (strokeWidth > 0) {
            if (value === 'inside') {
                // For inside stroke, we keep dimensions but stroke is drawn inward
                // Fabric.js draws stroke centered, so we'll use a workaround with clipPath
                // Store the original dimensions if not already stored
                if (!currentObject._originalWidth) {
                    currentObject._originalWidth = currentObject.width * currentObject.scaleX;
                    currentObject._originalHeight = currentObject.height * currentObject.scaleY;
                }
            } else if (value === 'outside') {
                // For outside stroke, increase dimensions to account for stroke
                if (!currentObject._originalWidth) {
                    currentObject._originalWidth = currentObject.width * currentObject.scaleX;
                    currentObject._originalHeight = currentObject.height * currentObject.scaleY;
                }
                // Adjust scale to make object larger to accommodate outside stroke
                const newWidth = currentObject._originalWidth + (strokeWidth * 2);
                const newHeight = currentObject._originalHeight + (strokeWidth * 2);
                currentObject.set({
                    scaleX: newWidth / currentObject.width,
                    scaleY: newHeight / currentObject.height
                });
            } else {
                // Center (default) - restore original dimensions
                if (currentObject._originalWidth) {
                    currentObject.set({
                        scaleX: currentObject._originalWidth / currentObject.width,
                        scaleY: currentObject._originalHeight / currentObject.height
                    });
                }
            }
        }

        canvas.renderAll();
        saveState();
    }

    function toggleShadow() {
        if (!currentObject) return;
        const enabled = document.getElementById('propShadowEnabled').checked;
        const shadowControls = document.getElementById('stylesShadowControls');

        if (enabled) {
            shadowControls.style.display = 'block';
            const offsetX = parseFloat(document.getElementById('propShadowOffsetX').value) || 5;
            const offsetY = parseFloat(document.getElementById('propShadowOffsetY').value) || 5;
            const blur = parseFloat(document.getElementById('propShadowBlur').value) || 10;
            const color = document.getElementById('propShadowColor').value || '#000000';
            currentObject.set('shadow', {
                offsetX: offsetX,
                offsetY: offsetY,
                blur: blur,
                color: color
            });
        } else {
            shadowControls.style.display = 'none';
            currentObject.set('shadow', null);
        }
        canvas.renderAll();
        saveState();
    }

    function updateShadow() {
        if (!currentObject) return;
        const enabled = document.getElementById('propShadowEnabled').checked;
        if (!enabled) return;

        const offsetX = parseFloat(document.getElementById('propShadowOffsetX').value) || 5;
        const offsetY = parseFloat(document.getElementById('propShadowOffsetY').value) || 5;
        const blur = parseFloat(document.getElementById('propShadowBlur').value) || 10;
        const color = document.getElementById('propShadowColor').value || '#000000';

        currentObject.set('shadow', {
            offsetX: offsetX,
            offsetY: offsetY,
            blur: blur,
            color: color
        });
        canvas.renderAll();
        saveState();
    }

    function toggleEdgeFeather() {
        if (!currentObject) return;
        const enabled = document.getElementById('propEdgeFeatherEnabled').checked;
        const edgeFeatherControls = document.getElementById('edgeFeatherControls');

        if (enabled) {
            edgeFeatherControls.style.display = 'block';
            const featherAmount = parseFloat(document.getElementById('propEdgeFeatherAmount').value) || 10;
            applyEdgeFeather(featherAmount);
        } else {
            edgeFeatherControls.style.display = 'none';
            removeEdgeFeather();
        }
    }

    function updateEdgeFeather(value) {
        if (!currentObject) return;
        const amount = parseFloat(value);
        applyEdgeFeather(amount);

        // Update the number input
        const propEdgeFeatherAmountValue = document.getElementById('propEdgeFeatherAmountValue');
        if (propEdgeFeatherAmountValue) propEdgeFeatherAmountValue.value = value;
    }

    function applyEdgeFeather(amount) {
        if (!currentObject || !canvas) return;

        // Store feather amount
        currentObject.set('edgeFeather', amount);

        // For images, use blur filter
        if (currentObject.type === 'image') {
            // Remove existing blur filters
            if (currentObject.filters) {
                currentObject.filters = currentObject.filters.filter(f => !(f instanceof fabric.Image.filters.Blur));
            } else {
                currentObject.filters = [];
            }

            // Add blur filter for edge feather effect
            if (amount > 0) {
                const blurFilter = new fabric.Image.filters.Blur({ blur: amount / 2 });
                currentObject.filters.push(blurFilter);
                currentObject.applyFilters();
            }
        } else {
            // For shapes, create a mask using clipPath with gradient
            // This is a simplified approach - for better results, we'd need to create a proper mask
            // For now, we'll store the value and apply visual effect through opacity gradient
            if (amount > 0) {
                // Store original opacity if not stored
                if (currentObject._originalOpacity === undefined) {
                    currentObject._originalOpacity = currentObject.opacity !== undefined ? currentObject.opacity : 1;
                }
                // Apply slight opacity reduction at edges (simplified effect)
                // In a full implementation, you'd create a proper mask
            }
        }

        canvas.renderAll();
        saveState();
    }

    function removeEdgeFeather() {
        if (!currentObject || !canvas) return;

        currentObject.set('edgeFeather', 0);

        // Remove blur filters for images
        if (currentObject.type === 'image' && currentObject.filters) {
            currentObject.filters = currentObject.filters.filter(f => !(f instanceof fabric.Image.filters.Blur));
            currentObject.applyFilters();
        }

        // Restore original opacity if stored
        if (currentObject._originalOpacity !== undefined) {
            currentObject.set('opacity', currentObject._originalOpacity);
            currentObject._originalOpacity = undefined;
        }

        canvas.renderAll();
        saveState();
    }

    // Table Editor Functions
    let currentTableObject = null;
    let tableData = [];

    function openTableEditorModal(tableObject) {
        if (!tableObject || tableObject.tableType !== 'table') {
            console.error('Invalid table object');
            return;
        }

        currentTableObject = tableObject;
        const modal = document.getElementById('tableEditorModal');
        if (!modal) {
            console.error('Table editor modal not found');
            return;
        }

        // Extract table data from the group
        extractTableData(tableObject);

        // Render table in editor
        renderTableEditor();

        // Show modal
        modal.style.display = 'block';
    }

    function closeTableEditorModal() {
        const modal = document.getElementById('tableEditorModal');
        if (modal) {
            modal.style.display = 'none';
        }
        currentTableObject = null;
        tableData = [];
    }

    function extractTableData(tableGroup) {
        if (!tableGroup || !tableGroup.getObjects) {
            return;
        }

        const objects = tableGroup.getObjects();
        const rows = tableGroup.tableRows || 3;
        const cols = tableGroup.tableCols || 3;

        // Initialize table data structure
        tableData = [];
        for (let row = 0; row < rows; row++) {
            tableData[row] = [];
            for (let col = 0; col < cols; col++) {
                tableData[row][col] = '';
            }
        }

        // Extract text from objects (every other object is text, starting from index 1)
        let textIndex = 1;
        for (let row = 0; row < rows; row++) {
            for (let col = 0; col < cols; col++) {
                if (textIndex < objects.length && objects[textIndex].type === 'text') {
                    tableData[row][col] = objects[textIndex].text || '';
                }
                textIndex += 2; // Skip to next text (rect, text, rect, text...)
            }
        }

        // Update input fields
        document.getElementById('tableRowsInput').value = rows;
        document.getElementById('tableColsInput').value = cols;
    }

    function renderTableEditor() {
        const header = document.getElementById('tableEditorHeader');
        const body = document.getElementById('tableEditorBody');

        if (!header || !body) return;

        const rows = tableData.length;
        const cols = tableData[0] ? tableData[0].length : 0;

        // Clear existing content
        header.innerHTML = '';
        body.innerHTML = '';

        // Create header row
        const headerRow = document.createElement('tr');
        for (let col = 0; col < cols; col++) {
            const th = document.createElement('th');
            th.style.cssText = 'border: 1px solid #d1d5db; padding: 0.5rem; text-align: left; background: #f3f4f6; font-weight: 600;';
            const input = document.createElement('input');
            input.type = 'text';
            input.value = tableData[0] ? (tableData[0][col] || '') : '';
            input.style.cssText = 'width: 100%; border: none; background: transparent; padding: 0.25rem; font-size: 0.875rem; font-weight: 600;';
            input.oninput = function() {
                if (tableData[0]) {
                    tableData[0][col] = this.value;
                }
            };
            th.appendChild(input);
            headerRow.appendChild(th);
        }
        header.appendChild(headerRow);

        // Create body rows
        for (let row = 1; row < rows; row++) {
            const tr = document.createElement('tr');
            for (let col = 0; col < cols; col++) {
                const td = document.createElement('td');
                td.style.cssText = 'border: 1px solid #d1d5db; padding: 0.5rem;';
                const input = document.createElement('input');
                input.type = 'text';
                input.value = tableData[row] ? (tableData[row][col] || '') : '';
                input.style.cssText = 'width: 100%; border: none; background: transparent; padding: 0.25rem; font-size: 0.875rem;';
                input.oninput = function() {
                    if (!tableData[row]) tableData[row] = [];
                    tableData[row][col] = this.value;
                };
                td.appendChild(input);
                tr.appendChild(td);
            }
            body.appendChild(tr);
        }
    }

    function updateTableStructure() {
        const rowsInput = document.getElementById('tableRowsInput');
        const colsInput = document.getElementById('tableColsInput');

        if (!rowsInput || !colsInput) return;

        const newRows = parseInt(rowsInput.value) || 1;
        const newCols = parseInt(colsInput.value) || 1;

        // Resize table data array
        const oldRows = tableData.length;
        const oldCols = tableData[0] ? tableData[0].length : 0;

        // Add or remove rows
        if (newRows > oldRows) {
            for (let row = oldRows; row < newRows; row++) {
                tableData[row] = [];
                for (let col = 0; col < Math.max(oldCols, newCols); col++) {
                    tableData[row][col] = '';
                }
            }
        } else if (newRows < oldRows) {
            tableData = tableData.slice(0, newRows);
        }

        // Add or remove columns
        for (let row = 0; row < tableData.length; row++) {
            if (newCols > (tableData[row] ? tableData[row].length : 0)) {
                if (!tableData[row]) tableData[row] = [];
                for (let col = tableData[row].length; col < newCols; col++) {
                    tableData[row][col] = '';
                }
            } else if (newCols < (tableData[row] ? tableData[row].length : 0)) {
                tableData[row] = tableData[row].slice(0, newCols);
            }
        }

        renderTableEditor();
    }

    function addTableRow() {
        const rowsInput = document.getElementById('tableRowsInput');
        if (rowsInput) {
            rowsInput.value = parseInt(rowsInput.value) + 1;
            updateTableStructure();
        }
    }

    function addTableColumn() {
        const colsInput = document.getElementById('tableColsInput');
        if (colsInput) {
            colsInput.value = parseInt(colsInput.value) + 1;
            updateTableStructure();
        }
    }

    function removeTableRow() {
        const rowsInput = document.getElementById('tableRowsInput');
        if (rowsInput && parseInt(rowsInput.value) > 1) {
            rowsInput.value = parseInt(rowsInput.value) - 1;
            updateTableStructure();
        }
    }

    function removeTableColumn() {
        const colsInput = document.getElementById('tableColsInput');
        if (colsInput && parseInt(colsInput.value) > 1) {
            colsInput.value = parseInt(colsInput.value) - 1;
            updateTableStructure();
        }
    }

    function applyTableChanges() {
        if (!currentTableObject || !canvas) {
            console.error('No table object or canvas available');
            return;
        }

        const rows = tableData.length;
        const cols = tableData[0] ? tableData[0].length : 0;
        const cellWidth = 100;
        const cellHeight = 40;
        const borderWidth = 1;
        const borderColor = '#000000';
        const headerBgColor = '#f3f4f6';
        const cellBgColor = '#ffffff';
        const textColor = '#000000';
        const fontSize = 14;

        // Get current table position
        const left = currentTableObject.left;
        const top = currentTableObject.top;

        // Create new table objects
        const tableObjects = [];

        for (let row = 0; row < rows; row++) {
            for (let col = 0; col < cols; col++) {
                const cellLeft = col * cellWidth;
                const cellTop = row * cellHeight;
                const isHeader = row === 0;
                const cellText = tableData[row] ? (tableData[row][col] || '') : '';

                // Create cell background rectangle
                const cellRect = new fabric.Rect({
                    left: cellLeft,
                    top: cellTop,
                    width: cellWidth,
                    height: cellHeight,
                    fill: isHeader ? headerBgColor : cellBgColor,
                    stroke: borderColor,
                    strokeWidth: borderWidth,
                    selectable: false,
                    evented: false
                });

                // Create cell text
                const cellTextObj = new fabric.Text(cellText, {
                    left: cellLeft + cellWidth / 2,
                    top: cellTop + cellHeight / 2,
                    fontSize: fontSize,
                    fill: textColor,
                    textAlign: 'center',
                    originX: 'center',
                    originY: 'center',
                    selectable: false,
                    evented: false
                });

                tableObjects.push(cellRect, cellTextObj);
            }
        }

        // Create new group
        const newTableGroup = new fabric.Group(tableObjects, {
            left: left,
            top: top,
            selectable: true,
            hasControls: true,
            hasBorders: true,
            lockUniScaling: false
        });

        // Set table properties
        newTableGroup.set('tableType', 'table');
        newTableGroup.set('tableRows', rows);
        newTableGroup.set('tableCols', cols);

        // Remove old table and add new one
        canvas.remove(currentTableObject);
        canvas.add(newTableGroup);
        canvas.setActiveObject(newTableGroup);
        canvas.renderAll();

        // Close modal
        closeTableEditorModal();

        // Save state
        saveState();
    }

    // Make functions globally accessible
    window.openTableEditorModal = openTableEditorModal;
    window.closeTableEditorModal = closeTableEditorModal;

    // Image Clipping Path Functions
    let clippingCanvas = null;
    let clippingCtx = null;
    let currentClippingImageObject = null;
    let clippingTool = 'pen';
    let clippingBrushSize = 3;
    let isDrawing = false;
    let clippingPath = [];
    let polygonPoints = [];

    function contextMenuClipImage() {
        console.log('contextMenuClipImage called');
        const activeObject = canvas.getActiveObject();

        if (!activeObject) {
            alert('Please select an image first.');
            hideContextMenu();
            return;
        }

        if (activeObject.type !== 'image') {
            alert('Please select an image to clip.');
            hideContextMenu();
            return;
        }

        hideContextMenu();
        setTimeout(function() {
            openImageClippingModal(activeObject);
        }, 50);
    }

    function openImageClippingModal(imageObject) {
        if (!imageObject || imageObject.type !== 'image') {
            alert('Error: Selected object is not an image.');
            return;
        }

        currentClippingImageObject = imageObject;
        const modal = document.getElementById('imageClippingModal');
        const clippingCanvasElement = document.getElementById('clippingCanvas');

        if (!modal || !clippingCanvasElement) {
            alert('Error: Clipping modal elements not found.');
            return;
        }

        // Show modal
        modal.style.display = 'block';

        // Get image source
        let imageSrc = null;
        try {
            const originalImageElement = imageObject.getElement();
            if (originalImageElement) {
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = originalImageElement.naturalWidth;
                tempCanvas.height = originalImageElement.naturalHeight;
                const ctx = tempCanvas.getContext('2d');
                ctx.drawImage(originalImageElement, 0, 0);
                imageSrc = tempCanvas.toDataURL('image/png');
            } else if (imageObject._element && imageObject._element.src) {
                imageSrc = imageObject._element.src;
            }
        } catch (e) {
            console.error('Error getting image source:', e);
        }

        if (!imageSrc) {
            alert('Unable to load image for clipping. Please try again.');
            closeImageClippingModal();
            return;
        }

        // Initialize canvas
        const img = new Image();
        img.onload = function() {
            // Set canvas size to match image (scaled to fit)
            const maxWidth = clippingCanvasElement.parentElement.clientWidth - 40;
            const maxHeight = clippingCanvasElement.parentElement.clientHeight - 40;
            let canvasWidth = img.width;
            let canvasHeight = img.height;

            const scale = Math.min(maxWidth / canvasWidth, maxHeight / canvasHeight, 1);
            canvasWidth = canvasWidth * scale;
            canvasHeight = canvasHeight * scale;

            clippingCanvasElement.width = canvasWidth;
            clippingCanvasElement.height = canvasHeight;
            clippingCanvasElement.style.width = canvasWidth + 'px';
            clippingCanvasElement.style.height = canvasHeight + 'px';

            clippingCtx = clippingCanvasElement.getContext('2d');
            clippingCanvas = clippingCanvasElement;

            // Draw image on canvas
            clippingCtx.drawImage(img, 0, 0, canvasWidth, canvasHeight);

            // Store original image data for reference
            clippingCanvas._originalImage = img;
            clippingCanvas._scale = scale;
            clippingCanvas._originalWidth = img.width;
            clippingCanvas._originalHeight = img.height;

            // Initialize drawing and default to Polygon tool
            initializeClippingDrawing();
            setClippingTool('polygon');
        };
        img.onerror = function() {
            alert('Error loading image into clipping editor.');
            closeImageClippingModal();
        };
        img.src = imageSrc;
    }

    /** Convert mouse position from display (CSS) coordinates to canvas buffer coordinates */
    function getClippingCanvasCoords(e) {
        if (!clippingCanvas) return { x: 0, y: 0 };
        const rect = clippingCanvas.getBoundingClientRect();
        const scaleX = clippingCanvas.width / (rect.width || 1);
        const scaleY = clippingCanvas.height / (rect.height || 1);
        return {
            x: (e.clientX - rect.left) * scaleX,
            y: (e.clientY - rect.top) * scaleY
        };
    }

    function initializeClippingDrawing() {
        if (!clippingCanvas) return;

        clippingPath = [];
        polygonPoints = [];
        isDrawing = false;

        // Mouse events for drawing
        clippingCanvas.addEventListener('mousedown', startClippingDraw);
        clippingCanvas.addEventListener('mousemove', drawClipping);
        clippingCanvas.addEventListener('mouseup', endClippingDraw);
        clippingCanvas.addEventListener('mouseleave', endClippingDraw);
        clippingCanvas.addEventListener('click', handleClippingClick);
    }

    function startClippingDraw(e) {
        if (clippingTool === 'pen') {
            isDrawing = true;
            const { x, y } = getClippingCanvasCoords(e);
            clippingPath = [{x, y}];
        }
    }

    function drawClipping(e) {
        if (!isDrawing || clippingTool !== 'pen') return;

        const { x, y } = getClippingCanvasCoords(e);

        clippingPath.push({x, y});

        // Draw path
        clippingCtx.strokeStyle = '#6366f1';
        clippingCtx.lineWidth = clippingBrushSize;
        clippingCtx.lineCap = 'round';
        clippingCtx.lineJoin = 'round';

        if (clippingPath.length > 1) {
            clippingCtx.beginPath();
            clippingCtx.moveTo(clippingPath[clippingPath.length - 2].x, clippingPath[clippingPath.length - 2].y);
            clippingCtx.lineTo(x, y);
            clippingCtx.stroke();
        }
    }

    function endClippingDraw(e) {
        if (clippingTool === 'pen') {
            isDrawing = false;
        }
    }

    function handleClippingClick(e) {
        if (clippingTool === 'polygon') {
            const { x, y } = getClippingCanvasCoords(e);

            // Check if clicking near the first point to close polygon (in buffer coords, use ~12px tolerance)
            const closeTolerance = 12 * (clippingCanvas ? Math.max(clippingCanvas.width / (clippingCanvas.getBoundingClientRect().width || 1), 1) : 1);
            if (polygonPoints.length >= 3) {
                const firstPoint = polygonPoints[0];
                const distance = Math.sqrt(Math.pow(x - firstPoint.x, 2) + Math.pow(y - firstPoint.y, 2));
                if (distance < closeTolerance) {
                    // Close polygon
                    clippingCtx.strokeStyle = '#6366f1';
                    clippingCtx.lineWidth = 2;
                    clippingCtx.beginPath();
                    clippingCtx.moveTo(polygonPoints[polygonPoints.length - 1].x, polygonPoints[polygonPoints.length - 1].y);
                    clippingCtx.lineTo(firstPoint.x, firstPoint.y);
                    clippingCtx.stroke();
                    return;
                }
            }

            polygonPoints.push({x, y});

            // Draw point
            clippingCtx.fillStyle = '#6366f1';
            clippingCtx.beginPath();
            clippingCtx.arc(x, y, 3, 0, Math.PI * 2);
            clippingCtx.fill();

            // Draw line to previous point
            if (polygonPoints.length > 1) {
                clippingCtx.strokeStyle = '#6366f1';
                clippingCtx.lineWidth = 2;
                clippingCtx.beginPath();
                clippingCtx.moveTo(polygonPoints[polygonPoints.length - 2].x, polygonPoints[polygonPoints.length - 2].y);
                clippingCtx.lineTo(x, y);
                clippingCtx.stroke();
            }

            // Show hint to close polygon
            if (polygonPoints.length >= 3) {
                const firstPoint = polygonPoints[0];
                clippingCtx.fillStyle = 'rgba(99, 102, 241, 0.3)';
                clippingCtx.beginPath();
                clippingCtx.arc(firstPoint.x, firstPoint.y, 10, 0, Math.PI * 2);
                clippingCtx.fill();
            }
        }
    }

    function setClippingTool(tool) {
        clippingTool = tool;

        // Update button styles
        document.getElementById('clippingToolPen').style.background = tool === 'pen' ? '#6366f1' : 'white';
        document.getElementById('clippingToolPen').style.color = tool === 'pen' ? 'white' : '#475569';
        document.getElementById('clippingToolPolygon').style.background = tool === 'polygon' ? '#6366f1' : 'white';
        document.getElementById('clippingToolPolygon').style.color = tool === 'polygon' ? 'white' : '#475569';

        // Change cursor
        if (clippingCanvas) {
            clippingCanvas.style.cursor = tool === 'pen' ? 'crosshair' : 'crosshair';
        }
    }

    function updateClippingBrushSize(value) {
        clippingBrushSize = parseInt(value);
        document.getElementById('clippingBrushSizeValue').textContent = value + 'px';
    }

    function clearClippingPath() {
        if (!clippingCanvas || !clippingCanvas._originalImage) return;

        // Redraw original image
        clippingCtx.clearRect(0, 0, clippingCanvas.width, clippingCanvas.height);
        clippingCtx.drawImage(clippingCanvas._originalImage, 0, 0, clippingCanvas.width, clippingCanvas.height);

        clippingPath = [];
        polygonPoints = [];
    }

    function closeImageClippingModal() {
        const modal = document.getElementById('imageClippingModal');
        if (modal) {
            modal.style.display = 'none';
        }

        // Clean up
        if (clippingCanvas) {
            clippingCanvas.removeEventListener('mousedown', startClippingDraw);
            clippingCanvas.removeEventListener('mousemove', drawClipping);
            clippingCanvas.removeEventListener('mouseup', endClippingDraw);
            clippingCanvas.removeEventListener('mouseleave', endClippingDraw);
            clippingCanvas.removeEventListener('click', handleClippingClick);
        }

        currentClippingImageObject = null;
        clippingCanvas = null;
        clippingCtx = null;
        clippingPath = [];
        polygonPoints = [];
        isDrawing = false;
    }

    function applyClippingPath() {
        if (!currentClippingImageObject || !clippingCanvas) {
            alert('No clipping path drawn. Please draw a path first.');
            return;
        }

        // Get the clipping path (use polygon points if available, otherwise use pen path)
        const path = polygonPoints.length > 0 ? polygonPoints : clippingPath;

        if (path.length < 3) {
            alert('Please draw a complete clipping path (at least 3 points).');
            return;
        }

        try {
            // Create a path string for SVG clipPath
            let pathString = 'M ' + path[0].x + ' ' + path[0].y;
            for (let i = 1; i < path.length; i++) {
                pathString += ' L ' + path[i].x + ' ' + path[i].y;
            }
            pathString += ' Z'; // Close path

            // Scale path back to original image dimensions
            const scale = clippingCanvas._scale || 1;
            const scaledPath = path.map(p => ({
                x: p.x / scale,
                y: p.y / scale
            }));

            // Create SVG path element
            const svgPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            let svgPathString = 'M ' + scaledPath[0].x + ' ' + scaledPath[0].y;
            for (let i = 1; i < scaledPath.length; i++) {
                svgPathString += ' L ' + scaledPath[i].x + ' ' + scaledPath[i].y;
            }
            svgPathString += ' Z';
            svgPath.setAttribute('d', svgPathString);

            // Image dimensions in local (source) space - must match what we used in the modal
            const imgW = clippingCanvas._originalWidth || 1;
            const imgH = clippingCanvas._originalHeight || 1;

            // Create Fabric.js Path object from the clipping path (coordinates in 0..imgW, 0..imgH)
            const fabricPath = new fabric.Path(svgPathString, {
                originX: 'left',
                originY: 'top',
                fill: 'black',
                stroke: 'black',
                strokeWidth: 0
            });
            // Fabric positions clipPath relative to the OBJECT'S CENTER, not top-left.
            // So we must offset the path so that path (0,0) lines up with image top-left.
            // In object local space, center is at (imgW/2, imgH/2). We want path (0,0) at image (0,0).
            // So set path position to (-imgW/2, -imgH/2) so that when Fabric adds object center, we get (0,0).
            fabricPath.set('left', -imgW / 2);
            fabricPath.set('top', -imgH / 2);

            // Apply clipPath to the image
            currentClippingImageObject.set('clipPath', fabricPath);
            currentClippingImageObject.set('dirty', true);
            if (currentClippingImageObject.applyFilters) {
                currentClippingImageObject.applyFilters();
            }
            canvas.renderAll();

            // Close modal
            closeImageClippingModal();

            // Update properties panel if open
            if (typeof updatePropertiesPanel === 'function') {
                updatePropertiesPanel(currentClippingImageObject);
            }

            // Save state - check if function exists in current scope
            try {
                if (typeof saveState === 'function') {
                    saveState();
                } else if (typeof window.saveState === 'function') {
                    window.saveState();
                } else {
                    // Manually save state if saveState is not accessible
                    if (pages && pages.length > 0 && currentPageIndex >= 0 && canvas) {
                        try {
                            const canvasData = canvas.toJSON(['width', 'height', 'backgroundColor']);
                            if (canvasData.objects) {
                                canvasData.objects = canvasData.objects.filter(obj => obj.name !== 'alignmentGuide');
                            }
                            canvasData.width = canvas.getWidth();
                            canvasData.height = canvas.getHeight();
                            canvasData.backgroundColor = canvas.backgroundColor || '#ffffff';
                            pages[currentPageIndex].data = JSON.stringify(canvasData);
                            if (typeof updatePageThumbnail === 'function') {
                                updatePageThumbnail(currentPageIndex);
                            }
                        } catch (e) {
                            console.error('Error saving state:', e);
                        }
                    }
                }
            } catch (e) {
                console.error('Error calling saveState:', e);
            }

            console.log('Clipping path applied successfully!');
        } catch (e) {
            console.error('Error applying clipping path:', e);
            alert('Error applying clipping path: ' + e.message);
        }
    }

    // Make functions globally accessible
    window.contextMenuClipImage = contextMenuClipImage;
    window.openImageClippingModal = openImageClippingModal;
    window.closeImageClippingModal = closeImageClippingModal;
    window.setClippingTool = setClippingTool;
    window.updateClippingBrushSize = updateClippingBrushSize;
    window.clearClippingPath = clearClippingPath;
    window.applyClippingPath = applyClippingPath;
</script>
@endpush
