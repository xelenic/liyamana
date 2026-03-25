@extends('layouts.admin')

@section('title', 'Session recording & heatmap')
@section('page-title', 'Session recording & heatmap')

@php
    $currentTab = old('settings_active_tab', $activeTab ?? 'session');
    if (! in_array($currentTab, ['session', 'heatmap'], true)) {
        $currentTab = 'session';
    }
    $tabSession = $currentTab === 'session';
    $tabHeatmap = $currentTab === 'heatmap';
@endphp

@section('content')
<div class="my-2 settings-page-compact">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.settings.session-recording.update') }}" method="POST" id="sessionRecordingSettingsForm">
        @csrf
        <input type="hidden" name="settings_active_tab" id="settings_active_tab" value="{{ $currentTab }}">

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-3" style="font-size: 0.85rem;">
                            Configure rrweb session replay and click heatmaps. Saved values override <code>.env</code> when set.
                        </p>

                        <ul class="nav nav-tabs mb-3" id="sessionHeatmapSettingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $tabSession ? 'active' : '' }}" id="tab-session-trigger" data-bs-toggle="tab" data-bs-target="#tab-pane-session" type="button" role="tab" aria-controls="tab-pane-session" aria-selected="{{ $tabSession ? 'true' : 'false' }}" data-settings-tab="session">
                                    <i class="fas fa-circle-play me-1"></i>Session recording
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $tabHeatmap ? 'active' : '' }}" id="tab-heatmap-trigger" data-bs-toggle="tab" data-bs-target="#tab-pane-heatmap" type="button" role="tab" aria-controls="tab-pane-heatmap" aria-selected="{{ $tabHeatmap ? 'true' : 'false' }}" data-settings-tab="heatmap">
                                    <i class="fas fa-fire me-1 text-danger"></i>Heatmap
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="sessionHeatmapSettingsTabContent">
                            <div class="tab-pane fade {{ $tabSession ? 'show active' : '' }}" id="tab-pane-session" role="tabpanel" aria-labelledby="tab-session-trigger" tabindex="0">
                                <p class="text-muted mb-4" style="font-size: 0.85rem;">
                                    Review replays under <strong>Users statistics → Session recordings</strong>.
                                </p>

                                @php $item = $settings['session_recording_enabled'] ?? null; @endphp
                                @if($item)
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="session_recording_enabled" value="0">
                                        <input class="form-check-input" type="checkbox" id="session_recording_enabled" name="session_recording_enabled" value="1" {{ old('session_recording_enabled', $item['value']) == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="session_recording_enabled">{{ $item['label'] }}</label>
                                    </div>
                                    @if(!empty($item['help']))
                                        <small class="text-muted d-block mt-1">{{ $item['help'] }}</small>
                                    @endif
                                </div>
                                @endif

                                <h6 class="mb-3" style="font-weight: 600; color: #1e293b;">Limits &amp; JSON</h6>

                                @php $item = $settings['session_recording_max_bytes_per_session'] ?? null; @endphp
                                @if($item)
                                <div class="mb-3">
                                    <label for="session_recording_max_bytes_per_session" class="form-label">{{ $item['label'] }}</label>
                                    <input type="text" inputmode="numeric" class="form-control font-monospace" style="max-width: 280px;" id="session_recording_max_bytes_per_session" name="session_recording_max_bytes_per_session" value="{{ old('session_recording_max_bytes_per_session', $item['value']) }}" placeholder="{{ $item['config_default'] ?? '' }}">
                                    <small class="text-muted d-block mt-1">Config default: <code>{{ $item['config_default'] ?? '—' }}</code>. @if(!empty($item['help'])) {{ $item['help'] }} @endif</small>
                                </div>
                                @endif

                                @php $item = $settings['session_recording_max_replay_bytes'] ?? null; @endphp
                                @if($item)
                                <div class="mb-3">
                                    <label for="session_recording_max_replay_bytes" class="form-label">{{ $item['label'] }}</label>
                                    <input type="text" inputmode="numeric" class="form-control font-monospace" style="max-width: 280px;" id="session_recording_max_replay_bytes" name="session_recording_max_replay_bytes" value="{{ old('session_recording_max_replay_bytes', $item['value']) }}" placeholder="{{ $item['config_default'] ?? '' }}">
                                    <small class="text-muted d-block mt-1">Config default: <code>{{ $item['config_default'] ?? '—' }}</code>. @if(!empty($item['help'])) {{ $item['help'] }} @endif</small>
                                </div>
                                @endif

                                @php $item = $settings['session_recording_json_max_depth'] ?? null; @endphp
                                @if($item)
                                <div class="mb-4">
                                    <label for="session_recording_json_max_depth" class="form-label">{{ $item['label'] }}</label>
                                    <input type="text" inputmode="numeric" class="form-control font-monospace" style="max-width: 280px;" id="session_recording_json_max_depth" name="session_recording_json_max_depth" value="{{ old('session_recording_json_max_depth', $item['value']) }}" placeholder="{{ $item['config_default'] ?? '' }}">
                                    <small class="text-muted d-block mt-1">Config default: <code>{{ $item['config_default'] ?? '—' }}</code>. @if(!empty($item['help'])) {{ $item['help'] }} @endif</small>
                                </div>
                                @endif

                                <h6 class="mb-3" style="font-weight: 600; color: #1e293b;">CDN URLs (optional)</h6>
                                <p class="text-muted mb-3" style="font-size: 0.8rem;">Override only if you self-host rrweb bundles or need a pinned mirror. Leave blank to use config defaults.</p>

                                @foreach(['session_recording_cdn_rrweb', 'session_recording_cdn_player', 'session_recording_cdn_player_css'] as $cdnKey)
                                    @php $item = $settings[$cdnKey] ?? null; @endphp
                                    @if($item)
                                    <div class="mb-3">
                                        <label for="{{ $cdnKey }}" class="form-label">{{ $item['label'] }}</label>
                                        <input type="url" class="form-control" id="{{ $cdnKey }}" name="{{ $cdnKey }}" value="{{ old($cdnKey, $item['value']) }}" placeholder="{{ $item['config_default'] ?? '' }}" autocomplete="off">
                                        @if(!empty($item['help']))
                                            <small class="text-muted d-block mt-1">{{ $item['help'] }}</small>
                                        @endif
                                    </div>
                                    @endif
                                @endforeach
                            </div>

                            <div class="tab-pane fade {{ $tabHeatmap ? 'show active' : '' }}" id="tab-pane-heatmap" role="tabpanel" aria-labelledby="tab-heatmap-trigger" tabindex="0">
                                <p class="text-muted mb-4" style="font-size: 0.85rem;">
                                    View data under <strong>Users statistics → User heatmaps</strong>. Optional limits override <code>config/user_heatmap.php</code> / <code>.env</code>.
                                </p>

                                @php $item = $settings['user_heatmap_enabled'] ?? null; @endphp
                                @if($item)
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="user_heatmap_enabled" value="0">
                                        <input class="form-check-input" type="checkbox" id="user_heatmap_enabled" name="user_heatmap_enabled" value="1" {{ old('user_heatmap_enabled', $item['value']) == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="user_heatmap_enabled">{{ $item['label'] }}</label>
                                    </div>
                                    @if(!empty($item['help']))
                                        <small class="text-muted d-block mt-1">{{ $item['help'] }}</small>
                                    @endif
                                </div>
                                @endif

                                @php $item = $settings['user_heatmap_max_clicks_per_ingest'] ?? null; @endphp
                                @if($item)
                                <div class="mb-3">
                                    <label for="user_heatmap_max_clicks_per_ingest" class="form-label">{{ $item['label'] }}</label>
                                    <input type="text" inputmode="numeric" class="form-control font-monospace" style="max-width: 280px;" id="user_heatmap_max_clicks_per_ingest" name="user_heatmap_max_clicks_per_ingest" value="{{ old('user_heatmap_max_clicks_per_ingest', $item['value']) }}" placeholder="{{ $item['config_default'] ?? '' }}">
                                    <small class="text-muted d-block mt-1">Config default: <code>{{ $item['config_default'] ?? '—' }}</code>. @if(!empty($item['help'])) {{ $item['help'] }} @endif</small>
                                </div>
                                @endif

                                @php $item = $settings['user_heatmap_admin_max_points_per_response'] ?? null; @endphp
                                @if($item)
                                <div class="mb-2">
                                    <label for="user_heatmap_admin_max_points_per_response" class="form-label">{{ $item['label'] }}</label>
                                    <input type="text" inputmode="numeric" class="form-control font-monospace" style="max-width: 280px;" id="user_heatmap_admin_max_points_per_response" name="user_heatmap_admin_max_points_per_response" value="{{ old('user_heatmap_admin_max_points_per_response', $item['value']) }}" placeholder="{{ $item['config_default'] ?? '' }}">
                                    <small class="text-muted d-block mt-1">Config default: <code>{{ $item['config_default'] ?? '—' }}</code>. @if(!empty($item['help'])) {{ $item['help'] }} @endif</small>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save all
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-2" style="font-size: 0.9rem;">
                            <i class="fas fa-circle-play text-primary me-1"></i>Session recording
                        </h5>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">
                            Empty numeric or URL fields use <code>config/session_recording.php</code> and <code>.env</code>. After CDN changes, hard-refresh the browser when testing replay.
                        </p>
                    </div>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-2" style="font-size: 0.9rem;">
                            <i class="fas fa-fire text-danger me-1"></i>Heatmap
                        </h5>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">
                            Empty limits use <code>config/user_heatmap.php</code> and <code>.env</code>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    var hidden = document.getElementById('settings_active_tab');
    if (!hidden) return;
    document.querySelectorAll('[data-settings-tab]').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function () {
            hidden.value = btn.getAttribute('data-settings-tab') || 'session';
        });
    });
})();
</script>
@endpush

@push('styles')
<style>
    .settings-page-compact .nav-tabs .nav-link {
        font-size: 0.85rem;
        padding: 0.5rem 0.85rem;
        color: #64748b;
        border: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
    }
    .settings-page-compact .nav-tabs .nav-link:hover {
        color: var(--primary-color, #6366f1);
        border-color: transparent;
    }
    .settings-page-compact .nav-tabs .nav-link.active {
        color: var(--primary-color, #6366f1);
        font-weight: 600;
        background: transparent;
        border-bottom-color: var(--primary-color, #6366f1);
    }
    .settings-page-compact .nav-tabs {
        border-bottom: 1px solid var(--border-color, #e2e8f0);
    }
    .settings-page-compact .form-label {
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }
    .settings-page-compact .form-control {
        font-size: 0.8rem;
        padding: 0.35rem 0.5rem;
        min-height: 32px;
    }
    .settings-page-compact small.text-muted {
        font-size: 0.7rem;
        margin-top: 0.15rem;
    }
    .settings-page-compact .btn {
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
    }
    .settings-page-compact code {
        font-size: 0.7rem;
        word-break: break-all;
    }
</style>
@endpush
@endsection
