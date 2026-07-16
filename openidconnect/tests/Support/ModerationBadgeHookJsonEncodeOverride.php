<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Presentation;

/**
 * Test-only helper: allows tests to force a JsonException inside
 * ModerationBadgeHook::append() by overriding json_encode in its namespace.
 */
final class ModerationBadgeHookJsonEncodeOverride
{
    public static bool $forceThrow = false;
}

/**
 * Namespace-scoped override of json_encode so tests can trigger the catch branch
 * in ModerationBadgeHook::append() without modifying production code.
 *
 * PHP function overrides in a namespace only shadow the built-in when the call
 * site is in the same namespace, which ModerationBadgeHook is.
 */
function json_encode(mixed $value, int $flags = 0, int $depth = 512): string|false
{
    if (ModerationBadgeHookJsonEncodeOverride::$forceThrow) {
        throw new \JsonException('forced json_encode failure for tests');
    }

    if (
        class_exists(SettingsHookJsonEncodeOverride::class)
        && SettingsHookJsonEncodeOverride::$forceThrow
    ) {
        throw new \JsonException('Forced JSON encode failure');
    }

    return \json_encode($value, $flags, $depth);
}
