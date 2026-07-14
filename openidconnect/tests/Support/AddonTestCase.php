<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support;

use Friendica\Addon\OpenIdConnect\Auth\SessionFunctionSpy;
use Friendica\Database\DBA;
use Friendica\DI;
use PHPUnit\Framework\TestCase;

abstract class AddonTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DBA::resetTestState();
        DI::resetTestState();
        SessionFunctionSpy::reset();
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
    }
}
