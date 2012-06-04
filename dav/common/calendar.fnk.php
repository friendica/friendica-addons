<?php


class vcard_source_data_email
{
	public $email, $type;

	function __construct($type, $email)
	{
		$this->email = $email;
		$this->type  = $type;
	}
}

class vcard_source_data_homepage
{
	public $homepage, $type;

	function __construct($type, $homepage)
	{
		$this->homepage = $homepage;
		$this->type     = $type;
	}
}

class vcard_source_data_telephone
{
	public $telephone, $type;

	function __construct($type, $telephone)
	{
		$this->telephone = $telephone;
		$this->type      = $type;
	}
}

class vcard_source_data_socialnetwork
{
	public $nick, $type, $url;

	function __construct($type, $nick, $url)
	{
		$this->nick = $nick;
		$this->type = $type;
		$this->url  = $url;
	}
}

class vcard_source_data_address
{
	public $street, $street2, $zip, $city, $country, $type;
}

class vcard_source_data_photo
{
	public $binarydata;
	public $width, $height;
	public $type;
}

class vcard_source_data
{
	function __construct($name_first, $name_middle, $name_last)
	{
		$this->name_first  = $name_first;
		$this->name_middle = $name_middle;
		$this->name_last   = $name_last;
	}

	public $name_first, $name_middle, $name_last;
	public $last_update;
	public $picture_data;

	/** @var array|vcard_source_data_telephone[] $telephones */
	public $telephones;

	/** @var array|vcard_source_data_homepage[] $homepages */
	public $homepages;

	/** @var array|vcard_source_data_socialnetwork[] $socialnetworks */
	public $socialnetworks;

	/** @var array|vcard_source_data_email[] $email */
	public $emails;

	/** @var array|vcard_source_data_addresses[] $addresses */
	public $addresses;

	/** @var vcard_source_data_photo */
	public $photo;
}

;


/**
 * @param vcard_source_data $vcardsource
 * @return string
 */
function vcard_source_compile($vcardsource)
{
	$str = "BEGIN:VCARD\r\nVERSION:3.0\r\nPRODID:-//Friendica//DAV-Plugin//EN\r\n";
	$str .= "N:" . str_replace(";", ",", $vcardsource->name_last) . ";" . str_replace(";", ",", $vcardsource->name_first) . ";" . str_replace(";", ",", $vcardsource->name_middle) . ";;\r\n";
	$str .= "FN:" . str_replace(";", ",", $vcardsource->name_first) . " " . str_replace(";", ",", $vcardsource->name_middle) . " " . str_replace(";", ",", $vcardsource->name_last) . "\r\n";
	$str .= "REV:" . str_replace(" ", "T", $vcardsource->last_update) . "Z\r\n";

	$item_count = 0;
	for ($i = 0; $i < count($vcardsource->homepages); $i++) {
		if ($i == 0) $str .= "URL;type=" . $vcardsource->homepages[0]->type . ":" . $vcardsource->homepages[0]->homepage . "\r\n";
		else {
			$c = ++$item_count;
			$str .= "item$c.URL;type=" . $vcardsource->homepages[0]->type . ":" . $vcardsource->homepages[0]->homepage . "\r\n";
			$str .= "item$c.X-ABLabel:_\$!<HomePage>!\$_\r\n";
		}
	}

	if (is_object($vcardsource->photo)) {
		$data = base64_encode($vcardsource->photo->binarydata);
		$str .= "PHOTO;ENCODING=BASE64;TYPE=" . $vcardsource->photo->type . ":" . $data . "\r\n";
	}

	if (isset($vcardsource->socialnetworks) && is_array($vcardsource->socialnetworks)) foreach ($vcardsource->socialnetworks as $netw) switch ($netw->type) {
		case "dfrn":
			$str .= "X-SOCIALPROFILE;type=dfrn;x-user=" . $netw->nick . ":" . $netw->url . "\r\n";
			break;
		case "facebook":
			$str .= "X-SOCIALPROFILE;type=facebook;x-user=" . $netw->nick . ":" . $netw->url . "\r\n";
			break;
		case "twitter":
			$str .= "X-SOCIALPROFILE;type=twitter;x-user=" . $netw->nick . ":" . $netw->url . "\r\n";
			break;
	}

	$str .= "END:VCARD\r\n";
	return $str;
}


