@extends('layouts.admin')

@section('title', 'Design fonts')
@section('page-title', 'Design fonts')

@section('content')
<div class="my-2">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="text-muted mb-0 small">These fonts load for every user in the multi-page design tool (font picker and CSS font-face).</p>
        <a href="{{ route('admin.design-fonts.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add font</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Display name</th>
                        <th>File</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fonts as $font)
                        <tr>
                            <td>{{ $font->sort_order }}</td>
                            <td class="fw-600">{{ $font->name }}</td>
                            <td class="small text-muted text-truncate" style="max-width: 200px;">{{ $font->original_filename }}</td>
                            <td><code class="small">{{ strtoupper($font->extension) }}</code></td>
                            <td>
                                @if($font->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Off</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.design-fonts.edit', $font->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.design-fonts.destroy', $font->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this font from storage and the editor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No fonts yet. Add TTF, OTF, WOFF, or WOFF2 files.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($fonts->hasPages())
            <div class="card-footer bg-white">{{ $fonts->links() }}</div>
        @endif
    </div>
</div>
@endsection
