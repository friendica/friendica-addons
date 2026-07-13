<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support;

use Friendica\DI;
use PHPUnit\Framework\TestCase;

abstract class AddonTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DI::resetTestState();
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
    }
}
