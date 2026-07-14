<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Presentation\AdminHook;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class AdminHookTest extends AddonTestCase
{
    public function testButtonTextIsEditableEvenWhenItsSourceIsStatic(): void
    {
        DI::config()->getCache()->setSource('openidconnect', 'button_text', 5);
        self::assertFalse((new AdminHook())->isReadOnly('button_text'));
    }

    public function testAllOtherStaticConfigurationIsReadOnly(): void
    {
        DI::config()->getCache()->setSource('openidconnect', 'client_id', 5);
        self::assertTrue((new AdminHook())->isReadOnly('client_id'));
    }

    public function testSaveRejectsInvalidDiscoveryUrl(): void
    {
        $hook = new AdminHook();

        $hook->save([
            'discovery_url' => 'not-a-url',
            'client_id' => 'client',
            'client_secret' => 'secret',
        ]);

        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertSame('OpenID Connect: Discovery URL must be a valid URL.', DI::sysmsg()->notices[0]);
        self::assertNull(DI::config()->get('openidconnect', 'discovery_url'));
    }

    public function testSaveRejectsMissingRequiredProviderFieldsWhenProviderIsSubmitted(): void
    {
        $hook = new AdminHook();

        $hook->save([
            'discovery_url' => 'https://id.example.com/.well-known/openid-configuration',
            'client_id' => '',
            'client_secret' => '',
        ]);

        self::assertContains('OpenID Connect: Client ID is required.', DI::sysmsg()->notices);
        self::assertContains('OpenID Connect: Client Secret is required.', DI::sysmsg()->notices);
        self::assertNull(DI::config()->get('openidconnect', 'client_id'));
    }
}
