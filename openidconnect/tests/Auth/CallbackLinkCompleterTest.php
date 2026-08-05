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
        $returnPath = 'settings/account';
        $ownerLookup = fn(string $sub): ?array => null;

        $accountLinker = $this->createMock(AccountLinker::class);
        $accountLinker
            ->expects(self::once())
            ->method('link')
            ->with(7, 'sub-1', 'person@example.test', 'nick')
            ->willReturn(true);

        $tokens = [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
        ];

        (new CallbackLinkCompleter($accountLinker, $ownerLookup))->complete(
            'sub-1',
            'person@example.test',
            'nick',
            $returnPath,
            $tokens
        );

        self::assertSame($tokens, DI::session()->get('openidconnect_tokens'));
        self::assertTrue(SessionFunctionSpy::$sessionWriteCloseCalled);
        self::assertSame($returnPath, DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->infos);
    }

    public function testCompleteRejectsForeignLinkedSubjectWithoutLoggingRawSubjectOrEmail(): void
    {
        DI::userSession()->setLocalUserId(7);
        $ownerLookup = fn(string $sub): ?array => ['uid' => 99];

        $accountLinker = $this->createMock(AccountLinker::class);
        $accountLinker
            ->expects(self::never())
            ->method('link');

        (new CallbackLinkCompleter($accountLinker, $ownerLookup))->complete(
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
        self::assertStringNotContainsString('access-token', $contextEncoded);
    }

    public function testCompleteRedirectsToLoginWhenUnauthenticated(): void
    {
        $ownerLookup = fn(string $sub): ?array => null;

        $accountLinker = $this->createMock(AccountLinker::class);
        $accountLinker
            ->expects(self::never())
            ->method('link');

        (new CallbackLinkCompleter($accountLinker, $ownerLookup))->complete(
            'subject-123',
            'person@example.test',
            'nick',
            'settings/account',
            ['access_token' => 'access-token']
        );

        self::assertSame('login', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertSame([], DI::logger()->warnings);
    }

    public function testCompleteRedirectsToSettingsWhenSubjectMissing(): void
    {
        DI::userSession()->setLocalUserId(7);

        $accountLinker = $this->createMock(AccountLinker::class);
        $accountLinker
            ->expects(self::never())
            ->method('link');

        (new CallbackLinkCompleter($accountLinker))->complete(
            '',
            'person@example.test',
            'nick',
            'settings/account',
            ['access_token' => 'access-token']
        );

        self::assertSame('settings/account', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
    }

    public function testCompleteAllowsSubjectAlreadyLinkedToCurrentUser(): void
    {
        DI::userSession()->setLocalUserId(7);
        $returnPath = 'settings/account';
        $ownerLookup = fn(string $sub): ?array => ['uid' => 7];

        $accountLinker = $this->createMock(AccountLinker::class);
        $accountLinker
            ->expects(self::once())
            ->method('link')
            ->with(7, 'subject-123', 'person@example.test', 'nick')
            ->willReturn(true);

        $tokens = [
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
        ];

        (new CallbackLinkCompleter($accountLinker, $ownerLookup))->complete(
            'subject-123',
            'person@example.test',
            'nick',
            $returnPath,
            $tokens
        );

        self::assertSame($returnPath, DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->infos);
        self::assertSame($tokens, DI::session()->get('openidconnect_tokens'));
        self::assertTrue(SessionFunctionSpy::$sessionWriteCloseCalled);
    }

    public function testCompleteRedirectsToSettingsWhenAccountLinkerReturnsFalse(): void
    {
        DI::userSession()->setLocalUserId(7);
        $ownerLookup = fn(string $sub): ?array => null;

        $accountLinker = $this->createMock(AccountLinker::class);
        $accountLinker
            ->expects(self::once())
            ->method('link')
            ->with(7, 'subject-123', 'person@example.test', 'nick')
            ->willReturn(false);

        (new CallbackLinkCompleter($accountLinker, $ownerLookup))->complete(
            'subject-123',
            'person@example.test',
            'nick',
            'settings/account',
            ['access_token' => 'access-token']
        );

        self::assertSame('settings/account', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: link mode failed to store user link', $lastWarning[0]);
        self::assertNull(DI::session()->get('openidconnect_tokens'));
        self::assertFalse(SessionFunctionSpy::$sessionWriteCloseCalled);
    }
}
