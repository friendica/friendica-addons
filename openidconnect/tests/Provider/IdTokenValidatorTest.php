<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Provider;

use Friendica\Addon\OpenIdConnect\Provider\IdTokenValidator;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;

/**
 * Only covers the branches reachable without a real signature check: this CI
 * image does not install the addon's own firebase/php-jwt dependency (that is
 * a manual `composer install` step inside addon/openidconnect/, done on a
 * live instance but not by the shared addons:phpstan/addons:test pipeline),
 * so exercising a genuinely decoded token here would depend on an extra
 * library this environment does not have.
 */
final class IdTokenValidatorTest extends DiTestCase
{
	public function testValidateRejectsEmptyIdToken(): void
	{
		$this->initDi();

		self::assertFalse((new IdTokenValidator())->validate(''));
	}

	public function testValidateRejectsMissingJwksUri(): void
	{
		$this->initDi();
		$this->cache->set('openidconnect:provider_config', ['authorization_endpoint' => 'https://idp.example/authorize']);

		self::assertFalse((new IdTokenValidator())->validate('a.b.c'));
	}

	public function testValidateRejectsMalformedToken(): void
	{
		$this->initDi();
		$this->cache->set('openidconnect:provider_config', ['jwks_uri' => 'https://idp.example/jwks']);

		self::assertFalse((new IdTokenValidator())->validate('not-a-jwt'));
	}

	public function testValidateRejectsNonAllowlistedAlgorithm(): void
	{
		$this->initDi();
		$this->cache->set('openidconnect:provider_config', ['jwks_uri' => 'https://idp.example/jwks']);
		$header  = self::base64UrlEncode(json_encode(['alg' => 'none', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
		$payload = self::base64UrlEncode(json_encode(['sub' => 'sub-123'], JSON_THROW_ON_ERROR));

		self::assertFalse((new IdTokenValidator())->validate($header . '.' . $payload . '.'));
	}

	public function testAccessTokenHashIsDeterministicForSameInput(): void
	{
		self::assertSame(IdTokenValidator::accessTokenHash('token-value'), IdTokenValidator::accessTokenHash('token-value'));
		self::assertNotSame(IdTokenValidator::accessTokenHash('token-value'), IdTokenValidator::accessTokenHash('other-value'));
	}

	public function testHasAudienceMatchesSingleStringAudience(): void
	{
		self::assertTrue(IdTokenValidator::hasAudience('client-id', 'client-id'));
		self::assertFalse(IdTokenValidator::hasAudience('other-client', 'client-id'));
	}

	public function testHasAudienceMatchesWithinArrayAudience(): void
	{
		self::assertTrue(IdTokenValidator::hasAudience(['other-client', 'client-id'], 'client-id'));
		self::assertFalse(IdTokenValidator::hasAudience(['other-client'], 'client-id'));
	}

	private static function base64UrlEncode(string $data): string
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
}
