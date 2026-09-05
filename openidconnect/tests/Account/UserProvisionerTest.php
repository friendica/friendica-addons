<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Account;

use Friendica\Addon\OpenIdConnect\Account\UserProvisioner;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Database\Database;
use Friendica\Network\HTTPClient\Capability\ICanSendHttpRequests;

final class UserProvisionerTest extends DiTestCase
{
    public function testNormaliseNicknameKeepsExplicitNickname(): void
    {
        $this->initDi();

        self::assertSame('given', (new UserProvisioner())->normaliseNickname('given', 'Some Name', 'user@example.com'));
    }

    public function testNormaliseNicknameDerivesFromNameWhenNicknameMissing(): void
    {
        $this->initDi();

        self::assertSame('somename', (new UserProvisioner())->normaliseNickname('', 'Some Name', 'user@example.com'));
    }

    public function testNormaliseNicknameFallsBackToEmailLocalPartWhenNameIsTooShort(): void
    {
        $this->initDi();

        self::assertSame('user', (new UserProvisioner())->normaliseNickname('', 'A', 'user@example.com'));
    }

    public function testFindOrCreateRefreshesLinkedUserAndUpdatesMetadata(): void
    {
        $httpClient = $this->mockHttpClient();
        $httpClient->method('fetch')->willReturn('fake-image-bytes');
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturnMap([
            ['user', [], ['openid' => 'sub-123'], [], ['uid' => 42, 'email' => 'old@example.com']],
            ['contact', ['id'], ['uid' => 42, 'self' => true], [], false],
        ]);
        $this->initDi([Database::class => $dba, ICanSendHttpRequests::class => $httpClient]);
        $this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');

        $result = (new UserProvisioner())->findOrCreate('sub-123', 'old@example.com', 'Some Name', 'somename', 'https://idp.example/pic.png');

        self::assertSame(42, $result['uid']);
        self::assertSame('sub-123', $this->pConfig->get(42, 'openidconnect', 'oidc_sub', ''));
    }

    public function testFindOrCreatePropagatesEmailChangeForLinkedUser(): void
    {
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturnMap([
            ['user', [], ['openid' => 'sub-123'], [], ['uid' => 42, 'email' => 'old@example.com']],
        ]);
        $dba->method('exists')->willReturn(false);
        $dba->expects(self::once())->method('update')->with('user', ['email' => 'new@example.com'], ['uid' => 42])->willReturn(true);
        $this->initDi([Database::class => $dba]);

        $result = (new UserProvisioner())->findOrCreate('sub-123', 'new@example.com', 'Some Name', 'somename', '');

        self::assertSame('new@example.com', $result['email']);
    }

    public function testFindOrCreateKeepsOldEmailWhenNewEmailIsAlreadyTaken(): void
    {
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturnMap([
            ['user', [], ['openid' => 'sub-123'], [], ['uid' => 42, 'email' => 'old@example.com']],
        ]);
        $dba->method('exists')->willReturn(true);
        $dba->expects(self::never())->method('update');
        $this->initDi([Database::class => $dba]);

        $result = (new UserProvisioner())->findOrCreate('sub-123', 'new@example.com', 'Some Name', 'somename', '');

        self::assertSame('old@example.com', $result['email']);
    }

    public function testFindOrCreateAutoLinksExistingUnlinkedAccountByEmail(): void
    {
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturnMap([
            ['user', [], ['openid' => 'sub-123'], [], false],
            ['user', ['uid', 'openid'], ['email' => 'user@example.com'], [], ['uid' => 42, 'openid' => '']],
            ['user', [], ['uid' => 42], [], ['uid' => 42, 'email' => 'user@example.com', 'openid' => 'sub-123']],
        ]);
        $dba->method('update')->willReturn(true);
        $this->initDi([Database::class => $dba]);
        $this->config->set('openidconnect', 'auto_create_accounts', true);

        $result = (new UserProvisioner())->findOrCreate('sub-123', 'user@example.com', 'Some Name', 'somename', '');

        self::assertSame(42, $result['uid']);
        self::assertSame('sub-123', $this->pConfig->get(42, 'openidconnect', 'oidc_sub', ''));
    }

    public function testFindOrCreateRejectsEmailMatchAlreadyLinkedToDifferentSub(): void
    {
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturnMap([
            ['user', [], ['openid' => 'sub-123'], [], false],
            ['user', ['uid', 'openid'], ['email' => 'user@example.com'], [], ['uid' => 42, 'openid' => 'other-sub']],
        ]);
        $this->initDi([Database::class => $dba]);

        $result = (new UserProvisioner())->findOrCreate('sub-123', 'user@example.com', 'Some Name', 'somename', '');

        self::assertNull($result);
        self::assertSame(
            ['OpenID Connect: This account is not linked to your identity provider. Please link your account in the settings or contact the administrator.'],
            $this->sysmsg->getNotices()
        );
    }

    public function testFindOrCreateRefusesWhenNoMatchAndAutoCreateIsDisabled(): void
    {
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturn(false);
        $this->initDi([Database::class => $dba]);

        $result = (new UserProvisioner())->findOrCreate('sub-123', 'new@example.com', 'Some Name', '', '');

        self::assertNull($result);
        self::assertSame(
            ['OpenID Connect: No matching account found and registration is not available. Please contact the administrator.'],
            $this->sysmsg->getNotices()
        );
    }

    public function testResolveUniqueNicknameAppendsCounterUntilFree(): void
    {
        $this->initDi();
        $taken = ['someuser', 'someuser1'];

        $result = (new UserProvisioner())->resolveUniqueNickname('someuser', static fn (string $candidate): bool => in_array($candidate, $taken, true));

        self::assertSame('someuser2', $result);
    }

    public function testResolveUniqueNicknameFallsBackToUserWhenBaseIsEmpty(): void
    {
        $this->initDi();

        $result = (new UserProvisioner())->resolveUniqueNickname('', static fn (string $candidate): bool => false);

        self::assertSame('user', $result);
    }

    public function testIsRetryableCreateExceptionRecognisesDuplicateKeyErrors(): void
    {
        $this->initDi();
        $provisioner = new UserProvisioner();

        self::assertTrue($provisioner->isRetryableCreateException(new \RuntimeException('Duplicate entry for key nickname')));
        self::assertTrue($provisioner->isRetryableCreateException(new \RuntimeException('SQLSTATE[23505] unique violation')));
        self::assertFalse($provisioner->isRetryableCreateException(new \RuntimeException('connection refused')));
    }
}
