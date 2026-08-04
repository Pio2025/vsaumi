<?php

namespace App\Libraries\PaymentGateway\Helpers;

/**
 * Carries the authenticated merchant for the lifetime of a single request.
 * Populated by ApiAuthFilter after API key/signature verification; read by
 * API controllers instead of re-querying or relying on dynamic request properties.
 */
class ApiContext
{
    private static ?array $merchant = null;

    public static function setMerchant(array $merchant): void
    {
        self::$merchant = $merchant;
    }

    public static function merchant(): ?array
    {
        return self::$merchant;
    }
}
