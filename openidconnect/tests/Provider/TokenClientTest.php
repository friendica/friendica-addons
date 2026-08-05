<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Provider;

use Friendica\Addon\OpenIdConnect\Provider\TokenClient;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;
use Friendica\TestHttpResponse;

final class TokenClientTest extends AddonTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DI::httpClient()->forceTypeErrorOnFirstStringPost = false;
        DI::httpClient()->forcedTypeErrorMessage = 'Simulated TypeError for string post payload';
    }

    /**
     * Covers exchangeCode() client_secret_basic auth branch ensuring credentials stay out of the form body.
     */
    public function testExchangeCodeClientSecretBasicSendsAuthorizationHeaderOnly(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-basic-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-basic-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
            'token_endpoint_auth_methods_supported' => ['client_secret_basic'],
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            true,
            json_encode(['access_token' => 'access-basic', 'refresh_token' => 'refresh-basic'], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new TokenClient())->exchangeCode('auth-code-basic');

        self::assertSame('access-basic', $result['access_token']);
        self::assertCount(1, DI::httpClient()->postCalls);
        $call = DI::httpClient()->postCalls[0];
        self::assertArrayHasKey('Authorization', $call['headers']);
        self::assertStringContainsString('Basic ', $call['headers']['Authorization']);
        self::assertIsString($call['postData']);
        self::assertStringNotContainsString('client_secret=', $call['postData']);
        self::assertStringNotContainsString('client_id=', $call['postData']);
        $this->assertWarningsAndDebugsDoNotContain(
            'access-basic',
            'refresh-basic',
            'client-basic-secret',
            'person@example.test'
        );
    }

    /**
     * Covers exchangeCode() client_secret_post auth branch ensuring credentials are sent in body and not in Authorization header.
     */
    public function testExchangeCodeClientSecretPostSendsCredentialsInFormBodyWithoutAuthorization(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-post-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-post-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
            'token_endpoint_auth_methods_supported' => ['client_secret_post'],
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            true,
            json_encode(['access_token' => 'access-post', 'refresh_token' => 'refresh-post'], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new TokenClient())->exchangeCode('auth-code-post', 'pkce-post-verifier');

        self::assertSame('access-post', $result['access_token']);
        self::assertCount(1, DI::httpClient()->postCalls);
        $call = DI::httpClient()->postCalls[0];
        self::assertArrayNotHasKey('Authorization', $call['headers']);
        self::assertIsString($call['postData']);
        self::assertStringContainsString('client_id=client-post-id', $call['postData']);
        self::assertStringContainsString('client_secret=client-post-secret', $call['postData']);
        self::assertStringContainsString('code_verifier=pkce-post-verifier', $call['postData']);
        $this->assertWarningsAndDebugsDoNotContain(
            'access-post',
            'refresh-post',
            'client-post-secret',
            'person@example.test'
        );
    }

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

    public function testExchangeCodeReturnsEmptyArrayWhenTokenResponseContainsMalformedJson(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(true, '{"access_token":', 200);

        $result = (new TokenClient())->exchangeCode('bad-code');

        self::assertSame([], $result);
        self::assertNotEmpty(DI::logger()->errors);
        self::assertSame(
            'openidconnect: malformed JSON in token response',
            DI::logger()->errors[array_key_last(DI::logger()->errors)][0]
        );
    }

    public function testExchangeCodeFailureLogContextDoesNotLeakSensitiveFields(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'ultra-secret-client-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            false,
            '{"access_token":"access-token-123","client_secret":"ultra-secret-client-secret","email":"person@example.test","sub":"subject-123"}',
            400
        );

        $result = (new TokenClient())->exchangeCode('bad-code');

        self::assertSame([], $result);
        $lastError = DI::logger()->errors[array_key_last(DI::logger()->errors)];
        self::assertSame('openidconnect: token endpoint returned non-success response', $lastError[0]);
        $contextEncoded = json_encode($lastError[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('access-token-123', $contextEncoded);
        self::assertStringNotContainsString('ultra-secret-client-secret', $contextEncoded);
        self::assertStringNotContainsString('person@example.test', $contextEncoded);
        self::assertStringNotContainsString('subject-123', $contextEncoded);
    }

    public function testExchangeCodeFailureLogContextRedactsPlainTextBearerTokenAndEmail(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'ultra-secret-client-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            false,
            'Bearer access-token-123 rejected for person@example.test',
            400
        );

        $result = (new TokenClient())->exchangeCode('bad-code');

        self::assertSame([], $result);
        $lastError = DI::logger()->errors[array_key_last(DI::logger()->errors)];
        self::assertSame('openidconnect: token endpoint returned non-success response', $lastError[0]);
        $contextEncoded = json_encode($lastError[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('access-token-123', $contextEncoded);
        self::assertStringNotContainsString('person@example.test', $contextEncoded);
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

    /**
     * Covers exchangeCode() success payload branch where refresh_token is absent but access_token is present.
     */
    public function testExchangeCodeHandlesMissingRefreshTokenGracefully(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'missing-refresh-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            true,
            json_encode(['access_token' => 'access-only-token'], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new TokenClient())->exchangeCode('auth-code');

        self::assertSame('access-only-token', $result['access_token']);
        self::assertArrayNotHasKey('refresh_token', $result);
        $this->assertWarningsAndDebugsDoNotContain(
            'access-only-token',
            'refresh-token-not-present',
            'missing-refresh-secret',
            'person@example.test'
        );
    }

    /**
     * Covers exchangeCode() valid-but-empty JSON object branch that must return [] and log missing access_token.
     */
    public function testExchangeCodeReturnsEmptyArrayForEmptyJsonObject(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(true, '{}', 200);

        $result = (new TokenClient())->exchangeCode('auth-code');

        self::assertSame([], $result);
        self::assertNotEmpty(DI::logger()->errors);
        self::assertSame(
            'openidconnect: token response missing access_token',
            DI::logger()->errors[array_key_last(DI::logger()->errors)][0]
        );
        $this->assertWarningsAndDebugsDoNotContain(
            'access-token-should-not-exist',
            'refresh-token-should-not-exist',
            'client-secret',
            'person@example.test'
        );
    }

    /**
     * Covers exchangeCode() OAuth error payload branch when JSON is valid but lacks access_token.
     */
    public function testExchangeCodeOAuthErrorPayloadReturnsEmptyArrayAndLogsMissingAccessToken(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'oauth-error-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            true,
            json_encode([
                'error' => 'invalid_grant',
                'error_description' => 'Account for person@example.test was rejected',
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new TokenClient())->exchangeCode('auth-code');

        self::assertSame([], $result);
        self::assertSame(
            'openidconnect: token response missing access_token',
            DI::logger()->errors[array_key_last(DI::logger()->errors)][0]
        );
        $this->assertWarningsAndDebugsDoNotContain(
            'access-token-missing',
            'refresh-token-missing',
            'oauth-error-secret',
            'person@example.test'
        );
    }

    /**
     * Covers postForm() TypeError retry branch by forcing first string post() call to fail and second array call to succeed.
     */
    public function testExchangeCodeRetriesPostFormWithArrayAfterTypeErrorOnStringPayload(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'retry-client-id');
        DI::config()->set('openidconnect', 'client_secret', 'retry-client-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);
        DI::httpClient()->forceTypeErrorOnFirstStringPost = true;
        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            true,
            json_encode(['access_token' => 'retry-access-token'], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new TokenClient())->exchangeCode('retry-auth-code');

        self::assertSame('retry-access-token', $result['access_token']);
        self::assertCount(2, DI::httpClient()->postCalls);
        self::assertIsString(DI::httpClient()->postCalls[0]['postData']);
        self::assertIsArray(DI::httpClient()->postCalls[1]['postData']);
        $this->assertWarningsAndDebugsDoNotContain(
            'retry-access-token',
            'retry-refresh-token',
            'retry-client-secret',
            'person@example.test'
        );
    }

    /**
     * Covers responseBodySnippet() exception branch via non-success token response whose getBodyString() throws.
     */
    public function testExchangeCodeUsesUnavailableSnippetWhenResponseBodyThrows(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'body-throw-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);

        $response = new TestHttpResponse(false, '{"access_token":"raw-token-should-not-leak"}', 502);
        $response->throwOnGetBodyString = true;
        $response->getBodyStringExceptionMessage = 'body read failed for token raw-token-should-not-leak';
        DI::httpClient()->nextPostResponse = $response;

        $result = (new TokenClient())->exchangeCode('auth-code');

        self::assertSame([], $result);
        $lastError = DI::logger()->errors[array_key_last(DI::logger()->errors)];
        self::assertSame('openidconnect: token endpoint returned non-success response', $lastError[0]);
        $contextEncoded = json_encode($lastError[1], JSON_THROW_ON_ERROR);
        self::assertStringContainsString('[unavailable:', $contextEncoded);
        self::assertStringNotContainsString('raw-token-should-not-leak', $contextEncoded);
        $this->assertWarningsAndDebugsDoNotContain(
            'raw-token-should-not-leak',
            'refresh-token-should-not-leak',
            'body-throw-secret',
            'person@example.test'
        );
    }

    /**
     * Covers oversized OAuth error payload sanitization ensuring long token-like values are redacted and stored snippet remains compact.
     */
    public function testExchangeCodeOversizedOAuthErrorBodyDoesNotLeakTokenAndSnippetCompressed(): void
    {
        $longToken = str_repeat('A', 1600);

        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'oversized-secret');
        DI::cache()->set('openidconnect:provider_config', [
            'token_endpoint' => 'https://id.example/token',
        ], 600);
        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            false,
            'Bearer ' . $longToken . ' rejected for person@example.test',
            400
        );

        $result = (new TokenClient())->exchangeCode('auth-code');

        self::assertSame([], $result);
        $lastError = DI::logger()->errors[array_key_last(DI::logger()->errors)];
        self::assertSame('openidconnect: token endpoint returned non-success response', $lastError[0]);
        self::assertArrayHasKey('body', $lastError[1]);
        self::assertLessThanOrEqual(512, mb_strlen((string)$lastError[1]['body']));
        self::assertStringNotContainsString($longToken, (string)$lastError[1]['body']);
        self::assertStringNotContainsString('person@example.test', (string)$lastError[1]['body']);
        $this->assertWarningsAndDebugsDoNotContain(
            $longToken,
            'refresh-token-not-present',
            'oversized-secret',
            'person@example.test'
        );
    }

    /**
     * Covers revoke() client_secret_basic auth branch ensuring credentials are sent in Authorization header only.
     */
    public function testRevokeClientSecretBasicSendsAuthorizationHeaderOnly(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'revoke-basic-id');
        DI::config()->set('openidconnect', 'client_secret', 'revoke-basic-secret');
        DI::httpClient()->nextPostResponse = new TestHttpResponse(true, '', 200);

        (new TokenClient())->revoke(
            'https://id.example/revoke',
            'revoke-access-token',
            ['revocation_endpoint_auth_methods_supported' => ['client_secret_basic']]
        );

        self::assertCount(1, DI::httpClient()->postCalls);
        $call = DI::httpClient()->postCalls[0];
        self::assertArrayHasKey('Authorization', $call['headers']);
        self::assertStringContainsString('Basic ', $call['headers']['Authorization']);
        self::assertIsString($call['postData']);
        self::assertStringContainsString('token=revoke-access-token', $call['postData']);
        self::assertStringNotContainsString('client_id=', $call['postData']);
        self::assertStringNotContainsString('client_secret=', $call['postData']);
        $this->assertWarningsAndDebugsDoNotContain(
            'revoke-access-token',
            'revoke-refresh-token',
            'revoke-basic-secret',
            'person@example.test'
        );
    }

    /**
     * Covers revoke() client_secret_post auth branch ensuring credentials move to form body and Authorization header is absent.
     */
    public function testRevokeClientSecretPostSendsCredentialsInFormBodyWithoutAuthorization(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'revoke-post-id');
        DI::config()->set('openidconnect', 'client_secret', 'revoke-post-secret');
        DI::httpClient()->nextPostResponse = new TestHttpResponse(true, '', 200);

        (new TokenClient())->revoke(
            'https://id.example/revoke',
            'revoke-access-token-post',
            ['revocation_endpoint_auth_methods_supported' => ['client_secret_post']]
        );

        self::assertCount(1, DI::httpClient()->postCalls);
        $call = DI::httpClient()->postCalls[0];
        self::assertArrayNotHasKey('Authorization', $call['headers']);
        self::assertIsString($call['postData']);
        self::assertStringContainsString('token=revoke-access-token-post', $call['postData']);
        self::assertStringContainsString('client_id=revoke-post-id', $call['postData']);
        self::assertStringContainsString('client_secret=revoke-post-secret', $call['postData']);
        $this->assertWarningsAndDebugsDoNotContain(
            'revoke-access-token-post',
            'revoke-refresh-token-post',
            'revoke-post-secret',
            'person@example.test'
        );
    }

    /**
     * Covers revoke() non-success logging with body containing client_secret-like data to ensure warning/debug context does not leak secrets.
     */
    public function testRevokeFailureBodyContainingClientSecretDoesNotLeakToWarningOrDebugLogs(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'revoke-client-id');
        DI::config()->set('openidconnect', 'client_secret', 'ultra-secret-client-secret');
        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            false,
            '{"client_secret":"ultra-secret-client-secret","email":"person@example.test","access_token":"revoke-access-token"}',
            400
        );

        (new TokenClient())->revoke(
            'https://id.example/revoke',
            'revoke-access-token',
            ['revocation_endpoint_auth_methods_supported' => ['client_secret_post']]
        );

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: revocation endpoint returned non-success', $lastWarning[0]);
        $contextEncoded = json_encode($lastWarning[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('ultra-secret-client-secret', $contextEncoded);
        self::assertStringNotContainsString('revoke-access-token', $contextEncoded);
        self::assertStringNotContainsString('person@example.test', $contextEncoded);
        $this->assertWarningsAndDebugsDoNotContain(
            'revoke-access-token',
            'revoke-refresh-token',
            'ultra-secret-client-secret',
            'person@example.test'
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

    public function testRevokeFailureLogContextDoesNotLeakTokenOrClientSecret(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'ultra-secret-client-secret');
        DI::httpClient()->nextPostResponse = new TestHttpResponse(
            false,
            '{"error":"invalid_token","error_description":"token access-token-123 rejected"}',
            400
        );

        (new TokenClient())->revoke(
            'https://id.example/revoke',
            'access-token-123',
            ['revocation_endpoint_auth_methods_supported' => ['client_secret_post']]
        );

        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: revocation endpoint returned non-success', $lastWarning[0]);
        $contextEncoded = json_encode($lastWarning[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('access-token-123', $contextEncoded);
        self::assertStringNotContainsString('ultra-secret-client-secret', $contextEncoded);
    }

    public function testRevokeExceptionLogContextDoesNotLeakTokenFromExceptionMessage(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'client-secret');
        DI::httpClient()->nextException = new \RuntimeException('timeout for token access-token-123');

        (new TokenClient())->revoke(
            'https://id.example/revoke',
            'access-token-123',
            ['revocation_endpoint_auth_methods_supported' => ['client_secret_post']]
        );

        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: revocation endpoint request threw exception', $lastWarning[0]);
        $contextEncoded = json_encode($lastWarning[1], JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('access-token-123', $contextEncoded);
    }

    private function assertWarningsAndDebugsDoNotContain(
        string $accessToken,
        string $refreshToken,
        string $clientSecret,
        string $email
    ): void {
        $logger = DI::logger();
        $warningsEncoded = json_encode($logger->warnings, JSON_THROW_ON_ERROR);
        $debugsEncoded = json_encode($logger->debugs, JSON_THROW_ON_ERROR);

        self::assertStringNotContainsString($accessToken, $warningsEncoded);
        self::assertStringNotContainsString($refreshToken, $warningsEncoded);
        self::assertStringNotContainsString($clientSecret, $warningsEncoded);
        self::assertStringNotContainsString($email, $warningsEncoded);

        self::assertStringNotContainsString($accessToken, $debugsEncoded);
        self::assertStringNotContainsString($refreshToken, $debugsEncoded);
        self::assertStringNotContainsString($clientSecret, $debugsEncoded);
        self::assertStringNotContainsString($email, $debugsEncoded);
    }
}
