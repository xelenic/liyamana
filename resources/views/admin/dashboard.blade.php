@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="my-4">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-2" style="opacity: 0.9; font-size: 0.85rem; font-weight: 600;">Total Users</h6>
                            <h3 class="mb-0" style="font-size: 2rem; font-weight: 800;">{{ number_format($stats['total_users']) }}</h3>
                        </div>
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-users" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); color: white;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-2" style="opacity: 0.9; font-size: 0.85rem; font-weight: 600;">Total Flip Books</h6>
                            <h3 class="mb-0" style="font-size: 2rem; font-weight: 800;">{{ number_format($stats['total_flipbooks']) }}</h3>
                        </div>
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-book" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); color: white;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-2" style="opacity: 0.9; font-size: 0.85rem; font-weight: 600;">Active Users (30d)</h6>
                            <h3 class="mb-0" style="font-size: 2rem; font-weight: 800;">{{ number_format($stats['active_users']) }}</h3>
                        </div>
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user-check" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); color: white;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-2" style="opacity: 0.9; font-size: 0.85rem; font-weight: 600;">Recent Flip Books (7d)</h6>
                            <h3 class="mb-0" style="font-size: 2rem; font-weight: 800;">{{ number_format($stats['recent_flipbooks']) }}</h3>
                        </div>
                        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-line" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3" style="font-weight: 700; font-size: 1.1rem;">
                        <i class="fas fa-shopping-cart me-2 text-primary"></i>Orders (Last 30 Days)
                    </h5>
                    <div style="position: relative; height: 250px;">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3" style="font-weight: 700; font-size: 1.1rem;">
                        <i class="fas fa-th-large me-2 text-primary"></i>Templates Added (Last 30 Days)
                    </h5>
                    <div style="position: relative; height: 250px;">
                        <canvas id="templatesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Users -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0" style="font-weight: 700; font-size: 1.1rem;">
                            <i class="fas fa-users me-2 text-primary"></i>Recent Users
                        </h5>
                        <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Name</th>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Email</th>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Role</th>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_users as $user)
                                <tr>
                                    <td style="font-size: 0.9rem;">{{ $user->name }}</td>
                                    <td style="font-size: 0.9rem; color: #64748b;">{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $user->roles->pluck('name')->first() ?: 'user' }}
                                        </span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #94a3b8;">{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No users found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Flip Books -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0" style="font-weight: 700; font-size: 1.1rem;">
                            <i class="fas fa-book me-2 text-primary"></i>Recent Flip Books
                        </h5>
                        <a href="{{ route('admin.flipbooks') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Title</th>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">User</th>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_flipbooks as $flipbook)
                                <tr>
                                    <td style="font-size: 0.9rem;">{{ strlen($flipbook->title) > 30 ? substr($flipbook->title, 0, 30) . '...' : $flipbook->title }}</td>
                                    <td style="font-size: 0.9rem; color: #64748b;">{{ $flipbook->user->name }}</td>
                                    <td style="font-size: 0.85rem; color: #94a3b8;">{{ $flipbook->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No flip books found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chartData ?? ['labels' => [], 'orders' => [], 'templates' => []]);

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    };

    if (document.getElementById('ordersChart')) {
        new Chart(document.getElementById('ordersChart'), {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Orders',
                    data: chartData.orders,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: chartOptions
        });
    }

    if (document.getElementById('templatesChart')) {
        new Chart(document.getElementById('templatesChart'), {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Templates',
                    data: chartData.templates,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: chartOptions
        });
    }
});
</script>
@endpush
@endsection

