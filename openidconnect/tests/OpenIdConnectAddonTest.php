<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests;

use Friendica\Addon\OpenIdConnect\OpenIdConnectAddon;
use Friendica\Addon\OpenIdConnect\Presentation\SettingsHook;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;

/**
 * dispatch() is a thin router onto AuthorizationRequest/CallbackHandler/
 * RevokeRoute/AccountLinkRoutes/LogoutHandler, which each already have
 * focused test suites. This file only covers the routing/glue itself, plus
 * the config-only parts of addonAdmin()/addonSettings() — their template
 * rendering needs a fully bootstrapped Smarty engine and is out of scope
 * for a unit test (see AdminHookTest/SettingsHookTest).
 */
final class OpenIdConnectAddonTest extends DiTestCase
{
	private function configureProvider(): void
	{
		$this->config->set('openidconnect', 'client_id', 'client-id');
		$this->config->set('openidconnect', 'client_secret', 'secret');
		$this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');
	}

	public function testDispatchAuthLogsWarningWhenProviderIsNotConfigured(): void
	{
		$this->initDi();

		(new OpenIdConnectAddon())->dispatch('auth');

		self::assertTrue($this->logger->hasRecordMatching('warning', 'OpenID Connect SSO tried to trigger, but the addon is not configured!'));
	}

	public function testDispatchUnknownRouteOnlyLogsWarning(): void
	{
		$this->initDi();

		(new OpenIdConnectAddon())->dispatch('unknown');

		self::assertTrue($this->logger->hasRecordMatching('warning', 'openidconnect: unknown route requested'));
	}

	public function testDispatchCallbackHandlesCurrentRequestQuery(): void
	{
		$this->initDi();
		$this->cache->set('oidcstate:s', ['silent_auth' => true, 'return_path' => 'oauth/authorize?client_id=test']);
		$_GET = ['state' => 's', 'error' => 'login_required'];

		(new OpenIdConnectAddon())->dispatch('callback');

		self::assertSame(
			'login?openidconnect_no_auto=1&return_path=oauth%2Fauthorize%3Fclient_id%3Dtest',
			$this->lastRedirect,
		);
	}

	public function testDispatchRevokeRedirectsOnGetRequest(): void
	{
		$this->initDi();
		$this->session->set('openidconnect_tokens', ['access_token' => 'access-token']);
		$_SERVER['REQUEST_METHOD'] = 'GET';

		(new OpenIdConnectAddon())->dispatch('revoke');

		self::assertSame('', $this->lastRedirect);
		self::assertSame('access-token', $this->session->get('openidconnect_tokens')['access_token']);
	}

	public function testDispatchLinkRedirectsToSettingsWhenNotConfigured(): void
	{
		$this->initDi();

		(new OpenIdConnectAddon())->dispatch('link');

		self::assertSame('settings/account', $this->lastRedirect);
		self::assertContains('OpenID Connect is not configured.', $this->sysmsg->getNotices());
	}

	public function testDispatchUnlinkRedirectsToLoginForAnonymousUser(): void
	{
		$this->initDi();
		$timestamp                                  = time();
		$_REQUEST[SettingsHook::UNLINK_TOKEN_FIELD] = $timestamp . '.' . hash('whirlpool', session_id() . $timestamp . 'openidconnect_unlink');

		(new OpenIdConnectAddon())->dispatch('unlink');

		self::assertSame('login', $this->lastRedirect);
	}

	public function testBeginAccountLinkRedirectsToSettingsWhenNotConfigured(): void
	{
		$this->initDi();

		(new OpenIdConnectAddon())->beginAccountLink();

		self::assertSame('settings/account', $this->lastRedirect);
		self::assertContains('OpenID Connect is not configured.', $this->sysmsg->getNotices());
	}

	public function testLogoutClearsTokenSession(): void
	{
		$this->initDi();
		$this->session->set('openidconnect_tokens', ['access_token' => 'access-token']);

		(new OpenIdConnectAddon())->logout();

		self::assertNull($this->session->get('openidconnect_tokens'));
	}

	public function testRevokeRejectsGetRequests(): void
	{
		$this->initDi();
		$this->session->set('openidconnect_tokens', ['access_token' => 'access-token']);
		$_SERVER['REQUEST_METHOD'] = 'GET';

		(new OpenIdConnectAddon())->revoke();

		self::assertSame('access-token', $this->session->get('openidconnect_tokens')['access_token']);
	}

	public function testSsoInitiateAppendsLoginButtonWhenConfigured(): void
	{
		$this->initDi();
		$this->configureProvider();
		$this->args->method('getQueryString')->willReturn('return_path=settings/account');

		$output = '';
		(new OpenIdConnectAddon())->ssoInitiate($output);

		self::assertStringContainsString('/openidconnect/auth?return_path=settings%2Faccount', $output);
	}

	public function testAddonSettingsPassesThroughToSettingsHookForAnonymousUser(): void
	{
		$this->initDi();

		$data = [];
		(new OpenIdConnectAddon())->addonSettings($data);

		self::assertSame([], $data);
	}

	public function testPageEndReturnsEarlyWhenProviderIsNotConfigured(): void
	{
		$this->initDi();

		$output = 'seed';
		(new OpenIdConnectAddon())->pageEnd($output);

		self::assertSame('seed', $output);
	}

	public function testAddonAdminPostRunsAdminValidation(): void
	{
		$this->initDi();

		(new OpenIdConnectAddon())->addonAdminPost([
			'discovery_url' => 'not-a-url',
			'client_id'     => '',
			'client_secret' => '',
		]);

		self::assertNotEmpty($this->sysmsg->getNotices());
	}

	public function testAddonAdminPostRedirectsBackToAddonPageAfterSuccessfulSave(): void
	{
		$this->initDi();

		(new OpenIdConnectAddon())->addonAdminPost([
			'discovery_url'          => 'https://idp.example.com/.well-known/openid-configuration',
			'client_id'              => 'client-id',
			'client_secret'          => 'secret-value',
			'scopes'                 => 'openid email profile',
			'button_text'            => 'Sign in with OpenID Connect',
			'auto_create_accounts'   => '1',
			'allow_unverified_email' => '1',
			'transparent_sso'        => '1',
			'idp_signout'            => '1',
		]);

		self::assertSame('admin/addons/openidconnect', $this->lastRedirect);
		self::assertContains('OpenID Connect settings saved.', $this->sysmsg->getInfos());
	}
}
