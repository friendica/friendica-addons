<?php

/*
 * Name: Mastodon Custom Emojis
 * Description: Replace emojis shortcodes in Mastodon posts with their originating server custom emojis images.
 * Version: 1.0
 * Author: Hypolite Petovan
 * Author: Roland Haeder
 * Status: Unsupported
 */

use Friendica\App;
use Friendica\Content\Smilies;
use Friendica\Core\Cache\Duration;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\Protocol;
use Friendica\DI;
use Friendica\Util\Network;
use Friendica\Util\Proxy as ProxyUtils;

function mastodoncustomemojis_install()
{
	Hook::register('put_item_in_cache',  __FILE__, 'mastodoncustomemojis_put_item_in_cache');
	Hook::register('network_mod_init',   __FILE__, 'mastodoncustomemojis_css_hook');
	Hook::register('display_mod_init',   __FILE__, 'mastodoncustomemojis_css_hook');
	Hook::register('search_mod_init',    __FILE__, 'mastodoncustomemojis_css_hook');
	Hook::register('community_mod_init', __FILE__, 'mastodoncustomemojis_css_hook');
	Hook::register('contacts_mod_init',  __FILE__, 'mastodoncustomemojis_css_hook');
}

function mastodoncustomemojis_uninstall()
{
	Hook::unregister('put_item_in_cache',  __FILE__, 'mastodoncustomemojis_put_item_in_cache');
	Hook::unregister('network_mod_init',   __FILE__, 'mastodoncustomemojis_css_hook');
	Hook::unregister('display_mod_init',   __FILE__, 'mastodoncustomemojis_css_hook');
	Hook::unregister('search_mod_init',    __FILE__, 'mastodoncustomemojis_css_hook');
	Hook::unregister('community_mod_init', __FILE__, 'mastodoncustomemojis_css_hook');
	Hook::unregister('contacts_mod_init',  __FILE__, 'mastodoncustomemojis_css_hook');
}

function mastodoncustomemojis_css_hook(App $a)
{
	DI::page()['htmlhead'] .= <<<HTML
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
	// Mastodon uses OStatus and ActivityPub, skipping other network protocols
	if (empty($hook_data['item']['author-link']) || !in_array($hook_data['item']['network'], [Protocol::OSTATUS, Protocol::ACTIVITYPUB])) {
		return;
	}

	$emojis = mastodoncustomemojis_get_custom_emojis_for_author($hook_data['item']['author-link']);

	$hook_data["rendered-html"] = Smilies::replaceFromArray($hook_data["rendered-html"], $emojis);
}

function mastodoncustomemojis_get_custom_emojis_for_author($author_link)
{
	$url_parts = parse_url($author_link);

	$api_base_url = $url_parts['scheme'] . '://' . $url_parts['host'] . (isset($url_parts['port']) ? ':' . $url_parts['port'] : '');

	$cache_key = 'mastodoncustomemojis:' . $api_base_url;

	$return = DI::cache()->get($cache_key);

	if (empty($return) || Config::get('system', 'ignore_cache')) {
		$return = mastodoncustomemojis_fetch_custom_emojis_for_url($api_base_url);

		DI::cache()->set($cache_key, $return, empty($return['texts']) ? Duration::QUARTER_HOUR : Duration::HOUR);
	}

	return $return;
}

function mastodoncustomemojis_fetch_custom_emojis_for_url($api_base_url)
{
	$return = ['texts' => [], 'icons' => []];

	$api_url = $api_base_url . '/api/v1/custom_emojis';

	$fetchResult = Network::fetchUrlFull($api_url);

	if ($fetchResult->isSuccess()) {
		$emojis_array = json_decode($fetchResult->getBody(), true);

		if (is_array($emojis_array) && count($emojis_array)) {
			foreach ($emojis_array as $emoji) {
				if (!empty($emoji['shortcode']) && !empty($emoji['static_url'])) {
					$return['texts'][] = ':' . $emoji['shortcode'] . ':';
					$return['icons'][] = '<img class="emoji mastodon" src="' . ProxyUtils::proxifyUrl($emoji['static_url']) . '" alt=":' . $emoji['shortcode'] . ':" title=":' . $emoji['shortcode'] . ':"/>';
				}
			}
		}
	}

	return $return;
}
