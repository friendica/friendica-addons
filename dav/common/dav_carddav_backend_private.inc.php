<?php

class Sabre_CardDAV_Backend_Std extends Sabre_CardDAV_Backend_Common
{
    /**
     * @var null|Sabre_CardDAV_Backend_Std
     */
    private static $instance = null;

    /**
     * @static
     *
     * @return Sabre_CardDAV_Backend_Std
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Sets up the object.
     */
    public function __construct()
    {
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
     *
     * @return string
     */
    public static function getBackendTypeName()
    {
        return t('Private Addressbooks');
    }

    /**
     * Returns the list of addressbooks for a specific user.
     *
     * @param string $principalUri
     *
     * @return array
     */
    public function getAddressBooksForUser($principalUri)
    {
        $n = dav_compat_principal2namespace($principalUri);
        if ($n['namespace'] != $this->getNamespace()) {
            return array();
        }

        $addressBooks = array();

        $books = q('SELECT * FROM %s%saddressbooks WHERE `namespace` = %d AND `namespace_id` = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, intval($n['namespace']), intval($n['namespace_id']));
        foreach ($books as $row) {
            if (in_array($row['uri'], $GLOBALS['CARDDAV_PRIVATE_SYSTEM_ADDRESSBOOKS'])) {
                continue;
            }

            $addressBooks[] = array(
                'id' => $row['id'],
                'uri' => $row['uri'],
                'principaluri' => $principalUri,
                '{DAV:}displayname' => $row['displayname'],
                '{'.Sabre_CardDAV_Plugin::NS_CARDDAV.'}addressbook-description' => $row['description'],
                '{http://calendarserver.org/ns/}getctag' => $row['ctag'],
                '{'.Sabre_CardDAV_Plugin::NS_CARDDAV.'}supported-address-data' => new Sabre_CardDAV_Property_SupportedAddressData(),
            );
        }

        return $addressBooks;
    }

    /**
     * Creates a new address book.
     *
     * @param string $principalUri
     * @param string $url          Just the 'basename' of the url
     * @param array  $properties
     *
     * @throws Sabre_DAV_Exception_BadRequest
     */
    public function createAddressBook($principalUri, $url, array $properties)
    {
        $uid = dav_compat_principal2uid($principalUri);

        $values = array(
            'displayname' => null,
            'description' => null,
            'principaluri' => $principalUri,
            'uri' => $url,
        );

        foreach ($properties as $property => $newValue) {
            switch ($property) {
                case '{DAV:}displayname':
                    $values['displayname'] = $newValue;
                    break;
                case '{'.Sabre_CardDAV_Plugin::NS_CARDDAV.'}addressbook-description':
                    $values['description'] = $newValue;
                    break;
                default:
                    throw new Sabre_DAV_Exception_BadRequest('Unknown property: '.$property);
            }
        }

        q("INSERT INTO %s%saddressbooks (`uri`, `displayname`, `description`, `namespace`, `namespace_id`, `ctag`) VALUES ('%s', '%s', '%s', %d, %d, 1)",
            CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($values['uri']), dbesc($values['displayname']), dbesc($values['description']), CARDDAV_NAMESPACE_PRIVATE, intval($uid)
        );
    }

    /**
     * Deletes an entire addressbook and all its contents.
     *
     * @param int $addressBookId
     *
     * @throws Sabre_DAV_Exception_Forbidden
     */
    public function deleteAddressBook($addressBookId)
    {
        q('DELETE FROM %s%saddressbookobjects WHERE `id` = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, intval($addressBookId));
        q('DELETE FROM %s%saddressbooks WHERE `addressbook_id` = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, intval($addressBookId));
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
     *
     * @return array
     */
    public function getCards($addressbookId)
    {
        $r = q('SELECT `id`, `carddata`, `uri`, `lastmodified`, `etag`, `size`, `contact` FROM %s%saddressbookobjects WHERE `addressbook_id` = %d AND `manually_deleted` = 0',
            CALDAV_SQL_DB, CALDAV_SQL_PREFIX, intval($addressbookId)
        );
        if ($r) {
            return $r;
        }

        return array();
    }

    /**
     * Returns a specfic card.
     *
     * The same set of properties must be returned as with getCards. The only
     * exception is that 'carddata' is absolutely required.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     *
     * @throws Sabre_DAV_Exception_NotFound
     *
     * @return array
     */
    public function getCard($addressBookId, $cardUri)
    {
        $x = q("SELECT `id`, `carddata`, `uri`, `lastmodified`, `etag`, `size` FROM %s%saddressbookobjects WHERE `addressbook_id` = %d AND `uri` = '%s'",
            CALDAV_SQL_DB, CALDAV_SQL_PREFIX, intval($addressBookId), dbesc($cardUri));
        if (count($x) == 0) {
            throw new Sabre_DAV_Exception_NotFound();
        }

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
     *
     * @throws Sabre_DAV_Exception_Forbidden
     *
     * @return string
     */
    public function createCard($addressBookId, $cardUri, $cardData)
    {
        $etag = md5($cardData);
        q("INSERT INTO %s%saddressbookobjects (`carddata`, `uri`, `lastmodified`, `addressbook_id`, `etag`, `size`) VALUES ('%s', '%s', NOW(), %d, '%s', %d)",
            CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($cardData), dbesc($cardUri), intval($addressBookId), dbesc($etag), strlen($cardData)
        );

        q('UPDATE %s%saddressbooks SET `ctag` = `ctag` + 1 WHERE `id` = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, intval($addressBookId));

        return '"'.$etag.'"';
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
     *
     * @throws Sabre_DAV_Exception_Forbidden
     *
     * @return string|null
     */
    public function updateCard($addressBookId, $cardUri, $cardData)
    {
        $etag = md5($cardData);
        q("UPDATE %s%saddressbookobjects SET `carddata` = '%s', `lastmodified` = NOW(), `etag` = '%s', `size` = %d, `manually_edited` = 1 WHERE `uri` = '%s' AND `addressbook_id` = %d",
            CALDAV_SQL_DB, CALDAV_SQL_PREFIX, dbesc($cardData), dbesc($etag), strlen($cardData), dbesc($cardUri), intval($addressBookId)
        );

        q('UPDATE %s%saddressbooks SET `ctag` = `ctag` + 1 WHERE `id` = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, intval($addressBookId));

        return '"'.$etag.'"';
    }

    /**
     * Deletes a card.
     *
     * @param string $addressBookId
     * @param string $cardUri
     *
     * @throws Sabre_DAV_Exception_Forbidden
     *
     * @return bool
     */
    public function deleteCard($addressBookId, $cardUri)
    {
        q("DELETE FROM %s%saddressbookobjects WHERE `addressbook_id` = %d AND `uri` = '%s'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, intval($addressBookId), dbesc($cardUri));
        q('UPDATE %s%saddressbooks SET `ctag` = `ctag` + 1 WHERE `id` = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, intval($addressBookId));

        return true;
    }
}