/**
 * @param array $start
 * @param array $end
 * @param bool $allday
 * @return vevent
 */
function dav_create_vevent($start, $end, $allday)
{
	if ($end["year"] < $start["year"] ||
		($end["year"] == $start["year"] && $end["month"] < $start["month"]) ||
		($end["year"] == $start["year"] && $end["month"] == $start["month"] && $end["day"] < $start["day"]) ||
		($end["year"] == $start["year"] && $end["month"] == $start["month"] && $end["day"] == $start["day"] && $end["hour"] < $start["hour"]) ||
		($end["year"] == $start["year"] && $end["month"] == $start["month"] && $end["day"] == $start["day"] && $end["hour"] == $start["hour"] && $end["minute"] < $start["minute"]) ||
		($end["year"] == $start["year"] && $end["month"] == $start["month"] && $end["day"] == $start["day"] && $end["hour"] == $start["hour"] && $end["minute"] == $start["minute"] && $end["second"] < $start["second"])
	) {
		$end = $start;
	} // DTEND muss <= DTSTART

	$vevent = new vevent();
	if ($allday) {
		$vevent->setDtstart($start["year"], $start["month"], $start["day"], FALSE, FALSE, FALSE, FALSE, array("VALUE"=> "DATE"));
		$end = IntVal(mktime(0, 0, 0, $end["month"], $end["day"], $end["year"]) + 3600 * 24);

		// If a DST change occurs on the current day
		$end += IntVal(date("Z", ($end - 3600 * 24)) - date("Z", $end));

		$vevent->setDtend(date("Y", $end), date("m", $end), date("d", $end), FALSE, FALSE, FALSE, FALSE, array("VALUE"=> "DATE"));
	} else {
		$vevent->setDtstart($start["year"], $start["month"], $start["day"], $start["hour"], $start["minute"], $start["second"], FALSE, array("VALUE"=> "DATE-TIME"));
		$vevent->setDtend($end["year"], $end["month"], $end["day"], $end["hour"], $end["minute"], $end["second"], FALSE, array("VALUE"=> "DATE-TIME"));
	}
	return $vevent;
}


/**
 * @param int $phpDate (UTC)
 * @return string (Lokalzeit)
 */
function wdcal_php2MySqlTime($phpDate)
{
	return date("Y-m-d H:i:s", $phpDate);
}

/**
 * @param string $sqlDate
 * @return int
 */
function wdcal_mySql2PhpTime($sqlDate)
{
	$ts = DateTime::createFromFormat("Y-m-d H:i:s", $sqlDate);
	return $ts->format("U");
}

/**
 * @param string $myqlDate
 * @return array
 */
function wdcal_mySql2icalTime($myqlDate)
{
	$x             = explode(" ", $myqlDate);
	$y             = explode("-", $x[0]);
	$ret           = array("year"=> $y[0], "month"=> $y[1], "day"=> $y[2]);
	$y             = explode(":", $x[1]);
	$ret["hour"]   = $y[0];
	$ret["minute"] = $y[1];
	$ret["second"] = $y[2];
	return $ret;
}


/**
 * @param string $str
 * @return string
 */
function icalendar_sanitize_string($str = "")
{
	$str = str_replace("\r\n", "\n", $str);
	$str = str_replace("\n\r", "\n", $str);
	$str = str_replace("\r", "\n", $str);
	return $str;
}


/**
 * @param DBClass_friendica_calendars $calendar
 * @param DBClass_friendica_calendarobjects $calendarobject
 */
