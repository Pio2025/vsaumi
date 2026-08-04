<?php

namespace App\Libraries\PaymentGateway\Helpers;

/**
 * HMAC request-signing helpers shared by the API auth filter and (eventually)
 * client-side SDKs. A merchant signs `rawBody` with their API secret and sends
 * the result in the `X-Signature` header; the filter recomputes it server-side.
 *
 * The API secret must be recoverable in plaintext to compute the HMAC, so it
 * cannot be stored as a one-way hash (bcrypt/password_hash) like a login
 * password would be. It is stored encrypted at rest via CI4's Encryption
 * service and decrypted here at verification time.
 */
class SecurityHelper
{
    public static function sign(string $rawBody, string $apiSecret): string
    {
        return hash_hmac('sha256', $rawBody, $apiSecret);
    }

    public static function verify(string $rawBody, string $apiSecret, string $signature): bool
    {
        return hash_equals(self::sign($rawBody, $apiSecret), $signature);
    }

    public static function encryptSecret(string $plaintextSecret): string
    {
        return base64_encode(service('encrypter')->encrypt($plaintextSecret));
    }

    public static function decryptSecret(string $encryptedSecret): string
    {
        return service('encrypter')->decrypt(base64_decode($encryptedSecret));
    }

    public static function generateSecret(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function generateApiKey(): string
    {
        return bin2hex(random_bytes(24));
    }
}
