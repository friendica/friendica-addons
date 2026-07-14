<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class AuthorizationRequestTest extends AddonTestCase
{
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
    }

    public function testConsumeStateReturnsStateEvenWhenCacheDeleteFails(): void
    {
        DI::cache()->set('oidcstate:state', ['nonce' => 'nonce'], 600);
        DI::cache()->nextDeleteException = new \RuntimeException('cache delete failed');

        $request = new AuthorizationRequest(new ProviderConfiguration());

        self::assertSame(['nonce' => 'nonce'], $request->consumeState('state'));
        self::assertSame('openidconnect: failed to delete callback state from cache', DI::logger()->warnings[0][0]);
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
    }
}
