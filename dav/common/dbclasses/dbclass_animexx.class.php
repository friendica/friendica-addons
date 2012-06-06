<?php

class DBClass_animexx
{
	protected $_string_fields = array();
	protected $_int_fields = array();
	protected $_float_fields = array();
	protected $_null_fields = array();

	public $PRIMARY_KEY = array();
	protected $SRC_TABLE = "";

	/**
	 * @param $dbarray_or_id
	 * @throws Exception
	 */
	function __construct($dbarray_or_id)
	{
		if (is_numeric($dbarray_or_id) && count($this->PRIMARY_KEY) == 1) {
			$dbarray_or_id = q("SELECT * FROM %s%s%s WHERE %s=%d",
				CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $this->SRC_TABLE, $this->PRIMARY_KEY[0], IntVal($dbarray_or_id)
			);
			if (count($dbarray_or_id) == 0) throw new Exception("Not found");
			$dbarray_or_id = $dbarray_or_id[0];
		}
		if (is_array($dbarray_or_id)) {
			foreach ($this->_string_fields as $field) {
				$this->$field = $dbarray_or_id[$field];
			}
			foreach ($this->_int_fields as $field) {
				$this->$field = IntVal($dbarray_or_id[$field]);
			}
			foreach ($this->_float_fields as $field) {
				$this->$field = FloatVal($dbarray_or_id[$field]);
			}
		} else throw new Exception("Not found");
	}

	/**
	 * @return array
	 */
	function toArray()
	{
		$arr = array();
		foreach ($this->_string_fields as $field) $arr[$field] = $this->$field;
		foreach ($this->_int_fields as $field) $arr[$field] = $this->$field;
		foreach ($this->_float_fields as $field) $arr[$field] = $this->$field;
		return $arr;
	}
}
