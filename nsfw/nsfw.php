<?php

/**
 * Name: NSFW
 * Description: Collapse posts with inappropriate content
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */
use Friendica\Core\Addon;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;

function nsfw_install()
{
	Addon::registerHook('prepare_body_content_filter', 'addon/nsfw/nsfw.php', 'nsfw_prepare_body_content_filter', 10);
	Addon::registerHook('addon_settings', 'addon/nsfw/nsfw.php', 'nsfw_addon_settings');
	Addon::registerHook('addon_settings_post', 'addon/nsfw/nsfw.php', 'nsfw_addon_settings_post');
}

function nsfw_uninstall()
{
	Addon::unregisterHook('prepare_body_content_filter', 'addon/nsfw/nsfw.php', 'nsfw_prepare_body_content_filter');
	Addon::unregisterHook('prepare_body', 'addon/nsfw/nsfw.php', 'nsfw_prepare_body');
	Addon::unregisterHook('addon_settings', 'addon/nsfw/nsfw.php', 'nsfw_addon_settings');
	Addon::unregisterHook('addon_settings_post', 'addon/nsfw/nsfw.php', 'nsfw_addon_settings_post');
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

function nsfw_addon_settings(&$a, &$s)
{
	if (!local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/nsfw/nsfw.css' . '" media="all" />' . "\r\n";

	$enable_checked = (intval(PConfig::get(local_user(), 'nsfw', 'disable')) ? '' : ' checked="checked" ');
	$words = PConfig::get(local_user(), 'nsfw', 'words');
	if (!$words) {
		$words = 'nsfw,';
	}

	$s .= '<span id="settings_nsfw_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_nsfw_expanded\'); openClose(\'settings_nsfw_inflated\');">';
	$s .= '<h3>' . L10n::t('Content Filter (NSFW and more)') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_nsfw_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_nsfw_expanded\'); openClose(\'settings_nsfw_inflated\');">';
	$s .= '<h3>' . L10n::t('Content Filter (NSFW and more)') . '</h3>';
	$s .= '</span>';

	$s .= '<div id="nsfw-wrapper">';
	$s .= '<p>' . L10n::t('This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.') . '</p>';
	$s .= '<label id="nsfw-enable-label" for="nsfw-enable">' . L10n::t('Enable Content filter') . ' </label>';
	$s .= '<input id="nsfw-enable" type="checkbox" name="nsfw-enable" value="1"' . $enable_checked . ' />';
	$s .= '<div class="clear"></div>';
	$s .= '<label id="nsfw-label" for="nsfw-words">' . L10n::t('Comma separated list of keywords to hide') . ' </label>';
	$s .= '<textarea id="nsfw-words" type="text" name="nsfw-words">' . $words . '</textarea>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="nsfw-submit" name="nsfw-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
	$s .= '<div class="nsfw-desc">' . L10n::t('Use /expression/ to provide regular expressions') . '</div></div>';
	return;
}

function nsfw_addon_settings_post(&$a, &$b)
{
	if (!local_user()) {
		return;
	}

	if ($_POST['nsfw-submit']) {
		PConfig::set(local_user(), 'nsfw', 'words', trim($_POST['nsfw-words']));
		$enable = (x($_POST, 'nsfw-enable') ? intval($_POST['nsfw-enable']) : 0);
		$disable = 1 - $enable;
		PConfig::set(local_user(), 'nsfw', 'disable', $disable);
		info(L10n::t('NSFW Settings saved.') . EOL);
	}
}

function nsfw_prepare_body_content_filter(\Friendica\App $a, &$hook_data)
{
	$words = null;
	if (PConfig::get(local_user(), 'nsfw', 'disable')) {
		return;
	}

	if (local_user()) {
		$words = PConfig::get(local_user(), 'nsfw', 'words');
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
			$hook_data['filter_reasons'][] = L10n::t('Filtered tag: %s', $word);
		} else {
			$hook_data['filter_reasons'][] = L10n::t('Filtered word: %s', $word);
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
