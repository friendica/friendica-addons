<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Presentation\SettingsHook;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;

/**
 * append() only reaches Renderer::getMarkupTemplate()/replaceMacros() for a
 * logged-in user, which needs a fully bootstrapped Smarty template engine
 * (theme paths, profiler, cache dir) that is out of scope for a unit test.
 * The anonymous-user early-return path is pure addon logic and is covered here.
 */
final class SettingsHookTest extends DiTestCase
{
    public function testAppendDoesNothingWithoutLocalUser(): void
    {
        $this->initDi();

        $data = [];
        (new SettingsHook())->append($data);

        self::assertSame([], $data);
    }
}
