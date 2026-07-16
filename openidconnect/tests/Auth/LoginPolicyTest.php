<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class LoginPolicyTest extends AddonTestCase
{
    public function testSanitizeReturnPathKeepsRelativePath(): void
    {
        self::assertSame('settings/account', LoginPolicy::sanitizeReturnPath('settings/account'));
        self::assertSame('oauth/authorize?client_id=test', LoginPolicy::sanitizeReturnPath('/oauth/authorize?client_id=test'));
    }

    public function testSanitizeReturnPathRejectsAbsoluteUrlsAndCustomSchemes(): void
    {
        self::assertSame('', LoginPolicy::sanitizeReturnPath('https://evil.example/callback'));
        self::assertSame('', LoginPolicy::sanitizeReturnPath('//evil.example/callback'));
        self::assertSame('', LoginPolicy::sanitizeReturnPath('mona://oauth'));
    }

    public function testGenerateNonceReturnsExpectedLengthAndHex(): void
    {
        $nonce = LoginPolicy::generateNonce();

        self::assertSame(LoginPolicy::NONCE_BYTES * 2, strlen($nonce));
        self::assertMatchesRegularExpression('/^[a-f0-9]+$/', $nonce);
    }

    public function testGeneratePkceVerifierAndChallengeUseBase64UrlAlphabet(): void
    {
        $verifier = LoginPolicy::generatePkceVerifier();
        $challenge = LoginPolicy::generatePkceChallenge($verifier);

        self::assertMatchesRegularExpression('/^[A-Za-z0-9\-_]+$/', $verifier);
        self::assertMatchesRegularExpression('/^[A-Za-z0-9\-_]+$/', $challenge);
        self::assertNotSame($verifier, $challenge);
    }

    public function testBuildsPkceS256Challenge(): void
    {
        self::assertSame(
            '-Wu4dbm6BXgYdq6QbSnPT9l6jlIRvrzI_Ywcmj4u0Ys',
            LoginPolicy::generatePkceChallenge('dBjftJeZ4CVP-m1GwdJdC1wV_N9dcVgM2T9JHhSwbLc')
        );
    }

    public function testShouldAutoRedirectLoginRequiresTransparentSsoEnabled(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        self::assertFalse(LoginPolicy::shouldAutoRedirect([], $_SERVER, false));

        self::assertTrue(LoginPolicy::shouldAutoRedirect([], $_SERVER, true));
    }

    public function testShouldAutoRedirectLoginRejectsBearerRequestsAndFallbackFlag(): void
    {
        self::assertFalse(LoginPolicy::shouldAutoRedirect(
            ['openidconnect_no_auto' => '1'],
            ['REQUEST_METHOD' => 'GET'],
            true
        ));

        self::assertFalse(LoginPolicy::shouldAutoRedirect(
            [],
            [
                'REQUEST_METHOD' => 'GET',
                'HTTP_AUTHORIZATION' => 'Bearer test-token',
            ],
            true
        ));

        self::assertFalse(LoginPolicy::shouldAutoRedirect([], ['REQUEST_METHOD' => 'POST'], true));
    }

    public function testShouldAutoRedirectLoginRejectsWhenLogoutNoAutoCookieIsPresent(): void
    {
        $_COOKIE[LoginPolicy::LOGOUT_NO_AUTO_COOKIE] = '1';

        self::assertFalse(LoginPolicy::shouldAutoRedirect([], ['REQUEST_METHOD' => 'GET'], true));

        unset($_COOKIE[LoginPolicy::LOGOUT_NO_AUTO_COOKIE]);
    }

    public function testIsBearerRequestOnlyUsesCanonicalServerHeader(): void
    {
        self::assertTrue(LoginPolicy::isBearerRequest(['HTTP_AUTHORIZATION' => 'Bearer test-token']));
        self::assertFalse(LoginPolicy::isBearerRequest(['Authorization' => 'Bearer test-token']));
        self::assertFalse(LoginPolicy::isBearerRequest([]));
    }

    public function testBuildLoginFallbackPathDisablesAutoRedirectAndPreservesReturnPath(): void
    {
        self::assertSame('login?openidconnect_no_auto=1', LoginPolicy::buildFallbackPath(''));
        self::assertSame(
            'login?openidconnect_no_auto=1&return_path=oauth%2Fauthorize%3Fclient_id%3Dtest',
            LoginPolicy::buildFallbackPath('oauth/authorize?client_id=test')
        );
    }
}
