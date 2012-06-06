<?php

# Generated automatically - do not change!

class DBClass_friendica_jqcalendar extends DBClass_animexx {
	/** @var $PRIMARY_KEY array */
	public $PRIMARY_KEY = array("id");

	protected $SRC_TABLE = 'jqcalendar';
	/** @var $ical_uri string */
	/** @var $ical_recurr_uri string */
	/** @var $Subject string|null */
	/** @var $Location string|null */
	/** @var $Description string|null */
	/** @var $StartTime string|null */
	/** @var $EndTime string|null */
	/** @var $Color string|null */
	/** @var $RecurringRule string|null */

	public $ical_uri, $ical_recurr_uri, $Subject, $Location, $Description, $StartTime, $EndTime, $Color, $RecurringRule;

	/** @var $id int */
	/** @var $uid int */
	/** @var $namespace int */
	/** @var $namespace_id int */
	/** @var $permission_edit int */
	/** @var $IsAllDayEvent int */

	public $id, $uid, $namespace, $namespace_id, $permission_edit, $IsAllDayEvent;


	protected $_string_fields = array('ical_uri', 'ical_recurr_uri', 'Subject', 'Location', 'Description', 'StartTime', 'EndTime', 'Color', 'RecurringRule');
	protected $_int_fields = array('id', 'uid', 'namespace', 'namespace_id', 'permission_edit', 'IsAllDayEvent');
	protected $_null_fields = array('Subject', 'Location', 'Description', 'StartTime', 'EndTime', 'Color', 'RecurringRule');
}
