@extends('layouts.app')

@section('title', $doc->title . ' - Documentation - ' . site_name())

@section('content')
<section class="py-5" style="background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%); min-height: 70vh;">
    <div class="container py-4">
        <div class="row">
            {{-- Sidebar --}}
            <aside class="col-lg-4 col-xl-3 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm sticky-top" style="top: 1rem;">
                    <div class="card-body p-3">
                        <a href="{{ route('docs.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-3" style="font-size: 0.9rem; color: #6366f1;">
                            <i class="fas fa-arrow-left me-2"></i>All documentation
                        </a>
                        <h5 class="mb-3 fw-600" style="font-size: 0.95rem; color: #1e293b;">
                            <i class="fas fa-book text-primary me-2"></i>Documentation
                        </h5>
                        @foreach($categories as $cat)
                            <div class="mb-3">
                                <div class="text-muted small text-uppercase fw-600 mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ $cat->name }}</div>
                                <ul class="list-unstyled mb-0">
                                    @foreach($docs->filter(fn($d) => $d->categories->contains('id', $cat->id)) as $d)
                                        <li>
                                            <a href="{{ route('docs.show', $d->slug) }}" class="d-block py-1 px-2 rounded text-decoration-none {{ $d->id === $doc->id ? 'fw-600' : '' }}" style="font-size: 0.9rem; color: {{ $d->id === $doc->id ? '#6366f1' : '#475569' }}; background: {{ $d->id === $doc->id ? 'rgba(99, 102, 241, 0.1)' : 'transparent' }};" onmouseover="this.style.background='#f1f5f9'; this.style.color='#6366f1';" onmouseout="this.style.background='{{ $d->id === $doc->id ? 'rgba(99, 102, 241, 0.1)' : 'transparent' }}'; this.style.color='{{ $d->id === $doc->id ? '#6366f1' : '#475569' }}';">
                                                {{ $d->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                        @if($docs->where('categories', '!=', collect())->isEmpty() && $docs->isNotEmpty())
                            <ul class="list-unstyled mb-0">
                                @foreach($docs as $d)
                                    <li>
                                        <a href="{{ route('docs.show', $d->slug) }}" class="d-block py-1 px-2 rounded text-decoration-none {{ $d->id === $doc->id ? 'fw-600' : '' }}" style="font-size: 0.9rem; color: {{ $d->id === $doc->id ? '#6366f1' : '#475569' }};">
                                            {{ $d->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </aside>

            <div class="col-lg-8 col-xl-9">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb mb-0" style="font-size: 0.875rem;">
                        <li class="breadcrumb-item"><a href="{{ route('docs.index') }}" style="color: #6366f1;">Documentation</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $doc->title }}</li>
                    </ol>
                </nav>

                <article class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <h1 class="mb-4" style="font-size: 1.75rem; font-weight: 800; color: #0f172a;">{{ $doc->title }}</h1>
                        @if($doc->categories->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                @foreach($doc->categories as $c)
                                    <span class="badge bg-light text-dark border" style="font-size: 0.8rem;">{{ $c->name }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="docs-content" style="font-size: 1rem; line-height: 1.7; color: #334155;">
                            {!! $doc->content !!}
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

<style>
.docs-content table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.9rem; }
.docs-content table th,
.docs-content table td { border: 1px solid #e2e8f0; padding: 0.6rem 0.75rem; text-align: left; }
.docs-content table th { background: #f8fafc; font-weight: 600; color: #1e293b; }
.docs-content code { background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.9em; }
.docs-content pre { background: #f8fafc; padding: 1rem; border-radius: 8px; overflow-x: auto; }
.docs-content pre code { background: none; padding: 0; }
.docs-content h2 { font-size: 1.35rem; margin-top: 1.5rem; margin-bottom: 0.75rem; color: #0f172a; }
.docs-content h3 { font-size: 1.15rem; margin-top: 1.25rem; margin-bottom: 0.5rem; }
.docs-content ul, .docs-content ol { margin-bottom: 1rem; padding-left: 1.5rem; }
.docs-content p { margin-bottom: 0.85rem; }
.docs-content blockquote { border-left: 4px solid #6366f1; padding-left: 1rem; margin: 1rem 0; color: #64748b; }
</style>
@endsection
