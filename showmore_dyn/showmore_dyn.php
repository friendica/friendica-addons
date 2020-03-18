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

function showmore_dyn_head(App $a, &$b)
{
	DI::page()->registerStylesheet(__DIR__ . '/showmore_dyn.css');
}

function showmore_dyn_footer(App $a, &$b)
{
	DI::page()->registerFooterScript(__DIR__ . '/showmore_dyn.js');
}

function showmore_dyn_settings_post()
{
	if(!local_user()) {
		return;
	}

	if (isset($_POST['showmore_dyn-submit'])) {
		$limitHeight = $_POST['limitHeight'];
		if ($limitHeight && is_numeric($limitHeight)) {
			DI::pConfig()->set(local_user(), 'showmore_dyn', 'limitHeight', $limitHeight);
		}
	}
}

function showmore_dyn_settings(App &$a, &$o)
{
	if(!local_user()) {
		return;
	}

	$limitHeight = DI::pConfig()->get(local_user(), 'showmore_dyn', 'limitHeight', 250);
	DI::pConfig()->set(local_user(), 'showmore_dyn', 'limitHeight', $limitHeight);

	$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/showmore_dyn/');
	$o .= Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$title' => 'Showmore Dynamic',
		'$limitHeight' => ['limitHeight', DI::l10n()->t('Limit Height'), $limitHeight, 'The maximal height of posts when collapsed', '', '', 'number'],
	]);

}

function showmore_dyn_script()
{
	$limitHeight = DI::pConfig()->get(local_user(), 'showmore_dyn', 'limitHeight', 250);
	$showmore_dyn_showmore_linktext = DI::l10n()->t('Show more ...');
	DI::page()['htmlhead'] .= <<<EOT
<script>
	var postLimitHeight = $limitHeight;
	var showmore_dyn_showmore_linktext = "$showmore_dyn_showmore_linktext";
</script>
EOT;
}
