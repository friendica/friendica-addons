<?php
/**
 * Name: Pledgie
 * Description: Show link to a pledgie account for donating
 * Version: 1.1
 * Author: tony baldwin <tony@free-haven.org>
 *         Hauke Altmann <https://snarl.de/profile/tugelblend>
 *
 */
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;

function pledgie_install() { 
	Hook::register('page_end', 'addon/pledgie/pledgie.php', 'pledgie_active');
	Hook::register('addon_settings', 'addon/pledgie/pledgie.php', 'pledgie_addon_settings');
	Hook::register('addon_settings_post', 'addon/pledgie/pledgie.php', 'pledgie_addon_settings_post');
}

function pledgie_uninstall() { 
	Hook::unregister('page_end', 'addon/pledgie/pledgie.php', 'pledgie_active');
	Hook::unregister('addon_settings', 'addon/pledgie/pledgie.php', 'pledgie_addon_settings');
	Hook::unregister('addon_settings_post', 'addon/pledgie/pledgie.php', 'pledgie_addon_settings_post');
}

function pledgie_addon_settings(&$a,&$s) {

	if(! is_site_admin())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/pledgie/pledgie.css' . '" media="all" />' . "\r\n";

	$campaign = Config::get('pledgie-campaign','text');
	$describe = Config::get('pledgie-describe','text');
	
	if(! $campaign)
		$campaign = '';
	
	if(! describe)
		$describe = '';

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . L10n::t('"pledgie" Settings') . '</h3>';
	$s .= '<div id="pledgie-wrapper">';
	$s .= '<label id="pledgie-label" for="pledgie-campaign">' . L10n::t('Pledgie campaign number to use for donations') . ' </label>';
	$s .= '<input id="pledgie-campaign" type="text" name="pledgie-campaign" value="' . $campaign . '">';
	$s .= '</div><div class="clear"></div>';
	
	$s .= '<div id="pledgie-wrapper">';
	$s .= '<label id="pledgie-label" for="pledgie-describe">' . L10n::t('Description of the Pledgie campaign') . ' </label>';
	$s .= '<input id="pledgie-describe" type="text" name="pledgie-describe" value="' . $describe . '">';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="pledgie-submit" name="pledgie-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';

	return;
}

function pledgie_addon_settings_post(&$a,&$b) {

	if(! is_site_admin())
		return;

	if($_POST['pledgie-submit']) {
		Config::set('pledgie-describe','text',trim(strip_tags($_POST['pledgie-describe'])));
		Config::set('pledgie-campaign','text',trim(strip_tags($_POST['pledgie-campaign'])));
		info(L10n::t('pledgie Settings saved.') . EOL);
	}
}

function pledgie_active(&$a,&$b) {
	$campaign = Config::get('pledgie-campaign','text');
	$describe = Config::get('pledgie-describe','text');
	$b .= '<div style="position: fixed; padding:5px; border-style:dotted; border-width:1px; background-color: white; line-height: 1; bottom: 5px; left: 20px; z-index: 1000; width: 150px; font-size: 12px;">';
	$b .= $describe . '<br/><a href="https://pledgie.com/campaigns/';
	$b .= $campaign;
	$b .= '"><img alt="Click here to lend your support to: ' . $describe .  '!" src="https://pledgie.com/campaigns/';
	$b .= $campaign;
	$b .= '.png?skin_name=chrome" border="0" target="_blank" /></a></div>';
}
