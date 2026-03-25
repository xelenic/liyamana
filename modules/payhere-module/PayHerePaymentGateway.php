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
        $concat = $this->getMerchantId() . $orderId . $amount . $currency . $secretMd5;

        return strtoupper(md5($concat));
    }

    /**
     * Verify PayHere notify hash.
     * PayHere may send amount as payhere_amount or amount; currency as payhere_currency or currency.
     * Hash formulas: MD5(merchant_id + order_id + amount + currency + MD5(secret)) or with status_code.
     */
    public function verifyNotifyHash(array $params): bool
    {
        $receivedHash = $params['md5sig'] ?? '';
        if ($receivedHash === '') {
            return false;
        }
        $orderId = $params['order_id'] ?? '';
        $amount = $params['payhere_amount'] ?? $params['amount'] ?? '';
        $currency = $params['payhere_currency'] ?? $params['currency'] ?? 'LKR';
        $statusCode = $params['payhere_payment_status'] ?? $params['payment_status'] ?? $params['status_code'] ?? '';

        $amount = (string) $amount;
        $expectedHash = $this->generateHash($orderId, $amount, $currency);
        if (hash_equals($expectedHash, $receivedHash)) {
            return true;
        }

        $secretMd5 = strtoupper(md5($this->getMerchantSecret()));
        $concatAlt = $this->getMerchantId() . $orderId . $amount . $currency . $statusCode . $secretMd5;
        $expectedAlt = strtoupper(md5($concatAlt));
        if (hash_equals($expectedAlt, $receivedHash)) {
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
