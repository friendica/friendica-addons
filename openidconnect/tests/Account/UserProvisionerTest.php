<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Account;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Account\AvatarUpdater;
use Friendica\Addon\OpenIdConnect\Account\UserProvisioner;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class UserProvisionerTest extends AddonTestCase
{
    public function testNormalisesNicknameFromNameThenEmailLocalPart(): void
    {
        $service = new UserProvisioner(new AccountLinker(), new AvatarUpdater());
        self::assertSame('janedoe', $service->normaliseNickname('', 'Jane Doe', 'jane@example.test'));
        self::assertSame('jane', $service->normaliseNickname('', '', 'jane@example.test'));
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
}
