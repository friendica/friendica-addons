<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Lifecycle;

use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Core\Config\ValueObject\Cache;
use Friendica\Core\Hook;
use Friendica\DI;

final class AddonLifecycle
{
	private function addonEntrypoint(): string
	{
		return dirname(__DIR__, 2) . '/openidconnect.php';
	}

	public function install(): void
	{
		$entrypoint = $this->addonEntrypoint();

		Hook::register('load_config', $entrypoint, 'openidconnect_load_config');
		Hook::register('login_hook', $entrypoint, 'openidconnect_sso_initiate');
		Hook::register('logging_out', $entrypoint, 'openidconnect_logout');
		Hook::register('page_end', $entrypoint, 'openidconnect_page_end');
		Hook::register('addon_settings', $entrypoint, 'openidconnect_addon_settings');
	}

	public function uninstall(): void
	{
		$entrypoint = $this->addonEntrypoint();

		Hook::unregister('load_config', $entrypoint, 'openidconnect_load_config');
		Hook::unregister('login_hook', $entrypoint, 'openidconnect_sso_initiate');
		Hook::unregister('logging_out', $entrypoint, 'openidconnect_logout');
		Hook::unregister('page_end', $entrypoint, 'openidconnect_page_end');
		Hook::unregister('addon_settings', $entrypoint, 'openidconnect_addon_settings');

		DI::cache()->delete('openidconnect:provider_config');
		DI::cache()->delete('openidconnect:jwks');

		// Preserve stored config and account-link data so a disable/re-enable cycle does not break SSO recovery.
		DI::logger()->info('openidconnect: uninstall complete — hooks removed, stored config preserved');
	}

	public function loadConfig(ConfigFileManager $loader): void
	{
		try {
			$config = $loader->loadAddonConfig('openidconnect');
			if (!is_array($config)) {
				DI::logger()->warning('openidconnect: addon config loader returned non-array', ['type' => gettype($config)]);
				return;
			}

			DI::appHelper()->getConfigCache()->load($config, Cache::SOURCE_STATIC);
		} catch (\Throwable $e) {
			DI::logger()->error('openidconnect: failed to load addon config', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
			]);
		}
	}
}
