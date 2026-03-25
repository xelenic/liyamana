<?php

namespace Modules\PayHereModule;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Setting;

class PayHerePaymentGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'payhere';
    }

    public function isEnabled(): bool
    {
        $enabled = filter_var(Setting::get('payment_payhere_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $merchantId = $this->getMerchantId();

        return $enabled && ! empty($merchantId);
    }

    public function getMerchantId(): string
    {
        return (string) (Setting::get('payment_payhere_merchant_id') ?: '');
    }

    public function getMerchantSecret(): string
    {
        return (string) (Setting::get('payment_payhere_merchant_secret') ?: '');
    }

    public function isSandbox(): bool
    {
        return filter_var(Setting::get('payment_payhere_sandbox', '1'), FILTER_VALIDATE_BOOLEAN);
    }

    public function getPayUrl(): string
    {
        return $this->isSandbox()
            ? 'https://sandbox.payhere.lk/pay'
            : 'https://www.payhere.lk/pay';
    }

    /**
     * Generate PayHere hash for security.
     * Hash = MD5(merchant_id + order_id + amount + currency + MD5(merchant_secret))
     * Per PayHere docs: https://support.payhere.lk
     */
    public function generateHash(string $orderId, string $amount, string $currency = 'LKR'): string
    {
        $secret = $this->getMerchantSecret();
        $secretMd5 = strtoupper(md5($secret));
        $concat = $this->getMerchantId().$orderId.$amount.$currency.$secretMd5;

        return strtoupper(md5($concat));
    }

    /**
     * Verify PayHere notify hash.
     * PayHere docs: md5sig = strtoupper(MD5(merchant_id + order_id + payhere_amount + payhere_currency + status_code + strtoupper(MD5(secret))))
     */
    public function verifyNotifyHash(array $params): bool
    {
        $receivedHash = $params['md5sig'] ?? '';
        if ($receivedHash === '') {
            return false;
        }
        $orderId = trim((string) ($params['order_id'] ?? ''));
        $amount = $params['payhere_amount'] ?? $params['amount'] ?? '';
        $amount = number_format((float) preg_replace('/[^0-9.]/', '', $amount), 2, '.', '');
        $currency = trim((string) ($params['payhere_currency'] ?? $params['currency'] ?? 'LKR'));
        $statusCode = $params['status_code'] ?? $params['payhere_payment_status'] ?? $params['payment_status'] ?? '';

        $secretMd5 = strtoupper(md5($this->getMerchantSecret()));
        $concatWithStatus = $this->getMerchantId().$orderId.$amount.$currency.$statusCode.$secretMd5;
        $expectedWithStatus = strtoupper(md5($concatWithStatus));
        if (hash_equals($expectedWithStatus, $receivedHash)) {
            return true;
        }

        $expectedWithoutStatus = strtoupper(md5($this->getMerchantId().$orderId.$amount.$currency.$secretMd5));
        if (hash_equals($expectedWithoutStatus, $receivedHash)) {
            return true;
        }

        return false;
    }

    /**
     * PayHere uses redirect flow - stub for interface compatibility.
     */
    public function createPaymentIntent(int $amountInCents, string $currency = 'usd', array $metadata = []): array
    {
        return [
            'success' => true,
            'redirect' => true,
            'gateway' => 'payhere',
        ];
    }

    /**
     * PayHere confirmation happens via notify callback - stub for interface.
     */
    public function confirmPayment(string $paymentIntentId, ?string $paymentMethodId = null): array
    {
        return [
            'success' => false,
            'error' => 'PayHere uses redirect flow. Payment is confirmed via callback.',
        ];
    }

    /**
     * Stub for interface compatibility.
     */
    public function getPaymentIntent(string $paymentIntentId): array
    {
        return [
            'success' => false,
            'error' => 'Not applicable for PayHere',
        ];
    }
}
