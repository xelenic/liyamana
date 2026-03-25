@if(isset($aiContentTemplates) && $aiContentTemplates->isNotEmpty())
@php
    $aiTemplateRecentGenerations = $aiTemplateRecentGenerations ?? collect();
@endphp
@once
@push('styles')
<style>
    .ai-ct-pro {
        --ai-ct-accent: #6366f1;
        --ai-ct-accent2: #8b5cf6;
        --ai-ct-surface: #ffffff;
        --ai-ct-border: #e2e8f0;
        --ai-ct-muted: #64748b;
        margin-bottom: 2rem;
    }
    .ai-ct-pro__shell {
        background: linear-gradient(145deg, #f8fafc 0%, #f1f5f9 45%, #eef2ff 100%);
        border: 1px solid var(--ai-ct-border);
        border-radius: 16px;
        padding: 1.25rem 1.25rem 1.5rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    @media (min-width: 768px) {
        .ai-ct-pro__shell { padding: 1.5rem 1.75rem 1.75rem; }
    }
    .ai-ct-pro__head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.9);
    }
    .ai-ct-pro__title-row {
        display: flex;
        align-items: flex-start;
        gap: 0.875rem;
    }
    .ai-ct-pro__icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--ai-ct-accent) 0%, var(--ai-ct-accent2) 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.28);
    }
    .ai-ct-pro__title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.02em;
        margin: 0 0 0.25rem 0;
        line-height: 1.25;
    }
    .ai-ct-pro__subtitle {
        margin: 0;
        font-size: 0.875rem;
        color: var(--ai-ct-muted);
        line-height: 1.45;
        max-width: 36rem;
    }
    .ai-ct-pro__meta {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }
    .ai-ct-pro__count {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--ai-ct-muted);
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid var(--ai-ct-border);
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
    }
    .ai-ct-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--ai-ct-border);
        background: var(--ai-ct-surface);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        text-decoration: none;
        color: inherit;
    }
    .ai-ct-card:hover {
        border-color: rgba(99, 102, 241, 0.45);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        transform: translateY(-2px);
        color: inherit;
    }
    .ai-ct-card:focus-visible {
        outline: 2px solid var(--ai-ct-accent);
        outline-offset: 2px;
    }
    .ai-ct-card__media {
        position: relative;
        aspect-ratio: 16 / 10;
        min-height: 100px;
        background: linear-gradient(160deg, #f1f5f9 0%, #e2e8f0 100%);
        overflow: hidden;
    }
    .ai-ct-card__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .ai-ct-card__placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 2rem;
    }
    .ai-ct-card__badge {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.2rem 0.45rem;
        border-radius: 6px;
        background: rgba(15, 23, 42, 0.72);
        color: #fff;
        backdrop-filter: blur(6px);
    }
    .ai-ct-card__body {
        padding: 0.75rem 0.875rem 0.95rem;
        display: flex;
        flex-direction: column;
        flex: 1;
        gap: 0.35rem;
    }
    .ai-ct-card__name {
        font-size: 0.8125rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .ai-ct-card__desc {
        font-size: 0.75rem;
        color: var(--ai-ct-muted);
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }
    .ai-ct-card__cta {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--ai-ct-accent);
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 0.15rem;
    }
    .ai-ct-card:hover .ai-ct-card__cta {
        color: #4f46e5;
    }
    .ai-ct-card-wrap {
        display: flex;
        flex-direction: column;
        height: 100%;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--ai-ct-border);
        background: var(--ai-ct-surface);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .ai-ct-card-wrap:hover {
        border-color: rgba(99, 102, 241, 0.45);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        transform: translateY(-2px);
    }
    .ai-ct-card-wrap .ai-ct-card {
        border: none;
        border-radius: 0;
        box-shadow: none;
        transform: none;
    }
    .ai-ct-card-wrap .ai-ct-card:hover {
        box-shadow: none;
        transform: none;
    }
    .ai-ct-card__recent {
        border-top: 1px solid #f1f5f9;
        padding: 0.5rem 0.65rem 0.65rem;
        background: #fafbfc;
    }
    .ai-ct-card__recent-label {
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
        margin-bottom: 0.35rem;
        display: block;
    }
    .ai-ct-card__recent-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.35rem;
        font-size: 0.68rem;
        padding: 0.2rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .ai-ct-card__recent-row:last-child { border-bottom: none; padding-bottom: 0; }
    .ai-ct-card__recent-name {
        color: #475569;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ai-ct-card__recent-open {
        flex-shrink: 0;
        font-weight: 600;
        color: var(--ai-ct-accent);
        text-decoration: none;
    }
    .ai-ct-card__recent-open:hover { color: #4f46e5; text-decoration: underline; }
</style>
@endpush
@endonce

<div class="ai-ct-pro">
    <div class="ai-ct-pro__shell">
        <div class="ai-ct-pro__head">
            <div class="ai-ct-pro__title-row">
                <div class="ai-ct-pro__icon" aria-hidden="true">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <h2 class="ai-ct-pro__title">AI content templates</h2>
                    <p class="ai-ct-pro__subtitle">Start from a guided brief—fill in a few fields and we generate a ready-to-edit layout in the design tool.</p>
                </div>
            </div>
            <div class="ai-ct-pro__meta">
                <span class="ai-ct-pro__count">{{ $aiContentTemplates->count() }} {{ Str::plural('template', $aiContentTemplates->count()) }}</span>
            </div>
        </div>
        <div class="row g-3">
            @foreach($aiContentTemplates as $tpl)
            @php
                $recentForTpl = $aiTemplateRecentGenerations->get($tpl->id, collect());
            @endphp
            <div class="col-6 col-md-4 col-lg-3">
                <div class="ai-ct-card-wrap">
                    <a href="{{ route('design.aiContentTemplates.form', $tpl->id) }}" class="ai-ct-card">
                        <div class="ai-ct-card__media">
                            <span class="ai-ct-card__badge">AI</span>
                            @if($tpl->image_url)
                            <img src="{{ $tpl->image_url }}" alt="{{ $tpl->name }}" loading="lazy">
                            @else
                            <div class="ai-ct-card__placeholder" aria-hidden="true">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            @endif
                        </div>
                        <div class="ai-ct-card__body">
                            <div class="ai-ct-card__name">{{ $tpl->name }}</div>
                            @if($tpl->description)
                            <div class="ai-ct-card__desc">{{ $tpl->description }}</div>
                            @else
                            <div class="ai-ct-card__desc text-muted" style="opacity: 0.75;">Personalize and generate in one step.</div>
                            @endif
                            <span class="ai-ct-card__cta">Configure <i class="fas fa-arrow-right" style="font-size: 0.65rem;"></i></span>
                        </div>
                    </a>
                    @auth
                    @if($recentForTpl->isNotEmpty())
                    <div class="ai-ct-card__recent">
                        <span class="ai-ct-card__recent-label">Your recent (this template)</span>
                        @foreach($recentForTpl as $gen)
                        <div class="ai-ct-card__recent-row">
                            <span class="ai-ct-card__recent-name" title="{{ $gen->name }}">{{ Str::limit($gen->name, 28) }}</span>
                            <a href="{{ route('design.aiContentGenerations.open', $gen) }}" class="ai-ct-card__recent-open">Open</a>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @endauth
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
