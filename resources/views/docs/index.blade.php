@extends('layouts.app')

@section('title', 'Documentation - ' . site_name())

@section('content')
<section class="py-5" style="background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%); min-height: 70vh;">
    <div class="container py-4">
        <div class="row">
            {{-- Sidebar: categories + doc list --}}
            <aside class="col-lg-4 col-xl-3 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm sticky-top" style="top: 1rem;">
                    <div class="card-body p-3">
                        <h5 class="mb-3 fw-600" style="font-size: 0.95rem; color: #1e293b;">
                            <i class="fas fa-book text-primary me-2"></i>Documentation
                        </h5>
                        @foreach($categories as $cat)
                            @php $catDocs = $docs->filter(fn($d) => $d->categories->contains('id', $cat->id)); @endphp
                            @if($catDocs->isNotEmpty())
                                <div class="mb-3">
                                    <div class="text-muted small text-uppercase fw-600 mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ $cat->name }}</div>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($catDocs as $d)
                                            <li>
                                                <a href="{{ route('docs.show', $d->slug) }}" class="d-block py-1 px-2 rounded text-decoration-none" style="font-size: 0.9rem; color: #475569;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#6366f1';" onmouseout="this.style.background='transparent'; this.style.color='#475569';">
                                                    {{ $d->title }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endforeach
                        @php $uncategorized = $docs->filter(fn($d) => $d->categories->isEmpty()); @endphp
                        @if($uncategorized->isNotEmpty())
                            <div class="mb-3">
                                <div class="text-muted small text-uppercase fw-600 mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Other</div>
                                <ul class="list-unstyled mb-0">
                                    @foreach($uncategorized as $d)
                                        <li>
                                            <a href="{{ route('docs.show', $d->slug) }}" class="d-block py-1 px-2 rounded text-decoration-none" style="font-size: 0.9rem; color: #475569;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#6366f1';" onmouseout="this.style.background='transparent'; this.style.color='#475569';">
                                                {{ $d->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if($docs->isEmpty())
                            <p class="text-muted small mb-0">No documentation pages yet.</p>
                        @endif
                    </div>
                </div>
            </aside>

            <div class="col-lg-8 col-xl-9">
                <div class="mb-4">
                    <h1 class="mb-2" style="font-size: 2rem; font-weight: 800; color: #0f172a;">
                        <i class="fas fa-book-open text-primary me-2"></i>Documentation
                    </h1>
                    <p class="text-muted mb-0" style="font-size: 1.05rem;">Browse help articles, API reference, and guides.</p>
                </div>

                @if($docs->isEmpty())
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No documentation pages have been published yet. Check back later.</p>
                        </div>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($docs as $doc)
                            <div class="col-md-6">
                                <a href="{{ route('docs.show', $doc->slug) }}" class="card border-0 shadow-sm text-decoration-none d-block h-100" style="transition: all 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                                    <div class="card-body">
                                        <h5 class="card-title mb-2" style="font-size: 1rem; font-weight: 600; color: #1e293b;">{{ $doc->title }}</h5>
                                        @if($doc->categories->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($doc->categories as $c)
                                                    <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">{{ $c->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
