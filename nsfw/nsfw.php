<?php

/**
 * Name: NSFW
 * Description: Collapse posts with inappropriate content
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

function nsfw_install()
{
	Hook::register('prepare_body_content_filter', 'addon/nsfw/nsfw.php', 'nsfw_prepare_body_content_filter', 10);
	Hook::register('addon_settings', 'addon/nsfw/nsfw.php', 'nsfw_addon_settings');
	Hook::register('addon_settings_post', 'addon/nsfw/nsfw.php', 'nsfw_addon_settings_post');
}

// This function isn't perfect and isn't trying to preserve the html structure - it's just a
// quick and dirty filter to pull out embedded photo blobs because 'nsfw' seems to come up
// inside them quite often. We don't need anything fancy, just pull out the data blob so we can
// check against the rest of the body.

function nsfw_extract_photos($body)
{
	$new_body = '';

	$img_start = strpos($body, 'src="data:');
	$img_end = (($img_start !== false) ? strpos(substr($body, $img_start), '>') : false);

	$cnt = 0;

	while ($img_end !== false) {
		$img_end += $img_start;
		$new_body = $new_body . substr($body, 0, $img_start);

		$cnt ++;
		$body = substr($body, 0, $img_end);

		$img_start = strpos($body, 'src="data:');
		$img_end = (($img_start !== false) ? strpos(substr($body, $img_start), '>') : false);
	}

	if (!$cnt) {
		return $body;
	}
	return $new_body;
}

function nsfw_addon_settings(App &$a, array &$data)
{
	if (!local_user()) {
		return;
	}

	$enabled = !DI::pConfig()->get(local_user(), 'nsfw', 'disable');
	$words   = DI::pConfig()->get(local_user(), 'nsfw', 'words', 'nsfw,');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/nsfw/');
	$html = Renderer::replaceMacros($t, [
		'$info'    => DI::l10n()->t('This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.'),
		'$enabled' => ['nsfw-enable', DI::l10n()->t('Enable Content filter'), $enabled],
		'$words'   => ['nsfw-words', DI::l10n()->t('Comma separated list of keywords to hide'), $words, DI::l10n()->t('Use /expression/ to provide regular expressions')],
	]);

	$data = [
		'addon' => 'nsfw',
		'title' => DI::l10n()->t('Content Filter (NSFW and more)'),
		'html'  => $html,
	];
}

function nsfw_addon_settings_post(App $a, array &$b)
{
	if (!local_user()) {
		return;
	}

	if (!empty($_POST['nsfw-submit'])) {
		DI::pConfig()->set(local_user(), 'nsfw', 'words', trim($_POST['nsfw-words']));
		$enable = (!empty($_POST['nsfw-enable']) ? intval($_POST['nsfw-enable']) : 0);
		$disable = 1 - $enable;
		DI::pConfig()->set(local_user(), 'nsfw', 'disable', $disable);
	}
}

function nsfw_prepare_body_content_filter(App $a, &$hook_data)
{
	$words = null;
	if (DI::pConfig()->get(local_user(), 'nsfw', 'disable')) {
		return;
	}

	if (local_user()) {
		$words = DI::pConfig()->get(local_user(), 'nsfw', 'words');
	}

	if ($words) {
		$word_list = explode(',', $words);
	} else {
		$word_list = ['nsfw'];
	}

	$found = false;
	if (count($word_list)) {
		$body = $hook_data['item']['title'] . "\n" . nsfw_extract_photos($hook_data['item']['body']);

		foreach ($word_list as $word) {
			$word = trim($word);
			if (!strlen($word)) {
				continue;
			}

			$tag_search = false;
			switch ($word[0]) {
				case '/'; // Regular expression
					$found = preg_match($word, $body);
					break;
				case '#': // Hashtag-only search
					$tag_search = true;
					$found = nsfw_find_word_in_item_tags($hook_data['item']['hashtags'], substr($word, 1));
					break;
				default:
					$found = stripos($body, $word) !== false || nsfw_find_word_in_item_tags($hook_data['item']['tags'], $word);
					break;
			}

			if ($found) {
				break;
			}
		}
	}

	if ($found) {
		if ($tag_search) {
			$hook_data['filter_reasons'][] = DI::l10n()->t('Filtered tag: %s', $word);
		} else {
			$hook_data['filter_reasons'][] = DI::l10n()->t('Filtered word: %s', $word);
		}
	}
}

function nsfw_find_word_in_item_tags($item_tags, $word)
{
	if (is_array($item_tags)) {
		foreach ($item_tags as $tag) {
			if (stripos($tag, '>' . $word . '<') !== false) {
				return true;
			}
		}
	}

	return false;
}
