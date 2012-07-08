<?php

class Sabre_CardDAV_Backend_FriendicaCommunity extends Sabre_CardDAV_Backend_Abstract
{

	/**
	 * @var null|Sabre_CardDAV_Backend_FriendicaCommunity
	 */
	private static $instance = null;

	/**
	 * @static
	 * @return Sabre_CardDAV_Backend_FriendicaCommunity
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new Sabre_CardDAV_Backend_FriendicaCommunity();
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

		$books = q("SELECT ctag FROM %s%saddressbooks_community WHERE uid = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($uid));
		if (count($books) == 0) {
			q("INSERT INTO %s%saddressbooks_community (uid, ctag) VALUES (%d, 1)", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($uid));
			$ctag = 1;
		} else {
			$ctag = $books[0]["ctag"];
		}
		$addressBooks[] = array(
			'id'                                                                => CARDDAV_NAMESPACE_COMMUNITYCONTACTS . "-" . $uid,
			'uri'                                                               => "friendica",
			'principaluri'                                                      => $principalUri,
			'{DAV:}displayname'                                                 => t("Friendica-Contacts"),
			'{' . Sabre_CardDAV_Plugin::NS_CARDDAV . '}addressbook-description' => t("Your Friendica-Contacts"),
			'{http://calendarserver.org/ns/}getctag'                            => $ctag,
			'{' . Sabre_CardDAV_Plugin::NS_CARDDAV . '}supported-address-data'  =>
			new Sabre_CardDAV_Property_SupportedAddressData(),
		);

		return $addressBooks;

	}


	/**
	 * Updates an addressbook's properties
	 *
	 * See Sabre_DAV_IProperties for a description of the mutations array, as
	 * well as the return value.
	 *
	 * @param string $addressBookId
	 * @param array $mutations
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @see Sabre_DAV_IProperties::updateProperties
	 * @return bool|array
	 */
	public function updateAddressBook($addressBookId, array $mutations)
	{
		throw new Sabre_DAV_Exception_Forbidden();
	}

	/**
	 * Creates a new address book
	 *
	 * @param string $principalUri
	 * @param string $url Just the 'basename' of the url.
	 * @param array $properties
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return void
	 */
	public function createAddressBook($principalUri, $url, array $properties)
	{
		throw new Sabre_DAV_Exception_Forbidden();
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
		throw new Sabre_DAV_Exception_Forbidden();
	}


	/**
	 * @param array $contact
	 * @return array
	 */
	private function dav_contactarr2vcardsource($contact)
	{
		$name        = explode(" ", $contact["name"]);
		$first_name  = $last_name = "";
		$middle_name = array();
		$num         = count($name);
		for ($i = 0; $i < $num && $first_name == ""; $i++) if ($name[$i] != "") {
			$first_name = $name[$i];
			unset($name[$i]);
		}
		for ($i = $num - 1; $i >= 0 && $last_name == ""; $i--) if (isset($name[$i]) && $name[$i] != "") {
			$last_name = $name[$i];
			unset($name[$i]);
		}
		foreach ($name as $n) if ($n != "") $middle_name[] = $n;
		$vcarddata              = new vcard_source_data($first_name, implode(" ", $middle_name), $last_name);
		$vcarddata->homepages[] = new vcard_source_data_homepage("pref", $contact["url"]);
		$vcarddata->last_update = ($contact["last-update"] > 0 ? $contact["last-update"] : $contact["created"]);

		$photo = q("SELECT * FROM photo WHERE `contact-id` = %d ORDER BY scale DESC", $contact["id"]); //prefer size 80x80
		if ($photo && count($photo) > 0) {
			$photodata             = new vcard_source_data_photo();
			$photodata->width      = $photo[0]["width"];
			$photodata->height     = $photo[0]["height"];
			$photodata->type       = "JPEG";
			$photodata->binarydata = $photo[0]["data"];
			$vcarddata->photo      = $photodata;
		}

		switch ($contact["network"]) {
			case "face":
				$vcarddata->socialnetworks[] = new vcard_source_data_socialnetwork("facebook", $contact["notify"], "http://www.facebook.com/" . $contact["notify"]);
				break;
			case "dfrn":
				$vcarddata->socialnetworks[] = new vcard_source_data_socialnetwork("dfrn", $contact["nick"], $contact["url"]);
				break;
			case "twitter":
				$vcarddata->socialnetworks[] = new vcard_source_data_socialnetwork("twitter", $contact["nick"], "http://twitter.com/" . $contact["nick"]); // @TODO Stimmt das?
				break;
		}

		$vcard = vcard_source_compile($vcarddata);
		return array(
			"id"           => $contact["id"],
			"carddata"     => $vcard,
			"uri"          => $contact["id"] . ".vcf",
			"lastmodified" => wdcal_mySql2PhpTime($vcarddata->last_update),
			"etag"         => md5($vcard),
			"size"         => strlen($vcard),
		);

	}

	/**
	 * @param int $uid
	 * @param array|int[] $exclude_ids
	 * @return array
	 */
	private function dav_getCommunityContactsVCards($uid = 0, $exclude_ids = array())
	{
		$notin    = (count($exclude_ids) > 0 ? " AND id NOT IN (" . implode(", ", $exclude_ids) . ") " : "");
		$uid      = IntVal($uid);
		$contacts = q("SELECT * FROM `contact` WHERE `uid` = %d AND `blocked` = 0 AND `pending` = 0 AND `hidden` = 0 AND `archive` = 0 $notin ORDER BY `name` ASC", $uid);

		$retdata = array();
		foreach ($contacts as $contact) {
			$x            = $this->dav_contactarr2vcardsource($contact);
			$x["contact"] = $contact["id"];
			$retdata[]    = $x;
		}
		return $retdata;
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
		$add = explode("-", $addressbookId);

		$indb           = q('SELECT id, carddata, uri, lastmodified, etag, size, contact, manually_deleted FROM %s%scards WHERE namespace = %d AND namespace_id = %d',
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($add[0]), IntVal($add[1])
		);
		$found_contacts = array();
		$contacts       = array();
		foreach ($indb as $x) {
			if ($x["manually_deleted"] == 0) $contacts[] = $x;
			$found_contacts[] = IntVal($x["contact"]);
		}
		$new_found = $this->dav_getCommunityContactsVCards($add[1], $found_contacts);
		foreach ($new_found as $new) {
			q("INSERT INTO %s%scards (namespace, namespace_id, contact, carddata, uri, lastmodified, manually_edited, manually_deleted, etag, size)
					VALUES (%d, %d, %d, '%s', '%s', %d, 0, 0, '%s', %d)", CALDAV_SQL_DB, CALDAV_SQL_PREFIX,
				IntVal($add[0]), IntVal($add[1]), IntVal($new["contact"]), dbesc($new["carddata"]), dbesc($new["uri"]), time(), md5($new["carddata"]), strlen($new["carddata"])
			);
		}
		return array_merge($contacts, $new_found);
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
		$x = explode("-", $addressBookId);

		q("UPDATE %s%scards SET manually_deleted = 1 WHERE namespace = %d AND namespace_id = %d AND uri = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[0]), IntVal($x[1]), dbesc($cardUri));
		q('UPDATE %s%saddressbooks_community SET ctag = ctag + 1 WHERE uid = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($x[1]));

		return true;
	}
}
