<?php

abstract class VirtualCalSourceBackend {

	/**
	 * @static
	 * @param int $uid
	 * @param int $namespace
	 */
	static public function invalidateCache($uid = 0, $namespace = 0) {
		q("DELETE FROM %s%scache_synchronized WHERE `uid` = %d AND `namespace` = %d",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($uid), IntVal($namespace));
	}

	/**
	 * @static
	 * @abstract
	 * @param int $uid
	 * @param int $namespace_id
	 */
	static abstract function createCache($uid = 0, $namespace_id = 0);

	/**
	 * @static
	 * @param int $uid
	 * @param int $namespace
	 * @return array
	 */
	static public function getCachedItems($uid = 0, $namespace = 0) {
		$uid = IntVal($uid);
		$namespace = IntVal($namespace);
		$r = q("SELECT COUNT(*) n FROM %s%scache_synchronized WHERE `uid` = %d AND `namespace` = %d",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($uid), $namespace);

		if ($r[0]["n"] == 0) self::createCache();

		$r = q("SELECT * FROM %s%scal_virtual_object_cache WHERE `uid` = %d AND `namespace` = %d",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $uid, $namespace);

		return $r;
	}

	/**
	 * @static
	 * @abstract
	 * @param int $uid
	 * @param int $namespace_id
	 * @param string $date_from
	 * @param string $date_to
	 * @return array
	 */
	abstract static public function getItemsByTime($uid = 0, $namespace_id = 0, $date_from = "", $date_to = "");

	/**
	 * @static
	 * @abstract
	 * @param int $uid
	 * @param string $uri
	 * @return array
	 */
	abstract static public function getItemsByUri($uid = 0, $uri);

}
