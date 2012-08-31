<?php

abstract class Sabre_CalDAV_Backend_Common extends Sabre_CalDAV_Backend_Abstract
{
	/**
	 * @var array
	 */
	protected $propertyMap = array(
		'{DAV:}displayname'                                   => 'displayname',
		'{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description',
		'{urn:ietf:params:xml:ns:caldav}calendar-timezone'    => 'timezone',
		'{http://apple.com/ns/ical/}calendar-order'           => 'calendarorder',
		'{http://apple.com/ns/ical/}calendar-color'           => 'calendarcolor',
	);


	/**
	 * @abstract
	 * @return int
	 */
	abstract public function getNamespace();

	/**
	 * @static
	 * @abstract
	 * @return string
	 */
	abstract public static function getBackendTypeName();


	/**
	 * @param int $calendarId
	 * @param string $sd
	 * @param string $ed
	 * @param string $base_path
	 * @return array
	 */
	abstract public function listItemsByRange($calendarId, $sd, $ed, $base_path);


	/**
	 * @var array
	 */
	static private $calendarCache = array();

	/**
	 * @var array
	 */
	static private $calendarObjectCache = array();

	/**
	 * @static
	 * @param int $calendarId
	 * @return array
	 */
	static public function loadCalendarById($calendarId)
	{
		if (!isset(self::$calendarCache[$calendarId])) {
			$c                                = q("SELECT * FROM %s%scalendars WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendarId));
			self::$calendarCache[$calendarId] = $c[0];
		}
		return self::$calendarCache[$calendarId];
	}

	/**
	 * @static
	 * @param int $obj_id
	 * @return array
	 */
	static public function loadCalendarobjectById($obj_id)
	{
		if (!isset(self::$calendarObjectCache[$obj_id])) {
			$o                                  = q("SELECT * FROM %s%scalendarobjects WHERE `id` = %d",
				CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($obj_id)
			);
			self::$calendarObjectCache[$obj_id] = $o[0];
		}
		return self::$calendarObjectCache[$obj_id];
	}


	/**
	 * @static
	 * @param Sabre\VObject\Component\VEvent $component
	 * @return int
	 */
	public static function getDtEndTimeStamp(&$component)
	{
		/** @var Sabre\VObject\Property\DateTime $dtstart */
		$dtstart = $component->__get("DTSTART");
		if ($component->__get("DTEND")) {
			/** @var Sabre\VObject\Property\DateTime $dtend */
			$dtend = $component->__get("DTEND");
			return $dtend->getDateTime()->getTimeStamp();
		} elseif ($component->__get("DURATION")) {
			$endDate = clone $dtstart->getDateTime();
			$endDate->add(Sabre\VObject\DateTimeParser::parse($component->__get("DURATION")->value));
			return $endDate->getTimeStamp();
		} elseif ($dtstart->getDateType() === Sabre\VObject\Property\DateTime::DATE) {
			$endDate = clone $dtstart->getDateTime();
			$endDate->modify('+1 day');
			return $endDate->getTimeStamp();
		} else {
			return $dtstart->getDateTime()->getTimeStamp() + 3600;
		}

	}


	/**
	 * Parses some information from calendar objects, used for optimized
	 * calendar-queries.
	 *
	 * Returns an array with the following keys:
	 *   * etag
	 *   * size
	 *   * componentType
	 *   * firstOccurence
	 *   * lastOccurence
	 *
	 * @param string $calendarData
	 * @throws Sabre_DAV_Exception_BadRequest
	 * @return array
	 */
	protected function getDenormalizedData($calendarData)
	{
		/** @var Sabre\VObject\Component\VEvent $vObject */
		$vObject        = Sabre\VObject\Reader::read($calendarData);
		$componentType  = null;
		$component      = null;
		$firstOccurence = null;
		$lastOccurence  = null;

		foreach ($vObject->getComponents() as $component) {
			if ($component->name !== 'VTIMEZONE') {
				$componentType = $component->name;
				break;
			}
		}
		if (!$componentType) {
			throw new Sabre_DAV_Exception_BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
		}
		if ($componentType === 'VEVENT') {
			/** @var Sabre\VObject\Component\VEvent $component */
			/** @var Sabre\VObject\Property\DateTime $dtstart  */
			$dtstart        = $component->__get("DTSTART");
			$firstOccurence = $dtstart->getDateTime()->getTimeStamp();
			// Finding the last occurence is a bit harder
			if (!$component->__get("RRULE")) {
				$lastOccurence = self::getDtEndTimeStamp($component);
			} else {
				$it      = new Sabre\VObject\RecurrenceIterator($vObject, (string)$component->__get("UID"));
				$maxDate = new DateTime(CALDAV_MAX_YEAR . "-01-01");
				if ($it->isInfinite()) {
					$lastOccurence = $maxDate->getTimeStamp();
				} else {
					$end = $it->getDtEnd();
					while ($it->valid() && $end < $maxDate) {
						$end = $it->getDtEnd();
						$it->next();

					}
					$lastOccurence = $end->getTimeStamp();
				}

			}
		}

		return array(
			'etag'           => md5($calendarData),
			'size'           => strlen($calendarData),
			'componentType'  => $componentType,
			'firstOccurence' => $firstOccurence,
			'lastOccurence'  => $lastOccurence,
		);

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

		$this->increaseCalendarCtag($calendarId);

		$valuesSql = array();
		foreach ($newValues as $fieldName=> $value) $valuesSql[] = "`" . $fieldName . "` = '" . dbesc($value) . "'";
		if (count($valuesSql) > 0) {
			q("UPDATE %s%scalendars SET " . implode(", ", $valuesSql) . " WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendarId));
		}

		return true;

	}

	/**
	 * @param int $calendarId
	 */
	protected function increaseCalendarCtag($calendarId)
	{
		q("UPDATE %s%scalendars SET `ctag` = `ctag` + 1 WHERE `id` = '%d'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendarId));
		self::$calendarObjectCache = array();
	}

	/**
	 * @abstract
	 * @param int $calendar_id
	 * @param int $calendarobject_id
	 * @return string
	 */
	abstract function getItemDetailRedirect($calendar_id, $calendarobject_id);

}