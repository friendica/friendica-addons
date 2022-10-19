<?php
/**
 * Name: Showmore Dynamic
 * Description: Dynamically limits height of posts
 * Version: 1.0
 * Author: Christian Wiwie
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\Session;
use Friendica\Database\DBA;
use Friendica\DI;

function showmore_dyn_install()
{
	Hook::register('page_end',  __FILE__, 'showmore_dyn_script');
	Hook::register('head', __FILE__, 'showmore_dyn_head');
	Hook::register('footer', __FILE__, 'showmore_dyn_footer');
	Hook::register('addon_settings',  __FILE__, 'showmore_dyn_settings');
	Hook::register('addon_settings_post',  __FILE__, 'showmore_dyn_settings_post');
}

function showmore_dyn_head(App $a, string &$body)
{
	DI::page()->registerStylesheet(__DIR__ . '/showmore_dyn.css');
}

function showmore_dyn_footer(App $a, string &$body)
{
	DI::page()->registerFooterScript(__DIR__ . '/showmore_dyn.js');
}

function showmore_dyn_settings_post()
{
	if(!Session::getLocalUser()) {
		return;
	}

	if (isset($_POST['showmore_dyn-submit'])) {
		DI::pConfig()->set(Session::getLocalUser(), 'showmore_dyn', 'limitHeight', $_POST['limitHeight'] ?? 0);
	}
}

function showmore_dyn_settings(App &$a, array &$data)
{
	if(!Session::getLocalUser()) {
		return;
	}

	$limitHeight = DI::pConfig()->get(Session::getLocalUser(), 'showmore_dyn', 'limitHeight', 250);
	DI::pConfig()->set(Session::getLocalUser(), 'showmore_dyn', 'limitHeight', $limitHeight);

	$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/showmore_dyn/');
	$html = Renderer::replaceMacros($t, [
		'$limitHeight' => ['limitHeight', DI::l10n()->t('Limit Height'), $limitHeight, DI::l10n()->t('The maximal pixel height of posts before the Show More link is added, 0 to disable'), '', '', 'number'],
	]);

	$data = [
		'addon' => 'showmore_dyn',
		'title' => DI::l10n()->t('Show More Dynamic'),
		'html'  => $html,
	];
}

function showmore_dyn_script()
{
	$limitHeight = intval(DI::pConfig()->get(Session::getLocalUser(), 'showmore_dyn', 'limitHeight', 250));
	$showmore_dyn_showmore_linktext = DI::l10n()->t('Show more...');
	DI::page()['htmlhead'] .= <<<EOT
<script>
	var postLimitHeight = $limitHeight;
	var showmore_dyn_showmore_linktext = "$showmore_dyn_showmore_linktext";
</script>
EOT;
}
