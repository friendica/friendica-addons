<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Provider;

use Firebase\JWT\JWT;
use Friendica\Addon\OpenIdConnect\Provider\IdTokenValidator;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class IdTokenValidatorTest extends AddonTestCase
{
    public function testComputesOidcAccessTokenHash(): void
    {
        self::assertSame(
            '-Wu4dbm6BXgYdq6QbSnPTw',
            IdTokenValidator::accessTokenHash('dBjftJeZ4CVP-m1GwdJdC1wV_N9dcVgM2T9JHhSwbLc')
        );
    }

    public function testAcceptsClientIdInScalarOrArrayAudience(): void
    {
        self::assertTrue(IdTokenValidator::hasAudience('client-id', 'client-id'));
        self::assertTrue(IdTokenValidator::hasAudience(['other', 'client-id'], 'client-id'));
        self::assertFalse(IdTokenValidator::hasAudience(['other'], 'client-id'));
    }

    public function testRejectsIdTokenUsingNonAllowlistedAlgorithm(): void
    {
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);

        $validator = new IdTokenValidator();
        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'exp' => time() + 300,
        ], str_repeat('s', 32), 'HS256');

        self::assertFalse($validator->validate($token));
    }

    public function testRejectsEmptyIdTokenAndLogsWarning(): void
    {
        $result = (new IdTokenValidator())->validate('');

        self::assertFalse($result);
        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: id_token missing from token response', $lastWarning[0]);
        self::assertSame([], $lastWarning[1]);
    }

    public function testRejectsMissingJwksUriFromDiscoveryConfiguration(): void
    {
        DI::cache()->set('openidconnect:provider_config', ['issuer' => 'https://id.example'], 600);

        $result = (new IdTokenValidator())->validate($this->createCompactJwt([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'exp' => time() + 300,
        ]));

        self::assertFalse($result);
    }

    public function testFetchesJwksOnCacheMissAndCachesResult(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::httpClient()->nextGetResponse = new \Friendica\TestHttpResponse(
            true,
            json_encode(['keys' => [$jwk]], JSON_THROW_ON_ERROR),
            200
        );

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'subject-1',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $privateKey, 'RS256', $jwk['kid']);

        $decoded = (new IdTokenValidator())->validate($token);

        self::assertIsObject($decoded);
        self::assertCount(1, DI::httpClient()->getCalls);

        $jwksSetCallFound = false;
        foreach (DI::cache()->setCalls as $setCall) {
            if ($setCall['key'] === 'openidconnect:jwks') {
                $jwksSetCallFound = true;
                self::assertSame(['keys' => [$jwk]], $setCall['value']);
            }
        }

        self::assertTrue($jwksSetCallFound);
    }

    public function testReturnsFalseWhenJwksFetchThrowsHttpException(): void
    {
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::httpClient()->nextException = new \RuntimeException('jwks fetch failed');

        $result = (new IdTokenValidator())->validate($this->createCompactJwt([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'exp' => time() + 300,
        ]));

        self::assertFalse($result);
        self::assertCount(1, DI::httpClient()->getCalls);
    }

    public function testReturnsFalseWhenJwksResponseIsNotSuccessful(): void
    {
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::httpClient()->nextGetResponse = new \Friendica\TestHttpResponse(false, '', 500);

        $result = (new IdTokenValidator())->validate($this->createCompactJwt([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'exp' => time() + 300,
        ]));

        self::assertFalse($result);
        self::assertCount(1, DI::httpClient()->getCalls);
    }

    public function testReturnsFalseWhenJwksResponseBodyIsMalformedJson(): void
    {
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::httpClient()->nextGetResponse = new \Friendica\TestHttpResponse(true, 'not json', 200);

        $result = (new IdTokenValidator())->validate($this->createCompactJwt([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'exp' => time() + 300,
        ]));

        self::assertFalse($result);
        self::assertCount(1, DI::httpClient()->getCalls);
    }

    public function testReturnsFalseWhenJwksResponseMissingKeysArray(): void
    {
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::httpClient()->nextGetResponse = new \Friendica\TestHttpResponse(
            true,
            json_encode(['foo' => 'bar'], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new IdTokenValidator())->validate($this->createCompactJwt([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'exp' => time() + 300,
        ]));

        self::assertFalse($result);
        self::assertCount(1, DI::httpClient()->getCalls);
    }

    public function testRejectsMalformedCompactJwtHeader(): void
    {
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);

        self::assertFalse((new IdTokenValidator())->validate('notajwt'));
        self::assertSame([], DI::httpClient()->getCalls);
    }

    public function testRejectsTokenWithoutAlgHeader(): void
    {
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);

        $token = $this->createCompactJwt([
            'typ' => 'JWT',
        ], [
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'exp' => time() + 300,
        ]);

        self::assertFalse((new IdTokenValidator())->validate($token));
        self::assertSame([], DI::httpClient()->getCalls);
    }

    public function testRejectsInvalidSignatureEvenAfterJwksRefresh(): void
    {
        [$tokenPrivateKey, $tokenJwk] = $this->generateRsaMaterial();
        [, $staleJwk] = $this->generateRsaMaterial();
        [, $refreshedJwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$staleJwk]], 600);
        DI::httpClient()->nextGetResponse = new \Friendica\TestHttpResponse(
            true,
            json_encode(['keys' => [$refreshedJwk]], JSON_THROW_ON_ERROR),
            200
        );

        $idToken = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'subject-1',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $tokenPrivateKey, 'RS256', $tokenJwk['kid']);

        $result = (new IdTokenValidator())->validate($idToken, '', 'sensitive-access-token-value');

        self::assertFalse($result);
        self::assertCount(1, DI::httpClient()->getCalls);
        self::assertNotEmpty(DI::logger()->warnings);
        self::assertContains(
            DI::logger()->warnings[array_key_last(DI::logger()->warnings)][0],
            [
                'openidconnect: id_token signature invalid after JWKS refresh',
                'openidconnect: id_token malformed',
            ]
        );
        $this->assertLogsDoNotContain($idToken, 'sensitive-access-token-value');
    }

    public function testRejectsUnexpectedValueExceptionWithoutRetry(): void
    {
        [, $jwk] = $this->generateRsaMaterial();

        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = $this->createCompactJwt([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'iat' => 'not-a-number',
            'exp' => time() + 300,
        ]);

        $result = (new IdTokenValidator())->validate($token);

        self::assertFalse($result);
        self::assertSame([], DI::httpClient()->getCalls);
        self::assertSame([], DI::cache()->deleteCalls);
        self::assertNotEmpty(DI::logger()->warnings);
        self::assertSame('openidconnect: id_token malformed', DI::logger()->warnings[array_key_last(DI::logger()->warnings)][0]);
    }

    public function testUsesWarmCachedJwksWithoutFetchingAgain(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'subject-1',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $privateKey, 'RS256', $jwk['kid']);

        self::assertIsObject((new IdTokenValidator())->validate($token));
        self::assertSame([], DI::httpClient()->getCalls);
    }

    public function testRefreshesStaleCachedJwksAfterSignatureFailureAndSucceeds(): void
    {
        [, $staleJwk] = $this->generateRsaMaterial();
        [$freshPrivateKey, $freshJwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$staleJwk]], 600);
        DI::httpClient()->nextGetResponse = new \Friendica\TestHttpResponse(
            true,
            json_encode(['keys' => [$freshJwk]], JSON_THROW_ON_ERROR),
            200
        );

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'subject-1',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $freshPrivateKey, 'RS256', $freshJwk['kid']);

        $decoded = (new IdTokenValidator())->validate($token);

        self::assertIsObject($decoded);
        self::assertSame('subject-1', $decoded->sub);
        self::assertCount(1, DI::httpClient()->getCalls);
        self::assertNotEmpty(DI::logger()->warnings);
        self::assertSame(
            'openidconnect: signature invalid — busting JWKS cache and retrying',
            DI::logger()->warnings[array_key_last(DI::logger()->warnings)][0]
        );
    }

    public function testReturnsNullWhenSignatureInvalidAndJwksRefreshFails(): void
    {
        [$tokenPrivateKey, $tokenJwk] = $this->generateRsaMaterial();
        [, $staleJwk] = $this->generateRsaMaterial();
        $staleJwk['kid'] = $tokenJwk['kid'];

        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$staleJwk]], 600);
        DI::httpClient()->nextGetResponse = new \Friendica\TestHttpResponse(false, '', 500);

        $idToken = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'subject-refresh-fail',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $tokenPrivateKey, 'RS256', $tokenJwk['kid']);

        $result = (new IdTokenValidator())->validate($idToken);

        self::assertFalse($result);
        self::assertCount(1, DI::httpClient()->getCalls);
        self::assertNotContains(
            'openidconnect: id_token signature invalid after JWKS refresh',
            array_map(static fn (array $entry): string => $entry[0], DI::logger()->warnings)
        );
    }

    public function testRejectsTokenWhenSignatureRemainsInvalidAfterJwksRefresh(): void
    {
        [$tokenPrivateKey, $tokenJwk] = $this->generateRsaMaterial();
        [, $staleJwk] = $this->generateRsaMaterial();
        [, $refreshedJwk] = $this->generateRsaMaterial();
        $staleJwk['kid'] = $tokenJwk['kid'];
        $refreshedJwk['kid'] = $tokenJwk['kid'];

        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$staleJwk]], 600);
        DI::httpClient()->nextGetResponse = new \Friendica\TestHttpResponse(
            true,
            json_encode(['keys' => [$refreshedJwk]], JSON_THROW_ON_ERROR),
            200
        );

        $idToken = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'subject-signature-still-invalid',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $tokenPrivateKey, 'RS256', $tokenJwk['kid']);

        $result = (new IdTokenValidator())->validate($idToken);

        self::assertFalse($result);
        self::assertCount(1, DI::httpClient()->getCalls);
        self::assertContains(
            'openidconnect: id_token signature invalid after JWKS refresh',
            array_map(static fn (array $entry): string => $entry[0], DI::logger()->warnings)
        );
    }

    public function testRejectsTokenWhenUnexpectedValueAndJwksRefreshFails(): void
    {
        [$tokenPrivateKey, $tokenJwk] = $this->generateRsaMaterial();
        [, $staleJwk] = $this->generateRsaMaterial();

        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$staleJwk]], 600);
        DI::httpClient()->nextGetResponse = new \Friendica\TestHttpResponse(false, '', 500);

        $idToken = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'subject-unexpected-value-refresh-fail',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $tokenPrivateKey, 'RS256', $tokenJwk['kid']);

        $result = (new IdTokenValidator())->validate($idToken);

        self::assertFalse($result);
        self::assertCount(1, DI::httpClient()->getCalls);
        self::assertSame(['openidconnect:jwks'], DI::cache()->deleteCalls);
        self::assertNotContains(
            'openidconnect: id_token malformed',
            array_map(static fn (array $entry): string => $entry[0], DI::logger()->warnings)
        );
    }

    public function testReturnsNullWhenJwkConfigThrowsInvalidArgument(): void
    {
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => []], 600);

        $token = $this->createCompactJwt([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'jwk-invalid-argument-branch',
            'exp' => time() + 300,
        ]);

        $result = (new IdTokenValidator())->validate($token);

        self::assertFalse($result);
        self::assertNotEmpty(DI::logger()->errors);
        self::assertSame(
            'openidconnect: JWKS key configuration error',
            DI::logger()->errors[array_key_last(DI::logger()->errors)][0]
        );
        $this->assertLogsDoNotContain($token, 'jwk-invalid-argument-branch');
    }

    public function testValidatesSuccessfullyWhenAtHashMatches(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();
        $accessToken = 'access-token-for-at-hash-success';

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'subject-at-hash-match',
            'at_hash' => IdTokenValidator::accessTokenHash($accessToken),
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $privateKey, 'RS256', $jwk['kid']);

        $decoded = (new IdTokenValidator())->validate($token, '', $accessToken);

        self::assertIsObject($decoded);
        self::assertSame('subject-at-hash-match', $decoded->sub);
    }

    public function testFetchJwksWorksWhenHttpClientOptionsTimeoutExists(): void
    {
        if (!class_exists(\Friendica\Network\HTTPClient\Client\HttpClientOptions::class, false)) {
            eval('namespace Friendica\\Network\\HTTPClient\\Client; final class HttpClientOptions { public const TIMEOUT = "timeout"; }');
        }

        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::httpClient()->nextGetResponse = new \Friendica\TestHttpResponse(
            true,
            json_encode(['keys' => []], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new IdTokenValidator())->validate($this->createCompactJwt([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'exp' => time() + 300,
        ]));

        self::assertFalse($result);
        self::assertCount(1, DI::httpClient()->getCalls);
        self::assertArrayHasKey('timeout', DI::httpClient()->getCalls[0]['options']);
        self::assertSame(15, DI::httpClient()->getCalls[0]['options']['timeout']);
    }

    public function testExtractAlgorithmReturnsFalseWhenHeaderIsNotValidBase64(): void
    {
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);

        $result = (new IdTokenValidator())->validate('!!!.payload.sig');

        self::assertFalse($result);
        self::assertSame([], DI::httpClient()->getCalls);
    }

    public function testValidateRestoresGlobalJwtLeewayAfterDecode(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'subject-1',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $privateKey, 'RS256', $jwk['kid']);

        JWT::$leeway = 7;
        $validator = new IdTokenValidator();

        self::assertIsObject($validator->validate($token));
        self::assertSame(7, JWT::$leeway);
    }

    public function testRejectsIssuerMismatch(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://evil.example',
            'aud' => 'client-id',
            'sub' => 'subject-1',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $privateKey, 'RS256', $jwk['kid']);

        self::assertFalse((new IdTokenValidator())->validate($token));
    }

    public function testRejectsIssueMismatch(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://rogue.example',
            'aud' => 'client-id',
            'sub' => 'subject-issue-mismatch',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $privateKey, 'RS256', $jwk['kid']);

        self::assertFalse((new IdTokenValidator())->validate($token));
    }

    public function testRejectsAudienceMismatch(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => ['other-client'],
            'sub' => 'subject-1',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $privateKey, 'RS256', $jwk['kid']);

        self::assertFalse((new IdTokenValidator())->validate($token));
    }

    public function testRejectsNonceMismatchWhenNonceIsExpected(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'nonce' => 'nonce-from-token',
            'sub' => 'subject-1',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $privateKey, 'RS256', $jwk['kid']);

        self::assertFalse((new IdTokenValidator())->validate($token, 'different-nonce'));
    }

    public function testRejectsNonceMismatch(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'nonce' => 'nonce-in-token',
            'sub' => 'subject-1',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $privateKey, 'RS256', $jwk['kid']);

        self::assertFalse((new IdTokenValidator())->validate($token, 'expected-nonce'));
    }

    public function testRejectsAccessTokenHashMismatch(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'at_hash' => IdTokenValidator::accessTokenHash('expected-access-token'),
            'sub' => 'subject-1',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $privateKey, 'RS256', $jwk['kid']);

        $accessToken = 'different-access-token';
        $result = (new IdTokenValidator())->validate($token, '', $accessToken);

        self::assertFalse($result);
        $this->assertLogsDoNotContain($accessToken, $token);
    }

    public function testRejectsAzpMismatchForMultiAudienceToken(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => ['client-id', 'other-client'],
            'azp' => 'wrong-client',
            'sub' => 'subject-1',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $privateKey, 'RS256', $jwk['kid']);

        self::assertFalse((new IdTokenValidator())->validate($token));
    }

    public function testRejectsExpiredToken(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'subject-1',
            'iat' => time() - 3600,
            'nbf' => time() - 3600,
            'exp' => time() - 120,
        ], $privateKey, 'RS256', $jwk['kid']);

        self::assertFalse((new IdTokenValidator())->validate($token));
        self::assertNotEmpty(DI::logger()->warnings);
        self::assertSame('openidconnect: id_token expired', DI::logger()->warnings[array_key_last(DI::logger()->warnings)][0]);
    }

    public function testRejectsNotYetValidToken(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'subject-future',
            'iat' => time() + 3600,
            'nbf' => time() + 3600,
            'exp' => time() + 7200,
        ], $privateKey, 'RS256', $jwk['kid']);

        self::assertFalse((new IdTokenValidator())->validate($token));
        self::assertNotEmpty(DI::logger()->warnings);
        self::assertSame('openidconnect: id_token not yet valid', DI::logger()->warnings[array_key_last(DI::logger()->warnings)][0]);
    }

    public function testLoggerNeverLeaksRawTokenMaterial(): void
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();
        $secretSub = 'sub-with-secret-claim-material';

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $token = JWT::encode([
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'nonce' => 'nonce-in-token',
            'sub' => $secretSub,
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ], $privateKey, 'RS256', $jwk['kid']);

        self::assertFalse((new IdTokenValidator())->validate($token, 'different-nonce'));
        $this->assertLogsDoNotContain($token, $secretSub);
    }

    public function testReturnsFalseWhenJwkParsingFailsDueToInvalidKeyConfig(): void
    {
        DI::cache()->set('openidconnect:provider_config', ['jwks_uri' => 'https://id.example/jwks'], 600);
        DI::cache()->set('openidconnect:jwks', [
            'keys' => [[
                'kty' => 'RSA',
                'kid' => 'broken-key',
                'alg' => 'RS256',
                'e' => 'AQAB',
            ]],
        ], 600);

        $token = $this->createCompactJwt([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], [
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'exp' => time() + 300,
        ]);

        self::assertFalse((new IdTokenValidator())->validate($token));
    }

    public function testValidatorDoesNotUseStaticSharedRetryFlag(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Provider/IdTokenValidator.php');

        self::assertIsString($source);
        self::assertStringNotContainsString('static $jwksRetried', $source);
    }

    /**
     * @return array{0: string, 1: array<string, string>}
     */
    private function generateRsaMaterial(): array
    {
        $resource = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);
        self::assertNotFalse($resource);

        $privateKey = '';
        self::assertTrue(openssl_pkey_export($resource, $privateKey));
        $details = openssl_pkey_get_details($resource);

        self::assertIsArray($details);
        self::assertArrayHasKey('rsa', $details);
        self::assertIsArray($details['rsa']);

        /** @var array{n: string, e: string} $rsa */
        $rsa = $details['rsa'];

        return [
            $privateKey,
            [
                'kty' => 'RSA',
                'use' => 'sig',
                'alg' => 'RS256',
                'kid' => 'kid-' . bin2hex(random_bytes(4)),
                'n' => $this->base64UrlEncode($rsa['n']),
                'e' => $this->base64UrlEncode($rsa['e']),
            ],
        ];
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * @param array<string, mixed> $header
     * @param array<string, mixed> $payload
     */
    private function createCompactJwt(array $header, array $payload): string
    {
        $headerPart = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $payloadPart = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));

        return $headerPart . '.' . $payloadPart . '.' . $this->base64UrlEncode('signature');
    }

    private function assertLogsDoNotContain(string ...$needles): void
    {
        $logger = DI::logger();
        $entries = array_merge($logger->debugs, $logger->warnings, $logger->errors, $logger->infos);
        $serializedEntries = json_encode($entries, JSON_THROW_ON_ERROR);

        foreach ($needles as $needle) {
            self::assertStringNotContainsString($needle, $serializedEntries);
        }
    }
}
