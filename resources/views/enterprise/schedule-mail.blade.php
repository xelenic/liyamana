@extends('layouts.app')
@section('title', 'Schedule mail - Enterprise - ' . site_name())
@section('page-title', 'Enterprise')

@include('enterprise.partials.panel-styles')

@push('styles')
<style>
    .enterprise-schedule-mail .mailbox-list { padding: 1rem 1.25rem; border-bottom: none; }
    .enterprise-schedule-mail .mailbox-empty { padding: 2rem; text-align: center; color: #94a3b8; }
</style>
@endpush

@section('content')
<div class="enterprise-panel enterprise-schedule-mail">
    <div class="enterprise-mailbox">
        @include('enterprise.partials.sidebar', ['activeSection' => 'schedule-mail'])

        <div class="mailbox-main">
            <div class="mailbox-toolbar">
                <div>
                    <span class="d-block text-muted" style="font-size: 0.9rem;">Schedule mail</span>
                    <span class="small text-muted">Credit is deducted automatically when the mail is sent.</span>
                </div>
                <a href="{{ route('enterprise') }}" class="btn btn-sm btn-outline-secondary ms-auto"><i class="fas fa-th-large me-1"></i>Dashboard</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3 mb-0" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show m-3 mb-0" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="mailbox-list">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0">Schedule new mail</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('enterprise.schedule-mail.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Template</label>
                                    <select name="template_id" class="form-select" required>
                                        <option value="">Select template</option>
                                        @foreach($templates as $t)
                                            <option value="{{ $t->id }}" {{ old('template_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Recipient (Address Book)</label>
                                    <select name="address_book_id" class="form-select" required>
                                        <option value="">Select recipient</option>
                                        @foreach($addresses as $a)
                                            <option value="{{ $a->id }}" {{ old('address_book_id') == $a->id ? 'selected' : '' }}>{{ $a->contact_name }}{{ $a->label ? ' · ' . $a->label : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12" id="templateVariablesContainer" style="display: none;">
                                    <label class="form-label d-block mb-2">Template variables</label>
                                    <div id="templateVariablesFields" class="card border-0 bg-light rounded p-3"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Send at (date & time)</label>
                                    <input type="datetime-local" name="send_at" class="form-control" required min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}" value="{{ old('send_at') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Credit to deduct</label>
                                    <input type="number" name="credit_amount" class="form-control" step="0.01" min="0" placeholder="{{ $defaultCreditCost }}" value="{{ old('credit_amount', $defaultCreditCost) }}">
                                    <small class="text-muted">Uses default if empty</small>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-plus me-1"></i>Schedule</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <h6 class="mb-2">Scheduled mails</h6>
                @forelse($scheduled as $s)
                <div class="sched-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $s->template_name }}</strong>
                            <div class="sched-meta">
                                To: {{ $s->recipient_snapshot['contact_name'] ?? ($s->addressBook->contact_name ?? '—') }}
                                &middot; {{ $s->send_at->format('M d, Y H:i') }}
                                &middot; {{ format_price($s->credit_amount) }}
                            </div>
                            @if($s->status === 'pending')
                                <span class="badge bg-warning text-dark mt-1">Pending</span>
                            @elseif($s->status === 'sent')
                                <span class="badge bg-success mt-1">Sent</span>
                                @if($s->order_id)
                                    <a href="{{ route('orders.show', $s->order_id) }}" class="ms-1 small">View order</a>
                                @endif
                            @elseif($s->status === 'cancelled')
                                <span class="badge bg-secondary mt-1">Cancelled</span>
                            @else
                                <span class="badge bg-danger mt-1">Failed</span>
                                @if($s->error_message)<br><small class="text-danger">{{ \Illuminate\Support\Str::limit($s->error_message, 80) }}</small>@endif
                            @endif
                        </div>
                        @if($s->status === 'pending')
                        <form action="{{ route('enterprise.schedule-mail.destroy', $s->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this scheduled mail?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="mailbox-empty">
                    <p class="mb-0">No scheduled mails. Use the form above to schedule one.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const templatesData = @json($templates->keyBy('id')->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'variables' => $t->variables ?? []])->keyBy('id'));
    const oldVariables = @json(old('variables', []));
    const container = document.getElementById('templateVariablesContainer');
    const fieldsEl = document.getElementById('templateVariablesFields');
    const templateSelect = document.querySelector('select[name="template_id"]');
    if (!templateSelect || !container || !fieldsEl) return;

    function escapeAttr(s) {
        return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function renderVariables(templateId) {
        const t = templatesData[templateId];
        if (!t || !t.variables || t.variables.length === 0) {
            container.style.display = 'none';
            fieldsEl.innerHTML = '';
            return;
        }
        let html = '';
        t.variables.forEach(function(v, i) {
            const label = escapeAttr(v.name || ('Variable ' + (i + 1)));
            const req = v.required ? ' required' : '';
            const rawName = v.name || ('var_' + i);
            const nameAttr = 'variables[' + rawName.replace(/"/g, '&quot;') + ']';
            const oldVal = oldVariables[rawName] != null ? String(oldVariables[rawName]) : '';
            const oldValEsc = escapeAttr(oldVal);
            html += '<div class="mb-3">';
            html += '<label class="form-label">' + label + (v.required ? ' <span class="text-danger">*</span>' : '') + '</label>';
            if (v.form_type === 'select' && v.options && v.options.length) {
                html += '<select name="' + nameAttr + '" class="form-control form-control-sm"' + req + '><option value="">Select</option>';
                v.options.forEach(function(o) {
                    const sel = (oldVal && oldVal === String(o)) ? ' selected' : '';
                    html += '<option value="' + escapeAttr(o) + '"' + sel + '>' + escapeAttr(o) + '</option>';
                });
                html += '</select>';
            } else if (v.form_type === 'textarea') {
                html += '<textarea name="' + nameAttr + '" class="form-control form-control-sm" rows="2"' + req + '>' + oldValEsc + '</textarea>';
            } else {
                html += '<input type="text" name="' + nameAttr + '" class="form-control form-control-sm" value="' + oldValEsc + '"' + req + '>';
            }
            html += '</div>';
        });
        fieldsEl.innerHTML = html;
        container.style.display = 'block';
    }

    templateSelect.addEventListener('change', function() {
        const id = this.value;
        if (id) renderVariables(id); else { container.style.display = 'none'; fieldsEl.innerHTML = ''; }
    });

    if (templateSelect.value) renderVariables(templateSelect.value);
})();
</script>
@endpush
@endsection
