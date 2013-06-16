<?php
/********************************************************************
 * Name: Calendar Export
 * Description: This addon exports the public events of your users as calendar files
 * Version: 0.1
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 * License: MIT
 * ******************************************************************/


function cal_install()
{
    register_hook('plugin_settings', 'addon/cal/cal.php', 'cal_addon_settings');
    register_hook('plugin_settings_post', 'addon/cal/cal.php', 'cal_addon_settings_post');
}
function cal_uninstall()
{
    unregister_hook('plugin_settings', 'addon/cal/cal.php', 'cal_addon_settings');
    unregister_hook('plugin_settings_post', 'addon/cal/cal.php', 'cal_addon_settings_post');
}
function cal_module()
{
}
/*  pathes
 *  /cal/$user/export/$
 *  currently supported format is ical (iCalendar
 */
function cal_content()
{
    $a = get_app();
    $o = "";
    if ($a->argc == 1) {
    $o .= "<h3>".t('Event Export')."</h3><p>".t('You can download public events from: ').$a->get_baseurl()."/cal/username/export/ical</p>";
    } elseif ($a->argc==4) {
	//  get the parameters from the request we just received
	$username = $a->argv[1];
	$do = $a->argv[2];
	$format = $a->argv[3];
	//  check that there is a user matching the requested profile
	$r = q("SELECT uid FROM user WHERE nickname='".$username."' LIMIT 1;");
	if (count($r)) 
	{
	    $uid = $r[0]['uid'];
	} else {
	    killme();
	}
	//  if we shall do anything other then export, end here
	if (! $do == 'export' )
	    killme();
	//  check if the user allows us to share the profile
	$enable = get_pconfig( $uid, 'cal', 'enable');
	if (! $enable == 1) {
	    info(t('The user does not export the calendar.'));
	    return;
	}
	//  we are allowed to show events
	//  get the timezone the user is in
	$r = q("SELECT timezone FROM user WHERE uid=".$uid." LIMIT 1;");
	if (count($r))
	    $timezone = $r[0]['timezone'];
	//  does the user who requests happen to be the owner of the events 
	//  requested? then show all of your events, otherwise only those that 
	//  don't have limitations set in allow_cid and allow_gid
	if (local_user() == $uid) {
	    $r = q("SELECT `start`, `finish`, `adjust`, `summary`, `desc`, `location` FROM `event` WHERE `uid`=".$uid.";");
	} else {
	    $r = q("SELECT `start`, `finish`, `adjust`, `summary`, `desc`, `location` FROM `event` WHERE `allow_cid`='' and `allow_gid`='' and `uid`='".$uid."';");
	}
	//  we have the events that are available for the requestor
	//  now format the output according to the requested format
	$res = cal_format_output($r, $format, $timezone);
	if (! $res=='')
	    info($res);
    } else {
	//  wrong number of parameters
	killme();
    }
    return $o;
}

function cal_format_output ($r, $f, $tz)
{
    $res = t('This calendar format is not supported');;
    switch ($f)
    {
	//  format the exported data as a CSV file
	case "csv":
	    header("Content-type: text/csv");
	    $o = '"Subject", "Start Date", "Start Time", "Description", "End Date", "End Time", "Location"' . PHP_EOL;
	    foreach ($r as $rr) {
		$tmp1 = strtotime($rr['start']);
		$tmp2 = strtotime($rr['finish']);
		$time_format = "%H:%M:%S";
		$date_format = "%d.%m.%Y";
		$o .= '"'.$rr['summary'].'", "'.strftime($date_format, $tmp1) .
		    '", "'.strftime($time_format, $tmp1).'", "'.$rr['desc'] .
		    '", "'.strftime($date_format, $tmp2) .
		    '", "'.strftime($time_format, $tmp2) . 
		    '", "'.$rr['location'].'"' . PHP_EOL;
	    }
	    echo $o;
	    killme();

	case "ical":
	    header("Content-type: text/ics");
	    $res = '';
	    $o = 'BEGIN:VCALENDAR'. PHP_EOL
		. 'PRODID:-//friendica calendar export//0.1//EN' . PHP_EOL
		. 'VERSION:2.0' . PHP_EOL;
//  TODO include timezone informations in cases were the time is not in UTC
//		. 'BEGIN:VTIMEZONE' . PHP_EOL
//		. 'TZID:' . $tz . PHP_EOL
//		. 'END:VTIMEZONE' . PHP_EOL;
	    foreach ($r as $rr) {
		if ($rr['adjust'] == 1) {
		    $UTC = 'Z';
		} else { 
		   $UTC = '';
		}
		$o .= 'BEGIN:VEVENT' . PHP_EOL;
		if ($rr[start]) {
		    $tmp = strtotime($rr['start']);
		    $dtformat = "%Y%m%dT%H%M%S".$UTC;
		    $o .= 'DTSTART:'.strftime($dtformat, $tmp).PHP_EOL;
		}
		if ($rr['finish']) {
		    $tmp = strtotime($rr['finish']);
		    $dtformat = "%Y%m%dT%H%M%S".$UTC;
		    $o .= 'DTEND:'.strftime($dtformat, $tmp).PHP_EOL;
		}
		if ($rr['summary'])
		    $tmp = $rr['summary'];
		    $tmp = str_replace(PHP_EOL, PHP_EOL.' ',$tmp);
		    $o .= 'SUMMARY:' . $tmp . PHP_EOL;
		if ($rr['desc'])
		    $tmp = $rr['desc'];
		    $tmp = str_replace(PHP_EOL, PHP_EOL.' ',$tmp);
		    $o .= 'DESCRIPTION:' . $tmp . PHP_EOL;
		if ($rr['location']) {
		    $tmp = $rr['location'];
		    $tmp = str_replace(PHP_EOL, PHP_EOL.' ',$tmp);
		    $o .= 'LOCATION:' . $tmp . PHP_EOL;
		}
		$o .= 'END:VEVENT' . PHP_EOL;
	    }
	    $o .= 'END:VCALENDAR' . PHP_EOL;
	    echo $o;
	    killme();
    }
    return $res;
}

function cal_addon_settings_post ( &$a, &$b  )
{
    if (! local_user())
	return;

    if (!x($_POST,'cal-submit'))
	return;

    set_pconfig(local_user(),'cal','enable',intval($_POST['cal-enable']));
}
function cal_addon_settings ( &$a, &$s  )
{
    if (! local_user())
	return;

    $enabled = get_pconfig(local_user(), 'cal', 'enable');
    $checked = (($enabled) ? ' checked="checked" ' : '');
    $url = $a->get_baseurl().'/cal/'.$a->user['nickname'].'/export/ical';

    $s .= '<h3>'.t('Export Events').'</h3>';
    $s .= '<p>'.t('If this is enabled, you public events will be available at').' <strong>'.$url.'</strong></p>';
    $s .= '<div id="cal-enable-wrapper">';
    $s .= '<label id="cal-enable-label" for="cal-checkbox">'. t('Enable calendar export') .'</label>';
    $s .= '<input id="cal-checkbox" type="checkbox" name="cal-enable" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';
    $s .= '<div class="settings-submit-wrapper" ><input type="submit" name="cal-submit" class="settings-submit" value="' . t('Submit') . '" /></div>'; 
    $s .= '</div><div class="clear"></div>';

}

?>
