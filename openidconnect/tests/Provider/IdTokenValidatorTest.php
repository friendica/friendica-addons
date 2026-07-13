<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Provider;

use Friendica\Addon\OpenIdConnect\Provider\IdTokenValidator;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class IdTokenValidatorTest extends AddonTestCase
{
    public function testComputesOidcAccessTokenHash(): void
    {
        self::assertSame(
            '-Wu4dbm6BXgYdq6QbSnPTw',
            IdTokenValidator::accessTokenHash('dBjftJeZ4CVP-m1GwdJdC1wV_N9dcVgM2T9JHhSwbLc')
        );
    }

    public function testAcceptsClientIdInScalarOrArrayAudience(): void
    {
        self::assertTrue(IdTokenValidator::hasAudience('client-id', 'client-id'));
        self::assertTrue(IdTokenValidator::hasAudience(['other', 'client-id'], 'client-id'));
        self::assertFalse(IdTokenValidator::hasAudience(['other'], 'client-id'));
    }
}
