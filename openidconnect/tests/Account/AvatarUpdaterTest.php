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

    public function testIsSafeUrlRejectsHostWhenAnyResolvedIpIsPrivate(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        $updater = new AvatarUpdater(static fn(string $host): array => ['8.8.8.8', '10.0.0.2']);

        self::assertFalse($updater->isSafeUrl('https://cdn.example.com/avatar.png'));
    }

    public function testIsSafeUrlAcceptsHostWhenAllResolvedIpsArePublic(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        $updater = new AvatarUpdater(static fn(string $host): array => ['1.1.1.1', '8.8.8.8']);

        self::assertTrue($updater->isSafeUrl('https://cdn.example.com/avatar.png'));
    }

    public function testIsSafeUrlRejectsUrlsWithEmbeddedCredentials(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        $updater = new AvatarUpdater(static fn(string $host): array => ['1.1.1.1']);

        self::assertFalse($updater->isSafeUrl('https://user:pass@cdn.example.com/avatar.png'));
    }

    public function testIsSafeUrlRejectsWhenResolverThrows(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        $updater = new AvatarUpdater(static function (string $host): array {
            throw new \RuntimeException('dns failure');
        });

        self::assertFalse($updater->isSafeUrl('https://cdn.example.com/avatar.png'));
    }

    public function testIsWithinDownloadSizeLimitTreatsUnknownOrInvalidContentLengthAsAllowed(): void
    {
        $updater = new AvatarUpdater();

        self::assertTrue($updater->isWithinDownloadSizeLimit('not-a-url', 1024));
    }

    public function testUpdateRejectsOversizedAvatarPayload(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        $updater = new AvatarUpdater();
        DI::httpClient()->nextResponse = str_repeat('A', (5 * 1024 * 1024) + 1);

        $updater->update(1, 'https://id.example.com/avatar.png');

        self::assertNotEmpty(DI::logger()->warnings);
        self::assertSame('openidconnect: rejected oversized avatar payload', DI::logger()->warnings[array_key_last(DI::logger()->warnings)][0]);
    }

    public function testUpdateFetchFailureLogDoesNotLeakSignedAvatarQueryParameters(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        $updater = new AvatarUpdater(static fn(string $host): array => ['1.1.1.1']);
        DI::httpClient()->nextException = new \RuntimeException('timeout');

        $updater->update(1, 'https://cdn.example.com/avatar.png?token=signed-token-123&email=person@example.test');

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: failed to fetch avatar image', $lastWarning[0]);
        $contextEncoded = json_encode($lastWarning[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('signed-token-123', $contextEncoded);
        self::assertStringNotContainsString('person@example.test', $contextEncoded);
    }
}
