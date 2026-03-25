@extends('layouts.admin')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-user me-2 text-primary"></i>User Details
            </h2>
            <p class="text-muted mb-0">View user information and activity</p>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Users
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white; font-size: 2.5rem; font-weight: 700;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h4 class="mb-2" style="font-weight: 700;">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    <span class="badge {{ $user->hasRole('admin') ? 'bg-danger' : 'bg-primary' }} mb-3" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                        {{ $user->roles->pluck('name')->first() ?: 'user' }}
                    </span>
                    <div class="mt-3">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary w-100">
                            <i class="fas fa-edit me-2"></i>Edit User
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600;">Account Information</h6>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Member Since</small>
                        <strong>{{ $user->created_at->format('F d, Y') }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Last Updated</small>
                        <strong>{{ $user->updated_at->format('F d, Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            {{-- Stats --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 me-2" style="background: rgba(99, 102, 241, 0.12);">
                                    <i class="fas fa-shopping-cart text-primary" style="font-size: 1.1rem;"></i>
                                </div>
                                <div>
                                    <span class="d-block fw-bold" style="font-size: 1.25rem;">{{ $ordersCount }}</span>
                                    <small class="text-muted">Orders</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 me-2" style="background: rgba(16, 185, 129, 0.12);">
                                    <i class="fas fa-file-alt text-success" style="font-size: 1.1rem;"></i>
                                </div>
                                <div>
                                    <span class="d-block fw-bold" style="font-size: 1.25rem;">{{ $templatesCount }}</span>
                                    <small class="text-muted">Templates</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 me-2" style="background: rgba(245, 158, 11, 0.12);">
                                    <i class="fas fa-book-open text-warning" style="font-size: 1.1rem;"></i>
                                </div>
                                <div>
                                    <span class="d-block fw-bold" style="font-size: 1.25rem;">{{ $flipBooksCount }}</span>
                                    <small class="text-muted">Flip Books</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 me-2" style="background: rgba(5, 150, 105, 0.12);">
                                    <i class="fas fa-wallet text-success" style="font-size: 1.1rem;"></i>
                                </div>
                                <div>
                                    <span class="d-block fw-bold" style="font-size: 1.1rem;">{{ format_price($totalSpent ?? 0) }}</span>
                                    <small class="text-muted">Total Spent</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 me-2" style="background: rgba(59, 130, 246, 0.12);">
                                    <i class="fas fa-coins text-info" style="font-size: 1.1rem;"></i>
                                </div>
                                <div>
                                    <span class="d-block fw-bold" style="font-size: 1.1rem;">{{ format_price($balance ?? 0) }}</span>
                                    <small class="text-muted">Balance</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 me-2" style="background: rgba(139, 92, 246, 0.12);">
                                    <i class="fas fa-star text-secondary" style="font-size: 1.1rem;"></i>
                                </div>
                                <div>
                                    <span class="d-block fw-bold" style="font-size: 1.25rem;">{{ $reviewsCount }}</span>
                                    <small class="text-muted">Reviews</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Line charts --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600;"><i class="fas fa-chart-line me-2 text-primary"></i>Orders (last 30 days)</h6>
                    <div style="height: 220px;">
                        <canvas id="userOrdersChart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600;"><i class="fas fa-chart-line me-2 text-success"></i>Templates created (last 30 days)</h6>
                    <div style="height: 220px;">
                        <canvas id="userTemplatesChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600;">
                        <i class="fas fa-book me-2 text-primary"></i>Flip Books ({{ $user->flipBooks->count() }})
                    </h6>
                    @if($user->flipBooks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Title</th>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Created</th>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->flipBooks->take(10) as $flipbook)
                                <tr>
                                    <td style="font-size: 0.9rem;">{{ $flipbook->title }}</td>
                                    <td style="font-size: 0.85rem; color: #94a3b8;">{{ $flipbook->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-success">Active</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted mb-0">No flip books created yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var labels = @json($chartLabels ?? []);
    var ordersData = @json($chartOrders ?? []);
    var templatesData = @json($chartTemplates ?? []);

    var ordersCtx = document.getElementById('userOrdersChart');
    if (ordersCtx) {
        new Chart(ordersCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Orders',
                    data: ordersData,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.08)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                    pointHoverRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 10, font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1, font: { size: 10 } } }
                }
            }
        });
    }

    var templatesCtx = document.getElementById('userTemplatesChart');
    if (templatesCtx) {
        new Chart(templatesCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Templates',
                    data: templatesData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                    pointHoverRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 10, font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1, font: { size: 10 } } }
                }
            }
        });
    }
});
</script>
@endpush
@endsection





