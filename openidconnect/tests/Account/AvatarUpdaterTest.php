<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Account;

use Friendica\Addon\OpenIdConnect\Account\AvatarUpdater;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;

final class AvatarUpdaterTest extends AddonTestCase
{
    /** @var array<int, string> */
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        unset(
            $GLOBALS['__test_get_headers_result'],
            $GLOBALS['__test_file_put_contents_fail'],
            $GLOBALS['__test_unlink_fail'],
            $GLOBALS['forceTempnamFalse'],
            $GLOBALS['forceRealpathFalse'],
            $GLOBALS['forceUnlinkThrow'],
            $GLOBALS['forceFilePutContentsFalse']
        );

        foreach ($this->temporaryFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $this->temporaryFiles = [];

        parent::tearDown();
    }

    private function rememberTempFile(string $path): void
    {
        $this->temporaryFiles[] = $path;
    }

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

    public function testUpdateSuccessAppliesAvatarAndCleansTemporaryFile(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'http://idp.local:9000/.well-known/openid-configuration');
        DBA::seedContact(['id' => 42, 'uid' => 321, 'self' => true]);

        $updater = new AvatarUpdater();
        DI::httpClient()->nextResponse = str_repeat('G', 512);

        $updater->update(321, 'http://idp.local:9000/media/avatar.jpg');

