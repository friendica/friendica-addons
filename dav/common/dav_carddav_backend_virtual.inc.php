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
	 * @param int $addressbookId
	 */
	static public function invalidateCache($addressbookId) {
		q("UPDATE %s%saddressbooks SET `needs_rebuild` = 1 WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($addressbookId));
	}

	/**
	 * @static
	 * @abstract
	 * @param int $addressbookId
	 * @param bool $force
	 */
	static abstract protected function createCache_internal($addressbookId, $force = false);

	/**
	 * @param int $addressbookId
	 * @param null|array $addressbook
	 * @param bool $force
	 */
	public function createCache($addressbookId, $addressbook = null, $force = false) {
		$addressbookId = IntVal($addressbookId);

		if (!$addressbook) {
			$add = q("SELECT `needs_rebuild`, `uri` FROM %s%saddressbooks WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId);
			$addressbook = $add[0];
		}
		if ($addressbook["needs_rebuild"] == 1 || $force) {
			static::createCache_internal($addressbookId, $force);
			q("UPDATE %s%saddressbooks SET `needs_rebuild` = 0, `ctag` = `ctag` + 1 WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId);
		}
	}

	/**
	 * @static
	 * @abstract
	 * @param int $addressbookId
	 * @param int $contactId
	 * @param bool $force
	 */
	static abstract protected function createCardCache($addressbookId, $contactId, $force = false);

	/**
	 * @param int $addressbookId
	 * @return array
	 */
	public function getCards($addressbookId)
	{
		$addressbookId = IntVal($addressbookId);
		$add = q("SELECT * FROM %s%saddressbooks WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId);
		if ($add[0]["needs_rebuild"]) {
			static::createCache_internal($addressbookId);
			q("UPDATE %s%saddressbooks SET `needs_rebuild` = 0, `ctag` = `ctag` + 1 WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId);
			$add[0]["needs_rebuild"] = 0;
			$add[0]["ctag"]++;
		}

		$ret = array();
		$x = q("SELECT * FROM %s%saddressbookobjects WHERE `addressbook_id` = %d AND `manually_deleted` = 0", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId);
		foreach ($x as $y) $ret[] = self::getCard($addressbookId, $add[0]["uri"], $add[0], $y);

		return $ret;
	}


	/**
	 * Replaces the x-prop_name value. Replaces the prop_name value IF the old value is the same as the old value of x-prop_name (meaning: the user has not manually changed it)
	 *
	 * @param Sabre\VObject\Component $component
	 * @param string $prop_name
	 * @param string $prop_value
	 * @param array $parameters
	 * @return void
	 */
	static public function card_set_automatic_value(&$component, $prop_name, $prop_value, $parameters = array()) {
		$automatic = $component->select("X-" . $prop_name);
		$curr = $component->select($prop_name);

		if (count($automatic) == 0) {
			$prop = new Sabre\VObject\Property('X-' . $prop_name, $prop_value);
			foreach ($parameters as $key=>$val) $prop->add($key, $val);
			$component->children[] = $prop;

			if (count($curr) == 0) {
				$prop = new Sabre\VObject\Property($prop_name, $prop_value);
				foreach ($parameters as $key=>$val) $prop->add($key, $val);
				$component->children[] = $prop;
			}

		} else foreach ($automatic as $auto_prop) {
			/** @var Sabre\VObject\Property $auto_prop */
			/** @var Sabre\VObject\Property $actual_prop */
			foreach ($curr as $actual_prop) {
				if ($auto_prop->value == $actual_prop->value) $actual_prop->setValue($prop_value);
			}
			$auto_prop->setValue($prop_value);
		}
	}



	/**
	 * Deletes the x-prop_name value. Deletes the prop_name value IF the old value is the same as the old value of x-prop_name (meaning: the user has not manually changed it)
	 *
	 * @param Sabre\VObject\Component $component
	 * @param string $prop_name
	 * @param array $parameters
	 */
	static public function card_del_automatic_value(&$component, $prop_name, $parameters = array()) {
		// @TODO
	}

	/**
	 * @param int $addressbookId
	 * @param string $objectUri
	 * @param array $book
	 * @param array $obj
	 * @throws Sabre_DAV_Exception_NotFound
	 * @return array
	 */
	public function getCard($addressbookId, $objectUri, $book = null, $obj = null)
	{
		$addressbookId = IntVal($addressbookId);

		if ($book == null) {
			$add = q("SELECT `needs_rebuild`, `uri` FROM %s%saddressbooks WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId);
			$book = $add[0];
		}
		if ($book["needs_rebuild"] == 1) {
			static::createCache_internal($addressbookId);
			q("UPDATE %s%saddressbooks SET `needs_rebuild` = 0, `ctag` = `ctag` + 1 WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId);
			$add[0]["needs_rebuild"] = 0;
		}

		if ($obj == null) {
			$r = q("SELECT * FROM %s%saddressbookobjects WHERE `uri` = '%s' AND `addressbook_id` = %d AND `manually_deleted` = 0",
				CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($objectUri), IntVal($addressbookId));
			if (count($r) == 0) throw new Sabre_DAV_Exception_NotFound();
			$obj = $r[0];
			if ($obj["needs_rebuild"] == 1) $obj = static::createCardCache($addressbookId, $obj["contact"]);
		}

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
	 * Updates a card.
	 *
	 * The addressbook id will be passed as the first argument. This is the
	 * same id as it is returned from the getAddressbooksForUser method.
	 *
	 * The cardUri is a base uri, and doesn't include the full path. The
	 * cardData argument is the vcard body, and is passed as a string.
	 *
	 * It is possible to return an ETag from this method. This ETag should
	 * match that of the updated resource, and must be enclosed with double
	 * quotes (that is: the string itself must contain the actual quotes).
	 *
	 * You should only return the ETag if you store the carddata as-is. If a
	 * subsequent GET request on the same card does not have the same body,
	 * byte-by-byte and you did return an ETag here, clients tend to get
	 * confused.
	 *
	 * If you don't return an ETag, you can just return null.
	 *
	 * @param string $addressBookId
	 * @param string $cardUri
	 * @param string $cardData
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return string|null
	 */
	public function updateCard($addressBookId, $cardUri, $cardData)
	{
		echo "Die!"; die(); // @TODO
		$x = explode("-", $addressBookId);

		$etag = md5($cardData);
		q("UPDATE %s%scards SET carddata = '%s', lastmodified = %d, etag = '%s', size = %d, manually_edited = 1 WHERE uri = '%s' AND namespace = %d AND namespace_id =%d",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($cardData), time(), $etag, strlen($cardData), dbesc($cardUri), IntVal($x[10]), IntVal($x[1])
		);
		q('UPDATE %s%saddressbooks_community SET ctag = ctag + 1 WHERE uid = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[1]));

		return '"' . $etag . '"';
	}




	/**
	 * Deletes a card
	 *
	 * @param string $addressBookId
	 * @param string $cardUri
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return bool
	 */
	public function deleteCard($addressBookId, $cardUri)
	{
		q("UPDATE %s%scards SET `manually_deleted` = 1 WHERE `addressbook_id` = %d AND `uri` = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($addressBookId), dbesc($cardUri));
		q('UPDATE %s%saddressbooks SET ctag = ctag + 1 WHERE `id` = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($addressBookId));

		return true;
	}


}