<?php
/**
 * Name: Page Header
 * Description: Inserts a page header
 * Version: 1.1
 * Author: Keith Fernie <http://friendika.me4.it/profile/keith>
 *         Hauke Altmann <https://snarl.de/profile/tugelblend>
 * 
 */

use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;

function pageheader_install() {
    Addon::registerHook('page_content_top', 'addon/pageheader/pageheader.php', 'pageheader_fetch');
	Addon::registerHook('addon_settings', 'addon/pageheader/pageheader.php', 'pageheader_addon_settings');
	Addon::registerHook('addon_settings_post', 'addon/pageheader/pageheader.php', 'pageheader_addon_settings_post');

}


function pageheader_uninstall() {
    Addon::unregisterHook('page_content_top', 'addon/pageheader/pageheader.php', 'pageheader_fetch');
	Addon::unregisterHook('addon_settings', 'addon/pageheader/pageheader.php', 'pageheader_addon_settings');
	Addon::unregisterHook('addon_settings_post', 'addon/pageheader/pageheader.php', 'pageheader_addon_settings_post');

	// hook moved, uninstall the old one if still there. 
    Addon::unregisterHook('page_header', 'addon/pageheader/pageheader.php', 'pageheader_fetch');

}





function pageheader_addon_settings(App $a, &$s) {


	if(! is_site_admin())
		return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/pageheader/pageheader.css' . '" media="all" />' . "\r\n";


	$words = Config::get('pageheader','text');
	if(! $words)
		$words = '';

	$t = get_markup_template("settings.tpl", "addon/pageheader/");
	$s .= replace_macros($t, [
					'$title' => L10n::t('"pageheader" Settings'),
					'$phwords' => ['pageheader-words', L10n::t('Message'), $words, L10n::t('Message to display on every page on this server (or put a pageheader.html file in your docroot)')],
					'$submit' => L10n::t('Save Settings')
	]);

	return;

}

function pageheader_addon_settings_post(App $a, array &$b) {

	if(! is_site_admin())
		return;

	if($_POST['pageheader-submit']) {
		Config::set('pageheader','text',trim(strip_tags($_POST['pageheader-words'])));
		info(L10n::t('pageheader Settings saved.') . EOL);
	}
}

function pageheader_fetch(App $a, &$b) {
	
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
