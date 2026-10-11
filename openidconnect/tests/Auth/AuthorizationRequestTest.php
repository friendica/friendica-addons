<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Network\HTTPClient\Capability\ICanHandleHttpResponses;
use Friendica\Network\HTTPClient\Capability\ICanSendHttpRequests;
use PHPUnit\Framework\Attributes\DataProvider;

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
		self::assertSame('login?openidconnect_no_auto=1', $this->lastRedirect);
	}

	#[DataProvider('discoveryFailures')]
	public function testDiscoveryFailureRedirectsToLoginWithNotice(bool $success, string $code, string $body): void
	{
		$response = $this->createMock(ICanHandleHttpResponses::class);
		$response->method('isSuccess')->willReturn($success);
		$response->method('getReturnCode')->willReturn($code);
		$response->method('getBodyString')->willReturn($body);

		$httpClient = $this->mockHttpClient();
		$httpClient->expects(self::once())->method('get')->willReturn($response);
		$this->initDi([ICanSendHttpRequests::class => $httpClient]);
		$this->configureProvider();

		(new AuthorizationRequest())->redirect();

		self::assertSame(['OpenID Connect provider configuration error.'], $this->sysmsg->getNotices());
		self::assertSame('login?openidconnect_no_auto=1', $this->lastRedirect);
		self::assertSame([], $this->cache->getAllKeys('oidcstate:'));
	}

	/** @return array<string, array{bool, string, string}> */
	public static function discoveryFailures(): array
	{
		return [
			'host cannot be resolved' => [false, '0', ''],
			'provider server error'   => [false, '500', 'server error'],
			'invalid discovery JSON'  => [true, '200', 'invalid JSON'],
		];
	}

	public function testDiscoveryExceptionRedirectsToLoginWithNotice(): void
	{
		$httpClient = $this->mockHttpClient();
		$httpClient->expects(self::once())->method('get')->willThrowException(new \RuntimeException('Could not resolve host'));
		$this->initDi([ICanSendHttpRequests::class => $httpClient]);
		$this->configureProvider();

		(new AuthorizationRequest())->redirect();

		self::assertSame(['OpenID Connect provider configuration error.'], $this->sysmsg->getNotices());
		self::assertSame('login?openidconnect_no_auto=1', $this->lastRedirect);
	}

	#[DataProvider('failureRedirects')]
	public function testFailureRedirectPreservesLoginDestinationAndAccountLinkContext(bool $linkMode, string $returnPath, string $expected): void
	{
		$this->initDi();
		$this->configureProvider();
		$this->cache->set('openidconnect:provider_config', ['token_endpoint' => 'https://idp.example/token']);

		(new AuthorizationRequest())->redirect($linkMode, $returnPath, true);

		self::assertSame($expected, $this->lastRedirect);
	}

	/** @return array<string, array{bool, string, string}> */
	public static function failureRedirects(): array
	{
		return [
			'login destination'    => [false, 'oauth/authorize?client_id=test', 'login?openidconnect_no_auto=1&return_path=oauth%2Fauthorize%3Fclient_id%3Dtest'],
			'external destination' => [false, 'https://external.example/', 'login?openidconnect_no_auto=1'],
			'account linking'      => [true, 'settings/account', 'settings/account'],
		];
	}

	private function configureProvider(): void
	{
		$this->config->set('openidconnect', 'client_id', 'client-id');
		$this->config->set('openidconnect', 'client_secret', 'client-secret');
		$this->config->set('openidconnect', 'discovery_url', 'https://idp.example/.well-known/openid-configuration');
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
