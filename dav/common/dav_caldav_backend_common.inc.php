<?php

abstract class Sabre_CalDAV_Backend_Common extends Sabre_CalDAV_Backend_Abstract {
	/**
	 * List of CalDAV properties, and how they map to database fieldnames
	 *
	 * Add your own properties by simply adding on to this array
	 *
	 * @var array
	 */
	public $propertyMap = array(
		'{DAV:}displayname'                          => 'displayname',
		'{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description',
		'{urn:ietf:params:xml:ns:caldav}calendar-timezone'    => 'timezone',
		'{http://apple.com/ns/ical/}calendar-order'  => 'calendarorder',
		'{http://apple.com/ns/ical/}calendar-color'  => 'calendarcolor',
	);


	abstract public function getNamespace();
	abstract public function getCalUrlPrefix();

	/**
	 * @param int $namespace
	 * @param int $namespace_id
	 */
	protected function increaseCalendarCtag($namespace, $namespace_id) {
		$namespace = IntVal($namespace);
		$namespace_id = IntVal($namespace_id);

		q("UPDATE %s%scalendars SET `ctag` = `ctag` + 1 WHERE `namespace` = %d AND `namespace_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $namespace, $namespace_id);
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
		list(,$name) = Sabre_DAV_URLUtil::splitPath($principalUri);
		$user_id = dav_compat_username2id($name);

		$cals = q("SELECT * FROM %s%scalendars WHERE `uid`=%d AND `namespace` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $user_id, $this->getNamespace());
		$ret = array();
		foreach ($cals as $cal) {
			$dat = array(
				"id" => $cal["namespace"] . "-" . $cal["namespace_id"],
				"uri" => $this->getCalUrlPrefix() . "-" . $cal["namespace_id"],
				"principaluri" => $principalUri,
				'{' . Sabre_CalDAV_Plugin::NS_CALENDARSERVER . '}getctag' => $cal['ctag']?$cal['ctag']:'0',
				"calendar_class" => "Sabre_CalDAV_Calendar",
			);
			foreach ($this->propertyMap as $key=>$field) $dat[$key] = $cal[$field];

			$ret[] = $dat;
		}

		return $ret;
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
	 * @param mixed $calendarId
	 * @param array $mutations
	 * @return bool|array
	 */
	public function updateCalendar($calendarId, array $mutations) {

		$newValues = array();
		$result = array(
			200 => array(), // Ok
			403 => array(), // Forbidden
			424 => array(), // Failed Dependency
		);

		$hasError = false;

		foreach($mutations as $propertyName=>$propertyValue) {

			// We don't know about this property.
			if (!isset($this->propertyMap[$propertyName])) {
				$hasError = true;
				$result[403][$propertyName] = null;
				unset($mutations[$propertyName]);
				continue;
			}

			$fieldName = $this->propertyMap[$propertyName];
			$newValues[$fieldName] = $propertyValue;

		}

		// If there were any errors we need to fail the request
		if ($hasError) {
			// Properties has the remaining properties
			foreach($mutations as $propertyName=>$propertyValue) {
				$result[424][$propertyName] = null;
			}

			// Removing unused statuscodes for cleanliness
			foreach($result as $status=>$properties) {
				if (is_array($properties) && count($properties)===0) unset($result[$status]);
			}

			return $result;

		}

		$x = explode("-", $calendarId);

		$this->increaseCalendarCtag($x[0], $x[1]);

		$valuesSql = array();
		foreach($newValues as $fieldName=>$value) $valuesSql[] = "`" . $fieldName . "` = '" . dbesc($value) . "'";
		if (count($valuesSql) > 0) {
			q("UPDATE %s%scalendars SET " . implode(", ", $valuesSql) . " WHERE `namespace` = %d AND `namespace_id` = %d",
				CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[0]), IntVal($x[1])
			);
		}

		return true;

	}

}