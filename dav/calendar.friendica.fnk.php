<?php

$a    = get_app();
$uri  = parse_url($a->get_baseurl());
$path = "/";
if (isset($uri["path"]) && strlen($uri["path"]) > 1) {
	$path = $uri["path"] . "/";
}

define("CALDAV_SQL_DB", "");
define("CALDAV_SQL_PREFIX", "dav_");
define("CALDAV_URL_PREFIX", $path . "dav/");

define("CALDAV_NAMESPACE_PRIVATE", 1);

define("CALDAV_FRIENDICA_MINE", "friendica-mine");
define("CALDAV_FRIENDICA_CONTACTS", "friendica-contacts");

$GLOBALS["CALDAV_PRIVATE_SYSTEM_CALENDARS"] = array(CALDAV_FRIENDICA_MINE, CALDAV_FRIENDICA_CONTACTS);

define("CARDDAV_NAMESPACE_COMMUNITYCONTACTS", 1);
define("CARDDAV_NAMESPACE_PHONECONTACTS", 2);

define("CALDAV_DB_VERSION", 2);

define("CALDAV_MAX_YEAR", date("Y") + 5);

/**
 * @return int
 */
function getCurMicrotime()
{
	list($usec, $sec) = explode(" ", microtime());
	return sprintf("%14.0f", $sec * 10000 + $usec * 10000);
} // function getCurMicrotime

/**
 *
 */
function debug_time()
{
	$cur = getCurMicrotime();
	if ($GLOBALS["debug_time_last"] > 0) {
		echo "Zeit: " . ($cur - $GLOBALS["debug_time_last"]) . "<br>\n";
	}
	$GLOBALS["debug_time_last"] = $cur;
}


/**
 * @param string $username
 * @return int|null
 */
function dav_compat_username2id($username = "")
{
	$x = q("SELECT `uid` FROM `user` WHERE `nickname`='%s' AND `account_removed` = 0 AND `account_expired` = 0", dbesc($username));
	if (count($x) == 1) return $x[0]["uid"];
	return null;
}

/**
 * @param int $id
 * @return string
 */
function dav_compat_id2username($id = 0)
{
	$x = q("SELECT `nickname` FROM `user` WHERE `uid` = %i AND `account_removed` = 0 AND `account_expired` = 0", IntVal($id));
	if (count($x) == 1) return $x[0]["nickname"];
	return "";
}

/**
 * @return int
 */
function dav_compat_get_curr_user_id()
{
	$a = get_app();
	return IntVal($a->user["uid"]);
}


/**
 * @param string $principalUri
 * @return int|null
 */
function dav_compat_principal2uid($principalUri = "")
{
	if (strlen($principalUri) == 0) return null;
	if ($principalUri[0] == "/") $principalUri = substr($principalUri, 1);
	if (strpos($principalUri, "principals/users/") !== 0) return null;
	$username = substr($principalUri, strlen("principals/users/"));
	return dav_compat_username2id($username);
}

/**
 * @param string $principalUri
 * @return array|null
 */
function dav_compat_principal2namespace($principalUri = "")
{
	if (strlen($principalUri) == 0) return null;
	if ($principalUri[0] == "/") $principalUri = substr($principalUri, 1);

	if (strpos($principalUri, "principals/users/") !== 0) return null;
	$username = substr($principalUri, strlen("principals/users/"));
	return array("namespace" => CALDAV_NAMESPACE_PRIVATE, "namespace_id" => dav_compat_username2id($username));
}


function dav_compat_currentUserPrincipal() {
	$a = get_app();
	return "principals/users/" . strtolower($a->user["nickname"]);
}


/**
 * @param string $name
 * @return null|string
 */
function dav_compat_getRequestVar($name = "")
{
	if (isset($_REQUEST[$name])) return $_REQUEST[$name];
	else return null;
}

/**
 * @param $text
 * @return null|string
 */
function dav_compat_parse_text_serverside($text)
{
	return dav_compat_getRequestVar($text);
}

/**
 * @param string $uri
 */
function dav_compat_redirect($uri = "")
{
	goaway($uri);
}


/**
 * @return null|int
 */
function dav_compat_get_max_private_calendars()
{
	return null;
}

/**
 * @param int $namespace
 * @param int $namespace_id
 * @param string $uri
 * @param array $calendar
 * @return Sabre_CalDAV_Backend_Common
 * @throws Exception
 */
function wdcal_calendar_factory($namespace, $namespace_id, $uri, $calendar = null)
{
	switch ($namespace) {
		case CALDAV_NAMESPACE_PRIVATE:
			if ($uri == CALDAV_FRIENDICA_MINE || $uri == CALDAV_FRIENDICA_CONTACTS) return Sabre_CalDAV_Backend_Friendica::getInstance();
			else return Sabre_CalDAV_Backend_Private::getInstance();
	}
	throw new Exception("Calendar Namespace not found");
}

/**
 * @param int $calendar_id
 * @return Sabre_CalDAV_Backend_Common
 * @throws Exception
 */
function wdcal_calendar_factory_by_id($calendar_id) {
	$calendar = Sabre_CalDAV_Backend_Common::loadCalendarById($calendar_id);
	return wdcal_calendar_factory($calendar["namespace"], $calendar["namespace_id"], $calendar["uri"], $calendar);
}



/**
 */
function wdcal_create_std_calendars()
{
	$a = get_app();
	if (!local_user()) return;

	$privates = q("SELECT COUNT(*) num FROM %s%scalendars WHERE `namespace` = %d AND `namespace_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CALDAV_NAMESPACE_PRIVATE, IntVal($a->user["uid"]));
	if ($privates[0]["num"] > 0) return;

	$uris = array(
		'private'                 => t("Private Calendar"),
		CALDAV_FRIENDICA_MINE     => t("Friendica Events: Mine"),
		CALDAV_FRIENDICA_CONTACTS => t("Friendica Events: Contacts"),
	);
	foreach ($uris as $uri => $name) {
		$cals = q("SELECT * FROM %s%scalendars WHERE `namespace` = %d AND `namespace_id` = %d AND `uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CALDAV_NAMESPACE_PRIVATE, $a->user["uid"], dbesc($uri));
		if (count($cals) == 0) {
			q("INSERT INTO %s%scalendars (`namespace`, `namespace_id`, `displayname`, `timezone`, `ctag`, `uri`, `has_vevent`, `has_vtodo`) VALUES (%d, %d, '%s', '%s', 1, '%s', 1, 0)",
				CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CALDAV_NAMESPACE_PRIVATE, IntVal($a->user["uid"]), dbesc($name), dbesc($a->timezone), dbesc($uri)
			);
		}
	}

}
