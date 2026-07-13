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
}
