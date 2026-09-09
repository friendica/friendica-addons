<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Account\UserProvisioner;
use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Auth\CallbackDependencies;
use Friendica\Addon\OpenIdConnect\Auth\CallbackHandler;
use Friendica\Addon\OpenIdConnect\Auth\CallbackIdentityVerifier;
use Friendica\Addon\OpenIdConnect\Auth\CallbackLinkCompleter;
use Friendica\Addon\OpenIdConnect\Identity\UserInfo;
use Friendica\Addon\OpenIdConnect\Provider\IdTokenValidator;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Provider\TokenClient;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Database\Database;
use Friendica\Network\HTTPClient\Capability\ICanHandleHttpResponses;
use Friendica\Network\HTTPClient\Capability\ICanSendHttpRequests;
use Friendica\Security\Authentication;

final class CallbackHandlerTest extends DiTestCase
{
    /**
     * Wires a CallbackHandler out of real collaborators (all final, so not
     * mockable), with a custom link-ownership lookup so the link-mode path
     * doesn't need a real database.
     */
    private function makeHandler(?callable $linkOwnerLookup = null): CallbackHandler
    {
        $providerConfiguration = new ProviderConfiguration();

        return new CallbackHandler(new CallbackDependencies(
            new AuthorizationRequest($providerConfiguration),
            new TokenClient($providerConfiguration),
            new CallbackIdentityVerifier(new IdTokenValidator($providerConfiguration), new UserInfo($providerConfiguration)),
            new CallbackLinkCompleter(new AccountLinker(), $linkOwnerLookup ?? static fn (string $sub): ?array => null),
            new UserProvisioner(),
        ));
    }

    private function seedTokenAndUserinfoResponses(array $tokens, array $userinfo, bool $userinfoSuccess = true): void
    {
        $tokenResponse = $this->createMock(ICanHandleHttpResponses::class);
        $tokenResponse->method('isSuccess')->willReturn(true);
        $tokenResponse->method('getBodyString')->willReturn(json_encode($tokens, JSON_THROW_ON_ERROR));

        $userinfoResponse = $this->createMock(ICanHandleHttpResponses::class);
        $userinfoResponse->method('isSuccess')->willReturn($userinfoSuccess);
        $userinfoResponse->method('getReturnCode')->willReturn($userinfoSuccess ? '200' : '500');
        $userinfoResponse->method('getBodyString')->willReturn(json_encode($userinfo, JSON_THROW_ON_ERROR));

        $httpClient = $this->mockHttpClient();
        $httpClient->method('post')->willReturn($tokenResponse);
        $httpClient->method('get')->willReturn($userinfoResponse);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);

