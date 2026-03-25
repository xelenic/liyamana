<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Setting;
use Illuminate\Support\Collection;

class PaymentGatewayRepository
{
    protected array $gateways = [];

    public function __construct()
    {
        $this->gateways = array_merge(
            config('payment.gateways', [
                'stripe' => StripePaymentGateway::class,
            ]),
            config('payment.extra_gateways', [])
        );
    }

    /**
     * Get the gateway instance for the given name.
     */
    public function get(string $name): ?PaymentGatewayInterface
    {
        $class = $this->gateways[$name] ?? null;
        if (! $class || ! class_exists($class)) {
            return null;
        }

        $gateway = app($class);
        if (! $gateway instanceof PaymentGatewayInterface) {
            return null;
        }

        return $gateway;
    }

    /**
     * Get all enabled payment gateways available for checkout.
     */
    public function getEnabledGateways(): Collection
    {
        $enabled = collect();
        $allowed = $this->getEnabledGatewayNames();

        foreach ($allowed as $name) {
            $gateway = $this->get($name);
            if ($gateway && $gateway->isEnabled()) {
                $enabled->put($name, $gateway);
            }
        }

        return $enabled;
    }

    /**
     * Get list of enabled gateway names from settings.
     */
    protected function getEnabledGatewayNames(): array
    {
        $enabled = [];
        if (filter_var(Setting::get('payment_stripe_enabled', '1'), FILTER_VALIDATE_BOOLEAN)) {
            $enabled[] = 'stripe';
        }
        if (filter_var(Setting::get('payment_paypal_enabled', '1'), FILTER_VALIDATE_BOOLEAN)) {
            $enabled[] = 'paypal';
        }
        if (filter_var(Setting::get('payment_bank_transfer_enabled', '1'), FILTER_VALIDATE_BOOLEAN)) {
            $enabled[] = 'bank_transfer';
        }
        if (filter_var(Setting::get('payment_payhere_enabled', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $enabled[] = 'payhere';
        }

        return $enabled ?: ['stripe', 'paypal', 'bank_transfer'];
    }

    /**
     * Get payment methods for checkout (formatted for UI).
     */
    public function getPaymentMethodsForCheckout(): array
    {
        $methods = [
            'stripe' => ['id' => 'stripe', 'name' => 'Credit / Debit Card', 'icon' => 'fa-credit-card', 'description' => 'Pay securely with Visa, Mastercard, or Amex'],
            'paypal' => ['id' => 'paypal', 'name' => 'PayPal', 'icon' => 'fa-paypal', 'description' => 'Pay with your PayPal account'],
            'bank_transfer' => ['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'icon' => 'fa-university', 'description' => 'Direct bank transfer'],
            'payhere' => ['id' => 'payhere', 'name' => 'PayHere', 'icon' => 'fa-credit-card', 'description' => 'Pay with card, mobile wallet via PayHere (Sri Lanka)'],
        ];

        $enabledNames = $this->getEnabledGatewayNames();
        $result = [];

        foreach ($enabledNames as $name) {
            if (! isset($methods[$name])) {
                continue;
            }
            if ($name === 'stripe' || $name === 'payhere') {
                $gateway = $this->get($name);
                if (! $gateway || ! $gateway->isEnabled()) {
                    continue;
                }
            }
            $result[] = $methods[$name];
        }

        return $result;
    }
}
