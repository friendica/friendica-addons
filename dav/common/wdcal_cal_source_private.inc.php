<?php

class AnimexxCalSourcePrivate extends AnimexxCalSource
{

	/**
	 * @return int
	 */
	public static function getNamespace()
	{
		return CALDAV_NAMESPACE_PRIVATE;
	}

	/**
	 * @param int $user
	 * @return array
	 */
	public function getPermissionsCalendar($user)
	{
		if ($user == $this->calendarDb->uid) return array("read"=> true, "write"=> true);
		return array("read"=> false, "write"=> false);
	}

	/**
	 * @param int $user
	 * @param string $item_uri
	 * @param string $recurrence_uri
	 * @param null|array $item_arr
	 * @return array
	 */
	public function getPermissionsItem($user, $item_uri, $recurrence_uri, $item_arr = null)
	{
		$cal_perm = $this->getPermissionsCalendar($user);
		if (!$cal_perm["read"]) return array("read"=> false, "write"=> false);
		if (!$cal_perm["write"]) array("read"=> true, "write"=> false);

		if ($item_arr === null) {
			$x = q("SELECT `permission_edit` FROM %s%sjqcalendar WHERE `namespace` = %d AND `namespace_id` = %d AND `ical_uri` = '%s' AND `ical_recurr_uri` = '%s'",
				CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $this->getNamespace(), $this->namespace_id, dbesc($item_uri), dbesc($recurrence_uri)
			);
			if (!$x || count($x) == 0) return array("read"=> false, "write"=> false);
			return array("read"=> true, "write"=> ($x[0]["permission_edit"]));
		} else {
			return array("read"=> true, "write"=> ($item_arr["permission_edit"]));
		}

	}

