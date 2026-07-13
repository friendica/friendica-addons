<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests;

use Friendica\Addon\OpenIdConnect\OpenIdConnectAddon;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class OpenIdConnectAddonTest extends AddonTestCase
{
	public function testUnknownRouteOnlyLogsWarning(): void
	{
		$addon = new OpenIdConnectAddon();
		$addon->dispatch('unknown');

		self::assertSame('openidconnect: unknown route requested', DI::logger()->warnings[0][0]);
	}
}
