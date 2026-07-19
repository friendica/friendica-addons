<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Account;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Account\AvatarUpdater;
use Friendica\Addon\OpenIdConnect\Account\UserProvisioner;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\User;

final class UserProvisionerTest extends AddonTestCase
{
    private function createService(): UserProvisioner
    {
        return new UserProvisioner(new AccountLinker(), new AvatarUpdater());
    }

    private function flattenLogEntries(array $entries): string
    {
        $flattened = '';
        foreach ($entries as $entry) {
            $flattened .= (string)($entry[0] ?? '');
            $flattened .= json_encode($entry[1] ?? [], JSON_THROW_ON_ERROR);
        }

        return $flattened;
    }

    private function prepareAvatarUpdatePath(string $pictureUrl, int $uid = 5): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://img.example.test/.well-known/openid-configuration');
        DI::httpClient()->nextResponse = 'avatar-bytes';
        DBA::seedContact([
            'id' => 1,
            'uid' => $uid,
            'self' => true,
        ]);

        self::assertStringContainsString('img.example.test', $pictureUrl);
    }

    public function testNormalisesNicknameFromNameThenEmailLocalPart(): void
    {
        $service = $this->createService();
        self::assertSame('already-set', $service->normaliseNickname('already-set', 'Jane Doe', 'jane@example.test'));
        self::assertSame('janedoe', $service->normaliseNickname('', 'Jane Doe', 'jane@example.test'));
        self::assertSame('jane', $service->normaliseNickname('', '', 'jane@example.test'));
    }

    public function testNormalisesNicknameTrimsAndFallsBackToEmptyWhenNameAndEmailPrefixAreUnusable(): void
    {
        $service = $this->createService();

        self::assertSame('a', $service->normaliseNickname('', 'A', 'a@example.test'));
        self::assertSame('', $service->normaliseNickname('', '!!!', '@example.test'));
    }

    public function testResolveUniqueNicknameFindsNextFreeSuffix(): void
    {
        $service = $this->createService();

        $taken = [
            'jane' => true,
            'jane1' => true,
        ];

        $result = $service->resolveUniqueNickname('jane', static fn(string $candidate): bool => isset($taken[$candidate]));

        self::assertSame('jane2', $result);
    }

    public function testDuplicateConstraintExceptionIsRetryable(): void
    {
        $service = $this->createService();

        $retryable = new \RuntimeException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry for key user.nickname');
        $nonRetryable = new \RuntimeException('network timeout while contacting identity provider');

        self::assertTrue($service->isRetryableCreateException($retryable));
        self::assertFalse($service->isRetryableCreateException($nonRetryable));
    }

    public function testFindOrCreateReturnsExistingLinkedUserAndPropagatesEmailChange(): void
    {
        DBA::seedUser([
            'uid' => 5,
            'openid' => 'subject-1',
            'email' => 'old@example.test',
            'nickname' => 'jane',
        ]);

        $service = $this->createService();

        $result = $service->findOrCreate('subject-1', 'new@example.test', 'Jane', 'jane', '');

        self::assertIsArray($result);
        self::assertSame(5, $result['uid']);
        self::assertSame('new@example.test', $result['email']);
        self::assertSame('subject-1', DI::pConfig()->get(5, 'openidconnect', 'oidc_sub'));
        self::assertSame('new@example.test', DI::pConfig()->get(5, 'openidconnect', 'oidc_email'));
    }

    public function testFindOrCreateDoesNotOverwriteLinkedUserEmailWhenNewAddressIsAlreadyTaken(): void
    {
        DBA::seedUser([
            'uid' => 5,
            'openid' => 'subject-1',
            'email' => 'old@example.test',
            'nickname' => 'jane',
        ]);
        DBA::seedUser([
            'uid' => 6,
            'openid' => '',
            'email' => 'taken@example.test',
            'nickname' => 'taken',
        ]);

        $service = $this->createService();

        $result = $service->findOrCreate('subject-1', 'taken@example.test', 'Jane', 'jane', '');

        self::assertIsArray($result);
        self::assertSame(5, $result['uid']);
        self::assertSame('old@example.test', $result['email']);
        self::assertSame('old@example.test', DBA::selectFirst('user', [], ['uid' => 5])['email']);
        self::assertSame('taken@example.test', DBA::selectFirst('user', [], ['uid' => 6])['email']);
        self::assertSame('taken@example.test', DI::pConfig()->get(5, 'openidconnect', 'oidc_email'));
    }

    public function testFindOrCreateKeepsLinkedUserEmailWhenProviderOmitsIt(): void
    {
        DBA::seedUser([
            'uid' => 5,
            'openid' => 'subject-1',
            'email' => 'old@example.test',
            'nickname' => 'jane',
        ]);

        $service = $this->createService();

        $result = $service->findOrCreate('subject-1', '', 'Jane', 'jane', '');

        self::assertIsArray($result);
        self::assertSame(5, $result['uid']);
        self::assertSame('old@example.test', $result['email']);
        self::assertSame('old@example.test', DBA::selectFirst('user', [], ['uid' => 5])['email']);
        self::assertSame('', DI::pConfig()->get(5, 'openidconnect', 'oidc_email'));
    }

    public function testFindOrCreateRejectsEmailMappedToDifferentLinkedSubject(): void
    {
        DBA::seedUser([
            'uid' => 7,
            'openid' => 'different-subject',
            'email' => 'same@example.test',
            'nickname' => 'existing',
        ]);

        $service = $this->createService();

        $result = $service->findOrCreate('incoming-subject', 'same@example.test', 'Incoming', 'incoming', '');

        self::assertNull($result);
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('This account is not linked to your identity provider', DI::sysmsg()->notices[0]);

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: email matches account linked to different sub - REJECTED', $lastWarning[0]);
        $contextEncoded = json_encode($lastWarning[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('same@example.test', $contextEncoded);
        self::assertStringNotContainsString('different-subject', $contextEncoded);
        self::assertStringNotContainsString('incoming-subject', $contextEncoded);
    }

    public function testFindOrCreateAutoLinksUnlinkedAccountByEmailWhenEnabled(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);
        DBA::seedUser([
            'uid' => 9,
            'openid' => '',
            'email' => 'match@example.test',
            'nickname' => 'match',
        ]);

        $service = $this->createService();

        $result = $service->findOrCreate('sub-9', 'match@example.test', 'Match', 'match', '');

        self::assertIsArray($result);
        self::assertSame(9, $result['uid']);
        self::assertSame('sub-9', $result['openid']);
        self::assertSame('sub-9', DI::pConfig()->get(9, 'openidconnect', 'oidc_sub'));
    }

    public function testFindOrCreateRetriesCreateAfterDuplicateExceptionAndEventuallyCreatesUser(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);

        $attempt = 0;
        User::$createHandler = static function (array $fields) use (&$attempt): array {
            $attempt++;
            if ($attempt === 1) {
                throw new \RuntimeException('SQLSTATE[23000]: duplicate key value violates unique constraint');
            }

            return DBA::createUser($fields);
        };

        $service = $this->createService();

        $result = $service->findOrCreate('new-subject', 'new@example.test', 'New User', 'newuser', '');

        self::assertIsArray($result);
        self::assertSame('new-subject', $result['openid']);
        self::assertSame(2, $attempt);
    }

    public function testFindOrCreateReturnsNullWhenNoMatchAndAutoCreateDisabled(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', false);

        $sub = 'sub-private-001';
        $email = 'private@example.test';
        $name = 'Private Name';
        $nickname = 'private-nick';

        $service = $this->createService();

        $result = $service->findOrCreate($sub, $email, $name, $nickname, '');

        self::assertNull($result);
        self::assertNotEmpty(DI::sysmsg()->notices);

        $logBlob = $this->flattenLogEntries(DI::logger()->debugs)
            . $this->flattenLogEntries(DI::logger()->warnings)
            . $this->flattenLogEntries(DI::logger()->errors)
            . $this->flattenLogEntries(DI::logger()->infos);
        self::assertStringNotContainsString($sub, $logBlob);
        self::assertStringNotContainsString($email, $logBlob);
        self::assertStringNotContainsString($name, $logBlob);
        self::assertStringNotContainsString($nickname, $logBlob);
    }

    public function testFindOrCreateHandlesMalformedIdentitySafelyWhenAutoCreateDisabled(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', false);

        $sub = '';
        $email = '';
        $name = "\u{0000}\u{0007}";
        $nickname = '';

        $service = $this->createService();

        $result = $service->findOrCreate($sub, $email, $name, $nickname, '');

        self::assertNull($result);
        self::assertNotEmpty(DI::sysmsg()->notices);
    }

    public function testStoreLinkedUserMetadataCallsAvatarUpdaterWhenPicturePresent(): void
    {
        $picture = 'https://img.example.test/avatar.png';
        $this->prepareAvatarUpdatePath($picture);
        DI::config()->set('openidconnect', 'auto_create_accounts', true);

        DBA::seedUser([
            'uid' => 5,
            'openid' => 'subject-linked',
            'email' => 'linked@example.test',
            'nickname' => 'linked-user',
        ]);

        $service = $this->createService();
        $result = $service->findOrCreate('subject-linked', 'linked@example.test', 'Linked', 'linked-user', $picture);

        self::assertIsArray($result);
        self::assertCount(1, \Friendica\Model\Contact::$avatarUpdates);
    }

    public function testStoreLinkedUserMetadataDoesNotCallAvatarUpdaterWhenPictureEmpty(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);
        DI::httpClient()->nextResponse = 'avatar-bytes';
        DBA::seedContact(['id' => 1, 'uid' => 5, 'self' => true]);

        DBA::seedUser([
            'uid' => 5,
            'openid' => 'subject-linked',
            'email' => 'linked@example.test',
            'nickname' => 'linked-user',
        ]);

        $service = $this->createService();
        $result = $service->findOrCreate('subject-linked', 'linked@example.test', 'Linked', 'linked-user', '');

        self::assertIsArray($result);
        self::assertCount(0, \Friendica\Model\Contact::$avatarUpdates);
        self::assertCount(0, DI::httpClient()->calls);
    }

    public function testAutoLinkExistingUserWithPictureLinksAndUpdatesAvatar(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);
        $picture = 'https://img.example.test/avatar-auto-link.png';
        $this->prepareAvatarUpdatePath($picture);

        DBA::seedUser([
            'uid' => 5,
            'openid' => '',
            'email' => 'link@example.test',
            'nickname' => 'link-user',
        ]);

        $service = $this->createService();
        $result = $service->findOrCreate('sub-link-5', 'link@example.test', 'Link User', 'link-user', $picture);

        self::assertIsArray($result);
        self::assertSame(5, $result['uid']);
        self::assertSame('sub-link-5', $result['openid']);
        self::assertSame('sub-link-5', DI::pConfig()->get(5, 'openidconnect', 'oidc_sub'));
        self::assertCount(1, \Friendica\Model\Contact::$avatarUpdates);
    }

    public function testCreateUserWhenAllowedHandlesNonRetryableFailureAndAvoidsPiiInErrorLogs(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);

        $sub = 'sub-no-echo-001';
        $email = 'hidden@example.test';
        $nickname = 'hidden-nick';
        $name = 'Hidden Name';

        User::$createHandler = static function (): array {
            throw new \RuntimeException('creation failed');
        };

        $service = $this->createService();
        $result = $service->findOrCreate($sub, $email, $name, $nickname, '');

        self::assertNull($result);
        self::assertSame('login?openidconnect_no_auto=1', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);

        self::assertNotEmpty(DI::logger()->errors);
        $lastError = DI::logger()->errors[array_key_last(DI::logger()->errors)];
        self::assertSame('openidconnect: create_user exception', $lastError[0]);
        $errorBlob = (string)$lastError[0] . json_encode($lastError[1] ?? [], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString($sub, $errorBlob);
        self::assertStringNotContainsString($email, $errorBlob);
        self::assertStringNotContainsString($nickname, $errorBlob);
    }

    public function testCreateUserBlankNicknameFallsBackToUserBase(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);

        $service = $this->createService();
        $result = $service->findOrCreate('sub-blank-nick', 'user@example.test', '!!!', '   ', '');

        self::assertIsArray($result);
        self::assertSame('user', $result['nickname']);
    }

    public function testCreateUserHandlesUnicodeAndControlCharactersInName(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);

        $service = $this->createService();
        $result = $service->findOrCreate('sub-unicode', 'u.name@example.test', "bad\x01name<script>\n", '', '');

        self::assertIsArray($result);
        self::assertNotSame('', $result['nickname']);
    }

    public function testCreateUserHandlesMalformedEmailWithoutExploding(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);

        $service = $this->createService();
        $result = $service->findOrCreate('sub-bad-email', 'not-an-email', 'Bad Email', 'badmail', '');

        self::assertIsArray($result);
        self::assertSame('not-an-email', $result['email']);
    }

    public function testCreateUserReconcilesWhenSubGetsLinkedByAnotherProcess(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);

        User::$createHandler = static function (array $fields): array {
            DBA::seedUser([
                'uid' => 99,
                'openid' => $fields['openid'] ?? '',
                'email' => 'reconciled@example.test',
                'nickname' => 'reconciled',
            ]);

            throw new \RuntimeException('transient creation race');
        };

        $service = $this->createService();
        $result = $service->findOrCreate('sub-race', 'race@example.test', 'Race', 'race', '');

        self::assertIsArray($result);
        self::assertSame(99, $result['uid']);
        self::assertSame('sub-race', $result['openid']);
    }

    public function testCreateUserRetryExhaustionFallsBackToNoticeAndNull(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);

        User::$createHandler = static function (): array {
            throw new \RuntimeException('duplicate');
        };

        $service = $this->createService();
        $result = $service->findOrCreate('sub-exhaust', 'exhaust@example.test', 'Exhaust', 'exhaust', '');

        self::assertNull($result);
        self::assertSame('login?openidconnect_no_auto=1', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('Account creation failed', DI::sysmsg()->notices[array_key_last(DI::sysmsg()->notices)]);
    }

    public function testCreateUserResolvesUidFromLinkedSubWhenCreatePayloadMissesUid(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);

        User::$createHandler = static function (array $fields): array {
            DBA::seedUser([
                'uid' => 77,
                'openid' => $fields['openid'] ?? '',
                'email' => $fields['email'] ?? '',
                'nickname' => $fields['nickname'] ?? '',
            ]);

            return ['ok' => true];
        };

        $service = $this->createService();
        $result = $service->findOrCreate('sub-fallback-uid', 'fallback@example.test', 'Fallback', 'fallback', '');

        self::assertIsArray($result);
        self::assertSame(77, $result['uid']);
        self::assertSame('sub-fallback-uid', $result['openid']);
    }

    public function testCreateUserAbortsWhenResolvedUidIsZero(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);

        User::$createHandler = static fn(): array => ['uid' => 0];

        $service = $this->createService();
        $result = $service->findOrCreate('sub-uid-zero', 'uidzero@example.test', 'Uid Zero', 'uidzero', '');

        self::assertNull($result);
        self::assertNotEmpty(DI::logger()->errors);
        $lastError = DI::logger()->errors[array_key_last(DI::logger()->errors)];
        self::assertSame('openidconnect: uid=0 after User::create — aborting account creation', $lastError[0]);
    }

    public function testCreateUserNonRetryableExceptionBubblesUnmodified(): void
    {
        $service = $this->createService();

        $thrown = new \RuntimeException('upstream unavailable');
        User::$createHandler = static function () use ($thrown): array {
            throw $thrown;
        };

        $method = new \ReflectionMethod(UserProvisioner::class, 'createUser');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('upstream unavailable');

        $method->invoke($service, 'sub-exception', 'exception@example.test', 'Name', 'nick', '');
    }

    public function testCreateUserWithPictureTriggersAvatarUpdaterBranch(): void
    {
        DI::config()->set('openidconnect', 'auto_create_accounts', true);
        $picture = 'https://img.example.test/avatar-create.png';
        $this->prepareAvatarUpdatePath($picture, 1);

        $service = $this->createService();
        $result = $service->findOrCreate('sub-picture', 'picture@example.test', 'Picture User', 'picuser', $picture);

        self::assertIsArray($result);
        self::assertNotEmpty(\Friendica\Model\Contact::$avatarUpdates);
    }

    public function testResolveUniqueNicknameUsesUserForBlankBase(): void
    {
        $service = $this->createService();

        $result = $service->resolveUniqueNickname('   ', static fn(string $candidate): bool => $candidate !== 'user');

        self::assertSame('user', $result);
    }

    public function testResolveUniqueNicknameThrowsWhenAllCandidatesExhausted(): void
    {
        $service = $this->createService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not generate a unique nickname for: user');

        $service->resolveUniqueNickname('user', static fn(string $candidate): bool => str_starts_with($candidate, 'user'));
    }

    public function testRunInTransactionHappyPathExecutesOperationAndReturnsResult(): void
    {
        $service = $this->createService();

        $method = new \ReflectionMethod(UserProvisioner::class, 'runInTransaction');

        $result = $method->invoke($service, static fn(): array => ['ok' => true]);

        self::assertSame(['ok' => true], $result);
    }
}
