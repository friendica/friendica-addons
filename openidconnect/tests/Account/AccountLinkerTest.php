<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Account;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\Database\DBA;
use Friendica\DI;

final class AccountLinkerTest extends AddonTestCase
{
    public function testRefusesEmptySubject(): void
    {
        self::assertFalse((new AccountLinker())->link(42, '', 'u@example.test', 'user'));
    }

    public function testRejectsAlreadyLinkedSubjectWithoutLoggingRawSubjectOrEmail(): void
    {
        DBA::seedUser([
            'uid' => 7,
            'openid' => 'subject-123',
            'email' => 'owner@example.test',
            'nickname' => 'owner',
        ]);

        self::assertFalse((new AccountLinker())->link(42, 'subject-123', 'person@example.test', 'sensitive-nick'));

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect_link_user: subject already linked to another uid', $lastWarning[0]);
        $contextEncoded = json_encode($lastWarning[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('subject-123', $contextEncoded);
        self::assertStringNotContainsString('person@example.test', $contextEncoded);
        self::assertStringNotContainsString('sensitive-nick', $contextEncoded);
    }
}
