<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title')@else{{ ($seo['title'] ?? null) ?: site_name() }}@endif</title>
    @include('partials.seo-meta', ['seo' => $seo ?? []])

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @include('layouts.partials.theme-vars')

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
            font-size: 0.9375rem;
            line-height: 1.5;
            padding-left: env(safe-area-inset-left);
            padding-right: env(safe-area-inset-right);
        }

        .navbar {
            background: linear-gradient(135deg, var(--navbar-bg-start) 0%, var(--navbar-bg-end) 100%);
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
            padding: var(--navbar-padding-y) 0;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.25rem;
            color: white !important;
            padding: 0.25rem 0;
        }

        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            font-size: var(--navbar-font-size);
            padding: 0.4rem 0.85rem;
            transition: all 0.3s;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
        }

        .card {
            border: none;
            border-radius: var(--theme-border-radius);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 0.75rem;
        }

        .card-body {
            padding: 1.15rem;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.6rem;
        }

        .btn {
            font-size: 0.9375rem;
            padding: 0.5rem 1.15rem;
            border-radius: var(--theme-btn-border-radius);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            font-weight: 500;
        }

        .btn-lg {
            padding: 0.6rem 1.5rem;
            font-size: 0.9375rem;
        }

        .btn-sm {
            padding: 0.35rem 0.85rem;
            font-size: 0.875rem;
        }

        .form-control, .form-select {
            border-radius: var(--theme-border-radius);
            border: 1px solid var(--border-color);
            padding: 0.5rem 0.85rem;
            font-size: 0.9375rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.4rem;
        }

        .alert {
            border-radius: 4px;
            border: none;
            padding: 0.7rem 1.15rem;
            font-size: 0.9rem;
            margin-bottom: 0.85rem;
        }

        .auth-container {
            min-height: calc(100vh - 150px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.75rem 1rem;
        }

        .auth-card {
            max-width: 400px;
            width: 100%;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-header h2 {
            color: var(--dark-text);
            font-weight: 600;
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
        }

        .mb-3 {
            margin-bottom: 0.9rem !important;
        }

        .mb-4 {
            margin-bottom: 1.15rem !important;
        }

        .mt-3 {
            margin-top: 0.9rem !important;
        }

        .mt-5 {
            margin-top: 1.75rem !important;
        }

        .my-5 {
            margin-top: 1.75rem !important;
            margin-bottom: 1.75rem !important;
        }

        .p-5 {
            padding: 1.5rem !important;
        }

        .g-4 {
            gap: 0.9rem !important;
        }

        .display-5 {
            font-size: 1.65rem;
            font-weight: 600;
        }

        .footer {
            background-color: white;
            border-top: 1px solid var(--border-color);
            padding: 0.9rem 0;
            margin-top: 1.75rem;
            font-size: 0.875rem;
        }

        .dropdown-menu {
            font-size: 0.9375rem;
            padding: 0.35rem 0;
        }

        .dropdown-item {
            padding: 0.5rem 1.15rem;
            font-size: 0.9375rem;
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }

        h1, h2, h3, h4, h5, h6 {
            margin-bottom: 0.6rem;
        }

        .text-muted {
            font-size: 0.875rem;
        }

        .small {
            font-size: 0.8rem;
        }

        hr {
            margin: 0.85rem 0;
        }

        .gap-2 {
            gap: 0.5rem !important;
        }

        .invalid-feedback {
            font-size: 0.8rem;
        }

        .container {
            padding-left: 0.85rem;
            padding-right: 0.85rem;
        }

        @media (min-width: 576px) {
            .container {
                padding-left: 1.15rem;
                padding-right: 1.15rem;
            }
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: white;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        .sidebar-header .brand {
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar-menu {
            padding: 0.75rem 0;
        }

        .sidebar-menu .menu-item {
            display: block;
            padding: 0.65rem 1.25rem;
            color: var(--dark-text);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            font-size: 0.9375rem;
        }

        .sidebar-menu .menu-item:hover {
            background-color: var(--light-bg);
            border-left-color: var(--primary-color);
            color: var(--primary-color);
        }

        .sidebar-menu .menu-item.active {
            background-color: rgba(99, 102, 241, 0.1);
            border-left-color: var(--primary-color);
            color: var(--primary-color);
            font-weight: 500;
        }

        .sidebar-menu .menu-item i {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }

        .sidebar-menu .menu-divider {
            height: 1px;
            background: var(--border-color);
            margin: 0.5rem 1.25rem;
        }

        .sidebar-menu .menu-title {
            padding: 0.75rem 1.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        .sidebar-menu .menu-item.has-submenu {
            cursor: pointer;
            user-select: none;
        }

        .sidebar-menu .menu-item.has-submenu .menu-arrow {
            float: right;
            transition: transform 0.3s ease;
            font-size: 0.75rem;
        }

        .sidebar-menu .menu-item.has-submenu.open .menu-arrow {
            transform: rotate(90deg);
        }

        .sidebar-menu .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background-color: var(--light-bg);
        }

        .sidebar-menu .menu-item.has-submenu.open + .submenu {
            max-height: 500px;
        }

        .sidebar-menu .submenu.show {
            max-height: 500px;
        }

        .sidebar-menu .submenu .menu-item {
            padding-left: 2.5rem;
            font-size: 0.875rem;
        }

        .sidebar-menu .submenu .menu-item.active {
            background-color: rgba(99, 102, 241, 0.15);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background-color: var(--light-bg);
        }

        .content-wrapper {
            padding: 1.5rem;
        }

        .top-navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            z-index: 1000;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 0.75rem 1.5rem;
            margin-bottom: 0;
            transform: translateZ(0);
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        .main-content {
            padding-top: 56px;
        }

        /* Full width when no sidebar (e.g. password reset / forgot password) */
        .main-content.main-content-full-width {
            margin-left: 0;
            padding-top: 0;
        }

        /* Enterprise panel: inbox sidebar replaces user sidebar */
        body.enterprise-panel-page .main-content {
            margin-left: 0;
        }
        body.enterprise-panel-page .top-navbar {
            left: 0;
        }
        body.enterprise-panel-page .content-wrapper {
            padding-left: 0;
        }

        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--dark-text);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
        }

        /* ========== Mobile / App-like UI (768px and below) ========== */
        @media (max-width: 768px) {
            .top-navbar {
                left: 0;
                padding: 0.5rem 0.75rem;
                padding-left: max(0.75rem, env(safe-area-inset-left));
                padding-right: max(0.75rem, env(safe-area-inset-right));
                padding-top: max(0.5rem, env(safe-area-inset-top));
                min-height: 52px;
            }
            .top-navbar .navbar-left-wrap {
                flex: 1;
                min-width: 0;
            }
            .top-navbar .page-title-mobile {
                font-size: 1rem;
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 140px;
            }
            .top-navbar .sidebar-toggle {
                min-width: 44px;
                min-height: 44px;
                padding: 0.5rem;
                margin-right: 0.25rem;
                flex-shrink: 0;
            }
            .top-navbar .create-mega-trigger .create-btn-text { display: none !important; }
            .top-navbar .create-mega-trigger {
                min-width: 44px;
                width: 44px;
                min-height: 44px;
                height: 44px;
                padding: 0;
                margin-right: 0.25rem;
                border-radius: 12px;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
            }
            .top-navbar .create-mega-trigger::after { display: none !important; }
            .top-navbar .create-mega-trigger .create-btn-icon { margin: 0 !important; font-size: 1.1rem; }
            .top-navbar .designer-cta-wrap,
            .top-navbar .designer-revenue-wrap { display: none !important; }
            .top-navbar .topbar-right-wrap {
                flex-shrink: 0;
                display: flex;
                align-items: center;
                gap: 0.25rem;
            }
            .top-navbar .notification-trigger-btn {
                min-width: 44px;
                width: 44px;
                min-height: 44px;
                height: 44px;
                padding: 0;
                border-radius: 12px;
                flex-shrink: 0;
            }
            .top-navbar .notification-trigger-btn i { margin: 0 !important; }
            .top-navbar #notificationDropdown { overflow: visible; }
            .top-navbar .notification-trigger-btn { overflow: visible; }
            .top-navbar .credits-link-mobile {
                min-width: 44px;
                min-height: 44px;
                padding: 0.5rem 0.5rem;
                border-radius: 12px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0.85rem;
                font-weight: 600;
                white-space: nowrap;
            }
            .top-navbar .credits-link-mobile .credits-amount-only { display: inline; }
            .top-navbar .credits-link-mobile .credits-label-mobile { display: none; }
            .top-navbar .credits-link-mobile .me-2 { margin-right: 0.35rem !important; }
            .top-navbar .user-dropdown-mobile {
                min-width: 44px;
                min-height: 44px;
                padding: 0.5rem;
                border-radius: 12px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .top-navbar .user-dropdown-mobile .user-name-mobile { display: none; }
            .top-navbar .topbar-user-dropdown { display: none !important; }
            .top-navbar .topbar-credits-wrap { display: none !important; }
            .sidebar {
                transform: translateX(-100%);
                width: min(320px, 85vw);
                max-width: 100%;
                box-shadow: 4px 0 24px rgba(0,0,0,0.15);
                padding-left: env(safe-area-inset-left);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .sidebar .sidebar-header {
                padding-top: max(1rem, env(safe-area-inset-top));
            }
            .sidebar-menu .menu-item {
                min-height: 48px;
                padding: 0.75rem 1.25rem;
                display: flex;
                align-items: center;
            }
            .main-content {
                margin-left: 0;
                padding-top: 52px;
                padding-bottom: env(safe-area-inset-bottom);
            }
            .main-content.has-bottom-nav {
                padding-bottom: calc(64px + env(safe-area-inset-bottom));
            }
            .sidebar-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .content-wrapper {
                padding: 1rem;
                padding-left: max(1rem, env(safe-area-inset-left));
                padding-right: max(1rem, env(safe-area-inset-right));
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }
            .sidebar-overlay.show {
                background: rgba(0,0,0,0.4);
                -webkit-tap-highlight-color: transparent;
            }
            #chatWidget { display: none !important; }
            /* Multi-page design tool: hide top bar and bottom nav on mobile */
            body.design-editor-page .top-navbar { display: none !important; }
            body.design-editor-page .main-content { padding-top: 0 !important; padding-bottom: 0 !important; }
            body.design-editor-page .content-wrapper { padding: 0 !important; }
            body.design-editor-page .bottom-nav { display: none !important; }
        }
        @media (max-width: 576px) {
            .top-navbar .page-title-mobile { max-width: 100px; }
            #notificationMenu.dropdown-menu {
                width: min(320px, calc(100vw - 1rem));
                max-height: 70vh;
            }
        }
        /* Bottom navigation - mobile app style */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1020;
            background: #fff;
            box-shadow: 0 -2px 12px rgba(0,0,0,0.08);
            padding-bottom: env(safe-area-inset-bottom);
            padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
        }
        @media (max-width: 768px) {
            .bottom-nav {
                display: flex;
                align-items: center;
                justify-content: space-around;
                padding: 0.5rem 0.25rem 0;
                min-height: 56px;
            }
            .bottom-nav a {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 0.4rem 0.25rem;
                color: #64748b;
                text-decoration: none;
                font-size: 0.65rem;
                font-weight: 500;
                min-height: 48px;
                border-radius: 10px;
                transition: color 0.2s, background 0.2s;
                -webkit-tap-highlight-color: transparent;
            }
            .bottom-nav a:hover { color: var(--primary-color); background: rgba(99, 102, 241, 0.06); }
            .bottom-nav a.active { color: var(--primary-color); }
            .bottom-nav a i {
                font-size: 1.25rem;
                margin-bottom: 0.2rem;
            }
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Professional Navbar Styles */
        .professional-navbar {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .professional-nav-link {
            position: relative;
            color: rgba(255, 255, 255, 0.95) !important;
            font-weight: 500;
        }

        .professional-nav-link:hover {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1) !important;
        }

        .professional-nav-link.active {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.15) !important;
        }

        .navbar-brand:hover {
            opacity: 0.9;
            transform: scale(1.02);
            transition: all 0.3s;
        }

        /* Mega Menu Styles */
        .mega-menu {
            left: 50% !important;
            transform: translateX(-50%);
            margin-top: 0.75rem !important;
            animation: fadeInDown 0.3s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        .mega-menu .row {
            margin: 0;
        }

        .mega-menu .col-md-4 {
            padding: 0 1.25rem;
        }

        .mega-menu h6 {
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 1.25rem;
        }

        .professional-menu-link:hover {
            background-color: #f8fafc !important;
            color: #6366f1 !important;
            transform: translateX(4px);
        }

        .professional-menu-link span {
            font-weight: 500;
        }

        @media (max-width: 991px) {
            .mega-menu {
                width: 100% !important;
                left: 0 !important;
                transform: none !important;
                margin-top: 0.5rem !important;
                padding: 1.5rem !important;
            }

            .professional-nav-link {
                padding: 0.75rem 1rem !important;
            }
        }

        /* Create Mega Menu Trigger Button */
        .create-mega-trigger {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            color: white !important;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.35);
            transition: all 0.2s ease;
        }

        .create-mega-trigger:hover {
            background: linear-gradient(135deg, #5558e3 0%, #7c3aed 100%);
            color: white !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.45);
            transform: translateY(-1px);
        }

        /* User Panel Create Mega Menu - Professional */
        .create-mega-menu.dropdown-menu {
            width: min(720px, calc(100vw - 2rem));
            padding: 0;
            border: 1px solid rgba(99, 102, 241, 0.12);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.03);
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .create-mega-menu .mega-menu-content {
            min-width: 100%;
            padding: 1.5rem 1.5rem 1.25rem;
            background: #fff;
        }

        .create-mega-menu .mega-menu-section {
            padding: 0 1rem 0 0;
        }

        .create-mega-menu .mega-menu-title {
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 1rem;
            padding-bottom: 0.6rem;
            border-bottom: 1px solid var(--border-color);
        }

        .create-mega-menu .mega-menu-link {
            display: flex;
            align-items: flex-start;
            padding: 0.75rem 0.85rem;
            color: var(--dark-text);
            text-decoration: none;
            font-size: 0.9rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            margin-bottom: 0.15rem;
        }

        .create-mega-menu .mega-menu-link:hover {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(139, 92, 246, 0.08) 100%);
            color: var(--primary-color);
        }

        .create-mega-menu .mega-menu-link i {
            width: 32px;
            height: 32px;
            min-width: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
            border-radius: 8px;
            margin-right: 0.9rem;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .create-mega-menu .mega-menu-link:hover i {
            background: rgba(99, 102, 241, 0.18);
        }

        .create-mega-menu .mega-menu-link-text {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
        }

        .create-mega-menu .mega-menu-link-title {
            font-weight: 600;
            font-size: 0.9rem;
            color: inherit;
        }

        .create-mega-menu .mega-menu-link-desc {
            font-size: 0.75rem;
            color: #64748b;
            line-height: 1.35;
        }

        .create-mega-menu .mega-menu-link:hover .mega-menu-link-desc {
            color: #6366f1;
            opacity: 0.9;
        }

        .create-mega-menu .mega-menu-footer {
            border-color: var(--border-color) !important;
            padding: 1rem 1.5rem 1.25rem;
            background: #fafbfc;
        }

        .create-mega-menu .mega-menu-footer .btn {
            font-weight: 600;
            padding: 0.65rem 1.25rem;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .create-mega-menu.dropdown-menu {
                width: calc(100vw - 1rem);
            }
        }
    </style>

    @stack('styles')
</head>
<body class="@if(request()->routeIs('enterprise*')) enterprise-panel-page @endif @if(request()->routeIs('design.create')) design-editor-page @endif">
    @auth
    @if(!request()->routeIs('enterprise*') && !request()->routeIs('password.request') && !request()->routeIs('password.reset'))
    <!-- Sidebar (hidden on forgot/reset password pages) -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('design.templates.explore') }}" class="brand">
                <i class="fas fa-book-open me-2"></i>{{ site_name() }}
            </a>
        </div>
        <nav class="sidebar-menu">
            <div class="menu-title">Main Menu</div>
            <a href="{{ route('design.templates.explore') }}" class="menu-item {{ request()->routeIs('design.templates.explore') ? 'active' : '' }}">
                <i class="fas fa-compass"></i>
                <span>Explore Templates</span>
            </a>
            <a href="{{ route('design.index') }}" class="menu-item {{ request()->routeIs('design.index') ? 'active' : '' }}">
                <i class="fas fa-palette"></i>
                <span>Design Tool</span>
            </a>
            <a href="{{ route('design.templates.page') }}" class="menu-item {{ request()->routeIs('design.templates.page') ? 'active' : '' }}">
                <i class="fas fa-folder-open"></i>
                <span>My Templates</span>
            </a>
            <a href="{{ route('orders.index') }}" class="menu-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span>My Orders</span>
            </a>
            <a href="{{ route('credits.index') }}" class="menu-item {{ request()->routeIs('credits.*') ? 'active' : '' }}">
                <i class="fas fa-wallet"></i>
                <span>Top Up Credits</span>
            </a>
            <a href="{{ route('enterprise') }}" class="menu-item {{ request()->routeIs('enterprise*') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                <span>Enterprise</span>
            </a>

            <div class="menu-divider"></div>

            @if(Auth::user()->hasRole('admin'))
            <div class="menu-title">Administration</div>
            <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i>
                <span>Admin Panel</span>
            </a>
            <a href="{{ route('admin.orders') }}" class="menu-item {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
            </a>
            <div class="menu-divider"></div>
            @endif

            <div class="menu-title">Account</div>
            <a href="{{ route('user.settings') }}" class="menu-item {{ request()->routeIs('user.settings*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="{{ route('user.address-book') }}" class="menu-item {{ request()->routeIs('user.address-book*') ? 'active' : '' }}">
                <i class="fas fa-address-book"></i>
                <span>Address Book</span>
            </a>
        </nav>
    </aside>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    @endif

    <!-- Main Content (full width on password reset/forgot so no sidebar) -->
    <div class="main-content @if(request()->routeIs('password.request') || request()->routeIs('password.reset')) main-content-full-width @endif @if(!request()->routeIs('password.request') && !request()->routeIs('password.reset') && !request()->routeIs('enterprise*')) has-bottom-nav @endif">
        <!-- Top Navbar (hidden on password reset/forgot) -->
        @if(!request()->routeIs('password.request') && !request()->routeIs('password.reset'))
        <nav class="top-navbar">
                <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center navbar-left-wrap">
                    @if(!request()->routeIs('enterprise*'))
                    <button class="sidebar-toggle me-3" onclick="toggleSidebar()" aria-label="Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    @endif
                    <h5 class="mb-0 me-4 page-title-mobile" style="font-size: 1.1rem;">@yield('page-title', 'Dashboard')</h5>
                    @if(!auth()->user()->hasRole('designer'))
                    @if(!request()->routeIs('enterprise*'))
                    <span class="designer-cta-wrap">
                    <a href="{{ route('designer-application.index') }}" class="btn btn-sm btn-outline-primary me-3 d-flex align-items-center" style="font-weight: 600; font-size: 0.8rem;">
                        <i class="fas fa-palette me-1"></i>Become a Designer
                    </a>
                    </span>
                    @endif
                    @else
                    <span class="d-flex align-items-center me-3 designer-revenue-wrap" style="font-size: 0.9rem; font-weight: 600; color: #059669;">
                        <i class="fas fa-coins me-1"></i>Template revenue: {{ format_price($designerTemplateRevenue ?? 0) }}
                    </span>
                    @endif
                    @if(!request()->routeIs('enterprise*'))
                    <!-- Create Mega Menu -->
                    <div class="dropdown">
                        <a class="btn btn-sm dropdown-toggle d-flex align-items-center create-mega-trigger" href="#" id="createMegaMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-plus create-btn-icon"></i><span class="create-btn-text">Create</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-start create-mega-menu" aria-labelledby="createMegaMenu">
                            <div class="mega-menu-content">
                                <div class="row g-0">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="mega-menu-section">
                                            <h6 class="mega-menu-title"><i class="fas fa-file-alt me-2"></i>Documents</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li>
                                                    <a href="{{ route('design.create', ['multi' => 'true', 'type' => 'letter']) }}" class="mega-menu-link">
                                                        <i class="fas fa-envelope"></i>
                                                        <span class="mega-menu-link-text">
                                                            <span class="mega-menu-link-title">Create a Letter</span>
                                                            <span class="mega-menu-link-desc">Professional letters & correspondence</span>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('design.create', ['multi' => 'true', 'type' => 'document']) }}" class="mega-menu-link">
                                                        <i class="fas fa-file-word"></i>
                                                        <span class="mega-menu-link-text">
                                                            <span class="mega-menu-link-title">Create a Document</span>
                                                            <span class="mega-menu-link-desc">Reports, proposals & more</span>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('design.create', ['multi' => 'true', 'type' => 'pdf']) }}" class="mega-menu-link">
                                                        <i class="fas fa-file-pdf"></i>
                                                        <span class="mega-menu-link-text">
                                                            <span class="mega-menu-link-title">Create a PDF</span>
                                                            <span class="mega-menu-link-desc">Export designs as PDF files</span>
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="mega-menu-section">
                                            <h6 class="mega-menu-title"><i class="fas fa-book me-2"></i>Flip Book & Cards</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li>
                                                    <a href="{{ route('flipbooks.create') }}" class="mega-menu-link">
                                                        <i class="fas fa-book-open"></i>
                                                        <span class="mega-menu-link-text">
                                                            <span class="mega-menu-link-title">Create a Flip Book</span>
                                                            <span class="mega-menu-link-desc">Interactive digital publications</span>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('design.create', ['multi' => 'true', 'type' => 'visiting-cards']) }}" class="mega-menu-link">
                                                        <i class="fas fa-id-card"></i>
                                                        <span class="mega-menu-link-text">
                                                            <span class="mega-menu-link-title">Create Visiting Cards</span>
                                                            <span class="mega-menu-link-desc">Business cards & name cards</span>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('design.create', ['multi' => 'true', 'type' => 'brochure']) }}" class="mega-menu-link">
                                                        <i class="fas fa-file-alt"></i>
                                                        <span class="mega-menu-link-text">
                                                            <span class="mega-menu-link-title">Create a Brochure</span>
                                                            <span class="mega-menu-link-desc">Marketing & promotional materials</span>
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="mega-menu-section">
                                            <h6 class="mega-menu-title"><i class="fas fa-th-large me-2"></i>Templates</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li>
                                                    <a href="{{ route('design.create', ['multi' => 'true', 'type' => 'template']) }}" class="mega-menu-link">
                                                        <i class="fas fa-magic"></i>
                                                        <span class="mega-menu-link-text">
                                                            <span class="mega-menu-link-title">Create from Template</span>
                                                            <span class="mega-menu-link-desc">Start with pre-designed layouts</span>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('design.create', ['multi' => 'true', 'type' => 'blank']) }}" class="mega-menu-link">
                                                        <i class="fas fa-palette"></i>
                                                        <span class="mega-menu-link-text">
                                                            <span class="mega-menu-link-title">Create from Scratch</span>
                                                            <span class="mega-menu-link-desc">Blank canvas design tool</span>
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="mega-menu-footer mt-0 pt-0 border-top d-flex gap-2 flex-wrap">
                                    <a href="{{ route('design.templates.explore') }}" class="btn btn-primary btn-sm flex-grow-1">
                                        <i class="fas fa-compass me-2"></i>Explore All Templates
                                    </a>
                                    <a href="{{ route('design.templates.page') }}" class="btn btn-outline-primary btn-sm flex-grow-1">
                                        <i class="fas fa-folder-open me-2"></i>My Templates
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="d-flex align-items-center topbar-right-wrap">
                    <!-- Notifications -->
                    @php
                        $user = auth()->user();
                        $unreadNotificationCount = $user->unreadNotifications()->count();
                    @endphp
                    <div class="dropdown me-2 me-md-3" id="notificationDropdown">
                        <a class="position-relative d-flex align-items-center justify-content-center text-dark text-decoration-none rounded notification-trigger-btn" href="#" id="notificationTrigger" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: #f1f5f9;" title="Notifications">
                            <i class="fas fa-bell" style="font-size: 1.1rem;"></i>
                            @if($unreadNotificationCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="font-size: 0.65rem; min-width: 1.1rem;">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                            @else
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="notificationBadge" style="font-size: 0.65rem; min-width: 1.1rem;">0</span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow border-0 py-0" id="notificationMenu" style="width: 320px; max-height: 380px;" aria-labelledby="notificationTrigger">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom bg-light">
                                <strong class="small">Notifications</strong>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="notificationMarkAllRead" style="font-size: 0.8rem;">Mark all read</button>
                            </div>
                            <div id="notificationList" class="overflow-auto" style="max-height: 300px;">
                                <div id="notificationListLoading" class="text-center py-4 text-muted small">Loading...</div>
                                <div id="notificationListItems"></div>
                                <div id="notificationListEmpty" class="text-center py-4 text-muted small d-none">No notifications yet.</div>
                            </div>
                        </div>
                    </div>
                    <span class="topbar-credits-wrap">
                    <a href="{{ route('credits.index') }}" class="d-flex align-items-center text-decoration-none text-dark me-3 px-3 py-2 rounded credits-link-mobile" style="background: #f1f5f9; font-size: 0.9rem; font-weight: 600;" title="Top Up Credits">
                        <i class="fas fa-wallet me-2" style="color: #6366f1;"></i>
                        <span class="credits-amount-only">{{ format_price(auth()->user()->balance ?? 0) }}</span>
                        <span class="credits-label-mobile d-none">Credits</span>
                    </a>
                    </span>
                    <div class="dropdown topbar-user-dropdown">
                        <a class="text-decoration-none text-dark dropdown-toggle user-dropdown-mobile d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i>
                            <span class="user-name-mobile">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('enterprise') }}"><i class="fas fa-building me-2"></i>Enterprise</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('user.settings') }}"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        @endif

        <!-- Bottom navigation (mobile app-like) -->
        @if(!request()->routeIs('password.request') && !request()->routeIs('password.reset') && !request()->routeIs('enterprise*'))
        <nav class="bottom-nav" aria-label="Main navigation">
            <a href="{{ route('design.templates.explore') }}" class="{{ request()->routeIs('design.templates.explore') ? 'active' : '' }}"><i class="fas fa-compass"></i><span>Explore</span></a>
            <a href="{{ route('design.templates.page') }}" class="{{ request()->routeIs('design.templates.page') ? 'active' : '' }}"><i class="fas fa-folder-open"></i><span>Templates</span></a>
            <a href="{{ route('design.index') }}" class="{{ request()->routeIs('design.index') ? 'active' : '' }}"><i class="fas fa-palette"></i><span>Design</span></a>
            <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'active' : '' }}"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
            <a href="{{ route('credits.index') }}" class="{{ request()->routeIs('credits.*') ? 'active' : '' }}"><i class="fas fa-wallet"></i><span>Credits</span></a>
        </nav>
        @endif

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>

        @if(auth()->user()->needsContactDetails())
        @if(request()->routeIs('design.templates.explore'))
        <script>window.__deferRequiredContactUntilExploreIntro = true;</script>
        @endif
        <div class="modal fade" id="requiredContactModal" tabindex="-1" aria-labelledby="requiredContactModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="requiredContactModalLabel"><i class="fas fa-id-card me-2 text-primary"></i>Complete your profile</h5>
                    </div>
                    <div class="modal-body pt-2">
                        <p class="text-muted small mb-3">Please add your phone number and address so we can reach you for orders and deliveries.</p>
                        @if($errors->has('phone') || $errors->has('address'))
                        <div class="alert alert-danger small py-2 mb-3">
                            @foreach($errors->get('phone') as $e)<div>{{ $e }}</div>@endforeach
                            @foreach($errors->get('address') as $e)<div>{{ $e }}</div>@endforeach
                        </div>
                        @endif
                        <form action="{{ route('user.required-contact') }}" method="POST" id="requiredContactForm">
                            @csrf
                            <div class="mb-3">
                                <label for="required_phone" class="form-label fw-semibold small">Phone number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="required_phone" name="phone" required maxlength="40" placeholder="e.g. +1 234 567 8900" value="{{ old('phone', auth()->user()->phone) }}">
                            </div>
                            <div class="mb-3">
                                <label for="required_address" class="form-label fw-semibold small">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="required_address" name="address" rows="3" required maxlength="2000" placeholder="Street, city, postal code, country">{{ old('address', auth()->user()->address) }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                                <i class="fas fa-check me-2"></i>Save and continue
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endauth

    @guest
    <!-- Navbar for Guests -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top professional-navbar" style="background: linear-gradient(135deg, var(--navbar-bg-start) 0%, var(--navbar-bg-end) 100%); box-shadow: 0 4px 20px rgba(0,0,0,0.12); z-index: 1000; padding: var(--navbar-padding-y) 0;">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}" style="font-weight: 700; font-size: 1.35rem; letter-spacing: -0.5px; padding: 0.25rem 0;">
                <i class="fas fa-book-open me-2" style="font-size: 1.2rem;"></i>{{ site_name() }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" style="border: 2px solid rgba(255,255,255,0.3); border-radius: 6px; padding: 0.3rem 0.5rem;">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center" style="gap: 0.25rem;">
                    <li class="nav-item">
                        <a class="nav-link professional-nav-link" href="{{ route('home') }}" style="font-weight: 500; font-size: 0.875rem; padding: 0.45rem 0.85rem; border-radius: 6px; transition: all 0.3s;">
                            Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link professional-nav-link" href="{{ route('services') }}" style="font-weight: 500; font-size: 0.875rem; padding: 0.45rem 0.85rem; border-radius: 6px; transition: all 0.3s;">
                            Our Services
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle professional-nav-link" href="#" id="templatesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-weight: 500; font-size: 0.875rem; padding: 0.45rem 0.85rem; border-radius: 6px; transition: all 0.3s;">
                            Templates
                        </a>
                        <div class="dropdown-menu mega-menu" aria-labelledby="templatesDropdown" style="width: 850px; padding: 2.5rem; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2); border-radius: 16px; margin-top: 1rem; background: white;">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="mb-4" style="font-weight: 700; color: #1e293b; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; padding-bottom: 0.75rem; border-bottom: 2px solid #e2e8f0;">
                                        <i class="fas fa-briefcase me-2" style="color: #6366f1;"></i>Business
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-3">
                                            <a href="{{ route('templates') }}?category=business-portfolio" class="text-decoration-none d-flex align-items-center professional-menu-link" style="color: #475569; font-size: 0.9rem; transition: all 0.2s; padding: 0.5rem; border-radius: 8px;">
                                                <i class="fas fa-briefcase me-3" style="width: 24px; color: #6366f1; font-size: 0.85rem;"></i>
                                                <span>Business Portfolio</span>
                                            </a>
                                        </li>
                                        <li class="mb-3">
                                            <a href="{{ route('templates') }}?category=annual-report" class="text-decoration-none d-flex align-items-center professional-menu-link" style="color: #475569; font-size: 0.9rem; transition: all 0.2s; padding: 0.5rem; border-radius: 8px;">
                                                <i class="fas fa-chart-line me-3" style="width: 24px; color: #6366f1; font-size: 0.85rem;"></i>
                                                <span>Annual Report</span>
                                            </a>
                                        </li>
                                        <li class="mb-3">
                                            <a href="{{ route('templates') }}?category=company-profile" class="text-decoration-none d-flex align-items-center professional-menu-link" style="color: #475569; font-size: 0.9rem; transition: all 0.2s; padding: 0.5rem; border-radius: 8px;">
                                                <i class="fas fa-building me-3" style="width: 24px; color: #6366f1; font-size: 0.85rem;"></i>
                                                <span>Company Profile</span>
                                            </a>
                                        </li>
                                        <li class="mb-3">
                                            <a href="{{ route('templates') }}?category=product-catalog" class="text-decoration-none d-flex align-items-center professional-menu-link" style="color: #475569; font-size: 0.9rem; transition: all 0.2s; padding: 0.5rem; border-radius: 8px;">
                                                <i class="fas fa-shopping-bag me-3" style="width: 24px; color: #6366f1; font-size: 0.85rem;"></i>
                                                <span>Product Catalog</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="mb-4" style="font-weight: 700; color: #1e293b; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; padding-bottom: 0.75rem; border-bottom: 2px solid #e2e8f0;">
                                        <i class="fas fa-palette me-2" style="color: #6366f1;"></i>Creative
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-3">
                                            <a href="{{ route('templates') }}?category=portfolio" class="text-decoration-none d-flex align-items-center professional-menu-link" style="color: #475569; font-size: 0.9rem; transition: all 0.2s; padding: 0.5rem; border-radius: 8px;">
                                                <i class="fas fa-user-circle me-3" style="width: 24px; color: #6366f1; font-size: 0.85rem;"></i>
                                                <span>Portfolio</span>
                                            </a>
                                        </li>
                                        <li class="mb-3">
                                            <a href="{{ route('templates') }}?category=photo-gallery" class="text-decoration-none d-flex align-items-center professional-menu-link" style="color: #475569; font-size: 0.9rem; transition: all 0.2s; padding: 0.5rem; border-radius: 8px;">
                                                <i class="fas fa-camera me-3" style="width: 24px; color: #6366f1; font-size: 0.85rem;"></i>
                                                <span>Photo Gallery</span>
                                            </a>
                                        </li>
                                        <li class="mb-3">
                                            <a href="{{ route('templates') }}?category=magazine" class="text-decoration-none d-flex align-items-center professional-menu-link" style="color: #475569; font-size: 0.9rem; transition: all 0.2s; padding: 0.5rem; border-radius: 8px;">
                                                <i class="fas fa-newspaper me-3" style="width: 24px; color: #6366f1; font-size: 0.85rem;"></i>
                                                <span>Magazine</span>
                                            </a>
                                        </li>
                                        <li class="mb-3">
                                            <a href="{{ route('templates') }}?category=brochure" class="text-decoration-none d-flex align-items-center professional-menu-link" style="color: #475569; font-size: 0.9rem; transition: all 0.2s; padding: 0.5rem; border-radius: 8px;">
                                                <i class="fas fa-file-alt me-3" style="width: 24px; color: #6366f1; font-size: 0.85rem;"></i>
                                                <span>Brochure</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="mb-4" style="font-weight: 700; color: #1e293b; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; padding-bottom: 0.75rem; border-bottom: 2px solid #e2e8f0;">
                                        <i class="fas fa-graduation-cap me-2" style="color: #6366f1;"></i>Education
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-3">
                                            <a href="{{ route('templates') }}?category=course-material" class="text-decoration-none d-flex align-items-center professional-menu-link" style="color: #475569; font-size: 0.9rem; transition: all 0.2s; padding: 0.5rem; border-radius: 8px;">
                                                <i class="fas fa-graduation-cap me-3" style="width: 24px; color: #6366f1; font-size: 0.85rem;"></i>
                                                <span>Course Material</span>
                                            </a>
                                        </li>
                                        <li class="mb-3">
                                            <a href="{{ route('templates') }}?category=textbook" class="text-decoration-none d-flex align-items-center professional-menu-link" style="color: #475569; font-size: 0.9rem; transition: all 0.2s; padding: 0.5rem; border-radius: 8px;">
                                                <i class="fas fa-book me-3" style="width: 24px; color: #6366f1; font-size: 0.85rem;"></i>
                                                <span>Textbook</span>
                                            </a>
                                        </li>
                                        <li class="mb-3">
                                            <a href="{{ route('templates') }}?category=presentation" class="text-decoration-none d-flex align-items-center professional-menu-link" style="color: #475569; font-size: 0.9rem; transition: all 0.2s; padding: 0.5rem; border-radius: 8px;">
                                                <i class="fas fa-chalkboard-teacher me-3" style="width: 24px; color: #6366f1; font-size: 0.85rem;"></i>
                                                <span>Presentation</span>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="mt-4 pt-4" style="border-top: 2px solid #e2e8f0;">
                                        <a href="{{ route('templates') }}" class="btn w-100" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white; border: none; font-weight: 600; border-radius: 10px; padding: 0.75rem 1.5rem; transition: all 0.3s; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(99, 102, 241, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.3)'">
                                            <i class="fas fa-th me-2"></i>View All Templates
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link professional-nav-link" href="{{ route('contact') }}" style="font-weight: 500; font-size: 0.875rem; padding: 0.45rem 0.85rem; border-radius: 6px; transition: all 0.3s;">
                            Contact Us
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link professional-nav-link" href="{{ route('docs.index') }}" style="font-weight: 500; font-size: 0.875rem; padding: 0.45rem 0.85rem; border-radius: 6px; transition: all 0.3s;">
                            Documentation
                        </a>
                    </li>
                    <li class="nav-item ms-2 d-none d-lg-block">
                        <div class="position-relative" style="width: 200px;">
                            <input type="text" id="navbarSearch" class="form-control" placeholder="Search..." style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white; font-size: 0.875rem; padding: 0.45rem 2.5rem 0.45rem 0.85rem; border-radius: 8px; transition: all 0.3s; width: 100%;" onfocus="this.style.background='rgba(255,255,255,0.25)'; this.style.borderColor='rgba(255,255,255,0.4)'" onblur="this.style.background='rgba(255,255,255,0.15)'; this.style.borderColor='rgba(255,255,255,0.2)'" onkeypress="if(event.key === 'Enter') { handleSearch(event); }">
                            <i class="fas fa-search" id="searchIcon" style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.8); font-size: 0.875rem; pointer-events: none;"></i>
                            <style>
                                #navbarSearch::placeholder {
                                    color: rgba(255,255,255,0.7);
                                }
                            </style>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link professional-nav-link" href="{{ route('login') }}" style="font-weight: 500; font-size: 0.875rem; padding: 0.45rem 0.85rem; border-radius: 6px; transition: all 0.3s;">
                            Login
                        </a>
                    </li>
                    @if(allow_registration())
                    <li class="nav-item">
                        <a class="btn ms-2" href="{{ route('register') }}" style="background: white; color: #6366f1; border: none; font-weight: 600; font-size: 0.875rem; padding: 0.5rem 1.25rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(255,255,255,0.2); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(255,255,255,0.3)'; this.style.background='#f8fafc'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(255,255,255,0.2)'; this.style.background='white'">
                            <i class="fas fa-user-plus me-1"></i>Sign Up
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @if(session('success'))
            <div class="container mt-2">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mt-2">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="container mt-2">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        <div class="container text-center">
            <p class="mb-1 text-muted">&copy; {{ date('Y') }} {{ site_name() }}. All rights reserved.</p>
            <p class="mb-0 small">
                <a href="{{ route('docs.index') }}" class="text-muted text-decoration-none">Documentation</a>
                @guest
                &middot; <a href="{{ route('contact') }}" class="text-muted text-decoration-none">Contact</a>
                &middot; <a href="{{ route('login') }}" class="text-muted text-decoration-none">Login</a>
                @endguest
            </p>
        </div>
    </footer>
    @endguest

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @auth
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (!sidebar || !overlay) return;
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        function toggleSubmenu(element) {
            element.classList.toggle('open');
            const submenu = element.nextElementSibling;
            if (submenu && submenu.classList.contains('submenu')) {
                if (element.classList.contains('open')) {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                } else {
                    submenu.style.maxHeight = '0';
                }
            }
        }

        // Initialize submenu state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const activeSubmenu = document.querySelector('.menu-item.has-submenu.open');
            if (activeSubmenu) {
                const submenu = activeSubmenu.nextElementSibling;
                if (submenu && submenu.classList.contains('submenu')) {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                }
            }
        });

        // Close sidebar when clicking outside on mobile
        var sidebarOverlayEl = document.getElementById('sidebarOverlay');
        if (sidebarOverlayEl) sidebarOverlayEl.addEventListener('click', function() { toggleSidebar(); });

        // Close sidebar on window resize if it's desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                if (sidebar) sidebar.classList.remove('show');
                if (overlay) overlay.classList.remove('show');
            }
        });
    </script>
    @endauth

    @stack('scripts')

    <!-- Chat Popup Widget -->
    <div id="chatWidget" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050;">
        <!-- Chat Button -->
        <button id="chatButton" onclick="toggleChat()" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border: none; color: white; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4); transition: all 0.3s; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 25px rgba(99, 102, 241, 0.5)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 20px rgba(99, 102, 241, 0.4)'">
            <i class="fas fa-comments" id="chatIcon"></i>
        </button>

        <!-- Chat Window -->
        <div id="chatWindow" style="display: none; position: absolute; bottom: 80px; right: 0; width: 380px; height: 500px; background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden; flex-direction: column;">
            <!-- Chat Header -->
            <div style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); padding: 1.25rem; color: white; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div>
                        <h6 style="margin: 0; font-weight: 700; font-size: 1rem;">Support Team</h6>
                        <p style="margin: 0; font-size: 0.75rem; opacity: 0.9;">We're here to help</p>
                    </div>
                </div>
                <button onclick="toggleChat()" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Chat Messages -->
            <div id="chatMessages" style="flex: 1; padding: 1.25rem; overflow-y: auto; background: #f8fafc;">
                <!-- Welcome Message -->
                <div style="margin-bottom: 1rem;">
                    <div style="background: white; padding: 0.875rem 1rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); max-width: 85%;">
                        <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: #475569;">
                            👋 Hi! Welcome to {{ site_name() }}. How can we help you today?
                        </p>
                    </div>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.7rem; color: #94a3b8;">Just now</p>
                </div>
            </div>

            <!-- Chat Input -->
            <div style="padding: 1rem; background: white; border-top: 1px solid #e2e8f0;">
                <form id="chatForm" onsubmit="sendMessage(event)" style="display: flex; gap: 0.5rem;">
                    <input type="text" id="chatInput" placeholder="Type your message..." style="flex: 1; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.9rem; outline: none; transition: all 0.3s;" onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
                    <button type="submit" style="width: 45px; height: 45px; border-radius: 12px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border: none; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(99, 102, 241, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.3)'">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Chat Widget Styles */
        #chatWindow {
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #chatMessages::-webkit-scrollbar {
            width: 6px;
        }

        #chatMessages::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        #chatMessages::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        #chatMessages::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .chat-message {
            margin-bottom: 1rem;
            animation: fadeIn 0.3s ease;
        }

        .chat-message.user {
            display: flex;
            justify-content: flex-end;
        }

        .chat-message.user .message-content {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        .chat-message.support .message-content {
            background: white;
            color: #475569;
        }

        .message-content {
            padding: 0.875rem 1rem;
            border-radius: 12px;
            max-width: 85%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .message-time {
            margin: 0.25rem 0 0 0;
            font-size: 0.7rem;
            color: #94a3b8;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            #chatWindow {
                width: calc(100vw - 40px);
                right: -10px;
            }
        }
    </style>

    <script>
        function toggleChat() {
            const chatWindow = document.getElementById('chatWindow');
            const chatIcon = document.getElementById('chatIcon');

            if (chatWindow.style.display === 'none' || chatWindow.style.display === '') {
                chatWindow.style.display = 'flex';
                chatIcon.classList.remove('fa-comments');
                chatIcon.classList.add('fa-times');
            } else {
                chatWindow.style.display = 'none';
                chatIcon.classList.remove('fa-times');
                chatIcon.classList.add('fa-comments');
            }
        }

        function sendMessage(event) {
            event.preventDefault();
            const input = document.getElementById('chatInput');
            const message = input.value.trim();

            if (message === '') return;

            const messagesContainer = document.getElementById('chatMessages');

            // Add user message
            const userMessage = document.createElement('div');
            userMessage.className = 'chat-message user';
            userMessage.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: flex-end; width: 100%;">
                    <div class="message-content">
                        <p style="margin: 0; font-size: 0.9rem; line-height: 1.6;">${message}</p>
                    </div>
                    <p class="message-time">Just now</p>
                </div>
            `;
            messagesContainer.appendChild(userMessage);

            // Clear input
            input.value = '';

            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            // Simulate support response
            setTimeout(() => {
                const supportMessage = document.createElement('div');
                supportMessage.className = 'chat-message support';
                supportMessage.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: flex-start; width: 100%;">
                        <div class="message-content">
                            <p style="margin: 0; font-size: 0.9rem; line-height: 1.6;">Thank you for your message! Our support team will get back to you shortly. In the meantime, feel free to browse our <a href="{{ route('templates') }}" style="color: #6366f1; text-decoration: none; font-weight: 600;">templates</a> or check out our <a href="{{ route('services') }}" style="color: #6366f1; text-decoration: none; font-weight: 600;">services</a>.</p>
                        </div>
                        <p class="message-time">Just now</p>
                    </div>
                `;
                messagesContainer.appendChild(supportMessage);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 1000);
        }

        // Close chat when clicking outside (optional)
        document.addEventListener('click', function(event) {
            const chatWidget = document.getElementById('chatWidget');
            const chatWindow = document.getElementById('chatWindow');
            const chatButton = document.getElementById('chatButton');

            if (chatWindow.style.display === 'flex' &&
                !chatWidget.contains(event.target) &&
                !chatButton.contains(event.target)) {
                // Keep chat open - uncomment below to close on outside click
                // toggleChat();
            }
        });

        // Search functionality
        function handleSearch(event) {
            event.preventDefault();
            const searchInput = document.getElementById('navbarSearch');
            const searchTerm = searchInput.value.trim();

            if (searchTerm) {
                // Redirect to templates page with search query
                window.location.href = '{{ route("templates") }}?search=' + encodeURIComponent(searchTerm);
            }
        }

        window.showRequiredContactModalIfNeeded = function() {
            var el = document.getElementById('requiredContactModal');
            if (!el || typeof bootstrap === 'undefined') return;
            bootstrap.Modal.getOrCreateInstance(el, { backdrop: 'static', keyboard: false }).show();
        };

        // Make search icon clickable
        document.addEventListener('DOMContentLoaded', function() {
            if (!window.__deferRequiredContactUntilExploreIntro) {
                window.showRequiredContactModalIfNeeded();
            }

            const searchInput = document.getElementById('navbarSearch');
            const searchIcon = document.getElementById('searchIcon');

            if (searchInput && searchIcon) {
                searchIcon.style.pointerEvents = 'auto';
                searchIcon.style.cursor = 'pointer';
                searchIcon.addEventListener('click', function() {
                    const searchTerm = searchInput.value.trim();
                    if (searchTerm) {
                        window.location.href = '{{ route("templates") }}?search=' + encodeURIComponent(searchTerm);
                    }
                });
            }

            // Notifications dropdown: fetch and show dynamically
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationDropdown) {
                const notificationListLoading = document.getElementById('notificationListLoading');
                const notificationListItems = document.getElementById('notificationListItems');
                const notificationListEmpty = document.getElementById('notificationListEmpty');
                const notificationBadge = document.getElementById('notificationBadge');
                const notificationMarkAllRead = document.getElementById('notificationMarkAllRead');
                const notificationsUrl = '{{ route("user.notifications") }}';
                const readAllUrl = '{{ route("user.notifications.read-all") }}';
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                function renderNotifications(data) {
                    notificationListLoading.classList.add('d-none');
                    notificationListItems.innerHTML = '';
                    if (!data.notifications || data.notifications.length === 0) {
                        notificationListEmpty.classList.remove('d-none');
                        return;
                    }
                    notificationListEmpty.classList.add('d-none');
                    data.notifications.forEach(function(n) {
                        const isUnread = !n.read_at;
                        const item = document.createElement('a');
                        item.className = 'dropdown-item py-2 border-bottom notification-item' + (isUnread ? ' bg-light' : '');
                        item.style.cursor = 'pointer';
                        item.style.fontSize = '0.875rem';
                        item.href = n.url || '#';
                        item.dataset.id = n.id;
                        item.dataset.read = n.read_at ? '1' : '0';
                        item.innerHTML = '<div class="d-flex justify-content-between align-items-start"><strong class="small">' + (n.title || 'Notification') + '</strong><span class="text-muted" style="font-size: 0.7rem;">' + (n.created_at ? new Date(n.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '') + '</span></div>' + (n.message ? '<p class="mb-0 mt-1 text-muted small">' + n.message + '</p>' : '');
                        if (n.url) {
                            item.addEventListener('click', function(e) {
                                if (isUnread && csrfToken) {
                                    fetch('{{ url("/user/notifications") }}/' + n.id + '/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' } });
                                }
                            });
                        } else {
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                if (isUnread && csrfToken) {
                                    fetch('{{ url("/user/notifications") }}/' + n.id + '/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' } }).then(function() { loadNotifications(); });
                                }
                            });
                        }
                        notificationListItems.appendChild(item);
                    });
                }

                function updateBadge(count) {
                    if (!notificationBadge) return;
                    if (count > 0) {
                        notificationBadge.textContent = count > 99 ? '99+' : count;
                        notificationBadge.classList.remove('d-none');
                    } else {
                        notificationBadge.classList.add('d-none');
                    }
                }

                function loadNotifications() {
                    if (!notificationListLoading) return;
                    notificationListItems.innerHTML = '';
                    notificationListEmpty.classList.add('d-none');
                    notificationListLoading.classList.remove('d-none');
                    fetch(notificationsUrl + '?limit=20', { headers: { 'Accept': 'application/json' } })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            renderNotifications(data);
                            updateBadge(data.unread_count || 0);
                        })
                        .catch(function() {
                            notificationListLoading.classList.add('d-none');
                            notificationListEmpty.classList.remove('d-none');
                            notificationListEmpty.textContent = 'Could not load notifications.';
                        });
                }

                notificationDropdown.addEventListener('show.bs.dropdown', function() {
                    loadNotifications();
                });

                if (notificationMarkAllRead) {
                    notificationMarkAllRead.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (!csrfToken) return;
                        fetch(readAllUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' } })
                            .then(function() { loadNotifications(); });
                    });
                }
            }
        });
    </script>
    @auth
        @if(session_recording_enabled() && ! auth()->user()->hasRole('admin'))
            <script>
                window.__SESSION_RECORDING__ = {
                    startUrl: @json(route('user.session-recording.start')),
                    appendUrl: @json(route('user.session-recording.append')),
                    finishUrl: @json(route('user.session-recording.finish')),
                    csrfToken: @json(csrf_token()),
                };
            </script>
            @include('partials.rrweb-session-record')
        @endif
        @if(user_heatmap_enabled() && ! auth()->user()->hasRole('admin'))
            <script>
                window.__USER_HEATMAP__ = {
                    url: @json(route('user.heatmap.ingest')),
                    csrfToken: @json(csrf_token()),
                };
            </script>
            @include('partials.user-heatmap-track')
        @endif
    @endauth
</body>
</html>

