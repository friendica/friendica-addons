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
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

function pageheader_install() {
    Hook::register('page_content_top', __FILE__, 'pageheader_fetch');
}

function pageheader_addon_admin(App &$a, &$s)
{
	if(! is_site_admin()) {
		return;
	}

    /* Add our stylesheet to the page so we can make our settings look nice */
	$stylesheetPath = __DIR__ . '/pageheader.css';
	DI::page()->registerStylesheet($stylesheetPath);

	$words = DI::config()->get('pageheader','text');
	if(! $words)
		$words = '';

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/pageheader');
	$s .= Renderer::replaceMacros($t, [
		'$title' => DI::l10n()->t('"pageheader" Settings'),
		'$phwords' => ['pageheader-words', DI::l10n()->t('Message'), $words, DI::l10n()->t('Message to display on every page on this server (or put a pageheader.html file in your docroot)')],
		'$submit' => DI::l10n()->t('Save Settings')
	]);

	return;
}

function pageheader_addon_admin_post(App $a)
{
	if(!is_site_admin()) {
		return;
	}

	if(!empty($_POST['pageheader-submit'])) {
		if (isset($_POST['pageheader-words'])) {
			DI::config()->set('pageheader', 'text', trim(strip_tags($_POST['pageheader-words'])));
		}
	}
}

function pageheader_fetch(App $a, &$b)
{
	if(file_exists('pageheader.html')){
		$s = file_get_contents('pageheader.html');
	} else {
		$s = DI::config()->get('pageheader', 'text');
	}

	$stylesheetPath = __DIR__ .'/pageheader.css';
	DI::page()->registerStylesheet($stylesheetPath);
    
    if ($s) {
        $b .= '<div class="pageheader">' . $s . '</div>';
    }
}
