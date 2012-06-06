<?php

# Generated automatically - do not change!

class DBClass_friendica_calendarobjects extends DBClass_animexx {
	/** @var $PRIMARY_KEY array */
	public $PRIMARY_KEY = array("id");

	protected $SRC_TABLE = 'calendarobjects';
	/** @var $calendardata string|null */
	/** @var $uri string */
	/** @var $lastmodified string|null */
	/** @var $etag string */

	public $calendardata, $uri, $lastmodified, $etag;

	/** @var $id int */
	/** @var $namespace int */
	/** @var $namespace_id int */
	/** @var $size int */

	public $id, $namespace, $namespace_id, $size;


	protected $_string_fields = array('calendardata', 'uri', 'lastmodified', 'etag');
	protected $_int_fields = array('id', 'namespace', 'namespace_id', 'size');
	protected $_null_fields = array('calendardata', 'lastmodified');
}
