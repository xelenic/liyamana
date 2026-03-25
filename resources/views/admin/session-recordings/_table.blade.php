@php
    /** @var \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator $recordings */
@endphp
<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead style="background: #f8fafc;">
            <tr>
                <th class="small text-muted">Started</th>
                <th class="small text-muted">Landing</th>
                <th class="small text-muted">Size</th>
                <th class="small text-muted text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recordings as $rec)
                <tr>
                    <td class="small">{{ $rec->started_at?->format('Y-m-d H:i') }}</td>
                    <td class="small text-truncate" style="max-width: 280px;" title="{{ $rec->landing_path }}">
                        {{ $rec->landing_path ?? '—' }}
                    </td>
                    <td class="small">{{ number_format($rec->byte_size / 1024, 1) }} KB</td>
                    <td class="text-center">
                        <a href="{{ route('admin.session-recordings.replay', $rec->uuid) }}" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.75rem;">
                            <i class="fas fa-play me-1"></i>Replay
                        </a>
                        <form method="post" action="{{ route('admin.session-recordings.destroy', $rec->uuid) }}" class="d-inline"
                              onsubmit="return confirm('Delete this recording permanently?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 ms-1" style="font-size: 0.75rem;">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-3 text-muted small">No recordings in this group.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
