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
 *     in case you don't use this add the following lines to your config/addon.config.php
 *     file:
 *
 *     [piwik]
 *     baseurl = example.com/piwik/
 *     sideid = 1
 *     optout = true ;set to false to disable
 *     async = false ;set to true to enable
 *
 *     Change the siteid to the ID that the Piwik tracker for your Friendica
 *     installation has. Alter the baseurl to fit your needs, don't care
 *     about http/https but beware to put the trailing / at the end of your
 *     setting.
 */

use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Util\Strings;

function piwik_install() {
	Hook::register('load_config', 'addon/piwik/piwik.php', 'piwik_load_config');
	Hook::register('page_end', 'addon/piwik/piwik.php', 'piwik_analytics');

	Logger::log("installed piwik addon");
}

function piwik_uninstall() {
	Hook::unregister('load_config', 'addon/piwik/piwik.php', 'piwik_load_config');
	Hook::unregister('page_end', 'addon/piwik/piwik.php', 'piwik_analytics');

	Logger::log("uninstalled piwik addon");
}

function piwik_load_config(\Friendica\App $a, Config\Cache\ConfigCacheManager $configCacheManager)
{
	$a->getConfigCache()->load($configCacheManager->loadAddonConfig('piwik'));
}

function piwik_analytics($a,&$b) {

	/*
	 *   styling of every HTML block added by this addon is done in the
	 *   associated CSS file. We just have to tell Friendica to get it
	 *   into the page header.
	 */
	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/piwik/piwik.css' . '" media="all" />';

	/*
	 *   Get the configuration variables from the config/addon.config.php file.
	 */
	$baseurl = Config::get('piwik', 'baseurl');
	$siteid  = Config::get('piwik', 'siteid');
	$optout  = Config::get('piwik', 'optout');
	$async   = Config::get('piwik', 'async');

	/*
	 *   Add the Piwik tracking code for the site.
	 *   If async is set to true use asynchronous tracking
	 */
	if ($async) {
	  $b .= "<!-- Piwik --> <script type=\"text/javascript\"> var _paq = _paq || []; _paq.push(['trackPageView']); _paq.push(['enableLinkTracking']); (function() { var u=((\"https:\" == document.location.protocol) ? \"https\" : \"http\") + \"://".$baseurl."\"; _paq.push(['setTrackerUrl', u+'piwik.php']); _paq.push(['setSiteId', ".$siteid."]); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s); })(); </script> <!-- End Piwik Code -->\r\n";
	  $b .= "<div id='piwik-code-block'> <!-- Piwik -->\r\n<noscript><p><img src=\"//".$baseurl."piwik.php?idsite=".$siteid."\" style=\"border:0\" alt=\"\" /></p></noscript>\r\n <!-- End Piwik Tracking Tag --> </div>";
	} else {
	  $b .= "<!-- Piwik --> <script type=\"text/javascript\"> var _paq = _paq || []; _paq.push(['trackPageView']); _paq.push(['enableLinkTracking']); (function() { var u=((\"https:\" == document.location.protocol) ? \"https\" : \"http\") + \"://".$baseurl."\"; _paq.push(['setTrackerUrl', u+'piwik.php']); _paq.push(['setSiteId', ".$siteid."]); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.defer=true; g.async=false; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s); })(); </script> <!-- End Piwik Code -->\r\n";
	}

	/*
	 *   If the optout variable is set to true then display the notice
	 *   otherwise just include the above code into the page.
	 */
	if ($optout) {
		$b .= "<div id='piwik-optout-link'>";
		$b .= L10n::t("This website is tracked using the <a href='http://www.matomo.org'>Matomo</a> analytics tool.");
		$b .= " ";
		$the_url =  "http://".$baseurl ."index.php?module=CoreAdminHome&action=optOut";
		$b .= L10n::t("If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Matomo / Piwik from tracking further visits of the site</a> (opt-out).", $the_url);
		$b .= "</div>";
	}
}
function piwik_addon_admin (&$a, &$o) {
	$t = Renderer::getMarkupTemplate( "admin.tpl", "addon/piwik/" );
	$o = Renderer::replaceMacros( $t, [
		'$submit' => L10n::t('Save Settings'),
		'$piwikbaseurl' => ['baseurl', L10n::t('Matomo (Piwik) Base URL'), Config::get('piwik','baseurl' ), L10n::t('Absolute path to your Matomo (Piwik) installation. (without protocol (http/s), with trailing slash)')],
		'$siteid' => ['siteid', L10n::t('Site ID'), Config::get('piwik','siteid' ), ''],
		'$optout' => ['optout', L10n::t('Show opt-out cookie link?'), Config::get('piwik','optout' ), ''],
		'$async' => ['async', L10n::t('Asynchronous tracking'), Config::get('piwik','async' ), ''],
	]);
}
function piwik_addon_admin_post (&$a) {
	$url = (!empty($_POST['baseurl']) ? Strings::escapeTags(trim($_POST['baseurl'])) : '');
	$id = (!empty($_POST['siteid']) ? trim($_POST['siteid']) : '');
	$optout = (!empty($_POST['optout']) ? trim($_POST['optout']) : '');
	$async = (!empty($_POST['async']) ? trim($_POST['async']) : '');
	Config::set('piwik', 'baseurl', $url);
	Config::set('piwik', 'siteid', $id);
	Config::set('piwik', 'optout', $optout);
	Config::set('piwik', 'async', $async);
	info(L10n::t('Settings updated.'). EOL);
}
