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

    public function testExtractUserinfoFromIdTokenMapsCoreClaims(): void
    {
        $claims = (object) [
            'sub' => 'oidc-sub-123',
            'email' => 'user@example.com',
            'name' => 'Example User',
            'preferred_username' => 'example-user',
            'picture' => 'https://id.example.com/avatar.png',
            'email_verified' => true,
        ];

        $userinfo = (new UserInfo())->fromIdToken($claims);

        self::assertSame('oidc-sub-123', $userinfo['sub']);
        self::assertSame('user@example.com', $userinfo['email']);
        self::assertSame('Example User', $userinfo['name']);
        self::assertSame('example-user', $userinfo['preferred_username']);
        self::assertSame('https://id.example.com/avatar.png', $userinfo['picture']);
        self::assertTrue($userinfo['email_verified']);
    }

    public function testExtractUserinfoFromIdTokenNormalizesStringEmailVerified(): void
    {
        $claims = (object) [
            'sub' => 'oidc-sub-456',
            'email' => 'user2@example.com',
            'nickname' => 'nick-fallback',
            'email_verified' => '1',
        ];

        $userinfo = (new UserInfo())->fromIdToken($claims);

        self::assertSame('nick-fallback', $userinfo['preferred_username']);
        self::assertTrue($userinfo['email_verified']);
    }
}
