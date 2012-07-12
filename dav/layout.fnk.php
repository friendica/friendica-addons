<?php


/**
 *
 */
function wdcal_addRequiredHeaders()
{
	$a = get_app();

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/jqueryui/jquery-ui-1.8.21.custom.css' . '" media="all" />' . "\r\n";
	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/jqueryui/jquery-ui-1.8.21.custom.min.js"></script>' . "\r\n";

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/colorpicker/colorPicker.css' . '" media="all" />' . "\r\n";
	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/colorpicker/jquery.colorPicker.min.js"></script>' . "\r\n";

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/timepicker/timePicker.css' . '" media="all" />' . "\r\n";
	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/timepicker/jquery.timePicker.min.js"></script>' . "\r\n";

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/wdcal.css' . '" media="all" />' . "\r\n";
	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/common/wdcal.js"></script>' . "\r\n";

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/wdcal/css/calendar.css' . '" media="all" />' . "\r\n";
	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dav/wdcal/css/main.css' . '" media="all" />' . "\r\n";

	switch (get_config("system", "language")) {
		case "de":
			$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/common/wdcal/js/wdCalendar_lang_DE.js"></script>' . "\r\n";
			$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/jqueryui/jquery.ui.datepicker-de.js"></script>' . "\r\n";
			break;
		default:
			$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/common/wdcal/js/wdCalendar_lang_EN.js"></script>' . "\r\n";
	}

	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/common/wdcal/js/jquery.calendar.js"></script>' . "\r\n";
	$a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/dav/common/wdcal/js/main.js"></script>' . "\r\n";
}



/**
 * @param array|int[] $calendars
 */
function wdcal_print_user_ics($calendars = array())
{
	$add = "";
	if (count($calendars) > 0) {
		$c = array();
		foreach ($calendars as $i) $c[] = IntVal($i);
		$add = " AND `id` IN (" . implode(", ", $c) . ")";
	}

	$a = get_app();
	header("Content-type: text/plain");

	$str  = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Friendica//DAV-Plugin//EN\r\n";
	$cals = q("SELECT * FROM %s%scalendars WHERE `namespace` = %d AND `namespace_id` = %d %s", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CALDAV_NAMESPACE_PRIVATE, $a->user["uid"], $add);
	if (count($cals) > 0) {
		$ids = array();
		foreach ($cals as $c) $ids[] = IntVal($c["id"]);
		$objs = q("SELECT * FROM %s%scalendarobjects WHERE `calendar_id` IN (" . implode(", ", $ids) . ") ORDER BY `firstOccurence`", CALDAV_SQL_DB, CALDAV_SQL_PREFIX);

		foreach ($objs as $obj) {
			preg_match("/BEGIN:VEVENT(.*)END:VEVENT/siu", $obj["calendardata"], $matches);
			$str2 = preg_replace("/([^\\r])\\n/siu", "\\1\r\n", $matches[0]);
			$str2 = preg_replace("/MAILTO:.*[^:a-z0-9_\+äöüß\\n\\n@-]+.*(:|\\r\\n[^ ])/siU", "\\1", $str2);
			$str .= $str2 . "\r\n";
		}
	}
	$str .= "END:VCALENDAR\r\n";

	echo $str;
	killme();
}


/**
 * @param array|Sabre_CalDAV_Calendar[] $calendars
 * @param array|int[] $calendars_selected
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
function wdcal_printCalendar($calendars, $calendars_selected, $data_feed_url, $view = "week", $theme = 0, $height_diff = 175, $readonly = false, $curr_day = "", $add_params = array(), $show_nav = true)
{

	$a            = get_app();
	$localization = wdcal_local::getInstanceByUser($a->user["uid"]);

	if (count($calendars_selected) == 0) foreach ($calendars as $c) {
		$prop                 = $c->getProperties(array("id"));
		$calendars_selected[] = $prop["id"];
	}

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

	foreach ($calendars as $cal) {
		$cal_id = $cal->getProperties(array("id", DAV_DISPLAYNAME));
		$x .= '<label style="margin-left: 10px; margin-right: 10px;"><input type="checkbox" name="cals[]" value="' . $cal_id["id"] . '"';
		$found = false;
		foreach ($calendars_selected as $pre) if ($pre["id"] == $cal_id["id"]) $found = true;
		if ($found) $x .= ' checked';
		$x .= '> ' . escape_tags($cal_id[DAV_DISPLAYNAME]) . '</label> ';
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
 * @param int $calendar_id
 * @param int $calendarobject_id
 * @param string $recurr_uri
 * @return string
 */
