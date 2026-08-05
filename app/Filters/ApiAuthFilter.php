<?php

namespace App\Filters;

use App\Libraries\PaymentGateway\Helpers\ApiContext;
use App\Libraries\PaymentGateway\Helpers\SecurityHelper;
use App\Models\ApplicationModel;
use App\Models\MerchantModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Authenticates merchant API requests.
 *
 * Expects headers:
 *   X-Api-Key:   the calling application's public API key
 *   X-Signature: hash_hmac('sha256', <raw request body>, <application's api secret>)
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

        $applicationModel = model(ApplicationModel::class);
        $application       = $applicationModel->findByApiKey($apiKey);

        if ($application === null) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid API key.']);
        }

        $merchant = model(MerchantModel::class)->find($application['merchant_id']);

        if ($merchant === null || $merchant['status'] !== 'active') {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['error' => 'Merchant account is not active.']);
        }

        if (! $applicationModel->isSubscriptionActive($application)) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['error' => 'Subscription inactive or expired.']);
        }

        $rawBody   = $request->getBody() ?? '';
        $apiSecret = SecurityHelper::decryptSecret($application['api_secret_encrypted']);

        if (! SecurityHelper::verify($rawBody, $apiSecret, $signature)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid request signature.']);
        }

        ApiContext::setContext($application, $merchant);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after the response is sent.
    }
}
