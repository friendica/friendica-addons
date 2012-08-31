<?php

abstract class Sabre_CalDAV_Backend_Virtual extends Sabre_CalDAV_Backend_Common
{



	/**
	 * @static
	 * @abstract
	 * @param int $calendarId
	 * @param string $uri
	 * @return array
	 */
	/*
	abstract public function getItemsByUri($calendarId, $uri);
    */

	/**
	 * @static
	 * @param int $uid
	 * @param int $namespace
	 */
	static public function invalidateCache($uid = 0, $namespace = 0) {
		q("DELETE FROM %s%scal_virtual_object_sync WHERE `uid` = %d AND `namespace` = %d",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($uid), IntVal($namespace));
	}

	/**
	 * @static
	 * @abstract
	 * @param int $calendarId
	 */
	static abstract protected function createCache_internal($calendarId);

	/**
	 * @static
	 * @param int $calendarId
	 */
	static protected function createCache($calendarId) {
		$calendarId = IntVal($calendarId);
		q("DELETE FROM %s%scal_virtual_object_cache WHERE `calendar_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $calendarId);
		static::createCache_internal($calendarId);
		q("REPLACE INTO %s%scal_virtual_object_sync (`calendar_id`, `date`) VALUES (%d, NOW())", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $calendarId);
	}

	/**
	 * @param string $calendarId
	 * @return array
	 */
	public function getCalendarObjects($calendarId)
	{
		$calendarId = IntVal($calendarId);
		$r = q("SELECT COUNT(*) n FROM %s%scal_virtual_object_sync WHERE `calendar_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $calendarId);

		if ($r[0]["n"] == 0) static::createCache($calendarId);

		$r = q("SELECT * FROM %s%scal_virtual_object_cache WHERE `calendar_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $calendarId);

		$ret = array();
		foreach ($r as $obj) {
			$ret[] = array(
				"id" => IntVal($obj["data_uri"]),
				"calendardata" => $obj["calendardata"],
				"uri" => $obj["data_uri"],
				"lastmodified" => $obj["date"],
				"calendarid" => $calendarId,
				"etag" => $obj["etag"],
				"size" => IntVal($obj["size"]),
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
	public function getCalendarObject($calendarId, $objectUri)
	{
		$calendarId = IntVal($calendarId);
		$r = q("SELECT COUNT(*) n FROM %s%scal_virtual_object_sync WHERE `calendar_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendarId));

		if ($r[0]["n"] == 0) static::createCache($calendarId);

		$r = q("SELECT * FROM %s%scal_virtual_object_cache WHERE `data_uri` = '%s' AND `calendar_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($objectUri), IntVal($calendarId));
		if (count($r) == 0) throw new Sabre_DAV_Exception_NotFound();

		$obj = $r[0];
		$ret = array(
			"id" => IntVal($obj["data_uri"]),
			"calendardata" => $obj["calendardata"],
			"uri" => $obj["data_uri"],
			"lastmodified" => $obj["date"],
			"calendarid" => $calendarId,
			"etag" => $obj["etag"],
			"size" => IntVal($obj["size"]),
		);
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
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return void
	 */
	public function createCalendar($principalUri, $calendarUri, array $properties)
	{
		throw new Sabre_DAV_Exception_Forbidden();
	}

	/**
	 * Delete a calendar and all it's objects
	 *
	 * @param string $calendarId
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return void
	 */
	public function deleteCalendar($calendarId)
	{
		throw new Sabre_DAV_Exception_Forbidden();
	}


	/**
	 * Creates a new calendar object.
	 *
	 * @param string $calendarId
	 * @param string $objectUri
	 * @param string $calendarData
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return null|string|void
	 */
	function createCalendarObject($calendarId, $objectUri, $calendarData)
	{
		throw new Sabre_DAV_Exception_Forbidden();
	}

	/**
	 * Updates an existing calendarobject, based on it's uri.
	 *
	 * @param string $calendarId
	 * @param string $objectUri
	 * @param string $calendarData
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return null|string|void
	 */
	function updateCalendarObject($calendarId, $objectUri, $calendarData)
	{
		throw new Sabre_DAV_Exception_Forbidden();
	}

	/**
	 * Deletes an existing calendar object.
	 *
	 * @param string $calendarId
	 * @param string $objectUri
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return void
	 */
	function deleteCalendarObject($calendarId, $objectUri)
	{
		throw new Sabre_DAV_Exception_Forbidden();
	}


}
