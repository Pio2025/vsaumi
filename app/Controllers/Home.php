<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\MerchantModel;

class Home extends BaseController
{
    public function index(): string
    {
        $demoMerchant     = model(MerchantModel::class)->findByEmail('demo@vsaumi.fj');
        $demoApplication  = $demoMerchant
            ? model(ApplicationModel::class)->where('merchant_id', $demoMerchant['id'])->orderBy('id', 'ASC')->first()
            : null;

        $plans = [
            'starter'    => ['label' => 'Starter', 'price' => 29, 'note' => 'Up to 500 transactions/mo', 'features' => ['All payment methods', 'Standard settlement', 'Email support']],
            'growth'     => ['label' => 'Growth', 'price' => 79, 'note' => 'Up to 5,000 transactions/mo', 'features' => ['All payment methods', 'Faster settlement', 'Priority email support']],
            'enterprise' => ['label' => 'Enterprise', 'price' => 199, 'note' => 'Unlimited transactions', 'features' => ['All payment methods', 'Fastest settlement', 'Priority support']],
        ];

        return view('home', [
            'pageTitle'   => 'Home',
            'demoApiKey'  => $demoApplication['api_key'] ?? null,
            'plans'       => $plans,
        ]);
    }
}
