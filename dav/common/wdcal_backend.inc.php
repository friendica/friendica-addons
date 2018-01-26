<?php

use Friendica\Core\L10n;
use Friendica\Util\Temporal;

/**
 * @param mixed $obj
 * @return string
 */
function wdcal_jsonp_encode($obj)
{
	$str = json_encode($obj);
	if (isset($_REQUEST["callback"])) {
		$str = $_REQUEST["callback"] . "(" . $str . ")";
	}
	return $str;
}


/**
 * @param string $day
 * @param int $weekstartday
 * @param int $num_days
 * @param string $type
 * @return array
 */
function wdcal_get_list_range_params($day, $weekstartday, $num_days, $type)
{
	$phpTime = IntVal($day);
	switch ($type) {
		case "month":
			$st = mktime(0, 0, 0, date("m", $phpTime), 1, date("Y", $phpTime));
			$et = mktime(0, 0, -1, date("m", $phpTime) + 1, 1, date("Y", $phpTime));
			break;
		case "week":
			//suppose first day of a week is monday
			$monday = date("d", $phpTime) - date('N', $phpTime) + 1;
			//echo date('N', $phpTime);
			$st = mktime(0, 0, 0, date("m", $phpTime), $monday, date("Y", $phpTime));
			$et = mktime(0, 0, -1, date("m", $phpTime), $monday + 7, date("Y", $phpTime));
			break;
		case "multi_days":
			//suppose first day of a week is monday
			$monday = date("d", $phpTime) - date('N', $phpTime) + $weekstartday;
			//echo date('N', $phpTime);
			$st = mktime(0, 0, 0, date("m", $phpTime), $monday, date("Y", $phpTime));
			$et = mktime(0, 0, -1, date("m", $phpTime), $monday + $num_days, date("Y", $phpTime));
			break;
		case "day":
			$st = mktime(0, 0, 0, date("m", $phpTime), date("d", $phpTime), date("Y", $phpTime));
			$et = mktime(0, 0, -1, date("m", $phpTime), date("d", $phpTime) + 1, date("Y", $phpTime));
			break;
		default:
			return array(0, 0);
	}
	return array($st, $et);
}


/**
 * @param Sabre_DAV_Server $server
 * @param string $right
 * @return null|Sabre_CalDAV_Calendar
 */
function wdcal_print_feed_getCal(&$server, $right)
{
	$cals     = dav_get_current_user_calendars($server, $right);
	$calfound = null;
	for ($i = 0; $i < count($cals) && $calfound === null; $i++) {
		$prop = $cals[$i]->getProperties(array("id"));
		if (isset($prop["id"]) && (!isset($_REQUEST["cal"]) || in_array($prop["id"], $_REQUEST["cal"]))) $calfound = $cals[$i];
	}
	return $calfound;
}


/**
 *
 */
