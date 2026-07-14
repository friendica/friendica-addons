<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Presentation\AdminHook;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;
use PHPUnit\Framework\Attributes\DataProvider;

final class AdminHookTest extends AddonTestCase
{
    public static function sourceLabelProvider(): array
    {
        return [
            'local config source' => ['discovery_url', '$discovery_url', 1, 'provided by a local config file', true],
            'environment source' => ['client_id', '$client_id', 2, 'provided by the server environment', true],
            'application source' => ['scopes', '$scopes', 3, 'fixed by the application', true],
            'addon default source' => ['allow_unverified_email', '$allow_unverified_email', 5, 'provided by the addon defaults', true],
            'database source stays writable' => ['button_text', '$button_text', 0, 'stored in the database', false],
        ];
    }

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

    public function testSaveRetainsStoredClientSecretWhenSubmittedBlankAndSaveSucceeds(): void
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

        self::assertSame('existing-secret', DI::config()->get('openidconnect', 'client_secret'));
        self::assertSame([], DI::sysmsg()->notices);
        self::assertContains('OpenID Connect settings saved.', DI::sysmsg()->infos);
    }

    public function testRenderDoesNotExposeStoredClientSecretInPayload(): void
    {
        DI::config()->set('openidconnect', 'client_secret', 'super-sensitive-secret');

        $output = '';
        (new AdminHook())->render($output);

        self::assertStringNotContainsString('super-sensitive-secret', $output);

        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('', $decoded['vars']['$client_secret'][2]);
    }

    #[DataProvider('sourceLabelProvider')]
    public function testRenderShowsSourceLabelsAndWriteHelp(
        string $configKey,
        string $fieldKey,
        int $source,
        string $expectedSourceLabel,
        bool $expectedReadOnly,
    ): void {
        DI::config()->getCache()->setSource('openidconnect', $configKey, $source);

        $output = '';
        (new AdminHook())->render($output);

        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $field = $decoded['vars'][$fieldKey];

        self::assertSame('Source: ' . $expectedSourceLabel . '.', $field[4]);
        self::assertSame(
            $expectedReadOnly
                ? 'This value cannot be changed from this page.'
                : 'This value can be changed from this page.',
            $field[5]
        );
        self::assertSame($expectedReadOnly, $field[6]);
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

    public function testSavePersistsWritableFlagsAndStringsSkipsReadOnlyFlagsAndInvalidatesCaches(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_id', 'existing-client');
        DI::config()->set('openidconnect', 'client_secret', 'existing-secret');
        DI::config()->set('openidconnect', 'auto_create_accounts', true);
        DI::config()->set('openidconnect', 'allow_unverified_email', false);
        DI::config()->set('openidconnect', 'idp_signout', false);
        DI::config()->set('openidconnect', 'transparent_sso', true);
        DI::config()->set('openidconnect', 'transparent_sso_prompt_none', true);
        DI::config()->set('openidconnect', 'scopes', 'openid email');
        DI::config()->set('openidconnect', 'button_text', 'Old Button');
        DI::config()->getCache()->setSource('openidconnect', 'auto_create_accounts', 5);

        $hook = new AdminHook();
        $hook->save([
            'discovery_url' => 'https://id.example.com/.well-known/openid-configuration',
            'client_id' => 'new-client',
            'client_secret' => 'new-secret',
            'allow_unverified_email' => '1',
            'idp_signout' => '1',
            'scopes' => 'openid profile',
            'button_text' => 'New Button',
        ]);

        self::assertTrue((bool) DI::config()->get('openidconnect', 'auto_create_accounts'));
        self::assertTrue((bool) DI::config()->get('openidconnect', 'allow_unverified_email'));
        self::assertTrue((bool) DI::config()->get('openidconnect', 'idp_signout'));
        self::assertFalse((bool) DI::config()->get('openidconnect', 'transparent_sso'));
        self::assertFalse((bool) DI::config()->get('openidconnect', 'transparent_sso_prompt_none'));
        self::assertSame('new-client', DI::config()->get('openidconnect', 'client_id'));
        self::assertSame('new-secret', DI::config()->get('openidconnect', 'client_secret'));
        self::assertSame('openid profile', DI::config()->get('openidconnect', 'scopes'));
        self::assertSame('New Button', DI::config()->get('openidconnect', 'button_text'));
        self::assertSame(
            ['openidconnect:provider_config', 'openidconnect:jwks'],
            DI::cache()->deleteCalls
        );
        self::assertContains('OpenID Connect settings saved.', DI::sysmsg()->infos);
    }

    public function testSaveKeepsReadOnlyClientSecretWhenBlankValueIsSubmitted(): void
    {
        DI::config()->set('openidconnect', 'client_secret', 'immutable-secret');
        DI::config()->set('openidconnect', 'button_text', 'Old Button');
        DI::config()->getCache()->setSource('openidconnect', 'client_secret', 5);

        $hook = new AdminHook();
        $hook->save([
            'client_secret' => '   ',
            'button_text' => 'New Button',
        ]);

        self::assertSame('immutable-secret', DI::config()->get('openidconnect', 'client_secret'));
        self::assertSame('New Button', DI::config()->get('openidconnect', 'button_text'));
        self::assertContains('OpenID Connect settings saved.', DI::sysmsg()->infos);
    }

    public function testSaveRequiresDiscoveryUrlWhenProviderFieldsAreSubmitted(): void
    {
        $hook = new AdminHook();

        $hook->save([
            'discovery_url' => '   ',
            'client_id' => 'client',
            'client_secret' => 'secret',
        ]);

        self::assertContains('OpenID Connect: Discovery URL must be a valid URL.', DI::sysmsg()->notices);
        self::assertContains('OpenID Connect: Discovery URL is required.', DI::sysmsg()->notices);
    }

    public function testSaveSkipsReadonlyDiscoveryValidationButStillRequiresWritableProviderFields(): void
    {
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'client_secret', 'existing-secret');
        DI::config()->getCache()->setSource('openidconnect', 'discovery_url', 5);

        $hook = new AdminHook();
        $hook->save([
            'discovery_url' => 'not-a-url',
            'client_id' => '   ',
            'client_secret' => 'existing-secret',
        ]);

        self::assertContains('OpenID Connect: Client ID is required.', DI::sysmsg()->notices);
        self::assertNotContains('OpenID Connect: Discovery URL must be a valid URL.', DI::sysmsg()->notices);
        self::assertNotContains('OpenID Connect: Discovery URL is required.', DI::sysmsg()->notices);
    }
}
