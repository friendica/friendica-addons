<?php
/**
 * Name: blockem
 * Description: Allows users to hide content by collapsing posts and replies.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */
use Friendica\Core\Addon;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;

function blockem_install()
{
	Addon::registerHook('prepare_body', 'addon/blockem/blockem.php', 'blockem_prepare_body');
	Addon::registerHook('display_item', 'addon/blockem/blockem.php', 'blockem_display_item');
	Addon::registerHook('addon_settings', 'addon/blockem/blockem.php', 'blockem_addon_settings');
	Addon::registerHook('addon_settings_post', 'addon/blockem/blockem.php', 'blockem_addon_settings_post');
	Addon::registerHook('conversation_start', 'addon/blockem/blockem.php', 'blockem_conversation_start');
	Addon::registerHook('item_photo_menu', 'addon/blockem/blockem.php', 'blockem_item_photo_menu');
	Addon::registerHook('enotify_store', 'addon/blockem/blockem.php', 'blockem_enotify_store');
}

function blockem_uninstall()
{
	Addon::unregisterHook('prepare_body', 'addon/blockem/blockem.php', 'blockem_prepare_body');
	Addon::unregisterHook('display_item', 'addon/blockem/blockem.php', 'blockem_display_item');
	Addon::unregisterHook('addon_settings', 'addon/blockem/blockem.php', 'blockem_addon_settings');
	Addon::unregisterHook('addon_settings_post', 'addon/blockem/blockem.php', 'blockem_addon_settings_post');
	Addon::unregisterHook('conversation_start', 'addon/blockem/blockem.php', 'blockem_conversation_start');
	Addon::unregisterHook('item_photo_menu', 'addon/blockem/blockem.php', 'blockem_item_photo_menu');
	Addon::unregisterHook('enotify_store', 'addon/blockem/blockem.php', 'blockem_enotify_store');
}

function blockem_addon_settings(&$a, &$s)
{

	if(! local_user())
		return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/blockem/blockem.css' . '" media="all" />' . "\r\n";


	$words = PConfig::get(local_user(), 'blockem', 'words');
	if(! $words)
		$words = '';

    $s .= '<span id="settings_blockem_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_blockem_expanded\'); openClose(\'settings_blockem_inflated\');">';
    $s .= '<h3>' . L10n::t('"Blockem"') . '</h3>';
    $s .= '</span>';
    $s .= '<div id="settings_blockem_expanded" class="settings-block" style="display: none;">';
    $s .= '<span class="fakelink" onclick="openClose(\'settings_blockem_expanded\'); openClose(\'settings_blockem_inflated\');">';
    $s .= '<h3>' . L10n::t('"Blockem"') . '</h3>';
    $s .= '</span>';

    $s .= '<div id="blockem-wrapper">';
    $s .= '<div id="blockem-desc">'. L10n::t("Hides user's content by collapsing posts. Also replaces their avatar with generic image.") . ' </div>';
    $s .= '<label id="blockem-label" for="blockem-words">' . L10n::t('Comma separated profile URLS:') . ' </label>';
    $s .= '<textarea id="blockem-words" type="text" name="blockem-words" >' . htmlspecialchars($words) . '</textarea>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="blockem-submit" name="blockem-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';

	return;

}

function blockem_addon_settings_post(&$a,&$b) {

	if(! local_user())
		return;

	if($_POST['blockem-submit']) {
		PConfig::set(local_user(),'blockem','words',trim($_POST['blockem-words']));
		info(L10n::t('BLOCKEM Settings saved.') . EOL);
	}
}

function blockem_enotify_store(&$a,&$b) {

	$words = PConfig::get($b['uid'],'blockem','words');
	if($words) {
		$arr = explode(',',$words);
	}
	else {
		return;
	}

	$found = false;
	if(count($arr)) {
		foreach($arr as $word) {
			if(! strlen(trim($word))) {
				continue;
			}

			if(link_compare($b['url'],$word)) {
				$found = true;
				break;
			}
		}
	}
	if($found) {
		$b['abort'] = true;
	}
}

function blockem_prepare_body(&$a,&$b) {

	if(! local_user())
		return;

	$words = null;
	if(local_user()) {
		$words = PConfig::get(local_user(),'blockem','words');
	}
	if($words) {
		$arr = explode(',',$words);
	}
	else {
		return;
	}

	$found = false;
	if(count($arr)) {
		foreach($arr as $word) {
			if(! strlen(trim($word))) {
				continue;
			}

			if(link_compare($b['item']['author-link'],$word)) {
				$found = true;
				break;
			}
		}
	}
	if($found) {
		$rnd = random_string(8);
		$b['html'] = '<div id="blockem-wrap-' . $rnd . '" class="fakelink" onclick=openClose(\'blockem-' . $rnd . '\'); >' . L10n::t('Hidden content by %s - Click to open/close', $word) . '</div><div id="blockem-' . $rnd . '" style="display: none; " >' . $b['html'] . '</div>';
	}
}

function blockem_display_item(&$a,&$b) {
	if(strstr($b['output']['body'],'id="blockem-wrap-'))
		$b['output']['thumb'] = $a->get_baseurl() . "/images/person-80.jpg";
}

function blockem_conversation_start(&$a,&$b) {

	if(! local_user())
		return;

	$words = PConfig::get(local_user(),'blockem','words');
	if($words) {
		$a->data['blockem'] = explode(',',$words);
	}
	$a->page['htmlhead'] .= <<< EOT

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

function blockem_item_photo_menu(&$a,&$b) {

	if((! local_user()) || ($b['item']['self']))
		return;

	$blocked = false;
	$author = $b['item']['author-link'];
	if(is_array($a->data['blockem'])) {
		foreach($a->data['blockem'] as $bloke) {
			if(link_compare($bloke,$author)) {
				$blocked = true;
				break;
			}
		}
	}
	if($blocked)
		$b['menu'][L10n::t('Unblock Author')] = 'javascript:blockemUnblock(\'' . $author . '\');';
	else
		$b['menu'][L10n::t('Block Author')] = 'javascript:blockemBlock(\'' . $author . '\');';
}

function blockem_module() {}


function blockem_init(&$a) {

	if(! local_user())
		return;

	$words = PConfig::get(local_user(),'blockem','words');

	if(array_key_exists('block',$_GET) && $_GET['block']) {
		if(strlen($words))
			$words .= ',';
		$words .= trim($_GET['block']);
	}
	if(array_key_exists('unblock',$_GET) && $_GET['unblock']) {
		$arr = explode(',',$words);
		$newarr = [];

		if(count($arr)) {
			foreach($arr as $x) {
				if(! link_compare(trim($x),trim($_GET['unblock'])))
					$newarr[] = $x;
			}
		}
		$words = implode(',',$newarr);
	}

	PConfig::set(local_user(),'blockem','words',$words);
	info(L10n::t('blockem settings updated') . EOL );
	killme();
}
