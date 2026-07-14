<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support;

use Friendica\Addon\OpenIdConnect\Auth\SessionFunctionSpy;
use Friendica\Addon\OpenIdConnect\Session\CookieSpy;
use Friendica\Core\Hook;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\User;
use PHPUnit\Framework\TestCase;

abstract class AddonTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DBA::resetTestState();
        Hook::resetTestState();
        DI::resetTestState();
        User::resetTestState();
        Contact::resetTestState();
        SessionFunctionSpy::reset();
        CookieSpy::reset();
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
        $_SERVER = [];
    }
}
