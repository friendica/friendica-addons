<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class AuthorizationRequestTest extends AddonTestCase
{
    public function testConsumesStateOnlyOnce(): void
    {
        DI::cache()->set('oidcstate:state', ['nonce' => 'nonce'], 600);
        $request = new AuthorizationRequest(new ProviderConfiguration());

        self::assertSame(['nonce' => 'nonce'], $request->consumeState('state'));
        self::assertSame([], $request->consumeState('state'));
    }
}
