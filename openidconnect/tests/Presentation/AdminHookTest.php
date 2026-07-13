<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Presentation\AdminHook;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class AdminHookTest extends AddonTestCase
{
    public function testButtonTextIsEditableEvenWhenItsSourceIsStatic(): void
    {
        DI::config()->getCache()->setSource('openidconnect', 'button_text', 5);
        self::assertFalse((new AdminHook())->isReadOnly('button_text'));
    }

    public function testAllOtherStaticConfigurationIsReadOnly(): void
    {
        DI::config()->getCache()->setSource('openidconnect', 'client_id', 5);
        self::assertTrue((new AdminHook())->isReadOnly('client_id'));
    }
}
