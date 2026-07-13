<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Lifecycle;

use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Core\Config\ValueObject\Cache;
use Friendica\Core\Hook;
use Friendica\Database\DBA;
use Friendica\DI;

final class AddonLifecycle
{
	private const CONFIG_KEYS = [
		'discovery_url',
		'client_id',
		'client_secret',
		'scopes',
		'button_text',
		'auto_create_accounts',
		'allow_unverified_email',
		'idp_signout',
		'transparent_sso',
		'transparent_sso_prompt_none',
	];

	public function install(): void
	{
		Hook::register('load_config', __FILE__, 'openidconnect_load_config');
		Hook::register('login_hook', __FILE__, 'openidconnect_sso_initiate');
		Hook::register('logging_out', __FILE__, 'openidconnect_logout');
		Hook::register('page_end', __FILE__, 'openidconnect_page_end');
		Hook::register('addon_settings', __FILE__, 'openidconnect_addon_settings');
	}

	public function uninstall(): void
	{
		Hook::unregister('load_config', __FILE__, 'openidconnect_load_config');
		Hook::unregister('login_hook', __FILE__, 'openidconnect_sso_initiate');
		Hook::unregister('logging_out', __FILE__, 'openidconnect_logout');
		Hook::unregister('page_end', __FILE__, 'openidconnect_page_end');
		Hook::unregister('addon_settings', __FILE__, 'openidconnect_addon_settings');

		DBA::delete('pconfig', ['cat' => 'openidconnect']);

		foreach (self::CONFIG_KEYS as $key) {
			DI::config()->delete('openidconnect', $key);
		}

		DI::cache()->delete('openidconnect:provider_config');
		DI::cache()->delete('openidconnect:jwks');

		DI::logger()->info('openidconnect: uninstall complete — hooks, pconfig, and global config cleared');
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
