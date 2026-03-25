@auth
@if(isset($aiGenerationsForTemplate) && $aiGenerationsForTemplate->isNotEmpty())
<div class="ai-ct-gen-list card border-0 shadow-sm mb-3" style="border-radius: 16px; overflow: hidden;">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="h6 mb-0 fw-bold" style="color: #0f172a;"><i class="fas fa-history me-2 text-primary"></i>Your generations (this template)</h2>
            <p class="small text-muted mb-0 mt-1">Open a past run in the multi-page editor. New jobs also appear here after they finish.</p>
        </div>
    </div>
    <ul class="list-group list-group-flush">
        @foreach($aiGenerationsForTemplate as $gen)
        <li class="list-group-item d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
            <div class="min-w-0">
                <div class="fw-semibold text-dark text-truncate" style="max-width: 100%;" title="{{ $gen->name }}">{{ Str::limit($gen->name, 64) }}</div>
                <div class="small text-muted">{{ $gen->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }} · {{ $gen->page_count }} {{ Str::plural('page', $gen->page_count) }}</div>
            </div>
            <a href="{{ route('design.aiContentGenerations.open', $gen) }}" class="btn btn-sm btn-primary rounded-3 flex-shrink-0">
                <i class="fas fa-external-link-alt me-1"></i>Open in editor
            </a>
        </li>
        @endforeach
    </ul>
</div>
@elseif(isset($aiGenerationsRecent) && $aiGenerationsRecent->isNotEmpty())
<div class="ai-ct-gen-list card border-0 shadow-sm mb-3" style="border-radius: 16px; overflow: hidden;">
    <div class="card-header bg-white border-bottom py-3">
        <h2 class="h6 mb-0 fw-bold" style="color: #0f172a;"><i class="fas fa-history me-2 text-primary"></i>Your recent AI generations</h2>
        <p class="small text-muted mb-0 mt-1">You have not generated this template yet. Here are your latest AI designs from other templates.</p>
    </div>
    <ul class="list-group list-group-flush">
        @foreach($aiGenerationsRecent as $gen)
        <li class="list-group-item d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
            <div class="min-w-0">
                <div class="fw-semibold text-dark text-truncate" style="max-width: 100%;" title="{{ $gen->name }}">{{ Str::limit($gen->name, 64) }}</div>
                <div class="small text-muted">
                    {{ $gen->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                    @if($gen->aiContentTemplate)
                        · {{ Str::limit($gen->aiContentTemplate->name, 32) }}
                    @endif
                </div>
            </div>
            <a href="{{ route('design.aiContentGenerations.open', $gen) }}" class="btn btn-sm btn-outline-primary rounded-3 flex-shrink-0">
                <i class="fas fa-external-link-alt me-1"></i>Open
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endif
@endauth
