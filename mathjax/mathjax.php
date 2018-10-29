<?php
/**
 * Name: MathJax
 * Description: Addon for Friendica to include MathJax (LaTeX math syntax)
 * Version: 2.0
 * Author: Tobias Diekershoff <https://social.diekershoff.de/profile/tobias>
 * Author: Hypolite Petovan <https://friendica.mrpetovan.com/profile/hypolite>
 * License: 3-clause BSD license
 */

use Friendica\App;
use Friendica\Content\Text;
use Friendica\Core\Addon;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;

function mathjax_install()
{
	Addon::registerHook('footer'             , __FILE__, 'mathjax_footer');
	Addon::registerHook('addon_settings'     , __FILE__, 'mathjax_settings');
	Addon::registerHook('addon_settings_post', __FILE__, 'mathjax_settings_post');
}

function mathjax_uninstall()
{
	Addon::unregisterHook('footer'             , __FILE__, 'mathjax_footer');
	Addon::unregisterHook('addon_settings'     , __FILE__, 'mathjax_settings');
	Addon::unregisterHook('addon_settings_post', __FILE__, 'mathjax_settings_post');

	// Legacy hooks
	Addon::unregisterHook('load_config'        , __FILE__, 'mathjax_load_config');
	Addon::unregisterHook('page_header'        , __FILE__, 'mathjax_page_header');
	Addon::unregisterHook('template_vars'      , __FILE__, 'mathjax_template_vars');
}

function mathjax_settings_post($a)
{
	if (!local_user() || empty($_POST['mathjax-submit'])) {
		return;
	}

	PConfig::set(local_user(), 'mathjax', 'use', intval($_POST['mathjax_use']));
}

function mathjax_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	$use = PConfig::get(local_user(), 'mathjax', 'use', false);

	$tpl = Text::getMarkupTemplate('settings.tpl', __DIR__);
	$s .= Text::replaceMacros($tpl, [
		'$title'        => 'MathJax',
		'$description'  => L10n::t('The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail.'),
		'$mathjax_use'  => ['mathjax_use', L10n::t('Use the MathJax renderer'), $use, ''],
		'$savesettings' => L10n::t('Save Settings'),
	]);
}

function mathjax_footer(App $a, &$b)
{
	//  if the visitor of the page is not a local_user, use MathJax
	//  otherwise check the users settings.
	if (!local_user() || PConfig::get(local_user(), 'mathjax', 'use', false)) {
		$a->registerFooterScript(__DIR__ . '/asset/MathJax.js?config=TeX-MML-AM_CHTML');
		$a->registerFooterScript(__DIR__ . '/mathjax.js');
	}
}
