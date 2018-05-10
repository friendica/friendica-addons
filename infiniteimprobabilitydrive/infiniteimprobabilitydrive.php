<?php
/**
* Name: Infinite Improbability Drive
* Description: Infinitely Improbably Find A Random User
* Version: 1.0
* Author: Thomas Willingham <https://kakste.com/profile/beardyunixer>
*/

use Friencia\App;
use Friendica\Core\Addon;
use Friendica\Core\L10n;

function infiniteimprobabilitydrive_install()
{
	Addon::registerHook('app_menu', 'addon/infiniteimprobabilitydrive/infiniteimprobabilitydrive.php', 'infiniteimprobabilitydrive_app_menu');
}

function infiniteimprobabilitydrive_uninstall()
{
	Addon::unregisterHook('app_menu', 'addon/infiniteimprobabilitydrive/infiniteimprobabilitydrive.php', 'infiniteimprobabilitydrive_app_menu');
}

function infiniteimprobabilitydrive_app_menu(App $a, array &$b)
{
	$b['app_menu'][] = '<div class="app-title"><a href="infiniteimprobabilitydrive">' . L10n::t('Infinite Improbability Drive') . '</a></div>';
}


function infiniteimprobabilitydrive_module()
{
	return;
}


function infiniteimprobabilitydrive_content(&$a)
{
	$baseurl = $a->get_baseurl() . '/addon/infiniteimprobabilitydrive';
	$o = '';

	$a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'.$a->get_baseurl().'/addon/infiniteimprobabilitydrive/infiniteimprobabilitydrive.css"/>';


	$baseurl = $a->get_baseurl();

	$o .= <<< EOT

<br><br>
<p>Try another destination with the <a href="$baseurl/infiniteimprobabilitydrive">Infinite Improbability Drive</a>
<iframe src ="$baseurl/randprof" height="1200" width="1024">

EOT;
	return $o;
}