function renderCalDavEntry_data(&$calendar, &$calendarobject)
{
	$a = get_app();

	$v = new vcalendar();
	$v->setConfig('unique_id', $a->get_hostname());
	$v->parse($calendarobject->calendardata);
	$v->sort();

	$eventArray = $v->selectComponents(2009, 1, 1, date("Y") + 2, 12, 30);

	$start_min = $end_max = "";

	$allday   = $summary = $vevent = $rrule = $color = $start = $end = null;
	$location = $description = "";

	foreach ($eventArray as $yearArray) {
		foreach ($yearArray as $monthArray) {
			foreach ($monthArray as $day => $dailyEventsArray) {
				foreach ($dailyEventsArray as $vevent) {
					/** @var $vevent vevent  */
					$start  = "";
					$rrule  = "NULL";
					$allday = 0;

					$dtstart = $vevent->getProperty('X-CURRENT-DTSTART');
					if (is_array($dtstart)) {
						$start = "'" . $dtstart[1] . "'";
						if (strpos($dtstart[1], ":") === false) $allday = 1;
					} else {
						$dtstart = $vevent->getProperty('dtstart');
						if (isset($dtstart["day"]) && $dtstart["day"] == $day) { // Mehrtägige Events nur einmal rein
							if (isset($dtstart["hour"])) $start = "'" . $dtstart["year"] . "-" . $dtstart["month"] . "-" . $dtstart["day"] . " " . $dtstart["hour"] . ":" . $dtstart["minute"] . ":" . $dtstart["secont"] . "'";
							else {
								$start  = "'" . $dtstart["year"] . "-" . $dtstart["month"] . "-" . $dtstart["day"] . " 00:00:00'";
								$allday = 1;
							}
						}
					}

					$dtend = $vevent->getProperty('X-CURRENT-DTEND');
					if (is_array($dtend)) {
						$end = "'" . $dtend[1] . "'";
						if (strpos($dtend[1], ":") === false) $allday = 1;
					} else {
						$dtend = $vevent->getProperty('dtend');
						if (isset($dtend["hour"])) $end = "'" . $dtend["year"] . "-" . $dtend["month"] . "-" . $dtend["day"] . " " . $dtend["hour"] . ":" . $dtend["minute"] . ":" . $dtend["second"] . "'";
						else {
							$end    = "'" . $dtend["year"] . "-" . $dtend["month"] . "-" . $dtend["day"] . " 00:00:00' - INTERVAL 1 SECOND";
							$allday = 1;
						}
					}
					$summary     = $vevent->getProperty('summary');
					$description = $vevent->getProperty('description');
					$location    = $vevent->getProperty('location');
					$rrule_prob  = $vevent->getProperty('rrule');
					if ($rrule_prob != null) {
						$rrule = $vevent->createRrule();
						$rrule = "'" . dbesc($rrule) . "'";
					}
					$color_ = $vevent->getProperty("X-ANIMEXX-COLOR");
					$color  = (is_array($color_) ? $color_[1] : "NULL");

					if ($start_min == "" || preg_replace("/[^0-9]/", "", $start) < preg_replace("/[^0-9]/", "", $start_min)) $start_min = $start;
					if ($end_max == "" || preg_replace("/[^0-9]/", "", $end) > preg_replace("/[^0-9]/", "", $start_min)) $end_max = $end;
				}
			}
		}
	}

	if ($start_min != "") {

		if ($allday && mb_strlen($end_max) == 12) {
			$x       = explode("-", str_replace("'", "", $end_max));
			$time    = mktime(0, 0, 0, IntVal($x[1]), IntVal($x[2]), IntVal($x[0]));
			$end_max = date("'Y-m-d H:i:s'", ($time - 1));
		}

		q("INSERT INTO %s%sjqcalendar (`uid`, `namespace`, `namespace_id`, `ical_uri`, `Subject`, `Location`, `Description`, `StartTime`, `EndTime`, `IsAllDayEvent`, `RecurringRule`, `Color`)
			VALUES (%d, %d, %d, '%s', '%s', '%s', '%s', %s, %s, %d, '%s', '%s')",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX,
			IntVal($calendar->uid), IntVal($calendarobject->namespace), IntVal($calendarobject->namespace_id), dbesc($calendarobject->uri), dbesc($summary),
			dbesc($location), dbesc(str_replace("\\n", "\n", $description)), $start_min, $end_max, IntVal($allday), dbesc($rrule), dbesc($color)
		);

		foreach ($vevent->components as $comp) {
			/** @var $comp calendarComponent */
			$trigger   = $comp->getProperty("TRIGGER");
			$sql_field = ($trigger["relatedStart"] ? $start : $end);
			$sql_op    = ($trigger["before"] ? "DATE_SUB" : "DATE_ADD");
			$num       = "";
			$rel_type  = "";
			$rel_value = 0;
			if (isset($trigger["second"])) {
				$num       = IntVal($trigger["second"]) . " SECOND";
				$rel_type  = "second";
				$rel_value = IntVal($trigger["second"]);
			}
			if (isset($trigger["minute"])) {
				$num       = IntVal($trigger["minute"]) . " MINUTE";
				$rel_type  = "minute";
				$rel_value = IntVal($trigger["minute"]);
			}
			if (isset($trigger["hour"])) {
				$num       = IntVal($trigger["hour"]) . " HOUR";
				$rel_type  = "hour";
				$rel_value = IntVal($trigger["hour"]);
			}
			if (isset($trigger["day"])) {
				$num       = IntVal($trigger["day"]) . " DAY";
				$rel_type  = "day";
				$rel_value = IntVal($trigger["day"]);
			}
			if (isset($trigger["week"])) {
				$num       = IntVal($trigger["week"]) . " WEEK";
				$rel_type  = "week";
				$rel_value = IntVal($trigger["week"]);
			}
			if (isset($trigger["month"])) {
				$num       = IntVal($trigger["month"]) . " MONTH";
				$rel_type  = "month";
				$rel_value = IntVal($trigger["month"]);
			}
			if (isset($trigger["year"])) {
				$num       = IntVal($trigger["year"]) . " YEAR";
				$rel_type  = "year";
				$rel_value = IntVal($trigger["year"]);
			}
			if ($trigger["before"]) $rel_value *= -1;

			if ($rel_type != "") {
				$not_date = "$sql_op($sql_field, INTERVAL $num)";
				q("INSERT INTO %s%snotifications (`uid`, `ical_uri`, `rel_type`, `rel_value`, `alert_date`, `notified`) VALUES ('%s', '%s', '%s', '%s', %s, IF(%s < NOW(), 1, 0))",
					CALDAV_SQL_DB, CALDAV_SQL_PREFIX,
					IntVal($calendar->uid), dbesc($calendarobject->uri), dbesc($rel_type), IntVal($rel_value), $not_date, $not_date);
			}
		}
	}
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
		$cal = new DBClass_friendica_calendars($calendar);
		$i++;
		if (($i % 100) == 0) echo "$i / $anz\n";
		$calobjs = q("SELECT * FROM %s%scalendarobjects WHERE `namespace` = %d AND `namespace_id` = %d",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($calendar["namespace"]), IntVal($calendar["namespace_id"]));
		foreach ($calobjs as $calobj) {
			$obj = new DBClass_friendica_calendarobjects($calobj);
			renderCalDavEntry_data($cal, $obj);
		}
	}
}


