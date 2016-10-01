<?php


/**
 * @param int $from_version
 *
 * @return array|string[]
 */
function dav_get_update_statements($from_version)
{
    $stms = array();

    if ($from_version == 1) {
        $stms[] = 'ALTER TABLE `dav_calendarobjects`
			ADD `calendar_id` INT NOT NULL AFTER `namespace_id` ,
			ADD `user_temp` INT NOT NULL AFTER `calendar_id` ';
        $stms[] = "ALTER TABLE `dav_calendarobjects`
			ADD `componentType` ENUM( 'VEVENT', 'VTODO' ) NOT NULL DEFAULT 'VEVENT' AFTER `lastmodified` ,
			ADD `firstOccurence` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `componentType` ,
			ADD `lastOccurence` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `firstOccurence`";
        $stms[] = 'UPDATE dav_calendarobjects a JOIN dav_calendars b ON a.namespace = b.namespace AND a.namespace_id = b.namespace_id SET a.user_temp = b.uid';
        $stms[] = 'DROP TABLE IF EXISTS
			`dav_addressbooks_community` ,
			`dav_addressbooks_phone` ,
			`dav_cache_synchronized` ,
			`dav_caldav_log` ,
			`dav_calendars` ,
			`dav_cal_virtual_object_cache` ,
			`dav_cards` ,
			`dav_jqcalendar` ,
			`dav_locks` ,
			`dav_notifications` ;';

        $stms = array_merge($stms, dav_get_create_statements(array('dav_calendarobjects')));

        $user_ids = q('SELECT DISTINCT `uid` FROM %s%scalendars', CALDAV_SQL_DB, CALDAV_SQL_PREFIX);
        foreach ($user_ids as $user) {
            $stms = array_merge($stms, wdcal_create_std_calendars_get_statements($user['uid'], false));
        }

        $stms[] = 'UPDATE dav_calendarobjects a JOIN dav_calendars b
			ON b.`namespace` = ' .CALDAV_NAMESPACE_PRIVATE." AND a.`user_temp` = b.`namespace_id` AND b.`uri` = 'private'
			SET a.`calendar_id` = b.`id`";

        $stms[] = 'ALTER TABLE `dav_calendarobjects` DROP `namespace`, DROP `namespace_id`, DROP `user_temp`';
    }

    if (in_array($from_version, array(1, 2))) {
        $stms[] = "CREATE TABLE IF NOT EXISTS `dav_addressbooks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` mediumint(9) NOT NULL,
  `namespace_id` int(11) unsigned NOT NULL,
  `displayname` varchar(200) NOT NULL,
  `description` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `needs_rebuild` TINYINT NOT NULL DEFAULT '1',
  `uri` varchar(50) NOT NULL,
  `ctag` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

        $stms[] = "CREATE TABLE IF NOT EXISTS `dav_addressbookobjects` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `addressbook_id` int(11) unsigned NOT NULL,
  `contact` int(11) DEFAULT NULL,
  `carddata` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `uri` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastmodified` timestamp NULL DEFAULT NULL,
  `needs_rebuild` tinyint(4) NOT NULL DEFAULT '0',
  `manually_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `etag` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `size` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`addressbook_id`,`contact`),
  KEY `contact` (`contact`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
    }

    return $stms;
}

/**
 * @param array $except
 *
 * @return array
 */
