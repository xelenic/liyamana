<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\Setting;
use App\Services\Payment\PaymentGatewayRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreditsController extends Controller
{
    /**
     * Get credits overview: balance, top-up config, payment methods.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $balance = (float) ($user->balance ?? 0);

        $creditTopupEnabled = filter_var(Setting::get('credit_topup_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        $minAmount = (float) (Setting::get('credit_topup_min_amount', '5') ?: 5);
        $maxAmount = (float) (Setting::get('credit_topup_max_amount', '10000') ?: 10000);

        $paymentMethodsForTopup = [];
        $stripeTopupEnabled = filter_var(Setting::get('credit_topup_stripe_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        $gateway = app(PaymentGatewayRepository::class)->get('stripe');
        if ($stripeTopupEnabled && $gateway && $gateway->isEnabled()) {
            $paymentMethodsForTopup[] = ['id' => 'stripe', 'name' => 'Credit / Debit Card', 'icon' => 'fa-credit-card'];
        }
        $payhereTopupEnabled = filter_var(Setting::get('credit_topup_payhere_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $payhereGateway = app(PaymentGatewayRepository::class)->get('payhere');
        if ($payhereTopupEnabled && $payhereGateway && $payhereGateway->isEnabled()) {
            $paymentMethodsForTopup[] = ['id' => 'payhere', 'name' => 'PayHere', 'icon' => 'fa-credit-card'];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $balance,
                'credit_topup_enabled' => $creditTopupEnabled,
                'min_amount' => $minAmount,
                'max_amount' => $maxAmount,
                'payment_methods' => $paymentMethodsForTopup,
                'currency_symbol' => Setting::get('currency_symbol', '$'),
            ],
        ]);
    }

    /**
     * List credit transactions (paginated).
     */
    public function transactions(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 15), 50);

        $transactions = CreditTransaction::where('user_id', auth()->id())
            ->latest()
            ->paginate($perPage);

        $items = $transactions->map(fn ($tx) => [
            'id' => $tx->id,
            'type' => $tx->type,
            'amount' => (float) $tx->amount,
            'balance_after' => (float) ($tx->balance_after ?? 0),
            'payment_method' => $tx->payment_method,
            'reference' => $tx->reference,
            'description' => $tx->description,
            'status' => $tx->status,
            'created_at' => $tx->created_at->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }
}
