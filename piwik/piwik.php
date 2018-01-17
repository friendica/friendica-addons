<?php
/**
 * Name: Piwik Analytics
 * Description: Piwik Analytics Plugin for Friendica
 * Version: 1.3
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 * Author: Klaus Weidenbach
 */

/*   Piwik Analytics Plugin for Friendica
 *
 *   Author: Tobias Diekershoff
 *           tobias.diekershoff@gmx.net
 *
 *   License: 3-clause BSD license
 *
 *   Configuration:
 *     Use the administration panel to configure the Piwik tracking addon, or
 *     in case you don't use this add the following lines to your .htconfig.php
 *     file:
 *
 *     $a->config['piwik']['baseurl'] = 'www.example.com/piwik/';
 *     $a->config['piwik']['siteid'] = '1';
 *     $a->config['piwik']['optout'] = true;  // set to false to disable
 *     $a->config['piwik']['async'] = false;  // set to true to enable
 *
 *     Change the siteid to the ID that the Piwik tracker for your Friendica
 *     installation has. Alter the baseurl to fit your needs, don't care
 *     about http/https but beware to put the trailing / at the end of your
 *     setting.
 */
use Friendica\Core\Addon;
use Friendica\Core\Config;

function piwik_install() {
	Addon::registerHook('page_end', 'addon/piwik/piwik.php', 'piwik_analytics');

	logger("installed piwik plugin");
}

function piwik_uninstall() {
	Addon::unregisterHook('page_end', 'addon/piwik/piwik.php', 'piwik_analytics');

	logger("uninstalled piwik plugin");
}

function piwik_analytics($a,&$b) {

	/*
	 *   styling of every HTML block added by this plugin is done in the
	 *   associated CSS file. We just have to tell Friendica to get it
	 *   into the page header.
	 */
	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/piwik/piwik.css' . '" media="all" />';

	/*
	 *   Get the configuration variables from the .htconfig file.
	 */
	$baseurl = Config::get('piwik','baseurl');
	$siteid  = Config::get('piwik','siteid');
	$optout  = Config::get('piwik','optout');
	$async   = Config::get('piwik','async');

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
		$b .= t("This website is tracked using the <a href='http://www.piwik.org'>Piwik</a> analytics tool.");
		$b .= " ";
		$the_url =  "http://".$baseurl ."index.php?module=CoreAdminHome&action=optOut";
		$b .= sprintf(t("If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Piwik from tracking further visits of the site</a> (opt-out)."), $the_url);
		$b .= "</div>";
	}
}
function piwik_plugin_admin (&$a, &$o) {
	$t = get_markup_template( "admin.tpl", "addon/piwik/" );
	$o = replace_macros( $t, [
		'$submit' => t('Save Settings'),
		'$piwikbaseurl' => ['baseurl', t('Piwik Base URL'), Config::get('piwik','baseurl' ), t('Absolute path to your Piwik installation. (without protocol (http/s), with trailing slash)')],
		'$siteid' => ['siteid', t('Site ID'), Config::get('piwik','siteid' ), ''],
		'$optout' => ['optout', t('Show opt-out cookie link?'), Config::get('piwik','optout' ), ''],
		'$async' => ['async', t('Asynchronous tracking'), Config::get('piwik','async' ), ''],
	]);
}
function piwik_plugin_admin_post (&$a) {
	$url = ((x($_POST, 'baseurl')) ? notags(trim($_POST['baseurl'])) : '');
	$id = ((x($_POST, 'siteid')) ? trim($_POST['siteid']) : '');
	$optout = ((x($_POST, 'optout')) ? trim($_POST['optout']) : '');
	$async = ((x($_POST, 'async')) ? trim($_POST['async']) : '');
	Config::set('piwik', 'baseurl', $url);
	Config::set('piwik', 'siteid', $id);
	Config::set('piwik', 'optout', $optout);
	Config::set('piwik', 'async', $async);
	info( t('Settings updated.'). EOL);
}
