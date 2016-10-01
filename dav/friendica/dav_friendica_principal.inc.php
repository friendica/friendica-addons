<?php


class Sabre_DAVACL_PrincipalBackend_Std implements Sabre_DAVACL_IPrincipalBackend
{
    /**
     * Principals prefix.
     *
     * @var string
     */
    public $prefix = 'principals/users';

    /**
     * @var Sabre_DAV_Auth_Backend_AbstractBasic;
     */
    protected $authBackend;

    public function __construct(&$authBackend)
    {
        $this->authBackend = &$authBackend;
    }

    /**
     * @var Sabre_DAVACL_IPrincipalBackend|null
     */
    private static $intstance = null;

    /**
     * @static
     *
     * @return Sabre_DAVACL_IPrincipalBackend
     */
    public static function &getInstance()
    {
        if (is_null(self::$intstance)) {
            $authBackend = Sabre_DAV_Auth_Backend_Std::getInstance();
            self::$intstance = new self($authBackend);
        }

        return self::$intstance;
    }

    /**
     * Returns a list of principals based on a prefix.
     *
     * This prefix will often contain something like 'principals'. You are only
     * expected to return principals that are in this base path.
     *
     * You are expected to return at least a 'uri' for every user, you can
     * return any additional properties if you wish so. Common properties are:
     *   {DAV:}displayname
     *   {http://sabredav.org/ns}email-address - This is a custom SabreDAV
     *     field that's actualy injected in a number of other properties. If
     *     you have an email address, use this property.
     *
     * @param string $prefixPath
     *
     * @return array
     */
    public function getPrincipalsByPrefix($prefixPath)
    {

        // This backend only support principals in one collection
        if ($prefixPath !== $this->prefix) {
            return array();
        }

        $users = array();

        $r = q("SELECT `nickname` FROM `user` WHERE `nickname` = '%s'", escape_tags($this->authBackend->getCurrentUser()));
        foreach ($r as $t) {
            $users[] = array(
                'uri' => $this->prefix.'/'.strtolower($t['nickname']),
                '{DAV:}displayname' => $t['nickname'],
            );
        }

        return $users;
    }

    /**
     * Returns a specific principal, specified by it's path.
     * The returned structure should be the exact same as from
     * getPrincipalsByPrefix.
     *
     * @param string $path
     *
     * @return array
     */
    public function getPrincipalByPath($path)
    {
        list($prefixPath, $userName) = Sabre_DAV_URLUtil::splitPath($path);

        // This backend only support principals in one collection
        if ($prefixPath !== $this->prefix) {
            return null;
        }

        $r = q("SELECT `nickname` FROM `user` WHERE `nickname` = '%s'", escape_tags($userName));
        if (count($r) == 0) {
            return array();
        }

        return array(
            'uri' => $this->prefix.'/'.strtolower($r[0]['nickname']),
            '{DAV:}displayname' => $r[0]['nickname'],
        );
    }

    public function getGroupMemberSet($principal)
    {
        return array();
    }

    public function getGroupMembership($principal)
    {
        return array();
    }

    /**
     * Updates the list of group members for a group principal.
     *
     * The principals should be passed as a list of uri's.
     *
     * @param string $principal
     * @param array  $members
     *
     * @throws Sabre_DAV_Exception
     */
    public function setGroupMemberSet($principal, array $members)
    {
        throw new Sabre_DAV_Exception('Operation not supported');
    }

    /**
     * Updates one ore more webdav properties on a principal.
     *
     * The list of mutations is supplied as an array. Each key in the array is
     * a propertyname, such as {DAV:}displayname.
     *
     * Each value is the actual value to be updated. If a value is null, it
     * must be deleted.
     *
     * This method should be atomic. It must either completely succeed, or
     * completely fail. Success and failure can simply be returned as 'true' or
     * 'false'.
     *
     * It is also possible to return detailed failure information. In that case
     * an array such as this should be returned:
     *
     * array(
     *   200 => array(
     *      '{DAV:}prop1' => null,
     *   ),
     *   201 => array(
     *      '{DAV:}prop2' => null,
     *   ),
     *   403 => array(
     *      '{DAV:}prop3' => null,
     *   ),
     *   424 => array(
     *      '{DAV:}prop4' => null,
     *   ),
     * );
     *
     * In this previous example prop1 was successfully updated or deleted, and
     * prop2 was succesfully created.
     *
     * prop3 failed to update due to '403 Forbidden' and because of this prop4
     * also could not be updated with '424 Failed dependency'.
     *
     * This last example was actually incorrect. While 200 and 201 could appear
     * in 1 response, if there's any error (403) the other properties should
     * always fail with 423 (failed dependency).
     *
     * But anyway, if you don't want to scratch your head over this, just
     * return true or false.
     *
     * @param string $path
     * @param array  $mutations
     *
     * @return array|bool
     */
    public function updatePrincipal($path, $mutations)
    {
        // TODO: Implement updatePrincipal() method.
    }

    /**
     * This method is used to search for principals matching a set of
     * properties.
     *
     * This search is specifically used by RFC3744's principal-property-search
     * REPORT. You should at least allow searching on
     * http://sabredav.org/ns}email-address.
     *
     * The actual search should be a unicode-non-case-sensitive search. The
     * keys in searchProperties are the WebDAV property names, while the values
     * are the property values to search on.
     *
     * If multiple properties are being searched on, the search should be
     * AND'ed.
     *
     * This method should simply return an array with full principal uri's.
     *
     * If somebody attempted to search on a property the backend does not
     * support, you should simply return 0 results.
     *
     * You can also just return 0 results if you choose to not support
     * searching at all, but keep in mind that this may stop certain features
     * from working.
     *
     * @param string $prefixPath
     * @param array  $searchProperties
     *
     * @return array
     */
    public function searchPrincipals($prefixPath, array $searchProperties)
    {
        // TODO: Implement searchPrincipals() method.
    }
}
