<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;

final class AuthorizationRequestTest extends DiTestCase
{
	public function testRedirectDoesNothingWhenNotConfigured(): void
	{
		$this->initDi();

		(new AuthorizationRequest())->redirect();

		self::assertTrue($this->logger->hasRecordMatching('warning', 'not configured'));
		self::assertSame([], $this->sysmsg->getNotices());
	}

	public function testRedirectAddsNoticeWhenAuthorizationEndpointIsMissing(): void
	{
		$this->initDi();
		$this->config->set('openidconnect', 'client_id', 'client-id');
		$this->config->set('openidconnect', 'client_secret', 'client-secret');
		$this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');
		$this->cache->set('openidconnect:provider_config', ['token_endpoint' => 'https://idp.example/token']);

		(new AuthorizationRequest())->redirect();

		self::assertSame(['OpenID Connect provider configuration error.'], $this->sysmsg->getNotices());
	}

	public function testConsumeStateReturnsEmptyArrayForUnknownState(): void
	{
		$this->initDi();

		self::assertSame([], (new AuthorizationRequest())->consumeState('unknown-state'));
	}

	public function testConsumeStateReturnsAndDeletesStoredStateData(): void
	{
		$this->initDi();
		$this->cache->set('oidcstate:abc', ['return_path' => 'settings/account', 'nonce' => 'n1']);

		$stateData = (new AuthorizationRequest())->consumeState('abc');

		self::assertSame(['return_path' => 'settings/account', 'nonce' => 'n1'], $stateData);
		self::assertNull($this->cache->get('oidcstate:abc'));
	}
}
