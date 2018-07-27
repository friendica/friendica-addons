<?php

/*
 * Name: Mastodon Custom Emojis
 * Description: Replace emojis shortcodes in Mastodon posts with their originating server custom emojis images.
 * Version: 1.0
 * Author: Hypolite Petovan
 * Author: Roland Haeder
 */

use Friendica\App;
use Friendica\Content\Smilies;
use Friendica\Core\Addon;
use Friendica\Core\Cache;
use Friendica\Core\Config;
use Friendica\Core\Protocol;
use Friendica\Network\Proxy;
use Friendica\Util\Network;

function mastodoncustomemojis_install()
{
	Addon::registerHook('put_item_in_cache',  __FILE__, 'mastodoncustomemojis_put_item_in_cache');
	Addon::registerHook('network_mod_init',   __FILE__, 'mastodoncustomemojis_css_hook');
	Addon::registerHook('display_mod_init',   __FILE__, 'mastodoncustomemojis_css_hook');
	Addon::registerHook('search_mod_init',    __FILE__, 'mastodoncustomemojis_css_hook');
	Addon::registerHook('community_mod_init', __FILE__, 'mastodoncustomemojis_css_hook');
	Addon::registerHook('contacts_mod_init',  __FILE__, 'mastodoncustomemojis_css_hook');
}

function mastodoncustomemojis_uninstall()
{
	Addon::unregisterHook('put_item_in_cache',  __FILE__, 'mastodoncustomemojis_put_item_in_cache');
	Addon::unregisterHook('network_mod_init',   __FILE__, 'mastodoncustomemojis_css_hook');
	Addon::unregisterHook('display_mod_init',   __FILE__, 'mastodoncustomemojis_css_hook');
	Addon::unregisterHook('search_mod_init',    __FILE__, 'mastodoncustomemojis_css_hook');
	Addon::unregisterHook('community_mod_init', __FILE__, 'mastodoncustomemojis_css_hook');
	Addon::unregisterHook('contacts_mod_init',  __FILE__, 'mastodoncustomemojis_css_hook');
}

function mastodoncustomemojis_css_hook(App $a)
{
	$a->page['htmlhead'] .= <<<HTML
<!-- Style added by mastodoncustomemojis -->
<style type="text/css">
	.emoji.mastodon {
		height: 1.2em;
		vertical-align: middle;
	}
</style>


HTML;
}

function mastodoncustomemojis_put_item_in_cache(App $a, array &$hook_data)
{
	// Mastodon uses OStatus, skipping other network protocols
	if (empty($hook_data['item']['author-link']) || ($hook_data['item']['network'] != Protocol::OSTATUS)) {
		return;
	}

	$emojis = mastodoncustomemojis_get_custom_emojis_for_author($hook_data['item']['author-link']);

	$hook_data["rendered-html"] = Smilies::replaceFromArray($hook_data["rendered-html"], $emojis);
}

function mastodoncustomemojis_get_custom_emojis_for_author($author_link)
{
	$return = ['texts' => [], 'icons' => []];

	$url_parts = parse_url($author_link);

	$api_base_url = $url_parts['scheme'] . '://' . $url_parts['host'] . (isset($url_parts['port']) ? ':' . $url_parts['port'] : '');

	$cache_key = 'mastodoncustomemojis:' . $api_base_url;

	$emojis = Cache::get($cache_key);
	if (empty($emojis['texts']) || Config::get('system', 'ignore_cache')) {
		// Reset the emojis array
		$emojis = $return;

		$api_url = $api_base_url . '/api/v1/custom_emojis';

		$ret = Network::fetchUrlFull($api_url);

		if ($ret['success']) {
			$emojis_array = json_decode($ret['body'], true);

			if (is_array($emojis_array)) {
				foreach ($emojis_array as $emoji) {
					$emojis['texts'][] = ':' . $emoji['shortcode'] . ':';
					$emojis['icons'][] = '<img class="emoji mastodon" src="' . Proxy::proxifyUrl($emoji['static_url']) . '" alt=":' . $emoji['shortcode'] . ':" title=":' . $emoji['shortcode'] . ':"/>';
				}
			}

			$ttl = Cache::WEEK;
		} else {
			$ttl = Cache::HALF_HOUR;
		}

		Cache::set($cache_key, $emojis, $ttl);

		$return = $emojis;
	}

	return $return;
}
