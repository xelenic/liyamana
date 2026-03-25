<?php

namespace Modules\PayHereModule;

use App\Providers\ModuleServiceProvider as BaseModuleServiceProvider;
use App\Contracts\PaymentGatewayInterface;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'payhere-module';

    public function boot(): void
    {
        parent::boot();

        $this->registerPaymentGateway();
        $this->registerPaymentSettings();
    }

    protected function registerPaymentGateway(): void
    {
        $extra = config('payment.extra_gateways', []);
        $extra['payhere'] = PayHerePaymentGateway::class;
        config(['payment.extra_gateways' => $extra]);

        $this->app->bind(PayHerePaymentGateway::class, function ($app) {
            return new PayHerePaymentGateway();
        });
    }

    protected function registerPaymentSettings(): void
    {
        $extra = config('payment.extra_settings', []);
        $extra = array_merge($extra, [
            'payment_payhere_enabled' => ['label' => 'Enable PayHere', 'type' => 'boolean', 'default' => '0'],
            'payment_payhere_merchant_id' => ['label' => 'PayHere Merchant ID', 'type' => 'text', 'default' => ''],
            'payment_payhere_merchant_secret' => ['label' => 'PayHere Merchant Secret', 'type' => 'password', 'default' => ''],
            'payment_payhere_sandbox' => ['label' => 'Use Sandbox (Test Mode)', 'type' => 'boolean', 'default' => '1'],
        ]);
        config(['payment.extra_settings' => $extra]);
    }
}
