<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class LoginPolicyTest extends AddonTestCase
{
    public function testSanitizesOnlyRelativeReturnPaths(): void
    {
        self::assertSame('settings/account', LoginPolicy::sanitizeReturnPath('/settings/account'));
        self::assertSame('', LoginPolicy::sanitizeReturnPath('https://evil.example/'));
        self::assertSame('', LoginPolicy::sanitizeReturnPath('mona://oauth'));
    }

    public function testBuildsPkceS256Challenge(): void
    {
        self::assertSame(
            'ungWv48Bz-pBQUDeXa4iI7ADYaOWF3qctBD_YfIAFa0',
            LoginPolicy::generatePkceChallenge('abc')
        );
    }

    public function testRejectsBearerAndPostRequestsForAutoRedirect(): void
    {
        self::assertFalse(LoginPolicy::shouldAutoRedirect([], ['REQUEST_METHOD' => 'POST'], true));
        self::assertFalse(LoginPolicy::shouldAutoRedirect([], ['REQUEST_METHOD' => 'GET', 'HTTP_AUTHORIZATION' => 'Bearer token'], true));
        self::assertTrue(LoginPolicy::shouldAutoRedirect([], ['REQUEST_METHOD' => 'GET'], true));
    }
}
