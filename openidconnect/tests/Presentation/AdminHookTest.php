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

    public function testSavePreservesExistingClientSecretWhenFieldIsOmitted(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'existing-client');
        DI::config()->set('openidconnect', 'client_secret', 'existing-secret');

        $hook = new AdminHook();
        $hook->save([
            'discovery_url' => 'https://id.example.com/.well-known/openid-configuration',
            'client_id' => 'new-client',
        ]);

        self::assertSame('existing-secret', DI::config()->get('openidconnect', 'client_secret'));
        self::assertSame('new-client', DI::config()->get('openidconnect', 'client_id'));
        self::assertContains('OpenID Connect settings saved.', DI::sysmsg()->infos);
    }

    public function testSaveRejectsExplicitEmptyClientSecretAndDoesNotOverwriteStoredSecret(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'existing-client');
        DI::config()->set('openidconnect', 'client_secret', 'existing-secret');

        $hook = new AdminHook();
        $hook->save([
            'discovery_url' => 'https://id.example.com/.well-known/openid-configuration',
            'client_id' => 'existing-client',
            'client_secret' => '   ',
        ]);

        self::assertContains('OpenID Connect: Client Secret is required.', DI::sysmsg()->notices);
        self::assertSame('existing-secret', DI::config()->get('openidconnect', 'client_secret'));
    }

    public function testSaveRespectsReadOnlyProviderFieldsButAllowsButtonText(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'immutable-client');
        DI::config()->set('openidconnect', 'client_secret', 'immutable-secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'button_text', 'Old Button');
        DI::config()->getCache()->setSource('openidconnect', 'client_id', 5);
        DI::config()->getCache()->setSource('openidconnect', 'client_secret', 5);
        DI::config()->getCache()->setSource('openidconnect', 'discovery_url', 5);

        $hook = new AdminHook();
        $hook->save([
            'client_id' => 'attacker-client',
            'client_secret' => 'attacker-secret',
            'discovery_url' => 'https://evil.example/.well-known/openid-configuration',
            'button_text' => 'New Button',
        ]);

        self::assertSame('immutable-client', DI::config()->get('openidconnect', 'client_id'));
        self::assertSame('immutable-secret', DI::config()->get('openidconnect', 'client_secret'));
        self::assertSame('https://id.example.com/.well-known/openid-configuration', DI::config()->get('openidconnect', 'discovery_url'));
        self::assertSame('New Button', DI::config()->get('openidconnect', 'button_text'));
    }
}