function wdcal_print_feed($base_path = "")
{
	$server = dav_create_server(true, true, false);

	$ret = null;

	$method = $_GET["method"];
	switch ($method) {
		case "add":
			$cs = wdcal_print_feed_getCal($server, DAV_ACL_WRITE);
			if ($cs == null) {
				echo wdcal_jsonp_encode(array('IsSuccess' => false,
											  'Msg'       => L10n::t('No access')));
				killme();
			}
			try {
				$item      = dav_create_empty_vevent();
				$component = dav_get_eventComponent($item);
				$component->add("SUMMARY", icalendar_sanitize_string(dav_compat_parse_text_serverside("CalendarTitle")));

				if (isset($_REQUEST["allday"])) $type = Sabre\VObject\Property\DateTime::DATE;
				else $type = Sabre\VObject\Property\DateTime::LOCALTZ;

				$datetime_start = new Sabre\VObject\Property\DateTime("DTSTART");
				$datetime_start->setDateTime(new DateTime(date(Temporal::MYSQL, IntVal($_REQUEST["CalendarStartTime"]))), $type);
				$datetime_end = new Sabre\VObject\Property\DateTime("DTEND");
				$datetime_end->setDateTime(new DateTime(date(Temporal::MYSQL, IntVal($_REQUEST["CalendarEndTime"]))), $type);

				$component->add($datetime_start);
				$component->add($datetime_end);

				$uid  = $component->__get("UID");
				$data = $item->serialize();

				$cs->createFile($uid . ".ics", $data);

				$ret = array(
					'IsSuccess' => true,
					'Msg'       => 'add success',
					'Data'      => $uid . ".ics",
				);

			} catch (Exception $e) {
				$ret = array(
					'IsSuccess' => false,
					'Msg'       => $e->__toString(),
				);
			}
			break;
		case "list":
			$weekstartday = (isset($_REQUEST["weekstartday"]) ? IntVal($_REQUEST["weekstartday"]) : 1); // 1 = Monday
			$num_days     = (isset($_REQUEST["num_days"]) ? IntVal($_REQUEST["num_days"]) : 7);
			$ret          = null;

			$date          = wdcal_get_list_range_params($_REQUEST["showdate"], $weekstartday, $num_days, $_REQUEST["viewtype"]);
			$ret           = array();
			$ret['events'] = array();
			$ret["issort"] = true;
			$ret["start"]  = $date[0];
			$ret["end"]    = $date[1];
			$ret['error']  = null;

			$cals = dav_get_current_user_calendars($server, DAV_ACL_READ);
			foreach ($cals as $cal) {
				$prop = $cal->getProperties(array("id"));
				if (isset($prop["id"]) && (!isset($_REQUEST["cal"]) || in_array($prop["id"], $_REQUEST["cal"]))) {
					$backend       = wdcal_calendar_factory_by_id($prop["id"]);
					$events        = $backend->listItemsByRange($prop["id"], $date[0], $date[1], $base_path);
					$ret["events"] = array_merge($ret["events"], $events);
				}
			}

			$tmpev = array();
			foreach ($ret["events"] as $e) {
				if (!isset($tmpev[$e["start"]])) $tmpev[$e["start"]] = array();
				$tmpev[$e["start"]][] = $e;
			}
			ksort($tmpev);
			$ret["events"] = array();
			foreach ($tmpev as $e) foreach ($e as $f) $ret["events"][] = $f;

			break;
		case "update":
			$r = q("SELECT `calendarobject_id`, `calendar_id` FROM %s%sjqcalendar WHERE `id`=%d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($_REQUEST["jq_id"]));
			if (count($r) != 1) {
				echo wdcal_jsonp_encode(array('IsSuccess' => false,
											  'Msg'       => L10n::t('No access')));
				killme();
			}
			try {
				$cs      = dav_get_current_user_calendar_by_id($server, $r[0]["calendar_id"], DAV_ACL_READ);
				$obj_uri = Sabre_CalDAV_Backend_Common::loadCalendarobjectById($r[0]["calendarobject_id"]);

				$vObject   = dav_get_current_user_calendarobject($server, $cs, $obj_uri["uri"], DAV_ACL_WRITE);
				$component = dav_get_eventComponent($vObject);

				if (!$component) {
					echo wdcal_jsonp_encode(array('IsSuccess' => false,
												  'Msg'       => L10n::t('No access')));
					killme();
				}

				if (isset($_REQUEST["allday"])) $type = Sabre\VObject\Property\DateTime::DATE;
				else $type = Sabre\VObject\Property\DateTime::LOCALTZ;

				$datetime_start = new Sabre\VObject\Property\DateTime("DTSTART");
				$datetime_start->setDateTime(new DateTime(date(Temporal::MYSQL, IntVal($_REQUEST["CalendarStartTime"]))), $type);
				$datetime_end = new Sabre\VObject\Property\DateTime("DTEND");
				$datetime_end->setDateTime(new DateTime(date(Temporal::MYSQL, IntVal($_REQUEST["CalendarEndTime"]))), $type);

				$component->__unset("DTSTART");
				$component->__unset("DTEND");
				$component->add($datetime_start);
				$component->add($datetime_end);

				$data = $vObject->serialize();
				/** @var Sabre_CalDAV_CalendarObject $child  */
				$child = $cs->getChild($obj_uri["uri"]);
				$child->put($data);

				$ret = array(
					'IsSuccess' => true,
					'Msg'       => 'Succefully',
				);
			} catch (Exception $e) {
				echo wdcal_jsonp_encode(array('IsSuccess' => false,
											  'Msg'       => L10n::t('No access')));
				killme();
			}
			break;
		case "remove":
			$r = q("SELECT `calendarobject_id`, `calendar_id` FROM %s%sjqcalendar WHERE `id`=%d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($_REQUEST["jq_id"]));
			if (count($r) != 1) {
				echo wdcal_jsonp_encode(array('IsSuccess' => false,
											  'Msg'       => L10n::t('No access')));
				killme();
			}
			try {
				$cs      = dav_get_current_user_calendar_by_id($server, $r[0]["calendar_id"], DAV_ACL_WRITE);
				$obj_uri = Sabre_CalDAV_Backend_Common::loadCalendarobjectById($r[0]["calendarobject_id"]);
				$child   = $cs->getChild($obj_uri["uri"]);
				$child->delete();

				$ret = array(
					'IsSuccess' => true,
					'Msg'       => 'Succefully',
				);
			} catch (Exception $e) {
				echo wdcal_jsonp_encode(array('IsSuccess' => false,
											  'Msg'       => L10n::t('No access')));
				killme();
			}

			break;
	}
	echo wdcal_jsonp_encode($ret);
	killme();
}

