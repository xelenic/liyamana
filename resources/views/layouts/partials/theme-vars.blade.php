@php
    $primary = \App\Models\Setting::get('theme_primary_color', '#6366f1');
    $secondary = \App\Models\Setting::get('theme_secondary_color', '#8b5cf6');
    $navbarStart = \App\Models\Setting::get('theme_navbar_bg_start', '#6366f1');
    $navbarEnd = \App\Models\Setting::get('theme_navbar_bg_end', '#8b5cf6');
    $navbarPadding = \App\Models\Setting::get('theme_navbar_padding_y', '0.5rem');
    $navbarFontSize = \App\Models\Setting::get('theme_navbar_font_size', '0.9375rem');
    $borderRadius = \App\Models\Setting::get('theme_border_radius', '6px');
    $btnBorderRadius = \App\Models\Setting::get('theme_btn_border_radius', '4px');
    $sidebarWidth = \App\Models\Setting::get('theme_sidebar_width', '250px');
@endphp
:root {
    --primary-color: {{ $primary }};
    --secondary-color: {{ $secondary }};
    --navbar-bg-start: {{ $navbarStart }};
    --navbar-bg-end: {{ $navbarEnd }};
    --navbar-padding-y: {{ $navbarPadding }};
    --navbar-font-size: {{ $navbarFontSize }};
    --theme-border-radius: {{ $borderRadius }};
    --theme-btn-border-radius: {{ $btnBorderRadius }};
    --sidebar-width: {{ $sidebarWidth }};
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --info-color: #3b82f6;
    --light-bg: #f8fafc;
    --dark-text: #1e293b;
    --border-color: #e2e8f0;
}
