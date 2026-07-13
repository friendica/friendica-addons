<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Identity;

use Friendica\Addon\OpenIdConnect\Identity\UserInfo;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class UserInfoTest extends AddonTestCase
{
    public function testMapsClaimsAndUsesNicknameFallback(): void
    {
        $result = (new UserInfo())->fromIdToken((object) ['sub' => 'sub', 'email' => 'u@example.test', 'nickname' => 'nick', 'email_verified' => '1']);
        self::assertSame('nick', $result['preferred_username']);
        self::assertTrue($result['email_verified']);
    }
}
