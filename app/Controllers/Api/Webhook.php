<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\PaymentGateway\Services\PaymentProcessor;
use Config\Payment as PaymentConfig;

class Webhook extends BaseController
{
    /**
     * POST /api/v1/webhooks/(visa|mastercard|mpaisa|mycash)
     *
     * Not behind ApiAuthFilter — providers can't sign with a merchant secret.
     * Each adapter's handleWebhook() is responsible for verifying the
     * provider's own signature scheme before the payload is trusted.
     */
    public function handle(string $paymentMethod)
    {
        $config = config(PaymentConfig::class);

        if (! isset($config->adapters[$paymentMethod])) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Unknown provider.']);
        }

        $payload = $this->request->getJSON(true) ?? [];

        $result = (new PaymentProcessor())->applyWebhook($paymentMethod, $payload);

        if (($result['status'] ?? '') === 'invalid') {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Signature verification failed.']);
        }

        return $this->response->setStatusCode(200)->setJSON(['received' => true]);
    }
}
