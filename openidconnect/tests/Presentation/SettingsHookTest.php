<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Presentation\SettingsHook;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\Addon\OpenIdConnect\Presentation\SettingsHookJsonEncodeOverride;
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

    public function testAppendDoesNotExposeStoredEmailOrNicknameInLinkedPayload(): void
    {
        DI::userSession()->setLocalUserId(42);
        DI::pConfig()->set(42, 'openidconnect', 'oidc_sub', 'sub-123');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_email', 'person@example.test');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_nickname', 'nick');

        $data = [];
        (new SettingsHook())->append($data);

        $aside = json_decode((string)$data['aside'], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(['sub' => 'sub-123'], $aside['vars']['$linked']);
        self::assertArrayNotHasKey('email', $aside['vars']['$linked']);
        self::assertArrayNotHasKey('nickname', $aside['vars']['$linked']);
    }

    public function testAppendRendersWithNullLinkedAccount(): void
    {
        DI::userSession()->setLocalUserId(42);

        $accountLinker = $this->createMock(AccountLinker::class);
        $accountLinker->expects($this->once())
            ->method('get')
            ->with(42)
            ->willReturn(null);

        $data = [];
        (new SettingsHook($accountLinker))->append($data);

        self::assertArrayHasKey('aside', $data);
        self::assertIsString($data['aside']);

        $aside = json_decode((string)$data['aside'], true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('vars', $aside);
        self::assertArrayHasKey('$linked', $aside['vars']);
        self::assertNull($aside['vars']['$linked']);
    }

    public function testAppendRendersWithLinkedAccountMissingSubFallback(): void
    {
        DI::userSession()->setLocalUserId(42);

        $accountLinker = $this->createMock(AccountLinker::class);
        $accountLinker->expects($this->once())
            ->method('get')
            ->with(42)
            ->willReturn([
                'email' => 'user@example.com',
                'nickname' => 'testuser',
            ]);

        $data = [];
        (new SettingsHook($accountLinker))->append($data);

        self::assertArrayHasKey('aside', $data);

        $aside = json_decode((string)$data['aside'], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('', $aside['vars']['$linked']['sub']);
        self::assertStringNotContainsString('user@example.com', (string)$data['aside']);
        self::assertStringNotContainsString('testuser', (string)$data['aside']);
    }

    public function testAppendRendersWithFullLinkedAccount(): void
    {
        DI::userSession()->setLocalUserId(42);

        $accountLinker = $this->createMock(AccountLinker::class);
        $accountLinker->expects($this->once())
            ->method('get')
            ->with(42)
            ->willReturn([
                'sub' => 'test-subject-id',
                'email' => 'user@example.com',
                'nickname' => 'testuser',
            ]);

        $data = [];
        (new SettingsHook($accountLinker))->append($data);

        self::assertArrayHasKey('aside', $data);

        $aside = json_decode((string)$data['aside'], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('test-subject-id', $aside['vars']['$linked']['sub']);
        self::assertStringNotContainsString('user@example.com', (string)$data['aside']);
        self::assertStringNotContainsString('testuser', (string)$data['aside']);
    }

    public function testAppendLogsErrorWhenJsonEncodeThrows(): void
    {
        DI::userSession()->setLocalUserId(42);
        DI::pConfig()->set(42, 'openidconnect', 'oidc_sub', 'sub-123');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_email', 'person@example.test');
        DI::pConfig()->set(42, 'openidconnect', 'oidc_nickname', 'nick');

        $data = [];
        SettingsHookJsonEncodeOverride::enable();
        try {
            (new SettingsHook())->append($data);
        } finally {
            SettingsHookJsonEncodeOverride::disable();
        }

        self::assertArrayHasKey('aside', $data);
        $aside = json_decode((string)$data['aside'], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('""', $aside['vars']['$confirm_json']);
        self::assertNotEmpty(DI::logger()->errors);
        self::assertSame('openidconnect: failed to encode unlink confirmation text', DI::logger()->errors[0][0]);
        self::assertArrayHasKey('error', DI::logger()->errors[0][1]);
        self::assertSame('Forced JSON encode failure', DI::logger()->errors[0][1]['error']);
        self::assertStringNotContainsString('person@example.test', (string)$data['aside']);
        self::assertStringNotContainsString('nick', (string)$data['aside']);
    }
}
