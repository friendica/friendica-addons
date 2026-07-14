<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Route;

use Friendica\Addon\OpenIdConnect\Route\AccountLinkRoutes;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\BaseModule;
use Friendica\DI;

final class AccountLinkRoutesTest extends AddonTestCase
{
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
