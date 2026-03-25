@extends('layouts.admin')

@section('title', 'Order #' . $order->id)
@section('page-title', 'Order #' . $order->id)

@section('content')
<div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1" style="font-weight: 700; font-size: 1.5rem; color: #1e293b;">
                <i class="fas fa-receipt me-2 text-primary"></i>Order #{{ $order->id }}
            </h2>
            <p class="text-muted mb-0">Order placed {{ $order->created_at->format('M d, Y H:i') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.pdf', $order->id) }}" class="btn btn-primary" target="_blank">
                <i class="fas fa-file-pdf me-2"></i>Export PDF
            </a>
            <a href="{{ route('admin.orders') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600; color: #1e293b;">
                        <i class="fas fa-user me-2 text-primary"></i>Customer
                    </h6>
                    <div class="mb-2">
                        <small class="text-muted d-block mb-0">Name</small>
                        <strong>{{ optional($order->user)->name ?? 'N/A' }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block mb-0">Email</small>
                        <strong>{{ optional($order->user)->email ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600; color: #1e293b;">
                        <i class="fas fa-credit-card me-2 text-primary"></i>Payment
                    </h6>
                    <div class="mb-2">
                        <small class="text-muted d-block mb-0">Method</small>
                        <strong>{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block mb-0">Payment Status</small>
                        @if($order->status === 'completed')
                            <span class="badge bg-success">Completed</span>
                        @elseif($order->status === 'cancelled')
                            <span class="badge bg-danger">Cancelled</span>
                        @else
                            <span class="badge bg-warning">Pending</span>
                        @endif
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block mb-0">Delivery Status</small>
                        <form action="{{ route('admin.orders.updateDeliveryStatus', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <select name="delivery_status" class="form-select form-select-sm" style="width: auto; display: inline-block; font-size: 0.8rem;" onchange="this.form.submit()">
                                @foreach(\App\Models\Order::DELIVERY_STATUSES as $ds)
                                <option value="{{ $ds }}" {{ ($order->delivery_status ?? 'pending') === $ds ? 'selected' : '' }}>{{ ucfirst($ds) }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="mt-3 pt-3" style="border-top: 1px solid #e2e8f0;">
                        <small class="text-muted d-block mb-0">Total</small>
                        <strong style="font-size: 1.25rem; color: #059669;">{{ format_price($order->total_amount) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600; color: #1e293b;">
                        <i class="fas fa-shopping-bag me-2 text-primary"></i>Order Summary
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Template</th>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Quantity</th>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Sheet Type</th>
                                    <th style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Material</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-size: 0.9rem;">{{ $order->template_name }}</td>
                                    <td style="font-size: 0.9rem;">{{ $order->quantity }} item(s)</td>
                                    <td style="font-size: 0.9rem;">{{ $order->checkout_data['sheet_type_name'] ?? 'Standard' }}</td>
                                    <td style="font-size: 0.9rem;">{{ $order->checkout_data['material_type_name'] ?? 'Paper' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if(isset($orderProduct) && $orderProduct)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600; color: #1e293b;">
                        <i class="fas fa-box me-2 text-primary"></i>Attached Product
                    </h6>
                    <div class="d-flex gap-3 flex-wrap">
                        @if($orderProduct->image_url)
                            <img src="{{ $orderProduct->image_url }}" alt="{{ $orderProduct->name }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                        @else
                            <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8;"><i class="fas fa-box-open fa-2x"></i></div>
                        @endif
                        <div class="flex-grow-1" style="min-width: 200px;">
                            <strong style="font-size: 1rem;">{{ $orderProduct->name }}</strong>
                            @if($orderProduct->sku)<div class="text-muted small"><code>{{ $orderProduct->sku }}</code></div>@endif
                            @if($orderProduct->description)<p class="mb-0 mt-1 small text-muted" style="line-height: 1.4;">{{ Str::limit($orderProduct->description, 200) }}</p>@endif
                            @if($orderProduct->faqs && is_array($orderProduct->faqs) && count($orderProduct->faqs) > 0)
                                <div class="mt-2 pt-2" style="border-top: 1px solid #e2e8f0;">
                                    <span class="small fw-bold text-muted">FAQ</span>
                                    @foreach(array_slice($orderProduct->faqs, 0, 3) as $faq)
                                        @if(!empty($faq['question']) || !empty($faq['answer']))
                                            <div class="small mt-1"><strong>{{ $faq['question'] ?? '—' }}</strong><br><span class="text-muted">{{ $faq['answer'] ?? '' }}</span></div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @php
                $checkoutData = $order->checkout_data ?? [];
                $items = $checkoutData['items'] ?? [];
                $designs = $checkoutData['designs'] ?? [];
                $displayItems = !empty($items) ? $items : $designs;
            @endphp
            @if(!empty($displayItems))
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600; color: #1e293b;">
                        <i class="fas fa-list me-2 text-primary"></i>Ordered Items ({{ count($displayItems) }} item(s))
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th style="font-size: 0.8rem; font-weight: 600; color: #64748b;">#</th>
                                    <th style="font-size: 0.8rem; font-weight: 600; color: #64748b;">Variables</th>
                                    @if(!empty($items))
                                    <th style="font-size: 0.8rem; font-weight: 600; color: #64748b;">Address</th>
                                    @endif
                                    <th style="font-size: 0.8rem; font-weight: 600; color: #64748b; width: 90px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($displayItems as $idx => $item)
                                <tr>
                                    <td style="font-size: 0.85rem; vertical-align: top; width: 40px;">{{ $idx + 1 }}</td>
                                    <td style="font-size: 0.85rem;">
                                        @php $vars = $item['variables'] ?? []; @endphp
                                        @if(!empty($vars))
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($vars as $varName => $varValue)
                                                    @if($varValue)
                                                    <span class="badge bg-light text-dark" style="font-size: 0.75rem; font-weight: 500;">
                                                        {{ $varName }}: {{ $varValue }}
                                                    </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted" style="font-size: 0.8rem;">—</span>
                                        @endif
                                    </td>
                                    @if(!empty($items))
                                    <td style="font-size: 0.85rem;">
                                        @php $addr = $item['address'] ?? []; @endphp
                                        @if(!empty($addr['name']) || !empty($addr['line1']))
                                            <div style="font-size: 0.8rem; line-height: 1.4;">
                                                @if(!empty($addr['name']))<strong>{{ $addr['name'] }}</strong><br>@endif
                                                @if(!empty($addr['line1'])){{ $addr['line1'] }}<br>@endif
                                                @if(!empty($addr['line2'])){{ $addr['line2'] }}<br>@endif
                                                @if(!empty($addr['city']) || !empty($addr['state']) || !empty($addr['zip']))
                                                    {{ trim(implode(', ', array_filter([$addr['city'] ?? '', $addr['state'] ?? '', $addr['zip'] ?? '']))) }}<br>
                                                @endif
                                                @if(!empty($addr['country'])){{ $addr['country'] }}@endif
                                            </div>
                                        @else
                                            <span class="text-muted" style="font-size: 0.8rem;">—</span>
                                        @endif
                                    </td>
                                    @endif
                                    <td style="font-size: 0.85rem; vertical-align: middle;">
                                        @if($order->template_id && $order->template && $order->template->pages)
                                        <div class="d-flex gap-1 flex-wrap">
                                            <a href="{{ route('admin.orders.preview', $order->id) }}?design={{ $idx }}" class="btn btn-sm btn-outline-primary" target="_blank" title="Preview" style="padding: 0.2rem 0.4rem; font-size: 0.7rem;">
                                                <i class="fas fa-eye me-0"></i>
                                            </a>
                                            <a href="{{ route('admin.orders.pdf.item', [$order->id, $idx]) }}" class="btn btn-sm btn-outline-danger" target="_blank" title="Export PDF" style="padding: 0.2rem 0.4rem; font-size: 0.7rem;">
                                                <i class="fas fa-file-pdf me-0"></i>
                                            </a>
                                        </div>
                                        @else
                                        <span class="text-muted" style="font-size: 0.75rem;">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @if(!empty($checkoutData))
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="mb-3" style="font-weight: 600; color: #1e293b;">
                        <i class="fas fa-calculator me-2 text-primary"></i>Cost Breakdown
                    </h6>
                    <div style="font-size: 0.9rem;">
                        @if(!empty($checkoutData['product_id']) && isset($orderProduct) && $orderProduct && isset($checkoutData['product_price']))
                            @php
                                $productPrice = (float) ($checkoutData['product_price'] ?? 0);
                                $qty = (int) ($order->quantity ?? 1);
                                $productTotal = $productPrice * $qty;
                                $letterCostNum = (float) preg_replace('/[^0-9.]/', '', $checkoutData['template_cost'] ?? 0);
                                $templateOnce = $letterCostNum - $productTotal;
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Template (once):</span>
                                <span>{{ format_price($templateOnce) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Product (× {{ $qty }}):</span>
                                <span>{{ format_price($productTotal) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Letter total:</span>
                                <span>{{ $checkoutData['template_cost'] ?? format_price($letterCostNum) }}</span>
                            </div>
                        @else
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Template Cost:</span>
                                <span>{{ $checkoutData['template_cost'] ?? format_price(0) }}</span>
                            </div>
                        @endif
                        @if(isset($checkoutData['envelope_cost']))
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Envelope:</span>
                                <span>{{ $checkoutData['envelope_cost'] }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Sheet Cost:</span>
                            <span>{{ $checkoutData['sheet_cost'] ?? format_price(0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Material Cost:</span>
                            <span>{{ $checkoutData['material_cost'] ?? format_price(0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 mt-2" style="border-top: 1px solid #e2e8f0; font-weight: 600;">
                            <span>Total:</span>
                            <span style="color: #059669;">{{ $checkoutData['total_cost'] ?? format_price($order->total_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