        $this->cache->set('openidconnect:provider_config', [
            'authorization_endpoint' => 'https://idp.example/authorize',
            'token_endpoint' => 'https://idp.example/token',
            'userinfo_endpoint' => 'https://idp.example/userinfo',
        ]);
    }

    public function testHandleShowsErrorFromAuthorizationEndpoint(): void
    {
        $this->initDi();

        $this->makeHandler()->handle(['error' => 'access_denied']);

        self::assertSame(['OpenID Connect authentication failed: access_denied'], $this->sysmsg->getNotices());
    }

    public function testHandleFallsBackSilentlyWhenSilentAuthIsRejected(): void
    {
        $this->initDi();
        $this->cache->set('oidcstate:s1', ['silent_auth' => true, 'return_path' => '']);

        $this->makeHandler()->handle(['error' => 'login_required', 'state' => 's1']);

        self::assertSame([], $this->sysmsg->getNotices());
    }

    public function testHandleRejectsMissingCodeOrState(): void
    {
        $this->initDi();

        $this->makeHandler()->handle([]);

        self::assertSame(['OpenID Connect authentication failed: missing parameters.'], $this->sysmsg->getNotices());
    }

    public function testHandleRejectsUnknownOrExpiredState(): void
    {
        $this->initDi();

        $this->makeHandler()->handle(['code' => 'abc', 'state' => 'unknown']);

        self::assertSame(['OpenID Connect authentication failed: invalid or expired state.'], $this->sysmsg->getNotices());
    }

    public function testHandleRejectsWhenTokenExchangeFails(): void
    {
        $this->initDi();
        $this->cache->set('oidcstate:s1', ['return_path' => '', 'pkce_verifier' => 'verifier']);

        $this->makeHandler()->handle(['code' => 'abc', 'state' => 's1']);

        self::assertSame(['OpenID Connect authentication failed: token exchange error.'], $this->sysmsg->getNotices());
    }

    public function testHandleRejectsWhenIdentityVerificationFails(): void
    {
        $this->seedTokenAndUserinfoResponses(['access_token' => 'tok'], [], false);
        $this->cache->set('oidcstate:s1', ['return_path' => '', 'pkce_verifier' => 'verifier']);

        $this->makeHandler()->handle(['code' => 'abc', 'state' => 's1']);

        self::assertSame([
            'OpenID Connect authentication failed: could not retrieve user info.',
            'OpenID Connect authentication failed: could not verify your identity.',
        ], $this->sysmsg->getNotices());
    }

    public function testHandleDelegatesToLinkCompleterInLinkMode(): void
    {
        $this->seedTokenAndUserinfoResponses(['access_token' => 'tok'], ['sub' => 'sub-123', 'email' => 'user@example.com']);
        $this->cache->set('oidcstate:s1', ['return_path' => 'settings/account', 'pkce_verifier' => 'verifier', 'link_mode' => true]);
        $this->session->setLocalUserId(42);

        $this->makeHandler()->handle(['code' => 'abc', 'state' => 's1']);

        self::assertSame(['OpenID Connect account successfully linked.'], $this->sysmsg->getInfos());
        self::assertSame('sub-123', $this->pConfig->get(42, 'openidconnect', 'oidc_sub', ''));
    }

    public function testHandleLogsInAndStoresTokensOnSuccessfulLogin(): void
    {
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturn(['uid' => 42, 'email' => 'user@example.com']);
        $auth = $this->createMock(Authentication::class);
        $auth->expects(self::once())->method('setForUser')->with(['uid' => 42, 'email' => 'user@example.com'], true, true);
        $this->initDi([
            ICanSendHttpRequests::class => $this->httpClientFromOverrides(),
            Database::class => $dba,
            Authentication::class => $auth,
        ]);
        $this->seedProviderConfigAndState();

        $this->makeHandler()->handle(['code' => 'abc', 'state' => 's1']);

        self::assertSame(['access_token' => 'tok'], $this->session->get('openidconnect_tokens'));
    }

    public function testHandleFallsBackWhenNoMatchingAccountIsFound(): void
    {
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturn(false);
        $this->initDi([ICanSendHttpRequests::class => $this->httpClientFromOverrides(), Database::class => $dba]);
        $this->seedProviderConfigAndState();

        $this->makeHandler()->handle(['code' => 'abc', 'state' => 's1']);

        self::assertSame([
            'OpenID Connect: No matching account found and registration is not available. Please contact the administrator.',
            'OpenID Connect login failed: No matching account found and registration is not available. Please contact the administrator.',
        ], $this->sysmsg->getNotices());
    }

    private function seedProviderConfigAndState(): void
    {
        $this->cache->set('openidconnect:provider_config', [
            'authorization_endpoint' => 'https://idp.example/authorize',
            'token_endpoint' => 'https://idp.example/token',
            'userinfo_endpoint' => 'https://idp.example/userinfo',
        ]);
        $this->cache->set('oidcstate:s1', ['return_path' => '', 'pkce_verifier' => 'verifier']);
    }

    private function httpClientFromOverrides(): ICanSendHttpRequests
    {
        $tokenResponse = $this->createMock(ICanHandleHttpResponses::class);
        $tokenResponse->method('isSuccess')->willReturn(true);
        $tokenResponse->method('getBodyString')->willReturn(json_encode(['access_token' => 'tok'], JSON_THROW_ON_ERROR));

        $userinfoResponse = $this->createMock(ICanHandleHttpResponses::class);
        $userinfoResponse->method('isSuccess')->willReturn(true);
        $userinfoResponse->method('getBodyString')->willReturn(json_encode(['sub' => 'sub-123', 'email' => 'user@example.com'], JSON_THROW_ON_ERROR));

        $httpClient = $this->mockHttpClient();
        $httpClient->method('post')->willReturn($tokenResponse);
        $httpClient->method('get')->willReturn($userinfoResponse);

        return $httpClient;
    }
}
