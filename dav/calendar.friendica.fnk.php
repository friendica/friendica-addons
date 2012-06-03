<?php

define("CALDAV_SQL_DB", "");
define("CALDAV_SQL_PREFIX", "dav_");
define("CALDAV_URL_PREFIX", "dav/");

define("CALDAV_NAMESPACE_PRIVATE", 1);
define("CALDAV_NAMESPACE_FRIENDICA_NATIVE", 2);

define("CALDAV_FRIENDICA_MINE", 1);
define("CALDAV_FRIENDICA_CONTACTS", 2);

define("CARDDAV_NAMESPACE_COMMUNITYCONTACTS", 1);
define("CARDDAV_NAMESPACE_PHONECONTACTS", 2);

define("CALDAV_DB_VERSION", 1);

function getCurMicrotime () {
        list($usec, $sec) = explode(" ", microtime());
        return sprintf("%14.0f", $sec * 10000 + $usec * 10000);
} // function getCurMicrotime

function debug_time() {
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
	$x = q("SELECT `uid` FROM user WHERE nickname='%s' AND account_removed = 0 AND account_expired = 0", dbesc($username));
	if (count($x) == 1) return $x[0]["uid"];
	return null;
}

/**
 * @param int $id
 * @return string
 */
function dav_compat_id2username($id = 0)
{
	$x = q("SELECT `nickname` FROM user WHERE uid = %i AND account_removed = 0 AND account_expired = 0", IntVal($id));
	if (count($x) == 1) return $x[0]["nickname"];
	return "";
}

/**
 * @return int
 */
function dav_compat_get_curr_user_id() {
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
 * @param $text
 * @return mixed
 */
function wdcal_parse_text_serverside($text)
{
	return $text;
}

/**
 * @param int $user_id
 * @param int $namespace
 * @param int $namespace_id
 * @return AnimexxCalSource
 * @throws Exception
 */
function wdcal_calendar_factory($user_id, $namespace, $namespace_id)
{
	switch ($namespace) {
		case CALDAV_NAMESPACE_PRIVATE:
			return new AnimexxCalSourcePrivate($user_id, $namespace_id);
		case CALDAV_NAMESPACE_FRIENDICA_NATIVE:
			return new FriendicaCalSourceEvents($user_id, $namespace_id);
	}
	throw new Exception("Calendar Namespace not found");
}


/**
 */
function wdcal_create_std_calendars()
{
	$a = get_app();
	if (!local_user()) return;

	$cals = q("SELECT * FROM %s%scalendars WHERE `uid` = %d AND `namespace` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $a->user["uid"], CALDAV_NAMESPACE_PRIVATE);
	if (count($cals) == 0) {
		$maxid = q("SELECT MAX(`namespace_id`) maxid FROM %s%scalendars WHERE `namespace` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CALDAV_NAMESPACE_PRIVATE);
		if (!$maxid) {
			notification("Something went wrong when trying to create your calendar.");
			goaway("/");
			killme();
		}
		$nextid = IntVal($maxid[0]["maxid"]) + 1;
		q("INSERT INTO %s%scalendars (`namespace`, `namespace_id`, `uid`, `displayname`, `timezone`, `ctag`) VALUES (%d, %d, %d, '%s', '%s', 1)",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CALDAV_NAMESPACE_PRIVATE, $nextid, $a->user["uid"], dbesc(t("Private Calendar")), dbesc($a->timezone)
		);
	}

	$cals = q("SELECT * FROM %s%scalendars WHERE `uid` = %d AND `namespace` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $a->user["uid"], CALDAV_NAMESPACE_FRIENDICA_NATIVE);
	if (count($cals) < 2) {
		q("INSERT INTO %s%scalendars (`namespace`, `namespace_id`, `uid`, `displayname`, `timezone`, `ctag`) VALUES (%d, %d, %d, '%s', '%s', 1)",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CALDAV_NAMESPACE_FRIENDICA_NATIVE, CALDAV_FRIENDICA_MINE, $a->user["uid"], dbesc(t("Friendica Events: Mine")), dbesc($a->timezone)
		);
		q("INSERT INTO %s%scalendars (`namespace`, `namespace_id`, `uid`, `displayname`, `timezone`, `ctag`) VALUES (%d, %d, %d, '%s', '%s', 1)",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CALDAV_NAMESPACE_FRIENDICA_NATIVE, CALDAV_FRIENDICA_CONTACTS, $a->user["uid"], dbesc(t("Friendica Events: Contacts")), dbesc($a->timezone)
		);
	}
}
