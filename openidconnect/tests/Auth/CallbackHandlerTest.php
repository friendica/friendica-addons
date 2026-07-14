<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\TestHttpResponse;
use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Account\AvatarUpdater;
use Friendica\Addon\OpenIdConnect\Account\UserProvisioner;
use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Auth\CallbackDependencies;
use Friendica\Addon\OpenIdConnect\Auth\CallbackHandler;
use Friendica\Addon\OpenIdConnect\Auth\CallbackIdentityVerifier;
use Friendica\Addon\OpenIdConnect\Auth\CallbackLinkCompleter;
use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;
use Friendica\Addon\OpenIdConnect\Identity\UserInfo;
use Friendica\Addon\OpenIdConnect\Provider\IdTokenValidator;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Provider\TokenClient;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class CallbackHandlerTest extends AddonTestCase
{
    public function testSilentLoginRequiredFallsBackToManualLoginWithReturnPath(): void
    {
        DI::cache()->set('oidcstate:s', ['silent_auth' => true, 'return_path' => 'oauth/authorize?client_id=test'], 600);
        (new CallbackHandler($this->dependencies()))->handle(['state' => 's', 'error' => 'login_required']);

        self::assertSame('login?openidconnect_no_auto=1&return_path=oauth%2Fauthorize%3Fclient_id%3Dtest', DI::baseUrl()->lastRedirect());
    }

    public function testMissingCodeOrStateRedirectsToLogin(): void
    {
        (new CallbackHandler($this->dependencies()))->handle([]);

        self::assertSame('login', DI::baseUrl()->lastRedirect());
    }

    public function testEmptyIdentityVerificationFallsBackToManualLoginWithNotice(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');

        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
            'jwks_uri' => 'https://id.example/jwks',
        ], 600);

        DI::cache()->set('oidcstate:s', [
            'nonce' => 'nonce',
            'return_path' => 'oauth/authorize?client_id=test',
        ], 600);

        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            true,
            json_encode(['access_token' => 'token'], JSON_THROW_ON_ERROR),
            200
        );

        (new CallbackHandler($this->dependencies()))->handle([
            'code' => 'auth-code',
            'state' => 's',
        ]);

        self::assertSame(
            'login?openidconnect_no_auto=1&return_path=oauth%2Fauthorize%3Fclient_id%3Dtest',
            DI::baseUrl()->lastRedirect()
        );
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString(
            'could not verify your identity',
            implode(' ', DI::sysmsg()->notices)
        );
    }

    private function dependencies(): CallbackDependencies
    {
        return new CallbackDependencies(
            new AuthorizationRequest(new ProviderConfiguration()),
            new TokenClient(),
            new CallbackIdentityVerifier(new IdTokenValidator(), new UserInfo(new ProviderConfiguration())),
            new CallbackLinkCompleter(new AccountLinker()),
            new UserProvisioner(new AccountLinker(), new AvatarUpdater()),
        );
    }
}
