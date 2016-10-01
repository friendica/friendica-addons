<?php

abstract class Sabre_CardDAV_Backend_Common extends Sabre_CardDAV_Backend_Abstract
{
    /**
     * @abstract
     *
     * @return int
     */
    abstract public function getNamespace();

    /**
     * @static
     * @abstract
     *
     * @return string
     */
    abstract public static function getBackendTypeName();

    /**
     * @var array
     */
    private static $addressbookCache = array();

    /**
     * @var array
     */
    private static $addressbookObjectCache = array();

    /**
     * @static
     *
     * @param int $addressbookId
     *
     * @return array
     */
    public static function loadCalendarById($addressbookId)
    {
        if (!isset(self::$addressbookCache[$addressbookId])) {
            $c = q('SELECT * FROM %s%saddressbooks WHERE `id` = %d', CALDAV_SQL_DB, CALDAV_SQL_PREFIX, intval($addressbookId));
            self::$addressbookCache[$addressbookId] = $c[0];
        }

        return self::$addressbookCache[$addressbookId];
    }

    /**
     * @static
     *
     * @param int $obj_id
     *
     * @return array
     */
    public static function loadAddressbookobjectById($obj_id)
    {
        if (!isset(self::$addressbookObjectCache[$obj_id])) {
            $o = q('SELECT * FROM %s%saddressbookobjects WHERE `id` = %d',
                CALDAV_SQL_DB, CALDAV_SQL_PREFIX, intval($obj_id)
            );
            self::$addressbookObjectCache[$obj_id] = $o[0];
        }

        return self::$addressbookObjectCache[$obj_id];
    }

    /**
     * Updates an addressbook's properties.
     *
     * See Sabre_DAV_IProperties for a description of the mutations array, as
     * well as the return value.
     *
     * @param mixed $addressBookId
     * @param array $mutations
     *
     * @throws Sabre_DAV_Exception_Forbidden
     *
     * @see Sabre_DAV_IProperties::updateProperties
     *
     * @return bool|array
     */
    public function updateAddressBook($addressBookId, array $mutations)
    {
        $updates = array();

        foreach ($mutations as $property => $newValue) {
            switch ($property) {
                case '{DAV:}displayname':
                    $updates['displayname'] = $newValue;
                    break;
                case '{'.Sabre_CardDAV_Plugin::NS_CARDDAV.'}addressbook-description':
                    $updates['description'] = $newValue;
                    break;
                default:
                    // If any unsupported values were being updated, we must
                    // let the entire request fail.
                    return false;
            }
        }

        // No values are being updated?
        if (!$updates) {
            return false;
        }

        $query = 'UPDATE '.CALDAV_SQL_DB.CALDAV_SQL_PREFIX.'addressbooks SET ctag = ctag + 1 ';
        foreach ($updates as $key => $value) {
            $query .= ', `'.dbesc($key).'` = '.dbesc($key).' ';
        }
        $query .= ' WHERE id = '.intval($addressBookId);
        q($query);

        return true;
    }

    /**
     * @param int $addressbookId
     */
    protected function increaseAddressbookCtag($addressbookId)
    {
        q("UPDATE %s%saddressbooks SET `ctag` = `ctag` + 1 WHERE `id` = '%d'", CALDAV_SQL_DB, CALDAV_SQL_PREFIX, intval($addressbookId));
        self::$addressbookCache = array();
    }
}
