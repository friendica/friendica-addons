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
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

function mathjax_install()
{
	Hook::register('footer'             , __FILE__, 'mathjax_footer');
	Hook::register('addon_settings'     , __FILE__, 'mathjax_settings');
	Hook::register('addon_settings_post', __FILE__, 'mathjax_settings_post');
}

function mathjax_settings_post()
{
	if (!DI::userSession()->getLocalUserId() || empty($_POST['mathjax-submit'])) {
		return;
	}

	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'mathjax', 'use', intval($_POST['mathjax_use']));
}

function mathjax_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$use = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'mathjax', 'use', false);

	$tpl = Renderer::getMarkupTemplate('settings.tpl', 'addon/mathjax');
	$html = Renderer::replaceMacros($tpl, [
		'$description'  => DI::l10n()->t('The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail.'),
		'$mathjax_use'  => ['mathjax_use', DI::l10n()->t('Use the MathJax renderer'), $use, ''],
	]);

	$data = [
		'addon' => 'mathjax',
		'title' => 'MathJax',
		'html'  => $html,
	];
}

function mathjax_footer(string &$body)
{
	//  if the visitor of the page is not a local_user, use MathJax
	//  otherwise check the users settings.
	if (!DI::userSession()->getLocalUserId() || DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'mathjax', 'use', false)) {
		DI::page()->registerFooterScript(__DIR__ . '/asset/MathJax.js?config=TeX-MML-AM_CHTML');
		DI::page()->registerFooterScript(__DIR__ . '/mathjax.js');
	}
}
