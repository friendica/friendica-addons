<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Presentation\AdminHook;
use Friendica\Core\Config\ValueObject\Cache;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;
use PHPUnit\Framework\Attributes\DataProvider;

final class AdminHookTest extends AddonTestCase
{
    public static function sourceLabelProvider(): array
    {
        return [
            'static file source stays writable' => ['discovery_url', '$discovery_url', Cache::SOURCE_STATIC, false],
            'database source stays writable' => ['client_id', '$client_id', Cache::SOURCE_DATA, false],
            'environment source is readonly' => ['scopes', '$scopes', Cache::SOURCE_ENV, true],
            'fixed source is readonly' => ['allow_unverified_email', '$allow_unverified_email', Cache::SOURCE_FIX, true],
        ];
    }

    public function testButtonTextIsReadonlyWhenProvidedByEnvironment(): void
    {
        DI::config()->getCache()->setSource('openidconnect', 'button_text', Cache::SOURCE_ENV);
        self::assertTrue((new AdminHook())->isReadOnly('button_text'));
    }

    public function testEnvironmentConfigurationIsReadonly(): void
    {
        DI::config()->getCache()->setSource('openidconnect', 'client_id', Cache::SOURCE_ENV);
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
        self::assertSame(
            'A client secret is already configured. Leave this field blank to keep the current value.',
            $decoded['vars']['$client_secret'][5]
        );
    }

    public function testRenderDoesNotShowConfiguredHintWhenClientSecretIsMissing(): void
    {
        $output = '';
        (new AdminHook())->render($output);

        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('', $decoded['vars']['$client_secret'][5]);
    }

    #[DataProvider('sourceLabelProvider')]
    public function testRenderShowsSourceLabelsAndWriteHelp(
        string $configKey,
        string $fieldKey,
        int $source,
        bool $expectedReadOnly,
    ): void {
        DI::config()->getCache()->setSource('openidconnect', $configKey, $source);

        $output = '';
        (new AdminHook())->render($output);

        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $field = $decoded['vars'][$fieldKey];

        self::assertSame($expectedReadOnly, $field[4]);
    }

    public function testSaveRespectsReadOnlyProviderFieldsButAllowsButtonText(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'immutable-client');
        DI::config()->set('openidconnect', 'client_secret', 'immutable-secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'button_text', 'Old Button');
        DI::config()->getCache()->setSource('openidconnect', 'client_id', Cache::SOURCE_ENV);
        DI::config()->getCache()->setSource('openidconnect', 'client_secret', Cache::SOURCE_ENV);
        DI::config()->getCache()->setSource('openidconnect', 'discovery_url', Cache::SOURCE_ENV);

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
        DI::config()->getCache()->setSource('openidconnect', 'auto_create_accounts', Cache::SOURCE_ENV);

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
        DI::config()->getCache()->setSource('openidconnect', 'client_secret', Cache::SOURCE_ENV);

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
        DI::config()->getCache()->setSource('openidconnect', 'discovery_url', Cache::SOURCE_ENV);

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
