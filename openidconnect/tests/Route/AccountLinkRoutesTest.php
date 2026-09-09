<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Route;

use Friendica\Addon\OpenIdConnect\Presentation\SettingsHook;
use Friendica\Addon\OpenIdConnect\Route\AccountLinkRoutes;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Database\Database;

final class AccountLinkRoutesTest extends DiTestCase
{
    private function configureProvider(): void
    {
        $this->config->set('openidconnect', 'client_id', 'client-id');
        $this->config->set('openidconnect', 'client_secret', 'client-secret');
        $this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');
    }

    private function validCsrfToken(string $typename): string
    {
        $timestamp = time();

        return $timestamp . '.' . hash('whirlpool', session_id() . $timestamp . $typename);
    }

    public function testBeginRedirectsToSettingsWhenNotConfigured(): void
    {
        $this->initDi();

        $this->assertRedirectsTo('settings/account', fn () => (new AccountLinkRoutes())->begin());

        self::assertSame(['OpenID Connect is not configured.'], $this->sysmsg->getNotices());
    }

    public function testBeginRedirectsToLoginWhenNotAuthenticated(): void
    {
        $this->initDi();
        $this->configureProvider();

        $this->assertRedirectsTo('login', fn () => (new AccountLinkRoutes())->begin());

        self::assertSame(['You must be logged in to link your account.'], $this->sysmsg->getNotices());
    }

    public function testBeginRedirectsToSettingsWhenAlreadyLinked(): void
    {
        $this->initDi();
        $this->configureProvider();
        $this->session->setLocalUserId(42);
        $this->pConfig->set(42, 'openidconnect', 'oidc_sub', 'sub-123');

        $this->assertRedirectsTo('settings/account', fn () => (new AccountLinkRoutes())->begin());

        self::assertSame(['Your account is already linked to an OpenID Connect provider.'], $this->sysmsg->getNotices());
    }

    public function testBeginDelegatesToAuthorizationRedirectInLinkMode(): void
    {
        $this->initDi();
        $this->configureProvider();
        $this->session->setLocalUserId(42);
        $calls = [];

        (new AccountLinkRoutes(null, null, null, function (bool $linkMode, string $returnPath) use (&$calls): void {
            $calls[] = [$linkMode, $returnPath];
        }))->begin();

        self::assertSame([[true, 'settings/account']], $calls);
    }

    public function testUnlinkRedirectsToSettingsWhenCsrfTokenIsInvalid(): void
    {
        $this->initDi();
        $_REQUEST[SettingsHook::UNLINK_TOKEN_FIELD] = 'invalid';

        $this->assertRedirectsTo('settings/account', fn () => (new AccountLinkRoutes())->unlink());
    }

    public function testUnlinkRedirectsToLoginWhenNotAuthenticated(): void
    {
        $this->initDi();
        $_REQUEST[SettingsHook::UNLINK_TOKEN_FIELD] = $this->validCsrfToken('openidconnect_unlink');

        $this->assertRedirectsTo('login', fn () => (new AccountLinkRoutes())->unlink());
    }

    public function testUnlinkRemovesLinkAndAddsInfoWhenLinked(): void
    {
        $dba = $this->mockDba();
        $dba->method('update')->willReturn(true);
        $this->initDi([Database::class => $dba]);
        $_REQUEST[SettingsHook::UNLINK_TOKEN_FIELD] = $this->validCsrfToken('openidconnect_unlink');
        $this->session->setLocalUserId(42);
        $this->pConfig->set(42, 'openidconnect', 'oidc_sub', 'sub-123');

        $this->assertRedirectsTo('settings/account', fn () => (new AccountLinkRoutes())->unlink());

        self::assertSame(['OpenID Connect account link removed.'], $this->sysmsg->getInfos());
        self::assertSame('', $this->pConfig->get(42, 'openidconnect', 'oidc_sub', ''));
    }

    public function testUnlinkBlockedWhenOnlyGeneratedPasswordIsSet(): void
    {
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturnMap([
            ['user', ['password'], ['uid' => 42], [], ['password' => 'generated-hash']],
        ]);
        $dba->expects(self::never())->method('update');
        $this->initDi([Database::class => $dba]);
        $_REQUEST[SettingsHook::UNLINK_TOKEN_FIELD] = $this->validCsrfToken('openidconnect_unlink');
        $this->session->setLocalUserId(42);
        $this->pConfig->set(42, 'openidconnect', 'oidc_sub', 'sub-123');
        $this->pConfig->set(42, 'openidconnect', 'oidc_generated_password_hash', 'generated-hash');

        $this->assertRedirectsTo('settings/account', fn () => (new AccountLinkRoutes())->unlink());

        self::assertSame(
            ['Please set a password for your account before unlinking OpenID Connect, otherwise you will not be able to log in anymore.'],
            $this->sysmsg->getNotices()
        );
        self::assertSame('sub-123', $this->pConfig->get(42, 'openidconnect', 'oidc_sub', ''));
    }

    public function testUnlinkProceedsWhenPasswordWasChangedSinceCreation(): void
    {
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturnMap([
            ['user', ['password'], ['uid' => 42], [], ['password' => 'new-hash']],
        ]);
        $dba->method('update')->willReturn(true);
        $this->initDi([Database::class => $dba]);
        $_REQUEST[SettingsHook::UNLINK_TOKEN_FIELD] = $this->validCsrfToken('openidconnect_unlink');
        $this->session->setLocalUserId(42);
        $this->pConfig->set(42, 'openidconnect', 'oidc_sub', 'sub-123');
        $this->pConfig->set(42, 'openidconnect', 'oidc_generated_password_hash', 'generated-hash');

        $this->assertRedirectsTo('settings/account', fn () => (new AccountLinkRoutes())->unlink());

        self::assertSame(['OpenID Connect account link removed.'], $this->sysmsg->getInfos());
    }

    public function testUnlinkAddsNoticeWhenNoLinkExists(): void
    {
        $this->initDi();
        $_REQUEST[SettingsHook::UNLINK_TOKEN_FIELD] = $this->validCsrfToken('openidconnect_unlink');
        $this->session->setLocalUserId(42);

        $this->assertRedirectsTo('settings/account', fn () => (new AccountLinkRoutes())->unlink());

        self::assertSame(['No OpenID Connect link found for this account.'], $this->sysmsg->getNotices());
    }
}
