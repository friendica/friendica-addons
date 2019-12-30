<?php
/**
 * Name: Quick Comment
 * Description: Two click comments
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 * Provides a set of text "snippets" which can be inserted into a comment window by clicking on them.
 * First enable the addon in the system admin panel.
 * Then each person can tailor their choice of words in Settings->Addon Settings in the Qcomment
 * pane. Initially no qcomments are provided, but on viewing the settings page, a default set of
 * of words is suggested. These can be accepted (click Submit) or edited first. Each text line represents
 * a different qcomment.
 * Many themes will hide the qcomments above or immediately adjacent to the comment input box until
 * you wish to use them. On some themes they may be visible.
 * Wave the mouse around near the comment input box and the qcomments will show up. Click on any of
 * them to open the comment window fully and insert the qcomment. Then "Submit" will submit it.
 *
 */
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;
use Friendica\DI;
use Friendica\Util\XML;

function qcomment_install() {
	Hook::register('addon_settings', 'addon/qcomment/qcomment.php', 'qcomment_addon_settings');
	Hook::register('addon_settings_post', 'addon/qcomment/qcomment.php', 'qcomment_addon_settings_post');

}

function qcomment_uninstall() {
	Hook::unregister('addon_settings', 'addon/qcomment/qcomment.php', 'qcomment_addon_settings');
	Hook::unregister('addon_settings_post', 'addon/qcomment/qcomment.php', 'qcomment_addon_settings_post');

}

function qcomment_addon_settings(&$a, &$s)
{
	if (! local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl()->get() . '/addon/qcomment/qcomment.css' . '" media="all" />' . "\r\n";

	$words = PConfig::get(local_user(), 'qcomment', 'words', L10n::t(':-)') . "\n" . L10n::t(':-(') . "\n" .  L10n::t('lol'));

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . L10n::t('Quick Comment Settings') . '</h3>';
	$s .= '<div id="qcomment-wrapper">';
	$s .= '<div id="qcomment-desc">' . L10n::t("Quick comments are found near comment boxes, sometimes hidden. Click them to provide simple replies.") . '</div>';
	$s .= '<label id="qcomment-label" for="qcomment-words">' . L10n::t('Enter quick comments, one per line') . ' </label>';
	$s .= '<textarea id="qcomment-words" type="text" name="qcomment-words" >' . htmlspecialchars(XML::unescape($words)) . '</textarea>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="qcomment-submit" name="qcomment-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
	$s .= '</div>';

	return;
}

function qcomment_addon_settings_post(&$a, &$b)
{
	if (! local_user()) {
		return;
	}

	if ($_POST['qcomment-submit']) {
		PConfig::set(local_user(), 'qcomment', 'words', XML::escape($_POST['qcomment-words']));
		info(L10n::t('Quick Comment settings saved.') . EOL);
	}
}
