<?php

namespace Modules\PayHereModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Modules\PayHereModule\PayHerePaymentGateway;

class PayHereController extends Controller
{
    protected PayHerePaymentGateway $gateway;

    public function __construct(PayHerePaymentGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Initiate PayHere payment - create pending order and redirect to PayHere.
     */
    public function initiate(Request $request)
    {
        $checkout = session('checkout');
        if (! $checkout) {
            return redirect()->route('design.templates.page')->with('error', 'Checkout session expired.');
        }

        if (! $this->gateway->isEnabled()) {
            return redirect()->route('design.checkout.paymentOptions')->with('error', 'PayHere is not configured.');
        }

        $checkoutData = $checkout['checkout_data'] ?? [];
        $totalCost = $checkoutData['total_cost'] ?? '0';
        $totalAmount = (float) preg_replace('/[^0-9.]/', '', $totalCost);
        $quantity = (int) ($checkoutData['quantity'] ?? 1);

        if ($totalAmount < 1) {
            return redirect()->back()->with('error', 'Invalid amount.');
        }

        $orderId = 'ORD-' . uniqid() . '-' . time();

        $checkoutData['payhere_order_id'] = $orderId;

        $order = Order::create([
            'user_id' => auth()->id(),
            'template_id' => $checkout['template_id'],
            'template_name' => $checkout['template_name'],
            'quantity' => $quantity,
            'total_amount' => $totalAmount,
            'payment_method' => 'payhere',
            'status' => 'pending',
            'checkout_data' => $checkoutData,
        ]);

        $amount = number_format($totalAmount, 2, '.', '');
        $currency = 'LKR';
        $hash = $this->gateway->generateHash($orderId, $amount, $currency);

        $user = auth()->user();
        $nameParts = explode(' ', $user->name ?? 'Customer', 2);

        $payment = [
            'sandbox' => $this->gateway->isSandbox(),
            'merchant_id' => $this->gateway->getMerchantId(),
            'return_url' => route('payhere.return'),
            'cancel_url' => route('payhere.cancel'),
            'notify_url' => route('payhere.notify'),
            'order_id' => $orderId,
            'items' => $checkout['template_name'] ?? 'Order',
            'amount' => $amount,
            'currency' => $currency,
            'hash' => $hash,
            'first_name' => $nameParts[0] ?? 'Customer',
            'last_name' => $nameParts[1] ?? '',
            'email' => $user->email ?? 'customer@example.com',
            'phone' => $user->phone ?? '0771234567',
            'address' => $user->address ?? 'No.1, Sample Street',
            'city' => 'Colombo',
            'country' => 'Sri Lanka',
        ];

        session(['payhere_pending_order_id' => $order->id]);

        return view('payhere-module::redirect', [
            'payment' => $payment,
        ]);
    }

    /**
     * PayHere server-to-server notification (callback).
     * PayHere sends: order_id, payhere_amount (or amount), payment_status or payhere_payment_status (2=success), payment_id or payhere_payment_id, md5sig, etc.
     */
    public function notify(Request $request)
    {
        $params = $request->all();

        if (! $this->gateway->verifyNotifyHash($params)) {
            \Log::warning('PayHere notify: Invalid hash', ['params' => array_diff_key($params, ['md5sig' => ''])]);
            return response('Invalid hash', 400);
        }

        $orderId = $params['order_id'] ?? '';
        $status = $params['payhere_payment_status'] ?? $params['payment_status'] ?? $params['status_code'] ?? '';
        $paymentId = $params['payhere_payment_id'] ?? $params['payment_id'] ?? '';

        $order = Order::where('checkout_data->payhere_order_id', $orderId)->first();

        if (! $order) {
            \Log::warning('PayHere notify: Order not found', ['order_id' => $orderId, 'params_keys' => array_keys($params)]);
            return response('Order not found', 404);
        }

        $isSuccess = ($status === '2' || $status === 2);
        if ($isSuccess) {
            $data = $order->checkout_data ?? [];
            $data['payhere_payment_id'] = $paymentId;
            $order->update(['status' => 'completed', 'checkout_data' => $data]);
            \Log::info('PayHere notify: Order marked completed', ['order_id' => $order->id, 'payhere_order_id' => $orderId]);
        }

        return response('OK', 200);
    }

    /**
     * User return from PayHere (success).
     */
    public function return(Request $request)
    {
        $orderId = $request->get('order_id');
        $sessionOrderId = session('payhere_pending_order_id');

        $order = Order::where('checkout_data->payhere_order_id', $orderId)->first();

        session()->forget('checkout');
        session()->forget('payhere_pending_order_id');

        if ($order && $order->status === 'completed') {
            return redirect()->route('design.templates.page')
                ->with('success', 'Payment successful! Thank you for your purchase.');
        }

        return redirect()->route('design.templates.page')
            ->with('info', 'Your payment is being processed. You will receive confirmation shortly.');
    }

    /**
     * User cancelled payment on PayHere.
     */
    public function cancel(Request $request)
    {
        $orderId = $request->get('order_id');

        $order = Order::where('checkout_data->payhere_order_id', $orderId)->first();
        if ($order && $order->status === 'pending') {
            $order->update(['status' => 'cancelled']);
        }

        session()->forget('payhere_pending_order_id');

        return redirect()->route('design.checkout.paymentOptions')
            ->with('error', 'Payment was cancelled.');
    }
}
