<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Account;

use Friendica\Addon\OpenIdConnect\Account\AvatarUpdater;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class AvatarUpdaterTest extends AddonTestCase
{
    public function testRejectsNonUrlAndNonHttpsThirdPartyAvatarUrls(): void
    {
        self::assertFalse((new AvatarUpdater())->isSafeUrl('not a url'));
        self::assertFalse((new AvatarUpdater())->isSafeUrl('http://cdn.example.test/avatar.png'));
    }

    public function testIsSafeUrlAllowsSameHostAsConfiguredDiscoveryUrlEvenOnPrivateNetwork(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'http://authentik-server.friendica-local-dev.orb.local:9000/application/o/demo/.well-known/openid-configuration');

        self::assertTrue((new AvatarUpdater())->isSafeUrl('http://authentik-server.friendica-local-dev.orb.local:9000/media/avatar.png'));
    }

    public function testIsSafeUrlRejectsNonHttpsThirdPartyHost(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        self::assertFalse((new AvatarUpdater())->isSafeUrl('http://cdn.example.com/avatar.png'));
    }
}
