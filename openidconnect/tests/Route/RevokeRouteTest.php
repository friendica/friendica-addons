<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Route;

use Friendica\Addon\OpenIdConnect\Route\RevokeRoute;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Network\HTTPClient\Capability\ICanHandleHttpResponses;
use Friendica\Network\HTTPClient\Capability\ICanSendHttpRequests;

final class RevokeRouteTest extends DiTestCase
{
	/**
	 * BaseModule::checkFormSecurityToken() hashes the anonymous (empty
	 * guid/prvkey) user against session_id()+timestamp+typename.
	 */
	private function validCsrfToken(string $typename): string
	{
		$timestamp = time();

		return $timestamp . '.' . hash('whirlpool', session_id() . $timestamp . $typename);
	}

	public function testHandleRedirectsWithoutRevokingOnGetRequest(): void
	{
		$this->initDi();
		$this->session->set('openidconnect_tokens', ['access_token' => 'access-token']);
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$this->assertRedirectsTo('', fn () => (new RevokeRoute())->handle());

		self::assertSame('access-token', $this->session->get('openidconnect_tokens')['access_token']);
	}

	public function testHandleRedirectsWithoutRevokingOnMissingCsrfToken(): void
	{
		$this->initDi();
		$this->session->set('openidconnect_tokens', ['access_token' => 'access-token']);
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_REQUEST                  = [];

		$this->assertRedirectsTo('', fn () => (new RevokeRoute())->handle());

		self::assertSame('access-token', $this->session->get('openidconnect_tokens')['access_token']);
	}

	public function testHandleRedirectsWithoutRevokingWhenAccessTokenIsMissing(): void
	{
		$this->initDi();
		$this->session->set('openidconnect_tokens', ['refresh_token' => 'refresh-token']);
		$_SERVER['REQUEST_METHOD']       = 'POST';
		$_REQUEST['form_security_token'] = $this->validCsrfToken('openidconnect_revoke');

		$this->assertRedirectsTo('', fn () => (new RevokeRoute())->handle());

		self::assertSame('refresh-token', $this->session->get('openidconnect_tokens')['refresh_token']);
	}

	public function testHandleClearsSessionLocallyWhenProviderHasNoRevocationEndpoint(): void
	{
		$this->initDi();
		$this->session->set('openidconnect_tokens', ['access_token' => 'access-token']);
		$_SERVER['REQUEST_METHOD']       = 'POST';
		$_REQUEST['form_security_token'] = $this->validCsrfToken('openidconnect_revoke');

		$this->assertRedirectsTo('', fn () => (new RevokeRoute())->handle());

		self::assertNull($this->session->get('openidconnect_tokens'));
	}

	public function testHandleRevokesAccessAndRefreshTokensThenClearsSession(): void
	{
		$response = $this->createMock(ICanHandleHttpResponses::class);
		$response->method('isSuccess')->willReturn(true);
		$httpClient = $this->mockHttpClient();
		$httpClient->expects(self::exactly(2))->method('post')
			->willReturnCallback(function (string $url, $body) use ($response): ICanHandleHttpResponses {
				self::assertSame('https://idp.example/revoke', $url);

				return $response;
			});
		$this->initDi([ICanSendHttpRequests::class => $httpClient]);
		$this->cache->set('openidconnect:provider_config', ['revocation_endpoint' => 'https://idp.example/revoke']);
		$this->session->set('openidconnect_tokens', ['access_token' => 'access-token', 'refresh_token' => 'refresh-token']);
		$_SERVER['REQUEST_METHOD']       = 'POST';
		$_REQUEST['form_security_token'] = $this->validCsrfToken('openidconnect_revoke');

		$this->assertRedirectsTo('', fn () => (new RevokeRoute())->handle());

		self::assertNull($this->session->get('openidconnect_tokens'));
	}

	public function testHandleLogsWarningAndStillClearsSessionWhenRevocationThrows(): void
	{
		$httpClient = $this->mockHttpClient();
		$httpClient->method('post')->willThrowException(new \RuntimeException('boom'));
		$this->initDi([ICanSendHttpRequests::class => $httpClient]);
		$this->cache->set('openidconnect:provider_config', ['revocation_endpoint' => 'https://idp.example/revoke']);
		$this->session->set('openidconnect_tokens', ['access_token' => 'access-token']);
		$_SERVER['REQUEST_METHOD']       = 'POST';
		$_REQUEST['form_security_token'] = $this->validCsrfToken('openidconnect_revoke');

		$this->assertRedirectsTo('', fn () => (new RevokeRoute())->handle());

		self::assertNull($this->session->get('openidconnect_tokens'));
		self::assertTrue($this->logger->hasRecordMatching('warning', 'revocation endpoint request threw exception'));
	}
}
