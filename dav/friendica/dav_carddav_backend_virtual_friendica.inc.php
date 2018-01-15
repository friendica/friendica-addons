<?php

class Sabre_CardDAV_Backend_Friendica extends Sabre_CardDAV_Backend_Virtual
{

	/**
	 * @var null|Sabre_CardDAV_Backend_Friendica
	 */
	private static $instance = null;

	/**
	 * @static
	 * @return Sabre_CardDAV_Backend_Friendica
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new Sabre_CardDAV_Backend_Friendica();
		}
		return self::$instance;
	}


	/**
	 * @return int
	 */
	public function getNamespace()
	{
		return CARDDAV_NAMESPACE_PRIVATE;
	}

	/**
	 * @static
	 * @return string
	 */
	public static function getBackendTypeName() {
		return t("Friendica-Contacts");
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

		$addressBooks = [];

		$books = q("SELECT id, ctag FROM %s%saddressbooks WHERE `namespace` = %d AND `namespace_id` = %d AND `uri` = '%s'",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, CARDDAV_NAMESPACE_PRIVATE, IntVal($uid), dbesc(CARDDAV_FRIENDICA_CONTACT));
		$ctag = $books[0]["ctag"];

		$addressBooks[] = [
			'id'                                                                => $books[0]["id"],
			'uri'                                                               => "friendica",
			'principaluri'                                                      => $principalUri,
			'{DAV:}displayname'                                                 => t("Friendica-Contacts"),
			'{' . Sabre_CardDAV_Plugin::NS_CARDDAV . '}addressbook-description' => t("Your Friendica-Contacts"),
			'{http://calendarserver.org/ns/}getctag'                            => $ctag,
			'{' . Sabre_CardDAV_Plugin::NS_CARDDAV . '}supported-address-data'  =>
			new Sabre_CardDAV_Property_SupportedAddressData(),
		];

		return $addressBooks;

	}

	/**
	 * @static
	 * @param array $contact
	 * @return array
	 */
	private static function dav_contactarr2vcardsource($contact)
	{
		$name        = explode(" ", $contact["name"]);
		$first_name  = $last_name = "";
		$middle_name = [];
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
		return [
			"id"           => $contact["id"],
			"carddata"     => $vcard,
			"uri"          => $contact["id"] . ".vcf",
			"lastmodified" => wdcal_mySql2PhpTime($vcarddata->last_update),
			"etag"         => md5($vcard),
			"size"         => strlen($vcard),
		];

	}

	/**
	 * @static
	 * @param int $addressbookId
	 * @param bool $force
	 * @throws Sabre_DAV_Exception_NotFound
	 */
	static protected function createCache_internal($addressbookId, $force = false) {
		//$notin    = (count($exclude_ids) > 0 ? " AND id NOT IN (" . implode(", ", $exclude_ids) . ") " : "");
		$addressbook = q("SELECT * FROM %s%saddressbooks WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($addressbookId));
		if (count($addressbook) != 1 || $addressbook[0]["namespace"] != CARDDAV_NAMESPACE_PRIVATE) throw new Sabre_DAV_Exception_NotFound();
		$contacts = q("SELECT * FROM `contact` WHERE `uid` = %d AND `blocked` = 0 AND `pending` = 0 AND `hidden` = 0 AND `archive` = 0 ORDER BY `name` ASC", $addressbook[0]["namespace_id"]);

		foreach ($contacts as $contact) {
			$x            = static::dav_contactarr2vcardsource($contact);
			q("INSERT INTO %s%saddressbookobjects (`addressbook_id`, `contact`, `carddata`, `uri`, `lastmodified`, `etag`, `size`) VALUES (%d, %d, '%s', '%s', NOW(), '%s', %d)",
				CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId, $contact["id"], dbesc($x["carddata"]), dbesc($x["uri"]), dbesc($x["etag"]), $x["size"]
			);
		}
	}


	/**
	 * @static
	 * @param int $addressbookId
	 * @param int $contactId
	 * @param bool $force
	 * @throws Sabre_DAV_Exception_NotFound
	 */
	static protected function createCardCache($addressbookId, $contactId, $force = false)
	{
		$addressbook = q("SELECT * FROM %s%saddressbooks WHERE `id` = %d", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, IntVal($addressbookId));
		if (count($addressbook) != 1 || $addressbook[0]["namespace"] != CARDDAV_NAMESPACE_PRIVATE) throw new Sabre_DAV_Exception_NotFound();

		$contacts = q("SELECT * FROM `contact` WHERE `uid` = %d AND `blocked` = 0 AND `pending` = 0 AND `hidden` = 0 AND `archive` = 0 AND `id` = %d ORDER BY `name` ASC",
			$addressbook[0]["namespace_id"], IntVal($contactId));
		$contact = $contacts[0];

		$x            = static::dav_contactarr2vcardsource($contact);
		q("INSERT INTO %s%saddressbookobjects (`addressbook_id`, `contact`, `carddata`, `uri`, `lastmodified`, `etag`, `size`) VALUES (%d, %d, '%s', '%s', NOW(), '%s', %d)",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $addressbookId, $contact["id"], dbesc($x["carddata"]), dbesc($x["uri"]), dbesc($x["etag"]), $x["size"]
		);
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
