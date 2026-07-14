<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Route;

use Friendica\Addon\OpenIdConnect\Route\AccountLinkRoutes;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\BaseModule;
use Friendica\Core\Hook;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\User;

final class AccountLinkRoutesTest extends AddonTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        DBA::resetTestState();
        Hook::resetTestState();
        DI::resetTestState();
        User::resetTestState();
        Contact::resetTestState();
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
        $_SERVER = [];

        parent::tearDown();
    }

    public function testBeginAlreadyLinked(): void
    {
        $this->configureProvider();
        DI::userSession()->setLocalUserId(42);
        DI::pConfig()->set(42, 'openidconnect', 'oidc_sub', 'subject-123');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_email', 'person@example.test');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_nickname', 'sensitive-nickname');
        DI::session()->set('openidconnect_tokens', ['access_token' => 'access-token']);

        (new AccountLinkRoutes())->begin();

        self::assertSame('settings/account', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('already linked', implode(' ', DI::sysmsg()->notices));
        self::assertSame('subject-123', DI::pConfig()->get(42, 'openidconnect', 'oidc_sub'));
        self::assertSame('person@example.test', DI::pConfig()->get(42, 'openidconnect', 'oidc_email'));
        self::assertSame('sensitive-nickname', DI::pConfig()->get(42, 'openidconnect', 'oidc_nickname'));
        self::assertSame('access-token', DI::session()->get('openidconnect_tokens')['access_token']);
        self::assertNoSensitiveLogLeak(['subject-123', 'person@example.test', 'sensitive-nickname', 'access-token']);
    }

    public function testBeginNotConfiguredRedirectsToAccountSettings(): void
    {
        DI::userSession()->setLocalUserId(42);

        (new AccountLinkRoutes())->begin();

        self::assertSame('settings/account', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('not configured', implode(' ', DI::sysmsg()->notices));
        self::assertNoSensitiveLogLeak(['subject-123', 'person@example.test', 'sensitive-nickname', 'access-token']);
    }

    public function testBeginUnauthenticatedIsRejected(): void
    {
        $this->configureProvider();

        (new AccountLinkRoutes())->begin();

        self::assertSame('login', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('must be logged in', implode(' ', DI::sysmsg()->notices));
        self::assertNoSensitiveLogLeak(['subject-123', 'person@example.test', 'sensitive-nickname', 'access-token']);
    }

    public function testBeginConfiguredAndUnlinkedTriggersAuthorizationRedirect(): void
    {
        $this->configureProvider();
        DI::userSession()->setLocalUserId(42);

        $captured = [];
        $routes = new AccountLinkRoutes(
            authorizationRedirectFn: static function (bool $linkMode, string $returnPath) use (&$captured): void {
                $captured[] = ['linkMode' => $linkMode, 'returnPath' => $returnPath];
            },
        );

        $routes->begin();

        self::assertSame([['linkMode' => true, 'returnPath' => 'settings/account']], $captured);
        self::assertNull(DI::baseUrl()->lastRedirect());
        self::assertEmpty(DI::sysmsg()->notices);
        self::assertNoSensitiveLogLeak(['subject-123', 'person@example.test', 'sensitive-nickname', 'access-token']);
    }

    public function testUnlinkNoLinkFound(): void
    {
        DI::userSession()->setLocalUserId(42);
        DI::pConfig()->set(42, 'openidconnect', 'oidc_email', 'person@example.test');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_nickname', 'sensitive-nickname');
        DI::session()->set('openidconnect_tokens', ['access_token' => 'access-token']);
        $_POST['form_security_token'] = BaseModule::getFormSecurityToken('openidconnect_unlink');

        $baseline = $this->captureMutationBaseline();

        (new AccountLinkRoutes())->unlink();

        self::assertSame('settings/account', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('No OpenID Connect link found', implode(' ', DI::sysmsg()->notices));
        self::assertEmpty(DI::sysmsg()->infos);
        self::assertSame('person@example.test', DI::pConfig()->get(42, 'openidconnect', 'oidc_email'));
        self::assertSame('sensitive-nickname', DI::pConfig()->get(42, 'openidconnect', 'oidc_nickname'));
        self::assertSame('access-token', DI::session()->get('openidconnect_tokens')['access_token']);
        self::assertNoAccountLinkMutationSince($baseline);
        self::assertNoSensitiveLogKeys();
        self::assertNoSensitiveLogLeak(['subject-123', 'person@example.test', 'sensitive-nickname', 'access-token']);
    }

    public function testUnlinkExistingLinkRemovesLinkAndShowsInfo(): void
    {
        DI::userSession()->setLocalUserId(42);
        DI::pConfig()->set(42, 'openidconnect', 'oidc_sub', 'subject-123');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_email', 'person@example.test');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_nickname', 'sensitive-nickname');
        DI::session()->set('openidconnect_tokens', ['access_token' => 'access-token']);
        $_POST['form_security_token'] = BaseModule::getFormSecurityToken('openidconnect_unlink');

        (new AccountLinkRoutes())->unlink();

        self::assertSame('settings/account', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->infos);
        self::assertStringContainsString('link removed', implode(' ', DI::sysmsg()->infos));
        self::assertNull(DI::pConfig()->get(42, 'openidconnect', 'oidc_sub'));
        self::assertNull(DI::pConfig()->get(42, 'openidconnect', 'oidc_email'));
        self::assertNull(DI::pConfig()->get(42, 'openidconnect', 'oidc_nickname'));
        self::assertNull(DI::session()->get('openidconnect_tokens'));
        self::assertNoSensitiveLogKeys();
        self::assertNoSensitiveLogLeak(['subject-123', 'person@example.test', 'sensitive-nickname', 'access-token']);
    }

    public function testUnlinkMalformedCsrfToken(): void
    {
        DI::userSession()->setLocalUserId(42);
        DI::pConfig()->set(42, 'openidconnect', 'oidc_sub', 'subject-123');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_email', 'person@example.test');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_nickname', 'sensitive-nickname');
        DI::session()->set('openidconnect_tokens', ['access_token' => 'access-token']);
        $_POST['form_security_token'] = 'malformed-openidconnect-token';

        $baseline = $this->captureMutationBaseline();

        (new AccountLinkRoutes())->unlink();

        self::assertSame('settings/account', DI::baseUrl()->lastRedirect());
        self::assertSame('subject-123', DI::pConfig()->get(42, 'openidconnect', 'oidc_sub'));
        self::assertSame('person@example.test', DI::pConfig()->get(42, 'openidconnect', 'oidc_email'));
        self::assertSame('sensitive-nickname', DI::pConfig()->get(42, 'openidconnect', 'oidc_nickname'));
        self::assertSame('access-token', DI::session()->get('openidconnect_tokens')['access_token']);
        self::assertNoAccountLinkMutationSince($baseline);
        self::assertEmpty(DI::sysmsg()->infos);
        self::assertEmpty(DI::sysmsg()->notices);
        self::assertEmpty(DI::logger()->debugs);
        self::assertEmpty(DI::logger()->warnings);
        self::assertNoSensitiveLogLeak(['subject-123', 'person@example.test', 'sensitive-nickname', 'access-token']);
    }

    public function testUnlinkAuthBypassAttempted(): void
    {
        DI::pConfig()->set(42, 'openidconnect', 'oidc_sub', 'subject-123');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_email', 'person@example.test');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_nickname', 'sensitive-nickname');
        DI::session()->set('openidconnect_tokens', ['access_token' => 'access-token']);
        $_POST['form_security_token'] = BaseModule::getFormSecurityToken('openidconnect_unlink');

        $baseline = $this->captureMutationBaseline();

        (new AccountLinkRoutes())->unlink();

        self::assertSame('login', DI::baseUrl()->lastRedirect());
        self::assertSame('subject-123', DI::pConfig()->get(42, 'openidconnect', 'oidc_sub'));
        self::assertSame('person@example.test', DI::pConfig()->get(42, 'openidconnect', 'oidc_email'));
        self::assertSame('sensitive-nickname', DI::pConfig()->get(42, 'openidconnect', 'oidc_nickname'));
        self::assertSame('access-token', DI::session()->get('openidconnect_tokens')['access_token']);
        self::assertNoAccountLinkMutationSince($baseline);
        self::assertNoSensitiveLogKeys();
        self::assertEmpty(DI::sysmsg()->infos);
        self::assertEmpty(DI::sysmsg()->notices);
        self::assertNoSensitiveLogLeak(['subject-123', 'person@example.test', 'sensitive-nickname', 'access-token']);
    }

    private function configureProvider(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::cache()->set('openidconnect:provider_config', [
            'authorization_endpoint' => 'https://id.example/authorize',
        ], 600);
    }

    private function captureMutationBaseline(): array
    {
        return [
            'updateCalls' => count(DBA::$updateCalls),
            'deleteCalls' => count(DBA::$deleteCalls),
            'setCalls' => count(DI::pConfig()->setCalls),
            'deleteConfigCalls' => count(DI::pConfig()->deleteCalls),
        ];
    }

    private static function assertNoAccountLinkMutationSince(array $baseline): void
    {
        self::assertSame($baseline['updateCalls'], count(DBA::$updateCalls));
        self::assertSame($baseline['deleteCalls'], count(DBA::$deleteCalls));
        self::assertSame($baseline['setCalls'], count(DI::pConfig()->setCalls));
        self::assertSame($baseline['deleteConfigCalls'], count(DI::pConfig()->deleteCalls));
    }

    private static function assertNoSensitiveLogKeys(): void
    {
        $channels = [
            DI::logger()->debugs,
            DI::logger()->warnings,
            DI::logger()->errors,
            DI::logger()->infos,
        ];

        foreach ($channels as $entries) {
            foreach ($entries as [$message, $context]) {
                self::assertStringNotContainsString('sub', $message);

                if (!is_array($context)) {
                    continue;
                }

                foreach (array_keys($context) as $key) {
                    self::assertIsString($key);
                    self::assertStringNotContainsString('sub', $key);
                    self::assertStringNotContainsString('email', $key);
                    self::assertStringNotContainsString('access_token', $key);
                    self::assertStringNotContainsString('id_token', $key);
                }
            }
        }
    }

    /**
     * @param list<string> $sensitiveValues
     */
    private function assertNoSensitiveLogLeak(array $sensitiveValues): void
    {
        $channels = [
            DI::logger()->debugs,
            DI::logger()->warnings,
            DI::logger()->errors,
            DI::logger()->infos,
        ];

        foreach ($channels as $entries) {
            foreach ($entries as [$message, $context]) {
                self::assertIsString($message);
                $encodedContext = json_encode($context);
                self::assertNotFalse($encodedContext);

                foreach ($sensitiveValues as $value) {
                    self::assertStringNotContainsString($value, $message);
                    self::assertStringNotContainsString($value, $encodedContext);
                }
            }
        }
    }
}
