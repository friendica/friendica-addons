<?php

use Friendica\Core\L10n;
use Friendica\Util\DateTimeFormat;

/**
 * @param wdcal_local $localization
 * @param string $baseurl
 * @param int $calendar_id
 * @param int $uri
 * @return string
 */
function wdcal_getEditPage_str(&$localization, $baseurl, $calendar_id, $uri)
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

		if ($component == null) return L10n::t('Could not open component for editing');

		/** @var Sabre\VObject\Property\DateTime $dtstart  */
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
		/** @var Sabre\VObject\Property\MultiDateTime $x */
		foreach ($exdates as $x) {
			/** @var DateTime $y */
			$z = $x->getDateTimes();
			foreach ($z as $y) $recurrentce_exdates[] = $y->getTimeStamp();
		}

		$notifications = array();
		$alarms = $component->select("VALARM");
		foreach ($alarms as $alarm)  {
			/** @var Sabre_VObject_Component_VAlarm $alarm */
			$action = $alarm->__get("ACTION")->value;
			$trigger = $alarm->__get("TRIGGER");

			if(isset($trigger['VALUE']) && strtoupper($trigger['VALUE']) !== 'DURATION') {
				notice("The notification of this event cannot be parsed");
				continue;
			}

			/** @var DateInterval $triggerDuration  */
			$triggerDuration = Sabre_VObject_DateTimeParser::parseDuration($trigger);
			$unit = "hour";
			$value = 1;
			if ($triggerDuration->s > 0) {
				$unit = "second";
				$value = $triggerDuration->s + $triggerDuration->i * 60 + $triggerDuration->h * 3600 + $triggerDuration->d * 3600 * 24; // @TODO support more than days?
			} elseif ($triggerDuration->i) {
				$unit = "minute";
				$value = $triggerDuration->i + $triggerDuration->h * 60 + $triggerDuration->d * 60 * 24;
			} elseif ($triggerDuration->h) {
				$unit = "hour";
				$value = $triggerDuration->h + $triggerDuration->d * 24;
			} elseif ($triggerDuration->d > 0) {
				$unit = "day";
				$value = $triggerDuration->d;
			}

			$rel = (isset($trigger['RELATED']) && strtoupper($trigger['RELATED']) == 'END') ? 'end' : 'start';


			$notifications[] = array(
				"action" => strtolower($action),
				"rel" => $rel,
				"trigger_unit" => $unit,
				"trigger_value" => $value,
			);
		}

		if ($component->select("RRULE")) $recurrence = new Sabre_VObject_RecurrenceIterator($vObject, (string)$component->__get("UID"));
		else $recurrence = null;

	} elseif (isset($_REQUEST["start"]) && $_REQUEST["start"] > 0) {
		$calendars = dav_get_current_user_calendars($server, DAV_ACL_WRITE);
		//$calendar  = dav_get_current_user_calendar_by_id($server, $calendar_id, DAV_ACL_WRITE);

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
			$notifications = array();
		} else {
			$notifications = array(array("action" => "email", "rel" => "start", "trigger_unit" => "hour", "trigger_value" => 1));
		}
		$recurrence          = null;
		$recurrentce_exdates = array();
	} else {
		$calendars = dav_get_current_user_calendars($server, DAV_ACL_WRITE);
		//$calendar  = dav_get_current_user_calendar_by_id($server, $calendar_id, DAV_ACL_WRITE);

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
		$notifications = array(array("action" => "email", "rel" => "start", "trigger_unit" => "hour", "trigger_value" => 1));
		$recurrence          = null;
		$recurrentce_exdates = array();
	}

	$postto = $baseurl . "/dav/wdcal/" . ($uri == 0 ? "new/" : $calendar_id . "/" . $uri . "/edit/");

	$out = "<a href='" . $baseurl . "/dav/wdcal/'>" . L10n::t("Go back to the calendar") . "</a><br><br>";
	$out .= "<form method='POST' action='$postto'>
		<input type='hidden' name='form_security_token' value='" . get_form_security_token('caledit') . "'>\n";

	$out .= "<h2>" . L10n::t("Event data") . "</h2>";

	$out .= "<label for='calendar' class='block'>" . L10n::t("Calendar") . ":</label><select id='calendar' name='calendar' size='1'>";
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
	$out .= "> " . L10n::t("Special color") . ":</label>";
	$out .= "<span id='cal_color_holder' ";
	if (is_null($event["Color"])) $out .= "style='display: none;'";
	$out .= "><input name='color' id='cal_color' value='" . (is_null($event["Color"]) ? "#" . $cal_col : escape_tags($event["Color"])) . "'></span>";
	$out .= "<br>\n";

	$out .= "<label class='block' for='cal_summary'>" . L10n::t("Subject") . ":</label>
		<input name='summary' id='cal_summary' value=\"" . escape_tags($event["Summary"]) . "\"><br>\n";
	$out .= "<label class='block' for='cal_allday'>Is All-Day event:</label><input type='checkbox' name='allday' id='cal_allday' " . ($event["IsAllDayEvent"] ? "checked" : "") . "><br>\n";

	$out .= "<label class='block' for='cal_start_date'>" . L10n::t("Starts") . ":</label>";
	$out .= "<input name='start_date' value='" . $localization->dateformat_datepicker_php($event["StartTime"]) . "' id='cal_start_date'>";
	$out .= "<input name='start_time' value='" . date("H:i", $event["StartTime"]) . "' id='cal_start_time'>";
	$out .= "<br>\n";

	$out .= "<label class='block' for='cal_end_date'>" . L10n::t("Ends") . ":</label>";
	$out .= "<input name='end_date' value='" . $localization->dateformat_datepicker_php($event["EndTime"]) . "' id='cal_end_date'>";
	$out .= "<input name='end_time' value='" . date("H:i", $event["EndTime"]) . "' id='cal_end_time'>";
	$out .= "<br>\n";

	$out .= "<label class='block' for='cal_location'>" . L10n::t("Location") . ":</label><input name='location' id='cal_location' value=\"" . escape_tags($event["Location"]) . "\"><br>\n";

	$out .= "<label class='block' for='event-desc-textarea'>" . L10n::t("Description") . ":</label> <textarea id='event-desc-textarea' name='wdcal_desc' style='vertical-align: top; width: 400px; height: 100px;'>" . escape_tags($event["Description"]) . "</textarea>";
	$out .= "<br style='clear: both;'>";

	$out .= "<h2>" . L10n::t("Recurrence") . "</h2>";

	$out .= "<label class='block' for='rec_frequency'>" . L10n::t("Frequency") . ":</label> <select id='rec_frequency' name='rec_frequency' size='1'>";
	$out .= "<option value=''>" . L10n::t("None") . "</option>\n";
	$out .= "<option value='daily' ";
	if ($recurrence && $recurrence->frequency == "daily") $out .= "selected";
	$out .= ">" . L10n::t("Daily") . "</option>\n";
	$out .= "<option value='weekly' ";
	if ($recurrence && $recurrence->frequency == "weekly") $out .= "selected";
	$out .= ">" . L10n::t("Weekly") . "</option>\n";
	$out .= "<option value='monthly' ";
	if ($recurrence && $recurrence->frequency == "monthly") $out .= "selected";
	$out .= ">" . L10n::t("Monthly") . "</option>\n";
	$out .= "<option value='yearly' ";
	if ($recurrence && $recurrence->frequency == "yearly") $out .= "selected";
	$out .= ">" . L10n::t("Yearly") . "</option>\n";
	$out .= "</select><br>\n";
	$out .= "<div id='rec_details'>";

	$select = "<select id='rec_interval' name='rec_interval' size='1'>";
	for ($i = 1; $i < 50; $i++) {
		$select .= "<option value='$i' ";
		if ($recurrence && $i == $recurrence->interval) $select .= "selected";
		$select .= ">$i</option>\n";
	}
	$select .= "</select>";
	$time = "<span class='rec_daily'>" . L10n::t("days") . "</span>";
	$time .= "<span class='rec_weekly'>" . L10n::t("weeks") . "</span>";
	$time .= "<span class='rec_monthly'>" . L10n::t("months") . "</span>";
	$time .= "<span class='rec_yearly'>" . L10n::t("years") . "</span>";
	$out .= "<label class='block'>" . L10n::t("Interval") . ":</label> " . str_replace(array("%select%", "%time%"), array($select, $time), L10n::t("All %select% %time%")) . "<br>";


	$out .= "<div class='rec_daily'>";
	$out .= "<label class='block'>" . L10n::t("Days") . ":</label>";
	if ($recurrence && $recurrence->byDay) {
		$byday = $recurrence->byDay;
	} else {
		$byday = array("MO", "TU", "WE", "TH", "FR", "SA", "SU");
	}
	if ($localization->getFirstDayOfWeek() == 0) {
		$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='SU' ";
		if (in_array("SU", $byday)) $out .= "checked";
		$out .= ">" . L10n::t("Sunday") . "</label> &nbsp; ";
	}
	$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='MO' ";
	if (in_array("MO", $byday)) $out .= "checked";
	$out .= ">" . L10n::t("Monday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='TU' ";
	if (in_array("TU", $byday)) $out .= "checked";
	$out .= ">" . L10n::t("Tuesday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='WE' ";
	if (in_array("WE", $byday)) $out .= "checked";
	$out .= ">" . L10n::t("Wednesday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='TH' ";
	if (in_array("TH", $byday)) $out .= "checked";
	$out .= ">" . L10n::t("Thursday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='FR' ";
	if (in_array("FR", $byday)) $out .= "checked";
	$out .= ">" . L10n::t("Friday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='SA' ";
	if (in_array("SA", $byday)) $out .= "checked";
	$out .= ">" . L10n::t("Saturday") . "</label> &nbsp; ";
	if ($localization->getFirstDayOfWeek() != 0) {
		$out .= "<label class='plain'><input class='rec_daily_byday' type='checkbox' name='rec_daily_byday[]' value='SU' ";
		if (in_array("SU", $byday)) $out .= "checked";
		$out .= ">" . L10n::t("Sunday") . "</label> &nbsp; ";
	}
	$out .= "</div>";


	$out .= "<div class='rec_weekly'>";
	$out .= "<label class='block'>" . L10n::t("Days") . ":</label>";
	if ($recurrence && $recurrence->byDay) {
		$byday = $recurrence->byDay;
	} else {
		$days = array("MO", "TU", "WE", "TH", "FR", "SA", "SU");
		$byday = array($days[date("N", $event["StartTime"]) - 1]);
	}
	if ($localization->getFirstDayOfWeek() == 0) {
		$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='SU' ";
		if (in_array("SU", $byday)) $out .= "checked";
		$out .= ">" . L10n::t("Sunday") . "</label> &nbsp; ";
	}
	$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='MO' ";
	if (in_array("MO", $byday)) $out .= "checked";
	$out .= ">" . L10n::t("Monday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='TU' ";
	if (in_array("TU", $byday)) $out .= "checked";
	$out .= ">" . L10n::t("Tuesday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='WE' ";
	if (in_array("WE", $byday)) $out .= "checked";
	$out .= ">" . L10n::t("Wednesday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='TH' ";
	if (in_array("TH", $byday)) $out .= "checked";
	$out .= ">" . L10n::t("Thursday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='FR' ";
	if (in_array("FR", $byday)) $out .= "checked";
	$out .= ">" . L10n::t("Friday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='SA' ";
	if (in_array("SA", $byday)) $out .= "checked";
	$out .= ">" . L10n::t("Saturday") . "</label> &nbsp; ";
	if ($localization->getFirstDayOfWeek() != 0) {
		$out .= "<label class='plain'><input class='rec_weekly_byday' type='checkbox' name='rec_weekly_byday[]' value='SU' ";
		if (in_array("SU", $byday)) $out .= "checked";
		$out .= ">" . L10n::t("Sunday") . "</label> &nbsp; ";
	}
	$out .= "<br>";

	$out .= "<label class='block'>" . L10n::t("First day of week:") . "</label>";
	if ($recurrence && $recurrence->weekStart != "") $wkst = $recurrence->weekStart;
	else {
		if ($localization->getFirstDayOfWeek() == 0) $wkst = "SU";
		else $wkst = "MO";
	}
	$out .= "<label class='plain'><input type='radio' name='rec_weekly_wkst' value='SU' ";
	if ($wkst == "SU") $out .= "checked";
	$out .= ">" . L10n::t("Sunday") . "</label> &nbsp; ";
	$out .= "<label class='plain'><input type='radio' name='rec_weekly_wkst' value='MO' ";
	if ($wkst == "MO") $out .= "checked";
	$out .= ">" . L10n::t("Monday") . "</label><br>\n";

	$out .= "</div>";

	$monthly_rule = "";
	if ($recurrence && ($recurrence->frequency == "monthly" || $recurrence->frequency == "yearly")) {
		if (is_null($recurrence->byDay) && !is_null($recurrence->byMonthDay) && count($recurrence->byMonthDay) == 1) {
			$day = date("j", $event["StartTime"]);
			if ($recurrence->byMonthDay[0] == $day) $monthly_rule = "bymonthday";
			else {
				$lastday = date("t", $event["StartTime"]);
				if ($recurrence->byMonthDay[0] == -1 * ($lastday - $day + 1)) $monthly_rule = "bymonthday_neg";
			}
		}
		if (is_null($recurrence->byMonthDay) && !is_null($recurrence->byDay) && count($recurrence->byDay) == 1) {
			$num = IntVal($recurrence->byDay[0]);
			/*
			$dayMap = array(
				'SU' => 0,
				'MO' => 1,
				'TU' => 2,
				'WE' => 3,
				'TH' => 4,
				'FR' => 5,
				'SA' => 6,
			);
			if ($num == 0) {
				$num = 1;
				$weekday = $dayMap[$recurrence->byDay[0]];
			} else {
				$weekday = $dayMap[substr($recurrence->byDay[0], strlen($num))];
			}

			echo $num . " - " . $weekday;
			*/
			if ($num > 0) $monthly_rule = "byday";
			if ($num < 0) $monthly_rule = "byday_neg";
		}
		if ($monthly_rule == "") notice("The recurrence of this event cannot be parsed");
	}

	$out .= "<div class='rec_monthly'>";
	$out .= "<label class='block' for='rec_monthly_day'>" . L10n::t("Day of month") . ":</label>";
	$out .= "<select id='rec_monthly_day' name='rec_monthly_day' size='1'>";
	$out .= "<option value='bymonthday' ";
	if ($monthly_rule == "bymonthday") $out .= "selected";
	$out .= ">" . L10n::t("#num#th of each month") . "</option>\n";
	$out .= "<option value='bymonthday_neg' ";
	if ($monthly_rule == "bymonthday_neg") $out .= "selected";
	$out .= ">" . L10n::t("#num#th-last of each month") . "</option>\n";
	$out .= "<option value='byday' ";
	if ($monthly_rule == "byday") $out .= "selected";
	$out .= ">" . L10n::t("#num#th #wkday# of each month") . "</option>\n";
	$out .= "<option value='byday_neg' ";
	if ($monthly_rule == "byday_neg") $out .= "selected";
	$out .= ">" . L10n::t("#num#th-last #wkday# of each month") . "</option>\n";
	$out .= "</select>";
	$out .= "</div>\n";

	if ($recurrence && $recurrence->frequency == "yearly") {
		if (count($recurrence->byMonth) != 1 || $recurrence->byMonth[0] != date("n", $event["StartTime"])) notice("The recurrence of this event cannot be parsed!");
	}

	$out .= "<div class='rec_yearly'>";
	$out .= "<label class='block'>" . L10n::t("Month") . ":</label> <span class='rec_month_name'>#month#</span><br>\n";
	$out .= "<label class='block' for='rec_yearly_day'>" . L10n::t("Day of month") . ":</label>";
	$out .= "<select id='rec_yearly_day' name='rec_yearly_day' size='1'>";
	$out .= "<option value='bymonthday' ";
	if ($monthly_rule == "bymonthday") $out .= "selected";
	$out .= ">" . L10n::t("#num#th of the given month") . "</option>\n";
	$out .= "<option value='bymonthday_neg' ";
	if ($monthly_rule == "bymonthday_neg") $out .= "selected";
	$out .= ">" . L10n::t("#num#th-last of the given month") . "</option>\n";
	$out .= "<option value='byday' ";
	if ($monthly_rule == "byday") $out .= "selected";
	$out .= ">" . L10n::t("#num#th #wkday# of the given month") . "</option>\n";
	$out .= "<option value='byday_neg' ";
	if ($monthly_rule == "byday_neg") $out .= "selected";
	$out .= ">" . L10n::t("#num#th-last #wkday# of the given month") . "</option>\n";
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
	$out .= "<label class='block' for='rec_until_type'>" . L10n::t("Repeat until") . ":</label> ";
	$out .= "<select name='rec_until_type' id='rec_until_type' size='1'>";
	$out .= "<option value='infinite' ";
	if ($rule_type == "infinite") $out .= "selected";
	$out .= ">" . L10n::t("Infinite") . "</option>\n";
	$out .= "<option value='date' ";
	if ($rule_type == "date") $out .= "selected";
	$out .= ">" . L10n::t("Until the following date") . ":</option>\n";
	$out .= "<option value='count' ";
	if ($rule_type == "count") $out .= "selected";
	$out .= ">" . L10n::t("Number of times") . ":</option>\n";
	$out .= "</select>";

	$out .= "<input name='rec_until_date' value='" . $localization->dateformat_datepicker_php($rule_until_date) . "' id='rec_until_date'>";
	$out .= "<input name='rec_until_count' value='$rule_until_count' id='rec_until_count'><br>";

	$out .= "<label class='block'>" . L10n::t("Exceptions") . ":</label><div class='rec_exceptions'>";
	$out .= "<div class='rec_exceptions_none' ";
	if (count($recurrentce_exdates) > 0) $out .= "style='display: none;'";
	$out .= ">" . L10n::t("none") . "</div>";
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

	$out .= "<h2>" . L10n::t("Notification") . "</h2>";

	if (!$notifications) $notifications = array();
	$notifications["new"] = array(
		"action" => "email",
		"trigger_value" => 60,
		"trigger_unit" => "minute",
		"rel" => "start",
	);

	foreach ($notifications as $index => $noti) {

		$unparsable = false;
		if (!in_array($noti["action"], array("email", "display"))) $unparsable = true;

		$out .= "<div class='noti_holder' ";
		if (!is_numeric($index) && $index == "new") $out .= "style='display: none;' id='noti_new_row'";
		$out .= "><label class='block' for='noti_type_" . $index . "'>" . L10n::t("Notify by") . ":</label>";
		$out .= "<select name='noti_type[$index]' size='1' id='noti_type_" . $index . "'>";
		$out .= "<option value=''>- " . L10n::t("Remove") . " -</option>\n";
		$out .= "<option value='email' "; if (!$unparsable && $noti["action"] == "email") $out .= "selected"; $out .= ">" . L10n::t("E-Mail") . "</option>\n";
		$out .= "<option value='display' "; if (!$unparsable && $noti["action"] == "display") $out .= "selected"; $out .= ">" . L10n::t("On Friendica / Display") . "</option>\n";
		//$out .= "<option value='other' "; if ($unparsable) $out .= "selected"; $out .= ">- " . L10n::t("other (leave it untouched)") . " -</option>\n"; // @TODO
		$out .= "</select><br>";

		$out .= "<label class='block'>" . L10n::t("Time") . ":</label>";
		$out .= "<input name='noti_value[$index]' size='5' style='width: 5em;' value='" . $noti["trigger_value"] . "'>";

		$out .= "<select name='noti_unit[$index]' size='1'>";
		$out .= "<option value='H' "; if ($noti["trigger_unit"] == "hour") $out .= "selected"; $out .= ">" . L10n::t("Hours") . "</option>\n";
		$out .= "<option value='M' "; if ($noti["trigger_unit"] == "minute") $out .= "selected"; $out .= ">" . L10n::t("Minutes") . "</option>\n";
		$out .= "<option value='S' "; if ($noti["trigger_unit"] == "second") $out .= "selected"; $out .= ">" . L10n::t("Seconds") . "</option>\n";
		$out .= "<option value='D' "; if ($noti["trigger_unit"] == "day") $out .= "selected"; $out .= ">" . L10n::t("Days") . "</option>\n";
		$out .= "<option value='W' "; if ($noti["trigger_unit"] == "week") $out .= "selected"; $out .= ">" . L10n::t("Weeks") . "</option>\n";
		$out .= "</select>";

		$out .= " <label class='plain'>" . L10n::t("before the") . " <select name='noti_ref[$index]' size='1'>";
		$out .= "<option value='start' "; if ($noti["rel"] == "start") $out .= "selected"; $out .= ">" . L10n::t("start of the event") . "</option>\n";
		$out .= "<option value='end' "; if ($noti["rel"] == "end") $out .= "selected"; $out .= ">" . L10n::t("end of the event") . "</option>\n";
		$out .= "</select></label>\n";

		$out .= "</div>";
	}
	$out .= "<input type='hidden' name='new_alarm' id='new_alarm' value='0'><div id='new_alarm_adder'><a href='#'>" . L10n::t("Add a notification") . "</a></div>";

	$out .= "<script>\$(function() {
		wdcal_edit_init('" . $localization->dateformat_datepicker_js() . "', '${baseurl}/dav/');
	});</script>";

	$out .= "<br><input type='submit' name='save' value='Save'></form>";

	return $out;
}


/**
 * @param Sabre_VObject_Component_VEvent $component
 * @param wdcal_local $localization
 * @return int
 */
function wdcal_set_component_date(&$component, &$localization)
{
	if (isset($_REQUEST["allday"])) {
		$ts_start = $localization->date_local2timestamp($_REQUEST["start_date"] . " 00:00");
		$ts_end   = $localization->date_local2timestamp($_REQUEST["end_date"] . " 00:00");
		$type     = Sabre\VObject\Property\DateTime::DATE;
	} else {
		$ts_start = $localization->date_local2timestamp($_REQUEST["start_date"] . " " . $_REQUEST["start_time"]);
		$ts_end   = $localization->date_local2timestamp($_REQUEST["end_date"] . " " . $_REQUEST["end_time"]);
		$type     = Sabre\VObject\Property\DateTime::LOCALTZ;
	}
	$datetime_start = new Sabre\VObject\Property\DateTime("DTSTART");
	$datetime_start->setDateTime(new DateTime(date(DateTimeFormat::MYSQL, $ts_start)), $type);
	$datetime_end = new Sabre\VObject\Property\DateTime("DTEND");
	$datetime_end->setDateTime(new DateTime(date(DateTimeFormat::MYSQL, $ts_end)), $type);

	$component->__unset("DTSTART");
	$component->__unset("DTEND");
	$component->add($datetime_start);
	$component->add($datetime_end);

	return $ts_start;
}

/**
 * @param Sabre_VObject_Component_VEvent $component
 * @param string $str
 * @return string
 */

function wdcal_set_component_recurrence_special(&$component, $str) {
	$ret = "";

	/** @var Sabre\VObject\Property\DateTime $start  */
	$start  = $component->__get("DTSTART");
	$dayMap = array(
		0 => 'SU',
		1 => 'MO',
		2 => 'TU',
		3 => 'WE',
		4 => 'TH',
		5 => 'FR',
		6 => 'SA',
	);

	switch ($str) {
		case "bymonthday":
			$day = $start->getDateTime()->format("j");
			$ret = ";BYMONTHDAY=" . $day;
			break;
		case "bymonthday_neg":
			$day     = $start->getDateTime()->format("j");
			$day_max = $start->getDateTime()->format("t");
			$ret = ";BYMONTHDAY=" . (-1 * ($day_max - $day + 1));
			break;
		case "byday":
			$day     = $start->getDateTime()->format("j");
			$weekday = $dayMap[$start->getDateTime()->format("w")];
			$num     = IntVal(ceil($day / 7));
			$ret = ";BYDAY=${num}${weekday}";
			break;
		case "byday_neg":
			$day     = $start->getDateTime()->format("j");
			$weekday = $dayMap[$start->getDateTime()->format("w")];
			$day_max = $start->getDateTime()->format("t");
			$day_last = ($day_max - $day + 1);
			$num     = IntVal(ceil($day_last / 7));
			$ret = ";BYDAY=-${num}${weekday}";
			break;
	}
	return $ret;
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
			$datetime_until = new Sabre\VObject\Property\DateTime("UNTIL");
			$datetime_until->setDateTime(new DateTime(date(DateTimeFormat::MYSQL, $date)), Sabre\VObject\Property\DateTime::DATE);
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
			$part_freq .= wdcal_set_component_recurrence_special($component, $_REQUEST["rec_monthly_day"]);
			break;
		case "yearly":
			/** @var Sabre\VObject\Property\DateTime $start  */
			$start  = $component->__get("DTSTART");
			$part_freq = "FREQ=YEARLY";
			$part_freq .= ";BYMONTH=" . $start->getDateTime()->format("n");
			$part_freq .= wdcal_set_component_recurrence_special($component, $_REQUEST["rec_yearly_day"]);
			break;
		default:
			$part_freq = "";
	}

	if ($part_freq == "") return;

	if (isset($_REQUEST["rec_interval"])) $part_freq .= ";INTERVAL=" . InTVal($_REQUEST["rec_interval"]);

	if (isset($_REQUEST["rec_exceptions"])) {
		$arr = array();
		foreach ($_REQUEST["rec_exceptions"] as $except) {
			$arr[] = new DateTime(date(DateTimeFormat::MYSQL, $except));
		}
		/** @var Sabre\VObject\Property\MultiDateTime $prop */
		$prop = Sabre\VObject\Property::create("EXDATE");
		$prop->setDateTimes($arr);
		$component->add($prop);
	}

	$rrule = $part_freq . $part_until;
	$component->add(new Sabre\VObject\Property("RRULE", $rrule));

}


	/**
	 * @param Sabre\VObject\Component\VEvent $component
	 * @param wdcal_local $localization
	 * @param string $summary
	 * @param int $dtstart
	 */
function wdcal_set_component_alerts(&$component, &$localization, $summary, $dtstart)
{
	$a = get_app();

	$prev_alarms = $component->select("VALARM");
	$component->__unset("VALARM");

	foreach ($prev_alarms as $al) {
		/** @var Sabre\VObject\Component\VAlarm $al */
		// @TODO Parse notifications that have been there before; e.g. from Lightning
	}

	foreach (array_keys($_REQUEST["noti_type"]) as $key) if (is_numeric($key) || ($key == "new" && $_REQUEST["new_alarm"] == 1)) {
		$alarm = new Sabre\VObject\Component\VAlarm("VALARM");

		switch ($_REQUEST["noti_type"][$key]) {
			case "email":
				$mailtext = str_replace(array(
					"#date#", "#name",
				), array(
					$localization->date_timestamp2local($dtstart), $summary,
				), L10n::t("The event #name# will start at #date"));

				$alarm->add(new Sabre\VObject\Property("ACTION", "EMAIL"));
				$alarm->add(new Sabre\VObject\Property("SUMMARY", $summary));
				$alarm->add(new Sabre\VObject\Property("DESCRIPTION", $mailtext));
				$alarm->add(new Sabre\VObject\Property("ATTENDEE", "MAILTO:" . $a->user["email"]));
				break;
			case "display":
				$alarm->add(new Sabre\VObject\Property("ACTION", "DISPLAY"));
				$text = str_replace("#name#", $summary, L10n::t("#name# is about to begin."));
				$alarm->add(new Sabre\VObject\Property("DESCRIPTION", $text));
				break;
			default:
				continue;
		}

		$trigger_name = "TRIGGER";
		$trigger_val = ""; // @TODO Bugfix : und ; sind evtl. vertauscht vgl. http://www.kanzaki.com/docs/ical/trigger.html
		if ($_REQUEST["noti_ref"][$key] == "end") $trigger_name .= ";RELATED=END";
		$trigger_val .= "-P";
		if (in_array($_REQUEST["noti_unit"][$key], array("H", "M", "S"))) $trigger_val .= "T";
		$trigger_val .= IntVal($_REQUEST["noti_value"][$key]) . $_REQUEST["noti_unit"][$key];
		$alarm->add(new Sabre\VObject\Property($trigger_name, $trigger_val));

		$component->add($alarm);
	}

}

	/**
 * @param string $uri
 * @param int $uid
 * @param string $timezone
 * @param string $goaway_url
 * @return array
 */
function wdcal_postEditPage($uri, $uid = 0, $timezone = "", $goaway_url = "")
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

		if ($component == null) return array("ok" => false, "msg" => L10n::t('Could not open component for editing'));
	} else {
		$calendar  = dav_get_current_user_calendar_by_id($server, $_REQUEST["calendar"], DAV_ACL_WRITE);
		$vObject   = dav_create_empty_vevent();
		$component = dav_get_eventComponent($vObject);
		$obj_uri   = $component->__get("UID");
	}

	$ts_start = wdcal_set_component_date($component, $localization);
	wdcal_set_component_recurrence($component, $localization);
	wdcal_set_component_alerts($component, $localization, icalendar_sanitize_string(dav_compat_parse_text_serverside("summary")), $ts_start);

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
	return array("ok" => false, "msg" => L10n::t("Saved"));
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
	/** @var Sabre\VObject\Component\VEvent $component */
	wdcal_set_component_date($component, $localization);
	wdcal_set_component_recurrence($component, $localization);


	$it         = new Sabre\VObject\RecurrenceIterator($vObject, (string)$component->__get("UID"));
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