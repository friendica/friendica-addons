<?php

require_once('include/security.php');

function dav_install()
{
	register_hook('event_created', 'addon/dav/dav.php', 'dav_event_created_hook');
	register_hook('event_updated', 'addon/dav/dav.php', 'dav_event_updated_hook');
	register_hook('profile_tabs', 'addon/dav/dav.php', 'dav_profile_tabs_hook');
}


function dav_uninstall()
{
	unregister_hook('event_created', 'addon/dav/dav.php', 'dav_event_created_hook');
	unregister_hook('event_updated', 'addon/dav/dav.php', 'dav_event_updated_hook');
	unregister_hook('profile_tabs', 'addon/dav/dav.php', 'dav_profile_tabs_hook');
}


function dav_module()
{
}


/**
 * @param App $a
 */
function dav_init(&$a)
{

	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	/*
	 * Recommended settings:
	 * ALTER TABLE `photo` ADD INDEX ( `contact-id` )
	 */

	require_once (__DIR__ . "/common/dbclasses/dbclass_animexx.class.php");
	require_once (__DIR__ . "/common/dbclasses/dbclass.friendica.calendars.class.php");
	require_once (__DIR__ . "/common/dbclasses/dbclass.friendica.jqcalendar.class.php");
	require_once (__DIR__ . "/common/dbclasses/dbclass.friendica.notifications.class.php");
	require_once (__DIR__ . "/common/dbclasses/dbclass.friendica.calendarobjects.class.php");

	/*
	require_once (__DIR__ . "/SabreDAV/lib/Sabre.includes.php");
	require_once (__DIR__ . "/SabreDAV/lib/Sabre/VObject/includes.php");
	require_once (__DIR__ . "/SabreDAV/lib/Sabre/DAVACL/includes.php");
	require_once (__DIR__ . "/SabreDAV/lib/Sabre/CalDAV/includes.php");
	*/
	require_once (__DIR__ . "/SabreDAV/lib/Sabre/autoload.php");

	$tz_before = date_default_timezone_get();
	require_once (__DIR__ . "/iCalcreator/iCalcreator.class.php");
	date_default_timezone_set($tz_before);

	require_once (__DIR__ . "/common/calendar.fnk.php");
	require_once (__DIR__ . "/common/dav_caldav_backend_common.inc.php");
	require_once (__DIR__ . "/common/dav_caldav_backend.inc.php");
	require_once (__DIR__ . "/common/dav_caldav_root.inc.php");
	require_once (__DIR__ . "/common/dav_user_calendars.inc.php");
	require_once (__DIR__ . "/common/dav_carddav_root.inc.php");
	require_once (__DIR__ . "/common/dav_carddav_backend_std.inc.php");
	require_once (__DIR__ . "/common/dav_user_addressbooks.inc.php");
	require_once (__DIR__ . "/common/virtual_cal_source_backend.inc.php");
	require_once (__DIR__ . "/common/wdcal_configuration.php");
	require_once (__DIR__ . "/common/wdcal_cal_source.inc.php");
	require_once (__DIR__ . "/common/wdcal_cal_source_private.inc.php");

	require_once (__DIR__ . "/dav_friendica_principal.inc.php");
	require_once (__DIR__ . "/dav_friendica_auth.inc.php");
	require_once (__DIR__ . "/dav_carddav_backend_friendica_community.inc.php");
	require_once (__DIR__ . "/dav_caldav_backend_friendica.inc.php");
	require_once (__DIR__ . "/virtual_cal_source_friendica.inc.php");
	require_once (__DIR__ . "/wdcal_cal_source_friendicaevents.inc.php");
	require_once (__DIR__ . "/FriendicaACLPlugin.inc.php");

	require_once (__DIR__ . "/calendar.friendica.fnk.php");
	require_once (__DIR__ . "/layout.fnk.php");

	if (false) {
		dbg(true);
		error_reporting(E_ALL);
		ini_set("display_errors", 1);
	}

	wdcal_create_std_calendars();


	if ($a->argc >= 2 && $a->argv[1] == "wdcal") {

		if ($a->argc >= 3 && $a->argv[2] == "feed") {
			wdcal_print_feed();
			killme();
		} elseif ($a->argc >= 3 && strlen($a->argv[2]) > 0) {
			wdcal_addRequiredHeadersEdit();
		} else {
			wdcal_addRequiredHeaders();
		}
		return;
	}

	if ($a->argc >= 2 && $a->argv[1] == "settings") {
		return;
	}

	$authBackend              = new Sabre_DAV_Auth_Backend_Friendica();
	$principalBackend         = new Sabre_DAVACL_PrincipalBackend_Friendica($authBackend);
	$caldavBackend_std        = new Sabre_CalDAV_Backend_Std();
	$caldavBackend_community  = new Sabre_CalDAV_Backend_Friendica();
	$carddavBackend_std       = new Sabre_CardDAV_Backend_Std();
	$carddavBackend_community = new Sabre_CardDAV_Backend_FriendicaCommunity();

	if (isset($_SERVER["PHP_AUTH_USER"])) {
		$tree = new Sabre_DAV_SimpleCollection('root', array(
			new Sabre_DAV_SimpleCollection('principals', array(
				new Sabre_CalDAV_Principal_Collection($principalBackend, "principals/users"),
			)),
			new Sabre_CalDAV_AnimexxCalendarRootNode($principalBackend, array(
				$caldavBackend_std,
				$caldavBackend_community,
			)),
			new Sabre_CardDAV_AddressBookRootFriendica($principalBackend, array(
				$carddavBackend_std,
				$carddavBackend_community,
			)),
		));
	} else {
		$tree = new Sabre_DAV_SimpleCollection('root', array());
	}

// The object tree needs in turn to be passed to the server class
	$server = new Sabre_DAV_Server($tree);

	$server->setBaseUri("/" . CALDAV_URL_PREFIX);

	$authPlugin = new Sabre_DAV_Auth_Plugin($authBackend, 'SabreDAV');
	$server->addPlugin($authPlugin);

	$aclPlugin                      = new Sabre_DAVACL_Plugin_Friendica();
	$aclPlugin->defaultUsernamePath = "principals/users";
	$server->addPlugin($aclPlugin);

	$caldavPlugin = new Sabre_CalDAV_Plugin();
	$server->addPlugin($caldavPlugin);

	$carddavPlugin = new Sabre_CardDAV_Plugin();
	$server->addPlugin($carddavPlugin);

	$browser = new Sabre_DAV_Browser_Plugin();
	$server->addPlugin($browser);

	$server->exec();

	killme();
}

