<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Presentation\ModerationBadgeHook;
use Friendica\Addon\OpenIdConnect\Presentation\ModerationBadgeHookJsonEncodeOverride;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\Database\DBA;
use Friendica\DI;

final class ModerationBadgeHookTest extends AddonTestCase
{
    public function testAppendReturnsEarlyWhenRouteIsNotModerationUsers(): void
    {
        DI::args()->setCommand('settings/addons');

        $output = '<div>initial</div>';
        (new ModerationBadgeHook())->append($output);

        self::assertSame('<div>initial</div>', $output);
        self::assertSame(0, DBA::$pCallCount);
        self::assertSame([], DI::page()->stylesheets);
    }

    public function testAppendReturnsEarlyWhenNoOidcUsersAreFound(): void
    {
        DI::args()->setCommand('moderation/users');
        DBA::$pRows = [];

        $output = '<div>initial</div>';
        (new ModerationBadgeHook())->append($output);

        self::assertSame('<div>initial</div>', $output);
        self::assertSame(1, DBA::$pCallCount);
        self::assertSame([], DI::page()->stylesheets);
    }

    public function testAppendInjectsBadgeScriptAndRegistersStylesheetForModerationUsers(): void
    {
        DI::args()->setCommand('moderation/users');
        DBA::$pRows = [
            ['uid' => 42],
            ['uid' => 7],
        ];

        $output = '<table id="users"><tbody></tbody></table>';
        (new ModerationBadgeHook())->append($output);

        self::assertSame(1, DBA::$pCallCount);
        self::assertCount(1, DI::page()->stylesheets);
        self::assertStringContainsString('addon.css', DI::page()->stylesheets[0]);
        self::assertStringContainsString('var uids = [42,7];', $output);
        self::assertStringContainsString('openidconnect-sso-badge', $output);
        self::assertStringContainsString('OpenID Connect SSO', $output);
    }

    public function testAppendLogsAndReturnsWhenJsonEncodingFails(): void
    {
        DI::args()->setCommand('moderation/users');
        DBA::$pRows = [
            ['uid' => 42],
        ];

        ModerationBadgeHookJsonEncodeOverride::$forceThrow = true;
        try {
            $output = '<table id="users"><tbody></tbody></table>';
            (new ModerationBadgeHook())->append($output);

            self::assertSame(1, DBA::$pCallCount);
            self::assertCount(1, DI::page()->stylesheets);
            self::assertStringNotContainsString('var uids =', $output);
            self::assertCount(1, DI::logger()->errors);
            self::assertStringContainsString(
                'failed to encode SSO badge user ids',
                DI::logger()->errors[0][0]
            );
        } finally {
            ModerationBadgeHookJsonEncodeOverride::$forceThrow = false;
        }
    }
}
