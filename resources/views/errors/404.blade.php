<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found | {{ site_name() }}</title>
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
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
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
            width: 80px;
            height: 100px;
            background: linear-gradient(145deg, #fff 0%, #f8fafc 100%);
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.12), 0 1px 3px rgba(0,0,0,0.06);
            border: 1px solid rgba(99, 102, 241, 0.15);
            animation: float 6s ease-in-out infinite;
        }
        .floating-pages .pg:nth-child(1) { top: 12%; left: 8%; animation-delay: 0s; transform: rotate(-8deg); }
        .floating-pages .pg:nth-child(2) { top: 18%; right: 6%; animation-delay: 1.2s; transform: rotate(6deg); }
        .floating-pages .pg:nth-child(3) { bottom: 20%; left: 5%; animation-delay: 2.4s; transform: rotate(5deg); }
        .floating-pages .pg:nth-child(4) { bottom: 15%; right: 10%; animation-delay: 0.8s; transform: rotate(-7deg); }
        .floating-pages .pg:nth-child(5) { top: 50%; left: 2%; animation-delay: 1.8s; transform: rotate(-4deg); width: 60px; height: 75px; }
        .floating-pages .pg:nth-child(6) { top: 45%; right: 3%; animation-delay: 3s; transform: rotate(4deg); width: 55px; height: 70px; }
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(var(--r, 0deg)); }
            50% { transform: translateY(-12px) rotate(var(--r, 0deg)); }
        }
        .error-code {
            font-size: clamp(6rem, 18vw, 10rem);
            font-weight: 700;
            line-height: 1;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
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
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }
        .btn-404 {
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
        .btn-primary-404 {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white !important;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        }
        .btn-primary-404:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
        }
        .btn-outline-404 {
            background: white;
            color: #6366f1;
            border: 2px solid #6366f1;
        }
        .btn-outline-404:hover {
            background: #f8fafc;
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
        .brand-link:hover { color: #6366f1; }
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
        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-desc">
            Oops! The page you're looking for seems to have turned a few pages ahead. Perhaps it's in another chapter of our site.
        </p>
        <div class="actions">
            <a href="{{ route('design.templates.explore') }}" class="btn-404 btn-primary-404">
                <i class="fas fa-compass"></i>
                Explore Templates
            </a>
            <a href="javascript:history.back()" class="btn-404 btn-outline-404">
                <i class="fas fa-arrow-left"></i>
                Go Back
            </a>
        </div>
    </div>
</body>
</html>
