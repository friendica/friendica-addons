<?php


class Sabre_CalDAV_AnimexxUserCalendars implements Sabre_DAV_IExtendedCollection, Sabre_DAVACL_IACL {

    /**
     * Principal backend 
     * 
     * @var Sabre_DAVACL_IPrincipalBackend
     */
    protected $principalBackend;

    /**
     * CalDAV backends
     * 
     * @var array|Sabre_CalDAV_Backend_Common[]
     */
    protected $caldavBackends;

    /**
     * Principal information 
     * 
     * @var array 
     */
    protected $principalInfo;
    
    /**
     * Constructor 
     * 
     * @param Sabre_DAVACL_IPrincipalBackend $principalBackend
     * @param array|Sabre_CalDAV_Backend_Common[] $caldavBackends
     * @param mixed $userUri 
     */
    public function __construct(Sabre_DAVACL_IPrincipalBackend $principalBackend, $caldavBackends, $userUri) {

        $this->principalBackend = $principalBackend;
        $this->caldavBackends = $caldavBackends;
        $this->principalInfo = $principalBackend->getPrincipalByPath($userUri);
       
    }

    /**
     * Returns the name of this object 
     * 
     * @return string
     */
    public function getName() {
      
        list(,$name) = Sabre_DAV_URLUtil::splitPath($this->principalInfo['uri']);
        return $name; 

    }

	/**
	 * Updates the name of this object
	 *
	 * @param string $name
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return void
	 */
    public function setName($name) {

        throw new Sabre_DAV_Exception_Forbidden();

    }

	/**
	 * Deletes this object
	 *
	 * @throws Sabre_DAV_Exception_Forbidden
	 * @return void
	 */
    public function delete() {

        throw new Sabre_DAV_Exception_Forbidden();

    }

    /**
     * Returns the last modification date 
     * 
     * @return int 
     */
    public function getLastModified() {

        return null; 

    }

	/**
	 * Creates a new file under this object.
	 *
	 * This is currently not allowed
	 *
	 * @param string $filename
	 * @param resource $data
	 * @throws Sabre_DAV_Exception_MethodNotAllowed
	 * @return null|string|void
	 */
    public function createFile($filename, $data=null) {

        throw new Sabre_DAV_Exception_MethodNotAllowed('Creating new files in this collection is not supported');

    }

	/**
	 * Creates a new directory under this object.
	 *
	 * This is currently not allowed.
	 *
	 * @param string $filename
	 * @throws Sabre_DAV_Exception_MethodNotAllowed
	 * @return void
	 */
    public function createDirectory($filename) {

        throw new Sabre_DAV_Exception_MethodNotAllowed('Creating new collections in this collection is not supported');

    }

	/**
	 * Returns a single calendar, by name
	 *
	 * @param string $name
	 * @throws Sabre_DAV_Exception_NotFound
	 * @todo needs optimizing
	 * @return \Sabre_CalDAV_Calendar|\Sabre_DAV_INode
	 */
    public function getChild($name) {

        foreach($this->getChildren() as $child) {
            if ($name==$child->getName())
                return $child;

        }
        throw new Sabre_DAV_Exception_NotFound('Calendar with name \'' . $name . '\' could not be found');

    }

    /**
     * Checks if a calendar exists.
     * 
     * @param string $name
     * @todo needs optimizing
     * @return bool 
     */
    public function childExists($name) {

        foreach($this->getChildren() as $child) {
            if ($name==$child->getName())
                return true; 

        }
        return false;

    }

	/**
	 * Returns a list of calendars
	 *
	 * @return array|\Sabre_DAV_INode[]
	 */
     
    public function getChildren() {
		$objs = array();
		foreach ($this->caldavBackends as $backend) {
			$calendars = $backend->getCalendarsForUser($this->principalInfo["uri"]);
        	foreach($calendars as $calendar) {
            	$objs[] = new $calendar["calendar_class"]($this->principalBackend, $backend, $calendar);
        	}
		}
        //$objs[] = new Sabre_CalDAV_AnimexxUserZirkelCalendars($this->principalBackend, $this->caldavBackend, $this->username);
        return $objs;

    }

	/**
	 * Creates a new calendar
	 *
	 * @param string $name
	 * @param array $resourceType
	 * @param array $properties
	 * @throws Sabre_DAV_Exception_InvalidResourceType
	 * @return void
	 */
    public function createExtendedCollection($name, array $resourceType, array $properties) {

        if (!in_array('{urn:ietf:params:xml:ns:caldav}calendar',$resourceType) || count($resourceType)!==2) {
            throw new Sabre_DAV_Exception_InvalidResourceType('Unknown resourceType for this collection');
        }
        $this->caldavBackends[0]->createCalendar($this->principalInfo['uri'], $name, $properties);

    }

    /**
     * Returns the owner principal
     *
     * This must be a url to a principal, or null if there's no owner 
     * 
     * @return string|null
     */
    public function getOwner() {

        return $this->principalInfo['uri'];

    }

    /**
     * Returns a group principal
     *
     * This must be a url to a principal, or null if there's no owner
     * 
     * @return string|null 
     */
    public function getGroup() {

        return null;

    }

    /**
     * Returns a list of ACE's for this node.
     *
     * Each ACE has the following properties:
     *   * 'privilege', a string such as {DAV:}read or {DAV:}write. These are 
     *     currently the only supported privileges
     *   * 'principal', a url to the principal who owns the node
     *   * 'protected' (optional), indicating that this ACE is not allowed to 
     *      be updated. 
     * 
     * @return array 
     */
    public function getACL() {
        return array(
            array(
                'privilege' => '{DAV:}read',
                'principal' => $this->principalInfo['uri'],
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => $this->principalInfo['uri'],
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => $this->principalInfo['uri'] . '/calendar-proxy-write',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => $this->principalInfo['uri'] . '/calendar-proxy-write',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => $this->principalInfo['uri'] . '/calendar-proxy-read',
                'protected' => true,
            ),

        );

    }

    /**
     * Updates the ACL
     *
     * This method will receive a list of new ACE's. 
     * 
     * @param array $acl
	 * @throws Sabre_DAV_Exception_MethodNotAllowed
     * @return void
     */
    public function setACL(array $acl) {

        throw new Sabre_DAV_Exception_MethodNotAllowed('Changing ACL is not yet supported');

    }


	/**
	 * Returns the list of supported privileges for this node.
	 *
	 * The returned data structure is a list of nested privileges.
	 * See Sabre_DAVACL_Plugin::getDefaultSupportedPrivilegeSet for a simple
	 * standard structure.
	 *
	 * If null is returned from this method, the default privilege set is used,
	 * which is fine for most common usecases.
	 *
	 * @return array|null
	 */
	function getSupportedPrivilegeSet()
	{
		return null;
	}
}
