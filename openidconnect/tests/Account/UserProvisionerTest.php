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
    public function testNormalisesNicknameFromNameThenEmailLocalPart(): void
    {
        $service = new UserProvisioner(new AccountLinker(), new AvatarUpdater());
        self::assertSame('janedoe', $service->normaliseNickname('', 'Jane Doe', 'jane@example.test'));
        self::assertSame('jane', $service->normaliseNickname('', '', 'jane@example.test'));
    }

    public function testNormalisesNicknameTrimsAndFallsBackToEmptyWhenNameAndEmailPrefixAreUnusable(): void
    {
        $service = new UserProvisioner(new AccountLinker(), new AvatarUpdater());

        self::assertSame('a', $service->normaliseNickname('', 'A', 'a@example.test'));
        self::assertSame('', $service->normaliseNickname('', '!!!', '@example.test'));
    }

    public function testResolveUniqueNicknameFindsNextFreeSuffix(): void
    {
        $service = new UserProvisioner(new AccountLinker(), new AvatarUpdater());

        $taken = [
            'jane' => true,
            'jane1' => true,
        ];

        $result = $service->resolveUniqueNickname('jane', static fn(string $candidate): bool => isset($taken[$candidate]));

        self::assertSame('jane2', $result);
    }

    public function testDuplicateConstraintExceptionIsRetryable(): void
    {
        $service = new UserProvisioner(new AccountLinker(), new AvatarUpdater());

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

        $service = new UserProvisioner(new AccountLinker(), new AvatarUpdater());

        $result = $service->findOrCreate('subject-1', 'new@example.test', 'Jane', 'jane', '');

        self::assertIsArray($result);
        self::assertSame(5, $result['uid']);
        self::assertSame('new@example.test', $result['email']);
        self::assertSame('subject-1', DI::pConfig()->get(5, 'openidconnect', 'oidc_sub'));
        self::assertSame('new@example.test', DI::pConfig()->get(5, 'openidconnect', 'oidc_email'));
    }

    public function testFindOrCreateRejectsEmailMappedToDifferentLinkedSubject(): void
    {
        DBA::seedUser([
            'uid' => 7,
            'openid' => 'different-subject',
            'email' => 'same@example.test',
            'nickname' => 'existing',
        ]);

        $service = new UserProvisioner(new AccountLinker(), new AvatarUpdater());

        $result = $service->findOrCreate('incoming-subject', 'same@example.test', 'Incoming', 'incoming', '');

        self::assertNull($result);
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('This account is not linked to your identity provider', DI::sysmsg()->notices[0]);
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

        $service = new UserProvisioner(new AccountLinker(), new AvatarUpdater());

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

        $service = new UserProvisioner(new AccountLinker(), new AvatarUpdater());

        $result = $service->findOrCreate('new-subject', 'new@example.test', 'New User', 'newuser', '');

        self::assertIsArray($result);
        self::assertSame('new-subject', $result['openid']);
        self::assertSame(2, $attempt);
    }
}
