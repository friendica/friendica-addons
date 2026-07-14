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

        self::assertFalse((new IdTokenValidator())->validate($token, '', 'different-access-token'));
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
}
