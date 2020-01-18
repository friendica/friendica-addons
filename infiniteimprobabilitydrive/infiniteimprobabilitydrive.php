<?php
/**
* Name: Infinite Improbability Drive
* Description: Infinitely Improbably Find A Random User
* Version: 1.0
* Author: Thomas Willingham <https://kakste.com/profile/beardyunixer>
*/
use Friendica\Core\Hook;
use Friendica\DI;

function infiniteimprobabilitydrive_install()
{
	Hook::register('app_menu', 'addon/infiniteimprobabilitydrive/infiniteimprobabilitydrive.php', 'infiniteimprobabilitydrive_app_menu');
}

function infiniteimprobabilitydrive_uninstall()
{
	Hook::unregister('app_menu', 'addon/infiniteimprobabilitydrive/infiniteimprobabilitydrive.php', 'infiniteimprobabilitydrive_app_menu');
}

function infiniteimprobabilitydrive_app_menu($a, &$b)
{
	$b['app_menu'][] = '<div class="app-title"><a href="infiniteimprobabilitydrive">' . DI::l10n()->t('Infinite Improbability Drive') . '</a></div>';
}


function infiniteimprobabilitydrive_module()
{
	return;
}


function infiniteimprobabilitydrive_content(&$a)
{
	$baseurl = DI::baseUrl()->get() . '/addon/infiniteimprobabilitydrive';
	$o = '';

	DI::page()['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'.DI::baseUrl()->get().'/addon/infiniteimprobabilitydrive/infiniteimprobabilitydrive.css"/>';


	$baseurl = DI::baseUrl()->get();

	$o .= <<< EOT

<br><br>
<p>Try another destination with the <a href="$baseurl/infiniteimprobabilitydrive">Infinite Improbability Drive</a>
<iframe src ="$baseurl/randprof" height="1200" width="1024">

EOT;
	return $o;
}
