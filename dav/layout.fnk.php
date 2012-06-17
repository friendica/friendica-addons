<?php


/**
 *
 */
function wdcal_addRequiredHeaders()
{
	$a = get_app();

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/jqueryui/jquery-ui-1.8.20.custom.css' . '" media="all" />' . "\r\n";
	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/jqueryui/jquery-ui-1.8.20.custom.min.js"></script>' . "\r\n";

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/wdcal.css' . '" media="all" />' . "\r\n";
	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/common/wdcal.js"></script>' . "\r\n";

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/wdcal/css/calendar.css' . '" media="all" />' . "\r\n";
	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/wdcal/css/main.css' . '" media="all" />' . "\r\n";

	switch (get_config("system", "language")) {
		case "de":
			$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/common/wdcal/js/wdCalendar_lang_DE.js"></script>' . "\r\n";
			break;
		default:
			$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/common/wdcal/js/wdCalendar_lang_EN.js"></script>' . "\r\n";
	}

	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/common/wdcal/js/jquery.calendar.js"></script>' . "\r\n";
	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/common/wdcal/js/main.js"></script>' . "\r\n";
}

/**
 *
 */
function wdcal_addRequiredHeadersEdit()
{
	$a = get_app();

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/jqueryui/jquery-ui-1.8.20.custom.css' . '" media="all" />' . "\r\n";
	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/jqueryui/jquery-ui-1.8.20.custom.min.js"></script>' . "\r\n";

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/colorpicker/colorPicker.css' . '" media="all" />' . "\r\n";
	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/colorpicker/jquery.colorPicker.min.js"></script>' . "\r\n";

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/timepicker/timePicker.css' . '" media="all" />' . "\r\n";
	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/timepicker/jquery.timePicker.min.js"></script>' . "\r\n";

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/wdcal.css' . '" media="all" />' . "\r\n";
	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/common/wdcal.js"></script>' . "\r\n";

}


/**
 * @param array|DBClass_friendica_calendars[] $calendars
 * @param array $calendar_preselected
 * @param string $data_feed_url
 * @param string $view
 * @param int $theme
 * @param int $height_diff
 * @param bool $readonly
 * @param string $curr_day
 * @param array $add_params
 * @param bool $show_nav
 * @return string
 */
