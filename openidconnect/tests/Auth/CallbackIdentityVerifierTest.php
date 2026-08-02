<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Firebase\JWT\JWT;
use Friendica\Addon\OpenIdConnect\Auth\CallbackIdentityVerifier;
use Friendica\Addon\OpenIdConnect\Identity\UserInfo;
use Friendica\Addon\OpenIdConnect\Provider\IdTokenValidator;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;
use Friendica\TestHttpResponse;

final class CallbackIdentityVerifierTest extends AddonTestCase
{
    public function testVerifyRejectsUnverifiedEmailWhenNotAllowed(): void
    {
        DI::cache()->set('openidconnect:provider_config', [
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-1',
                'email' => 'person@example.test',
                'email_verified' => false,
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new CallbackIdentityVerifier(null, new UserInfo(new ProviderConfiguration()), new ProviderConfiguration()))
            ->verify(['access_token' => 'access-token'], []);

        self::assertSame([], $result);
        self::assertSame('login?openidconnect_no_auto=1', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('not been verified', implode(' ', DI::sysmsg()->notices));
    }

    public function testVerifyAcceptsStringEmailVerifiedTrueAndReturnsUserinfo(): void
    {
        DI::cache()->set('openidconnect:provider_config', [
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-1',
                'email' => 'person@example.test',
                'email_verified' => 'true',
                'preferred_username' => 'person',
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new CallbackIdentityVerifier(null, new UserInfo(new ProviderConfiguration()), new ProviderConfiguration()))
            ->verify(['access_token' => 'access-token'], []);

        self::assertSame('person@example.test', $result['email']);
        self::assertSame('sub-1', $result['sub']);
        self::assertNull(DI::baseUrl()->lastRedirect());
    }

    public function testVerifyRejectsPayloadWithoutEmail(): void
    {
        DI::cache()->set('openidconnect:provider_config', [
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-1',
                'email_verified' => true,
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new CallbackIdentityVerifier(null, new UserInfo(new ProviderConfiguration()), new ProviderConfiguration()))
            ->verify(['access_token' => 'access-token'], []);

        self::assertSame([], $result);
        self::assertSame('login?openidconnect_no_auto=1', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('Email address not provided', implode(' ', DI::sysmsg()->notices));
    }

    public function testVerifyUnverifiedEmailWarningDoesNotLogRawEmailAddress(): void
    {
        DI::cache()->set('openidconnect:provider_config', [
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-1',
                'email' => 'sensitive.person@example.test',
                'email_verified' => false,
            ], JSON_THROW_ON_ERROR),
            200
        );

        (new CallbackIdentityVerifier(null, new UserInfo(new ProviderConfiguration()), new ProviderConfiguration()))
            ->verify(['access_token' => 'access-token'], []);

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: email not verified by IdP', $lastWarning[0]);
        self::assertArrayNotHasKey('email', $lastWarning[1]);
        self::assertStringNotContainsString('sensitive.person@example.test', json_encode($lastWarning[1], JSON_THROW_ON_ERROR));
    }

    public function testVerifyRejectsInvalidIdentityTokenAndRedirectsToLogin(): void
    {
        $rawEmail = 'private.person@example.test';
        $rawSub = 'subject-private-1';
        $rawAccessToken = 'access-token-private-1';

        $idToken = $this->issueIdToken([
            'sub' => $rawSub,
            'email' => $rawEmail,
            'exp' => time() - 120,
        ]);

        $result = (new CallbackIdentityVerifier())
            ->verify([
                'access_token' => $rawAccessToken,
                'id_token' => $idToken,
            ], [
                'nonce' => 'nonce-1',
            ]);

        self::assertSame([], $result);
        self::assertSame('login?openidconnect_no_auto=1', DI::baseUrl()->lastRedirect());
        self::assertStringContainsString('invalid identity token', implode(' ', DI::sysmsg()->notices));

        $this->assertNoSensitiveValuesInLogs($rawEmail, $rawSub, $rawAccessToken);
        $this->assertNoSensitiveValuesInLogs($idToken);
    }

    public function testVerifyFallsBackToIdTokenClaimsWhenUserinfoFetchThrows(): void
    {
        $rawEmail = 'fallback.person@example.test';
        $rawSub = 'subject-fallback-1';
        $rawAccessToken = 'access-token-fallback-1';

        $idToken = $this->issueIdToken([
            'sub' => $rawSub,
            'email' => $rawEmail,
            'email_verified' => true,
            'name' => 'Fallback Person',
            'preferred_username' => 'fallback-person',
        ]);

        /** @var UserInfo $brokenUserInfo */
        $brokenUserInfo = (new \ReflectionClass(UserInfo::class))->newInstanceWithoutConstructor();

        $result = (new CallbackIdentityVerifier(null, $brokenUserInfo, new ProviderConfiguration()))
            ->verify([
                'access_token' => $rawAccessToken,
                'id_token' => $idToken,
            ], [
                'nonce' => 'nonce-1',
            ]);

        self::assertSame($rawEmail, $result['email']);
        self::assertSame($rawSub, $result['sub']);
        self::assertSame('fallback-person', $result['preferred_username']);
        self::assertNull(DI::baseUrl()->lastRedirect());

        self::assertNotEmpty(DI::logger()->warnings);
        self::assertSame(
            'openidconnect: userinfo endpoint unavailable or unusable, falling back to id_token claims',
            DI::logger()->warnings[array_key_last(DI::logger()->warnings)][0]
        );

        $this->assertNoSensitiveValuesInLogs($rawEmail, $rawSub, $rawAccessToken, $idToken);
    }

    public function testVerifyRejectsWhenNoUserinfoCanBeRetrievedAndNoValidatedIdTokenExists(): void
    {
        $rawEmail = 'nobody@example.test';
        $rawSub = 'no-subject';
        $rawAccessToken = 'access-token-no-userinfo';
        $rawIp = '203.0.113.44';

        $result = (new CallbackIdentityVerifier())
            ->verify([
                'access_token' => $rawAccessToken,
            ], []);

        self::assertSame([], $result);
        self::assertSame('login?openidconnect_no_auto=1', DI::baseUrl()->lastRedirect());
        self::assertStringContainsString('could not retrieve user info', implode(' ', DI::sysmsg()->notices));

        $this->assertNoSensitiveValuesInLogs($rawEmail, $rawSub, $rawAccessToken, $rawIp);
    }

    public function testVerifyAcceptsFalseEmailVerifiedWhenAllowUnverifiedEmailIsEnabled(): void
    {
        DI::config()->set('openidconnect', 'allow_unverified_email', true);
        DI::cache()->set('openidconnect:provider_config', [
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-allow-unverified',
                'email' => 'allow-unverified@example.test',
                'email_verified' => false,
                'preferred_username' => 'allow-unverified',
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new CallbackIdentityVerifier())
            ->verify(['access_token' => 'access-token'], []);

        self::assertSame('allow-unverified@example.test', $result['email']);
        self::assertSame('sub-allow-unverified', $result['sub']);
        self::assertNull(DI::baseUrl()->lastRedirect());
    }

    public function testVerifyRejectsMismatchedSubjectBetweenIdTokenAndUserinfo(): void
    {
        $rawEmail = 'mismatch.person@example.test';
        $rawSub = 'userinfo-subject';
        $rawAccessToken = 'access-token-mismatch-1';

        $idToken = $this->issueIdToken([
            'sub' => 'id-token-subject',
            'email' => $rawEmail,
            'email_verified' => true,
        ]);

        DI::cache()->set('openidconnect:provider_config', [
            'jwks_uri' => 'https://id.example/jwks',
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => $rawSub,
                'email' => $rawEmail,
                'email_verified' => true,
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new CallbackIdentityVerifier())
            ->verify([
                'access_token' => $rawAccessToken,
                'id_token' => $idToken,
            ], [
                'nonce' => 'nonce-1',
            ]);

        self::assertSame([], $result);
        self::assertSame('login?openidconnect_no_auto=1', DI::baseUrl()->lastRedirect());
        self::assertStringContainsString('inconsistent provider identity', implode(' ', DI::sysmsg()->notices));

        self::assertNotEmpty(DI::logger()->warnings);
        self::assertSame(
            'openidconnect: id_token sub and userinfo sub mismatch',
            DI::logger()->warnings[array_key_last(DI::logger()->warnings)][0]
        );

        $this->assertNoSensitiveValuesInLogs($rawEmail, $rawSub, $rawAccessToken, $idToken);
    }

    public function testVerifyNormalizesMalformedEmailVerifiedSignal(): void
    {
        DI::cache()->set('openidconnect:provider_config', [
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-malformed-0',
                'email' => 'malformed-0@example.test',
                'email_verified' => '0',
            ], JSON_THROW_ON_ERROR),
            200
        );

        $rejectedResult = (new CallbackIdentityVerifier())->verify(['access_token' => 'access-token'], []);
        self::assertSame([], $rejectedResult);
        self::assertSame('login?openidconnect_no_auto=1', DI::baseUrl()->lastRedirect());

        DI::resetTestState();

        DI::cache()->set('openidconnect:provider_config', [
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-malformed-gibberish',
                'email' => 'malformed-gibberish@example.test',
                'email_verified' => 'gibberish',
            ], JSON_THROW_ON_ERROR),
            200
        );

        $acceptedResult = (new CallbackIdentityVerifier())->verify(['access_token' => 'access-token'], []);
        self::assertSame('malformed-gibberish@example.test', $acceptedResult['email']);
        self::assertSame('sub-malformed-gibberish', $acceptedResult['sub']);
        self::assertNull(DI::baseUrl()->lastRedirect());
    }

    private function assertNoSensitiveValuesInLogs(string ...$needles): void
    {
        $allLogEntries = [
            ...DI::logger()->debugs,
            ...DI::logger()->warnings,
            ...DI::logger()->errors,
            ...DI::logger()->infos,
        ];

        foreach ($allLogEntries as $logEntry) {
            $message = is_string($logEntry) ? $logEntry : json_encode($logEntry, JSON_THROW_ON_ERROR);
            foreach ($needles as $needle) {
                if ($needle === '') {
                    continue;
                }
                self::assertStringNotContainsString($needle, $message);
            }
        }
    }

    private function issueIdToken(array $overrides = []): string
    {
        [$privateKey, $jwk] = $this->generateRsaMaterial();

        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::cache()->set('openidconnect:provider_config', [
            'jwks_uri' => 'https://id.example/jwks',
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::cache()->set('openidconnect:jwks', ['keys' => [$jwk]], 600);

        $claims = [
            'iss' => 'https://id.example',
            'aud' => 'client-id',
            'sub' => 'subject-default',
            'email' => 'default@example.test',
            'email_verified' => true,
            'nonce' => 'nonce-1',
            'iat' => time() - 5,
            'nbf' => time() - 5,
            'exp' => time() + 300,
        ];

        foreach ($overrides as $key => $value) {
            $claims[$key] = $value;
        }

        return JWT::encode($claims, $privateKey, 'RS256', $jwk['kid']);
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
