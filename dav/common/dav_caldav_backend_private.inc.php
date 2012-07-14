<?php

class Sabre_CalDAV_Backend_Private extends Sabre_CalDAV_Backend_Common
{


	/**
	 * @var null|Sabre_CalDAV_Backend_Private
	 */
	private static $instance = null;

	/**
	 * @static
	 * @return Sabre_CalDAV_Backend_Private
	 */
	public static function getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new Sabre_CalDAV_Backend_Private();
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
		return t("Private Events");
	}

	/**
	 * @obsolete
	 * @param array $calendar
	 * @param int $user
	 * @return array
	 */
	public function getPermissionsCalendar($calendar, $user)
	{
		if ($calendar["namespace"] == CALDAV_NAMESPACE_PRIVATE && $user == $calendar["namespace_id"]) return array("read"=> true, "write"=> true);
		return array("read"=> false, "write"=> false);
	}

	/**
	 * @obsolete
	 * @param array $calendar
	 * @param int $user
	 * @param string $calendarobject_id
	 * @param null|array $item_arr
	 * @return array
	 */
	public function getPermissionsItem($calendar, $user, $calendarobject_id, $item_arr = null)
	{
		return $this->getPermissionsCalendar($calendar, $user);
	}


	/**
	 * @param array $row
	 * @param array $calendar
	 * @param string $base_path
	 * @return array
	 */
	private function jqcal2wdcal($row, $calendar, $base_path)
	{
		$not      = q("SELECT COUNT(*) num FROM %s%snotifications WHERE `calendar_id` = %d AND `calendarobject_id` = %d",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($row["calendar_id"]), IntVal($row["calendarobject_id"])
		);
		$editable = $this->getPermissionsItem($calendar["namespace_id"], $row["calendarobject_id"], $row);

		$end = wdcal_mySql2PhpTime($row["EndTime"]);
		if ($row["IsAllDayEvent"]) $end -= 1;

		return array(
			"jq_id"             => $row["id"],
			"ev_id"             => $row["calendarobject_id"],
			"summary"           => escape_tags($row["Summary"]),
			"start"             => wdcal_mySql2PhpTime($row["StartTime"]),
			"end"               => $end,
			"is_allday"         => $row["IsAllDayEvent"],
			"is_moredays"       => 0,
			"is_recurring"      => $row["IsRecurring"],
			"color"             => (is_null($row["Color"]) || $row["Color"] == "" ? $calendar["calendarcolor"] : $row["Color"]),
			"is_editable"       => ($editable ? 1 : 0),
			"is_editable_quick" => ($editable && !$row["IsRecurring"] ? 1 : 0),
			"location"          => "Loc.",
			"attendees"         => '',
			"has_notification"  => ($not[0]["num"] > 0 ? 1 : 0),
			"url_detail"        => $base_path . $row["calendarobject_id"] . "/",
			"url_edit"          => $base_path . $row["calendarobject_id"] . "/edit/",
			"special_type"      => "",
		);
	}

	/**
	 * @param int $calendarId
	 * @param string $sd
	 * @param string $ed
	 * @param string $base_path
	 * @return array
	 */
	public function listItemsByRange($calendarId, $sd, $ed, $base_path)
	{
		$calendar = Sabre_CalDAV_Backend_Common::loadCalendarById($calendarId);
		$von      = wdcal_php2MySqlTime($sd);
		$bis      = wdcal_php2MySqlTime($ed);

		// @TODO Events, die früher angefangen haben, aber noch andauern
		$evs = q("SELECT * FROM %s%sjqcalendar WHERE `calendar_id` = %d AND `starttime` between '%s' and '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX,
			IntVal($calendarId), dbesc($von), dbesc($bis));

		$events = array();
		foreach ($evs as $row) $events[] = $this->jqcal2wdcal($row, $calendar, $base_path . $row["calendar_id"] . "/");

		return $events;
	}


	/**
	 * @param int $calendar_id
	 * @param int $calendarobject_id
	 * @return string
	 */
	public function getItemDetailRedirect($calendar_id, $calendarobject_id)
	{
		return "/dav/wdcal/$calendar_id/$calendarobject_id/edit/";
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
			if (in_array($cal["uri"], $GLOBALS["CALDAV_PRIVATE_SYSTEM_CALENDARS"])) continue;

			$dat = array(
				"id"                                                      => $cal["id"],
				"uri"                                                     => $cal["uri"],
				"principaluri"                                            => $principalUri,
				'{' . Sabre_CalDAV_Plugin::NS_CALENDARSERVER . '}getctag' => $cal['ctag'] ? $cal['ctag'] : '0',
				"calendar_class"                                          => "Sabre_CalDAV_Calendar",
			);
			foreach ($this->propertyMap as $key=> $field) $dat[$key] = $cal[$field];

			$ret[] = $dat;
		}

		return $ret;
	}


	/**
	 * Creates a new calendar for a principal.
	 *
	 * If the creation was a success, an id must be returned that can be used to reference
	 * this calendar in other methods, such as updateCalendar.
	 *
	 * @param string $principalUri
	 * @param string $calendarUri
	 * @param array $properties
	 * @throws Sabre_DAV_Exception
	 * @return string|void
	 */
	public function createCalendar($principalUri, $calendarUri, array $properties)
	{

		$uid = dav_compat_principal2uid($principalUri);

		$r = q("SELECT * FROM %s%scalendars WHERE `namespace` = %d AND `namespace_id` = %d AND `uri` = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CALDAV_NAMESPACE_PRIVATE, $uid, dbesc($calendarUri));
		if (count($r) > 0) throw new Sabre_DAV_Exception("A calendar with this URI already exists");

		$keys = array("`namespace`", "`namespace_id`", "`ctag`", "`uri`");
		$vals = array(CALDAV_NAMESPACE_PRIVATE, IntVal($uid), 1, "'" . dbesc($calendarUri) . "'");

		// Default value
		$sccs       = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
		$has_vevent = $has_vtodo = 1;
		if (isset($properties[$sccs])) {
			if (!($properties[$sccs] instanceof Sabre_CalDAV_Property_SupportedCalendarComponentSet)) {
				throw new Sabre_DAV_Exception('The ' . $sccs . ' property must be of type: Sabre_CalDAV_Property_SupportedCalendarComponentSet');
			}
			$v          = $properties[$sccs]->getValue();
			$has_vevent = $has_vtodo = 0;
			foreach ($v as $w) {
				if (mb_strtolower($w) == "vevent") $has_vevent = 1;
				if (mb_strtolower($w) == "vtodo") $has_vtodo = 1;
			}
		}
		$keys[] = "`has_vevent`";
		$keys[] = "`has_vtodo`";
		$vals[] = $has_vevent;
		$vals[] = $has_vtodo;

		foreach ($this->propertyMap as $xmlName=> $dbName) {
			if (isset($properties[$xmlName])) {
				$keys[] = "`$dbName`";
				$vals[] = "'" . dbesc($properties[$xmlName]) . "'";
			}
		}

		$sql = sprintf("INSERT INTO %s%scalendars (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $vals) . ")", CALDAV_SQL_DB, CALDAV_SQL_PREFIX);

		q($sql);

		$x = q("SELECT id FROM %s%scalendars WHERE `namespace` = %d AND `namespace_id` = %d AND `uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CALDAV_NAMESPACE_PRIVATE, $uid, $calendarUri
		);
		return $x[0]["id"];

	}

	/**
	 * Updates properties for a calendar.
	 *
	 * The mutations array uses the propertyName in clark-notation as key,
	 * and the array value for the property value. In the case a property
	 * should be deleted, the property value will be null.
	 *
	 * This method must be atomic. If one property cannot be changed, the
	 * entire operation must fail.
	 *
	 * If the operation was successful, true can be returned.
	 * If the operation failed, false can be returned.
	 *
	 * Deletion of a non-existent property is always successful.
	 *
	 * Lastly, it is optional to return detailed information about any
	 * failures. In this case an array should be returned with the following
	 * structure:
	 *
	 * array(
	 *   403 => array(
	 *      '{DAV:}displayname' => null,
	 *   ),
	 *   424 => array(
	 *      '{DAV:}owner' => null,
	 *   )
	 * )
	 *
	 * In this example it was forbidden to update {DAV:}displayname.
	 * (403 Forbidden), which in turn also caused {DAV:}owner to fail
	 * (424 Failed Dependency) because the request needs to be atomic.
	 *
	 * @param string $calendarId
	 * @param array $mutations
	 * @return bool|array
	 */
	public function updateCalendar($calendarId, array $mutations)
	{

		$newValues = array();
		$result    = array(
			200 => array(), // Ok
			403 => array(), // Forbidden
			424 => array(), // Failed Dependency
		);

		$hasError = false;

		foreach ($mutations as $propertyName=> $propertyValue) {

			// We don't know about this property.
			if (!isset($this->propertyMap[$propertyName])) {
				$hasError                   = true;
				$result[403][$propertyName] = null;
				unset($mutations[$propertyName]);
				continue;
			}

			$fieldName             = $this->propertyMap[$propertyName];
			$newValues[$fieldName] = $propertyValue;

		}

		// If there were any errors we need to fail the request
		if ($hasError) {
			// Properties has the remaining properties
			foreach ($mutations as $propertyName=> $propertyValue) {
				$result[424][$propertyName] = null;
			}

			// Removing unused statuscodes for cleanliness
			foreach ($result as $status=> $properties) {
				if (is_array($properties) && count($properties) === 0) unset($result[$status]);
			}

			return $result;

		}

		$sql = "`ctag` = `ctag` + 1";
		foreach ($newValues as $key=> $val) $sql .= ", `" . $key . "` = '" . dbesc($val) . "'";

		$sql = sprintf("UPDATE %s%scalendars SET $sql WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendarId));

		q($sql);

		return true;

	}


	/**
	 * Delete a calendar and all it's objects
	 *
	 * @param string $calendarId
	 * @return void
	 */
	public function deleteCalendar($calendarId)
	{
		q("DELETE FROM %s%scalendarobjects WHERE `calendar_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendarId));
		q("DELETE FROM %s%scalendars WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendarId));

	}


	/**
	 * Returns all calendar objects within a calendar.
	 *
	 * Every item contains an array with the following keys:
	 *   * id - unique identifier which will be used for subsequent updates
	 *   * calendardata - The iCalendar-compatible calendar data
	 *   * uri - a unique key which will be used to construct the uri. This can be any arbitrary string.
	 *   * lastmodified - a timestamp of the last modification time
	 *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
	 *   '  "abcdef"')
	 *   * calendarid - The calendarid as it was passed to this function.
	 *   * size - The size of the calendar objects, in bytes.
	 *
	 * Note that the etag is optional, but it's highly encouraged to return for
	 * speed reasons.
	 *
	 * The calendardata is also optional. If it's not returned
	 * 'getCalendarObject' will be called later, which *is* expected to return
	 * calendardata.
	 *
	 * If neither etag or size are specified, the calendardata will be
	 * used/fetched to determine these numbers. If both are specified the
	 * amount of times this is needed is reduced by a great degree.
	 *
	 * @param mixed $calendarId
	 * @return array
	 */
	function getCalendarObjects($calendarId)
	{
		$objs = q("SELECT * FROM %s%scalendarobjects WHERE `calendar_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendarId));
		$ret  = array();
		foreach ($objs as $obj) {
			$ret[] = array(
				"id"           => IntVal($obj["id"]),
				"calendardata" => $obj["calendardata"],
				"uri"          => $obj["uri"],
				"lastmodified" => $obj["lastmodified"],
				"calendarid"   => $calendarId,
				"etag"         => $obj["etag"],
				"size"         => IntVal($obj["size"]),
			);
		}
		return $ret;
	}

	/**
	 * Returns information from a single calendar object, based on it's object
	 * uri.
	 *
	 * The returned array must have the same keys as getCalendarObjects. The
	 * 'calendardata' object is required here though, while it's not required
	 * for getCalendarObjects.
	 *
	 * @param string $calendarId
	 * @param string $objectUri
	 * @throws Sabre_DAV_Exception_NotFound
	 * @return array
	 */
	function getCalendarObject($calendarId, $objectUri)
	{
		$o = q("SELECT * FROM %s%scalendarobjects WHERE `calendar_id` = %d AND `uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendarId), dbesc($objectUri));
		if (count($o) > 0) {
			$o[0]["calendarid"]   = $calendarId;
			$o[0]["calendardata"] = str_ireplace("Europe/Belgrade", "Europe/Berlin", $o[0]["calendardata"]);
			return $o[0];
		} else throw new Sabre_DAV_Exception_NotFound($calendarId . " / " . $objectUri);
	}

	/**
	 * Creates a new calendar object.
	 *
	 * It is possible return an etag from this function, which will be used in
	 * the response to this PUT request. Note that the ETag must be surrounded
	 * by double-quotes.
	 *
	 * However, you should only really return this ETag if you don't mangle the
	 * calendar-data. If the result of a subsequent GET to this object is not
	 * the exact same as this request body, you should omit the ETag.
	 *
	 * @param mixed $calendarId
	 * @param string $objectUri
	 * @param string $calendarData
	 * @return string|null
	 */
	function createCalendarObject($calendarId, $objectUri, $calendarData)
	{
		$calendarData = icalendar_sanitize_string($calendarData);

		$extraData = $this->getDenormalizedData($calendarData);

		q("INSERT INTO %s%scalendarobjects (`calendar_id`, `uri`, `calendardata`, `lastmodified`, `componentType`, `firstOccurence`, `lastOccurence`, `etag`, `size`)
			VALUES (%d, '%s', '%s', NOW(), '%s', '%s', '%s', '%s', %d)",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendarId), dbesc($objectUri), addslashes($calendarData), dbesc($extraData['componentType']),
			dbesc(wdcal_php2MySqlTime($extraData['firstOccurence'])), dbesc(wdcal_php2MySqlTime($extraData['lastOccurence'])), dbesc($extraData["etag"]), IntVal($extraData["size"])
		);

		$this->increaseCalendarCtag($calendarId);
		renderCalDavEntry_uri($objectUri);

		return '"' . $extraData['etag'] . '"';
	}

	/**
	 * Updates an existing calendarobject, based on it's uri.
	 *
	 * It is possible return an etag from this function, which will be used in
	 * the response to this PUT request. Note that the ETag must be surrounded
	 * by double-quotes.
	 *
	 * However, you should only really return this ETag if you don't mangle the
	 * calendar-data. If the result of a subsequent GET to this object is not
	 * the exact same as this request body, you should omit the ETag.
	 *
	 * @param mixed $calendarId
	 * @param string $objectUri
	 * @param string $calendarData
	 * @return string|null
	 */
	function updateCalendarObject($calendarId, $objectUri, $calendarData)
	{
		$calendarData = icalendar_sanitize_string($calendarData);

		$extraData = $this->getDenormalizedData($calendarData);

		q("UPDATE %s%scalendarobjects SET `calendardata` = '%s', `lastmodified` = NOW(), `etag` = '%s', `size` = %d, `componentType` = '%s', `firstOccurence` = '%s', `lastOccurence` = '%s'
			WHERE `calendar_id` = %d AND `uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($calendarData), dbesc($extraData["etag"]), IntVal($extraData["size"]), dbesc($extraData["componentType"]),
			dbesc(wdcal_php2MySqlTime($extraData["firstOccurence"])), dbesc(wdcal_php2MySqlTime($extraData["lastOccurence"])), IntVal($calendarId), dbesc($objectUri));

		$this->increaseCalendarCtag($calendarId);
		renderCalDavEntry_uri($objectUri);

		return '"' . $extraData['etag'] . '"';
	}

	/**
	 * Deletes an existing calendar object.
	 *
	 * @param string $calendarId
	 * @param string $objectUri
	 * @throws Sabre_DAV_Exception_NotFound
	 * @return void
	 */
	function deleteCalendarObject($calendarId, $objectUri)
	{
		$r = q("SELECT `id` FROM %s%scalendarobjects WHERE `calendar_id` = %d AND `uri` = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendarId), dbesc($objectUri));
		if (count($r) == 0) throw new Sabre_DAV_Exception_NotFound();

		q("DELETE FROM %s%scalendarobjects WHERE `calendar_id` = %d AND `uri` = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendarId), dbesc($objectUri));

		$this->increaseCalendarCtag($calendarId);
		renderCalDavEntry_calobj_id($r[0]["id"]);
	}
}
