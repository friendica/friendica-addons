<?php


/**
 * @param int $from_version
 * @return array|string[]
 */
function dav_get_update_statements($from_version)
{
	$stms = array();

	if ($from_version <= 0) {
		$stms[] = "ALTER TABLE `dav_calendars` ADD `uri` VARCHAR( 50 ) NULL DEFAULT NULL AFTER `description` , ADD `has_vevent` TINYINT NOT NULL DEFAULT '1' AFTER `uri` , ADD `has_vtodo` TINYINT NOT NULL DEFAULT '1' AFTER `has_vevent`";

		$stms[] = "UPDATE `dav_calendars` SET `uri` = 'private' WHERE `namespace` = 1";
		$stms[] = "UPDATE `dav_calendars` SET `uri` = 'friendica-mine' WHERE `namespace` = 2 AND `namespace_id` = 1";
		$stms[] = "UPDATE `dav_calendars` SET `uri` = 'friendica-contacts' WHERE `namespace` = 2 AND `namespace_id` = 2";
		$stms[] = "ALTER TABLE `dav_calendars` DROP PRIMARY KEY ";
		$stms[] = "ALTER TABLE `dav_calendars` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ";

		$stms[] = "ALTER TABLE `dav_calendarobjects` ADD `calendar_id` INT NOT NULL AFTER `id` ";
		$stms[] = "UPDATE `dav_calendarobjects` a JOIN `dav_calendars` b ON a.`namespace` = b.`namespace` AND a.`namespace_id` = b.`namespace_id` SET a.`calendar_id` = b.`id`";
		$stms[] = "ALTER TABLE `dav_calendarobjects` DROP `namespace` , DROP `namespace_id` ;";
		$stms[] = "ALTER TABLE `dav_calendarobjects` ADD INDEX ( `calendar_id` ) ";
		$stms[] = "ALTER TABLE `dav_calendarobjects` ADD `componentType` ENUM( 'VEVENT', 'VTODO' ) NOT NULL DEFAULT 'VEVENT' AFTER `calendardata` ,
		 	ADD `firstOccurence` TIMESTAMP NOT NULL AFTER `lastmodified` ,
			ADD `lastOccurence` TIMESTAMP NOT NULL AFTER `firstOccurence`";

		$stms[] = "DROP TABLE `dav_jqcalendar`";
		$stms[] = "CREATE TABLE IF NOT EXISTS `dav_jqcalendar` (
  			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  			`ical_recurr_uri` varchar(100) DEFAULT NULL,
  			`calendar_id` int(10) unsigned NOT NULL,
  			`calendarobject_id` int(10) unsigned NOT NULL,
  			`Subject` varchar(1000) NOT NULL,
  			`StartTime` timestamp NULL DEFAULT NULL,
  			`EndTime` timestamp NULL DEFAULT NULL,
  			`IsEditable` tinyint(3) unsigned NOT NULL,
  			`IsAllDayEvent` tinyint(4) NOT NULL,
  			`IsRecurring` tinyint(4) NOT NULL,
  			`Color` CHAR(6) DEFAULT NULL,
  			PRIMARY KEY (`id`),
  			KEY `calendarByStart` (`calendar_id`,`StartTime`),
  			KEY `calendarobject_id` (`calendarobject_id`,`ical_recurr_uri`)
			) DEFAULT CHARSET=utf8 ";


		$stms[] = "ALTER TABLE `dav_notifications` ADD `calendar_id` INT NOT NULL AFTER `ical_recurr_uri` ";
		$stms[] = "ALTER TABLE `dav_notifications` DROP INDEX `ical_uri` , ADD INDEX `ical_uri` ( `calendar_id` , `ical_uri` , `ical_recurr_uri` ) ";
		$stms[] = "TRUNCATE TABLE `dav_notifications`";
		$stms[] = "ALTER TABLE `dav_notifications` DROP `namespace` , DROP `namespace_id`";

		$stms[] = "TRUNCATE TABLE `dav_cal_virtual_object_cache`";
		$stme[] = "ALTER TABLE `dav_cal_virtual_object_cache` ADD `calendar_id` INT UNSIGNED NOT NULL AFTER `id` ";
		$stms[] = "ALTER TABLE `dav_cal_virtual_object_cache` DROP INDEX `ref_type` , ADD INDEX `ref_type` ( `calendar_id` , `data_end` )  ";
		$stms[] = "ALTER TABLE `dav_cal_virtual_object_cache` DROP `uid`, DROP `namespace` , DROP `namespace_id` ";

		$stms[] = "CREATE TABLE `friendica`.`dav_cal_virtual_object_sync` (
			`calendar_id` INT UNSIGNED NOT NULL ,
			`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
			PRIMARY KEY ( `calendar_id` )
			) ENGINE=MyISAM DEFAULT CHARSET=utf8";

		$stms[] = "DROP TABLE `dav_cache_synchronized` ";

		$stms[] = "UPDATE `dav_calendars` SET `namespace` = 1, `namespace_id` = `uid`"; // last
		$stms[] = "ALTER TABLE `dav_calendars` DROP `uid`";
	}

	return $stms;
}

/**
 * @return array
 */
function dav_get_create_statements()
{
	$arr = array();

	$arr[] = "CREATE TABLE IF NOT EXISTS `dav_addressbooks_community` (
		`uid` int(11) NOT NULL,
  `ctag` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

	$arr[] = "CREATE TABLE IF NOT EXISTS `dav_addressbooks_phone` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `principaluri` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `displayname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uri` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `ctag` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `principaluri` (`principaluri`,`uri`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

	$arr[] = "CREATE TABLE IF NOT EXISTS `dav_cache_synchronized` (
		`uid` mediumint(8) unsigned NOT NULL,
  `namespace` smallint(6) NOT NULL,
  `namespace_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`,`namespace`,`namespace_id`),
  KEY `namespace` (`namespace`,`namespace_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

	$arr[] = "CREATE TABLE IF NOT EXISTS `dav_caldav_log` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` mediumint(9) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `user_agent` varchar(100) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `method` varchar(10) NOT NULL,
  `url` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mitglied` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

	$arr[] = "CREATE TABLE IF NOT EXISTS `dav_calendarobjects` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` mediumint(9) NOT NULL,
  `namespace_id` int(10) unsigned NOT NULL,
  `calendardata` text,
  `uri` varchar(200) NOT NULL,
  `lastmodified` timestamp NULL DEFAULT NULL,
  `etag` varchar(15) NOT NULL,
  `size` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`uri`,`namespace`,`namespace_id`),
  KEY `namespace` (`namespace`,`namespace_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

	$arr[] = "CREATE TABLE IF NOT EXISTS `dav_calendars` (
		`namespace` mediumint(9) NOT NULL,
  `namespace_id` int(10) unsigned NOT NULL,
  `uid` mediumint(8) unsigned NOT NULL,
  `calendarorder` int(11) NOT NULL DEFAULT '1',
  `calendarcolor` varchar(20) NOT NULL DEFAULT '#5858FF',
  `displayname` varchar(200) NOT NULL,
  `timezone` text NOT NULL,
  `description` varchar(500) NOT NULL,
  `uri` varchar(50) DEFAULT NULL,
  `has_vevent` tinyint(4) NOT NULL DEFAULT '1',
  `has_vtodo` tinyint(4) NOT NULL DEFAULT '1',
  `ctag` int(10) unsigned NOT NULL,
  PRIMARY KEY (`namespace`,`namespace_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

	$arr[] = "CREATE TABLE IF NOT EXISTS `dav_cal_virtual_object_cache` (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) unsigned NOT NULL,
  `namespace` mediumint(9) NOT NULL,
  `namespace_id` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_uri` char(80) NOT NULL,
  `data_subject` varchar(1000) NOT NULL,
  `data_location` varchar(1000) NOT NULL,
  `data_description` text NOT NULL,
  `data_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_allday` tinyint(4) NOT NULL,
  `data_type` varchar(20) NOT NULL,
  `ical` text NOT NULL,
  `ical_size` int(11) NOT NULL,
  `ical_etag` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ref_type` (`namespace`,`namespace_id`),
  KEY `mitglied` (`uid`,`data_end`),
  KEY `data_uri` (`data_uri`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

	$arr[] = "CREATE TABLE IF NOT EXISTS `dav_cards` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` tinyint(3) unsigned NOT NULL,
  `namespace_id` int(11) unsigned NOT NULL,
  `contact` int(11) DEFAULT NULL,
  `carddata` mediumtext COLLATE utf8_unicode_ci,
  `uri` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastmodified` int(11) unsigned DEFAULT NULL,
  `manually_edited` tinyint(4) NOT NULL DEFAULT '0',
  `manually_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `etag` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `size` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`namespace`,`namespace_id`,`contact`),
  KEY `contact` (`contact`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

	$arr[] = "CREATE TABLE IF NOT EXISTS `dav_jqcalendar` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `ical_uri` varchar(200) NOT NULL,
  `ical_recurr_uri` varchar(100) NOT NULL,
  `namespace` mediumint(9) NOT NULL,
  `namespace_id` int(11) NOT NULL,
  `permission_edit` tinyint(4) NOT NULL DEFAULT '1',
  `Subject` varchar(1000) DEFAULT NULL,
  `Location` varchar(1000) DEFAULT NULL,
  `Description` text,
  `StartTime` timestamp NULL DEFAULT NULL,
  `EndTime` timestamp NULL DEFAULT NULL,
  `IsAllDayEvent` smallint(6) NOT NULL,
  `Color` varchar(20) DEFAULT NULL,
  `RecurringRule` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`uid`,`StartTime`),
  KEY `zuord_typ` (`namespace`,`namespace_id`),
  KEY `ical_uri` (`ical_uri`,`ical_recurr_uri`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

	$arr[] = "CREATE TABLE IF NOT EXISTS `dav_locks` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner` varchar(100) DEFAULT NULL,
  `timeout` int(10) unsigned DEFAULT NULL,
  `created` int(11) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `scope` tinyint(4) DEFAULT NULL,
  `depth` tinyint(4) DEFAULT NULL,
  `uri` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";


	$arr[] = "CREATE TABLE IF NOT EXISTS `dav_notifications` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `ical_uri` varchar(200) NOT NULL,
  `ical_recurr_uri` varchar(100) NOT NULL,
  `namespace` mediumint(8) unsigned NOT NULL,
  `namespace_id` int(10) unsigned NOT NULL,
  `alert_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rel_type` enum('second','minute','hour','day','week','month','year') NOT NULL,
  `rel_value` mediumint(9) NOT NULL,
  `notified` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `notified` (`notified`,`alert_date`),
  KEY `ical_uri` (`uid`,`ical_uri`,`ical_recurr_uri`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

	return $arr;
}

/**
 * @return int
 */
function dav_check_tables()
{
	$dbv = get_config("dav", "db_version");
	if ($dbv == CALDAV_DB_VERSION) return 0; // Correct
	if (is_numeric($dbv) || $dbv == "CALDAV_DB_VERSION") return 1; // Older version (update needed)
	return -1; // Not installed
}


/**
 * @return array
 */
function dav_create_tables()
{
	$stms   = dav_get_create_statements();
	$errors = array();

	global $db;
	foreach ($stms as $st) {
		$db->q($st);
		if ($db->error) $errors[] = $db->error;
	}

	if (count($errors) == 0) set_config("dav", "db_version", CALDAV_DB_VERSION);

	return $errors;
}

/**
 * @return array
 */
function dav_upgrade_tables()
{
	$dbv = get_config("dav", "db_version");
	if ($dbv == "CALDAV_DB_VERSION") $ver = 0;
	else $ver = IntVal($dbv);
	$stms   = dav_get_update_statements($ver);
	$errors = array();

	global $db;
	foreach ($stms as $st) {
		$db->q($st);
		if ($db->error) $errors[] = $db->error;
	}

	if (count($errors) == 0) set_config("dav", "db_version", CALDAV_DB_VERSION);

	return $errors;
}