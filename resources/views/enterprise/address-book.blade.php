@extends('layouts.app')
@section('title', 'Address Book - Enterprise - ' . site_name())
@section('page-title', 'Enterprise')

@include('enterprise.partials.panel-styles')

@push('styles')
<style>
    .enterprise-address-book .mailbox-list { padding: 1rem 1.25rem; border-bottom: none; }
    .enterprise-address-book .mailbox-empty { flex: 1; display: flex; align-items: center; justify-content: center; color: #94a3b8; padding: 2rem; text-align: center; }
</style>
@endpush

@section('content')
<div class="enterprise-panel enterprise-address-book">
    <div class="enterprise-mailbox">
        @include('enterprise.partials.sidebar', ['activeSection' => 'address-book'])

        <div class="mailbox-main">
            <div class="mailbox-toolbar">
                <span class="text-muted" style="font-size: 0.9rem;">Address Book</span>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('enterprise') }}" class="btn btn-sm btn-outline-secondary d-none d-md-inline-block"><i class="fas fa-th-large me-1"></i>Dashboard</a>
                    <a href="{{ route('enterprise.address-book.export-csv') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-file-export me-1"></i>Export CSV
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                        <i class="fas fa-file-import me-1"></i>Import CSV
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addressModal" onclick="openAddressForm()">
                        <i class="fas fa-plus me-1"></i>Add address
                    </button>
                </div>
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
                @forelse($addresses as $addr)
                <div class="addr-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="addr-name">{{ $addr->contact_name }}{{ $addr->label ? ' · ' . $addr->label : '' }}</div>
                            @if($addr->email)<div class="addr-meta"><i class="fas fa-envelope me-1"></i>{{ $addr->email }}</div>@endif
                            @if($addr->phone)<div class="addr-meta"><i class="fas fa-phone me-1"></i>{{ $addr->phone }}</div>@endif
                            <div class="addr-meta mt-1">{{ $addr->full_address }}</div>
                        </div>
                        <div class="addr-actions d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick='editAddress({{ json_encode($addr->only(["id","label","contact_name","email","phone","address_line1","address_line2","city","state","postal_code","country"])) }})' title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('enterprise.address-book.destroy', $addr->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this address?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="mailbox-empty">
                    <div>
                        <i class="fas fa-address-book fa-3x text-muted mb-3 d-block"></i>
                        <p class="mb-2">No addresses yet</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addressModal" onclick="openAddressForm()">
                            <i class="fas fa-plus me-1"></i>Add address
                        </button>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addressModalTitle">Add address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addressForm" method="POST" action="{{ route('enterprise.address-book.store') }}">
                @csrf
                <div id="addressFormMethod"></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Label <span class="text-muted">(optional)</span></label>
                        <input type="text" name="label" class="form-control" placeholder="e.g. Home, Office" maxlength="64" value="{{ old('label') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Contact name <span class="text-danger">*</span></label>
                        <input type="text" name="contact_name" class="form-control" required maxlength="255" value="{{ old('contact_name') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" maxlength="255" value="{{ old('email') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" maxlength="32" value="{{ old('phone') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Address line 1</label>
                        <input type="text" name="address_line1" class="form-control" maxlength="255" value="{{ old('address_line1') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Address line 2</label>
                        <input type="text" name="address_line2" class="form-control" maxlength="255" value="{{ old('address_line2') }}">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" maxlength="64" value="{{ old('city') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">State / Region</label>
                            <input type="text" name="state" class="form-control" maxlength="64" value="{{ old('state') }}">
                        </div>
                    </div>
                    <div class="row g-2 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">Postal code</label>
                            <input type="text" name="postal_code" class="form-control" maxlength="20" value="{{ old('postal_code') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country (code)</label>
                            <input type="text" name="country" class="form-control" maxlength="2" placeholder="e.g. US" value="{{ old('country') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import CSV Modal -->
<div class="modal fade" id="importCsvModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('enterprise.address-book.import-csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">Upload a CSV with columns: <code>label</code>, <code>contact_name</code>, <code>email</code>, <code>phone</code>, <code>address_line1</code>, <code>address_line2</code>, <code>city</code>, <code>state</code>, <code>postal_code</code>, <code>country</code>. First row can be a header.</p>
                    <div class="mb-0">
                        <label class="form-label">CSV file</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-file-import me-1"></i>Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openAddressForm() {
    document.getElementById('addressModalTitle').textContent = 'Add address';
    document.getElementById('addressForm').action = '{{ route('enterprise.address-book.store') }}';
    document.getElementById('addressFormMethod').innerHTML = '';
    document.querySelectorAll('#addressForm input[name]').forEach(function(inp) {
        if (inp.name !== '_token') inp.value = '';
    });
}
function editAddress(addr) {
    document.getElementById('addressModalTitle').textContent = 'Edit address';
    document.getElementById('addressForm').action = '{{ url('enterprise/address-book') }}/' + addr.id;
    document.getElementById('addressFormMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    var names = ['label','contact_name','email','phone','address_line1','address_line2','city','state','postal_code','country'];
    names.forEach(function(name) {
        var el = document.querySelector('#addressForm input[name="' + name + '"]');
        if (el) el.value = addr[name] || '';
    });
    new bootstrap.Modal(document.getElementById('addressModal')).show();
}
</script>
@endpush
