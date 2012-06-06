<?php

class Sabre_CalDAV_Backend_Friendica extends Sabre_CalDAV_Backend_Common
{

	public function getNamespace() {
		return CALDAV_NAMESPACE_FRIENDICA_NATIVE;
	}

	public function getCalUrlPrefix() {
		return "friendica";
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
	function createCalendar($principalUri, $calendarUri, array $properties)
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
	function deleteCalendar($calendarId)
	{
		throw new Sabre_DAV_Exception_Forbidden();
	}

	/**
	 * @param string $calendarId
	 * @return array
	 */
	function getCalendarObjects($calendarId)
	{
		$a = get_app();
		$user_id = $a->user["uid"];
		$x = explode("-", $calendarId);

		$ret = array();
		$objs = FriendicaVirtualCalSourceBackend::getItemsByTime($user_id, $x[1]);
		foreach ($objs as $obj) {
			$ret[] = array(
				"id" => IntVal($obj["data_uri"]),
				"calendardata" => $obj["ical"],
				"uri" => $obj["data_uri"],
				"lastmodified" => $obj["date"],
				"calendarid" => $calendarId,
				"etag" => $obj["ical_etag"],
				"size" => IntVal($obj["ical_size"]),
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
		$a = get_app();
		$user_id = $a->user["uid"];
		$obj = FriendicaVirtualCalSourceBackend::getItemsByUri($user_id, $objectUri);

		return array(
			"id" => IntVal($obj["data_uri"]),
			"calendardata" => $obj["ical"],
			"uri" => $obj["data_uri"],
			"lastmodified" => $obj["date"],
			"calendarid" => $calendarId,
			"etag" => $obj["ical_etag"],
			"size" => IntVal($obj["ical_size"]),
		);
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
