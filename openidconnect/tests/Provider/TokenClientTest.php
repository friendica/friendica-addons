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

    public function testExchangeCodeReturnsEmptyArrayWhenTokenEndpointReturnsFailure(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(false, '{"error":"invalid_grant"}', 400);

        $result = (new TokenClient())->exchangeCode('bad-code');

        self::assertSame([], $result);
        self::assertNotEmpty(DI::logger()->errors);
        self::assertSame(
            'openidconnect: token endpoint returned non-success response',
            DI::logger()->errors[array_key_last(DI::logger()->errors)][0]
        );
    }

    public function testExchangeCodeReturnsEmptyArrayWhenHttpClientThrows(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);
        DI::httpClient()->nextException = new \RuntimeException('network down');

        $result = (new TokenClient())->exchangeCode('code');

        self::assertSame([], $result);
        self::assertNotEmpty(DI::logger()->errors);
        self::assertSame(
            'openidconnect: token endpoint request threw exception',
            DI::logger()->errors[array_key_last(DI::logger()->errors)][0]
        );
    }

    public function testExchangeCodeReturnsEmptyArrayWhenAccessTokenIsMissing(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            true,
            json_encode(['id_token' => 'id-token-only'], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new TokenClient())->exchangeCode('auth-code');

        self::assertSame([], $result);
        self::assertNotEmpty(DI::logger()->errors);
        self::assertSame(
            'openidconnect: token response missing access_token',
            DI::logger()->errors[array_key_last(DI::logger()->errors)][0]
        );
    }

    public function testRevokeLogsWarningWhenEndpointReturnsFailure(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::httpClient()->nextPostResponse = new TestHttpResponse(false, '{"error":"invalid_token"}', 400);

        (new TokenClient())->revoke(
            'https://id.example/revoke',
            'access-token',
            ['revocation_endpoint_auth_methods_supported' => ['client_secret_basic']]
        );

        self::assertNotEmpty(DI::logger()->warnings);
        self::assertSame(
            'openidconnect: revocation endpoint returned non-success',
            DI::logger()->warnings[array_key_last(DI::logger()->warnings)][0]
        );
    }

    public function testRevokeHandlesHttpClientExceptionWithoutThrowing(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::httpClient()->nextException = new \RuntimeException('timeout');

        (new TokenClient())->revoke(
            'https://id.example/revoke',
            'access-token',
            ['revocation_endpoint_auth_methods_supported' => ['client_secret_post']]
        );

        self::assertNotEmpty(DI::logger()->warnings);
        self::assertSame(
            'openidconnect: revocation endpoint request threw exception',
            DI::logger()->warnings[array_key_last(DI::logger()->warnings)][0]
        );
    }
}
