<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests;

use Friendica\Addon\openidconnect\src\Utilities;
use Friendica\Addon\openidconnect\src\ProviderConfig;
use Friendica\Addon\openidconnect\src\UserInfo;
use Friendica\DI;
use PHPUnit\Framework\TestCase;

final class OpenIdConnectTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		DI::resetTestState();
	}

	// -----------------------------------------------------------------------
	// Utilities class tests
	// -----------------------------------------------------------------------

	public function testUtilitiesSanitizeReturnPathKeepsRelativePath(): void
	{
		self::assertSame('settings/account', Utilities::sanitizeReturnPath('settings/account'));
		self::assertSame('oauth/authorize?client_id=test', Utilities::sanitizeReturnPath('/oauth/authorize?client_id=test'));
	}

	public function testUtilitiesSanitizeReturnPathRejectsAbsoluteUrlsAndCustomSchemes(): void
	{
		self::assertSame('', Utilities::sanitizeReturnPath('https://evil.example/callback'));
		self::assertSame('', Utilities::sanitizeReturnPath('//evil.example/callback'));
		self::assertSame('', Utilities::sanitizeReturnPath('mona://oauth'));
	}

	public function testUtilitiesGenerateNonceReturnsExpectedLengthAndHex(): void
	{
		$nonce = Utilities::generateNonce();

		self::assertSame(OIDC_NONCE_LENGTH * 2, strlen($nonce));
		self::assertMatchesRegularExpression('/^[a-f0-9]+$/', $nonce);
	}

	public function testUtilitiesGeneratePkceVerifierAndChallengeUseBase64UrlAlphabet(): void
	{
		$verifier = Utilities::generatePkceVerifier();
		$challenge = Utilities::generatePkceChallenge($verifier);

		self::assertMatchesRegularExpression('/^[A-Za-z0-9\-_]+$/', $verifier);
		self::assertMatchesRegularExpression('/^[A-Za-z0-9\-_]+$/', $challenge);
		self::assertNotSame($verifier, $challenge);
	}

	public function testUtilitiesIsSafeUrlAllowsSameHostAsConfiguredDiscoveryUrlEvenOnPrivateNetwork(): void
	{
		DI::config()->set('openidconnect', 'discovery_url', 'http://authentik-server.friendica-local-dev.orb.local:9000/application/o/demo/.well-known/openid-configuration');

		self::assertTrue(Utilities::isSafeUrl('http://authentik-server.friendica-local-dev.orb.local:9000/media/avatar.png'));
	}

	public function testUtilitiesIsSafeUrlRejectsNonHttpsThirdPartyHost(): void
	{
		DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

		self::assertFalse(Utilities::isSafeUrl('http://cdn.example.com/avatar.png'));
	}

	public function testUtilitiesShouldAutoRedirectLoginRequiresTransparentSsoEnabled(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';

		self::assertFalse(Utilities::shouldAutoRedirectLogin([], $_SERVER));

		DI::config()->set('openidconnect', 'transparent_sso', true);

		self::assertTrue(Utilities::shouldAutoRedirectLogin([], $_SERVER));
	}

	public function testUtilitiesShouldAutoRedirectLoginRejectsBearerRequestsAndFallbackFlag(): void
	{
		DI::config()->set('openidconnect', 'transparent_sso', true);

		self::assertFalse(Utilities::shouldAutoRedirectLogin(
			['openidconnect_no_auto' => '1'],
			['REQUEST_METHOD' => 'GET']
		));

		self::assertFalse(Utilities::shouldAutoRedirectLogin(
			[],
			[
				'REQUEST_METHOD' => 'GET',
				'HTTP_AUTHORIZATION' => 'Bearer test-token',
			]
		));

		self::assertFalse(Utilities::shouldAutoRedirectLogin([], ['REQUEST_METHOD' => 'POST']));
	}

	public function testUtilitiesIsBearerRequestOnlyUsesCanonicalServerHeader(): void
	{
		self::assertTrue(Utilities::isBearerRequest(['HTTP_AUTHORIZATION' => 'Bearer test-token']));
		self::assertFalse(Utilities::isBearerRequest(['Authorization' => 'Bearer test-token']));
		self::assertFalse(Utilities::isBearerRequest([]));
	}

	public function testUtilitiesBuildLoginFallbackPathDisablesAutoRedirectAndPreservesReturnPath(): void
	{
		self::assertSame('login?openidconnect_no_auto=1', Utilities::buildLoginFallbackPath(''));
		self::assertSame(
			'login?openidconnect_no_auto=1&return_path=oauth%2Fauthorize%3Fclient_id%3Dtest',
			Utilities::buildLoginFallbackPath('oauth/authorize?client_id=test')
		);
	}

	public function testUtilitiesGetAuthorizationErrorSupportsErrAlias(): void
	{
		self::assertSame('login_required', Utilities::getAuthorizationError(['err' => 'login_required']));
		self::assertSame('access_denied', Utilities::getAuthorizationError(['error' => 'access_denied']));
		self::assertSame('', Utilities::getAuthorizationError([]));
	}

	public function testUtilitiesShouldFallbackToManualLoginForSilentAuthErrors(): void
	{
		self::assertTrue(Utilities::shouldFallbackToManualLogin('login_required'));
		self::assertTrue(Utilities::shouldFallbackToManualLogin('interaction_required'));
		self::assertTrue(Utilities::shouldFallbackToManualLogin('consent_required'));
		self::assertTrue(Utilities::shouldFallbackToManualLogin('account_selection_required'));
		self::assertFalse(Utilities::shouldFallbackToManualLogin('access_denied'));
	}

	// -----------------------------------------------------------------------
	// UserInfo class tests
	// -----------------------------------------------------------------------

	public function testUserInfoExtractFromIdTokenMapsCoreClaims(): void
	{
		$claims = (object) [
			'sub' => 'oidc-sub-123',
			'email' => 'user@example.com',
			'name' => 'Example User',
			'preferred_username' => 'example-user',
			'picture' => 'https://id.example.com/avatar.png',
			'email_verified' => true,
		];

		$userinfo = UserInfo::extractFromIdToken($claims);

		self::assertSame('oidc-sub-123', $userinfo['sub']);
		self::assertSame('user@example.com', $userinfo['email']);
		self::assertSame('Example User', $userinfo['name']);
		self::assertSame('example-user', $userinfo['preferred_username']);
		self::assertSame('https://id.example.com/avatar.png', $userinfo['picture']);
		self::assertTrue($userinfo['email_verified']);
	}

	public function testUserInfoExtractFromIdTokenNormalizesStringEmailVerified(): void
	{
		$claims = (object) [
			'sub' => 'oidc-sub-456',
			'email' => 'user2@example.com',
			'nickname' => 'nick-fallback',
			'email_verified' => '1',
		];

		$userinfo = UserInfo::extractFromIdToken($claims);

		self::assertSame('nick-fallback', $userinfo['preferred_username']);
		self::assertTrue($userinfo['email_verified']);
	}

	// -----------------------------------------------------------------------
	// ProviderConfig class tests
	// -----------------------------------------------------------------------

	public function testProviderConfigGetClientAuthMethodPrefersClientSecretBasic(): void
	{
		$config = ['token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic']];

		self::assertSame('client_secret_basic', ProviderConfig::getClientAuthMethod($config));
	}

	public function testProviderConfigGetClientAuthMethodFallsBackToClientSecretPost(): void
	{
		$config = ['token_endpoint_auth_methods_supported' => ['client_secret_post']];

		self::assertSame('client_secret_post', ProviderConfig::getClientAuthMethod($config));
	}

	// -----------------------------------------------------------------------
	// Procedural wrapper backward-compatibility tests
	// -----------------------------------------------------------------------

	public function testProceduralSanitizeReturnPath(): void
	{
		self::assertSame('settings/account', openidconnect_sanitize_return_path('settings/account'));
	}

	public function testProceduralIsSafeUrl(): void
	{
		DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');

		self::assertFalse(openidconnect_is_safe_url('http://cdn.example.com/avatar.png'));
	}

	public function testProceduralGetClientAuthMethod(): void
	{
		$config = ['token_endpoint_auth_methods_supported' => ['client_secret_basic']];

		self::assertSame('client_secret_basic', openidconnect_get_client_auth_method($config));
	}

	public function testProceduralExtractUserinfoFromIdToken(): void
	{
		$claims = (object) [
			'sub' => 'sub-1',
			'email' => 'a@b.com',
			'name' => 'A B',
			'preferred_username' => 'ab',
			'picture' => '',
			'email_verified' => true,
		];

		$userinfo = openidconnect_extract_userinfo_from_id_token($claims);

		self::assertSame('sub-1', $userinfo['sub']);
		self::assertSame('a@b.com', $userinfo['email']);
	}
}
