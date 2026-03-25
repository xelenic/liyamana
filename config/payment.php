<?php

return [
    'gateways' => [
        'stripe' => \App\Services\Payment\StripePaymentGateway::class,
    ],

    'extra_gateways' => [],

    'extra_settings' => [],
];
