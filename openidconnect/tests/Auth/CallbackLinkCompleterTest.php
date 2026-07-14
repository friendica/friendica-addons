<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Auth\CallbackLinkCompleter;
use Friendica\Addon\OpenIdConnect\Auth\SessionFunctionSpy;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class CallbackLinkCompleterTest extends AddonTestCase
{
    public function testCompleteClosesSessionAfterTokenWriteBeforeRedirect(): void
    {
        DI::userSession()->setLocalUserId(7);

        $tokens = [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
        ];

        (new CallbackLinkCompleter(new AccountLinker()))->complete(
            'sub-1',
            'person@example.test',
            'nick',
            'settings/account',
            $tokens
        );

        self::assertSame($tokens, DI::session()->get('openidconnect_tokens'));
        self::assertTrue(SessionFunctionSpy::$sessionWriteCloseCalled);
        self::assertSame('settings/account', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->infos);
    }
}
