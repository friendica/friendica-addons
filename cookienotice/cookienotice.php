<?php

/**
 * Name: Cookie Notice
 * Description: Configure, show and handle a simple cookie notice
 * Version: 1.0
 * Author: Peter Liebetrau <https://socivitas/profile/peerteer>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

/**
 * cookienotice_install
 * registers hooks
 *
 * @return void
 */
function cookienotice_install()
{
	Hook::register('page_content_top', __FILE__, 'cookienotice_page_content_top');
	Hook::register('page_end', __FILE__, 'cookienotice_page_end');
}

/**
 * cookienotice_addon_admin
 * creates the admins config panel
 *
 * @param App    $a
 * @param string $s The existing config panel html so far
 *
 * @return void
 */
function cookienotice_addon_admin(App $a, &$s)
{
	if (!$a->isSiteAdmin()) {
		return;
	}

	$text = DI::config()->get('cookienotice', 'text', DI::l10n()->t('This website uses cookies. If you continue browsing this website, you agree to the usage of cookies.'));
	$oktext = DI::config()->get('cookienotice', 'oktext', DI::l10n()->t('OK'));

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/cookienotice/');
	$s .= Renderer::replaceMacros($t, [
		'$description' => DI::l10n()->t('<b>Configure your cookie usage notice.</b> It should just be a notice, saying that the website uses cookies. It is shown as long as a user didnt confirm clicking the OK button.'),
		'$text' => ['cookienotice-text', DI::l10n()->t('Cookie Usage Notice'), $text],
		'$oktext' => ['cookienotice-oktext', DI::l10n()->t('OK Button Text'), $oktext],
		'$submit' => DI::l10n()->t('Save Settings')
	]);

	return;
}

/**
 * cookienotice_addon_admin_post
 * handles the post request from the admin panel
 *
 * @param App    $a
 * @return void
 */
function cookienotice_addon_admin_post(App $a)
{
	if (!$a->isSiteAdmin()) {
		return;
	}

	if ($_POST['cookienotice-submit']) {
		DI::config()->set('cookienotice', 'text', trim(strip_tags($_POST['cookienotice-text'])));
		DI::config()->set('cookienotice', 'oktext', trim(strip_tags($_POST['cookienotice-oktext'])));
	}
}

/**
 * cookienotice_page_content_top
 * page_content_top hook
 * adds css and scripts to the <head> section of the html
 *
 * @param App    $a
 * @param string $b unused - the header html incl. nav
 *
 * @return void
 */
function cookienotice_page_content_top(App $a, array &$b)
{
	DI::page()->registerStylesheet(__DIR__ . '/cookienotice.css');
	DI::page()->registerFooterScript(__DIR__ . '/cookienotice.js');
}

/**
 * cookienotice_page_end
 * page_end hook
 * ads our cookienotice box to the end of the html
 *
 * @param App    $a
 * @param string $b the page html
 *
 * @return void
 */
function cookienotice_page_end(App $a, array &$b)
{
	$text = (string)DI::config()->get('cookienotice', 'text', DI::l10n()->t('This website uses cookies to recognize revisiting and logged in users. You accept the usage of these cookies by continue browsing this website.'));
	$oktext = (string)DI::config()->get('cookienotice', 'oktext', DI::l10n()->t('OK'));

	$page_end_tpl = Renderer::getMarkupTemplate('cookienotice.tpl', 'addon/cookienotice/');

	$page_end = Renderer::replaceMacros($page_end_tpl, [
		'$text' => $text,
		'$oktext' => $oktext,
	]);

	$b .= $page_end;
}
