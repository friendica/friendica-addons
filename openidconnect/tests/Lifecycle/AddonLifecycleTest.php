<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Lifecycle;

use Friendica\Addon\OpenIdConnect\Lifecycle\AddonLifecycle;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Core\Config\ValueObject\Cache;
use Friendica\Core\Hook;
use Friendica\Database\DBA;
use Friendica\DI;

final class AddonLifecycleTest extends AddonTestCase
{
	public function testInstallRegistersExpectedHooks(): void
	{
		$lifecycle = new AddonLifecycle();

		$lifecycle->install();

		self::assertSame([
			['hook' => 'load_config', 'file' => dirname(__DIR__, 2) . '/src/Lifecycle/AddonLifecycle.php', 'callback' => 'openidconnect_load_config'],
			['hook' => 'login_hook', 'file' => dirname(__DIR__, 2) . '/src/Lifecycle/AddonLifecycle.php', 'callback' => 'openidconnect_sso_initiate'],
			['hook' => 'logging_out', 'file' => dirname(__DIR__, 2) . '/src/Lifecycle/AddonLifecycle.php', 'callback' => 'openidconnect_logout'],
			['hook' => 'page_end', 'file' => dirname(__DIR__, 2) . '/src/Lifecycle/AddonLifecycle.php', 'callback' => 'openidconnect_page_end'],
			['hook' => 'addon_settings', 'file' => dirname(__DIR__, 2) . '/src/Lifecycle/AddonLifecycle.php', 'callback' => 'openidconnect_addon_settings'],
		], Hook::$registerCalls);
	}

	public function testUninstallRemovesHooksAndSensitiveConfiguration(): void
	{
		$lifecycle = new AddonLifecycle();

		$lifecycle->uninstall();

		self::assertSame([
			['hook' => 'load_config', 'file' => dirname(__DIR__, 2) . '/src/Lifecycle/AddonLifecycle.php', 'callback' => 'openidconnect_load_config'],
			['hook' => 'login_hook', 'file' => dirname(__DIR__, 2) . '/src/Lifecycle/AddonLifecycle.php', 'callback' => 'openidconnect_sso_initiate'],
			['hook' => 'logging_out', 'file' => dirname(__DIR__, 2) . '/src/Lifecycle/AddonLifecycle.php', 'callback' => 'openidconnect_logout'],
			['hook' => 'page_end', 'file' => dirname(__DIR__, 2) . '/src/Lifecycle/AddonLifecycle.php', 'callback' => 'openidconnect_page_end'],
			['hook' => 'addon_settings', 'file' => dirname(__DIR__, 2) . '/src/Lifecycle/AddonLifecycle.php', 'callback' => 'openidconnect_addon_settings'],
		], Hook::$unregisterCalls);

		self::assertSame([
			['table' => 'pconfig', 'condition' => ['cat' => 'openidconnect']],
		], DBA::$deleteCalls);

		self::assertSame([
			['openidconnect', 'discovery_url'],
			['openidconnect', 'client_id'],
			['openidconnect', 'client_secret'],
			['openidconnect', 'scopes'],
			['openidconnect', 'button_text'],
			['openidconnect', 'auto_create_accounts'],
			['openidconnect', 'allow_unverified_email'],
			['openidconnect', 'idp_signout'],
			['openidconnect', 'transparent_sso'],
			['openidconnect', 'transparent_sso_prompt_none'],
		], DI::config()->deleteCalls);

		self::assertSame([
			'openidconnect:provider_config',
			'openidconnect:jwks',
		], DI::cache()->deleteCalls);

		self::assertSame('openidconnect: uninstall complete — hooks, pconfig, and global config cleared', DI::logger()->infos[0][0]);
	}

	public function testLoadConfigLoadsStaticAddonConfigurationIntoConfigCache(): void
	{
		$loader = new ConfigFileManager();
		$loader->nextAddonConfig = ['openidconnect' => ['client_id' => 'client-id']];
		$lifecycle = new AddonLifecycle();

		$lifecycle->loadConfig($loader);

		self::assertSame(['openidconnect'], $loader->loadAddonConfigCalls);
		self::assertSame([
			['config' => ['openidconnect' => ['client_id' => 'client-id']], 'source' => Cache::SOURCE_STATIC],
		], DI::appHelper()->getConfigCache()->loadCalls);
		self::assertSame([], DI::logger()->warnings);
		self::assertSame([], DI::logger()->errors);
	}

	public function testLoadConfigWarnsWhenLoaderReturnsNonArray(): void
	{
		$loader = new ConfigFileManager();
		$loader->nextAddonConfig = 'invalid';
		$lifecycle = new AddonLifecycle();

		$lifecycle->loadConfig($loader);

		self::assertSame([], DI::appHelper()->getConfigCache()->loadCalls);
		self::assertSame('openidconnect: addon config loader returned non-array', DI::logger()->warnings[0][0]);
		self::assertSame(['type' => 'string'], DI::logger()->warnings[0][1]);
	}

	public function testLoadConfigLogsErrorWhenLoaderThrows(): void
	{
		$loader = new ConfigFileManager();
		$loader->nextException = new \RuntimeException('boom');
		$lifecycle = new AddonLifecycle();

		$lifecycle->loadConfig($loader);

		self::assertSame([], DI::appHelper()->getConfigCache()->loadCalls);
		self::assertSame('openidconnect: failed to load addon config', DI::logger()->errors[0][0]);
		self::assertSame('boom', DI::logger()->errors[0][1]['error']);
		self::assertIsString(DI::logger()->errors[0][1]['trace']);
	}
}