<?php

# Generated automatically - do not change!

class DBClass_friendica_calendars extends DBClass_animexx {
	/** @var $PRIMARY_KEY array */
	public $PRIMARY_KEY = array("namespace", "namespace_id");

	protected $SRC_TABLE = 'calendars';
	/** @var $calendarcolor string */
	/** @var $displayname string */
	/** @var $timezone string */
	/** @var $description string */

	public $calendarcolor, $displayname, $timezone, $description;

	/** @var $namespace int */
	/** @var $namespace_id int */
	/** @var $uid int */
	/** @var $calendarorder int */
	/** @var $ctag int */

	public $namespace, $namespace_id, $uid, $calendarorder, $ctag;


	protected $_string_fields = array('calendarcolor', 'displayname', 'timezone', 'description');
	protected $_int_fields = array('namespace', 'namespace_id', 'uid', 'calendarorder', 'ctag');
	protected $_null_fields = array();
}