function wdcal_printCalendar($calendars, $calendar_preselected, $data_feed_url, $view = "week", $theme = 0, $height_diff = 175, $readonly = false, $curr_day = "", $add_params = array(), $show_nav = true)
{

	$a            = get_app();
	$localization = wdcal_local::getInstanceByUser($a->user["uid"]);

	$cals_avail = array();
	foreach ($calendars as $c) $cals_avail[] = array("ns" => $c->namespace, "id" => $c->namespace_id, "displayname" => $c->displayname);
	$opts = array(
		"view"             => $view,
		"theme"            => $theme,
		"readonly"         => $readonly,
		"height_diff"      => $height_diff,
		"weekstartday"     => $localization->getFirstDayOfWeek(),
		"data_feed_url"    => $data_feed_url,
		"date_format_dm1"  => $localization->dateformat_js_dm1(),
		"date_format_dm2"  => $localization->dateformat_js_dm2(),
		"date_format_dm3"  => $localization->dateformat_js_dm3(),
		"date_format_full" => $localization->dateformat_datepicker_js(),
		"baseurl"          => $a->get_baseurl() . "/dav/wdcal/",
	);

	$x = '
<script>
	$(function() {
		$("#animexxcalendar").animexxCalendar(' . json_encode($opts) . ');
	});
</script>

<div id="animexxcalendar" class="animexxcalendar">
	<div class="calselect"><strong>Available Calendars:</strong>';

	foreach ($cals_avail as $cal) {
		$x .= '<label style="margin-left: 10px; margin-right: 10px;"><input type="checkbox" name="cals[]" value="' . $cal["ns"] . '-' . $cal["id"] . '"';
		$found = false;
		foreach ($calendar_preselected as $pre) if ($pre["ns"] == $cal["ns"] && $pre["id"] == $cal["id"]) $found = true;
		if ($found) $x .= ' checked';
		$x .= '> ' . escape_tags($cal["displayname"]) . '</label> ';
	}

	$x .= '</div>
	<div class="calhead" style="padding-left:1px;padding-right:1px;">
		<div class="ptogtitle loaderror" style="display: none;">Sorry, could not load your data, please try again later</div>
	</div>';

	if ($show_nav) {

		$x .= '<div class="ctoolbar">
		<div class="fbutton faddbtn" style="float: right;">
			<div><a href="' . $a->get_baseurl() . '/dav/settings/"><span>' . t("Settings") . ' / ' . t("Help") . '</span></a></div>
		</div>
		<div class="fbutton addcal">
			<div><a href="' . $a->get_baseurl() . '/dav/wdcal/new/" class="addcal">' . t("New event") . '</a></div>
		</div>
		<div class="btnseparator"></div>
		<div class="fbutton showtodaybtn">
			<div><span class="showtoday">' . t("Today") . '</span></div>
		</div>
		<div class="btnseparator"></div>

		<div class="fbutton showdaybtn">
			<div><span title="Day" class="showdayview ';

		if ($view == "day") $x .= 'fcurrent';

		$x .= '">' . t("Day") . '</span></div>
		</div>
		<div class="fbutton showweekbtn ';

		if ($view == "week") $x .= "fcurrent";

		$x .= '">
			<div><span title="Week" class="showweekview">' . t("Week") . '</span></div>
		</div>
		<div class="showmonthbtn fbutton ';

		if ($view == "month") $x .= 'fcurrent';

		$x .= '">
			<div><span title="Month" class="showmonthview">' . t("Month") . '</span></div>

		</div>
		<div class="btnseparator"></div>
		<div class="fbutton showreflashbtn">
			<div><span class="showdayflash">' . t("Reload") . '</span></div>
		</div>
		<div class="btnseparator"></div>
		<div title="' . t("Previous") . '"  class="fbutton sfprevbtn">
			<span class="fprev"></span>
		</div>
		<div title="' . t("Next") . '" class="fbutton sfnextbtn">
			<span class="fnext"></span>
		</div>
		<div class="fshowdatep fbutton" style="white-space: nowrap; position: relative;">
			<input name="txtshow" class="hdtxtshow" style="position: absolute; bottom: 0; left: 0; width: 0; height: 0; border: 0; padding: 0; margin: 0;">
			<span class="txtdatetimeshow">' . t("Date") . '</span>
		</div>
		<div style="float: right;">
			<div class="clear"></div>
		</div>
	</div>';
	}
	$x .= '
	<div style="padding:1px;">
		<div class="calmain printborder">
			<div class="gridcontainer" style="overflow-y: visible;"></div>
		</div>
	</div>
</div>';

	return $x;
}


/**
 * @param string $uri
 * @param string $recurr_uri
 * @return string
 */
function wdcal_getDetailPage($uri, $recurr_uri)
{
	$a = get_app();

	$details = null;
	$cals    = dav_getMyCals($a->user["uid"]);
	foreach ($cals as $c) {
		$cs = wdcal_calendar_factory($a->user["uid"], $c->namespace, $c->namespace_id);
		$p  = $cs->getPermissionsItem($a->user["uid"], $uri, $recurr_uri);
		if ($p["read"]) try {
			$redirect = $cs->getItemDetailRedirect($uri);
			if ($redirect !== null) goaway($redirect);
			$details = $cs->getItemByUri($uri);
		} catch (Exception $e) {
			notification(t("Error") . ": " . $e);
			goaway($a->get_baseurl() . "/dav/wdcal/");
		}
	}


	return $uri . " / " . $recurr_uri . "<br>" . print_r($details, true);
}

/**
 * @param string $uri
 * @param string $recurr_uri
 * @return string
 */
