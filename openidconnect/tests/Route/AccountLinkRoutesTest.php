<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Route;

use Friendica\Addon\OpenIdConnect\Route\AccountLinkRoutes;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\BaseModule;
use Friendica\DI;

final class AccountLinkRoutesTest extends AddonTestCase
{
    public function testBeginRequiresAuthenticatedUser(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::cache()->set('openidconnect:provider_config', [
            'authorization_endpoint' => 'https://id.example/authorize',
        ], 600);

        (new AccountLinkRoutes())->begin();

        self::assertSame('login', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('must be logged in', implode(' ', DI::sysmsg()->notices));
    }

    public function testUnlinkLogsOnlySafeFlagsForLinkedAccount(): void
    {
        DI::userSession()->setLocalUserId(42);
        DI::pConfig()->set(42, 'openidconnect', 'oidc_sub', 'subject-123');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_email', 'person@example.test');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_nickname', 'sensitive-nickname');
        $_POST['form_security_token'] = BaseModule::getFormSecurityToken('openidconnect_unlink');

        (new AccountLinkRoutes())->unlink();

        self::assertSame('settings/account', DI::baseUrl()->lastRedirect());

        $logContext = null;
        foreach (DI::logger()->debugs as [$message, $context]) {
            if ($message === 'openidconnect_unlink_account linked') {
                $logContext = $context;
                break;
            }
        }

        self::assertIsArray($logContext);
        self::assertSame(42, $logContext['uid'] ?? null);
        self::assertTrue((bool)($logContext['has_link'] ?? false));
        self::assertTrue((bool)($logContext['has_sub'] ?? false));
        self::assertArrayNotHasKey('linkedAccount', $logContext);
        self::assertArrayNotHasKey('email', $logContext);
        self::assertArrayNotHasKey('nickname', $logContext);
    }
}
