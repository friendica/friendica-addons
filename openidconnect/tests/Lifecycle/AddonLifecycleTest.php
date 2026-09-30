<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Lifecycle;

use Friendica\Addon\OpenIdConnect\Lifecycle\AddonLifecycle;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Core\Config\ValueObject\Cache;
use Friendica\Database\Database;

final class AddonLifecycleTest extends DiTestCase
{
	private function entrypoint(): string
	{
		return dirname(__DIR__, 2) . '/openidconnect.php';
	}

	public function testInstallRegistersExpectedHooks(): void
	{
		$dba = $this->mockDba();
		$dba->method('exists')->willReturn(false);
		$dba->expects(self::exactly(5))->method('insert')->willReturn(true);
		$this->initDi([Database::class => $dba]);

		(new AddonLifecycle())->install();
	}

	public function testUninstallRemovesHooksAndClearsCacheButPreservesStoredConfiguration(): void
	{
		$dba = $this->mockDba();
		$dba->expects(self::exactly(10))->method('delete')->willReturn(true);
		$this->initDi([Database::class => $dba]);

		(new AddonLifecycle())->uninstall();

		self::assertNull($this->cache->get('openidconnect:provider_config'));
		self::assertNull($this->cache->get('openidconnect:jwks'));
		self::assertTrue($this->logger->hasRecordMatching('info', 'uninstall complete'));
	}

	public function testLoadConfigLoadsStaticAddonConfigurationIntoConfigCache(): void
	{
		$this->initDi();
		$loader = $this->createMock(ConfigFileManager::class);
		$loader->method('loadAddonConfig')->with('openidconnect')->willReturn(['openidconnect' => ['client_id' => 'client-id']]);
		$configCache = $this->createMock(Cache::class);
		$configCache->expects(self::once())->method('load')->with(['openidconnect' => ['client_id' => 'client-id']], Cache::SOURCE_STATIC);
		$this->appHelper->method('getConfigCache')->willReturn($configCache);

		(new AddonLifecycle())->loadConfig($loader);

		self::assertSame([], $this->logger->records);
	}

	public function testLoadConfigLogsErrorWhenLoaderThrows(): void
	{
		$this->initDi();
		$loader = $this->createMock(ConfigFileManager::class);
		$loader->method('loadAddonConfig')->willThrowException(new \RuntimeException('boom'));

		(new AddonLifecycle())->loadConfig($loader);

		self::assertTrue($this->logger->hasRecordMatching('error', 'failed to load addon config'));
	}
}
