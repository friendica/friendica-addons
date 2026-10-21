<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Presentation\AdminHook;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Core\Config\ValueObject\Cache;
use PHPUnit\Framework\Attributes\DataProvider;

final class AdminHookTest extends DiTestCase
{
	public static function sourceLabelProvider(): array
	{
		return [
			'static file source stays writable' => ['discovery_url', Cache::SOURCE_STATIC, false],
			'database source stays writable'    => ['client_id', Cache::SOURCE_DATA, false],
			'environment source is readonly'    => ['scopes', Cache::SOURCE_ENV, true],
			'fixed source is readonly'          => ['allow_unverified_email', Cache::SOURCE_FIX, true],
		];
	}

	#[DataProvider('sourceLabelProvider')]
	public function testIsReadOnlyReflectsConfigSource(string $key, int $source, bool $expectedReadOnly): void
	{
		$this->initDi();
		$this->config->getCache()->set('openidconnect', $key, '', $source);

		self::assertSame($expectedReadOnly, (new AdminHook())->isReadOnly($key));
	}

	public function testSaveRejectsInvalidDiscoveryUrl(): void
	{
		$this->initDi();

		(new AdminHook())->save([
			'discovery_url' => 'not-a-url',
			'client_id'     => 'client',
			'client_secret' => 'secret',
		]);

		self::assertSame(['OpenID Connect: Discovery URL must be a valid URL.'], $this->sysmsg->getNotices());
		self::assertNull($this->config->get('openidconnect', 'discovery_url'));
	}

	public function testSaveRejectsMissingRequiredProviderFieldsWhenProviderIsSubmitted(): void
	{
		$this->initDi();

		(new AdminHook())->save([
			'discovery_url' => 'https://id.example.com/.well-known/openid-configuration',
			'client_id'     => '',
			'client_secret' => '',
		]);

		self::assertContains('OpenID Connect: Client ID is required.', $this->sysmsg->getNotices());
		self::assertContains('OpenID Connect: Client Secret is required.', $this->sysmsg->getNotices());
		self::assertNull($this->config->get('openidconnect', 'client_id'));
	}

	public function testSavePreservesExistingClientSecretWhenFieldIsOmitted(): void
	{
		$this->initDi();
		$this->config->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');
		$this->config->set('openidconnect', 'client_id', 'existing-client');
		$this->config->set('openidconnect', 'client_secret', 'existing-secret');

		(new AdminHook())->save([
			'discovery_url' => 'https://id.example.com/.well-known/openid-configuration',
			'client_id'     => 'new-client',
		]);

		self::assertSame('existing-secret', $this->config->get('openidconnect', 'client_secret'));
		self::assertSame('new-client', $this->config->get('openidconnect', 'client_id'));
		self::assertContains('OpenID Connect settings saved.', $this->sysmsg->getInfos());
	}

	public function testSaveRetainsStoredClientSecretWhenSubmittedBlankAndSaveSucceeds(): void
	{
		$this->initDi();
		$this->config->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');
		$this->config->set('openidconnect', 'client_id', 'existing-client');
		$this->config->set('openidconnect', 'client_secret', 'existing-secret');

		(new AdminHook())->save([
			'discovery_url' => 'https://id.example.com/.well-known/openid-configuration',
			'client_id'     => 'existing-client',
			'client_secret' => '   ',
		]);

		self::assertSame('existing-secret', $this->config->get('openidconnect', 'client_secret'));
		self::assertSame([], $this->sysmsg->getNotices());
		self::assertContains('OpenID Connect settings saved.', $this->sysmsg->getInfos());
	}

	public function testSaveRespectsReadOnlyProviderFieldsButAllowsButtonText(): void
	{
		$this->initDi();
		$this->config->set('openidconnect', 'client_id', 'immutable-client');
		$this->config->set('openidconnect', 'client_secret', 'immutable-secret');
		$this->config->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');
		$this->config->set('openidconnect', 'button_text', 'Old Button');
		$this->config->getCache()->set('openidconnect', 'client_id', '', Cache::SOURCE_ENV);
		$this->config->getCache()->set('openidconnect', 'client_secret', '', Cache::SOURCE_ENV);
		$this->config->getCache()->set('openidconnect', 'discovery_url', '', Cache::SOURCE_ENV);

		(new AdminHook())->save([
			'client_id'     => 'attacker-client',
			'client_secret' => 'attacker-secret',
			'discovery_url' => 'https://evil.example/.well-known/openid-configuration',
			'button_text'   => 'New Button',
		]);

		self::assertSame('immutable-client', $this->config->get('openidconnect', 'client_id'));
		self::assertSame('immutable-secret', $this->config->get('openidconnect', 'client_secret'));
		self::assertSame('https://id.example.com/.well-known/openid-configuration', $this->config->get('openidconnect', 'discovery_url'));
		self::assertSame('New Button', $this->config->get('openidconnect', 'button_text'));
	}

