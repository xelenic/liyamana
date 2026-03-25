<?php

namespace App\Http\Controllers;

use App\Models\CreditTransaction;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Payment\PaymentGatewayRepository;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    /**
     * Show credits page: balance, topup form, transaction history
     */
    public function index()
    {
        $user = auth()->user();
        $balance = (float) ($user->balance ?? 0);
        $transactions = CreditTransaction::where('user_id', $user->id)->latest()->paginate(15);

        $creditTopupEnabled = filter_var(Setting::get('credit_topup_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        $minAmount = (float) (Setting::get('credit_topup_min_amount', '5') ?: 5);
        $maxAmount = (float) (Setting::get('credit_topup_max_amount', '10000') ?: 10000);

        $repository = app(PaymentGatewayRepository::class);
        $stripeEnabled = false;
        $stripePublishableKey = '';
        $paymentMethodsForTopup = [];

        $stripeTopupEnabled = filter_var(Setting::get('credit_topup_stripe_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        $gateway = $repository->get('stripe');
        if ($stripeTopupEnabled && $gateway && $gateway->isEnabled()) {
            $stripeEnabled = true;
            $stripePublishableKey = $gateway->getPublishableKey();
            $paymentMethodsForTopup[] = ['id' => 'stripe', 'name' => 'Credit / Debit Card', 'icon' => 'fa-credit-card', 'description' => 'Pay securely with Visa, Mastercard, or Amex'];
        }

        $payhereTopupEnabled = filter_var(Setting::get('credit_topup_payhere_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $payhereGateway = $repository->get('payhere');
        if ($payhereTopupEnabled && $payhereGateway && $payhereGateway->isEnabled()) {
            $paymentMethodsForTopup[] = ['id' => 'payhere', 'name' => 'PayHere', 'icon' => 'fa-credit-card', 'description' => 'Pay with card or mobile wallet via PayHere'];
        }

        return view('credits.index', compact('balance', 'transactions', 'stripeEnabled', 'stripePublishableKey', 'creditTopupEnabled', 'minAmount', 'maxAmount', 'paymentMethodsForTopup'));
    }

    /**
     * Create Stripe PaymentIntent for credit topup (AJAX)
     */
    public function createTopupIntent(Request $request)
    {
        if (! filter_var(Setting::get('credit_topup_enabled', '1'), FILTER_VALIDATE_BOOLEAN)) {
            return response()->json(['success' => false, 'error' => 'Credit top-up is currently disabled.'], 403);
        }

        $minAmount = (float) (Setting::get('credit_topup_min_amount', '5') ?: 5);
        $maxAmount = (float) (Setting::get('credit_topup_max_amount', '10000') ?: 10000);

        $request->validate([
            'amount' => 'required|numeric|min:'.$minAmount.'|max:'.$maxAmount,
        ]);

        $amount = (float) $request->amount;
        $amountInCents = (int) round($amount * 100);

        if ($amount < $minAmount) {
            return response()->json(['success' => false, 'error' => 'Minimum top-up amount is '.format_price($minAmount)], 400);
        }
        if ($amount > $maxAmount) {
            return response()->json(['success' => false, 'error' => 'Maximum top-up amount is '.format_price($maxAmount)], 400);
        }

        $currency = strtolower(Setting::get('default_currency', 'usd'));
        $repository = app(PaymentGatewayRepository::class);
        $gateway = $repository->get('stripe');

        $stripeTopupEnabled = filter_var(Setting::get('credit_topup_stripe_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        if (! $stripeTopupEnabled || ! $gateway || ! $gateway->isEnabled()) {
            return response()->json(['success' => false, 'error' => 'Card payment is not available. Please try again later.'], 400);
        }

        $result = $gateway->createPaymentIntent($amountInCents, $currency, [
            'user_id' => (string) auth()->id(),
            'type' => 'credit_topup',
            'amount' => (string) $amount,
        ]);

        if (! $result['success']) {
            return response()->json(['success' => false, 'error' => $result['error'] ?? 'Failed to create payment'], 400);
        }

        return response()->json([
            'success' => true,
            'clientSecret' => $result['client_secret'],
            'paymentIntentId' => $result['payment_intent_id'],
            'amount' => $amount,
        ]);
    }

    /**
     * Process credit topup after Stripe payment
     */
    public function processTopup(Request $request)
    {
        if (! filter_var(Setting::get('credit_topup_enabled', '1'), FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->route('credits.index')->with('error', 'Credit top-up is currently disabled.');
        }

        $minAmount = (float) (Setting::get('credit_topup_min_amount', '5') ?: 5);
        $maxAmount = (float) (Setting::get('credit_topup_max_amount', '10000') ?: 10000);

        $request->validate([
            'payment_method' => 'required|in:stripe',
            'payment_intent_id' => 'required|string',
            'amount' => 'required|numeric|min:'.$minAmount.'|max:'.$maxAmount,
        ]);

        $gateway = app(PaymentGatewayRepository::class)->get('stripe');
        if (! $gateway || ! $gateway->isEnabled()) {
            return redirect()->route('credits.index')->with('error', 'Payment is not available.');
        }

        $result = $gateway->confirmPayment($request->payment_intent_id);
        if (! $result['success'] || ($result['status'] ?? '') !== 'succeeded') {
            return redirect()->route('credits.index')->with('error', $result['error'] ?? 'Payment could not be confirmed. Please try again.');
        }

        $amount = (float) $request->amount;
        $user = auth()->user();
        $user->addCredits($amount, 'topup', 'stripe', $request->payment_intent_id, 'Credit top-up via card');

        return redirect()->route('credits.index')->with('success', 'Credits added successfully! Your new balance is '.format_price($user->fresh()->balance));
    }

    /**
     * Initiate PayHere payment for credit top-up (redirect to PayHere).
     */
    public function initiatePayHereTopup(Request $request)
    {
        if (! filter_var(Setting::get('credit_topup_enabled', '1'), FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->route('credits.index')->with('error', 'Credit top-up is currently disabled.');
        }
        if (! filter_var(Setting::get('credit_topup_payhere_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->route('credits.index')->with('error', 'PayHere is not available for top-up.');
        }

        $minAmount = (float) (Setting::get('credit_topup_min_amount', '5') ?: 5);
        $maxAmount = (float) (Setting::get('credit_topup_max_amount', '10000') ?: 10000);

        $request->validate([
            'amount' => 'required|numeric|min:'.$minAmount.'|max:'.$maxAmount,
        ]);

        $amount = (float) $request->amount;
        $gateway = app(PaymentGatewayRepository::class)->get('payhere');
        if (! $gateway || ! $gateway->isEnabled()) {
            return redirect()->route('credits.index')->with('error', 'PayHere is not configured.');
        }

        $orderId = 'TOPUP-'.uniqid().'-'.time();

        $order = Order::create([
            'user_id' => auth()->id(),
            'template_id' => null,
            'template_name' => 'Credit top-up',
            'quantity' => 1,
            'total_amount' => $amount,
            'payment_method' => 'payhere',
            'status' => 'pending',
            'checkout_data' => [
                'payhere_order_id' => $orderId,
                'is_credit_topup' => true,
            ],
        ]);

        $amountStr = number_format($amount, 2, '.', '');
        $currency = 'LKR';
        $hash = $gateway->generateHash($orderId, $amountStr, $currency);

        $user = auth()->user();
        $nameParts = explode(' ', $user->name ?? 'Customer', 2);

        $payment = [
            'sandbox' => $gateway->isSandbox(),
            'merchant_id' => $gateway->getMerchantId(),
            'return_url' => route('credits.payhere.return'),
            'cancel_url' => route('credits.payhere.cancel'),
            'notify_url' => route('payhere.notify'),
            'order_id' => $orderId,
            'items' => 'Credit top-up',
            'amount' => $amountStr,
            'currency' => $currency,
            'hash' => $hash,
            'first_name' => $nameParts[0] ?? 'Customer',
            'last_name' => $nameParts[1] ?? '',
            'email' => $user->email ?? 'customer@example.com',
            'phone' => $user->phone ?? '0771234567',
            'address' => 'N/A',
            'city' => 'Colombo',
            'country' => 'Sri Lanka',
        ];

        return view('payhere-module::redirect', ['payment' => $payment]);
    }

    /**
     * PayHere return URL for credit top-up (user redirected back after payment).
     */
    public function payHereReturn(Request $request)
    {
        $orderId = trim((string) $request->get('order_id', ''));
        $order = $orderId ? Order::where('checkout_data->payhere_order_id', $orderId)->first() : null;

        if (! $order || ($order->checkout_data['is_credit_topup'] ?? false) !== true) {
            return redirect()->route('credits.index')->with('info', 'Your payment is being processed.');
        }

        $status = $request->get('status_code') ?? $request->get('payhere_payment_status') ?? $request->get('payment_status');
        if ($order->status === 'pending' && ($status == 2 || $status === '2')) {
            $data = $order->checkout_data ?? [];
            $data['payhere_payment_id'] = $data['payhere_payment_id'] ?? $request->get('payment_id');
            $order->update(['status' => 'completed', 'checkout_data' => $data]);
            $order->user->addCredits((float) $order->total_amount, 'topup', 'payhere', $data['payhere_payment_id'] ?? $orderId, 'Credit top-up via PayHere');
        }

        if ($order->fresh()->status === 'completed') {
            return redirect()->route('credits.index')->with('success', 'Credits added successfully! Your new balance is '.format_price($order->user->fresh()->balance));
        }

        return redirect()->route('credits.index')->with('info', 'Your payment is being processed. You will receive confirmation shortly.');
    }

    /**
     * PayHere cancel URL for credit top-up.
     */
    public function payHereCancel(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = $orderId ? Order::where('checkout_data->payhere_order_id', $orderId)->first() : null;
        if ($order && $order->status === 'pending' && ($order->checkout_data['is_credit_topup'] ?? false)) {
            $order->update(['status' => 'cancelled']);
        }

        return redirect()->route('credits.index')->with('error', 'Payment was cancelled.');
    }
}
