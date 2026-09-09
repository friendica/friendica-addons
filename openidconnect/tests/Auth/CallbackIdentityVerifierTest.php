<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Auth\CallbackIdentityVerifier;
use Friendica\Addon\OpenIdConnect\Identity\UserInfo;
use Friendica\Addon\OpenIdConnect\Provider\IdTokenValidator;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Network\HTTPClient\Capability\ICanHandleHttpResponses;
use Friendica\Network\HTTPClient\Capability\ICanSendHttpRequests;

/**
 * Uses the real IdTokenValidator and UserInfo (both final, so not mockable)
 * driven through the DI-mocked config/cache/httpClient instead. The
 * id_token-present branches only cover validation failure: exercising a
 * genuinely decoded token would need the addon's own firebase/php-jwt
 * dependency, which this CI image does not install (see IdTokenValidatorTest).
 */
final class CallbackIdentityVerifierTest extends DiTestCase
{
    private function seedUserinfoResponse(array $body, bool $success = true): void
    {
        $response = $this->createMock(ICanHandleHttpResponses::class);
        $response->method('isSuccess')->willReturn($success);
        $response->method('getBodyString')->willReturn(json_encode($body, JSON_THROW_ON_ERROR));
        $response->method('getReturnCode')->willReturn($success ? '200' : '500');

        $httpClient = $this->mockHttpClient();
        $httpClient->method('get')->willReturn($response);
        $this->initDi([ICanSendHttpRequests::class => $httpClient]);
        $this->cache->set('openidconnect:provider_config', ['userinfo_endpoint' => 'https://idp.example/userinfo']);
    }

    private function verifier(): CallbackIdentityVerifier
    {
        return new CallbackIdentityVerifier(new IdTokenValidator(), new UserInfo());
    }

    public function testVerifyRejectsWhenIdTokenFailsValidation(): void
    {
        $this->initDi();

        $result = $this->verifier()->verify(['id_token' => 'not-a-jwt', 'access_token' => 'tok'], []);

        self::assertSame([], $result);
        self::assertSame(['OpenID Connect authentication failed: invalid identity token.'], $this->sysmsg->getNotices());
    }

    public function testVerifyUsesUserinfoEndpointResultWhenAvailable(): void
    {
        $this->seedUserinfoResponse(['sub' => 'sub-123', 'email' => 'user@example.com']);

        $result = $this->verifier()->verify(['access_token' => 'tok'], []);

        self::assertSame(['sub' => 'sub-123', 'email' => 'user@example.com'], $result);
    }

    public function testVerifyRejectsWhenUserinfoIsCompletelyUnavailable(): void
    {
        $this->seedUserinfoResponse([], false);

        $result = $this->verifier()->verify(['access_token' => 'tok'], []);

        self::assertSame([], $result);
        self::assertSame(['OpenID Connect authentication failed: could not retrieve user info.'], $this->sysmsg->getNotices());
    }

    public function testVerifyRejectsUnverifiedEmailByDefault(): void
    {
        $this->seedUserinfoResponse(['sub' => 'sub-123', 'email' => 'user@example.com', 'email_verified' => false]);

        $result = $this->verifier()->verify(['access_token' => 'tok'], []);

        self::assertSame([], $result);
        self::assertSame(
            ['OpenID Connect: Your email address has not been verified by the identity provider.'],
            $this->sysmsg->getNotices()
        );
    }

    public function testVerifyAllowsUnverifiedEmailWhenConfigured(): void
    {
        $this->seedUserinfoResponse(['sub' => 'sub-123', 'email' => 'user@example.com', 'email_verified' => false]);
        $this->config->set('openidconnect', 'allow_unverified_email', true);

        $result = $this->verifier()->verify(['access_token' => 'tok'], []);

        self::assertSame('user@example.com', $result['email']);
    }

    public function testVerifyRejectsMissingEmail(): void
    {
        $this->seedUserinfoResponse(['sub' => 'sub-123']);

        $result = $this->verifier()->verify(['access_token' => 'tok'], []);

        self::assertSame([], $result);
        self::assertSame(['OpenID Connect: Email address not provided by the identity provider.'], $this->sysmsg->getNotices());
    }
}
