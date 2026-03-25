@extends('layouts.admin')

@section('title', 'Design Intro Tour')
@section('page-title', 'Design Intro Tour')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-route me-2 text-primary"></i>Design Intro Tour
            </h2>
            <p class="text-muted mb-0">Intro.js tours: Multi-Page Design Tool and Explore Templates page. Set when to show and manage steps per tour.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Design Tool: Show mode -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3" style="font-weight: 600; color: #1e293b;">
                <i class="fas fa-palette me-1 text-primary"></i>Multi-Page Design Tool – when to show intro
            </h5>
            <form action="{{ route('admin.design-intro.settings') }}" method="POST" class="row align-items-end g-3">
                @csrf
                <div class="col-md-6 col-lg-4">
                    <label for="design_intro_show_mode" class="form-label">Display option</label>
                    <select name="design_intro_show_mode" id="design_intro_show_mode" class="form-select">
                        <option value="first_time" {{ $showMode === 'first_time' ? 'selected' : '' }}>First time (this device)</option>
                        <option value="first_time_account" {{ $showMode === 'first_time_account' ? 'selected' : '' }}>First time (per account, once)</option>
                        <option value="always" {{ $showMode === 'always' ? 'selected' : '' }}>Every time</option>
                        <option value="never" {{ $showMode === 'never' ? 'selected' : '' }}>Never</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Design Tool: Steps -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
            <h5 class="mb-0" style="font-weight: 600; color: #1e293b;">
                <i class="fas fa-list-ol me-1 text-primary"></i>Steps – Multi-Page Design Tool
            </h5>
            <a href="{{ route('admin.design-intro.steps.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add step</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">#</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Element (selector)</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Title</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Intro text</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($steps as $step)
                        <tr>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">{{ $step->sort_order }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem; font-family: monospace;">{{ $step->element_selector ?: '—' }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem;">{{ $step->title ?: '—' }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">{{ Str::limit(strip_tags($step->intro_text), 60) }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <a href="{{ route('admin.design-intro.steps.edit', $step->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.design-intro.steps.delete', $step->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this step?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No steps. <a href="{{ route('admin.design-intro.steps.create') }}">Add step</a> or the page uses built-in defaults.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Explore Page: Show mode -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3" style="font-weight: 600; color: #1e293b;">
                <i class="fas fa-compass me-1 text-primary"></i>Explore Templates page – when to show intro
            </h5>
            <form action="{{ route('admin.design-intro.explore-settings') }}" method="POST" class="row align-items-end g-3">
                @csrf
                <div class="col-md-6 col-lg-4">
                    <label for="design_intro_explore_show_mode" class="form-label">Display option</label>
                    <select name="design_intro_explore_show_mode" id="design_intro_explore_show_mode" class="form-select">
                        <option value="first_time" {{ ($exploreShowMode ?? 'first_time') === 'first_time' ? 'selected' : '' }}>First time (this device)</option>
                        <option value="first_time_account" {{ ($exploreShowMode ?? '') === 'first_time_account' ? 'selected' : '' }}>First time (per account, once)</option>
                        <option value="always" {{ ($exploreShowMode ?? '') === 'always' ? 'selected' : '' }}>Every time</option>
                        <option value="never" {{ ($exploreShowMode ?? '') === 'never' ? 'selected' : '' }}>Never</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Explore Page: Steps -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
            <h5 class="mb-0" style="font-weight: 600; color: #1e293b;">
                <i class="fas fa-list-ol me-1 text-primary"></i>Steps – Explore Templates page
            </h5>
            <a href="{{ route('admin.design-intro.steps.create', ['tour' => 'templates_explore']) }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add step</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">#</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Element (selector)</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Title</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Intro text</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exploreSteps ?? [] as $step)
                        <tr>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">{{ $step->sort_order }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem; font-family: monospace;">{{ $step->element_selector ?: '—' }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem;">{{ $step->title ?: '—' }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">{{ Str::limit(strip_tags($step->intro_text), 60) }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <a href="{{ route('admin.design-intro.steps.edit', $step->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.design-intro.steps.delete', $step->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this step?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No steps. <a href="{{ route('admin.design-intro.steps.create', ['tour' => 'templates_explore']) }}">Add step</a> or the explore page uses built-in defaults.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
