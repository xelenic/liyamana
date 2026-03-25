<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ site_name() }} Admin</title>

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
        }

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
            background: linear-gradient(135deg, var(--navbar-bg-start) 0%, var(--navbar-bg-end) 100%);
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

        .sidebar-menu .menu-item-wrapper {
            list-style: none;
        }

        .sidebar-menu .menu-item-parent {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-menu .menu-item-parent .menu-chevron {
            font-size: 0.7rem;
            transition: transform 0.2s;
        }

        .sidebar-menu .menu-item-parent.expanded .menu-chevron {
            transform: rotate(90deg);
        }

        .sidebar-menu .menu-submenu {
            display: none;
            padding-left: 0;
            list-style: none;
            margin: 0;
            background: rgba(0,0,0,0.02);
        }

        .sidebar-menu .menu-submenu.show {
            display: block;
        }

        .sidebar-menu .menu-submenu .menu-item {
            padding-left: 2.5rem;
            font-size: 0.875rem;
        }

        .sidebar-menu .menu-submenu .menu-item i {
            width: 16px;
            font-size: 0.8rem;
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

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background-color: var(--light-bg);
        }

        .content-wrapper {
            padding: 1.5rem;
        }

        .top-navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 0.75rem 1.5rem;
            margin-bottom: 0;
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

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: inline-block;
            }

            .content-wrapper {
                padding: 1rem;
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

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
        }

        .btn {
            font-size: 0.9375rem;
            padding: 0.5rem 1.15rem;
            border-radius: 6px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            font-weight: 500;
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }

        /* Pagination - ensure proper display */
        .pagination {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            padding-left: 0;
            list-style: none;
            margin: 0;
        }
        .pagination .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.25rem;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--primary-color);
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .pagination .page-link:hover {
            background: var(--light-bg);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-color: transparent;
            color: white;
        }
        .pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background: #f8fafc;
            border-color: var(--border-color);
            pointer-events: none;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="brand">
                <i class="fas fa-shield-alt me-2"></i>Admin Panel
            </a>
        </div>
        <nav class="sidebar-menu">
            <div class="menu-title">Admin Menu</div>
            <a href="{{ route('admin.users') }}" class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <div class="menu-item-wrapper">
                <a href="#" class="menu-item menu-item-parent {{ request()->routeIs('admin.reports*') ? 'expanded' : '' }}" id="reportsMenuParent" onclick="toggleSubmenu(event, 'reportsSubmenu')">
                    <span>
                        <i class="fas fa-chart-pie"></i>
                        <span>Reports</span>
                    </span>
                    <i class="fas fa-chevron-right menu-chevron"></i>
                </a>
                <ul class="menu-submenu {{ request()->routeIs('admin.reports*') ? 'show' : '' }}" id="reportsSubmenu">
                    <li>
                        <a href="{{ route('admin.reports.index') }}" class="menu-item {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Overview</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.orders') }}" class="menu-item {{ request()->routeIs('admin.reports.orders') ? 'active' : '' }}">
                            <i class="fas fa-shopping-bag"></i>
                            <span>Orders &amp; revenue</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.credits') }}" class="menu-item {{ request()->routeIs('admin.reports.credits') ? 'active' : '' }}">
                            <i class="fas fa-wallet"></i>
                            <span>Credits</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.activity') }}" class="menu-item {{ request()->routeIs('admin.reports.activity') ? 'active' : '' }}">
                            <i class="fas fa-bolt"></i>
                            <span>Platform activity</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="menu-item-wrapper">
                <a href="#" class="menu-item menu-item-parent {{ request()->routeIs('admin.logs*') || request()->routeIs('admin.session-recordings*') || request()->routeIs('admin.heatmap*') ? 'expanded' : '' }}" id="userStatisticsMenuParent" onclick="toggleSubmenu(event, 'userStatisticsSubmenu')">
                    <span>
                        <i class="fas fa-chart-line"></i>
                        <span>Users statistics</span>
                    </span>
                    <i class="fas fa-chevron-right menu-chevron"></i>
                </a>
                <ul class="menu-submenu {{ request()->routeIs('admin.logs*') || request()->routeIs('admin.session-recordings*') || request()->routeIs('admin.heatmap*') ? 'show' : '' }}" id="userStatisticsSubmenu">
                    <li>
                        <a href="{{ route('admin.heatmap.index') }}" class="menu-item {{ request()->routeIs('admin.heatmap*') ? 'active' : '' }}">
                            <i class="fas fa-fire"></i>
                            <span>User heatmaps</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.session-recordings.index') }}" class="menu-item {{ request()->routeIs('admin.session-recordings*') ? 'active' : '' }}">
                            <i class="fas fa-circle-play"></i>
                            <span>Session recordings</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.logs') }}" class="menu-item {{ request()->routeIs('admin.logs*') ? 'active' : '' }}">
                            <i class="fas fa-scroll"></i>
                            <span>Log Viewer</span>
                        </a>
                    </li>
                </ul>
            </div>
            <a href="{{ route('admin.flipbooks') }}" class="menu-item {{ request()->routeIs('admin.flipbooks.*') ? 'active' : '' }}">
                <i class="fas fa-book"></i>
                <span>Flip Books</span>
            </a>
            <div class="menu-item-wrapper">
                <a href="#" class="menu-item menu-item-parent {{ request()->routeIs('admin.templates.*') || request()->routeIs('admin.licenses*') || request()->routeIs('admin.template-categories*') || request()->routeIs('admin.explore-slides*') || request()->routeIs('admin.thumbnail-prompts*') || request()->routeIs('admin.design-intro*') ? 'expanded' : '' }}" id="templatesMenuParent" onclick="toggleSubmenu(event, 'templatesSubmenu')">
                    <span>
                        <i class="fas fa-th-large"></i>
                        <span>Templates</span>
                    </span>
                    <i class="fas fa-chevron-right menu-chevron"></i>
                </a>
                <ul class="menu-submenu {{ request()->routeIs('admin.templates.*') || request()->routeIs('admin.licenses*') || request()->routeIs('admin.template-categories*') || request()->routeIs('admin.explore-slides*') || request()->routeIs('admin.thumbnail-prompts*') || request()->routeIs('admin.design-intro*') ? 'show' : '' }}" id="templatesSubmenu">
                    <li>
                        <a href="{{ route('admin.templates') }}" class="menu-item {{ request()->routeIs('admin.templates') && !request()->routeIs('admin.templates.toggle-featured') ? 'active' : '' }}">
                            <i class="fas fa-th-list"></i>
                            <span>Template Node</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.template-categories') }}" class="menu-item {{ request()->routeIs('admin.template-categories*') ? 'active' : '' }}">
                            <i class="fas fa-folder"></i>
                            <span>Template Categories</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.licenses') }}" class="menu-item {{ request()->routeIs('admin.licenses*') ? 'active' : '' }}">
                            <i class="fas fa-certificate"></i>
                            <span>Template License</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.explore-slides') }}" class="menu-item {{ request()->routeIs('admin.explore-slides*') ? 'active' : '' }}">
                            <i class="fas fa-images"></i>
                            <span>Explore Slider</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.thumbnail-prompts') }}" class="menu-item {{ request()->routeIs('admin.thumbnail-prompts*') ? 'active' : '' }}">
                            <i class="fas fa-magic"></i>
                            <span>Thumbnail Prompts</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.design-intro') }}" class="menu-item {{ request()->routeIs('admin.design-intro*') ? 'active' : '' }}">
                            <i class="fas fa-route"></i>
                            <span>Design Intro Tour</span>
                        </a>
                    </li>
                </ul>
            </div>
            <a href="{{ route('admin.orders') }}" class="menu-item d-flex align-items-center {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span class="flex-grow-1">Orders</span>
                @if(isset($adminPendingOrdersCount) && $adminPendingOrdersCount > 0)
                <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">{{ $adminPendingOrdersCount }}</span>
                @endif
            </a>
            <div class="menu-item-wrapper">
                <a href="#" class="menu-item menu-item-parent {{ request()->routeIs('admin.stock*') || request()->routeIs('admin.suppliers*') ? 'expanded' : '' }}" id="inventoryMenuParent" onclick="toggleSubmenu(event, 'inventorySubmenu')">
                    <span>
                        <i class="fas fa-warehouse"></i>
                        <span>Inventory</span>
                    </span>
                    <i class="fas fa-chevron-right menu-chevron"></i>
                </a>
                <ul class="menu-submenu {{ request()->routeIs('admin.stock*') || request()->routeIs('admin.suppliers*') ? 'show' : '' }}" id="inventorySubmenu">
                    <li>
                        <a href="{{ route('admin.stock.index') }}" class="menu-item {{ request()->routeIs('admin.stock.index') ? 'active' : '' }}">
                            <i class="fas fa-boxes"></i>
                            <span>Stock overview</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.suppliers.index') }}" class="menu-item {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                            <i class="fas fa-truck"></i>
                            <span>Suppliers</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.stock.purchases.index') }}" class="menu-item {{ request()->routeIs('admin.stock.purchases.index') || request()->routeIs('admin.stock.purchases.show') || request()->routeIs('admin.stock.purchases.create') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice"></i>
                            <span>Purchases</span>
                        </a>
                    </li>
                </ul>
            </div>
            <a href="{{ route('admin.designer-applications') }}" class="menu-item {{ request()->routeIs('admin.designer-applications*') ? 'active' : '' }}">
                <i class="fas fa-palette"></i>
                <span>Designer Applications</span>
            </a>
            <a href="{{ route('admin.global-images.index') }}" class="menu-item {{ request()->routeIs('admin.global-images.*') ? 'active' : '' }}">
                <i class="fas fa-globe"></i>
                <span>Global Images</span>
            </a>
            <a href="{{ route('admin.design-fonts.index') }}" class="menu-item {{ request()->routeIs('admin.design-fonts.*') ? 'active' : '' }}">
                <i class="fas fa-font"></i>
                <span>Design fonts</span>
            </a>
            <a href="{{ route('admin.sheet-types') }}" class="menu-item {{ request()->routeIs('admin.sheet-types.*') ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i>
                <span>Sheet Types</span>
            </a>
            <a href="{{ route('admin.envelope-types') }}" class="menu-item {{ request()->routeIs('admin.envelope-types.*') ? 'active' : '' }}">
                <i class="fas fa-envelope-open-text"></i>
                <span>Envelope Types</span>
            </a>
            <a href="{{ route('admin.pricing-rules') }}" class="menu-item {{ request()->routeIs('admin.pricing-rules*') ? 'active' : '' }}">
                <i class="fas fa-percent"></i>
                <span>Pricing Rules</span>
            </a>
            <a href="{{ route('admin.testimonials') }}" class="menu-item {{ request()->routeIs('admin.testimonials*') ? 'active' : '' }}">
                <i class="fas fa-quote-right"></i>
                <span>Testimonials</span>
            </a>
            <a href="{{ route('admin.products') }}" class="menu-item {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
            <div class="menu-item-wrapper">
                <a href="#" class="menu-item menu-item-parent {{ request()->routeIs('admin.documentation*') || request()->routeIs('admin.documentation-categories*') ? 'expanded' : '' }}" id="documentationMenuParent" onclick="toggleSubmenu(event, 'documentationSubmenu')">
                    <span>
                        <i class="fas fa-book"></i>
                        <span>Documentation</span>
                    </span>
                    <i class="fas fa-chevron-right menu-chevron"></i>
                </a>
                <ul class="menu-submenu {{ request()->routeIs('admin.documentation*') || request()->routeIs('admin.documentation-categories*') ? 'show' : '' }}" id="documentationSubmenu">
                    <li>
                        <a href="{{ route('admin.documentation') }}" class="menu-item {{ request()->routeIs('admin.documentation*') ? 'active' : '' }}">
                            <i class="fas fa-file-alt"></i>
                            <span>Pages</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.documentation-categories') }}" class="menu-item {{ request()->routeIs('admin.documentation-categories*') ? 'active' : '' }}">
                            <i class="fas fa-folder"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="menu-item-wrapper">
                <a href="#" class="menu-item menu-item-parent {{ request()->routeIs('admin.ai-content-templates*') || request()->routeIs('admin.nanobanana.templates*') ? 'expanded' : '' }}" id="aiTemplateMenuParent" onclick="toggleSubmenu(event, 'aiTemplateSubmenu')">
                    <span>
                        <i class="fas fa-robot"></i>
                        <span>AI Template</span>
                    </span>
                    <i class="fas fa-chevron-right menu-chevron"></i>
                </a>
                <ul class="menu-submenu {{ request()->routeIs('admin.ai-content-templates*') || request()->routeIs('admin.nanobanana.templates*') ? 'show' : '' }}" id="aiTemplateSubmenu">
                    <li>
                        <a href="{{ route('admin.ai-content-templates') }}" class="menu-item {{ request()->routeIs('admin.ai-content-templates*') ? 'active' : '' }}">
                            <i class="fas fa-file-alt"></i>
                            <span>AI Content Template</span>
                        </a>
                    </li>
                    @if(\App\Models\Module::where('name', 'nano-banana-module')->where('enabled', true)->exists())
                    <li>
                        <a href="{{ route('admin.nanobanana.templates.index') }}" class="menu-item {{ request()->routeIs('admin.nanobanana.templates*') ? 'active' : '' }}">
                            <i class="fas fa-images"></i>
                            <span>AI Image Templates</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
            @foreach(app(\App\Services\Module\ModuleRegistry::class)->getAdminMenuItems() as $item)
            @if(\Illuminate\Support\Facades\Route::has($item['route']) && ($item['module'] ?? '') !== 'nano-banana-module')
            <a href="{{ route($item['route']) }}" class="menu-item {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                <i class="fas {{ $item['icon'] ?? 'fa-cube' }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
            @endif
            @endforeach
            <div class="menu-item-wrapper">
                <a href="#" class="menu-item menu-item-parent {{ request()->routeIs('admin.settings*') || request()->routeIs('admin.modules*') || request()->routeIs('admin.currencies*') || request()->routeIs('admin.nanobanana.settings*') || request()->routeIs('admin.seo*') ? 'expanded' : '' }}" id="settingsMenuParent" onclick="toggleSubmenu(event, 'settingsSubmenu')">
                    <span>
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </span>
                    <i class="fas fa-chevron-right menu-chevron"></i>
                </a>
                <ul class="menu-submenu {{ request()->routeIs('admin.settings*') || request()->routeIs('admin.modules*') || request()->routeIs('admin.currencies*') || request()->routeIs('admin.nanobanana.settings*') || request()->routeIs('admin.seo*') ? 'show' : '' }}" id="settingsSubmenu">
                    <li>
                        <a href="{{ route('admin.seo.index') }}" class="menu-item {{ request()->routeIs('admin.seo*') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span>SEO &amp; meta</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings', ['tab' => 'general']) }}" class="menu-item {{ request()->routeIs('admin.settings') && !request()->routeIs('admin.seo*') && !request()->routeIs('admin.settings.editor*') && !request()->routeIs('admin.settings.theme*') && !request()->routeIs('admin.settings.payment*') && !request()->routeIs('admin.settings.oauth*') && !request()->routeIs('admin.settings.credit-topup*') && !request()->routeIs('admin.settings.special-offers-modal*') && !request()->routeIs('admin.settings.session-recording*') && !request()->routeIs('admin.settings.backup-restore*') && !request()->routeIs('admin.nanobanana.settings*') ? 'active' : '' }}">
                            <i class="fas fa-sliders-h"></i>
                            <span>General</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.currencies') }}" class="menu-item {{ request()->routeIs('admin.currencies*') ? 'active' : '' }}">
                            <i class="fas fa-coins"></i>
                            <span>Currencies</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.editor') }}" class="menu-item {{ request()->routeIs('admin.settings.editor*') ? 'active' : '' }}">
                            <i class="fas fa-palette"></i>
                            <span>Editor</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.theme') }}" class="menu-item {{ request()->routeIs('admin.settings.theme*') ? 'active' : '' }}">
                            <i class="fas fa-paint-brush"></i>
                            <span>UI Theme</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.payment') }}" class="menu-item {{ request()->routeIs('admin.settings.payment*') ? 'active' : '' }}">
                            <i class="fas fa-credit-card"></i>
                            <span>Payment Gateway</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.oauth') }}" class="menu-item {{ request()->routeIs('admin.settings.oauth*') ? 'active' : '' }}">
                            <i class="fab fa-google"></i>
                            <span>OAuth Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.session-recording') }}" class="menu-item {{ request()->routeIs('admin.settings.session-recording*') ? 'active' : '' }}">
                            <i class="fas fa-video"></i>
                            <span>Session &amp; heatmap</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.backup-restore') }}" class="menu-item {{ request()->routeIs('admin.settings.backup-restore*') ? 'active' : '' }}">
                            <i class="fas fa-database"></i>
                            <span>Backup &amp; restore</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.credit-topup') }}" class="menu-item {{ request()->routeIs('admin.settings.credit-topup*') ? 'active' : '' }}">
                            <i class="fas fa-coins"></i>
                            <span>Credit Top-up</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.special-offers-modal') }}" class="menu-item {{ request()->routeIs('admin.settings.special-offers-modal*') ? 'active' : '' }}">
                            <i class="fas fa-gift"></i>
                            <span>Special Offers Modal</span>
                        </a>
                    </li>
                    @if(\App\Models\Module::where('name', 'nano-banana-module')->where('enabled', true)->exists())
                    <li>
                        <a href="{{ route('admin.nanobanana.settings') }}" class="menu-item {{ request()->routeIs('admin.nanobanana.settings*') ? 'active' : '' }}">
                            <i class="fas fa-magic"></i>
                            <span>Gemini Image Settings</span>
                        </a>
                    </li>
                    @endif
                    <li>
                        <a href="{{ route('admin.modules.index') }}" class="menu-item {{ request()->routeIs('admin.modules*') ? 'active' : '' }}">
                            <i class="fas fa-puzzle-piece"></i>
                            <span>Modules</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="menu-divider"></div>

            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="menu-item w-100 text-start border-0 bg-transparent" style="cursor: pointer;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </nav>
    </aside>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="sidebar-toggle me-3" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="mb-0" style="font-size: 1.1rem;">@yield('page-title', 'Admin Dashboard')</h5>
                </div>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a class="text-decoration-none text-dark dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i>
                            <span>{{ Auth::user()->name }}</span>
                            <span class="badge bg-danger ms-2">Admin</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('design.templates.explore') }}"><i class="fas fa-compass me-2"></i>Explore Templates</a></li>
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
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Close sidebar on window resize if it's desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });

        // Close sidebar when clicking overlay
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            toggleSidebar();
        });

        // Submenu toggle
        function toggleSubmenu(event, submenuId) {
            event.preventDefault();
            const parent = event.currentTarget;
            const submenu = document.getElementById(submenuId);
            if (!submenu) return;
            parent.classList.toggle('expanded');
            submenu.classList.toggle('show');
        }
    </script>

    @stack('scripts')
</body>
</html>

