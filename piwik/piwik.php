<?php
/**
 * Name: Matomo / Piwik Analytics
 * Description: Matomo / Piwik Analytics Addon for Friendica
 * Version: 1.3
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 * Author: Klaus Weidenbach
 */

/*   Piwik Analytics Addon for Friendica
 *
 *   Author: Tobias Diekershoff
 *           tobias.diekershoff@gmx.net
 *
 *   License: 3-clause BSD license
 *
 *   Configuration:
 *     Use the administration panel to configure the Piwik tracking addon, or
 *     in case you don't use this, add the following lines to your config/piwik.config.php
 *     file:
 *
 *      return [
 *          'piwik' => [
 *              'baseurl' => '',
 *              'sideid' => '',
 *              'optout' => true,
 *              'async' => false,
 *              'shortendpoint' => false,
 *          ],
 *      ];
 *
 *     Change the siteid to the ID that the Piwik tracker for your Friendica
 *     installation has. Alter the baseurl to fit your needs, don't care
 *     about http/https but beware to put the trailing / at the end of your
 *     setting.
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Core\Config\Util\ConfigFileManager;

function piwik_install() {
	Hook::register('load_config', 'addon/piwik/piwik.php', 'piwik_load_config');
	Hook::register('page_end', 'addon/piwik/piwik.php', 'piwik_analytics');

	Logger::notice("installed piwik addon");
}

function piwik_load_config(ConfigFileManager $loader)
{
	DI::app()->getConfigCache()->load($loader->loadAddonConfig('piwik'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function piwik_analytics(string &$b)
{
	/*
	 *   styling of every HTML block added by this addon is done in the
	 *   associated CSS file. We just have to tell Friendica to get it
	 *   into the page header.
	 */
	DI::page()->registerStylesheet('addon/piwik/piwik.css', 'all');

	/*
	 *   Get the configuration values.
	 */
	$baseurl = DI::config()->get('piwik', 'baseurl');
	$siteid  = DI::config()->get('piwik', 'siteid');
	$optout  = DI::config()->get('piwik', 'optout');
	$async   = DI::config()->get('piwik', 'async');
	$shortendpoint = DI::config()->get('piwik', 'shortendpoint');

	/*
	 *   Add the Piwik tracking code for the site.
	 *   If async is set to true use asynchronous tracking
	 */
	
	$scriptAsyncValue = $async ? 'true' : 'false';
	$scriptPhpEndpoint = $shortendpoint ? 'js/' : 'piwik.php';
	$scriptJsEndpoint = $shortendpoint ? 'js/' : 'piwik.js';

	$b .= "<!-- Piwik --> <script type=\"text/javascript\"> var _paq = _paq || []; _paq.push(['trackPageView']); _paq.push(['enableLinkTracking']); (function() { var u=((\"https:\" == document.location.protocol) ? \"https\" : \"http\") + \"://$baseurl\"; _paq.push(['setTrackerUrl', u+'$scriptPhpEndpoint']); _paq.push(['setSiteId', $siteid]); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.defer=true; g.async=$scriptAsyncValue; g.src=u+'$scriptJsEndpoint'; s.parentNode.insertBefore(g,s); })(); </script> <!-- End Piwik Code -->\r\n";

	if ($async) {
		$b .= "<div id='piwik-code-block'> <!-- Piwik -->\r\n<noscript><p><img src=\"//$baseurl$scriptPhpEndpoint?idsite=$siteid\" style=\"border:0\" alt=\"\" /></p></noscript>\r\n <!-- End Piwik Tracking Tag --> </div>";
	}

	/*
	 *   If the optout variable is set to true then display the notice
	 *   otherwise just include the above code into the page.
	 */
	if ($optout) {
		$b .= "<div id='piwik-optout-link'>";
		$b .= DI::l10n()->t("This website is tracked using the <a href='http://www.matomo.org'>Matomo</a> analytics tool.");
		$b .= " ";
		$the_url =  "http://{$baseurl}index.php?module=CoreAdminHome&action=optOut";
		$b .= DI::l10n()->t("If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Matomo / Piwik from tracking further visits of the site</a> (opt-out).", $the_url);
		$b .= "</div>";
	}
}
function piwik_addon_admin (string &$o)
{
	$t = Renderer::getMarkupTemplate( "admin.tpl", "addon/piwik/" );

	$o = Renderer::replaceMacros( $t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$piwikbaseurl' => ['baseurl', DI::l10n()->t('Matomo (Piwik) Base URL'), DI::config()->get('piwik','baseurl' ), DI::l10n()->t('Absolute path to your Matomo (Piwik) installation. (without protocol (http/s), with trailing slash)')],
		'$siteid' => ['siteid', DI::l10n()->t('Site ID'), DI::config()->get('piwik','siteid' ), ''],
		'$optout' => ['optout', DI::l10n()->t('Show opt-out cookie link?'), DI::config()->get('piwik','optout' ), ''],
		'$async' => ['async', DI::l10n()->t('Asynchronous tracking'), DI::config()->get('piwik','async' ), ''],
		'$shortendpoint' => ['shortendpoint', DI::l10n()->t("Shortcut path to the script ('/js/' instead of '/piwik.js')"), DI::config()->get('piwik','shortendpoint' ), ''],
	]);
}

function piwik_addon_admin_post()
{
	DI::config()->set('piwik', 'baseurl', trim($_POST['baseurl'] ?? ''));
	DI::config()->set('piwik', 'siteid', trim($_POST['siteid'] ?? ''));
	DI::config()->set('piwik', 'optout', trim($_POST['optout'] ?? ''));
	DI::config()->set('piwik', 'async', trim($_POST['async'] ?? ''));
	DI::config()->set('piwik', 'shortendpoint', trim($_POST['shortendpoint'] ?? ''));
}
