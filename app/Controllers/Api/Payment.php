<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\PaymentGateway\Helpers\ApiContext;
use App\Libraries\PaymentGateway\Services\PaymentProcessor;
use Config\Payment as PaymentConfig;

class Payment extends BaseController
{
    /**
     * POST /api/v1/pay
     * Body: { payment_method, amount, currency?, customer_email?, customer_msisdn?, metadata? }
     */
    public function initiate()
    {
        $merchant = ApiContext::merchant();
        $data     = $this->request->getJSON(true) ?? [];

        $paymentMethod = $data['payment_method'] ?? '';
        $config        = config(PaymentConfig::class);

        if (! isset($config->adapters[$paymentMethod])) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'Unsupported or missing payment_method.',
            ]);
        }

        if (! isset($data['amount']) || ! is_numeric($data['amount']) || $data['amount'] <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'amount is required and must be a positive number.',
            ]);
        }

        $result = (new PaymentProcessor())->initiatePayment($merchant, $paymentMethod, $data);

        return $this->response->setStatusCode(201)->setJSON($result);
    }
}
