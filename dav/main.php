<?php

require_once('include/security.php');

function dav_install()
{
	register_hook('event_created', 'addon/dav/dav.php', 'dav_event_created_hook');
	register_hook('event_updated', 'addon/dav/dav.php', 'dav_event_updated_hook');
	register_hook('profile_tabs', 'addon/dav/dav.php', 'dav_profile_tabs_hook');
	register_hook('cron', 'addon/dav/dav.php', 'dav_cron');
}


function dav_uninstall()
{
	unregister_hook('event_created', 'addon/dav/dav.php', 'dav_event_created_hook');
	unregister_hook('event_updated', 'addon/dav/dav.php', 'dav_event_updated_hook');
	unregister_hook('profile_tabs', 'addon/dav/dav.php', 'dav_profile_tabs_hook');
	unregister_hook('cron', 'addon/dav/dav.php', 'dav_cron');
}


function dav_module()
{
}

function dav_include_files()
{
	require_once (__DIR__ . "/SabreDAV/lib/Sabre/autoload.php");

	require_once (__DIR__ . "/common/calendar.fnk.php");
	require_once (__DIR__ . "/common/calendar_rendering.fnk.php");
	require_once (__DIR__ . "/common/dav_caldav_backend_common.inc.php");
	require_once (__DIR__ . "/common/dav_caldav_backend_private.inc.php");
	require_once (__DIR__ . "/common/dav_caldav_backend_virtual.inc.php");
	require_once (__DIR__ . "/common/dav_caldav_root.inc.php");
	require_once (__DIR__ . "/common/dav_user_calendars.inc.php");
	require_once (__DIR__ . "/common/dav_carddav_root.inc.php");
	require_once (__DIR__ . "/common/dav_carddav_backend_std.inc.php");
	require_once (__DIR__ . "/common/dav_user_addressbooks.inc.php");
	require_once (__DIR__ . "/common/dav_caldav_calendar_virtual.inc.php");
	require_once (__DIR__ . "/common/wdcal_configuration.php");
	require_once (__DIR__ . "/common/wdcal_backend.inc.php");

	require_once (__DIR__ . "/dav_friendica_principal.inc.php");
	require_once (__DIR__ . "/dav_friendica_auth.inc.php");
	require_once (__DIR__ . "/dav_carddav_backend_virtual_friendica.inc.php");
	require_once (__DIR__ . "/dav_caldav_backend_virtual_friendica.inc.php");
	require_once (__DIR__ . "/FriendicaACLPlugin.inc.php");

	require_once (__DIR__ . "/common/wdcal_edit.inc.php");
	require_once (__DIR__ . "/calendar.friendica.fnk.php");
	require_once (__DIR__ . "/layout.fnk.php");
}


/**
 * @param App $a
 */