	/**
	 * @param string $uri
	 * @throws Sabre_DAV_Exception_NotFound
	 */
	public function removeItem($uri){
		$obj_alt = q("SELECT * FROM %s%sjqcalendar WHERE namespace = %d AND namespace_id = %d AND ical_uri = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $this->getNamespace(), $this->namespace_id, dbesc($uri));

		if (count($obj_alt) == 0) throw new Sabre_DAV_Exception_NotFound("Not found");

		$calendarBackend = new Sabre_CalDAV_Backend_Std();
		$calendarBackend->deleteCalendarObject($this->getNamespace() . "-" . $this->namespace_id, $obj_alt[0]["ical_uri"]);
	}

	/**
	 * @param string $uri
	 * @param array $start
	 * @param array $end
	 * @param string $subject
	 * @param bool $allday
	 * @param string $description
	 * @param string $location
	 * @param null $color
	 * @param string $timezone
	 * @param bool $notification
	 * @param null $notification_type
	 * @param null $notification_value
	 * @throws Sabre_DAV_Exception_NotFound
	 * @throws Sabre_DAV_Exception_Conflict
	 */
	public function updateItem($uri, $start, $end, $subject = "", $allday = false, $description = "", $location = "", $color = null, $timezone = "", $notification = true, $notification_type = null, $notification_value = null)
	{
		$a = get_app();

		$usr_id = IntVal($this->calendarDb->uid);

		$old = q("SELECT * FROM %s%sjqcalendar WHERE `uid` = %d AND `namespace` = %d AND `namespace_id` = %d AND `ical_uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $usr_id, $this->getNamespace(), $this->namespace_id, dbesc($uri));
		if (count($old) == 0) throw new Sabre_DAV_Exception_NotFound("Not Found 1");
		$old_obj = new DBClass_friendica_jqcalendar($old[0]);

		$calendarBackend = new Sabre_CalDAV_Backend_Std();
		$obj             = $calendarBackend->getCalendarObject($this->getNamespace() . "-" . $this->namespace_id, $old_obj->ical_uri);
		if (!$obj) throw new Sabre_DAV_Exception_NotFound("Not Found 2");

		$v = new vcalendar();
		$v->setConfig('unique_id', $a->get_hostname());

		$v->setMethod('PUBLISH');
		$v->setProperty("x-wr-calname", "AnimexxCal");
		$v->setProperty("X-WR-CALDESC", "Animexx Calendar");
		$v->setProperty("X-WR-TIMEZONE", $a->timezone);

		$obj["calendardata"] = icalendar_sanitize_string($obj["calendardata"]);

		$v->parse($obj["calendardata"]);
		/** @var $vevent vevent */
		$vevent = $v->getComponent('vevent');

		if (trim($vevent->getProperty('uid')) . ".ics" != $old_obj->ical_uri)
			throw new Sabre_DAV_Exception_Conflict("URI != URI: " . $old_obj->ical_uri . " vs. " . trim($vevent->getProperty("uid")));

		if ($end["year"] < $start["year"] ||
			($end["year"] == $start["year"] && $end["month"] < $start["month"]) ||
			($end["year"] == $start["year"] && $end["month"] == $start["month"] && $end["day"] < $start["day"]) ||
			($end["year"] == $start["year"] && $end["month"] == $start["month"] && $end["day"] == $start["day"] && $end["hour"] < $start["hour"]) ||
			($end["year"] == $start["year"] && $end["month"] == $start["month"] && $end["day"] == $start["day"] && $end["hour"] == $start["hour"] && $end["minute"] < $start["minute"]) ||
			($end["year"] == $start["year"] && $end["month"] == $start["month"] && $end["day"] == $start["day"] && $end["hour"] == $start["hour"] && $end["minute"] == $start["minute"] && $end["second"] < $start["second"])
		) {
			$end = $start;
			if ($end["hour"] < 23) $end["hour"]++;
		} // DTEND muss <= DTSTART

		if ($start["hour"] == 0 && $start["minute"] == 0 && $end["hour"] == 23 && $end["minute"] == 59) {
			$allday = true;
		}

		if ($allday) {
			$vevent->setDtstart($start["year"], $start["month"], $start["day"], FALSE, FALSE, FALSE, FALSE, array("VALUE"=> "DATE"));
			$end = mktime(0, 0, 0, $end["month"], $end["day"], $end["year"]) + 3600 * 24;

			// If a DST change occurs on the current day
			$end += date("Z", ($end - 3600*24)) - date("Z", $end);

			$vevent->setDtend(date("Y", $end), date("m", $end), date("d", $end), FALSE, FALSE, FALSE, FALSE, array("VALUE"=> "DATE"));
		} else {
			$vevent->setDtstart($start["year"], $start["month"], $start["day"], $start["hour"], $start["minute"], $start["second"], FALSE, array("VALUE"=> "DATE-TIME"));
			$vevent->setDtend($end["year"], $end["month"], $end["day"], $end["hour"], $end["minute"], $end["second"], FALSE, array("VALUE"=> "DATE-TIME"));
		}

		if ($subject != "") {
			$vevent->setProperty('LOCATION', $location);
			$vevent->setProperty('summary', $subject);
			$vevent->setProperty('description', $description);
		}
		if (!is_null($color) && $color >= 0) $vevent->setProperty("X-ANIMEXX-COLOR", $color);

		if (!$notification || $notification_type != null) {
			$vevent->deleteComponent("VALARM");

			if ($notification) {
				$valarm = new valarm();

				$valarm->setTrigger(
					($notification_type == "year" ? $notification_value : 0),
					($notification_type == "month" ? $notification_value : 0),
					($notification_type == "day" ? $notification_value : 0),
					($notification_type == "week" ? $notification_value : 0),
					($notification_type == "hour" ? $notification_value : 0),
					($notification_type == "minute" ? $notification_value : 0),
					($notification_type == "minute" ? $notification_value : 0),
					true,
					($notification_value > 0)
				);
				$valarm->setProperty("ACTION", "DISPLAY");
				$valarm->setProperty("DESCRIPTION", $subject);

				$vevent->setComponent($valarm);
			}
		}


		$v->deleteComponent("vevent");
		$v->setComponent($vevent, trim($vevent->getProperty("uid")));
		$ical = $v->createCalendar();

		$calendarBackend->updateCalendarObject($this->getNamespace() . "-" . $this->namespace_id, $old_obj->ical_uri, $ical);
	}

	/**
	 * @param array $start
	 * @param array $end
	 * @param string $subject
	 * @param bool $allday
	 * @param string $description
	 * @param string $location
	 * @param null $color
	 * @param string $timezone
	 * @param bool $notification
	 * @param null $notification_type
	 * @param null $notification_value
	 * @return array|string
	 */
	public function addItem($start, $end, $subject, $allday = false, $description = "", $location = "", $color = null,
							$timezone = "", $notification = true, $notification_type = null, $notification_value = null)
	{
		$a = get_app();

		$v = new vcalendar();
		$v->setConfig('unique_id', $a->get_hostname());

		$v->setProperty('method', 'PUBLISH');
		$v->setProperty("x-wr-calname", "AnimexxCal");
		$v->setProperty("X-WR-CALDESC", "Animexx Calendar");
		$v->setProperty("X-WR-TIMEZONE", $a->timezone);

		$vevent = dav_create_vevent($start, $end, $allday);
		$vevent->setLocation(icalendar_sanitize_string($location));
		$vevent->setSummary(icalendar_sanitize_string($subject));
		$vevent->setDescription(icalendar_sanitize_string($description));

		if (!is_null($color) && $color >= 0) $vevent->setProperty("X-ANIMEXX-COLOR", $color);

		if ($notification && $notification_type == null) {
			if ($allday) {
				$notification_type  = "hour";
				$notification_value = 24;
			} else {
				$notification_type  = "minute";
				$notification_value = 60;
			}
		}
		if ($notification) {
			$valarm = new valarm();

			$valarm->setTrigger(
				($notification_type == "year" ? $notification_value : 0),
				($notification_type == "month" ? $notification_value : 0),
				($notification_type == "day" ? $notification_value : 0),
				($notification_type == "week" ? $notification_value : 0),
				($notification_type == "hour" ? $notification_value : 0),
				($notification_type == "minute" ? $notification_value : 0),
				($notification_type == "second" ? $notification_value : 0),
				true,
				($notification_value > 0)
			);
			$valarm->setAction("DISPLAY");
			$valarm->setDescription($subject);

			$vevent->setComponent($valarm);

		}

		$v->setComponent($vevent);
		$ical   = $v->createCalendar();
		$obj_id = trim($vevent->getProperty("UID"));

		$calendarBackend = new Sabre_CalDAV_Backend_Std();
		$calendarBackend->createCalendarObject($this->getNamespace() . "-" . $this->namespace_id, $obj_id . ".ics", $ical);

		return $obj_id . ".ics";
	}

	private function jqcal2wdcal($row, $usr_id, $base_path) {
		$evo             = new DBClass_friendica_jqcalendar($row);
		$not             = q("SELECT COUNT(*) num FROM %s%snotifications WHERE `ical_uri` = '%s' AND `ical_recurr_uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($row["ical_uri"]), $row["ical_recurr_uri"]
		);
		$editable        = $this->getPermissionsItem($usr_id, $row["ical_uri"], $row["ical_recurr_uri"], $row);
		$recurring       = (is_null($evo->RecurringRule) || $evo->RecurringRule == "" || $evo->RecurringRule == "NULL" ? 0 : 1);

		$end = wdcal_mySql2PhpTime($evo->EndTime);
		if ($evo->IsAllDayEvent) $end -= 1;

		$arr             = array(
			"uri"               => $evo->ical_uri,
			"subject"           => escape_tags($evo->Subject),
			"start"             => wdcal_mySql2PhpTime($evo->StartTime),
			"end"               => $end,
			"is_allday"         => $evo->IsAllDayEvent,
			"is_moredays"       => 0,
			"is_recurring"      => $recurring,
			"color"             => (is_null($evo->Color) || $evo->Color == "" ? $this->calendarDb->calendarcolor : $evo->Color),
			"is_editable"       => ($editable ? 1 : 0),
			"is_editable_quick" => ($editable && !$recurring ? 1 : 0),
			"location"          => $evo->Location,
			"attendees"         => '',
			"has_notification"  => ($not[0]["num"] > 0 ? 1 : 0),
			"url_detail"        => $base_path . $evo->ical_uri . "/",
			"url_edit"          => $base_path . $evo->ical_uri . "/edit/",
			"special_type"      => "",
		);
		return $arr;
	}

	/**
	 * @param string $sd
	 * @param string $ed
	 * @param string $base_path
	 * @return array
	 */
	public function listItemsByRange($sd, $ed, $base_path)
	{

		$usr_id = IntVal($this->calendarDb->uid);

		$von           = wdcal_php2MySqlTime($sd);
		$bis           = wdcal_php2MySqlTime($ed);

		// @TODO Events, die früher angefangen haben, aber noch andauern
		$evs = q("SELECT * FROM %s%sjqcalendar WHERE `uid` = %d AND `namespace` = %d AND `namespace_id` = %d AND `starttime` between '%s' and '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX,
			$usr_id, $this->getNamespace(), $this->namespace_id, dbesc($von), dbesc($bis));

		$events = array();
		foreach ($evs as $row) $events[] = $this->jqcal2wdcal($row, $usr_id, $base_path);

		return $events;
	}

	/**
	 * @param string $uri
	 * @throws Sabre_DAV_Exception_NotFound
	 * @return array
	 */
	public function getItemByUri($uri)
	{
		$usr_id = IntVal($this->calendarDb->uid);
		$evs = q("SELECT * FROM %s%sjqcalendar WHERE `uid` = %d AND `namespace` = %d AND `namespace_id` = %d AND `ical_uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX,
			$usr_id, $this->getNamespace(), $this->namespace_id, dbesc($uri));
		if (count($evs) == 0) throw new Sabre_DAV_Exception_NotFound();
		return $this->jqcal2wdcal($evs[0], $usr_id);
	}


	/**
	 * @param string $uri
	 * @return string
	 */
	public function getItemDetailRedirect($uri) {
		return "/dav/wdcal/$uri/edit/";
	}
}
