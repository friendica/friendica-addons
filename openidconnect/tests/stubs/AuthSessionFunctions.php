<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Auth;

final class SessionFunctionSpy
{
    public static bool $sessionWriteCloseCalled = false;

    public static function reset(): void
    {
        self::$sessionWriteCloseCalled = false;
    }
}

function session_write_close(): bool
{
    SessionFunctionSpy::$sessionWriteCloseCalled = true;
    return true;
}
