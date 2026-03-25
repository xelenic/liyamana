@php
    $s = $seo ?? [];
    $metaDesc = $s['meta_description'] ?? null;
    $keywords = $s['meta_keywords'] ?? null;
@endphp
@if(! empty($metaDesc))
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(e(strip_tags($metaDesc)), 320, '') }}">
@endif
@if(! empty($keywords))
    <meta name="keywords" content="{{ e(strip_tags($keywords)) }}">
@endif
<meta name="robots" content="{{ e($s['robots'] ?? 'index, follow') }}">
@if(! empty($s['canonical_url']))
    <link rel="canonical" href="{{ e($s['canonical_url']) }}">
@endif

@if(! empty($s['google_verification']))
    <meta name="google-site-verification" content="{{ e($s['google_verification']) }}">
@endif
@if(! empty($s['bing_verification']))
    <meta name="msvalidate.01" content="{{ e($s['bing_verification']) }}">
@endif

<meta property="og:type" content="website">
<meta property="og:url" content="{{ e($s['canonical_url'] ?? url()->current()) }}">
@if(! empty($s['og_title']))
    <meta property="og:title" content="{{ e(\Illuminate\Support\Str::limit(strip_tags($s['og_title']), 200, '')) }}">
@endif
@if(! empty($s['og_description']))
    <meta property="og:description" content="{{ e(\Illuminate\Support\Str::limit(strip_tags($s['og_description']), 300, '')) }}">
@endif
@if(! empty($s['og_image']))
    <meta property="og:image" content="{{ e($s['og_image']) }}">
@endif
<meta property="og:site_name" content="{{ e(site_name()) }}">

<meta name="twitter:card" content="{{ e($s['twitter_card'] ?? 'summary_large_image') }}">
@if(! empty($s['twitter_handle']))
    @php
        $twHandle = '@'.ltrim($s['twitter_handle'], '@');
    @endphp
    <meta name="twitter:site" content="{{ e($twHandle) }}">
@endif
@if(! empty($s['og_title']))
    <meta name="twitter:title" content="{{ e(\Illuminate\Support\Str::limit(strip_tags($s['og_title']), 70, '')) }}">
@endif
@if(! empty($s['og_description']))
    <meta name="twitter:description" content="{{ e(\Illuminate\Support\Str::limit(strip_tags($s['og_description']), 200, '')) }}">
@endif
@if(! empty($s['og_image']))
    <meta name="twitter:image" content="{{ e($s['og_image']) }}">
@endif

@php
    $jsonLd = $s['organization_json_ld'] ?? null;
    $decoded = is_string($jsonLd) && trim($jsonLd) !== '' ? json_decode($jsonLd, true) : null;
@endphp
@if(is_array($decoded))
    <script type="application/ld+json">{!! json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
