@extends('layouts.app')

@section('title', 'Address Book - ' . site_name())
@section('page-title', 'Address Book')

@push('styles')
<style>
    :root {
        --primary-color: #6366f1;
        --border-color: #e2e8f0;
        --dark-text: #1e293b;
    }
    .addr-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
        transition: box-shadow 0.2s;
    }
    .addr-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .addr-card .addr-name { font-weight: 600; color: var(--dark-text); margin-bottom: 0.25rem; }
    .addr-card .addr-meta { font-size: 0.875rem; color: #64748b; }
    .addr-empty {
        text-align: center;
        padding: 3rem 2rem;
        color: #94a3b8;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px dashed var(--border-color);
    }
    .form-label { font-weight: 600; font-size: 0.8125rem; color: #475569; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-3"></i>
            <span class="flex-grow-1">{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 list-unstyled">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-address-book me-2 text-primary"></i>Address Book
            </h2>
            <p class="text-muted mb-0">Save and manage your contacts and addresses</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('user.address-book.export-csv') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-file-export me-1"></i>Export CSV
            </a>
            <a href="{{ route('user.address-book.import-google') }}" class="btn btn-sm btn-outline-primary">
                <i class="fab fa-google me-1"></i>Import from Google
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addressModal" onclick="openAddressForm()">
                <i class="fas fa-plus me-1"></i>Add address
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10">
            @forelse($addresses as $addr)
            <div class="addr-card">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <div class="addr-name">{{ $addr->contact_name }}{{ $addr->label ? ' · ' . e($addr->label) : '' }}</div>
                        @if($addr->email)<div class="addr-meta"><i class="fas fa-envelope me-1"></i>{{ $addr->email }}</div>@endif
                        @if($addr->phone)<div class="addr-meta"><i class="fas fa-phone me-1"></i>{{ $addr->phone }}</div>@endif
                        <div class="addr-meta mt-1">{{ $addr->full_address }}</div>
                    </div>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick='editAddress({{ json_encode($addr->only(["id","label","contact_name","email","phone","address_line1","address_line2","city","state","postal_code","country"])) }})' title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('user.address-book.destroy', $addr->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this address?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="addr-empty">
                <i class="fas fa-address-book fa-3x text-muted mb-3 d-block"></i>
                <p class="mb-2">No addresses yet</p>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addressModal" onclick="openAddressForm()">
                    <i class="fas fa-plus me-1"></i>Add address
                </button>
            </div>
            @endforelse
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
            <form id="addressForm" method="POST" action="{{ route('user.address-book.store') }}">
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
@endsection

@push('scripts')
<script>
function openAddressForm() {
    document.getElementById('addressModalTitle').textContent = 'Add address';
    document.getElementById('addressForm').action = '{{ route('user.address-book.store') }}';
    document.getElementById('addressFormMethod').innerHTML = '';
    document.querySelectorAll('#addressForm input[name]').forEach(function(inp) {
        if (inp.name !== '_token') inp.value = '';
    });
}
function editAddress(addr) {
    document.getElementById('addressModalTitle').textContent = 'Edit address';
    document.getElementById('addressForm').action = '{{ route("user.address-book.update", ["id" => "__ID__"]) }}'.replace('__ID__', addr.id);
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
