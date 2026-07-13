<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Account;

use Friendica\Addon\OpenIdConnect\Account\AvatarUpdater;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class AvatarUpdaterTest extends AddonTestCase
{
    public function testRejectsNonUrlAndNonHttpsThirdPartyAvatarUrls(): void
    {
        self::assertFalse((new AvatarUpdater())->isSafeUrl('not a url'));
        self::assertFalse((new AvatarUpdater())->isSafeUrl('http://cdn.example.test/avatar.png'));
    }
}
