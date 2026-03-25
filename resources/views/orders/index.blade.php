@extends('layouts.app')

@section('title', 'My Orders - ' . site_name())
@section('page-title', 'My Orders')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-shopping-cart me-2 text-primary"></i>My Orders
            </h2>
            <p class="text-muted mb-0">View your order history and invoices</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Order #</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Template</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Qty</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Total</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Payment</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Status</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">Date</th>
                            <th style="padding: 1rem; font-size: 0.85rem; font-weight: 600; color: #64748b; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td style="padding: 1rem; font-size: 0.9rem; font-weight: 600; color: #1e293b;">#{{ $order->id }}</td>
                            <td style="padding: 1rem; font-size: 0.9rem; color: #475569;">{{ $order->template_name }}</td>
                            <td style="padding: 1rem; font-size: 0.9rem; color: #64748b;">{{ $order->quantity }}</td>
                            <td style="padding: 1rem; font-size: 0.9rem; font-weight: 600; color: #059669;">{{ format_price($order->total_amount) }}</td>
                            <td style="padding: 1rem;">
                                <span class="badge bg-secondary" style="font-size: 0.75rem;">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</span>
                            </td>
                            <td style="padding: 1rem;">
                                @if($order->status === 'completed')
                                    <span class="badge bg-success" style="font-size: 0.75rem;">Completed</span>
                                @elseif($order->status === 'cancelled')
                                    <span class="badge bg-danger" style="font-size: 0.75rem;">Cancelled</span>
                                @else
                                    <span class="badge bg-warning text-dark" style="font-size: 0.75rem;">Pending</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.85rem; color: #94a3b8;">{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary me-1" title="View Order">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('orders.invoice', $order->id) }}" class="btn btn-sm btn-outline-secondary" title="Download Invoice" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div style="background: url('{{url('feature_actor/not_found.png')}}');background-position: center;background-repeat: no-repeat;height: 300px;background-size: contain;margin-bottom: 20px;"></div>
                                <p class="text-muted mb-2">No orders yet</p>
                                <a href="{{ route('design.templates.explore') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-compass me-1"></i>Explore Templates
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($orders->hasPages())
    <div class="mt-3">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
