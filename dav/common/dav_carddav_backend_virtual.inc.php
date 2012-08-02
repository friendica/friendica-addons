<?php

abstract class Sabre_CardDAV_Backend_Virtual extends Sabre_CardDAV_Backend_Common
{

	/**
	 * @static
	 * @abstract
	 * @param int $addressbookId
	 * @param string $uri
	 * @return array
	 */
	/*
	abstract public function getItemsByUri($addressbookId, $uri);
    */

	/**
	 * @static
	 * @param int $uid
	 * @param int $namespace
	 */
	static public function invalidateCache($uid = 0, $namespace = 0) {
		q("DELETE FROM %s%sadd_virtual_object_sync WHERE `uid` = %d AND `namespace` = %d",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($uid), IntVal($namespace));
	}

	/**
	 * @static
	 * @abstract
	 * @param int $addressbookId
	 */
	static abstract protected function createCache_internal($addressbookId);

	/**
	 * @static
	 * @param int $addressbookId
	 */
	static protected function createCache($addressbookId) {
		$addressbookId = IntVal($addressbookId);
		q("DELETE FROM %s%saddressbookobjects WHERE `addressbook_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId);
		static::createCache_internal($addressbookId);
		q("REPLACE INTO %s%sadd_virtual_object_sync (`addressbook_id`, `date`) VALUES (%d, NOW())", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId);
	}


	/**
	 * @param string $addressbookId
	 * @return array
	 */
	public function getCards($addressbookId)
	{
		$addressbookId = IntVal($addressbookId);
		$r = q("SELECT COUNT(*) n FROM %s%sadd_virtual_object_sync WHERE `addressbook_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId);

		if ($r[0]["n"] == 0) static::createCache($addressbookId);

		return q("SELECT * FROM %s%saddressbookobjects WHERE `addressbook_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId);

	}

	/**
	 * @param string $addressbookId
	 * @param string $objectUri
	 * @throws Sabre_DAV_Exception_NotFound
	 * @return array
	 */
	public function getCard($addressbookId, $objectUri)
	{
		$addressbookId = IntVal($addressbookId);
		$r = q("SELECT COUNT(*) n FROM %s%sadd_virtual_object_sync WHERE `addressbook_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($addressbookId));

		if ($r[0]["n"] == 0) static::createCache($addressbookId);

		$r = q("SELECT * FROM %s%saddressbookobjects WHERE `uri` = '%s' AND `addressbook_id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($objectUri), IntVal($addressbookId));
		if (count($r) == 0) throw new Sabre_DAV_Exception_NotFound();

		$obj = $r[0];
		$ret = array(
			"id" => IntVal($obj["uri"]),
			"carddata" => $obj["carddata"],
			"uri" => $obj["uri"],
			"lastmodified" => $obj["lastmodified"],
			"addressbookid" => $addressbookId,
			"etag" => $obj["etag"],
			"size" => IntVal($obj["size"]),
		);
		return $ret;
	}



	/**
	 * @param string $principalUri
	 * @param string $addressbookUri
	 * @param array $properties
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return void
	 */
	public function createAddressBook($principalUri, $addressbookUri, array $properties)
	{
		throw new Sabre_DAV_Exception_Forbidden();
	}

	/**
	 * @param string $addressbookId
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return void
	 */
	public function deleteAddressBook($addressbookId)
	{
		throw new Sabre_DAV_Exception_Forbidden();
	}


	/**
	 * @param string $addressbookId
	 * @param string $objectUri
	 * @param string $cardData
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return null|string|void
	 */
	function createCard($addressbookId, $objectUri, $cardData)
	{
		throw new Sabre_DAV_Exception_Forbidden();
	}

	/**
	 * @param string $addressbookId
	 * @param string $objectUri
	 * @param string $cardData
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return null|string|void
	 */
	function updateCard($addressbookId, $objectUri, $cardData)
	{
		throw new Sabre_DAV_Exception_Forbidden();
	}

	/**
	 * @param string $addressbookId
	 * @param string $objectUri
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return void
	 */
	function deleteCard($addressbookId, $objectUri)
	{
		throw new Sabre_DAV_Exception_Forbidden();
	}


}