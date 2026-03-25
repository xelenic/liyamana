<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Get the gateway identifier (e.g. 'stripe', 'paypal').
     */
    public function getName(): string;

    /**
     * Check if this gateway is enabled and configured.
     */
    public function isEnabled(): bool;

    /**
     * Create a payment intent for the given amount (in smallest currency unit, e.g. cents).
     * Returns array with 'client_secret', 'payment_intent_id', etc.
     *
     * @param  int  $amountInCents  Amount in smallest currency unit (e.g. cents for USD)
     * @param  string  $currency  Currency code (e.g. 'usd')
     * @param  array  $metadata  Optional metadata (e.g. order_id, user_id)
     */
    public function createPaymentIntent(int $amountInCents, string $currency = 'usd', array $metadata = []): array;

    /**
     * Confirm/complete a payment using the payment intent ID and optional payment method.
     *
     * @param  string  $paymentIntentId  The Stripe PaymentIntent ID
     * @param  string|null  $paymentMethodId  Optional payment method ID (for card payments)
     */
    public function confirmPayment(string $paymentIntentId, ?string $paymentMethodId = null): array;

    /**
     * Retrieve payment intent status.
     */
    public function getPaymentIntent(string $paymentIntentId): array;
}
