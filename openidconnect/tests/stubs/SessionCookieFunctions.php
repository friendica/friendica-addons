<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Session;

final class CookieSpy
{
    public static array $calls = [];

    public static function reset(): void
    {
        self::$calls = [];
    }
}

function setcookie(string $name, string $value = '', array|int $expires_or_options = 0, string $path = '', string $domain = '', bool $secure = false, bool $httponly = false): bool
{
    CookieSpy::$calls[] = [
        'name' => $name,
        'value' => $value,
        'options' => $expires_or_options,
        'path' => $path,
        'domain' => $domain,
        'secure' => $secure,
        'httponly' => $httponly,
    ];

    return true;
}
