<?php

namespace App\Filters;

use App\Libraries\PaymentGateway\Helpers\ApiContext;
use App\Libraries\PaymentGateway\Helpers\SecurityHelper;
use App\Models\MerchantModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Authenticates merchant API requests.
 *
 * Expects headers:
 *   X-Api-Key:   the merchant's public API key
 *   X-Signature: hash_hmac('sha256', <raw request body>, <api secret>)
 */
class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $apiKey    = $request->getHeaderLine('X-Api-Key');
        $signature = $request->getHeaderLine('X-Signature');

        if ($apiKey === '' || $signature === '') {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Missing API credentials.']);
        }

        $merchant = model(MerchantModel::class)->findByApiKey($apiKey);

        if ($merchant === null) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid API key.']);
        }

        if (! model(MerchantModel::class)->isSubscriptionActive($merchant)) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['error' => 'Subscription inactive or expired.']);
        }

        $rawBody   = $request->getBody() ?? '';
        $apiSecret = SecurityHelper::decryptSecret($merchant['api_secret_encrypted']);

        if (! SecurityHelper::verify($rawBody, $apiSecret, $signature)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid request signature.']);
        }

        ApiContext::setMerchant($merchant);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after the response is sent.
    }
}
