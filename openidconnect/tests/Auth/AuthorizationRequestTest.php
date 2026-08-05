<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class AuthorizationRequestTest extends AddonTestCase
{
    public function testRedirectBuildsAuthorizationUrlAndPersistsPkceNonceAndState(): void
    {
        $probeOutput = shell_exec(sprintf(
            '%s %s',
            escapeshellarg(PHP_BINARY),
            escapeshellarg(__DIR__ . '/../Support/authorization_redirect_probe.php')
        ));

        self::assertIsString($probeOutput);
        $probe = json_decode(trim($probeOutput), true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($probe['state_write'] ?? null);
        self::assertStringStartsWith('oidcstate:', (string)$probe['state_write']['key']);
        self::assertSame('oauth/authorize?client_id=test', $probe['state_write']['value']['return_path']);
        self::assertFalse((bool)$probe['state_write']['value']['link_mode']);
        self::assertTrue((bool)$probe['state_write']['value']['silent_auth']);
        self::assertIsInt($probe['state_write']['value']['created_at']);
        self::assertSame(64, strlen((string)$probe['state_write']['value']['nonce']));
        self::assertNotSame('', (string)$probe['state_write']['value']['pkce_verifier']);

        $location = $probe['location_header'] ?? '';
        self::assertIsString($location);
        self::assertStringStartsWith('Location: https://id.example/authorize?', $location);

        parse_str((string)parse_url(substr($location, strlen('Location: ')), PHP_URL_QUERY), $params);

        self::assertSame('code', $params['response_type'] ?? null);
        self::assertSame('client-id', $params['client_id'] ?? null);
        self::assertSame('https://example.test/openidconnect/callback', $params['redirect_uri'] ?? null);
        self::assertSame('openid email profile', $params['scope'] ?? null);
        self::assertSame('none', $params['prompt'] ?? null);
        self::assertSame(substr((string)$probe['state_write']['key'], strlen('oidcstate:')), $params['state'] ?? null);
        self::assertSame($probe['state_write']['value']['nonce'], $params['nonce'] ?? null);
        self::assertSame(
            LoginPolicy::generatePkceChallenge((string)$probe['state_write']['value']['pkce_verifier']),
            $params['code_challenge'] ?? null
        );
        self::assertSame('S256', $params['code_challenge_method'] ?? null);
    }

    public function testConsumesStateOnlyOnce(): void
    {
        DI::cache()->set('oidcstate:state', ['nonce' => 'nonce'], 600);
        $request = new AuthorizationRequest(new ProviderConfiguration());

        self::assertSame(['nonce' => 'nonce'], $request->consumeState('state'));
        self::assertSame([], $request->consumeState('state'));
    }

    public function testConsumeStateReturnsEmptyWhenCacheReadFails(): void
    {
        DI::cache()->nextGetException = new \RuntimeException('cache get failed');

        $request = new AuthorizationRequest(new ProviderConfiguration());

        self::assertSame([], $request->consumeState('state'));
        self::assertSame('openidconnect: failed to read callback state from cache', DI::logger()->errors[0][0]);
        self::assertArrayNotHasKey('state', DI::logger()->errors[0][1]);
    }

    public function testConsumeStateReturnsStateEvenWhenCacheDeleteFails(): void
    {
        DI::cache()->set('oidcstate:state', ['nonce' => 'nonce'], 600);
        DI::cache()->nextDeleteException = new \RuntimeException('cache delete failed');

        $request = new AuthorizationRequest(new ProviderConfiguration());

        self::assertSame(['nonce' => 'nonce'], $request->consumeState('state'));
        self::assertSame('openidconnect: failed to delete callback state from cache', DI::logger()->warnings[0][0]);
        self::assertArrayNotHasKey('state', DI::logger()->warnings[0][1]);
    }

    public function testRedirectReturnsWhenAddonIsNotConfigured(): void
    {
        $request = new AuthorizationRequest(new ProviderConfiguration());

        $request->redirect();

        self::assertSame('OpenID Connect SSO tried to trigger, but the addon is not configured!', DI::logger()->warnings[0][0]);
    }

    public function testRedirectReturnsNoticeWhenAuthorizationEndpointIsMissing(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::cache()->set('openidconnect:provider_config', [], 600);

        $request = new AuthorizationRequest(new ProviderConfiguration());
        $request->redirect();

        self::assertContains('OpenID Connect provider configuration error.', DI::sysmsg()->notices);
    }

    public function testRedirectSanitizesAbsoluteReturnPathInLinkModeBeforePersistingState(): void
    {
        DI::config()->set('openidconnect', 'client_id', 1);
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::cache()->set('openidconnect:provider_config', [
            'authorization_endpoint' => 'https://id.example/authorize',
        ], 600);

        $request = new AuthorizationRequest(new ProviderConfiguration());
        $request->redirect(true, 'https://evil.example/phish');

        self::assertSame('openidconnect: missing client_id while trying to redirect to provider', DI::logger()->errors[0][0]);
        self::assertContains('OpenID Connect is not fully configured.', DI::sysmsg()->notices);

        $stateWrite = DI::cache()->setCalls[1] ?? null;
        self::assertIsArray($stateWrite);
        self::assertStringStartsWith('oidcstate:', (string)$stateWrite['key']);
        self::assertSame('settings/account', $stateWrite['value']['return_path']);
        self::assertTrue((bool)$stateWrite['value']['link_mode']);
    }

    public function testRedirectPersistsSilentAuthFlagForPromptNoneFlow(): void
    {
        DI::config()->set('openidconnect', 'client_id', 1);
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::cache()->set('openidconnect:provider_config', [
            'authorization_endpoint' => 'https://id.example/authorize',
        ], 600);

        $request = new AuthorizationRequest(new ProviderConfiguration());
        $request->redirect(false, '//evil.example/path', true);

        $stateWrite = DI::cache()->setCalls[1] ?? null;
        self::assertIsArray($stateWrite);
        self::assertSame('', $stateWrite['value']['return_path']);
        self::assertTrue((bool)$stateWrite['value']['silent_auth']);
        self::assertSame('openidconnect: rejected absolute return_path', DI::logger()->warnings[0][0]);
    }

    public function testRedirectReturnsNoticeWhenStatePersistenceFails(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::cache()->set('openidconnect:provider_config', [
            'authorization_endpoint' => 'https://id.example/authorize',
        ], 600);
        DI::cache()->nextSetException = new \RuntimeException('cache set failed');

        $request = new AuthorizationRequest(new ProviderConfiguration());
        $request->redirect();

        self::assertSame('openidconnect: failed to persist state in cache', DI::logger()->errors[0][0]);
        self::assertContains('OpenID Connect authentication could not be started.', DI::sysmsg()->notices);
        self::assertArrayNotHasKey('state', DI::logger()->errors[0][1]);
    }
}
