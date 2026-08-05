<?php

namespace App\Libraries\PaymentGateway\Helpers;

/**
 * Carries the authenticated application and its parent merchant for the
 * lifetime of a single request. Populated by ApiAuthFilter after API
 * key/signature verification; read by API controllers instead of
 * re-querying or relying on dynamic request properties.
 */
class ApiContext
{
    private static ?array $application = null;
    private static ?array $merchant    = null;

    public static function setContext(array $application, array $merchant): void
    {
        self::$application = $application;
        self::$merchant    = $merchant;
    }

    public static function application(): ?array
    {
        return self::$application;
    }

    public static function merchant(): ?array
    {
        return self::$merchant;
    }
}
