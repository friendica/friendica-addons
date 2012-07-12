<?php

class Sabre_CalDAV_Backend_Friendica extends Sabre_CalDAV_Backend_Virtual
{

	/**
	 * @var null|Sabre_CalDAV_Backend_Friendica
	 */
	private static $instance = null;

	/**
	 * @static
	 * @return Sabre_CalDAV_Backend_Friendica
	 */
	public static function getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new Sabre_CalDAV_Backend_Friendica();
		}
		return self::$instance;
	}

	/**
	 * @return int
	 */
	public function getNamespace()
	{
		return CALDAV_NAMESPACE_PRIVATE;
	}

	/**
	 * @static
	 * @return string
	 */
	public static function getBackendTypeName() {
		return t("Friendicy-Native events");
	}

	/**
	 * @static
	 * @param int $calendarId
	 * @throws Sabre_DAV_Exception_NotFound
	 * @return void
	 */
	protected static function createCache_internal($calendarId)
	{
		$calendar = Sabre_CalDAV_Backend_Common::loadCalendarById($calendarId);

		switch ($calendar["uri"]) {
			case CALDAV_FRIENDICA_MINE:
				$sql_where = " AND cid = 0";
				break;
			case CALDAV_FRIENDICA_CONTACTS:
				$sql_where = " AND cid > 0";
				break;
			default:
				throw new Sabre_DAV_Exception_NotFound();
		}

		$r = q("SELECT * FROM `event` WHERE `uid` = %d " . $sql_where . " ORDER BY `start`", IntVal($calendar["namespace_id"]));

		foreach ($r as $row) {
			$uid       = $calendar["uri"] . "-" . $row["id"];
			$vevent    = dav_create_empty_vevent($uid);
			$component = dav_get_eventComponent($vevent);

			if ($row["adjust"]) {
				$start  = datetime_convert('UTC', date_default_timezone_get(), $row["start"]);
				$finish = datetime_convert('UTC', date_default_timezone_get(), $row["finish"]);
			} else {
				$start  = $row["start"];
				$finish = $row["finish"];
			}

			$summary = ($row["summary"] != "" ? $row["summary"] : $row["desc"]);
			$desc    = ($row["summary"] != "" ? $row["desc"] : "");
			$component->add("SUMMARY", icalendar_sanitize_string($summary));
			$component->add("LOCATION", icalendar_sanitize_string($row["location"]));
			$component->add("DESCRIPTION", icalendar_sanitize_string($desc));

			$ts_start = wdcal_mySql2PhpTime($start);
			$ts_end   = wdcal_mySql2PhpTime($start);

			$allday = (strpos($start, "00:00:00") !== false && strpos($finish, "00:00:00") !== false);
			$type           = ($allday ? Sabre_VObject_Property_DateTime::DATE : Sabre_VObject_Property_DateTime::LOCALTZ);

			$datetime_start = new Sabre_VObject_Property_DateTime("DTSTART");
			$datetime_start->setDateTime(new DateTime(date("Y-m-d H:i:s", $ts_start)), $type);
			$datetime_end = new Sabre_VObject_Property_DateTime("DTEND");
			$datetime_end->setDateTime(new DateTime(date("Y-m-d H:i:s", $ts_end)), $type);

			$component->add($datetime_start);
			$component->add($datetime_end);

			$data = $vevent->serialize();

			q("INSERT INTO %s%scal_virtual_object_cache (`calendar_id`, `data_uri`, `data_summary`, `data_location`, `data_start`, `data_end`, `data_allday`, `data_type`,
				`calendardata`, `size`, `etag`) VALUES (%d, '%s', '%s', '%s', '%s', '%s', %d, '%s', '%s', %d, '%s')",
				CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $calendarId, dbesc($uid), dbesc($summary), dbesc($row["location"]), dbesc($row["start"]), dbesc($row["finish"]),
					($allday ? 1 : 0), dbesc(($row["type"] == "birthday" ? "birthday" : "")), dbesc($data), strlen($data), md5($data));

		}

	}


	/**
	 * @param array $row
	 * @param array $calendar
	 * @param string $base_path
	 * @return array
	 */
	private function jqcal2wdcal($row, $calendar, $base_path)
	{
		if ($row["adjust"]) {
			$start  = datetime_convert('UTC', date_default_timezone_get(), $row["start"]);
			$finish = datetime_convert('UTC', date_default_timezone_get(), $row["finish"]);
		} else {
			$start  = $row["start"];
			$finish = $row["finish"];
		}

		$allday = (strpos($start, "00:00:00") !== false && strpos($finish, "00:00:00") !== false);

		$summary = (($row["summary"]) ? $row["summary"] : substr(preg_replace("/\[[^\]]*\]/", "", $row["desc"]), 0, 100));

		return array(
			"jq_id"             => $row["id"],
			"ev_id"             => $row["id"],
			"summary"           => escape_tags($summary),
			"start"             => wdcal_mySql2PhpTime($start),
			"end"               => wdcal_mySql2PhpTime($finish),
			"is_allday"         => ($allday ? 1 : 0),
			"is_moredays"       => (substr($start, 0, 10) != substr($finish, 0, 10)),
			"is_recurring"      => ($row["type"] == "birthday"),
			"color"             => "#f8f8ff",
			"is_editable"       => 0,
			"is_editable_quick" => 0,
			"location"          => $row["location"],
			"attendees"         => '',
			"has_notification"  => 0,
			"url_detail"        => $base_path . "/events/event/" . $row["id"],
			"url_edit"          => "",
			"special_type"      => ($row["type"] == "birthday" ? "birthday" : ""),
		);
	}


	/**
	 * @param int $calendarId
	 * @param string $date_from
	 * @param string $date_to
	 * @param string $base_path
	 * @throws Sabre_DAV_Exception_NotFound
	 * @return array
	 */
	public function listItemsByRange($calendarId, $date_from, $date_to, $base_path)
	{
		$calendar = Sabre_CalDAV_Backend_Common::loadCalendarById($calendarId);

		if ($calendar["namespace"] != CALDAV_NAMESPACE_PRIVATE) throw new Sabre_DAV_Exception_NotFound();

		switch ($calendar["uri"]) {
			case CALDAV_FRIENDICA_MINE:
				$sql_where = " AND cid = 0";
				break;
			case CALDAV_FRIENDICA_CONTACTS:
				$sql_where = " AND cid > 0";
				break;
			default:
				throw new Sabre_DAV_Exception_NotFound();
		}

		if ($date_from != "") {
			if (is_numeric($date_from)) $sql_where .= " AND `finish` >= '" . date("Y-m-d H:i:s", $date_from) . "'";
			else $sql_where .= " AND `finish` >= '" . dbesc($date_from) . "'";
		}
		if ($date_to != "") {
			if (is_numeric($date_to)) $sql_where .= " AND `start` <= '" . date("Y-m-d H:i:s", $date_to) . "'";
			else $sql_where .= " AND `start` <= '" . dbesc($date_to) . "'";
		}
		$ret = array();

		$r = q("SELECT * FROM `event` WHERE `uid` = %d " . $sql_where . " ORDER BY `start`", IntVal($calendar["namespace_id"]));

		$a = get_app();
		foreach ($r as $row) {
			$r                = $this->jqcal2wdcal($row, $calendar, $a->get_baseurl());
			$r["calendar_id"] = $calendar["id"];
			$ret[]            = $r;
		}

		return $ret;
	}


	/**
	 * Returns a list of calendars for a principal.
	 *
	 * Every project is an array with the following keys:
	 *  * id, a unique id that will be used by other functions to modify the
	 *    calendar. This can be the same as the uri or a database key.
	 *  * uri, which the basename of the uri with which the calendar is
	 *    accessed.
	 *  * principaluri. The owner of the calendar. Almost always the same as
	 *    principalUri passed to this method.
	 *
	 * Furthermore it can contain webdav properties in clark notation. A very
	 * common one is '{DAV:}displayname'.
	 *
	 * @param string $principalUri
	 * @return array
	 */
	public function getCalendarsForUser($principalUri)
	{
		$n = dav_compat_principal2namespace($principalUri);
		if ($n["namespace"] != $this->getNamespace()) return array();

		$cals = q("SELECT * FROM %s%scalendars WHERE `namespace` = %d AND `namespace_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $this->getNamespace(), IntVal($n["namespace_id"]));
		$ret  = array();
		foreach ($cals as $cal) {
			if (!in_array($cal["uri"], $GLOBALS["CALDAV_PRIVATE_SYSTEM_CALENDARS"])) continue;

			$dat = array(
				"id"                                                      => $cal["id"],
				"uri"                                                     => $cal["uri"],
				"principaluri"                                            => $principalUri,
				'{' . Sabre_CalDAV_Plugin::NS_CALENDARSERVER . '}getctag' => $cal['ctag'] ? $cal['ctag'] : '0',
				"calendar_class"                                          => "Sabre_CalDAV_Calendar_Virtual",
			);
			foreach ($this->propertyMap as $key=> $field) $dat[$key] = $cal[$field];

			$ret[] = $dat;
		}

		return $ret;
	}

	/**
	 * @param int $calendar_id
	 * @param int $calendarobject_id
	 * @return string
	 */
	function getItemDetailRedirect($calendar_id, $calendarobject_id)
	{
		$a    = get_app();
		$item = q("SELECT `id` FROM `item` WHERE `event-id` = %d AND `uid` = %d AND deleted = 0", IntVal($calendarobject_id), $a->user["uid"]);
		if (count($item) == 0) return "/events/";
		return "/display/" . $a->user["nickname"] . "/" . IntVal($item[0]["id"]);

	}
}