function dav_get_create_statements($except = array())
{
    $arr = array();

    if (!in_array('dav_caldav_log', $except)) {
        $arr[] = 'CREATE TABLE IF NOT EXISTS `dav_caldav_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` mediumint(9) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `user_agent` varchar(100) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `method` varchar(10) NOT NULL,
  `url` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mitglied` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8';
    }

    if (!in_array('dav_calendarobjects', $except)) {
        $arr[] = "CREATE TABLE IF NOT EXISTS `dav_calendarobjects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `calendar_id` int(11) NOT NULL,
  `calendardata` text,
  `uri` varchar(200) NOT NULL,
  `lastmodified` timestamp NULL DEFAULT NULL,
  `componentType` enum('VEVENT','VTODO') NOT NULL DEFAULT 'VEVENT',
  `firstOccurence` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastOccurence` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `etag` varchar(15) NOT NULL,
  `size` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`uri`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
    }

    if (!in_array('dav_calendars', $except)) {
        $arr[] = "CREATE TABLE IF NOT EXISTS `dav_calendars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` mediumint(9) NOT NULL,
  `namespace_id` int(10) unsigned NOT NULL,
  `calendarorder` int(11) NOT NULL DEFAULT '1',
  `calendarcolor` char(6) NOT NULL DEFAULT '5858FF',
  `displayname` varchar(200) NOT NULL,
  `timezone` text NOT NULL,
  `description` varchar(500) NOT NULL,
  `uri` varchar(50) NOT NULL DEFAULT '',
  `has_vevent` tinyint(4) NOT NULL DEFAULT '1',
  `has_vtodo` tinyint(4) NOT NULL DEFAULT '1',
  `ctag` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`namespace` , `namespace_id` , `uri`),
  KEY `uri` (`uri`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
    }

    if (!in_array('dav_cal_virtual_object_cache', $except)) {
        $arr[] = "CREATE TABLE IF NOT EXISTS `dav_cal_virtual_object_cache` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `calendar_id` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_uri` char(80) NOT NULL,
  `data_summary` varchar(1000) NOT NULL,
  `data_location` varchar(1000) NOT NULL,
  `data_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_allday` tinyint(4) NOT NULL,
  `data_type` varchar(20) NOT NULL,
  `calendardata` text NOT NULL,
  `size` int(11) NOT NULL,
  `etag` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `data_uri` (`data_uri`),
  KEY `ref_type` (`calendar_id`,`data_end`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
    }

    if (!in_array('dav_cal_virtual_object_sync', $except)) {
        $arr[] = 'CREATE TABLE IF NOT EXISTS `dav_cal_virtual_object_sync` (
  `calendar_id` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8';
    }

    if (!in_array('dav_jqcalendar', $except)) {
        $arr[] = 'CREATE TABLE IF NOT EXISTS `dav_jqcalendar` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ical_recurr_uri` varchar(100) DEFAULT NULL,
  `calendar_id` int(10) unsigned NOT NULL,
  `calendarobject_id` int(10) unsigned NOT NULL,
  `Summary` varchar(100) NOT NULL,
  `StartTime` timestamp NULL DEFAULT NULL,
  `EndTime` timestamp NULL DEFAULT NULL,
  `IsEditable` tinyint(3) unsigned NOT NULL,
  `IsAllDayEvent` tinyint(4) NOT NULL,
  `IsRecurring` tinyint(4) NOT NULL,
  `Color` char(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calendarByStart` (`calendar_id`,`StartTime`),
  KEY `calendarobject_id` (`calendarobject_id`,`ical_recurr_uri`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8';
    }

    if (!in_array('dav_notifications', $except)) {
        $arr[] = "CREATE TABLE IF NOT EXISTS `dav_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ical_recurr_uri` varchar(100) DEFAULT NULL,
  `calendar_id` int(11) NOT NULL,
  `calendarobject_id` int(10) unsigned NOT NULL,
  `action` enum('email','display') NOT NULL DEFAULT 'email',
  `alert_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notified` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `notified` (`notified`,`alert_date`),
  KEY `calendar_id` (`calendar_id`,`calendarobject_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
    }

    if (!in_array('dav_addressbooks', $except)) {
        $arr[] = "CREATE TABLE IF NOT EXISTS `dav_addressbooks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` mediumint(9) NOT NULL,
  `namespace_id` int(11) unsigned NOT NULL,
  `displayname` varchar(200) NOT NULL,
  `description` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `needs_rebuild` TINYINT NOT NULL DEFAULT '1',
  `uri` varchar(50) NOT NULL,
  `ctag` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
    }

    if (!in_array('dav_addressbookobjects', $except)) {
        $arr[] = "CREATE TABLE IF NOT EXISTS `dav_addressbookobjects` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `addressbook_id` int(11) unsigned NOT NULL,
  `contact` int(11) DEFAULT NULL,
  `carddata` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `uri` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastmodified` timestamp NULL DEFAULT NULL,
  `needs_rebuild` tinyint(4) NOT NULL DEFAULT '0',
  `manually_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `etag` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `size` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`addressbook_id`,`contact`),
  KEY `contact` (`contact`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
    }

    return $arr;
}

/**
 * @return int
 */
function dav_check_tables()
{
    $x = q('DESCRIBE %s%scalendars', CALDAV_SQL_DB, CALDAV_SQL_PREFIX);
    if (!$x) {
        return -1;
    }
    if (count($x) == 9) {
        return 1;
    } // Version 0.1

    $x2 = q("show tables like '%s%saddressbooks'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX);
    if (!$x2 || count($x2) == 0) {
        return 2;
    } // Version 0.2

    if (count($x) == 12) {
        return 0;
    } // Correct

    return -2; // Unknown version
}

/**
 * @return array
 */
function dav_create_tables()
{
    $stms = dav_get_create_statements();
    $errors = array();

    global $db;
    foreach ($stms as $st) { // @TODO Friendica-dependent
        $db->q($st);
        if ($db->error) {
            $errors[] = $db->error;
        }
    }

    return $errors;
}

/**
 * @return array
 */
function dav_upgrade_tables()
{
    $ver = dav_check_tables();
    if (!in_array($ver, array(1, 2))) {
        return array('Unknown error');
    }
    $stms = dav_get_update_statements($ver);

    $errors = array();
    global $db;
    foreach ($stms as $st) { // @TODO Friendica-dependent
        $db->q($st);
        if ($db->error) {
            $errors[] = $db->error;
        }
    }

    return $errors;
}
