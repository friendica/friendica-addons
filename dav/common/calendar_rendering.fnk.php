<?php



/**
 * @param Sabre_VObject_Component_VAlarm $alarm
 * @param Sabre_VObject_Component_VEvent|Sabre_VObject_Component_VTodo $parent
 * @return DateTime|null
 * @throws Sabre_DAV_Exception
 */
function renderCalDavEntry_calcalarm(&$alarm, &$parent)
{
	$trigger = $alarm->__get("TRIGGER");
	if (!isset($trigger['VALUE']) || strtoupper($trigger['VALUE']) === 'DURATION') {
		$triggerDuration = Sabre_VObject_DateTimeParser::parseDuration($trigger->value);

		$related = (isset($trigger['RELATED']) && strtoupper($trigger['RELATED']) == 'END') ? 'END' : 'START';

		if ($related === 'START') {
			/** @var Sabre_VObject_Property_DateTime $dtstart  */
			$dtstart          = $parent->__get("DTSTART");
			$effectiveTrigger = $dtstart->getDateTime();
			$effectiveTrigger->add($triggerDuration);
		} else {
			if ($parent->name === 'VTODO') {
				$endProp = 'DUE';
			} else {
				$endProp = 'DTEND';
			}

			/** @var Sabre_VObject_Property_DateTime $dtstart  */
			$dtstart = $parent->__get("DTSTART");
			if (isset($parent->$endProp)) {
				$effectiveTrigger = clone $parent->$endProp->getDateTime();
				$effectiveTrigger->add($triggerDuration);
			} elseif ($parent->__get("DURATION") != "") {
				$effectiveTrigger = clone $dtstart->getDateTime();
				$duration         = Sabre_VObject_DateTimeParser::parseDuration($parent->__get("DURATION"));
				$effectiveTrigger->add($duration);
				$effectiveTrigger->add($triggerDuration);
			} else {
				$effectiveTrigger = clone $dtstart->getDateTime();
				$effectiveTrigger->add($triggerDuration);
			}
		}
	} else {
		// ??? @TODO
		$effectiveTrigger = $trigger->getDateTime();
	}
	return $effectiveTrigger;
}

/**
 * @param array $calendar
 * @param array $calendarobject
 * @throws Sabre_DAV_Exception_BadRequest
 * @return void
 */
function renderCalDavEntry_data(&$calendar, &$calendarobject)
{
	/** @var Sabre_VObject_Component_VCalendar $vObject  */
	$vObject       = Sabre_VObject_Reader::read($calendarobject["calendardata"]);
	$componentType = null;
	/** @var Sabre_VObject_Component_VEvent $component  */
	$component = null;
	foreach ($vObject->getComponents() as $component) {
		if ($component->name !== 'VTIMEZONE') {
			$componentType = $component->name;
			break;
		}
	}
	if (!$componentType) {
		throw new Sabre_DAV_Exception_BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
	}

	$timezoneOffset = date("P"); // @TODO Get the actual timezone from the event


	if ($componentType !== 'VEVENT') return;

	$event = array(
		"description" => ($component->__get("DESCRIPTION") ? $component->__get("DESCRIPTION")->value : null),
		"summary"     => ($component->__get("SUMMARY") ? $component->__get("SUMMARY")->value : null),
		"location"    => ($component->__get("LOCATION") ? $component->__get("LOCATION")->value : null),
		"color"       => ($component->__get("X-ANIMEXX-COLOR") ? $component->__get("X-ANIMEXX-COLOR")->value : null),
	);

	$recurring = ($component->__get("RRULE") ? 1 : 0);
	/** @var Sabre_VObject_Property_DateTime $dtstart  */
	$dtstart = $component->__get("DTSTART");
	$allday  = ($dtstart->getDateType() == Sabre_VObject_Property_DateTime::DATE ? 1 : 0);

	/** @var array|Sabre_VObject_Component_VAlarm[] $alarms  */
	$alarms = array();
	foreach ($component->getComponents() as $a_component) if ($a_component->name == "VALARM") {
		/** var Sabre_VObject_Component_VAlarm $component */
		$alarms[] = $a_component;
	}

	$it       = new Sabre_VObject_RecurrenceIterator($vObject, (string)$component->__get("UID"));
	$last_end = 0;
	$max_ts   = mktime(0, 0, 0, 1, 1, CALDAV_MAX_YEAR * 1);
	$first    = true;

	while ($it->valid() && $last_end < $max_ts && ($recurring || $first)) {
		$first    = false;
		$last_end = $it->getDtEnd()->getTimestamp();
		$start    = $it->getDtStart()->getTimestamp();

		q("INSERT INTO %s%sjqcalendar (`calendar_id`, `calendarobject_id`, `Summary`, `StartTime`, `EndTime`, `IsEditable`, `IsAllDayEvent`, `IsRecurring`, `Color`) VALUES
			(%d, %d, '%s', CONVERT_TZ('%s', '$timezoneOffset', @@session.time_zone), CONVERT_TZ('%s', '$timezoneOffset', @@session.time_zone), %d, %d, %d, '%s')",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendar["id"]), IntVal($calendarobject["id"]), dbesc($event["summary"]), date("Y-m-d H:i:s", $start),
			date("Y-m-d H:i:s", $last_end), 1, $allday, $recurring, dbesc(substr($event["color"], 1))
		);

		foreach ($alarms as $alarm) {
			$alarm    = renderCalDavEntry_calcalarm($alarm, $component);
			$notified = ($alarm->getTimestamp() < time() ? 1 : 0);
			q("INSERT INTO %s%snotifications (`calendar_id`, `calendarobject_id`, `alert_date`, `notified`) VALUES (%d, %d, CONVERT_TZ('%s', '$timezoneOffset', @@session.time_zone), %d)",
				CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendar["id"]), IntVal($calendarobject["id"]), $alarm->format("Y-m-d H:i:s"), $notified
			);
		}

		$it->next();
	}

	return;

}


