<?php
/**
* Name: Infinite Improbability Drive
* Description: Infinitely Improbably Find A Random User
* Version: 1.0
* Author: Thomas Willingham <https://kakste.com/profile/beardyunixer>
*/

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\DI;

function infiniteimprobabilitydrive_install()
{
	Hook::register('app_menu', 'addon/infiniteimprobabilitydrive/infiniteimprobabilitydrive.php', 'infiniteimprobabilitydrive_app_menu');
}

function infiniteimprobabilitydrive_app_menu(array &$b)
{
	$b['app_menu'][] = '<div class="app-title"><a href="infiniteimprobabilitydrive">' . DI::l10n()->t('Infinite Improbability Drive') . '</a></div>';
}

/**
 * This is a statement rather than an actual function definition. The simple
 * existence of this method is checked to figure out if the addon offers a
 * module.
 */
function infiniteimprobabilitydrive_module() {}


function infiniteimprobabilitydrive_content()
{
	$o = '';

	DI::page()['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'.DI::baseUrl().'/addon/infiniteimprobabilitydrive/infiniteimprobabilitydrive.css"/>';


	$baseurl = (string)DI::baseUrl();

	$o .= <<< EOT

<br><br>
<p>Try another destination with the <a href="$baseurl/infiniteimprobabilitydrive">Infinite Improbability Drive</a>
<iframe src ="$baseurl/randprof" height="1200" width="1024">

EOT;
	return $o;
}