function dav_init(&$a)
{

	/*
	 * Recommended settings:
	 * ALTER TABLE `photo` ADD INDEX ( `contact-id` )
	 */

	dav_include_files();

	if (false) {
		dbg(true);
		error_reporting(E_ALL);
		ini_set("display_errors", 1);
	}

	wdcal_create_std_calendars();
	wdcal_addRequiredHeaders();

	if ($a->argc >= 2 && $a->argv[1] == "wdcal") {

		if ($a->argc >= 3 && $a->argv[2] == "feed") {
			wdcal_print_feed($a->get_baseurl() . "/dav/wdcal/");
			killme();
		}
		return;
	}
	if ($a->argc >= 2 && $a->argv[1] == "getExceptionDates") {
		echo wdcal_getEditPage_exception_selector();
		killme();
	}

	if ($a->argc >= 2 && $a->argv[1] == "settings") {
		return;
	}


	if (isset($_REQUEST["test"])) {
		renderAllCalDavEntries();
	}


	$server  = dav_create_server();
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
	try {
		if ($a->argv[1] == "settings") {
			return wdcal_getSettingsPage($a);
		} elseif ($a->argv[1] == "wdcal") {
			if (isset($a->argv[2]) && strlen($a->argv[2]) > 0) {
				if ($a->argv[2] == "new") {
					$o = "";
					if (isset($_REQUEST["save"])) {
						check_form_security_token_redirectOnErr($a->get_baseurl() . "/dav/wdcal/", "caledit");
						$ret = wdcal_postEditPage("new", "", $a->user["uid"], $a->timezone, $a->get_baseurl() . "/dav/wdcal/");
						if ($ret["ok"]) notice($ret["msg"]);
						else info($ret["msg"]);
						goaway($a->get_baseurl() . "/dav/wdcal/");
					}
					$o .= wdcal_getNewPage();
					return $o;
				} else {
					$calendar_id = IntVal($a->argv[2]);
					if (isset($a->argv[3]) && $a->argv[3] == "ics-export") {
						wdcal_print_user_ics($calendar_id);
					} elseif (isset($a->argv[3]) && $a->argv[3] == "ics-import") {
						return wdcal_import_user_ics($calendar_id);
					} elseif (isset($a->argv[3]) && $a->argv[3] > 0) {
						if (isset($a->argv[4]) && $a->argv[4] == "edit") {
							$o = "";
							if (isset($_REQUEST["save"])) {
								check_form_security_token_redirectOnErr($a->get_baseurl() . "/dav/wdcal/", "caledit");
								$ret = wdcal_postEditPage($a->argv[3], $a->user["uid"], $a->timezone, $a->get_baseurl() . "/dav/wdcal/");
								if ($ret["ok"]) notice($ret["msg"]);
								else info($ret["msg"]);
								goaway($a->get_baseurl() . "/dav/wdcal/");
							}
							$o .= wdcal_getEditPage($calendar_id, $a->argv[3]);
							return $o;
						} else {
							return wdcal_getDetailPage($calendar_id, $a->argv[3]);
						}
					} else {
						// @TODO Edit Calendar
					}
				}
			} else {
				$server = dav_create_server(true, true, false);
				$cals   = dav_get_current_user_calendars($server, DAV_ACL_READ);
				$x      = wdcal_printCalendar($cals, array(), $a->get_baseurl() . "/dav/wdcal/feed/", "week", 0, 200);
			}
		}
	} catch (DAVVersionMismatchException $e) {
		$x = t("The current version of this plugin has not been set up correctly. Please contact the system administrator of your installation of friendica to fix this.");
	}
	return $x;
}


/**
 * @param App $a
 * @param object $b
 */
function dav_event_created_hook(&$a, &$b)
{
	dav_include_files();
	// @TODO Updating the cache instead of completely invalidating and rebuilding it
	Sabre_CalDAV_Backend_Friendica::invalidateCache($a->user["uid"], CALDAV_FRIENDICA_CONTACTS);
	Sabre_CalDAV_Backend_Friendica::invalidateCache($a->user["uid"], CALDAV_FRIENDICA_MINE);
}

/**
 * @param App $a
 * @param object $b
 */
function dav_event_updated_hook(&$a, &$b)
{
	dav_include_files();
	// @TODO Updating the cache instead of completely invalidating and rebuilding it
	Sabre_CalDAV_Backend_Friendica::invalidateCache($a->user["uid"], CALDAV_FRIENDICA_CONTACTS);
	Sabre_CalDAV_Backend_Friendica::invalidateCache($a->user["uid"], CALDAV_FRIENDICA_MINE);
}

/**
 * @param App $a
 * @param object $b
 */
function dav_profile_tabs_hook(&$a, &$b)
{
	$b["tabs"][] = array(
		"label" => t('Calendar'),
		"url"   => $a->get_baseurl() . "/dav/wdcal/",
		"sel"   => "",
		"title" => t('Extended calendar with CalDAV-support'),
	);
}


/**
 * @param App $a
 * @param object $b
 */
