<?php
/**
 * Name: Gnot
 * Description: Thread email comment notifications on Gmail and anonymise them
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 *
 */
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;

function gnot_install() {

	Hook::register('addon_settings', 'addon/gnot/gnot.php', 'gnot_settings');
	Hook::register('addon_settings_post', 'addon/gnot/gnot.php', 'gnot_settings_post');
	Hook::register('enotify_mail', 'addon/gnot/gnot.php', 'gnot_enotify_mail');

	Logger::log("installed gnot");
}


function gnot_uninstall() {

	Hook::unregister('addon_settings', 'addon/gnot/gnot.php', 'gnot_settings');
	Hook::unregister('addon_settings_post', 'addon/gnot/gnot.php', 'gnot_settings_post');
	Hook::unregister('enotify_mail', 'addon/gnot/gnot.php', 'gnot_enotify_mail');


	Logger::log("removed gnot");
}



/**
 *
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 */

function gnot_settings_post($a,$post) {
	if(! local_user() || empty($_POST['gnot-submit']))
		return;

	PConfig::set(local_user(),'gnot','enable',intval($_POST['gnot']));
	info(L10n::t('Gnot settings updated.') . EOL);
}


/**
 *
 * Called from the Addon Setting form. 
 * Add our own settings info to the page.
 *
 */



function gnot_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/gnot/gnot.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$gnot = intval(PConfig::get(local_user(),'gnot','enable'));

	$gnot_checked = (($gnot) ? ' checked="checked" ' : '' );
	
	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . L10n::t('Gnot Settings') . '</h3>';
	$s .= '<div id="gnot-wrapper">';
	$s .= '<div id="gnot-desc">' . L10n::t("Allows threading of email comment notifications on Gmail and anonymising the subject line.") . '</div>';
	$s .= '<label id="gnot-label" for="gnot">' . L10n::t('Enable this addon?') . '</label>';
	$s .= '<input id="gnot-input" type="checkbox" name="gnot" value="1"'.  $gnot_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="gnot-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';

}


function gnot_enotify_mail(&$a,&$b) {
	if((! $b['uid']) || (! intval(PConfig::get($b['uid'], 'gnot','enable'))))
		return;
	if($b['type'] == NOTIFY_COMMENT)
		$b['subject'] = L10n::t('[Friendica:Notify] Comment to conversation #%d', $b['parent']);
}
