<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Identity;

use Friendica\Addon\OpenIdConnect\Identity\UserInfo;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Network\HTTPClient\Capability\ICanHandleHttpResponses;
use Friendica\Network\HTTPClient\Capability\ICanSendHttpRequests;

final class UserInfoTest extends DiTestCase
{
	public function testMapsClaimsAndUsesNicknameFallback(): void
	{
		$result = (new UserInfo())->fromIdToken((object) ['sub' => 'sub', 'email' => 'u@example.test', 'nickname' => 'nick', 'email_verified' => '1']);
		self::assertSame('nick', $result['preferred_username']);
		self::assertTrue($result['email_verified']);
	}

	public function testExtractUserinfoFromIdTokenMapsCoreClaims(): void
	{
		$claims = (object) [
			'sub'                => 'oidc-sub-123',
			'email'              => 'user@example.com',
			'name'               => 'Example User',
			'preferred_username' => 'example-user',
			'picture'            => 'https://id.example.com/avatar.png',
			'email_verified'     => true,
		];

		$userinfo = (new UserInfo())->fromIdToken($claims);

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
			'sub'            => 'oidc-sub-456',
			'email'          => 'user2@example.com',
			'nickname'       => 'nick-fallback',
			'email_verified' => '1',
		];

		$userinfo = (new UserInfo())->fromIdToken($claims);

		self::assertSame('nick-fallback', $userinfo['preferred_username']);
		self::assertTrue($userinfo['email_verified']);
	}

	public function testFetchReturnsEmptyArrayForEmptyAccessToken(): void
	{
		$this->initDi();

		self::assertSame([], (new UserInfo())->fetch(''));
	}

	public function testFetchReturnsEmptyArrayWhenUserinfoEndpointIsMissing(): void
	{
		$this->initDi();

		self::assertSame([], (new UserInfo())->fetch('tok'));
	}

	public function testFetchReturnsDecodedJsonFromUserinfoEndpoint(): void
	{
		$response = $this->createMock(ICanHandleHttpResponses::class);
		$response->method('isSuccess')->willReturn(true);
		$response->method('getBodyString')->willReturn(json_encode(['sub' => 'sub-123'], JSON_THROW_ON_ERROR));

		$httpClient = $this->mockHttpClient();
		$httpClient->expects(self::once())->method('get')
			->with('https://idp.example/userinfo', '', self::callback(
				static fn (array $opts): bool => ($opts['headers']['Authorization'] ?? '') === 'Bearer tok',
			))
			->willReturn($response);
		$this->initDi([ICanSendHttpRequests::class => $httpClient]);
		$this->cache->set('openidconnect:provider_config', ['userinfo_endpoint' => 'https://idp.example/userinfo']);

		self::assertSame(['sub' => 'sub-123'], (new UserInfo())->fetch('tok'));
	}

	public function testFetchReturnsEmptyArrayWhenResponseIsNotSuccessful(): void
	{
		$response = $this->createMock(ICanHandleHttpResponses::class);
		$response->method('isSuccess')->willReturn(false);
		$response->method('getReturnCode')->willReturn('401');

		$httpClient = $this->mockHttpClient();
		$httpClient->method('get')->willReturn($response);
		$this->initDi([ICanSendHttpRequests::class => $httpClient]);
		$this->cache->set('openidconnect:provider_config', ['userinfo_endpoint' => 'https://idp.example/userinfo']);

		self::assertSame([], (new UserInfo())->fetch('tok'));
	}
}
