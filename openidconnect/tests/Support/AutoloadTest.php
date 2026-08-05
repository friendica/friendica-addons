<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support;

use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;
use PHPUnit\Framework\TestCase;

final class AutoloadTest extends TestCase
{
    public function testAddonNamespaceIsComposerAutoloadable(): void
    {
        self::assertTrue(class_exists(LoginPolicy::class));
    }
}
