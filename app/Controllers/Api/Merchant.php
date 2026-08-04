<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\PaymentGateway\Helpers\ApiContext;
use App\Libraries\PaymentGateway\Helpers\SecurityHelper;
use App\Models\MerchantModel;

class Merchant extends BaseController
{
    /**
     * POST /api/v1/merchants
     * Public registration endpoint. Account starts as 'pending' until an
     * admin approves it and a subscription payment activates it — see
     * section 3.1 of the design doc. The API secret is returned once, in
     * plaintext, and never again: only its encrypted form is persisted.
     */
    public function register()
    {
        $data = $this->request->getJSON(true) ?? [];

        $rules = [
            'business_name' => 'required|min_length[2]|max_length[150]',
            'contact_email' => 'required|valid_email|is_unique[merchants.contact_email]',
        ];

        if (! $this->validateData($data, $rules)) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $this->validator->getErrors()]);
        }

        $plainSecret = SecurityHelper::generateSecret();

        $merchants  = model(MerchantModel::class);
        $merchantId = $merchants->insert([
            'business_name'        => $data['business_name'],
            'contact_email'        => $data['contact_email'],
            'contact_phone'        => $data['contact_phone'] ?? null,
            'status'                => 'pending',
            'api_key'               => SecurityHelper::generateApiKey(),
            'api_secret_encrypted'  => SecurityHelper::encryptSecret($plainSecret),
        ], true);

        $merchant = $merchants->find($merchantId);

        return $this->response->setStatusCode(201)->setJSON([
            'merchant_id' => $merchant['id'],
            'status'      => $merchant['status'],
            'api_key'     => $merchant['api_key'],
            'api_secret'  => $plainSecret,
            'notice'      => 'Store the api_secret now — it will not be shown again.',
        ]);
    }

    /**
     * GET /api/v1/merchants/me
     * Behind ApiAuthFilter — returns the authenticated merchant's own profile.
     */
    public function me()
    {
        $merchant = ApiContext::merchant();
        unset($merchant['api_secret_encrypted']);

        return $this->response->setJSON($merchant);
    }
}
