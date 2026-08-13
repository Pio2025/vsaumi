<?php

namespace App\Libraries\PaymentGateway\Adapters;

use App\Libraries\PaymentGateway\Core\Interfaces\PaymentGatewayInterface;
use App\Models\TransactionModel;
use Config\Payment;
use Config\Services;
use RuntimeException;
use Throwable;

/**
 * Vodafone Fiji M-PAiSA Payments Gateway adapter, wired to the real
 * staging REST API (https://payments-staging.m-paisa.com) per the Vodafone
 * M-PAiSA Payments Gateway API Guide.
 *
 * Unlike the card adapters, M-PAiSA is a browser-redirect flow, not a
 * server-to-server one: processPayment() only performs the handshake
 * (generateAuth + the signed API call) and hands back a redirect_url for
 * the customer's own browser to load — the customer authenticates and
 * approves directly on Vodafone's hosted page. Completion is confirmed
 * when the browser is redirected back to us, via verifyRedirectSignature(),
 * with verifyTransaction() as a status-checker fallback for reconciliation.
 */
class MPaisaAdapter implements PaymentGatewayInterface
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $merchantSecret;

    public function __construct()
    {
        $config               = config(Payment::class);
        $this->baseUrl        = rtrim($config->mpaisaBaseUrl, '/');
        $this->clientId       = $config->mpaisaClientId;
        $this->clientSecret   = $config->mpaisaClientSecret;
        $this->merchantSecret = $config->mpaisaMerchantSecret !== '' ? $config->mpaisaMerchantSecret : $config->mpaisaClientSecret;
    }

    /**
     * Performs the generateAuth + signed handshake API calls and returns a
     * redirect_url for the customer's browser. The payment page itself
     * (API call 3) is never proxied server-side — only the customer's own
     * browser may load it, since it's an interactive Vodafone login/OTP page.
     */
    public function processPayment(array $paymentData): array
    {
        if ($this->clientId === '' || $this->clientSecret === '') {
            log_message('error', 'M-PAiSA payment attempted with no clientId/clientSecret configured.');

            return [
                'status'        => 'failed',
                'psp_reference' => '',
                'message'       => 'M-PAiSA is not configured yet. Please choose another payment method.',
            ];
        }

        $tID  = (string) $paymentData['reference'];
        $amt  = number_format((float) $paymentData['amount'], 2, '.', '');
        $iDet = $this->buildItemDetail($paymentData);
        $url  = site_url('checkout/mpaisa/return');

        try {
            $token     = $this->getBearerToken();
            $handshake = $this->handshake($token, $tID, $amt, $iDet, $url);

            return [
                'status'        => 'pending',
                'psp_reference' => (string) $handshake['requestID'],
                'message'       => 'Redirecting to M-PAiSA for approval.',
                'redirect_url'  => $this->buildPaymentPageUrl($tID, $amt, $iDet, $url, (string) $handshake['requestID']),
                'metadata'      => ['item_detail' => $iDet],
            ];
        } catch (Throwable $e) {
            log_message('error', 'M-PAiSA processPayment failed: ' . $e->getMessage());

            return [
                'status'        => 'failed',
                'psp_reference' => '',
                'message'       => 'Could not reach M-PAiSA. Please try again shortly.',
            ];
        }
    }

    /**
     * M-PAiSA's staging API has no server-to-server webhook — completion is
     * confirmed via the signed browser redirect (verifyRedirectSignature())
     * or the status-checker API (verifyTransaction()).
     */
    public function handleWebhook(array $payload): array
    {
        return ['psp_reference' => '', 'status' => 'unsupported'];
    }

    /**
     * Recomputes tokenv2 for a browser-redirect callback and compares it,
     * constant-time, against what M-PAiSA sent. $transaction supplies the
     * amount and item-detail snapshot recorded at handshake time — never
     * values taken from the callback itself, so a tampered query string
     * can't forge its own comparison target.
     */
    public function verifyRedirectSignature(array $transaction, array $params): bool
    {
        $requestId = trim((string) ($params['rID'] ?? ''));
        $rCode     = trim((string) ($params['rCode'] ?? ''));
        $tokenv2   = trim((string) ($params['tokenv2'] ?? ''));

        if ($requestId === '' || $rCode === '' || $tokenv2 === '') {
            return false;
        }

        $itemDetail = json_decode((string) ($transaction['metadata'] ?? ''), true)['item_detail'] ?? $transaction['reference'];
        $amt        = number_format((float) $transaction['amount'], 2, '.', '');

        $expected = hash('sha256', $transaction['reference'] . $amt . $itemDetail . $requestId . $this->merchantSecret . $rCode);

        return hash_equals(strtolower($expected), strtolower($tokenv2));
    }

    /**
     * Maps a M-PAiSA response code to our internal transaction status.
     */
    public function mapResponseCode(string $rCode): string
    {
        return match ($rCode) {
            '101', '112' => 'captured',
            '100'        => 'pending',
            '111'        => 'voided',
            default      => 'failed',
        };
    }

    /**
     * Reconciliation fallback: asks M-PAiSA directly for a transaction's
     * current status, for cases where the customer never made it back
     * through the browser redirect.
     */
    public function verifyTransaction(string $referenceId): array
    {
        $transaction = model(TransactionModel::class)->findByReference($referenceId);

        if ($transaction === null || empty($transaction['psp_reference'])) {
            return ['status' => 'unknown'];
        }

        try {
            $token    = $this->getBearerToken();
            $response = $this->request()->get('/live/requeststatus/', [
                'query'   => [
                    'rID' => $transaction['psp_reference'],
                    'tID' => $transaction['reference'],
                    'cID' => $this->clientId,
                ],
                'headers' => ['Authorization' => 'Bearer ' . $token],
            ]);

            $body = json_decode((string) $response->getBody(), true);

            if (! is_array($body) || ! isset($body['responsecode'])) {
                return ['status' => 'unknown'];
            }

            return ['status' => $this->mapResponseCode((string) $body['responsecode'])];
        } catch (Throwable $e) {
            log_message('error', 'M-PAiSA verifyTransaction failed: ' . $e->getMessage());

            return ['status' => 'unknown'];
        }
    }

    private function buildItemDetail(array $paymentData): string
    {
        $detail = trim((string) ($paymentData['product_name'] ?? '')) !== ''
            ? (string) $paymentData['product_name']
            : 'Payment via VSaumi';

        return substr($detail, 0, 190);
    }

    /**
     * API call 2: the signed handshake that returns a requestID and the
     * authdigestv2 hash we must verify before trusting it.
     */
    private function handshake(string $token, string $tID, string $amt, string $iDet, string $url): array
    {
        $response = $this->request()->get('/API/', [
            'query'   => [
                'url'  => $url,
                'tID'  => $tID,
                'amt'  => $amt,
                'cID'  => $this->clientId,
                'iDet' => $iDet,
            ],
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);

        $body = json_decode((string) $response->getBody(), true);

        if (! is_array($body) || empty($body['requestID']) || (int) ($body['response'] ?? 0) !== 101) {
            throw new RuntimeException('M-PAiSA handshake was not successful.');
        }

        $expectedDigest = hash('sha256', $tID . $amt . $iDet . $this->merchantSecret . (string) $body['response']);

        if (! hash_equals(strtolower($expectedDigest), strtolower((string) ($body['authdigestv2'] ?? '')))) {
            throw new RuntimeException('M-PAiSA handshake signature did not match — refusing to proceed.');
        }

        return $body;
    }

    /**
     * API call 3: the hosted payment page URL — a genuine browser redirect
     * target, never fetched server-side.
     */
    private function buildPaymentPageUrl(string $tID, string $amt, string $iDet, string $url, string $requestId): string
    {
        $query = http_build_query([
            'url'  => $url,
            'tID'  => $tID,
            'amt'  => $amt,
            'cID'  => $this->clientId,
            'iDet' => $iDet,
            'rID'  => $requestId,
        ]);

        return $this->baseUrl . '/live/?' . $query;
    }

    /**
     * API call 1: exchanges clientId/clientSecret for a bearer token, cached
     * just under its ~10-minute expiry so repeat checkouts don't re-auth on
     * every request.
     */
    private function getBearerToken(): string
    {
        $cache    = cache();
        $cacheKey = 'mpaisa_bearer_token';
        $cached   = $cache->get($cacheKey);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = $this->request()->post('/live/API/generateAuth', [
            'json' => [
                'clientId'     => $this->clientId,
                'clientSecret' => $this->clientSecret,
            ],
        ]);

        $body = json_decode((string) $response->getBody(), true);

        if (! is_array($body) || empty($body['token'])) {
            throw new RuntimeException('Could not authenticate with M-PAiSA.');
        }

        $ttl = max(60, ((int) ($body['expiresIn'] ?? 600)) - 30);
        $cache->save($cacheKey, $body['token'], $ttl);

        return $body['token'];
    }

    private function request()
    {
        return Services::curlrequest([
            'baseURI' => $this->baseUrl,
            'timeout' => 15,
        ], null, null, false);
    }
}
