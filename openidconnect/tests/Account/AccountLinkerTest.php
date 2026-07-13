<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Account;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class AccountLinkerTest extends AddonTestCase
{
    public function testRefusesEmptySubject(): void
    {
        self::assertFalse((new AccountLinker())->link(42, '', 'u@example.test', 'user'));
    }
}