	public function testSavePersistsWritableFlagsAndStringsSkipsReadOnlyFlagsAndInvalidatesCaches(): void
	{
		$this->initDi();
		$this->config->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');
		$this->config->set('openidconnect', 'client_id', 'existing-client');
		$this->config->set('openidconnect', 'client_secret', 'existing-secret');
		$this->config->set('openidconnect', 'auto_create_accounts', true);
		$this->config->set('openidconnect', 'transparent_sso', true);
		$this->config->set('openidconnect', 'transparent_sso_prompt_none', true);
		$this->config->getCache()->set('openidconnect', 'auto_create_accounts', '', Cache::SOURCE_ENV);
		$this->cache->set('openidconnect:provider_config', ['stale' => true]);
		$this->cache->set('openidconnect:jwks', ['stale' => true]);

		(new AdminHook())->save([
			'discovery_url'          => 'https://id.example.com/.well-known/openid-configuration',
			'client_id'              => 'new-client',
			'client_secret'          => 'new-secret',
			'allow_unverified_email' => '1',
			'idp_signout'            => '1',
			'scopes'                 => 'openid profile',
			'button_text'            => 'New Button',
		]);

		self::assertTrue((bool) $this->config->get('openidconnect', 'auto_create_accounts'));
		self::assertTrue((bool) $this->config->get('openidconnect', 'allow_unverified_email'));
		self::assertTrue((bool) $this->config->get('openidconnect', 'idp_signout'));
		self::assertFalse((bool) $this->config->get('openidconnect', 'transparent_sso'));
		self::assertFalse((bool) $this->config->get('openidconnect', 'transparent_sso_prompt_none'));
		self::assertSame('new-client', $this->config->get('openidconnect', 'client_id'));
		self::assertSame('new-secret', $this->config->get('openidconnect', 'client_secret'));
		self::assertSame('openid profile', $this->config->get('openidconnect', 'scopes'));
		self::assertSame('New Button', $this->config->get('openidconnect', 'button_text'));
		self::assertNull($this->cache->get('openidconnect:provider_config'));
		self::assertNull($this->cache->get('openidconnect:jwks'));
		self::assertContains('OpenID Connect settings saved.', $this->sysmsg->getInfos());
	}

	public function testSaveKeepsReadOnlyClientSecretWhenBlankValueIsSubmitted(): void
	{
		$this->initDi();
		$this->config->set('openidconnect', 'client_secret', 'immutable-secret');
		$this->config->set('openidconnect', 'button_text', 'Old Button');
		$this->config->getCache()->set('openidconnect', 'client_secret', '', Cache::SOURCE_ENV);

		(new AdminHook())->save([
			'client_secret' => '   ',
			'button_text'   => 'New Button',
		]);

		self::assertSame('immutable-secret', $this->config->get('openidconnect', 'client_secret'));
		self::assertSame('New Button', $this->config->get('openidconnect', 'button_text'));
		self::assertContains('OpenID Connect settings saved.', $this->sysmsg->getInfos());
	}

	public function testSaveRequiresDiscoveryUrlWhenProviderFieldsAreSubmitted(): void
	{
		$this->initDi();

		(new AdminHook())->save([
			'discovery_url' => '   ',
			'client_id'     => 'client',
			'client_secret' => 'secret',
		]);

		self::assertContains('OpenID Connect: Discovery URL must be a valid URL.', $this->sysmsg->getNotices());
		self::assertContains('OpenID Connect: Discovery URL is required.', $this->sysmsg->getNotices());
	}

	public function testSaveSkipsReadonlyDiscoveryValidationButStillRequiresWritableProviderFields(): void
	{
		$this->initDi();
		$this->config->set('openidconnect', 'discovery_url', 'https://id.example.com/.well-known/openid-configuration');
		$this->config->set('openidconnect', 'client_secret', 'existing-secret');
		$this->config->getCache()->set('openidconnect', 'discovery_url', '', Cache::SOURCE_ENV);

		(new AdminHook())->save([
			'discovery_url' => 'not-a-url',
			'client_id'     => '   ',
			'client_secret' => 'existing-secret',
		]);

		self::assertContains('OpenID Connect: Client ID is required.', $this->sysmsg->getNotices());
		self::assertNotContains('OpenID Connect: Discovery URL must be a valid URL.', $this->sysmsg->getNotices());
		self::assertNotContains('OpenID Connect: Discovery URL is required.', $this->sysmsg->getNotices());
	}
}
