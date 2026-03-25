<?php

namespace Modules\PayHereModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
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

        $orderId = 'ORD-'.uniqid().'-'.time();

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
     * PayHere sends: order_id, payhere_amount, payhere_currency, status_code (2=success), payment_id, md5sig, etc.
     */
    public function notify(Request $request)
    {
        $params = $request->all();

        if (! $this->gateway->verifyNotifyHash($params)) {
            \Log::warning('PayHere notify: Invalid hash', ['param_keys' => array_keys($params)]);

            return response('Invalid hash', 400);
        }

        $orderId = trim((string) ($params['order_id'] ?? ''));
        $status = $params['status_code'] ?? $params['payhere_payment_status'] ?? $params['payment_status'] ?? '';
        $paymentId = $params['payhere_payment_id'] ?? $params['payment_id'] ?? '';

        $order = Order::where('checkout_data->payhere_order_id', $orderId)->first();

        if (! $order) {
            \Log::warning('PayHere notify: Order not found', ['order_id' => $orderId, 'param_keys' => array_keys($params)]);

            return response('Order not found', 404);
        }

        $isSuccess = ($status == 2 || $status === '2');
        if ($isSuccess) {
            $data = $order->checkout_data ?? [];
            $data['payhere_payment_id'] = $paymentId;
            $order->update(['status' => 'completed', 'checkout_data' => $data]);
            \Log::info('PayHere notify: Order marked completed', ['order_id' => $order->id, 'payhere_order_id' => $orderId]);

            if (! empty($data['is_credit_topup'])) {
                $order->user->addCredits((float) $order->total_amount, 'topup', 'payhere', $paymentId, 'Credit top-up via PayHere');
                \Log::info('PayHere notify: Credits added for top-up', ['order_id' => $order->id, 'user_id' => $order->user_id]);
            }
        }

        return response('OK', 200);
    }

    /**
     * User return from PayHere (success/callback).
     * PayHere may pass order_id (and sometimes status) on the redirect. If order is still pending and we have
     * success params, mark completed as fallback when notify_url is delayed or unreachable.
     */
    public function return(Request $request)
    {
        $orderId = trim((string) $request->get('order_id', ''));
        $status = $request->get('status_code') ?? $request->get('payhere_payment_status') ?? $request->get('payment_status');

        $order = $orderId ? Order::where('checkout_data->payhere_order_id', $orderId)->first() : null;

        if ($order && $order->status === 'pending' && ($status == 2 || $status === '2')) {
            $data = $order->checkout_data ?? [];
            $data['payhere_payment_id'] = $data['payhere_payment_id'] ?? $request->get('payment_id');
            $order->update(['status' => 'completed', 'checkout_data' => $data]);
            \Log::info('PayHere return: Order marked completed (fallback)', ['order_id' => $order->id, 'payhere_order_id' => $orderId]);
        }

        session()->forget('checkout');
        session()->forget('payhere_pending_order_id');

        if ($order && $order->fresh()->status === 'completed') {
            $redirect = redirect()->route('design.templates.page')
                ->with('success', 'Payment successful! Thank you for your purchase.');
            if ($order->template_id && $order->template_name) {
                $redirect->with('order_review_prompt', [
                    'order_id' => $order->id,
                    'template_id' => $order->template_id,
                    'template_name' => $order->template_name,
                ]);
            }

            return $redirect;
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
