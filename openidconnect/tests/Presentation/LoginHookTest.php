<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Presentation\LoginHook;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class LoginHookTest extends AddonTestCase
{
    public function testAppendBuildsReturnPathFromArgsQueryString(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');

        DI::args()->setQueryString('return_path=settings/account');
        $_GET['return_path'] = 'ignored-via-superglobal';

        $output = '';
        (new LoginHook())->append($output);

        self::assertStringContainsString('return_path=settings%2Faccount', $output);
        self::assertStringNotContainsString('ignored-via-superglobal', $output);
    }

    public function testAppendPrefersReturnAuthorizeAndEscapesConfiguredButtonText(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'button_text', 'Login <OIDC> "now"');
        DI::args()->setQueryString('return_authorize=client_id%3Dabc%26scope%3Dopenid&return_path=settings/account');

        $output = '';
        (new LoginHook())->append($output);

        self::assertStringContainsString('return_path=oauth%2Fauthorize%3Fclient_id%3Dabc%26scope%3Dopenid', $output);
        self::assertStringContainsString('Login &lt;OIDC&gt; &quot;now&quot;', $output);
        self::assertStringNotContainsString('>Login <OIDC> "now"<', $output);
    }

    public function testAppendDoesNothingWhenAddonIsNotConfigured(): void
    {
        // No config set → isConfigured() returns false
        $output = 'pre-existing';
        (new LoginHook())->append($output);
        self::assertSame('pre-existing', $output);
    }

    public function testAppendRendersButtonWithoutReturnPathWhenQueryStringIsEmpty(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        // DI::args()->getQueryString() returns '' by default after reset

        $output = '';
        (new LoginHook())->append($output);

        self::assertStringContainsString('/openidconnect/auth', $output);
        self::assertStringNotContainsString('return_path', $output);
        self::assertStringContainsString('openidconnect-sso-button', $output);
    }

    public function testAppendTriggersAutoRedirectAttemptWhenTransparentSsoIsEnabled(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'transparent_sso', true);
        // Cache a provider config that lacks authorization_endpoint → redirect() returns early
        DI::cache()->set('openidconnect:provider_config', ['issuer' => 'https://id.example'], 600);
        // $_SERVER['REQUEST_METHOD'] is not set → serverParams() defaults to 'GET'
        // No openidconnect_no_auto in query, no Bearer header → shouldAutoRedirect returns true

        $output = '';
        (new LoginHook())->append($output);

        self::assertContains('OpenID Connect provider configuration error.', DI::sysmsg()->notices);
        // After the failed redirect, append() continues and renders the button as fallback
        self::assertStringContainsString('/openidconnect/auth', $output);
    }

    public function testAppendPopulatesRequestMethodFromServerGlobal(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'transparent_sso', true);
        DI::cache()->set('openidconnect:provider_config', ['issuer' => 'https://id.example'], 600);
        $_SERVER['REQUEST_METHOD'] = 'POST'; // non-GET → shouldAutoRedirect returns false

        $output = '';
        (new LoginHook())->append($output);

        // POST method prevents auto-redirect → no configuration error notice
        self::assertEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('/openidconnect/auth', $output);
    }

    public function testAppendSkipsAutoRedirectForBearerAuthorizationRequests(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'transparent_sso', true);
        DI::cache()->set('openidconnect:provider_config', ['issuer' => 'https://id.example'], 600);
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test-bearer-token';

        $output = '';
        (new LoginHook())->append($output);

        // Bearer request → shouldAutoRedirect returns false → no redirect notice
        self::assertEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('/openidconnect/auth', $output);
        // OWASP: token must not appear in rendered HTML or logs
        self::assertStringNotContainsString('test-bearer-token', $output);
        self::assertStringNotContainsString(
            'test-bearer-token',
            json_encode(['warnings' => DI::logger()->warnings, 'errors' => DI::logger()->errors], JSON_THROW_ON_ERROR)
        );
    }

    public function testAppendSkipsAutoRedirectWhenNoAutoFlagOnlyExistsInGetParameters(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'transparent_sso', true);
        DI::cache()->set('openidconnect:provider_config', ['issuer' => 'https://id.example'], 600);
        $_GET['openidconnect_no_auto'] = '1';

        $output = '';
        (new LoginHook())->append($output);

        self::assertEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('/openidconnect/auth', $output);
    }

    public function testAppendPrefersQueryStringOverGetFallbackForReturnPath(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');

        DI::args()->setQueryString('return_path=settings/account');
        $_GET['return_path'] = 'network';

        $output = '';
        (new LoginHook())->append($output);

        self::assertStringContainsString('return_path=settings%2Faccount', $output);
        self::assertStringNotContainsString('return_path=network', $output);
    }
}
