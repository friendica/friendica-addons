<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Presentation\LoginHook;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;

final class LoginHookTest extends DiTestCase
{
    private function configureProvider(): void
    {
        $this->config->set('openidconnect', 'client_id', 'client-id');
        $this->config->set('openidconnect', 'client_secret', 'secret');
        $this->config->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
    }

    public function testAppendBuildsReturnPathFromArgsQueryString(): void
    {
        $this->initDi();
        $this->configureProvider();
        $this->args->method('getQueryString')->willReturn('return_path=settings/account');
        $_GET['return_path'] = 'ignored-via-superglobal';

        $output = '';
        (new LoginHook())->append($output);

        self::assertStringContainsString('return_path=settings%2Faccount', $output);
        self::assertStringNotContainsString('ignored-via-superglobal', $output);
    }

    public function testAppendPrefersReturnAuthorizeAndEscapesConfiguredButtonText(): void
    {
        $this->initDi();
        $this->configureProvider();
        $this->config->set('openidconnect', 'button_text', 'Login <OIDC> "now"');
        $this->args->method('getQueryString')->willReturn('return_authorize=client_id%3Dabc%26scope%3Dopenid&return_path=settings/account');

        $output = '';
        (new LoginHook())->append($output);

        self::assertStringContainsString('return_path=oauth%2Fauthorize%3Fclient_id%3Dabc%26scope%3Dopenid', $output);
        self::assertStringContainsString('Login &lt;OIDC&gt; &quot;now&quot;', $output);
        self::assertStringNotContainsString('>Login <OIDC> "now"<', $output);
    }

    public function testAppendDoesNothingWhenAddonIsNotConfigured(): void
    {
        $this->initDi();

        $output = 'pre-existing';
        (new LoginHook())->append($output);

        self::assertSame('pre-existing', $output);
    }

    public function testAppendRendersButtonWithoutReturnPathWhenQueryStringIsEmpty(): void
    {
        $this->initDi();
        $this->configureProvider();

        $output = '';
        (new LoginHook())->append($output);

        self::assertStringContainsString('/openidconnect/auth', $output);
        self::assertStringNotContainsString('return_path', $output);
        self::assertStringContainsString('openidconnect-sso-button', $output);
    }

    public function testAppendTriggersAutoRedirectAttemptWhenTransparentSsoIsEnabled(): void
    {
        $this->initDi();
        $this->configureProvider();
        $this->config->set('openidconnect', 'transparent_sso', true);
        $this->cache->set('openidconnect:provider_config', ['issuer' => 'https://id.example']);

        $output = '';
        (new LoginHook())->append($output);

        self::assertContains('OpenID Connect provider configuration error.', $this->sysmsg->getNotices());
        self::assertStringContainsString('/openidconnect/auth', $output);
    }

    public function testAppendPopulatesRequestMethodFromServerGlobal(): void
    {
        $this->initDi();
        $this->configureProvider();
        $this->config->set('openidconnect', 'transparent_sso', true);
        $this->cache->set('openidconnect:provider_config', ['issuer' => 'https://id.example']);
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $output = '';
        (new LoginHook())->append($output);

        self::assertSame([], $this->sysmsg->getNotices());
        self::assertStringContainsString('/openidconnect/auth', $output);
    }

    public function testAppendSkipsAutoRedirectForBearerAuthorizationRequests(): void
    {
        $this->initDi();
        $this->configureProvider();
        $this->config->set('openidconnect', 'transparent_sso', true);
        $this->cache->set('openidconnect:provider_config', ['issuer' => 'https://id.example']);
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test-bearer-token';

        $output = '';
        (new LoginHook())->append($output);

        self::assertSame([], $this->sysmsg->getNotices());
        self::assertStringContainsString('/openidconnect/auth', $output);
        self::assertStringNotContainsString('test-bearer-token', $output);
    }

    public function testAppendSkipsAutoRedirectWhenNoAutoFlagOnlyExistsInGetParameters(): void
    {
        $this->initDi();
        $this->configureProvider();
        $this->config->set('openidconnect', 'transparent_sso', true);
        $this->cache->set('openidconnect:provider_config', ['issuer' => 'https://id.example']);
        $_GET['openidconnect_no_auto'] = '1';

        $output = '';
        (new LoginHook())->append($output);

        self::assertSame([], $this->sysmsg->getNotices());
        self::assertStringContainsString('/openidconnect/auth', $output);
    }

    public function testAppendPrefersQueryStringOverGetFallbackForReturnPath(): void
    {
        $this->initDi();
        $this->configureProvider();
        $this->args->method('getQueryString')->willReturn('return_path=settings/account');
        $_GET['return_path'] = 'network';

        $output = '';
        (new LoginHook())->append($output);

        self::assertStringContainsString('return_path=settings%2Faccount', $output);
        self::assertStringNotContainsString('return_path=network', $output);
    }
}
