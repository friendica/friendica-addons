<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Account;

use Friendica\Addon\OpenIdConnect\Account\AvatarUpdater;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Database\Database;
use Friendica\Network\HTTPClient\Capability\ICanSendHttpRequests;

final class AvatarUpdaterTest extends DiTestCase
{
    public function testIsSafeUrlRejectsMalformedUrl(): void
    {
        $this->initDi();

        self::assertFalse((new AvatarUpdater())->isSafeUrl('not-a-url'));
    }

    public function testIsSafeUrlRejectsCredentialsInUrl(): void
    {
        $this->initDi();

        self::assertFalse((new AvatarUpdater())->isSafeUrl('https://user:pass@example.com/avatar.png'));
    }

    public function testIsSafeUrlTrustsConfiguredIdpHostEvenOverPlainHttp(): void
    {
        $this->initDi();
        $this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');

        self::assertTrue((new AvatarUpdater())->isSafeUrl('http://idp.example/avatar.png'));
    }

    public function testIsSafeUrlRejectsNonHttpsForUnrelatedHost(): void
    {
        $this->initDi();

        self::assertFalse((new AvatarUpdater())->isSafeUrl('http://example.com/avatar.png'));
    }

    public function testIsSafeUrlRejectsHostResolvingToPrivateIp(): void
    {
        $this->initDi();
        $updater = new AvatarUpdater(static fn (string $host): array => ['10.0.0.5']);

        self::assertFalse($updater->isSafeUrl('https://example.com/avatar.png'));
    }

    public function testIsSafeUrlAcceptsHostResolvingToPublicIp(): void
    {
        $this->initDi();
        $updater = new AvatarUpdater(static fn (string $host): array => ['203.0.113.10']);

        self::assertTrue($updater->isSafeUrl('https://example.com/avatar.png'));
    }

    public function testIsSafeUrlRejectsHostThatFailsToResolve(): void
    {
        $this->initDi();
        $updater = new AvatarUpdater(static fn (string $host): array => []);

        self::assertFalse($updater->isSafeUrl('https://example.com/avatar.png'));
    }

    public function testIsWithinDownloadSizeLimitAllowsUnknownContentLength(): void
    {
        $this->initDi();

        self::assertTrue((new AvatarUpdater())->isWithinDownloadSizeLimit('https://example.com/does-not-exist.png', 1024));
    }

    public function testDeleteTemporaryFileRefusesPathOutsideTempDir(): void
    {
        $this->initDi();
        $outsideFile = getcwd() . '/avatar_should_not_be_deleted.tmp';
        file_put_contents($outsideFile, 'x');

        try {
            (new AvatarUpdater())->deleteTemporaryFile($outsideFile, 42);

            self::assertFileExists($outsideFile);
        } finally {
            unlink($outsideFile);
        }
    }

    public function testDeleteTemporaryFileRefusesUnexpectedFilename(): void
    {
        $this->initDi();
        $tempFile = tempnam(sys_get_temp_dir(), 'not_an_avatar_');

        try {
            (new AvatarUpdater())->deleteTemporaryFile($tempFile, 42);

            self::assertFileExists($tempFile);
        } finally {
            unlink($tempFile);
        }
    }

    public function testDeleteTemporaryFileRemovesMatchingAvatarFile(): void
    {
        $this->initDi();
        $tempFile = tempnam(sys_get_temp_dir(), 'avatar_');

        (new AvatarUpdater())->deleteTemporaryFile($tempFile, 42);

        self::assertFileDoesNotExist($tempFile);
    }

    public function testUpdateDoesNothingForEmptyUrl(): void
    {
        $httpClient = $this->mockHttpClient();
        $httpClient->expects(self::never())->method('fetch');
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);

        (new AvatarUpdater())->update(42, '');
    }

    public function testUpdateDoesNothingForUnsafeUrl(): void
    {
        $httpClient = $this->mockHttpClient();
        $httpClient->expects(self::never())->method('fetch');
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);

        (new AvatarUpdater())->update(42, 'not-a-url');
    }

    public function testUpdateSkipsWhenFetchThrows(): void
    {
        $httpClient = $this->mockHttpClient();
        $httpClient->method('fetch')->willThrowException(new \RuntimeException('connection reset'));
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');

        (new AvatarUpdater())->update(42, 'https://idp.example/avatar.png');

        self::assertTrue($this->logger->hasRecordMatching('warning', 'failed to fetch avatar image'));
    }

    public function testUpdateSkipsWhenSelfContactIsNotFound(): void
    {
        $httpClient = $this->mockHttpClient();
        $httpClient->method('fetch')->willReturn('fake-image-bytes');
        $dba = $this->mockDba();
        $dba->expects(self::once())->method('selectFirst')->with('contact', ['id'], ['uid' => 42, 'self' => true])->willReturn(false);
        $this->initDi([ICanSendHttpRequests::class => $httpClient, Database::class => $dba]);
        $this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');

        (new AvatarUpdater())->update(42, 'https://idp.example/avatar.png');
    }
}
