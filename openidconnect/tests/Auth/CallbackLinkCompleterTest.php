<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Auth\CallbackLinkCompleter;
use Friendica\Addon\OpenIdConnect\Auth\SessionFunctionSpy;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\Database\DBA;
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

    public function testCompleteRejectsForeignLinkedSubjectWithoutLoggingRawSubjectOrEmail(): void
    {
        DI::userSession()->setLocalUserId(7);
        DBA::seedUser([
            'uid' => 99,
            'openid' => 'subject-123',
            'email' => 'owner@example.test',
            'nickname' => 'owner',
        ]);

        (new CallbackLinkCompleter(new AccountLinker()))->complete(
            'subject-123',
            'person@example.test',
            'sensitive-nick',
            'settings/account',
            ['access_token' => 'access-token']
        );

        self::assertSame('settings/account', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: refusing to link subject already linked to another account', $lastWarning[0]);
        $contextEncoded = json_encode($lastWarning[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('subject-123', $contextEncoded);
        self::assertStringNotContainsString('person@example.test', $contextEncoded);
        self::assertStringNotContainsString('sensitive-nick', $contextEncoded);
    }
}
