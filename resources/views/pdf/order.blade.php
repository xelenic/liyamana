<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Order #{{ $order->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .container { max-width: 100%; padding: 20px; }
        h1 { font-size: 18px; margin-bottom: 4px; color: #1e293b; }
        .subtitle { font-size: 10px; color: #64748b; margin-bottom: 20px; }
        .section { margin-bottom: 18px; }
        .section-title { font-size: 12px; font-weight: bold; color: #1e293b; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }
        .row { margin-bottom: 6px; }
        .label { color: #64748b; }
        .value { font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { padding: 8px 10px; text-align: left; border: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; font-size: 10px; color: #64748b; }
        td { font-size: 10px; }
        .badge { display: inline-block; padding: 2px 8px; margin: 2px 2px 2px 0; background: #f1f5f9; border-radius: 4px; font-size: 9px; }
        .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Order #{{ $order->id }}</h1>
        <div class="subtitle">Order placed {{ $order->created_at->format('M d, Y H:i') }}</div>

        <div class="section">
            <div class="section-title">Customer</div>
            <div class="row"><span class="label">Name: </span><span class="value">{{ optional($order->user)->name ?? 'N/A' }}</span></div>
            <div class="row"><span class="label">Email: </span><span class="value">{{ optional($order->user)->email ?? 'N/A' }}</span></div>
        </div>

        <div class="section">
            <div class="section-title">Payment</div>
            <div class="row"><span class="label">Method: </span><span class="value">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</span></div>
            <div class="row"><span class="label">Status: </span><span class="value">{{ ucfirst($order->status ?? 'N/A') }}</span></div>
            <div class="row"><span class="label">Total: </span><span class="value">{{ format_price($order->total_amount) }}</span></div>
        </div>

        <div class="section">
            <div class="section-title">Order Summary</div>
            <table>
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>Quantity</th>
                        <th>Sheet Type</th>
                        <th>Material</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $order->template_name }}</td>
                        <td>{{ $order->quantity }} item(s)</td>
                        <td>{{ $order->checkout_data['sheet_type_name'] ?? 'Standard' }}</td>
                        <td>{{ $order->checkout_data['material_type_name'] ?? 'Paper' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if(isset($orderProduct) && $orderProduct)
        <div class="section">
            <div class="section-title">Attached Product</div>
            <div class="row"><span class="value">{{ $orderProduct->name }}</span></div>
            @if($orderProduct->sku)<div class="row"><span class="label">SKU: </span><span class="value">{{ $orderProduct->sku }}</span></div>@endif
            @if($orderProduct->description)<div class="row" style="margin-top: 4px;"><span class="label">Description: </span><span>{{ Str::limit($orderProduct->description, 300) }}</span></div>@endif
            @if($orderProduct->faqs && is_array($orderProduct->faqs) && count($orderProduct->faqs) > 0)
                <div style="margin-top: 8px; font-size: 10px;">
                    <div class="label" style="margin-bottom: 4px;">FAQ</div>
                    @foreach(array_slice($orderProduct->faqs, 0, 3) as $faq)
                        @if(!empty($faq['question']) || !empty($faq['answer']))
                            <div style="margin-bottom: 4px;"><strong>{{ $faq['question'] ?? '—' }}</strong><br>{{ $faq['answer'] ?? '' }}</div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
        @endif

        @php
            $checkoutData = $order->checkout_data ?? [];
            $items = $checkoutData['items'] ?? [];
            $designs = $checkoutData['designs'] ?? [];
            $displayItems = !empty($items) ? $items : $designs;
        @endphp
        @if(!empty($displayItems))
        <div class="section">
            <div class="section-title">Ordered Items ({{ count($displayItems) }} item(s))</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th>Variables</th>
                        @if(!empty($items))
                        <th>Address</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($displayItems as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            @php $vars = $item['variables'] ?? []; @endphp
                            @if(!empty($vars))
                                @foreach($vars as $varName => $varValue)
                                    @if($varValue)
                                    <span class="badge">{{ $varName }}: {{ $varValue }}</span>
                                    @endif
                                @endforeach
                            @else
                                —
                            @endif
                        </td>
                        @if(!empty($items))
                        <td>
                            @php $addr = $item['address'] ?? []; @endphp
                            @if(!empty($addr['name']) || !empty($addr['line1']))
                                @if(!empty($addr['name']))<strong>{{ $addr['name'] }}</strong><br>@endif
                                @if(!empty($addr['line1'])){{ $addr['line1'] }}@if(!empty($addr['line2'])), {{ $addr['line2'] }}@endif<br>@endif
                                @if(!empty($addr['city']) || !empty($addr['state']) || !empty($addr['zip']))
                                    {{ trim(implode(', ', array_filter([$addr['city'] ?? '', $addr['state'] ?? '', $addr['zip'] ?? '']))) }}<br>
                                @endif
                                @if(!empty($addr['country'])){{ $addr['country'] }}@endif
                            @else
                                —
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(!empty($checkoutData))
        <div class="section">
            <div class="section-title">Cost Breakdown</div>
            <table>
                @if(!empty($checkoutData['product_id']) && isset($orderProduct) && $orderProduct && isset($checkoutData['product_price']))
                    @php
                        $productPrice = (float) ($checkoutData['product_price'] ?? 0);
                        $qty = (int) ($order->quantity ?? 1);
                        $productTotal = $productPrice * $qty;
                        $letterCostNum = (float) preg_replace('/[^0-9.]/', '', $checkoutData['template_cost'] ?? 0);
                        $templateOnce = $letterCostNum - $productTotal;
                    @endphp
                    <tr><td>Template (once):</td><td style="text-align: right;">{{ format_price($templateOnce) }}</td></tr>
                    <tr><td>Product (× {{ $qty }}):</td><td style="text-align: right;">{{ format_price($productTotal) }}</td></tr>
                    <tr><td>Letter total:</td><td style="text-align: right;">{{ $checkoutData['template_cost'] ?? format_price($letterCostNum) }}</td></tr>
                @else
                    <tr><td>Template Cost:</td><td style="text-align: right;">{{ $checkoutData['template_cost'] ?? format_price(0) }}</td></tr>
                @endif
                @if(isset($checkoutData['envelope_cost']))
                    <tr><td>Envelope:</td><td style="text-align: right;">{{ $checkoutData['envelope_cost'] }}</td></tr>
                @endif
                <tr><td>Sheet Cost:</td><td style="text-align: right;">{{ $checkoutData['sheet_cost'] ?? format_price(0) }}</td></tr>
                <tr><td>Material Cost:</td><td style="text-align: right;">{{ $checkoutData['material_cost'] ?? format_price(0) }}</td></tr>
                <tr style="font-weight: bold; font-size: 12px; color: #059669;"><td>Total:</td><td style="text-align: right;">{{ $checkoutData['total_cost'] ?? format_price($order->total_amount) }}</td></tr>
            </table>
        </div>
        @endif

        <div class="footer">
            {{ site_name() }} — Order #{{ $order->id }} — Exported {{ now()->format('M d, Y H:i') }}
        </div>
    </div>
</body>
</html>
