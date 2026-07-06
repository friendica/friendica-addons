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
}