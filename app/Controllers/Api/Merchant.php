<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\PaymentGateway\Helpers\ApiContext;
use App\Libraries\PaymentGateway\Helpers\SecurityHelper;
use App\Models\ApplicationModel;
use App\Models\MerchantModel;

class Merchant extends BaseController
{
    /**
     * POST /api/v1/merchants
     * Public registration endpoint. Creates the business plus its first
     * application (see section 3.1 of the design doc) — an application is
     * an app/website that will call the payment API, and it's what carries
     * the api_key/api_secret pair, not the merchant. Account starts as
     * 'pending' until an admin approves it and a subscription payment
     * activates it. The API secret is returned once, in plaintext, and
     * never again: only its encrypted form is persisted.
     */
    public function register()
    {
        $data = $this->request->getJSON(true) ?? [];

        $rules = [
            'business_name' => 'required|min_length[2]|max_length[150]',
            'contact_email' => 'required|valid_email|is_unique[merchants.contact_email]',
            'app_name'      => 'required|min_length[2]|max_length[150]',
            'website_url'   => 'permit_empty|valid_url_strict|max_length[255]',
        ];

        if (! $this->validateData($data, $rules)) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $this->validator->getErrors()]);
        }

        $plainSecret = SecurityHelper::generateSecret();
        $apiKey      = SecurityHelper::generateApiKey();

        $merchants    = model(MerchantModel::class);
        $applications = model(ApplicationModel::class);

        $db = db_connect();
        $db->transStart();

        $merchantId = $merchants->insert([
            'business_name' => $data['business_name'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'] ?? null,
            'status'        => 'pending',
        ], true);

        $applicationId = $applications->insert([
            'merchant_id'           => $merchantId,
            'name'                  => $data['app_name'],
            'website_url'           => $data['website_url'] ?? null,
            'status'                => 'active',
            'api_key'               => $apiKey,
            'api_secret_encrypted'  => SecurityHelper::encryptSecret($plainSecret),
        ], true);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Could not create merchant.']);
        }

        $merchant    = $merchants->find($merchantId);
        $application = $applications->find($applicationId);

        return $this->response->setStatusCode(201)->setJSON([
            'merchant_id' => $merchant['id'],
            'status'      => $merchant['status'],
            'application' => [
                'id'          => $application['id'],
                'name'        => $application['name'],
                'website_url' => $application['website_url'],
                'api_key'     => $application['api_key'],
                'api_secret'  => $plainSecret,
            ],
            'notice' => 'Store the api_secret now — it will not be shown again.',
        ]);
    }

    /**
     * GET /api/v1/merchants/me
     * Behind ApiAuthFilter — returns the authenticated application and its
     * parent merchant.
     */
    public function me()
    {
        $merchant    = ApiContext::merchant();
        $application = ApiContext::application();

        unset($application['api_secret_encrypted'], $merchant['password_hash'], $merchant['api_secret_encrypted']);

        return $this->response->setJSON([
            'merchant'    => $merchant,
            'application' => $application,
        ]);
    }
}
