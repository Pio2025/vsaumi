<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Payment extends BaseConfig
{
    /**
     * Payment method keys mapped to the adapter class that handles them.
     *
     * @var array<string, class-string>
     */
    public array $adapters = [
        'visa'       => \App\Libraries\PaymentGateway\Adapters\VisaAdapter::class,
        'mastercard' => \App\Libraries\PaymentGateway\Adapters\MastercardAdapter::class,
        'mpaisa'     => \App\Libraries\PaymentGateway\Adapters\MPaisaAdapter::class,
        'mycash'     => \App\Libraries\PaymentGateway\Adapters\MyCashAdapter::class,
    ];

    // --- Card processor (Visa/Mastercard) ---
    public string $cardApiUrl    = '';
    public string $cardApiKey    = '';
    public string $cardApiSecret = '';

    // --- Vodafone M-PAiSA Payments Gateway (staging by default) ---
    public string $mpaisaBaseUrl        = 'https://payments-staging.m-paisa.com';
    public string $mpaisaClientId       = '';
    public string $mpaisaClientSecret   = '';
    public string $mpaisaMerchantSecret = '';

    // --- Digicel MyCash ---
    public string $mycashApiUrl         = '';
    public string $mycashApiKey         = '';
    public string $mycashMerchantId     = '';
    public string $mycashWebhookSecret  = '';

    /**
     * Default platform fee taken on settlement, as a decimal fraction (e.g. 0.02 = 2%).
     */
    public float $platformFeeRate = 0.02;
}