        self::assertCount(1, Contact::$avatarUpdates);
        self::assertSame(42, Contact::$avatarUpdates[0][0]);
        self::assertFalse(is_file(Contact::$avatarUpdates[0][1]));
        self::assertSame([], DI::logger()->warnings);
    }

    public function testUpdateLogsWarningWhenTemporaryAvatarFileWriteFails(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'http://idp.local:9000/.well-known/openid-configuration');

        $updater = new AvatarUpdater();
        DI::httpClient()->nextResponse = str_repeat('P', 256);
        $GLOBALS['__test_file_put_contents_fail'] = true;

        $updater->update(7, 'http://idp.local:9000/media/photo.jpg');

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: failed to write avatar temp file', $lastWarning[0]);
        self::assertSame(7, $lastWarning[1]['uid']);
        self::assertSame([], Contact::$avatarUpdates);
    }

    public function testDeleteTemporaryFileReturnsForNonExistingPath(): void
    {
        $updater = new AvatarUpdater();

        $updater->deleteTemporaryFile('/tmp/definitely-not-existing-openidconnect-avatar.tmp', 1);

        self::assertSame([], DI::logger()->warnings);
    }

    public function testDeleteTemporaryFileLogsWhenUnlinkFails(): void
    {
        $updater = new AvatarUpdater();
        $tempFile = tempnam(sys_get_temp_dir(), 'avatar_');
        self::assertNotFalse($tempFile);

        $GLOBALS['__test_unlink_fail'] = true;

        $updater->deleteTemporaryFile($tempFile, 11);

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: failed to unlink avatar temp file', $lastWarning[0]);
        self::assertSame(11, $lastWarning[1]['uid']);
        self::assertTrue(is_file($tempFile));

        unlink($tempFile);
    }

    public function testDeleteTemporaryFileRealpathFailureLogs(): void
    {
        $updater = new AvatarUpdater();
        $tempFile = tempnam(sys_get_temp_dir(), 'avatar_');
        self::assertNotFalse($tempFile);
        $this->rememberTempFile($tempFile);

        $GLOBALS['forceRealpathFalse'] = true;

        $updater->deleteTemporaryFile($tempFile, 55);

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: failed to resolve temp avatar path for cleanup', $lastWarning[0]);
        self::assertSame(55, $lastWarning[1]['uid']);
    }

    public function testDeleteTemporaryFileOutsideTempDirLogs(): void
    {
        $updater = new AvatarUpdater();

        $outsideFile = __DIR__ . '/../../composer.json';
        self::assertTrue(is_file($outsideFile));

        $updater->deleteTemporaryFile($outsideFile, 56);

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: refused to unlink avatar temp file outside temp dir', $lastWarning[0]);
        self::assertSame(56, $lastWarning[1]['uid']);
    }

    public function testDeleteTemporaryFileWrongPrefixLogs(): void
    {
        $updater = new AvatarUpdater();
        $tempFile = tempnam(sys_get_temp_dir(), 'wrongprefix_');
        self::assertNotFalse($tempFile);
        $this->rememberTempFile($tempFile);

        $updater->deleteTemporaryFile($tempFile, 57);

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: refused to unlink unexpected temp avatar filename', $lastWarning[0]);
        self::assertSame(57, $lastWarning[1]['uid']);
    }

    public function testDeleteTemporaryFileUnlinkExceptionLogs(): void
    {
        $updater = new AvatarUpdater();
        $tempFile = tempnam(sys_get_temp_dir(), 'avatar_');
        self::assertNotFalse($tempFile);
        $this->rememberTempFile($tempFile);

        $GLOBALS['forceUnlinkThrow'] = true;

        $updater->deleteTemporaryFile($tempFile, 58);

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: exception while unlinking avatar temp file', $lastWarning[0]);
        self::assertSame(58, $lastWarning[1]['uid']);
    }

    public function testWriteAvatarPayloadToTempFileReturnsFalseWhenTempnamFails(): void
    {
        $updater = new AvatarUpdater();
        $ref = new \ReflectionMethod(AvatarUpdater::class, 'writeAvatarPayloadToTempFile');

        $GLOBALS['forceTempnamFalse'] = true;

        $result = $ref->invoke($updater, 99, 'bytes');

        self::assertNull($result);
        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: failed to allocate temporary avatar file', $lastWarning[0]);
        self::assertSame(99, $lastWarning[1]['uid']);
    }

    public function testWriteAvatarPayloadToTempFileReturnsFalseWhenFilePutContentsFails(): void
    {
        $updater = new AvatarUpdater();
        $ref = new \ReflectionMethod(AvatarUpdater::class, 'writeAvatarPayloadToTempFile');

        $before = glob(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'avatar_*') ?: [];

        $GLOBALS['forceFilePutContentsFalse'] = true;

        $result = $ref->invoke($updater, 100, 'bytes');

        $after = glob(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'avatar_*') ?: [];

        self::assertNull($result);
        self::assertNotEmpty(DI::logger()->warnings);
        self::assertSame('openidconnect: failed to write avatar temp file', DI::logger()->warnings[0][0]);
        self::assertSame($before, $after);
    }

    public function testSanitizeSensitiveUrlWithMalformedUrlReturnsRedacted(): void
    {
        $updater = new AvatarUpdater();
        $ref = new \ReflectionMethod(AvatarUpdater::class, 'sanitizeSensitiveUrl');

        $value = $ref->invoke($updater, 'http://:80');

        self::assertSame('[redacted-url]', $value);
    }

    public function testSanitizeSensitiveUrlStripsCredentials(): void
    {
        $updater = new AvatarUpdater();
        $ref = new \ReflectionMethod(AvatarUpdater::class, 'sanitizeSensitiveUrl');

        $sanitized = $ref->invoke($updater, 'https://user:secret@example.com/avatar.jpg?token=abc123');

        self::assertStringNotContainsString('user', $sanitized);
        self::assertStringNotContainsString('secret', $sanitized);
        self::assertStringNotContainsString('abc123', $sanitized);
    }

    public function testPassesAvatarUrlGuardsRejectsOversizedContentLength(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        $updater = new AvatarUpdater(static fn(string $host): array => ['1.1.1.1']);
        $ref = new \ReflectionMethod(AvatarUpdater::class, 'passesAvatarUrlGuards');

        $GLOBALS['__test_get_headers_result'] = ['Content-Length' => (string)((5 * 1024 * 1024) + 1)];

        $allowed = $ref->invoke($updater, 77, 'https://cdn.example.com/avatar.png');

        self::assertFalse($allowed);
        self::assertSame([], DI::httpClient()->calls);
        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: rejected avatar URL with oversized content-length', $lastWarning[0]);
    }

    public function testUpdateLogsWhenSelectingSelfContactThrows(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'http://idp.local:9000/.well-known/openid-configuration');

        DBA::$nextSelectException = new \RuntimeException('db exploded');
        $updater = new AvatarUpdater();
        DI::httpClient()->nextResponse = 'avatar-bytes';

        $updater->update(13, 'http://idp.local:9000/media/avatar.jpg');

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('Failed to update avatar', $lastWarning[0]);
        self::assertSame(13, $lastWarning[1]['uid']);
        self::assertStringContainsString('db exploded', (string)$lastWarning[1]['exception']);
    }

    public function testIsSafeUrlRejectsWhenResolverReturnsEmptyArray(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        $updater = new AvatarUpdater(static fn(string $host): array => []);

        self::assertFalse($updater->isSafeUrl('https://cdn.example.com/avatar.png'));
    }

    public function testIsWithinDownloadSizeLimitHandlesArrayLowercaseHeader(): void
    {
        $updater = new AvatarUpdater();

        $GLOBALS['__test_get_headers_result'] = ['content-length' => ['42', '2048']];
        self::assertFalse($updater->isWithinDownloadSizeLimit('https://cdn.example.com/avatar.png', 1024));
    }

    public function testIsWithinDownloadSizeLimitTreatsMissingOrZeroHeaderAsAllowed(): void
    {
        $updater = new AvatarUpdater();

        $GLOBALS['__test_get_headers_result'] = [];
        self::assertTrue($updater->isWithinDownloadSizeLimit('https://cdn.example.com/avatar.png', 1024));

        $GLOBALS['__test_get_headers_result'] = ['Content-Length' => '0'];
        self::assertTrue($updater->isWithinDownloadSizeLimit('https://cdn.example.com/avatar.png', 1024));
    }

    public function testUpdateUnsafeUrlWithMalformedInputUsesRedactedPlaceholder(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        $updater = new AvatarUpdater();
        $updater->update(22, '://broken-url?token=abc');

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: rejected unsafe picture URL', $lastWarning[0]);
        self::assertStringNotContainsString('token=abc', $lastWarning[1]['url']);
    }

    public function testUpdateReturnsEarlyForEmptyUrlWithoutNetworkCall(): void
    {
        $updater = new AvatarUpdater();

        $updater->update(1, '');

        self::assertSame([], DI::httpClient()->calls);
        self::assertSame([], DI::logger()->warnings);
    }

    public function testUpdateRejectsEmptyFetchedAvatarPayload(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        $updater = new AvatarUpdater(static fn(string $host): array => ['1.1.1.1']);
        DI::httpClient()->nextResponse = '';

        $updater->update(99, 'https://cdn.example.com/avatar.png?email=user@example.test');

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: avatar fetch returned empty payload', $lastWarning[0]);
        $contextEncoded = json_encode($lastWarning[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('user@example.test', $contextEncoded);
    }

    public function testIsSafeUrlRejectsWhenResolverReturnsEmptyIp(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        $updater = new AvatarUpdater(static fn(string $host): array => ['']);

        self::assertFalse($updater->isSafeUrl('https://cdn.example.com/avatar.png'));
    }

    public function testUpdateFetchFailureLogRedactsMalformedPiiLikeQueryValues(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

        $updater = new AvatarUpdater(static fn(string $host): array => ['1.1.1.1']);
        DI::httpClient()->nextException = new \RuntimeException('upstream timeout');

        $updater->update(7, 'https://cdn.example.com/avatar.png?email=foo%40bar.example&token=abc123&sub=user-123');

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: failed to fetch avatar image', $lastWarning[0]);
        $contextEncoded = json_encode($lastWarning[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('foo@bar.example', $contextEncoded);
        self::assertStringNotContainsString('abc123', $contextEncoded);
        self::assertStringNotContainsString('user-123', $contextEncoded);
    }
}