function wdcal_getDetailPage($calendar_id, $calendarobject_id, $recurr_uri)
{
	$a = get_app();

	try {
		$details = null;
		$server  = dav_create_server(true, true, false);
		$cal     = dav_get_current_user_calendar_by_id($server, $calendar_id, DAV_ACL_READ);
		$obj     = Sabre_CalDAV_Backend_Common::loadCalendarobjectById($calendarobject_id);
		dav_get_current_user_calendarobject($server, $cal, $obj["uri"], DAV_ACL_READ); // Check permissions

		$calbackend = wdcal_calendar_factory_by_id($calendar_id);
		$redirect   = $calbackend->getItemDetailRedirect($calendar_id, $calendarobject_id);

		if ($redirect !== null) goaway($a->get_baseurl() . $redirect);

		$details = $obj;
	} catch (Exception $e) {
		info(t("Error") . ": " . $e);
		goaway($a->get_baseurl() . "/dav/wdcal/");
	}

	return print_r($details, true);
}


/**
 * @param int $calendar_id
 * @param int $uri
 * @param string $recurr_uri
 * @return string
 */
function wdcal_getEditPage($calendar_id, $uri, $recurr_uri = "")
{
	$a            = get_app();
	$localization = wdcal_local::getInstanceByUser($a->user["uid"]);

	return wdcal_getEditPage_str($localization, $a->get_baseurl(), $a->user["uid"], $calendar_id, $uri, $recurr_uri);
}

/**
 * @return string
 */
