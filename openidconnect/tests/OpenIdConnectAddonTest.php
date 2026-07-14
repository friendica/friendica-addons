<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests;

use Friendica\Addon\OpenIdConnect\OpenIdConnectAddon;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class OpenIdConnectAddonTest extends AddonTestCase
{
	public function testDispatchAuthReturnsWarningWhenProviderIsNotConfigured(): void
	{
		$addon = new OpenIdConnectAddon();

		$addon->dispatch('auth');

		self::assertSame('OpenID Connect SSO tried to trigger, but the addon is not configured!', DI::logger()->warnings[0][0]);
	}

	public function testUnknownRouteOnlyLogsWarning(): void
	{
		$addon = new OpenIdConnectAddon();
		$addon->dispatch('unknown');

		self::assertSame('openidconnect: unknown route requested', DI::logger()->warnings[0][0]);
	}

	public function testBeginAccountLinkRedirectsToSettingsWhenNotConfigured(): void
	{
		$addon = new OpenIdConnectAddon();

		$addon->beginAccountLink();

		self::assertSame('settings/account', DI::baseUrl()->lastRedirect());
		self::assertContains('OpenID Connect is not configured.', DI::sysmsg()->notices);
	}

	public function testUnlinkAccountRedirectsToLoginForAnonymousUser(): void
	{
		$addon = new OpenIdConnectAddon();

		$addon->unlinkAccount();

		self::assertSame('login', DI::baseUrl()->lastRedirect());
	}

	public function testLogoutClearsTokenSessionWhenNoIdTokenExists(): void
	{
		DI::session()->set('openidconnect_tokens', ['access_token' => 'access-token']);

		$addon = new OpenIdConnectAddon();
		$addon->logout();

		self::assertNull(DI::session()->get('openidconnect_tokens'));
	}

	public function testRevokeRejectsGetRequests(): void
	{
		DI::session()->set('openidconnect_tokens', [
			'access_token' => 'access-token',
		]);
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$addon = new OpenIdConnectAddon();
		$addon->revoke();

		self::assertSame('access-token', DI::session()->get('openidconnect_tokens')['access_token']);
	}

	public function testSsoInitiateAppendsLoginButtonWhenConfigured(): void
	{
		DI::config()->set('openidconnect', 'client_id', 'client-id');
		DI::config()->set('openidconnect', 'client_secret', 'secret');
		DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
		DI::args()->setQueryString('return_path=settings/account');

		$addon = new OpenIdConnectAddon();
		$output = '';
		$addon->ssoInitiate($output);

		self::assertStringContainsString('/openidconnect/auth?return_path=settings%2Faccount', $output);
	}

	public function testAddonSettingsPassesThroughToSettingsHook(): void
	{
		DI::userSession()->setLocalUserId(23);
		DI::pConfig()->set(23, 'openidconnect', 'oidc_sub', 'sub-xyz');

		$addon = new OpenIdConnectAddon();
		$data = [];
		$addon->addonSettings($data);

		self::assertArrayHasKey('aside', $data);
	}

	public function testPageEndReturnsEarlyWhenProviderIsNotConfigured(): void
	{
		$addon = new OpenIdConnectAddon();
		$output = 'seed';

		$addon->pageEnd($output);

		self::assertSame('seed', $output);
	}

	public function testAddonAdminRendersTemplateOutput(): void
	{
		$addon = new OpenIdConnectAddon();
		$output = '';
		$addon->addonAdmin($output);

		self::assertStringContainsString('admin.tpl', $output);
	}

	public function testAddonAdminPostRunsAdminValidation(): void
	{
		$addon = new OpenIdConnectAddon();
		$addon->addonAdminPost([
			'discovery_url' => 'not-a-url',
			'client_id' => '',
			'client_secret' => '',
		]);

		self::assertNotEmpty(DI::sysmsg()->notices);
	}
}