/**
 * @param string $uri
 * @return bool
 */
function renderCalDavEntry_uri($uri)
{
	q("DELETE FROM %s%sjqcalendar WHERE `ical_uri` = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($uri));
	q("DELETE FROM %s%snotifications WHERE `ical_uri` = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($uri));

	$calobj = q("SELECT * FROM %s%scalendarobjects WHERE `uri` = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($uri));
	if (count($calobj) == 0) return false;
	$cal       = new DBClass_friendica_calendarobjects($calobj[0]);
	$calendars = q("SELECT * FROM %s%scalendars WHERE `namespace`=%d AND `namespace_id`=%d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($cal->namespace), IntVal($cal->namespace_id));
	$calendar  = new DBClass_friendica_calendars($calendars[0]);
	renderCalDavEntry_data($calendar, $cal);
	return true;
}


/**
 * @param $user_id
 * @return array|DBClass_friendica_calendars[]
 */
function dav_getMyCals($user_id)
{
	$d    = q("SELECT * FROM %s%scalendars WHERE `uid` = %d ORDER BY `calendarorder` ASC",
		CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($user_id), CALDAV_NAMESPACE_PRIVATE
	);
	$cals = array();
	foreach ($d as $e) $cals[] = new DBClass_friendica_calendars($e);
	return $cals;
}


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
 *
 */
function wdcal_print_feed($base_path = "")
{
	$user_id = dav_compat_get_curr_user_id();
	$cals    = array();
	if (isset($_REQUEST["cal"])) foreach ($_REQUEST["cal"] as $c) {
		$x              = explode("-", $c);
		$calendarSource = wdcal_calendar_factory($user_id, $x[0], $x[1]);
		$calp           = $calendarSource->getPermissionsCalendar($user_id);
		if ($calp["read"]) $cals[] = $calendarSource;
	}

	$ret = null;
	/** @var $cals array|AnimexxCalSource[] */

	$method = $_GET["method"];
	switch ($method) {
		case "add":
			$cs = null;
			foreach ($cals as $c) if ($cs == null) {
				$x = $c->getPermissionsCalendar($user_id);
				if ($x["read"]) $cs = $c;
			}
			if ($cs == null) {
				echo wdcal_jsonp_encode(array('IsSuccess' => false,
											  'Msg'       => t('No access')));
				killme();
			}
			try {
				$start  = wdcal_mySql2icalTime(wdcal_php2MySqlTime($_REQUEST["CalendarStartTime"]));
				$end    = wdcal_mySql2icalTime(wdcal_php2MySqlTime($_REQUEST["CalendarEndTime"]));
				$newuri = $cs->addItem($start, $end, $_REQUEST["CalendarTitle"], $_REQUEST["IsAllDayEvent"]);
				$ret    = array(
					'IsSuccess' => true,
					'Msg'       => 'add success',
					'Data'      => $newuri,
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

			foreach ($cals as $c) {
				$events        = $c->listItemsByRange($date[0], $date[1], $base_path);
				$ret["events"] = array_merge($ret["events"], $events);
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
			$found = false;
			$start = wdcal_mySql2icalTime(wdcal_php2MySqlTime($_REQUEST["CalendarStartTime"]));
			$end   = wdcal_mySql2icalTime(wdcal_php2MySqlTime($_REQUEST["CalendarEndTime"]));
			foreach ($cals as $c) try {
				$permissions_item = $c->getPermissionsItem($user_id, $_REQUEST["calendarId"], "");
				if ($permissions_item["write"]) {
					$c->updateItem($_REQUEST["calendarId"], $start, $end);
					$found = true;
				}
			} catch (Exception $e) {
			}
			;

			if ($found) {
				$ret = array(
					'IsSuccess' => true,
					'Msg'       => 'Succefully',
				);
			} else {
				echo wdcal_jsonp_encode(array('IsSuccess' => false,
											  'Msg'       => t('No access')));
				killme();
			}

			try {
			} catch (Exception $e) {
				$ret = array(
					'IsSuccess' => false,
					'Msg'       => $e->__toString(),
				);
			}
			break;
		case "remove":
			$found = false;
			foreach ($cals as $c) try {
				$permissions_item = $c->getPermissionsItem($user_id, $_REQUEST["calendarId"], "");
				if ($permissions_item["write"]) $c->removeItem($_REQUEST["calendarId"]);
			} catch (Exception $e) {
			}

			if ($found) {
				$ret = array(
					'IsSuccess' => true,
					'Msg'       => 'Succefully',
				);
			} else {
				echo wdcal_jsonp_encode(array('IsSuccess' => false,
											  'Msg'       => t('No access')));
				killme();
			}
			break;
	}
	echo wdcal_jsonp_encode($ret);
	killme();
}

