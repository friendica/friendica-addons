<?php
/**
 * Name: Pledgie
 * Description: Show link to a pledgie account for donating
 * Version: 1.1
 * Author: tony baldwin <tony@free-haven.org>
 *         Hauke Altmann <https://snarl.de/profile/tugelblend>
 *      
 */

function pledgie_install() { 
	register_hook('page_end', 'addon/pledgie/pledgie.php', 'pledgie_active'); 
	register_hook('plugin_settings', 'addon/pledgie/pledgie.php', 'pledgie_addon_settings');
	register_hook('plugin_settings_post', 'addon/pledgie/pledgie.php', 'pledgie_addon_settings_post');
}

function pledgie_uninstall() { 
	unregister_hook('page_end', 'addon/pledgie/pledgie.php', 'pledgie_active');
	unregister_hook('plugin_settings', 'addon/pledgie/pledgie.php', 'pledgie_addon_settings');
	unregister_hook('plugin_settings_post', 'addon/pledgie/pledgie.php', 'pledgie_addon_settings_post');
}

function pledgie_addon_settings(&$a,&$s) {

	if(! is_site_admin())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/pledgie/pledgie.css' . '" media="all" />' . "\r\n";

	$campaign = get_config('pledgie-campaign','text');
	$describe = get_config('pledgie-describe','text');
	
	if(! $campaign)
		$campaign = '';
	
	if(! describe)
		$describe = '';

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('"pledgie" Settings') . '</h3>';
	$s .= '<div id="pledgie-wrapper">';
	$s .= '<label id="pledgie-label" for="pledgie-campaign">' . t('Pledgie campaign number to use for donations') . ' </label>';
	$s .= '<input id="pledgie-campaign" type="text" name="pledgie-campaign" value="' . $campaign . '">';
	$s .= '</div><div class="clear"></div>';
	
	$s .= '<div id="pledgie-wrapper">';
	$s .= '<label id="pledgie-label" for="pledgie-describe">' . t('Description of the Pledgie campaign') . ' </label>';
	$s .= '<input id="pledgie-describe" type="text" name="pledgie-describe" value="' . $describe . '">';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="pledgie-submit" name="pledgie-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

	return;
}

function pledgie_addon_settings_post(&$a,&$b) {

	if(! is_site_admin())
		return;

	if($_POST['pledgie-submit']) {
		set_config('pledgie-describe','text',trim(strip_tags($_POST['pledgie-describe'])));
		set_config('pledgie-campaign','text',trim(strip_tags($_POST['pledgie-campaign'])));
		info( t('pledgie Settings saved.') . EOL);
	}
}

function pledgie_active(&$a,&$b) {
	$campaign = get_config('pledgie-campaign','text');
	$describe = get_config('pledgie-describe','text');
	$b .= '<div style="position: fixed; padding:5px; border-style:dotted; border-width:1px; background-color: white; line-height: 1; bottom: 5px; left: 20px; z-index: 1000; width: 150px; font-size: 12px;">';
	$b .= $describe . '<br/><a href="https://pledgie.com/campaigns/';
	$b .= $campaign;
	$b .= '"><img alt="Click here to lend your support to: ' . $describe .  '!" src="https://pledgie.com/campaigns/';
	$b .= $campaign;
	$b .= '.png?skin_name=chrome" border="0" target="_blank" /></a></div>';
}