/**
 * @return string
 */
function dav_content()
{
	$a = get_app();
	if (!isset($a->user["uid"]) || $a->user["uid"] == 0) {
		return login();
	}

	$x = "";

	if ($a->argv[1] == "settings") {
		return wdcal_getSettingsPage($a);
	} elseif ($a->argv[1] == "wdcal") {
		if ($a->argc >= 3 && strlen($a->argv[2]) > 0) {
			$uri        = $a->argv[2];
			$recurr_uri = ""; // @TODO
			if (isset($a->argv[3]) && $a->argv[3] == "edit") {
				$o = "";
				if (isset($_REQUEST["save"])) $o .= wdcal_postEditPage($uri, $recurr_uri);
				$o .= wdcal_getEditPage($uri, $recurr_uri);
				return $o;
			} else {
				return wdcal_getDetailPage($uri, $recurr_uri);
			}
		} else {
			$cals      = dav_getMyCals($a->user["uid"]);
			$cals_show = array();
			foreach ($cals as $e) $cals_show[] = array("ns" => $e->namespace, "id" => $e->namespace_id, "displayname" => $e->displayname);
			$x = wdcal_printCalendar($cals, $cals_show, "/dav/wdcal/feed/", "week", 0, 200);
		}
	}
	return $x;
}


/**
 * @param App $a
 * @param object $b
 */
function dav_event_created_hook(&$a, &$b)
{
	// @TODO Updating the cache instead of completely invalidating and rebuilding it
	FriendicaVirtualCalSourceBackend::invalidateCache($a->user["uid"], CALDAV_FRIENDICA_CONTACTS);
	FriendicaVirtualCalSourceBackend::invalidateCache($a->user["uid"], CALDAV_FRIENDICA_MINE);
}

/**
 * @param App $a
 * @param object $b
 */
function dav_event_updated_hook(&$a, &$b)
{
	// @TODO Updating the cache instead of completely invalidating and rebuilding it
	FriendicaVirtualCalSourceBackend::invalidateCache($a->user["uid"], CALDAV_FRIENDICA_CONTACTS);
	FriendicaVirtualCalSourceBackend::invalidateCache($a->user["uid"], CALDAV_FRIENDICA_MINE);
}

/**
 * @param App $a
 * @param object $b
 */
function dav_profile_tabs_hook(&$a, &$b)
{
	$b["tabs"][] = array(
    	"label" => t('Calendar'),
		"url" => "/dav/wdcal/",
		"sel" => "",
		"title" => t('Extended calendar with CalDAV-support'),
	);
}

/**
 * @param App $a
 * @param null|object $o
 */
function dav_plugin_admin_post(&$a = null, &$o = null)
{
	check_form_security_token_redirectOnErr('/admin/plugins/dav', 'dav_admin_save');

	require_once(__DIR__ . "/database-init.inc.php");

	if (isset($_REQUEST["install"])) {
		$errs = dav_create_tables();
		if (count($errs) == 0) info(t('The database tables have been installed.') . EOL);
		else notice(t("An error occurred during the installation.") . EOL);
	}
}

/**
 * @param App $a
 * @param null|object $o
 */
function dav_plugin_admin(&$a, &$o)
{

	require_once(__DIR__ . "/database-init.inc.php");

	$dbstatus = dav_check_tables();

	$o = '<input type="hidden" name="form_security_token" value="' . get_form_security_token("dav_admin_save") . '">';
	$o .= '<i>' . t("No system-wide settings yet.") . '</i><br><br>';


	$o .= '<h3>' . t('Database status') . '</h3>';
	switch ($dbstatus) {
		case 0:
			$o .= t('Installed');
			break;
		case 1:
			$o .= t('Upgrade needed') . "<br><br><input type='submit' name='upgrade' value='" . t('Upgrade') . "'>";
			break;
		case -1:
			$o .= t('Not installed') . "<br><br><input type='submit' name='install' value='" . t('Install') . "'>";
			break;
	}
	$o .= "<br><br>";

	$o .= "<h3>" . t("Troubleshooting") . "</h3>";
	$o .= "<h4>" . t("Manual creation of the database tables:") . "</h4>";
	$o .= "<a href='#' onClick='\$(\"#sqlstatements\").show(); return false;'>" . t("Show SQL-statements") . "</a><blockquote style='display: none;' id='sqlstatements'><pre>";
	$tables = dav_get_create_statements();
	foreach ($tables as $t) $o .= escape_tags($t . "\n\n");
	$o .= "</pre></blockquote>";
}