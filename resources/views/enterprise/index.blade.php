@extends('layouts.app')
@section('title', 'Mail box - Enterprise - ' . site_name())
@section('page-title', 'Enterprise')

@include('enterprise.partials.panel-styles')

@section('content')
<div class="enterprise-panel">
    <div class="enterprise-mailbox">
        @include('enterprise.partials.sidebar', ['activeSection' => $filter ?? 'pending'])
        <div class="mailbox-main">
            <div class="mailbox-toolbar">
                <span class="text-muted" style="font-size: 0.9rem;">{{ ($filter ?? '') === 'completed' ? 'Completed mail' : 'Pending mail' }}</span>
                <div class="d-flex flex-wrap gap-2 ms-auto">
                    <a href="{{ route('enterprise') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-th-large me-1"></i>Dashboard</a>
                    <a href="{{ route('design.templates.explore') }}" class="btn btn-sm btn-primary"><i class="fas fa-compass me-1"></i>Explore Templates</a>
                </div>
            </div>
            <div class="mailbox-list">
                @forelse($orders as $order)
                <div class="order-item">
                    <span class="order-num">#{{ $order->id }}</span>
                    <span class="order-template" title="{{ $order->template_name }}">{{ $order->template_name }}</span>
                    <span class="order-total">{{ format_price($order->total_amount) }}</span>
                    @if($order->status === 'completed')
                        <span class="badge bg-success order-status">Completed</span>
                    @elseif($order->status === 'cancelled')
                        <span class="badge bg-danger order-status">Cancelled</span>
                    @else
                        <span class="badge bg-warning text-dark order-status">Pending</span>
                    @endif
                    <span class="order-date">{{ $order->created_at->format('M d, Y') }}</span>
                    <span class="order-actions">
                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" title="View order"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('orders.invoice', $order->id) }}" class="btn btn-sm btn-outline-secondary" title="Invoice" target="_blank" rel="noopener"><i class="fas fa-file-pdf"></i></a>
                    </span>
                </div>
                @empty
                <div class="mailbox-empty">
                    <div class="text-center">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3 d-block"></i>
                        <p class="mb-2">No orders yet</p>
                        <a href="{{ route('design.templates.explore') }}" class="btn btn-primary btn-sm"><i class="fas fa-compass me-1"></i>Explore Templates</a>
                    </div>
                </div>
                @endforelse
            </div>
            @if($orders->hasPages())
            <div class="mail-reader d-flex justify-content-center py-3 border-top">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