function dav_cron(&$a, &$b)
{
	dav_include_files();

	$r = q("SELECT * FROM %s%snotifications WHERE `notified` = 0 AND `alert_date` <= NOW()", CALDAV_SQL_DB, CALDAV_SQL_PREFIX);
	foreach ($r as $not) {
		q("UPDATE %s%snotifications SET `notified` = 1 WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $not["id"]);
		$event    = q("SELECT * FROM %s%sjqcalendar WHERE `calendarobject_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $not["calendarobject_id"]);
		$calendar = q("SELECT * FROM %s%scalendars WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $not["calendar_id"]);
		$users    = array();
		if (count($calendar) != 1 || count($event) == 0) continue;
		switch ($calendar[0]["namespace"]) {
			case CALDAV_NAMESPACE_PRIVATE:
				$user = q("SELECT * FROM user WHERE `uid` = %d AND `blocked` = 0", $calendar[0]["namespace_id"]);
				if (count($user) != 1) continue;
				$users[] = $user[0];
				break;
		}
		switch ($not["action"]) {
			case "email":
			case "display": // @TODO implement "Display"
				foreach ($users as $user) {
					$find      = array("%to%", "%event%", "%url%");
					$repl      = array($user["username"], $event[0]["Summary"], $a->get_baseurl() . "/dav/wdcal/" . $calendar[0]["id"] . "/" . $not["calendarobject_id"] . "/");
					$text_text = str_replace($find, $repl, "Hi %to%!\n\nThe event \"%event%\" is about to begin:\n%url%");
					$text_html = str_replace($find, $repl, "Hi %to%!<br>\n<br>\nThe event \"%event%\" is about to begin:<br>\n<a href='" . "%url%" . "'>%url%</a>");
					$params    = array(
						'fromName'             => FRIENDICA_PLATFORM,
						'fromEmail'            => t('noreply') . '@' . $a->get_hostname(),
						'replyTo'              => t('noreply') . '@' . $a->get_hostname(),
						'toEmail'              => $user["email"],
						'messageSubject'       => t("Notification: " . $event[0]["Summary"]),
						'htmlVersion'          => $text_html,
						'textVersion'          => $text_text,
						'additionalMailHeader' => "",
					);
					require_once('include/enotify.php');
					enotify::send($params);
				}
				break;
		}
	}
}


/**
 * @param App $a
 * @param null|object $o
 */
function dav_plugin_admin_post(&$a = null, &$o = null)
{
	check_form_security_token_redirectOnErr('/admin/plugins/dav', 'dav_admin_save');

	dav_include_files();
	require_once(__DIR__ . "/database-init.inc.php");

	if (isset($_REQUEST["install"])) {
		$errs = dav_create_tables();
		if (count($errs) == 0) info(t('The database tables have been installed.') . EOL);
		else notice(t("An error occurred during the installation.") . EOL);
	}
	if (isset($_REQUEST["upgrade"])) {
		$errs = dav_upgrade_tables();
		if (count($errs) == 0) {
			renderAllCalDavEntries();
			info(t('The database tables have been updated.') . EOL);
		}
		else notice(t("An error occurred during the update.") . EOL);
	}
}

/**
 * @param App $a
 * @param string $o
 */
function dav_plugin_admin(&$a, &$o)
{
	dav_include_files();
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
			$o .= "<strong>" . t('Upgrade needed') . "</strong><br>" . t("Please back up all calendar data (the tables beginning with dav_*) before proceeding. While all calendar events <i>should</i> be converted to the new database structure, it's always safe to have a backup. Below, you can have a look at the database-queries that will be made when pressing the 'update'-button.") . "<br><br><input type='submit' name='upgrade' value='" . t('Upgrade') . "'>";
			break;
		case -1:
			$o .= t('Not installed') . "<br><br><input type='submit' name='install' value='" . t('Install') . "'>";
			break;
		case -2:
		default:
			$o .= t('Unknown') . "<br><br>" . t("Something really went wrong. I cannot recover from this state automatically, sorry. Please go to the database backend, back up the data, and delete all tables beginning with 'dav_' manually. Afterwards, this installation routine should be able to reinitialize the tables automatically.");
			break;
	}
	$o .= "<br><br>";

	$o .= "<h3>" . t("Troubleshooting") . "</h3>";
	$o .= "<h4>" . t("Manual creation of the database tables:") . "</h4>";
	$o .= "<a href='#' onClick='\$(\"#sqlstatements\").show(); return false;'>" . t("Show SQL-statements") . "</a><blockquote style='display: none;' id='sqlstatements'><pre>";
	switch ($dbstatus) {
		case 1:
			$tables = dav_get_update_statements(1);
			foreach ($tables as $t) $o .= escape_tags($t . ";\n\n");
			break;
		default:
			$tables = dav_get_create_statements();
			foreach ($tables as $t) $o .= escape_tags($t . ";\n\n");
			break;
	}
	$o .= "</pre></blockquote>";
}
