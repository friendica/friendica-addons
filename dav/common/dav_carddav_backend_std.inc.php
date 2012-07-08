<?php

/**
 * PDO CardDAV backend
 *
 * This CardDAV backend uses PDO to store addressbooks
 *
 * @package Sabre
 * @subpackage CardDAV
 * @copyright Copyright (C) 2007-2012 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Sabre_CardDAV_Backend_Std extends Sabre_CardDAV_Backend_Abstract
{

	/**
	 * @var null|Sabre_CardDAV_Backend_Std
	 */
	private static $instance = null;

	/**
	 * @static
	 * @return Sabre_CardDAV_Backend_Std
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new Sabre_CardDAV_Backend_Std();
		}
		return self::$instance;
	}


	/**
	 * Sets up the object
	 */
	public function __construct()
	{

	}

	/**
	 * Returns the list of addressbooks for a specific user.
	 *
	 * @param string $principalUri
	 * @return array
	 */
	public function getAddressBooksForUser($principalUri)
	{
		$uid = dav_compat_principal2uid($principalUri);

		$addressBooks = array();

		$books = q("SELECT id, uri, displayname, principaluri, description, ctag FROM %s%saddressbooks_phone WHERE principaluri = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($principalUri));
		if (count($books) == 0) {
			q("INSERT INTO %s%saddressbooks_phone (uid, principaluri, displayname, uri, description, ctag) VALUES (%d, '%s', '%s', '%s', '%s', 1)",
				CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $uid, dbesc($principalUri), 'Other', 'phone', 'Manually added contacts'
			);
			$books = q("SELECT id, uri, displayname, principaluri, description, ctag FROM %s%saddressbooks_phone WHERE principaluri = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($principalUri));
		}
		foreach ($books as $row) {
			$addressBooks[] = array(
				'id'                                                                => CARDDAV_NAMESPACE_PHONECONTACTS . "-" . $row['id'],
				'uri'                                                               => $row['uri'],
				'principaluri'                                                      => $row['principaluri'],
				'{DAV:}displayname'                                                 => $row['displayname'],
				'{' . Sabre_CardDAV_Plugin::NS_CARDDAV . '}addressbook-description' => $row['description'],
				'{http://calendarserver.org/ns/}getctag'                            => $row['ctag'],
				'{' . Sabre_CardDAV_Plugin::NS_CARDDAV . '}supported-address-data'  =>
				new Sabre_CardDAV_Property_SupportedAddressData(),
			);
		}

		return $addressBooks;

	}


	/**
	 * Updates an addressbook's properties
	 *
	 * See Sabre_DAV_IProperties for a description of the mutations array, as
	 * well as the return value.
	 *
	 * @param mixed $addressBookId
	 * @param array $mutations
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @see Sabre_DAV_IProperties::updateProperties
	 * @return bool|array
	 */
	public function updateAddressBook($addressBookId, array $mutations)
	{
		$x = explode("-", $addressBookId);

		$updates = array();

		foreach ($mutations as $property=> $newValue) {

			switch ($property) {
				case '{DAV:}displayname' :
					$updates['displayname'] = $newValue;
					break;
				case '{' . Sabre_CardDAV_Plugin::NS_CARDDAV . '}addressbook-description' :
					$updates['description'] = $newValue;
					break;
				default :
					// If any unsupported values were being updated, we must
					// let the entire request fail.
					return false;
			}

		}

		// No values are being updated?
		if (!$updates) {
			return false;
		}

		$query = 'UPDATE ' . CALDAV_SQL_DB . CALDAV_SQL_PREFIX . 'addressbooks_phone SET ctag = ctag + 1 ';
		foreach ($updates as $key=> $value) {
			$query .= ', `' . dbesc($key) . '` = ' . dbesc($key) . ' ';
		}
		$query .= ' WHERE id = ' . IntVal($x[1]);
		q($query);

		return true;

	}

	/**
	 * Creates a new address book
	 *
	 * @param string $principalUri
	 * @param string $url Just the 'basename' of the url.
	 * @param array $properties
	 * @throws Sabre_DAV_Exception_BadRequest
	 * @return void
	 */
	public function createAddressBook($principalUri, $url, array $properties)
	{
		$values = array(
			'displayname'  => null,
			'description'  => null,
			'principaluri' => $principalUri,
			'uri'          => $url,
		);

		foreach ($properties as $property=> $newValue) {

			switch ($property) {
				case '{DAV:}displayname' :
					$values['displayname'] = $newValue;
					break;
				case '{' . Sabre_CardDAV_Plugin::NS_CARDDAV . '}addressbook-description' :
					$values['description'] = $newValue;
					break;
				default :
					throw new Sabre_DAV_Exception_BadRequest('Unknown property: ' . $property);
			}

		}

		q("INSERT INTO %s%saddressbooks_phone (uri, displayname, description, principaluri, ctag) VALUES ('%s', '%s', '%s', '%s', 1)",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($values["uri"]), dbesc($values["displayname"]), dbesc($values["description"]), dbesc($values["principaluri"])
		);
	}

	/**
	 * Deletes an entire addressbook and all its contents
	 *
	 * @param int $addressBookId
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return void
	 */
	public function deleteAddressBook($addressBookId)
	{
		$x = explode("-", $addressBookId);
		q("DELETE FROM %s%scards WHERE namespace = %d AND namespace_id = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[0]), IntVal($x[1]));
		q("DELETE FROM %s%saddressbooks_phone WHERE id = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[1]));
	}

	/**
	 * Returns all cards for a specific addressbook id.
	 *
	 * This method should return the following properties for each card:
	 *   * carddata - raw vcard data
	 *   * uri - Some unique url
	 *   * lastmodified - A unix timestamp
	 *
	 * It's recommended to also return the following properties:
	 *   * etag - A unique etag. This must change every time the card changes.
	 *   * size - The size of the card in bytes.
	 *
	 * If these last two properties are provided, less time will be spent
	 * calculating them. If they are specified, you can also ommit carddata.
	 * This may speed up certain requests, especially with large cards.
	 *
	 * @param string $addressbookId
	 * @return array
	 */
	public function getCards($addressbookId)
	{
		$x = explode("-", $addressbookId);

		$r = q('SELECT id, carddata, uri, lastmodified, etag, size, contact FROM %s%scards WHERE namespace = %d AND namespace_id = %d AND manually_deleted = 0',
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[0]), IntVal($x[1])
		);
		if ($r) return $r;
		return array();
	}

	/**
	 * Returns a specfic card.
	 *
	 * The same set of properties must be returned as with getCards. The only
	 * exception is that 'carddata' is absolutely required.
	 *
	 * @param mixed $addressBookId
	 * @param string $cardUri
	 * @throws Sabre_DAV_Exception_NotFound
	 * @return array
	 */
	public function getCard($addressBookId, $cardUri)
	{
		$x = explode("-", $addressBookId);
		$x = q("SELECT id, carddata, uri, lastmodified, etag, size FROM %s%scards WHERE namespace = %d AND namespace_id = %d AND uri = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[0]), IntVal($x[1]), dbesc($cardUri));
		if (count($x) == 0) throw new Sabre_DAV_Exception_NotFound();
		return $x[0];
	}

	/**
	 * Creates a new card.
	 *
	 * The addressbook id will be passed as the first argument. This is the
	 * same id as it is returned from the getAddressbooksForUser method.
	 *
	 * The cardUri is a base uri, and doesn't include the full path. The
	 * cardData argument is the vcard body, and is passed as a string.
	 *
	 * It is possible to return an ETag from this method. This ETag is for the
	 * newly created resource, and must be enclosed with double quotes (that
	 * is, the string itself must contain the double quotes).
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
	 * @return string
	 */
	public function createCard($addressBookId, $cardUri, $cardData)
	{
		$x = explode("-", $addressBookId);

		$etag = md5($cardData);
		q("INSERT INTO %s%scards (carddata, uri, lastmodified, namespace, namespace_id, etag, size) VALUES ('%s', '%s', %d, %d, '%s', %d)",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($cardData), dbesc($cardUri), time(), IntVal($x[0]), IntVal($x[1]), $etag, strlen($cardData)
		);

		q('UPDATE %s%saddressbooks_phone SET ctag = ctag + 1 WHERE id = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[1]));

		return '"' . $etag . '"';

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
		$x = explode("-", $addressBookId);

		$etag = md5($cardData);
		q("UPDATE %s%scards SET carddata = '%s', lastmodified = %d, etag = '%s', size = %d, manually_edited = 1 WHERE uri = '%s' AND namespace = %d AND namespace_id =%d",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($cardData), time(), $etag, strlen($cardData), dbesc($cardUri), IntVal($x[10]), IntVal($x[1])
		);

		q('UPDATE %s%saddressbooks_phone SET ctag = ctag + 1 WHERE id = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[1]));

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
		$x = explode("-", $addressBookId);

		q("DELETE FROM %s%scards WHERE namespace = %d AND namespace_id = %d AND uri = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[0]), IntVal($x[1]), dbesc($cardUri));
		q('UPDATE %s%saddressbooks_phone SET ctag = ctag + 1 WHERE id = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[1]));

		return true;
	}
}
