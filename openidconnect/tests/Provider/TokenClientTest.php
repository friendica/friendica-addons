<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Provider;

use Friendica\Addon\OpenIdConnect\Provider\TokenClient;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;
use Friendica\TestHttpResponse;

final class TokenClientTest extends AddonTestCase
{
    public function testExchangeCodePostsExplicitFormEncodedBody(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
            'token_endpoint_auth_methods_supported' => ['client_secret_basic'],
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            true,
            json_encode(['access_token' => 'access-token'], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new TokenClient())->exchangeCode('auth-code', 'pkce-verifier');

        self::assertSame('access-token', $result['access_token']);
        self::assertCount(1, DI::httpClient()->postCalls);
        self::assertIsString(DI::httpClient()->postCalls[0]['postData']);
        self::assertStringContainsString('grant_type=authorization_code', DI::httpClient()->postCalls[0]['postData']);
        self::assertStringContainsString('code=auth-code', DI::httpClient()->postCalls[0]['postData']);
        self::assertStringContainsString('code_verifier=pkce-verifier', DI::httpClient()->postCalls[0]['postData']);
        self::assertStringContainsString('redirect_uri=', DI::httpClient()->postCalls[0]['postData']);
    }

    public function testExchangeCodeReturnsEmptyArrayForJsonScalarTokenPayload(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(true, '"scalar"', 200);

        $result = (new TokenClient())->exchangeCode('auth-code');

        self::assertSame([], $result);
        self::assertNotEmpty(DI::logger()->errors);
        self::assertSame(
            'openidconnect: token response was valid JSON but not an object',
            DI::logger()->errors[array_key_last(DI::logger()->errors)][0]
        );
    }
}
