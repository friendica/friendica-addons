<?php

class Sabre_CalDAV_Backend_Std extends Sabre_CalDAV_Backend_Common
{

	public function getNamespace()
	{
		return CALDAV_NAMESPACE_PRIVATE;
	}

	public function getCalUrlPrefix()
	{
		return "private";
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
	 * @return void
	 */
	public function createCalendar($principalUri, $calendarUri, array $properties)
	{
		// TODO: Implement createCalendar() method.
	}

	/**
	 * Delete a calendar and all it's objects
	 *
	 * @param string $calendarId
	 * @return void
	 */
	public function deleteCalendar($calendarId)
	{
		// TODO: Implement deleteCalendar() method.
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
	 * @param string $calendarId
	 * @return array
	 */
	function getCalendarObjects($calendarId)
	{
		$x    = explode("-", $calendarId);
		$objs = q("SELECT * FROM %s%scalendarobjects WHERE `namespace` = %d AND `namespace_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[0]), IntVal($x[1]));
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
	 * @throws Sabre_DAV_Exception_FileNotFound
	 * @return array
	 */
	function getCalendarObject($calendarId, $objectUri)
	{
		$x = explode("-", $calendarId);

		$o = q("SELECT * FROM %s%scalendarobjects WHERE `namespace` = %d AND `namespace_id` = %d AND `uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[0]), IntVal($x[1]), dbesc($objectUri));
		if (count($o) > 0) {
			$o[0]["calendarid"]   = $calendarId;
			$o[0]["calendardata"] = str_ireplace("Europe/Belgrade", "Europe/Berlin", $o[0]["calendardata"]);
			return $o[0];
		} else throw new Sabre_DAV_Exception_FileNotFound($calendarId . " / " . $objectUri);
	}

	/**
	 * Creates a new calendar object.
	 *
	 * @param string $calendarId
	 * @param string $objectUri
	 * @param string $calendarData
	 * @return null|string|void
	 */
	function createCalendarObject($calendarId, $objectUri, $calendarData)
	{
		$x = explode("-", $calendarId);

		q("INSERT INTO %s%scalendarobjects (`namespace`, `namespace_id`, `uri`, `calendardata`, `lastmodified`, `etag`, `size`) VALUES (%d, %d, '%s', '%s', NOW(), '%s', %d)",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX,
			IntVal($x[0]), IntVal($x[1]), dbesc($objectUri), addslashes($calendarData), md5($calendarData), strlen($calendarData)
		);

		$this->increaseCalendarCtag($x[0], $x[1]);
		renderCalDavEntry_uri($objectUri);
	}

	/**
	 * Updates an existing calendarobject, based on it's uri.
	 *
	 * @param string $calendarId
	 * @param string $objectUri
	 * @param string $calendarData
	 * @return null|string|void
	 */
	function updateCalendarObject($calendarId, $objectUri, $calendarData)
	{
		$x = explode("-", $calendarId);

		q("UPDATE %s%scalendarobjects SET `calendardata` = '%s', `lastmodified` = NOW(), `etag` = '%s', `size` = %d WHERE `namespace` = %d AND `namespace_id` = %d AND `uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($calendarData), md5($calendarData), strlen($calendarData), IntVal($x[0]), IntVal($x[1]), dbesc($objectUri));

		$this->increaseCalendarCtag($x[0], $x[1]);
		renderCalDavEntry_uri($objectUri);
	}

	/**
	 * Deletes an existing calendar object.
	 *
	 * @param string $calendarId
	 * @param string $objectUri
	 * @return void
	 */
	function deleteCalendarObject($calendarId, $objectUri)
	{
		$x = explode("-", $calendarId);

		q("DELETE FROM %s%scalendarobjects WHERE `namespace` = %d AND `namespace_id` = %d AND `uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[0]), IntVal($x[1]), dbesc($objectUri)
		);

		$this->increaseCalendarCtag($x[0], $x[1]);
		renderCalDavEntry_uri($objectUri);
	}
}