function wdcal_getEditPage($uri, $recurr_uri = "")
{

	$a            = get_app();
	$localization = wdcal_local::getInstanceByUser($a->user["uid"]);

	if ($uri != "" && $uri != "new") {
		$o = q("SELECT * FROM %s%sjqcalendar WHERE `uid` = %d AND `ical_uri` = '%s' AND `ical_recurr_uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $a->user["uid"], dbesc($uri), dbesc($recurr_uri)
		);
		if (count($o) != 1) return t('Not found');
		$event = $o[0];

		$calendarSource = wdcal_calendar_factory($a->user["uid"], $event["namespace"], $event["namespace_id"]);

		$permissions = $calendarSource->getPermissionsItem($a->user["uid"], $uri, $recurr_uri, $event);

		if (!$permissions["write"]) return t('No access');

		$n = q("SELECT * FROM %s%snotifications WHERE `uid` = %d AND `ical_uri` = '%s' AND `ical_recurr_uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $a->user["uid"], dbesc($uri), dbesc($recurr_uri)
		);
		if (count($n) > 0) {
			$notification_type  = $n[0]["rel_type"];
			$notification_value = -1 * $n[0]["rel_value"];
			$notification       = true;
		} else {
			if ($event["IsAllDayEvent"]) {
				$notification_type  = "hour";
				$notification_value = 24;
			} else {
				$notification_type  = "minute";
				$notification_value = 60;
			}
			$notification = false;
		}


	} elseif (isset($_REQUEST["start"]) && $_REQUEST["start"] > 0) {
		$event = array(
			"id"            => 0,
			"Subject"       => $_REQUEST["title"],
			"Location"      => "",
			"Description"   => "",
			"StartTime"     => wdcal_php2MySqlTime($_REQUEST["start"]),
			"EndTime"       => wdcal_php2MySqlTime($_REQUEST["end"]),
			"IsAllDayEvent" => $_REQUEST["isallday"],
			"Color"         => null,
			"RecurringRule" => null,
		);
		if ($_REQUEST["isallday"]) {
			$notification_type  = "hour";
			$notification_value = 24;
		} else {
			$notification_type  = "hour";
			$notification_value = 1;
		}

		$notification = true;
	} else {
		$event              = array(
			"id"            => 0,
			"Subject"       => "",
			"Location"      => "",
			"Description"   => "",
			"StartTime"     => date("Y-m-d H:i:s"),
			"EndTime"       => date("Y-m-d H:i:s", time() + 3600),
			"IsAllDayEvent" => "0",
			"Color"         => "#5858ff",
			"RecurringRule" => null,
		);
		$notification_type  = "hour";
		$notification_value = 1;
		$notification       = true;
	}

	$postto = $a->get_baseurl() . "/dav/wdcal/" . ($uri == "new" ? "new/" : $uri . "/edit/");

	$out = "<a href='" . $a->get_baseurl() . "/dav/wdcal/'>" . t("Go back to the calendar") . "</a><br><br>";
	$out .= "<form method='POST' action='$postto'><input type='hidden' name='form_security_token' value='" . get_form_security_token('caledit') . "'>\n";

	$out .= "<label for='cal_subject'>Subject:</label>
		<input name='color' id='cal_color' value='" . (strlen($event["Color"]) != 7 ? "#5858ff" : escape_tags($event["Color"])) . "'>
		<input name='subject' id='cal_subject' value='" . escape_tags($event["Subject"]) . "'><br>\n";
	$out .= "<label for='cal_allday'>Is All-Day event:</label><input type='checkbox' name='allday' id='cal_allday' " . ($event["IsAllDayEvent"] ? "checked" : "") . "><br>\n";

	$out .= "<label for='cal_startdate'>" . t("Starts") . ":</label>";
	$out .= "<input name='start_date' value='" . $localization->dateformat_datepicker_php(wdcal_mySql2PhpTime($event["StartTime"])) . "' id='cal_start_date'>";
	$out .= "<input name='start_time' value='" . substr($event["StartTime"], 11, 5) . "' id='cal_start_time'>";
	$out .= "<br>\n";

	$out .= "<label for='cal_enddate'>" . t("Ends") . ":</label>";
	$out .= "<input name='end_date' value='" . $localization->dateformat_datepicker_php(wdcal_mySql2PhpTime($event["EndTime"])) . "' id='cal_end_date'>";
	$out .= "<input name='end_time' value='" . substr($event["EndTime"], 11, 5) . "' id='cal_end_time'>";
	$out .= "<br>\n";

	$out .= "<label for='cal_location'>" . t("Location") . ":</label><input name='location' id='cal_location' value='" . escape_tags($event["Location"]) . "'><br>\n";

	$out .= "<label for='event-desc-textarea'>" . t("Description") . ":</label> <textarea id='event-desc-textarea' name='wdcal_desc' style='vertical-align: top; width: 400px; height: 100px;'>" . escape_tags($event["Description"]) . "</textarea>";
	$out .= "<br style='clear: both;'>";

	$out .= "<label for='notification'>" . t('Notification') . ":</label>";
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


	$out .= "<script>\$(function() {
		wdcal_edit_init('" . $localization->dateformat_datepicker_js() . "');
	});</script>";

	$out .= "<input type='submit' name='save' value='Save'></form>";

	return $out;
}


