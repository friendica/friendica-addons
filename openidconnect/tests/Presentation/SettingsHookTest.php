<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Presentation\SettingsHook;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class SettingsHookTest extends AddonTestCase
{
    public function testAppendDoesNothingWithoutLocalUser(): void
    {
        $data = [];

        (new SettingsHook())->append($data);

        self::assertSame([], $data);
    }

    public function testAppendBuildsSettingsPayloadForLinkedUser(): void
    {
        DI::userSession()->setLocalUserId(42);
        DI::pConfig()->set(42, 'openidconnect', 'oidc_sub', 'sub-123');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_email', 'person@example.test');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_nickname', 'nick');

        $data = [];
        (new SettingsHook())->append($data);

        self::assertArrayHasKey('aside', $data);
        $aside = json_decode((string)$data['aside'], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('addon/openidconnect/settings.tpl', $aside['template']);
        self::assertSame('sub-123', $aside['vars']['$linked']['sub']);
        self::assertSame('https://example.test/openidconnect/unlink', $aside['vars']['$unlink_url']);
        self::assertSame('https://example.test/openidconnect/link', $aside['vars']['$link_url']);
        self::assertSame('test-token-openidconnect_unlink', $aside['vars']['$unlink_token']);

        $expectedConfirmJson = json_encode(
            'Are you sure you want to unlink your OpenID Connect account?',
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
        );
        self::assertSame($expectedConfirmJson, $aside['vars']['$confirm_json']);
    }
}
