<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>419 - Page Expired | {{ site_name() }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(145deg, #fffbeb 0%, #fef3c7 35%, #fde68a 100%);
        }
        .page-wrap {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 2rem;
            max-width: 520px;
        }
        .floating-pages {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }
        .floating-pages .pg {
            position: absolute;
            width: 72px;
            height: 96px;
            background: linear-gradient(145deg, #fff 0%, #fffbeb 100%);
            border-radius: 6px;
            box-shadow: 0 4px 24px rgba(245, 158, 11, 0.15), 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid rgba(245, 158, 11, 0.25);
            animation: float 6s ease-in-out infinite;
        }
        .floating-pages .pg:nth-child(1) { top: 12%; left: 8%; animation-delay: 0s; transform: rotate(-8deg); }
        .floating-pages .pg:nth-child(2) { top: 20%; right: 10%; animation-delay: 1.2s; transform: rotate(6deg); }
        .floating-pages .pg:nth-child(3) { bottom: 20%; left: 6%; animation-delay: 2.4s; transform: rotate(5deg); }
        .floating-pages .pg:nth-child(4) { bottom: 14%; right: 8%; animation-delay: 0.8s; transform: rotate(-7deg); }
        .floating-pages .pg:nth-child(5) { top: 48%; left: 4%; animation-delay: 1.8s; transform: rotate(-4deg); width: 56px; height: 72px; }
        .floating-pages .pg:nth-child(6) { top: 42%; right: 4%; animation-delay: 3s; transform: rotate(4deg); width: 52px; height: 68px; }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }
        .icon-ring {
            width: 88px;
            height: 88px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(251, 146, 60, 0.15) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 32px rgba(245, 158, 11, 0.2);
        }
        .icon-ring i {
            font-size: 2.25rem;
            background: linear-gradient(135deg, #d97706 0%, #ea580c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .error-code {
            font-size: clamp(6rem, 18vw, 10rem);
            font-weight: 700;
            line-height: 1;
            background: linear-gradient(135deg, #d97706 0%, #ea580c 45%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.04em;
            margin-bottom: 0.5rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }
        .error-desc {
            font-size: 1rem;
            color: #64748b;
            line-height: 1.65;
            margin-bottom: 2rem;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }
        .btn-err {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.9375rem;
            font-weight: 600;
            font-family: inherit;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.25s ease;
            border: none;
            cursor: pointer;
        }
        .btn-primary-err {
            background: linear-gradient(135deg, #d97706 0%, #ea580c 100%);
            color: white !important;
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.35);
        }
        .btn-primary-err:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(234, 88, 12, 0.42);
        }
        .btn-outline-err {
            background: white;
            color: #c2410c;
            border: 2px solid rgba(234, 88, 12, 0.35);
        }
        .btn-outline-err:hover {
            background: #fffbeb;
            transform: translateY(-2px);
        }
        .brand-link {
            position: absolute;
            top: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }
        .brand-link:hover { color: #d97706; }
        .brand-link i { font-size: 1.1rem; }
    </style>
</head>
<body>
    <a href="{{ route('design.templates.explore') }}" class="brand-link">
        <i class="fas fa-book-open"></i>
        <span>{{ site_name() }}</span>
    </a>

    <div class="floating-pages">
        <div class="pg"></div>
        <div class="pg"></div>
        <div class="pg"></div>
        <div class="pg"></div>
        <div class="pg"></div>
        <div class="pg"></div>
    </div>

    <div class="page-wrap">
        <div class="icon-ring" aria-hidden="true">
            <i class="fas fa-shield-halved"></i>
        </div>
        <div class="error-code">419</div>
        <h1 class="error-title">Page Expired</h1>
        <p class="error-desc">
            Your session token expired or this form was open too long. That keeps your account safe—refresh and try again.
        </p>
        <div class="actions">
            <button type="button" class="btn-err btn-primary-err" onclick="location.reload()">
                <i class="fas fa-rotate-right"></i>
                Reload page
            </button>
            <a href="{{ route('design.templates.explore') }}" class="btn-err btn-outline-err">
                <i class="fas fa-compass"></i>
                Explore Templates
            </a>
            <a href="javascript:history.back()" class="btn-err btn-outline-err" style="border-style: dashed;">
                <i class="fas fa-arrow-left"></i>
                Go Back
            </a>
        </div>
        <p class="text-muted small mt-4" style="font-size: 0.8125rem; color: #94a3b8;">
            After reload, submit your form again. Tokens expire to protect against cross-site attacks.
        </p>
    </div>
</body>
</html>