/**
 * @param App $a
 * @return string
 */
function wdcal_getSettingsPage(&$a)
{

	if (!local_user()) {
		notice(t('Permission denied.') . EOL);
		return '';
	}

	if (isset($_REQUEST["save"])) {
		check_form_security_token_redirectOnErr($a->get_baseurl() . '/dav/settings/', 'calprop');
		set_pconfig($a->user["uid"], "dav", "dateformat", $_REQUEST["wdcal_date_format"]);
		info(t('The new values have been saved.'));
	}

	$o = "";

	$o .= "<a href='" . $a->get_baseurl() . "/dav/wdcal/'>" . t("Go back to the calendar") . "</a><br><br>";

	$o .= '<h3>' . t('Calendar Settings') . '</h3>';

	$current_format = wdcal_local::getInstanceByUser($a->user["uid"]);
	$o .= '<form method="POST" action="' . $a->get_baseurl() . '/dav/settings/">';
	$o .= "<input type='hidden' name='form_security_token' value='" . get_form_security_token('calprop') . "'>\n";

	$o .= '<label for="wdcal_date_format">' . t('Date format') . ':</label><select name="wdcal_date_format" id="wdcal_date_format" size="1">';
	$classes = wdcal_local::getInstanceClasses();
	foreach ($classes as $c) {
		$o .= '<option value="' . $c::getID() . '" ';
		if ($c::getID() == $current_format::getID()) $o .= 'selected';
		$o .= '>' . escape_tags($c::getName()) . '</option>';
	}
	$o .= '</select><br>';

	$o .= '<label for="wdcal_time_zone">' . t('Time zone') . ':</label><input id="wdcal_time_zone" value="' . $a->timezone . '" disabled><br>';

	$o .= '<input type="submit" name="save" value="' . t('Save') . '">';
	$o .= '</form>';

	$o .= "<br><h3>" . t("Limitations") . "</h3>";

	$o .= "- The native friendica events are embedded as read-only, half-transparent in the calendar.<br>";

	$o .= "<br><h3>" . t("Warning") . "</h3>";

	$o .= "This plugin still is in a very early stage of development. Expect major bugs!<br>";

	$o .= "<br><h3>" . t("Synchronization (iPhone, Thunderbird Lightning, Android, ...)") . "</h3>";

	$o .= 'This plugin enables synchronization of your dates and contacts with CalDAV- and CardDAV-enabled programs or devices.<br>
		As an example, the instructions how to set up two-way synchronization with an iPhone/iPodTouch are provided below.<br>
		Unfortunately, Android does not have native support for CalDAV or CardDAV, so an app has to be installed.<br>
		On desktops, the Lightning-extension to Mozilla Thunderbird should be able to use this plugin as a backend.<br><br>';

	$o .= '<h4>' . t('Synchronizing this calendar with the iPhone') . '</h4>';

	$o .= "<ul>
	<li>Go to the settings</li>
	<li>Mail, contacts, settings</li>
	<li>Add a new account</li>
	<li>Other...</li>
	<li>Calendar -> CalDAV-Account</li>
	<li><b>Server:</b> " . $a->get_baseurl() . "/dav/ / <b>Username/Password:</b> <em>the same as your friendica-login</em></li>
	</ul>";

	$o .= '<h4>' . t('Synchronizing your Friendica-Contacts with the iPhone') . '</h4>';

	$o .= "<ul>
	<li>Go to the settings</li>
	<li>Mail, contacts, settings</li>
	<li>Add a new account</li>
	<li>Other...</li>
	<li>Contacts -> CardDAV-Account</li>
	<li><b>Server:</b> " . $a->get_baseurl() . "/dav/ / <b>Username/Password:</b> <em>the same as your friendica-login</em></li>
	</ul>";

	return $o;
}

