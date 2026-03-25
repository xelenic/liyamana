@extends('layouts.admin')

@section('title', 'Explore Page Slider')
@section('page-title', 'Explore Page Slider')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-images me-2 text-primary"></i>Explore Page Slider
            </h2>
            <p class="text-muted mb-0">Manage image slides with descriptions shown on the template explore page</p>
        </div>
        <a href="{{ route('admin.explore-slides.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Slide
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; width: 100px;">Image</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Title</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Description</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Sort</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slides as $slide)
                        <tr>
                            <td style="padding: 1rem;">
                                @if($slide->image_url)
                                    <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}" style="width: 80px; height: 50px; object-fit: cover; border-radius: 6px;">
                                @else
                                    <div style="width: 80px; height: 50px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #94a3b8;"><i class="fas fa-image"></i></div>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.9rem; font-weight: 500;">{{ $slide->title ?: '—' }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">{{ Str::limit($slide->description, 60) ?: '—' }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem;">{{ $slide->sort_order }}</td>
                            <td style="padding: 1rem;">
                                @if($slide->is_active)
                                    <span class="badge bg-success" style="font-size: 0.75rem;">Active</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 0.75rem;">Inactive</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <div class="btn-group">
                                    <a href="{{ route('admin.explore-slides.edit', $slide->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.explore-slides.delete', $slide->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this slide?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No slides yet. <a href="{{ route('admin.explore-slides.create') }}">Add one</a></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($slides->hasPages())
    <div class="mt-3">{{ $slides->links() }}</div>
    @endif
</div>
@endsection
