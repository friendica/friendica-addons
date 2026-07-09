<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests;

use Friendica\DI;
use PHPUnit\Framework\TestCase;

final class OpenIdConnectTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		DI::resetTestState();
	}

	public function testSanitizeReturnPathKeepsRelativePath(): void
	{
		self::assertSame('settings/account', openidconnect_sanitize_return_path('settings/account'));
		self::assertSame('oauth/authorize?client_id=test', openidconnect_sanitize_return_path('/oauth/authorize?client_id=test'));
	}

	public function testSanitizeReturnPathRejectsAbsoluteUrlsAndCustomSchemes(): void
	{
		self::assertSame('', openidconnect_sanitize_return_path('https://evil.example/callback'));
		self::assertSame('', openidconnect_sanitize_return_path('//evil.example/callback'));
		self::assertSame('', openidconnect_sanitize_return_path('mona://oauth'));
	}

	public function testGenerateNonceReturnsExpectedLengthAndHex(): void
	{
		$nonce = openidconnect_generate_nonce();

		self::assertSame(OIDC_NONCE_LENGTH * 2, strlen($nonce));
		self::assertMatchesRegularExpression('/^[a-f0-9]+$/', $nonce);
	}

	public function testGeneratePkceVerifierAndChallengeUseBase64UrlAlphabet(): void
	{
		$verifier = openidconnect_generate_pkce_verifier();
		$challenge = openidconnect_generate_pkce_challenge($verifier);

		self::assertMatchesRegularExpression('/^[A-Za-z0-9\-_]+$/', $verifier);
		self::assertMatchesRegularExpression('/^[A-Za-z0-9\-_]+$/', $challenge);
		self::assertNotSame($verifier, $challenge);
	}

	public function testGetClientAuthMethodPrefersClientSecretBasic(): void
	{
		$config = ['token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic']];

		self::assertSame('client_secret_basic', openidconnect_get_client_auth_method($config));
	}

	public function testGetClientAuthMethodFallsBackToClientSecretPost(): void
	{
		$config = ['token_endpoint_auth_methods_supported' => ['client_secret_post']];

		self::assertSame('client_secret_post', openidconnect_get_client_auth_method($config));
	}

	public function testIsSafeUrlAllowsSameHostAsConfiguredDiscoveryUrlEvenOnPrivateNetwork(): void
	{
		DI::config()->set('openidconnect', 'discovery_url', 'http://authentik-server.friendica-local-dev.orb.local:9000/application/o/demo/.well-known/openid-configuration');

		self::assertTrue(openidconnect_is_safe_url('http://authentik-server.friendica-local-dev.orb.local:9000/media/avatar.png'));
	}

	public function testIsSafeUrlRejectsNonHttpsThirdPartyHost(): void
	{
		DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

		self::assertFalse(openidconnect_is_safe_url('http://cdn.example.com/avatar.png'));
	}

	public function testShouldAutoRedirectLoginRequiresTransparentSsoEnabled(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		self::assertFalse(openidconnect_should_auto_redirect_login([], $_SERVER));

		DI::config()->set('openidconnect', 'transparent_sso', true);

		self::assertTrue(openidconnect_should_auto_redirect_login([], $_SERVER));
	}

	public function testShouldAutoRedirectLoginRejectsBearerRequestsAndFallbackFlag(): void
	{
		DI::config()->set('openidconnect', 'transparent_sso', true);

		self::assertFalse(openidconnect_should_auto_redirect_login(
			['openidconnect_no_auto' => '1'],
			['REQUEST_METHOD' => 'GET']
		));

		self::assertFalse(openidconnect_should_auto_redirect_login(
			[],
			[
				'REQUEST_METHOD' => 'GET',
				'HTTP_AUTHORIZATION' => 'Bearer test-token',
			]
		));

		self::assertFalse(openidconnect_should_auto_redirect_login([], ['REQUEST_METHOD' => 'POST']));
	}

	public function testIsBearerRequestOnlyUsesCanonicalServerHeader(): void
	{
		self::assertTrue(openidconnect_is_bearer_request(['HTTP_AUTHORIZATION' => 'Bearer test-token']));
		self::assertFalse(openidconnect_is_bearer_request(['Authorization' => 'Bearer test-token']));
		self::assertFalse(openidconnect_is_bearer_request([]));
	}

	public function testBuildLoginFallbackPathDisablesAutoRedirectAndPreservesReturnPath(): void
	{
		self::assertSame('login?openidconnect_no_auto=1', openidconnect_build_login_fallback_path(''));
		self::assertSame(
			'login?openidconnect_no_auto=1&return_path=oauth%2Fauthorize%3Fclient_id%3Dtest',
			openidconnect_build_login_fallback_path('oauth/authorize?client_id=test')
		);
	}

	public function testGetAuthorizationErrorSupportsErrAlias(): void
	{
		self::assertSame('login_required', openidconnect_get_authorization_error(['err' => 'login_required']));
		self::assertSame('access_denied', openidconnect_get_authorization_error(['error' => 'access_denied']));
		self::assertSame('', openidconnect_get_authorization_error([]));
	}

	public function testShouldFallbackToManualLoginForSilentAuthErrors(): void
	{
		self::assertTrue(openidconnect_should_fallback_to_manual_login('login_required'));
		self::assertTrue(openidconnect_should_fallback_to_manual_login('interaction_required'));
		self::assertTrue(openidconnect_should_fallback_to_manual_login('consent_required'));
		self::assertTrue(openidconnect_should_fallback_to_manual_login('account_selection_required'));
		self::assertFalse(openidconnect_should_fallback_to_manual_login('access_denied'));
	}

	public function testExtractUserinfoFromIdTokenMapsCoreClaims(): void
	{
		$claims = (object) [
			'sub' => 'oidc-sub-123',
			'email' => 'user@example.com',
			'name' => 'Example User',
			'preferred_username' => 'example-user',
			'picture' => 'https://id.example.com/avatar.png',
			'email_verified' => true,
		];

		$userinfo = openidconnect_extract_userinfo_from_id_token($claims);

		self::assertSame('oidc-sub-123', $userinfo['sub']);
		self::assertSame('user@example.com', $userinfo['email']);
		self::assertSame('Example User', $userinfo['name']);
		self::assertSame('example-user', $userinfo['preferred_username']);
		self::assertSame('https://id.example.com/avatar.png', $userinfo['picture']);
		self::assertTrue($userinfo['email_verified']);
	}

	public function testExtractUserinfoFromIdTokenNormalizesStringEmailVerified(): void
	{
		$claims = (object) [
			'sub' => 'oidc-sub-456',
			'email' => 'user2@example.com',
			'nickname' => 'nick-fallback',
			'email_verified' => '1',
		];

		$userinfo = openidconnect_extract_userinfo_from_id_token($claims);

		self::assertSame('nick-fallback', $userinfo['preferred_username']);
		self::assertTrue($userinfo['email_verified']);
	}
}