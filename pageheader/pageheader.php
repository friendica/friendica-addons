<?php
/**
 * Name: Page Header
 * Description: Inserts a page header
 * Version: 1.1
 * Author: Keith Fernie <http://friendika.me4.it/profile/keith>
 *         Hauke Altmann <https://snarl.de/profile/tugelblend>
 * 
 */
use Friendica\Core\Addon;
use Friendica\Core\Config;

function pageheader_install() {
    Addon::registerHook('page_content_top', 'addon/pageheader/pageheader.php', 'pageheader_fetch');
	Addon::registerHook('plugin_settings', 'addon/pageheader/pageheader.php', 'pageheader_addon_settings');
	Addon::registerHook('plugin_settings_post', 'addon/pageheader/pageheader.php', 'pageheader_addon_settings_post');

}


function pageheader_uninstall() {
    Addon::unregisterHook('page_content_top', 'addon/pageheader/pageheader.php', 'pageheader_fetch');
	Addon::unregisterHook('plugin_settings', 'addon/pageheader/pageheader.php', 'pageheader_addon_settings');
	Addon::unregisterHook('plugin_settings_post', 'addon/pageheader/pageheader.php', 'pageheader_addon_settings_post');

	// hook moved, uninstall the old one if still there. 
    Addon::unregisterHook('page_header', 'addon/pageheader/pageheader.php', 'pageheader_fetch');

}





function pageheader_addon_settings(&$a,&$s) {


	if(! is_site_admin())
		return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/pageheader/pageheader.css' . '" media="all" />' . "\r\n";


	$words = Config::get('pageheader','text');
	if(! $words)
		$words = '';

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('"pageheader" Settings') . '</h3>';
    $s .= '<div id="pageheader-wrapper">';
    $s .= '<label id="pageheader-label" for="pageheader-words">' . t('Message to display on every page on this server (or put a pageheader.html file in your docroot)') . ' </label>';
    $s .= '<textarea id="pageheader-words" type="text" name="pageheader-words">' . $words . '</textarea>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="pageheader-submit" name="pageheader-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

	return;

}

function pageheader_addon_settings_post(&$a,&$b) {

	if(! is_site_admin())
		return;

	if($_POST['pageheader-submit']) {
		Config::set('pageheader','text',trim(strip_tags($_POST['pageheader-words'])));
		info( t('pageheader Settings saved.') . EOL);
	}
}

function pageheader_fetch($a,&$b) {
	
	if(file_exists('pageheader.html')){
		$s = file_get_contents('pageheader.html');
	} else {
		$s = Config::get('pageheader', 'text');
	}

    $a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'
        . $a->get_baseurl() . '/addon/pageheader/pageheader.css' . '" media="all" />' . "\r\n";
    
    if(! $s)
        $s = '';
    if ($s != '')
        $b .= '<div class="pageheader">' . $s . '</div>';
}
