<?php

/**
 * Name: Cookie Notice
 * Description: Configure, show and handle a simple cookie notice
 * Version: 1.0
 * Author: Peter Liebetrau <https://socivitas/profile/peerteer>
 * 
 */
use Friendica\Core\Addon;
use Friendica\Core\Hook;
use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\Renderer;

/**
 * cookienotice_install
 * registers hooks
 * 
 * @return void
 */
function cookienotice_install()
{
	$file = 'addon/cookienotice/cookienotice.php';
	Hook::register('page_content_top', $file, 'cookienotice_page_content_top');
	Hook::register('page_end', $file, 'cookienotice_page_end');
	Hook::register('addon_settings', $file, 'cookienotice_addon_settings');
	Hook::register('addon_settings_post', $file, 'cookienotice_addon_settings_post');
}

/**
 * cookienotice_uninstall
 * unregisters hooks
 * 
 * @return void
*/
function cookienotice_uninstall()
{
	$file = 'addon/cookienotice/cookienotice.php';
	Hook::unregister('page_content_top', $file, 'cookienotice_page_content_top');
	Hook::unregister('page_end', $file, 'cookienotice_page_end');
	Hook::unregister('addon_settings', $file, 'cookienotice_addon_settings');
	Hook::unregister('addon_settings_post', $file, 'cookienotice_addon_settings_post');
}

/**
 * cookienotice_addon_settings
 * addon_settings hook
 * creates the admins config panel
 * 
 * @param \Friendica\App $a
 * @param string $s The existing config panel html so far
 * 
 * @return void
 */
function cookienotice_addon_settings(\Friendica\App $a, &$s)
{
	if (!is_site_admin()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */
    $stylesheetPath = 'addon/cookienotice/cookienotice.css';
    $a->registerStylesheet($stylesheetPath);

	$text = Config::get('cookienotice', 'text');
	if (!$text) {
		$text = '';
	}
	$oktext = Config::get('cookienotice', 'oktext');
	if (!$oktext) {
		$oktext = '';
	}

	$t = Renderer::getMarkupTemplate("settings.tpl", "addon/cookienotice/");
	$s .= Renderer::replaceMacros($t, [
		'$title' => L10n::t('"cookienotice" Settings'),
		'$description' => L10n::t('<b>Configure your cookie usage notice.</b> It should just be a notice, saying that the website uses cookies. It is shown as long as a user didnt confirm clicking the OK button.'),
		'$text' => ['cookienotice-text', L10n::t('Cookie Usage Notice'), $text, L10n::t('The cookie usage notice')],
		'$oktext' => ['cookienotice-oktext', L10n::t('OK Button Text'), $oktext, L10n::t('The OK Button text')],
		'$submit' => L10n::t('Save Settings')
	]);

	return;
}

/**
 * cookienotice_addon_settings_post
 * addon_settings_post hook
 * handles the post request from the admin panel
 * 
 * @param \Friendica\App $a
 * @param string $b
 * 
 * @return void
 */
function cookienotice_addon_settings_post(\Friendica\App $a, &$b)
{
	if (!is_site_admin()) {
		return;
	}

	if ($_POST['cookienotice-submit']) {
		Config::set('cookienotice', 'text', trim(strip_tags($_POST['cookienotice-text'])));
		Config::set('cookienotice', 'oktext', trim(strip_tags($_POST['cookienotice-oktext'])));
		info(L10n::t('cookienotice Settings saved.') . EOL);
	}
}

/**
 * cookienotice_page_content_top
 * page_content_top hook
 * adds css and scripts to the <head> section of the html
 * 
 * @param \Friendica\App $a
 * @param string $b unnused - the header html incl. nav
 * 
 * @return void
 */
function cookienotice_page_content_top(\Friendica\App $a, &$b)
{
    $stylesheetPath = 'addon/cookienotice/cookienotice.css';
    $footerscriptPath = 'addon/cookienotice/cookienotice.js';

    $a->registerStylesheet($stylesheetPath);
    $a->registerFooterScript($footerscriptPath);
}

/**
 * cookienotice_page_end
 * page_end hook
 * ads our cookienotice box to the end of the html
 * 
 * @param \Friendica\App $a
 * @param string $b the page html
 * 
 * @return void
 */
function cookienotice_page_end(\Friendica\App $a, &$b)
{
	$text = (string) Config::get('cookienotice', 'text', L10n::t('This website uses cookies to recognize revisiting and logged in users. You accept the usage of these cookies by continue browsing this website.'));
	$oktext = (string) Config::get('cookienotice', 'oktext', L10n::t('OK'));

	$page_end_tpl = Renderer::getMarkupTemplate("cookienotice.tpl", "addon/cookienotice/");

	$page_end = Renderer::replaceMacros($page_end_tpl, [
		'$text' => $text,
		'$oktext' => $oktext,
	]);

	$b .= $page_end;
}
