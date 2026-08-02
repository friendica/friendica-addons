<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Presentation;

/**
 * Test-only helper: allows tests to force a JsonException inside
 * SettingsHook::append() by overriding json_encode in its namespace.
 */
final class SettingsHookJsonEncodeOverride
{
    public static bool $forceThrow = false;

    public static function enable(): void
    {
        self::$forceThrow = true;
    }

    public static function disable(): void
    {
        self::$forceThrow = false;
    }
}

/**
 * Namespace-scoped override of json_encode so tests can trigger the catch branch
 * in SettingsHook::append() without modifying production code.
 *
 * PHP function overrides in a namespace only shadow the built-in when the call
 * site is in the same namespace, which SettingsHook is.
 */
if (!function_exists(__NAMESPACE__ . '\\json_encode')) {
    function json_encode(mixed $value, int $flags = 0, int $depth = 512): string|false
    {
        if (SettingsHookJsonEncodeOverride::$forceThrow) {
            throw new \JsonException('Forced JSON encode failure');
        }

        return \json_encode($value, $flags, $depth);
    }
}