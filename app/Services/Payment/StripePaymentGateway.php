<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Setting;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripePaymentGateway implements PaymentGatewayInterface
{
    protected ?StripeClient $stripe = null;

    public function getName(): string
    {
        return 'stripe';
    }

    public function isEnabled(): bool
    {
        $enabled = filter_var(Setting::get('payment_stripe_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $secretKey = $this->getSecretKey();

        return $enabled && ! empty($secretKey);
    }

    protected function getStripe(): StripeClient
    {
        if ($this->stripe === null) {
            $secretKey = $this->getSecretKey();
            if (empty($secretKey)) {
                throw new \RuntimeException('Stripe secret key is not configured.');
            }
            $this->stripe = new StripeClient($secretKey);
        }

        return $this->stripe;
    }

    public function getPublishableKey(): string
    {
        return (string) (Setting::get('payment_stripe_publishable_key') ?: config('services.stripe.key') ?: '');
    }

    protected function getSecretKey(): string
    {
        return (string) (Setting::get('payment_stripe_secret_key') ?: config('services.stripe.secret') ?: '');
    }

    public function createPaymentIntent(int $amountInCents, string $currency = 'usd', array $metadata = []): array
    {
        try {
            $stripe = $this->getStripe();
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $amountInCents,
                'currency' => strtolower($currency),
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];
        } catch (ApiErrorException $e) {
            \Log::error('Stripe createPaymentIntent error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function confirmPayment(string $paymentIntentId, ?string $paymentMethodId = null): array
    {
        try {
            $stripe = $this->getStripe();
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

            if ($paymentIntent->status === 'succeeded') {
                return [
                    'success' => true,
                    'status' => 'succeeded',
                ];
            }

            if ($paymentIntent->status === 'requires_payment_method') {
                return [
                    'success' => false,
                    'error' => 'Payment requires a valid payment method.',
                ];
            }

            return [
                'success' => $paymentIntent->status === 'succeeded',
                'status' => $paymentIntent->status,
            ];
        } catch (ApiErrorException $e) {
            \Log::error('Stripe confirmPayment error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getPaymentIntent(string $paymentIntentId): array
    {
        try {
            $stripe = $this->getStripe();
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

            return [
                'success' => true,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
            ];
        } catch (ApiErrorException $e) {
            \Log::error('Stripe getPaymentIntent error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
