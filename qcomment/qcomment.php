<?php


/**
 * Name: Quick Comment
 * Description: Two click comments
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 * Provides a set of text "snippets" which can be inserted into a comment window by clicking on them.
 * First enable the addon in the system admin panel. 
 * Then each person can tailor their choice of words in Settings->Plugin Settings in the Qcomment 
 * pane. Initially no qcomments are provided, but on viewing the settings page, a default set of
 * of words is suggested. These can be accepted (click Submit) or edited first. Each text line represents 
 * a different qcomment. 
 * Many themes will hide the qcomments above or immediately adjacent to the comment input box until
 * you wish to use them. On some themes they may be visible.
 * Wave the mouse around near the comment input box and the qcomments will show up. Click on any of 
 * them to open the comment window fully and insert the qcomment. Then "Submit" will submit it.
 *
 */
use Friendica\Core\Addon;
use Friendica\Core\PConfig;

function qcomment_install() {
	Addon::registerHook('plugin_settings', 'addon/qcomment/qcomment.php', 'qcomment_addon_settings');
	Addon::registerHook('plugin_settings_post', 'addon/qcomment/qcomment.php', 'qcomment_addon_settings_post');

}


function qcomment_uninstall() {
	Addon::unregisterHook('plugin_settings', 'addon/qcomment/qcomment.php', 'qcomment_addon_settings');
	Addon::unregisterHook('plugin_settings_post', 'addon/qcomment/qcomment.php', 'qcomment_addon_settings_post');

}





function qcomment_addon_settings(&$a, &$s)
{
	if (! local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/qcomment/qcomment.css' . '" media="all" />' . "\r\n";

	$words = PConfig::get(local_user(), 'qcomment', 'words', t(':-)') . "\n" . t(':-(') . "\n" .  t('lol'));

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('Quick Comment Settings') . '</h3>';
	$s .= '<div id="qcomment-wrapper">';
	$s .= '<div id="qcomment-desc">' . t("Quick comments are found near comment boxes, sometimes hidden. Click them to provide simple replies.") . '</div>';
	$s .= '<label id="qcomment-label" for="qcomment-words">' . t('Enter quick comments, one per line') . ' </label>';
	$s .= '<textarea id="qcomment-words" type="text" name="qcomment-words" >' . htmlspecialchars(unxmlify($words)) . '</textarea>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="qcomment-submit" name="qcomment-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>';
	$s .= '</div>';

	return;
}

function qcomment_addon_settings_post(&$a,&$b) {

	if(! local_user())
		return;

	if($_POST['qcomment-submit']) {
		PConfig::set(local_user(),'qcomment','words',xmlify($_POST['qcomment-words']));
		info( t('Quick Comment settings saved.') . EOL);
	}
}

