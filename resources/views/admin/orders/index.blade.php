@extends('layouts.admin')

@section('title', 'Orders Management')
@section('page-title', 'Orders Management')

@section('content')
<div class="my-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-shopping-cart me-2 text-primary"></i>Orders Management
            </h2>
            <p class="text-muted mb-0">View all orders from quick use checkout</p>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by template name or customer..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Pay Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="delivery_status" class="form-select">
                        <option value="">All Delivery</option>
                        <option value="pending" {{ request('delivery_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="hold" {{ request('delivery_status') == 'hold' ? 'selected' : '' }}>Hold</option>
                        <option value="processing" {{ request('delivery_status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="sending" {{ request('delivery_status') == 'sending' ? 'selected' : '' }}>Sending</option>
                        <option value="complete" {{ request('delivery_status') == 'complete' ? 'selected' : '' }}>Complete</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment_method" class="form-select">
                        <option value="">All Payment</option>
                        <option value="stripe" {{ request('payment_method') == 'stripe' ? 'selected' : '' }}>Card</option>
                        <option value="platform_credit" {{ request('payment_method') == 'platform_credit' ? 'selected' : '' }}>Platform Credits</option>
                        <option value="payhere" {{ request('payment_method') == 'payhere' ? 'selected' : '' }}>PayHere</option>
                        <option value="paypal" {{ request('payment_method') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">ID</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Customer</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Template</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Qty</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Total</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Payment</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Payment Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Delivery</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Date</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">#{{ $order->id }}</td>
                            <td style="padding: 1rem;">
                                <div style="font-size: 0.9rem; font-weight: 500; color: #1e293b;">{{ optional($order->user)->name ?? 'N/A' }}</div>
                                <div style="font-size: 0.8rem; color: #94a3b8;">{{ optional($order->user)->email ?? '' }}</div>
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">{{ $order->template_name }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #64748b;">{{ $order->quantity }}</td>
                            <td style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #059669;">{{ format_price($order->total_amount) }}</td>
                            <td style="padding: 1rem;">
                                <span class="badge bg-secondary" style="font-size: 0.75rem;">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</span>
                            </td>
                            <td style="padding: 1rem;">
                                @if($order->status === 'completed')
                                    <span class="badge bg-success" style="font-size: 0.75rem;">Completed</span>
                                @elseif($order->status === 'cancelled')
                                    <span class="badge bg-danger" style="font-size: 0.75rem;">Cancelled</span>
                                @else
                                    <span class="badge bg-warning" style="font-size: 0.75rem;">Pending</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                @php $ds = $order->delivery_status ?? 'pending'; @endphp
                                @if($ds === 'complete')
                                    <span class="badge bg-success" style="font-size: 0.75rem;">Complete</span>
                                @elseif($ds === 'sending')
                                    <span class="badge bg-info" style="font-size: 0.75rem;">Sending</span>
                                @elseif($ds === 'processing')
                                    <span class="badge bg-primary" style="font-size: 0.75rem;">Processing</span>
                                @elseif($ds === 'hold')
                                    <span class="badge bg-secondary" style="font-size: 0.75rem;">Hold</span>
                                @else
                                    <span class="badge bg-warning text-dark" style="font-size: 0.75rem;">Pending</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" title="View Order">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
    <div class="mt-3">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