/**
 *
 */
function renderAllCalDavEntries()
{
	q("DELETE FROM %s%sjqcalendar", CALDAV_SQL_DB, CALDAV_SQL_PREFIX);
	q("DELETE FROM %s%snotifications", CALDAV_SQL_DB, CALDAV_SQL_PREFIX);
	$calendars = q("SELECT * FROM %s%scalendars", CALDAV_SQL_DB, CALDAV_SQL_PREFIX);
	$anz       = count($calendars);
	$i         = 0;
	foreach ($calendars as $calendar) {
		$i++;
		if (($i % 100) == 0) echo "$i / $anz\n";
		$calobjs = q("SELECT * FROM %s%scalendarobjects WHERE `calendar_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendar["id"]));
		foreach ($calobjs as $calobj) renderCalDavEntry_data($calendar, $calobj);
	}
}


/**
 * @param string $uri
 * @return bool
 */
function renderCalDavEntry_uri($uri)
{
	$calobj = q("SELECT * FROM %s%scalendarobjects WHERE `uri` = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($uri));
	if (count($calobj) == 0) return false;

	q("DELETE FROM %s%sjqcalendar WHERE `calendar_id` = %d AND `calendarobject_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calobj[0]["calendar_id"]), IntVal($calobj[0]["id"]));
	q("DELETE FROM %s%snotifications WHERE `calendar_id` = %d AND `calendarobject_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calobj[0]["calendar_id"]), IntVal($calobj[0]["id"]));

	$calendars = q("SELECT * FROM %s%scalendars WHERE `id`=%d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calobj[0]["calendar_id"]));

	renderCalDavEntry_data($calendars[0], $calobj[0]);
	return true;
}


/**
 * @param int $calobj_id
 * @return bool
 */
function renderCalDavEntry_calobj_id($calobj_id)
{
	$calobj_id = IntVal($calobj_id);
	q("DELETE FROM %s%sjqcalendar WHERE `calendarobject_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $calobj_id);
	q("DELETE FROM %s%snotifications WHERE `calendarobject_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $calobj_id);

	$calobj = q("SELECT * FROM %s%scalendarobjects WHERE `id` = '%d'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $calobj_id);
	if (count($calobj) == 0) return false;

	$calendars = q("SELECT * FROM %s%scalendars WHERE `id`=%d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calobj[0]["calendar_id"]));

	renderCalDavEntry_data($calendars[0], $calobj[0]);
	return true;
}
