<?php

# Generated automatically - do not change!

class DBClass_friendica_notifications extends DBClass_animexx {
	/** @var $PRIMARY_KEY array */
	public $PRIMARY_KEY = array("id");

	protected $SRC_TABLE = 'notifications';
	/** @var $ical_uri string */
	/** @var $ical_recurr_uri string */
	/** @var $alert_date string */
	/** @var $rel_type string */

	public $ical_uri, $ical_recurr_uri, $alert_date, $rel_type;

	/** @var $id int */
	/** @var $uid int */
	/** @var $namespace int */
	/** @var $namespace_id int */
	/** @var $rel_value int */
	/** @var $notified int */

	public $id, $uid, $namespace, $namespace_id, $rel_value, $notified;

	/** @var $REL_TYPE_VALUES array */
	public static $REL_TYPE_VALUES = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year');
	public static $REL_TYPE_SECOND = 'second';
	public static $REL_TYPE_MINUTE = 'minute';
	public static $REL_TYPE_HOUR = 'hour';
	public static $REL_TYPE_DAY = 'day';
	public static $REL_TYPE_WEEK = 'week';
	public static $REL_TYPE_MONTH = 'month';
	public static $REL_TYPE_YEAR = 'year';


	protected $_string_fields = array('ical_uri', 'ical_recurr_uri', 'alert_date', 'rel_type');
	protected $_int_fields = array('id', 'uid', 'namespace', 'namespace_id', 'rel_value', 'notified');
	protected $_null_fields = array();
}
