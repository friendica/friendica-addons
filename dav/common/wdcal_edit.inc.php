<?php

/**
 * @param wdcal_local $localization
 * @param string $baseurl
 * @param int $uid
 * @param int $calendar_id
 * @param int $uri
 * @param string $recurr_uri
 * @return string
 */
function wdcal_getEditPage_str(&$localization, $baseurl, $uid, $calendar_id, $uri, $recurr_uri = "")
{
	$server = dav_create_server(true, true, false);

	if ($uri > 0) {
		$calendar = dav_get_current_user_calendar_by_id($server, $calendar_id, DAV_ACL_WRITE);
		if (!$calendar) {
			$calendar  = dav_get_current_user_calendar_by_id($server, $calendar_id, DAV_ACL_READ);
			$calendars = array();
		} else {
			$calendars = dav_get_current_user_calendars($server, DAV_ACL_WRITE);
		}

		if ($calendar == null) return "Calendar not found";

		$obj_uri = Sabre_CalDAV_Backend_Common::loadCalendarobjectById($uri);

		$vObject   = dav_get_current_user_calendarobject($server, $calendar, $obj_uri["uri"], DAV_ACL_WRITE);
		$component = dav_get_eventComponent($vObject);

		if ($component == null) return t('Could not open component for editing');

		/** @var Sabre_VObject_Property_DateTime $dtstart  */
		$dtstart = $component->__get("DTSTART");
		$event   = array(
			"id"            => IntVal($uri),
			"Summary"       => ($component->__get("SUMMARY") ? $component->__get("SUMMARY")->value : null),
			"StartTime"     => $dtstart->getDateTime()->getTimeStamp(),
			"EndTime"       => Sabre_CalDAV_Backend_Common::getDtEndTimeStamp($component),
			"IsAllDayEvent" => (strlen($dtstart->value) == 8),
			"Description"   => ($component->__get("DESCRIPTION") ? $component->__get("DESCRIPTION")->value : null),
			"Location"      => ($component->__get("LOCATION") ? $component->__get("LOCATION")->value : null),
			"Color"         => ($component->__get("X-ANIMEXX-COLOR") ? $component->__get("X-ANIMEXX-COLOR")->value : null),
		);

		$exdates             = $component->select("EXDATE");
		$recurrentce_exdates = array();
		/** @var Sabre_VObject_Property_MultiDateTime $x */
		foreach ($exdates as $x) {
			/** @var DateTime $y */
			$z = $x->getDateTimes();
			foreach ($z as $y) $recurrentce_exdates[] = $y->getTimeStamp();
		}

		if ($component->select("RRULE")) $recurrence = new Sabre_VObject_RecurrenceIterator($vObject, (string)$component->__get("UID"));
		else $recurrence = null;

	} elseif (isset($_REQUEST["start"]) && $_REQUEST["start"] > 0) {
		$calendars = dav_get_current_user_calendars($server, DAV_ACL_WRITE);
		$calendar  = dav_get_current_user_calendar_by_id($server, $calendar_id, DAV_ACL_WRITE);

		$event = array(
			"id"            => 0,
			"Summary"       => $_REQUEST["title"],
			"StartTime"     => InTVal($_REQUEST["start"]),
			"EndTime"       => IntVal($_REQUEST["end"]),
			"IsAllDayEvent" => $_REQUEST["isallday"],
			"Description"   => "",
			"Location"      => "",
			"Color"         => null,
		);
		if ($_REQUEST["isallday"]) {
			$notifications = array(array("rel" => "start", "type" => "duration", "period" => "hour", "period_val" => 24));
		} else {
			$notifications = array(array("rel" => "start", "type" => "duration", "period" => "hour", "period_val" => 1));
		}
		$recurrence          = null;
		$recurrentce_exdates = array();
	} else {
		$calendars = dav_get_current_user_calendars($server, DAV_ACL_WRITE);
		$calendar  = dav_get_current_user_calendar_by_id($server, $calendar_id, DAV_ACL_WRITE);

		$event               = array(
			"id"            => 0,
			"Summary"       => "",
			"StartTime"     => time(),
			"EndTime"       => time() + 3600,
			"IsAllDayEvent" => "0",
			"Description"   => "",
			"Location"      => "",
			"Color"         => null,
		);
		$notifications       = array(array("rel" => "start", "type" => "duration", "period" => "hour", "period_val" => 1));
		$recurrence          = null;
		$recurrentce_exdates = array();
	}

	$postto = $baseurl . "/dav/wdcal/" . ($uri == 0 ? "new/" : $calendar_id . "/" . $uri . "/edit/");

	$out = "<a href='" . $baseurl . "/dav/wdcal/'>" . t("Go back to the calendar") . "</a><br><br>";
	$out .= "<form method='POST' action='$postto'>
		<input type='hidden' name='form_security_token' value='" . get_form_security_token('caledit') . "'>\n";

	$out .= "<h2>" . t("Event data") . "</h2>";

	$out .= "<label for='calendar'>" . t("Calendar") . ":</label><select name='calendar' size='1'>";
	$found   = false;
	$cal_col = "aaaaaa";
	foreach ($calendars as $cal) {
		$prop = $cal->getProperties(array("id", DAV_DISPLAYNAME, DAV_CALENDARCOLOR));
		$out .= "<option value='" . $prop["id"] . "' ";
		if ($prop["id"] == $calendar_id) {
			$out .= "selected";
			$cal_col = $prop[DAV_CALENDARCOLOR];
			$found   = true;
		} elseif (!$found) $cal_col = $prop[DAV_CALENDARCOLOR];
		$out .= ">" . escape_tags($prop[DAV_DISPLAYNAME]) . "</option>\n";
	}

	$out .= "</select>";
	$out .= "&nbsp; &nbsp; <label class='plain'><input type='checkbox' name='color_override' id='color_override' ";
	if (!is_null($event["Color"])) $out .= "checked";
	$out .= "> " . t("Special color") . ":</label>";
	$out .= "<span id='cal_color_holder' ";
	if (is_null($event["Color"])) $out .= "style='display: none;'";
	$out .= "><input name='color' id='cal_color' value='" . (is_null($event["Color"]) ? "#" . $cal_col : escape_tags($event["Color"])) . "'></span>";
	$out .= "<br>\n";

	$out .= "<label class='block' for='cal_summary'>" . t("Subject") . ":</label>
		<input name='summary' id='cal_summary' value=\"" . escape_tags($event["Summary"]) . "\"><br>\n";
	$out .= "<label class='block' for='cal_allday'>Is All-Day event:</label><input type='checkbox' name='allday' id='cal_allday' " . ($event["IsAllDayEvent"] ? "checked" : "") . "><br>\n";

	$out .= "<label class='block' for='cal_startdate'>" . t("Starts") . ":</label>";
	$out .= "<input name='start_date' value='" . $localization->dateformat_datepicker_php($event["StartTime"]) . "' id='cal_start_date'>";
	$out .= "<input name='start_time' value='" . date("H:i", $event["StartTime"]) . "' id='cal_start_time'>";
	$out .= "<br>\n";

	$out .= "<label class='block' for='cal_enddate'>" . t("Ends") . ":</label>";
	$out .= "<input name='end_date' value='" . $localization->dateformat_datepicker_php($event["EndTime"]) . "' id='cal_end_date'>";
	$out .= "<input name='end_time' value='" . date("H:i", $event["EndTime"]) . "' id='cal_end_time'>";
	$out .= "<br>\n";

	$out .= "<label class='block' for='cal_location'>" . t("Location") . ":</label><input name='location' id='cal_location' value=\"" . escape_tags($event["Location"]) . "\"><br>\n";

	$out .= "<label class='block' for='event-desc-textarea'>" . t("Description") . ":</label> <textarea id='event-desc-textarea' name='wdcal_desc' style='vertical-align: top; width: 400px; height: 100px;'>" . escape_tags($event["Description"]) . "</textarea>";
	$out .= "<br style='clear: both;'>";

	$out .= "<h2>" . t("Recurrence") . "</h2>";

	$out .= "<label class='block' for='rec_frequency'>" . t("Frequency") . ":</label> <select id='rec_frequency' name='rec_frequency' size='1'>";
	$out .= "<option value=''>" . t("None") . "</option>\n";
	$out .= "<option value='daily' ";
	if ($recurrence && $recurrence->frequency == "daily") $out .= "selected";
	$out .= ">" . t("Daily") . "</option>\n";
	$out .= "<option value='weekly' ";
	if ($recurrence && $recurrence->frequency == "weekly") $out .= "selected";
	$out .= ">" . t("Weekly") . "</option>\n";
	$out .= "<option value='monthly' ";
	if ($recurrence && $recurrence->frequency == "monthly") $out .= "selected";
	$out .= ">" . t("Monthly") . "</option>\n";
	$out .= "<option value='yearly' ";
	if ($recurrence && $recurrence->frequency == "yearly") $out .= "selected";
	$out .= ">" . t("Yearly") . "</option>\n";
	$out .= "</select><br>\n";
	$out .= "<div id='rec_details'>";

	$select = "<select id='rec_interval' name='rec_interval' size='1'>";
	for ($i = 1; $i < 50; $i++) {
		$select .= "<option value='$i' ";
		if ($recurrence && $i == $recurrence->interval) $select .= "selected";
		$select .= ">$i</option>\n";
	}
	$select .= "</select>";
	$time = "<span class='rec_daily'>" . t("days") . "</span>";
	$time .= "<span class='rec_weekly'>" . t("weeks") . "</span>";
	$time .= "<span class='rec_monthly'>" . t("months") . "</span>";
	$time .= "<span class='rec_yearly'>" . t("years") . "</span>";
	$out .= "<label class='block' for='rev_interval'>" . t("Interval") . ":</label> " . str_replace(array("%select%", "%time%"), array($select, $time), t("All %select% %time%")) . "<br>";


	$out .= "<div class='rec_daily'>";
	$out .= "<label class='block'>" . t("Days") . ":</label>";
	if ($recurrence && $recurrence->byDay) {
		$byday = $recurrence->byDay;
	} else {
		$byday = array("MO", "TU", "WE", "TH", "FR", "SA", "SU");
	}
	if ($localization->getFirstDayOfWeek() == 0) {
		$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='SU' ";
		if (in_array("SU", $byday)) $out .= "checked";
		$out .= ">" . t("Sunday") . "</label> &nbsp; ";
	}
	$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='MO' ";
	if (in_array("MO", $byday)) $out .= "checked";
	$out .= ">" . t("Monday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='TU' ";
	if (in_array("TU", $byday)) $out .= "checked";
	$out .= ">" . t("Tuesday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='WE' ";
	if (in_array("WE", $byday)) $out .= "checked";
	$out .= ">" . t("Wednesday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='TH' ";
	if (in_array("TH", $byday)) $out .= "checked";
	$out .= ">" . t("Thursday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='FR' ";
	if (in_array("FR", $byday)) $out .= "checked";
	$out .= ">" . t("Friday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='SA' ";
	if (in_array("SA", $byday)) $out .= "checked";
	$out .= ">" . t("Saturday") . "</label> &nbsp; ";
	if ($localization->getFirstDayOfWeek() != 0) {
		$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='SU' ";
		if (in_array("SU", $byday)) $out .= "checked";
		$out .= ">" . t("Sunday") . "</label> &nbsp; ";
	}
	$out .= "</div>";


	$out .= "<div class='rec_weekly'>";
	$out .= "<label class='block'>" . t("Days") . ":</label>";
	if ($recurrence && $recurrence->byDay) {
		$byday = $recurrence->byDay;
	} else {
		$byday = array("MO", "TU", "WE", "TH", "FR", "SA", "SU");
	}
	if ($localization->getFirstDayOfWeek() == 0) {
		$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='SU' ";
		if (in_array("SU", $byday)) $out .= "checked";
		$out .= ">" . t("Sunday") . "</label> &nbsp; ";
	}
	$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='MO' ";
	if (in_array("MO", $byday)) $out .= "checked";
	$out .= ">" . t("Monday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='TU' ";
	if (in_array("TU", $byday)) $out .= "checked";
	$out .= ">" . t("Tuesday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='WE' ";
	if (in_array("WE", $byday)) $out .= "checked";
	$out .= ">" . t("Wednesday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='TH' ";
	if (in_array("TH", $byday)) $out .= "checked";
	$out .= ">" . t("Thursday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='FR' ";
	if (in_array("FR", $byday)) $out .= "checked";
	$out .= ">" . t("Friday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='SA' ";
	if (in_array("SA", $byday)) $out .= "checked";
	$out .= ">" . t("Saturday") . "</label> &nbsp; ";
	if ($localization->getFirstDayOfWeek() != 0) {
		$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='SU' ";
		if (in_array("SU", $byday)) $out .= "checked";
		$out .= ">" . t("Sunday") . "</label> &nbsp; ";
	}
	$out .= "<br>";

	$out .= "<label class='block'>" . t("First day of week:") . "</label>";
	if ($recurrence && $recurrence->weekStart != "") $wkst = $recurrence->weekStart;
	else {
		if ($localization->getFirstDayOfWeek() == 0) $wkst = "SU";
		else $wkst = "MO";
	}
	$out .= "<label class='plain'><input type='radio' name='rec_weekly_wkst' value='SU' ";
	if ($wkst == "SU") $out .= "checked";
	$out .= ">" . t("Sunday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input type='radio' name='rec_weekly_wkst' value='MO' ";
	if ($wkst == "MO") $out .= "checked";
	$out .= ">" . t("Monday") . "</label><br>\n";

	$out .= "</div>";

	$monthly_rule = "bymonthday"; // @TODO
	$out .= "<div class='rec_monthly'>";
	$out .= "<label class='block' name='rec_monthly_day'>" . t("Day of month") . ":</label>";
	$out .= "<select id='rec_monthly_day' name='rec_monthly_day' size='1'>";
	$out .= "<option value='bymonthday' ";
	if ($monthly_rule == "bymonthday") $out .= "selected";
	$out .= ">" . t("#num#th of each month") . "</option>\n";
	$out .= "<option value='bymonthday_neg' ";
	if ($monthly_rule == "bymonthday_neg") $out .= "selected";
	$out .= ">" . t("#num#th-last of each month") . "</option>\n";
	$out .= "<option value='byday' ";
	if ($monthly_rule == "byday") $out .= "selected";
	$out .= ">" . t("#num#th #wkday# of each month") . "</option>\n";
	$out .= "<option value='byday_neg' ";
	if ($monthly_rule == "byday_neg") $out .= "selected";
	$out .= ">" . t("#num#th-last #wkday# of each month") . "</option>\n";
	$out .= "</select>";
	$out .= "</div>\n";


	$out .= "<div class='rec_yearly'>";
	$out .= "<label class='block' name='rec_yearly_day'>" . t("Month") . ":</label> <span class='rec_month_name'>#month#</span><br>\n";
	$out .= "<label class='block' name='rec_yearly_day'>" . t("Day of month") . ":</label>";
	$out .= "<select id='rec_yearly_day' name='rec_yearly_day' size='1'>";
	$out .= "<option value='bymonthday' ";
	if ($monthly_rule == "bymonthday") $out .= "selected";
	$out .= ">" . t("#num#th of each month") . "</option>\n";
	$out .= "<option value='bymonthday_neg' ";
	if ($monthly_rule == "bymonthday_neg") $out .= "selected";
	$out .= ">" . t("#num#th-last of each month") . "</option>\n";
	$out .= "<option value='byday' ";
	if ($monthly_rule == "byday") $out .= "selected";
	$out .= ">" . t("#num#th #wkday# of each month") . "</option>\n";
	$out .= "<option value='byday_neg' ";
	if ($monthly_rule == "byday_neg") $out .= "selected";
	$out .= ">" . t("#num#th-last #wkday# of each month") . "</option>\n";
	$out .= "</select>";
	$out .= "</div>\n";


	if ($recurrence) {
		$until = $recurrence->until;
		$count = $recurrence->count;
		if (is_a($until, "DateTime")) {
			/** @var DateTime $until */
			$rule_type        = "date";
			$rule_until_date  = $until->getTimestamp();
			$rule_until_count = 1;
		} elseif ($count > 0) {
			$rule_type        = "count";
			$rule_until_date  = time();
			$rule_until_count = $count;
		} else {
			$rule_type        = "infinite";
			$rule_until_date  = time();
			$rule_until_count = 1;
		}
	} else {
		$rule_type        = "infinite";
		$rule_until_date  = time();
		$rule_until_count = 1;
	}
	$out .= "<label class='block' for='rec_until_type'>" . t("Repeat until") . ":</label> ";
	$out .= "<select name='rec_until_type' id='rec_until_type' size='1'>";
	$out .= "<option value='infinite' ";
	if ($rule_type == "infinite") $out .= "selected";
	$out .= ">" . t("Infinite") . "</option>\n";
	$out .= "<option value='date' ";
	if ($rule_type == "date") $out .= "selected";
	$out .= ">" . t("Until the following date") . ":</option>\n";
	$out .= "<option value='count' ";
	if ($rule_type == "count") $out .= "selected";
	$out .= ">" . t("Number of times") . ":</option>\n";
	$out .= "</select>";

	$out .= "<input name='rec_until_date' value='" . $localization->dateformat_datepicker_php($rule_until_date) . "' id='rec_until_date'>";
	$out .= "<input name='rec_until_count' value='$rule_until_count' id='rec_until_count'><br>";

	$out .= "<label class='block'>" . t("Exceptions") . ":</label><div class='rec_exceptions'>";
	$out .= "<div class='rec_exceptions_none' ";
	if (count($recurrentce_exdates) > 0) $out .= "style='display: none;'";
	$out .= ">" . t("none") . "</div>";
	$out .= "<div class='rec_exceptions_holder' ";
	if (count($recurrentce_exdates) == 0) $out .= "style='display: none;'";
	$out .= ">";

	foreach ($recurrentce_exdates as $exdate) {
		$out .= "<div data-timestamp='$exdate' class='except'><input type='hidden' class='rec_exception' name='rec_exceptions[]' value='$exdate'>";
		$out .= "<a href='#' class='exception_remover'>[remove]</a> ";
		$out .= $localization->date_timestamp2localDate($exdate);
		$out .= "</div>\n";
	}
	$out .= "</div><div><a href='#' class='exception_adder'>[add]</a></div>";
	$out .= "</div>\n";
	$out .= "<br>\n";

	$out .= "</div><br>";

	$out .= "<h2>" . t("Notification") . "</h2>";

	/*
	$out .= '<input type="checkbox" name="notification" id="notification" ';
	if ($notification) $out .= "checked";
	$out .= '> ';
	$out .= '<span id="notification_detail" style="display: none;">
			<input name="notification_value" value="' . $notification_value . '" size="3">
			<select name="notification_type" size="1">
				<option value="minute" ';
	if ($notification_type == "minute") $out .= "selected";
	$out .= '> ' . t('Minutes') . '</option>
				<option value="hour" ';
	if ($notification_type == "hour") $out .= "selected";
	$out .= '> ' . t('Hours') . '</option>
				<option value="day" ';
	if ($notification_type == "day") echo "selected";
	$out .= '> ' . t('Days') . '</option>
			</select> ' . t('before') . '
		</span><br><br>';
	*/

	$out .= "<script>\$(function() {
		wdcal_edit_init('" . $localization->dateformat_datepicker_js() . "', '${baseurl}/dav/');
	});</script>";

	$out .= "<input type='submit' name='save' value='Save'></form>";

	return $out;
}


/**
 * @param Sabre_VObject_Component_VEvent $component
 * @param wdcal_local $localization
 */
function wdcal_set_component_date(&$component, &$localization)
{
	if (isset($_REQUEST["allday"])) {
		$ts_start = $localization->date_local2timestamp($_REQUEST["start_date"] . " 00:00");
		$ts_end   = $localization->date_local2timestamp($_REQUEST["end_date"] . " 00:00");
		$type     = Sabre_VObject_Property_DateTime::DATE;
	} else {
		$ts_start = $localization->date_local2timestamp($_REQUEST["start_date"] . " " . $_REQUEST["start_time"]);
		$ts_end   = $localization->date_local2timestamp($_REQUEST["end_date"] . " " . $_REQUEST["end_time"]);
		$type     = Sabre_VObject_Property_DateTime::LOCALTZ;
	}
	$datetime_start = new Sabre_VObject_Property_DateTime("DTSTART");
	$datetime_start->setDateTime(new DateTime(date("Y-m-d H:i:s", $ts_start)), $type);
	$datetime_end = new Sabre_VObject_Property_DateTime("DTEND");
	$datetime_end->setDateTime(new DateTime(date("Y-m-d H:i:s", $ts_end)), $type);

	$component->__unset("DTSTART");
	$component->__unset("DTEND");
	$component->add($datetime_start);
	$component->add($datetime_end);
}

/**
 * @param Sabre_VObject_Component_VEvent $component
 * @param wdcal_local $localization
 */
function wdcal_set_component_recurrence(&$component, &$localization)
{
	$component->__unset("RRULE");
	$component->__unset("EXRULE");
	$component->__unset("EXDATE");
	$component->__unset("RDATE");

	$part_until = "";
	switch ($_REQUEST["rec_until_type"]) {
		case "date":
			$date           = $localization->date_local2timestamp($_REQUEST["rec_until_date"]);
			$part_until     = ";UNTIL=" . date("Ymd", $date);
			$datetime_until = new Sabre_VObject_Property_DateTime("UNTIL");
			$datetime_until->setDateTime(new DateTime(date("Y-m-d H:i:s", $date)), Sabre_VObject_Property_DateTime::DATE);
			break;
		case "count":
			$part_until = ";COUNT=" . IntVal($_REQUEST["rec_until_count"]);
			break;
	}

	switch ($_REQUEST["rec_frequency"]) {
		case "daily":
			$part_freq = "FREQ=DAILY";
			if (isset($_REQUEST["rec_daily_byday"])) {
				$days = array();
				foreach ($_REQUEST["rec_daily_byday"] as $x) if (in_array($x, array("MO", "TU", "WE", "TH", "FR", "SA", "SU"))) $days[] = $x;
				if (count($days) > 0) $part_freq .= ";BYDAY=" . implode(",", $days);
			}
			break;
		case "weekly":
			$part_freq = "FREQ=WEEKLY";
			if (isset($_REQUEST["rec_weekly_wkst"]) && in_array($_REQUEST["rec_weekly_wkst"], array("MO", "SU"))) $part_freq .= ";WKST=" . $_REQUEST["rec_weekly_wkst"];
			if (isset($_REQUEST["rec_weekly_byday"])) {
				$days = array();
				foreach ($_REQUEST["rec_weekly_byday"] as $x) if (in_array($x, array("MO", "TU", "WE", "TH", "FR", "SA", "SU"))) $days[] = $x;
				if (count($days) > 0) $part_freq .= ";BYDAY=" . implode(",", $days);
			}
			break;
		case "monthly":
			$part_freq = "FREQ=MONTHLY";
			break;
		case "FREQ=yearly":
			$part_freq = "FREQ=YEARLY";
			break;
		default:
			$part_freq = "";
	}

	if ($part_freq == "") return;

	if (isset($_REQUEST["rec_interval"])) $part_freq .= ";INTERVAL=" . InTVal($_REQUEST["rec_interval"]);

	if (isset($_REQUEST["rec_exceptions"])) {
		$arr = array();
		foreach ($_REQUEST["rec_exceptions"] as $except) {
			$arr[] = new DateTime(date("Y-m-d H:i:s", $except));
		}
		/** @var Sabre_VObject_Property_MultiDateTime $prop */
		$prop = Sabre_VObject_Property::create("EXDATE");
		$prop->setDateTimes($arr);
		$component->add($prop);
	}

	$rrule = $part_freq . $part_until;
	$component->add(new Sabre_VObject_Property("RRULE", $rrule));

}


/**
 * @param string $uri
 * @param string $recurr_uri
 * @param int $uid
 * @param string $timezone
 * @param string $goaway_url
 * @return array
 */
function wdcal_postEditPage($uri, $recurr_uri = "", $uid = 0, $timezone = "", $goaway_url = "")
{
	$uid          = IntVal($uid);
	$localization = wdcal_local::getInstanceByUser($uid);

	$server = dav_create_server(true, true, false);

	if ($uri > 0) {
		$calendar = dav_get_current_user_calendar_by_id($server, $_REQUEST["calendar"], DAV_ACL_READ);
		$obj_uri  = Sabre_CalDAV_Backend_Common::loadCalendarobjectById($uri);
		$obj_uri  = $obj_uri["uri"];

		$vObject   = dav_get_current_user_calendarobject($server, $calendar, $obj_uri, DAV_ACL_WRITE);
		$component = dav_get_eventComponent($vObject);

		if ($component == null) return array("ok" => false, "msg" => t('Could not open component for editing'));
	} else {
		$calendar  = dav_get_current_user_calendar_by_id($server, $_REQUEST["calendar"], DAV_ACL_WRITE);
		$vObject   = dav_create_empty_vevent();
		$component = dav_get_eventComponent($vObject);
		$obj_uri   = $component->__get("UID");
	}

	wdcal_set_component_date($component, $localization);
	wdcal_set_component_recurrence($component, $localization);

	$component->__unset("LOCATION");
	$component->__unset("SUMMARY");
	$component->__unset("DESCRIPTION");
	$component->__unset("X-ANIMEXX-COLOR");
	$component->add("SUMMARY", icalendar_sanitize_string(dav_compat_parse_text_serverside("summary")));
	$component->add("LOCATION", icalendar_sanitize_string(dav_compat_parse_text_serverside("location")));
	$component->add("DESCRIPTION", icalendar_sanitize_string(dav_compat_parse_text_serverside("wdcal_desc")));
	if (isset($_REQUEST["color_override"])) {
		$component->add("X-ANIMEXX-COLOR", $_REQUEST["color"]);
	}

	$data = $vObject->serialize();

	if ($uri == 0) {
		$calendar->createFile($obj_uri . ".ics", $data);
	} else {
		$obj = $calendar->getChild($obj_uri);
		$obj->put($data);
	}
	return array("ok" => false, "msg" => t("Saved"));
}


/**
 * @return string
 */
function wdcal_getEditPage_exception_selector()
{
	header("Content-type: application/json");

	$a            = get_app();
	$localization = wdcal_local::getInstanceByUser($a->user["uid"]);

	$vObject = dav_create_empty_vevent();

	foreach ($vObject->getComponents() as $component) {
		if ($component->name !== 'VTIMEZONE') break;
	}
	/** @var Sabre_VObject_Component_VEvent $component */
	wdcal_set_component_date($component, $localization);
	wdcal_set_component_recurrence($component, $localization);


	$it         = new Sabre_VObject_RecurrenceIterator($vObject, (string)$component->__get("UID"));
	$max_ts     = mktime(0, 0, 0, 1, 1, CALDAV_MAX_YEAR + 1);
	$last_start = 0;

	$o = "<ul>";

	$i = 0;
	while ($it->valid() && $last_start < $max_ts && $i++ < 1000) {
		$last_start = $it->getDtStart()->getTimestamp();
		$o .= "<li><a href='#' class='exception_selector_link' data-timestamp='$last_start'>" . $localization->date_timestamp2localDate($last_start) . "</a></li>\n";
		$it->next();
	}
	$o .= "</ul>\n";

	return $o;
}