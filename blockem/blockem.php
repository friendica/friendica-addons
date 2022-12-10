<?php
/**
 * Name: blockem
 * Description: Allows users to hide content by collapsing posts and replies.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Roland Haeder <https://f.haeder.net/roland>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Util\Strings;

global $blockem_words;

function blockem_install()
{
	Hook::register('prepare_body_content_filter', 'addon/blockem/blockem.php', 'blockem_prepare_body_content_filter');
	Hook::register('display_item'               , 'addon/blockem/blockem.php', 'blockem_display_item');
	Hook::register('addon_settings'             , 'addon/blockem/blockem.php', 'blockem_addon_settings');
	Hook::register('addon_settings_post'        , 'addon/blockem/blockem.php', 'blockem_addon_settings_post');
	Hook::register('conversation_start'         , 'addon/blockem/blockem.php', 'blockem_conversation_start');
	Hook::register('item_photo_menu'            , 'addon/blockem/blockem.php', 'blockem_item_photo_menu');
	Hook::register('enotify_store'              , 'addon/blockem/blockem.php', 'blockem_enotify_store');
}

function blockem_addon_settings(App $a, array &$data)
{
	if (!local_user()) {
		return;
	}

	$words   = DI::pConfig()->get(local_user(), 'blockem', 'words', '');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/blockem/');
	$html = Renderer::replaceMacros($t, [
		'$info'    => DI::l10n()->t("Hides user's content by collapsing posts. Also replaces their avatar with generic image."),
		'$words'   => ['blockem-words', DI::l10n()->t('Comma separated profile URLS:'), $words],
	]);

	$data = [
		'addon' => 'blockem',
		'title' => DI::l10n()->t('Blockem'),
		'html'  => $html,
	];
}

function blockem_addon_settings_post(App $a, array &$b)
{
	if (!local_user()) {
		return;
	}

	if (!empty($_POST['blockem-submit'])) {
		DI::pConfig()->set(local_user(), 'blockem', 'words', trim($_POST['blockem-words']));
	}
}

function blockem_enotify_store(App $a, array &$b)
{
	$words = DI::pConfig()->get($b['uid'], 'blockem', 'words');

	if ($words) {
		$arr = explode(',', $words);
	} else {
		return;
	}

	$found = false;

	if (count($arr)) {
		foreach ($arr as $word) {
			if (!strlen(trim($word))) {
				continue;
			}

			if (Strings::compareLink($b['url'], $word)) {
				$found = true;
				break;
			}
		}
	}

	if ($found) {
		// empty out the fields
		$b = [];
	}
}

function blockem_prepare_body_content_filter(App $a, array &$hook_data)
{
	if (!local_user()) {
		return;
	}

	$profiles_string = null;

	if (local_user()) {
		$profiles_string = DI::pConfig()->get(local_user(), 'blockem', 'words');
	}

	if ($profiles_string) {
		$profiles_array = explode(',', $profiles_string);
	} else {
		return;
	}

	$found = false;

	foreach ($profiles_array as $word) {
		if (Strings::compareLink($hook_data['item']['author-link'], trim($word))) {
			$found = true;
			break;
		}
	}

	if ($found) {
		$hook_data['filter_reasons'][] = DI::l10n()->t('Filtered user: %s', $hook_data['item']['author-name']);
	}
}

function blockem_display_item(App $a, array &$b = null)
{
	if (!empty($b['output']['body']) && strstr($b['output']['body'], 'id="blockem-wrap-')) {
		$b['output']['thumb'] = DI::baseUrl()->get() . "/images/person-80.jpg";
	}
}

function blockem_conversation_start(App $a, array &$b)
{
	global $blockem_words;

	if (!local_user()) {
		return;
	}

	$words = DI::pConfig()->get(local_user(), 'blockem', 'words');

	if ($words) {
		$blockem_words = explode(',', $words);
	}

	DI::page()['htmlhead'] .= <<< EOT

<script>
function blockemBlock(author) {
	$.get('blockem?block=' +author, function(data) {
		location.reload(true);
	});
}
function blockemUnblock(author) {
	$.get('blockem?unblock=' +author, function(data) {
		location.reload(true);
	});
}
</script>

EOT;
}

function blockem_item_photo_menu(App $a, array &$b)
{
	global $blockem_words;

	if (!local_user() || $b['item']['self']) {
		return;
	}

	$blocked = false;
	$author = $b['item']['author-link'];

	if (!empty($blockem_words)) {
		foreach($blockem_words as $bloke) {
			if (Strings::compareLink($bloke,$author)) {
				$blocked = true;
				break;
			}
		}
	}
	if ($blocked) {
		$b['menu'][DI::l10n()->t('Unblock Author')] = 'javascript:blockemUnblock(\'' . $author . '\');';
	} else {
		$b['menu'][DI::l10n()->t('Block Author')] = 'javascript:blockemBlock(\'' . $author . '\');';
	}
}

/**
 * This is a statement rather than an actual function definition. The simple
 * existence of this method is checked to figure out if the addon offers a
 * module.
 */
function blockem_module() {}

function blockem_init(App $a)
{
	if (!local_user()) {
		return;
	}

	$words = DI::pConfig()->get(local_user(), 'blockem', 'words');

	if (array_key_exists('block', $_GET) && $_GET['block']) {
		if (strlen($words)) {
			$words .= ',';
		}

		$words .= trim($_GET['block']);
	}

	if (array_key_exists('unblock', $_GET) && $_GET['unblock']) {
		$arr = explode(',',$words);
		$newarr = [];

		if (count($arr)) {
			foreach ($arr as $x) {
				if (!Strings::compareLink(trim($x), trim($_GET['unblock']))) {
					$newarr[] = $x;
				}
			}
		}

		$words = implode(',', $newarr);
	}

	DI::pConfig()->set(local_user(), 'blockem', 'words', $words);
	exit();
}