function wdcal_getNewPage()
{
	$a            = get_app();
	$localization = wdcal_local::getInstanceByUser($a->user["uid"]);

	return wdcal_getEditPage_str($localization, $a->get_baseurl(), $a->user["uid"], 0, 0);
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

	if (isset($_REQUEST["save_cals"])) {
		check_form_security_token_redirectOnErr($a->get_baseurl() . '/dav/settings/', 'calprop');

		$r = q("SELECT * FROM %s%scalendars WHERE `namespace` = " . CALDAV_NAMESPACE_PRIVATE . " AND `namespace_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($a->user["uid"]));
		foreach ($r as $cal) {
			$backend = wdcal_calendar_factory($cal["namespace"], $cal["namespace_id"], $cal["uri"], $cal);
			$change_sql = "";
			$col = substr($_REQUEST["color"][$cal["id"]], 1);
			if (strtolower($col) != strtolower($cal["calendarcolor"])) $change_sql .= ", `calendarcolor` = '" . dbesc($col) . "'";
			if (!is_subclass_of($backend, "Sabre_CalDAV_Backend_Virtual")) {
				if ($_REQUEST["uri"][$cal["id"]] != $cal["uri"]) $change_sql .= ", `uri` = '" . dbesc($_REQUEST["uri"][$cal["id"]]) . "'";
				if ($_REQUEST["name"][$cal["id"]] != $cal["displayname"]) $change_sql .= ", `displayname` = '" . dbesc($_REQUEST["name"][$cal["id"]]) . "'";
			}
			if ($change_sql != "") {
				q("UPDATE %s%scalendars SET `ctag` = `ctag` + 1 $change_sql WHERE `id` = %d AND `namespace_id` = %d AND `namespace_id` = %d",
					CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $cal["id"], CALDAV_NAMESPACE_PRIVATE, IntVal($a->user["uid"]));
				info(t('The calendar has been updated.'));
			}
		}

		if (isset($_REQUEST["uri"]["new"]) && $_REQUEST["uri"]["new"] != "" && $_REQUEST["name"]["new"] && $_REQUEST["name"]["new"] != "") {
			$order = q("SELECT MAX(`calendarorder`) ord FROM %s%scalendars WHERE `namespace_id` = %d AND `namespace_id` = %d",
				CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CALDAV_NAMESPACE_PRIVATE, IntVal($a->user["uid"]));
			$neworder = $order[0]["ord"] + 1;
			q("INSERT INTO %s%scalendars (`namespace`, `namespace_id`, `calendarorder`, `calendarcolor`, `displayname`, `timezone`, `uri`, `has_vevent`, `ctag`)
				VALUES (%d, %d, %d, '%s', '%s', '%s', '%s', 1, 1)",
				CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CALDAV_NAMESPACE_PRIVATE, IntVal($a->user["uid"]), $neworder, dbesc(strtolower(substr($_REQUEST["color"]["new"], 1))),
				dbesc($_REQUEST["name"]["new"]), dbesc($a->timezone), dbesc($_REQUEST["uri"]["new"])
			);
			info(t('The new calendar has been created.'));
		}
	}

	if (isset($_REQUEST["remove_cal"])) {
		check_form_security_token_redirectOnErr($a->get_baseurl() . '/dav/settings/', 'del_cal', 't');

		$c = q("SELECT * FROM %s%scalendars WHERE `id` = %d AND `namespace_id` = %d AND `namespace_id` = %d",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($_REQUEST["remove_cal"]), CALDAV_NAMESPACE_PRIVATE, IntVal($a->user["uid"]));
		if (count($c) != 1) killme();

		$calobjs = q("SELECT `id` FROM %s%scalendarobjects WHERE `calendar_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($_REQUEST["remove_cal"]));

		$newcal = q("SELECT * FROM %s%scalendars WHERE `id` != %d AND `namespace_id` = %d AND `namespace_id` = %d ORDER BY `calendarcolor` LIMIT 0,1",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($_REQUEST["remove_cal"]), CALDAV_NAMESPACE_PRIVATE, IntVal($a->user["uid"]));
		if (count($newcal) != 1) killme();

		q("UPDATE %s%scalendarobjects SET `calendar_id` = %d WHERE `calendar_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($newcal[0]["id"]), IntVal($c[0]["id"]));

		foreach ($calobjs as $calobj) renderCalDavEntry_calobj_id($calobj["id"]);

		q("DELETE FROM %s%scalendars WHERE `id` = %s", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($_REQUEST["remove_cal"]));
		q("UPDATE %s%scalendars SET `ctag` = `ctag` + 1 WHERE `id` = " . CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $newcal[0]["id"]);

		info(t('The calendar has been deleted.'));
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


	$o .= '<br><br><h3>' . t('Calendars') . '</h3>';
	$o .= '<form method="POST" action="' . $a->get_baseurl() . '/dav/settings/">';
	$o .= "<input type='hidden' name='form_security_token' value='" . get_form_security_token('calprop') . "'>\n";
	$o .= "<table><tr><th>Type</th><th>Color</th><th>Name</th><th>URI (for CalDAV)</th></tr>";

	$r = q("SELECT * FROM %s%scalendars WHERE `namespace` = " . CALDAV_NAMESPACE_PRIVATE . " AND `namespace_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($a->user["uid"]));
	$private_max = 0;
	$num_non_virtual = 0;
	foreach ($r as $x) {
		$backend = wdcal_calendar_factory($x["namespace"], $x["namespace_id"], $x["uri"], $x);
		if (!is_subclass_of($backend, "Sabre_CalDAV_Backend_Virtual")) $num_non_virtual++;
	}
	foreach ($r as $x) {
		$p = explode("private-", $x["uri"]);
		if (count($p) == 2 && $p[1] > $private_max) $private_max = $p[1];

		$backend = wdcal_calendar_factory($x["namespace"], $x["namespace_id"], $x["uri"], $x);
		$disabled = (is_subclass_of($backend, "Sabre_CalDAV_Backend_Virtual") ? "disabled" : "");
		$o .= "<tr>";
		$o .= "<td style='padding: 2px;'>" . escape_tags($backend->getBackendTypeName()) . "</td>";
		$o .= "<td style='padding: 2px; text-align: center;'><input style='margin-left: 10px; width: 70px;' class='cal_color' name='color[" . $x["id"] . "]' id='cal_color_" . $x["id"] . "' value='#" . (strlen($x["calendarcolor"]) != 6 ? "5858ff" : escape_tags($x["calendarcolor"])) . "'></td>";
		$o .= "<td style='padding: 2px;'><input style='margin-left: 10px;' name='name[" . $x["id"] . "]' value='" . escape_tags($x["displayname"]) . "' $disabled></td>";
		$o .= "<td style='padding: 2px;'><input style='margin-left: 10px; width: 150px;' name='uri[" . $x["id"] . "]' value='" . escape_tags($x["uri"]) . "' $disabled></td>";
		$o .= "<td style='padding: 2px;'>";
		if (!is_subclass_of($backend, "Sabre_CalDAV_Backend_Virtual") && $num_non_virtual > 1) $o .= "<a href='" . $a->get_baseurl() . "/dav/settings/?remove_cal=" . $x["id"] . "&amp;t=" . get_form_security_token("del_cal") . "' class='delete_cal'>Delete</a>";
		$o .= "</td>\n";
		$o .= "</tr>\n";
	}

	$private_max++;
	$o .= "<tr class='cal_add_row' style='display: none;'>";
	$o .= "<td style='padding: 2px;'>" . escape_tags(Sabre_CalDAV_Backend_Private::getBackendTypeName()) . "</td>";
	$o .= "<td style='padding: 2px; text-align: center;'><input style='margin-left: 10px; width: 70px;' class='cal_color' name='color[new]' id='cal_color_new' value='#5858ff'></td>";
	$o .= "<td style='padding: 2px;'><input style='margin-left: 10px;' name='name[new]' value='Another calendar'></td>";
	$o .= "<td style='padding: 2px;'><input style='margin-left: 10px; width: 150px;' name='uri[new]' value='private-${private_max}'></td>";
	$o .= "</tr>\n";

	$o .= "</table>";
	$o .= "<div style='text-align: center;'>[<a href='#' class='calendar_add_caller'>" . t("Create a new calendar") . "</a>]</div>";
	$o .= '<input type="submit" name="save_cals" value="' . t('Save') . '">';
	$o .= '</form>';
	$baseurl = $a->get_baseurl();
	$o .= "<script>\$(function() {
		wdcal_edit_calendars_start('" . $current_format->dateformat_datepicker_js() . "', '${baseurl}/dav/');
	});</script>";


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

