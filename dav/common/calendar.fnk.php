<?php


define("DAV_ACL_READ", "{DAV:}read");
define("DAV_ACL_WRITE", "{DAV:}write");
define("DAV_DISPLAYNAME", "{DAV:}displayname");
define("DAV_CALENDARCOLOR", "{http://apple.com/ns/ical/}calendar-color");


class DAVVersionMismatchException extends Exception {}


class vcard_source_data_email
{
	public $email, $type;

	function __construct($type, $email)
	{
		$this->email = $email;
		$this->type  = $type;
	}
}

class vcard_source_data_homepage
{
	public $homepage, $type;

	function __construct($type, $homepage)
	{
		$this->homepage = $homepage;
		$this->type     = $type;
	}
}

class vcard_source_data_telephone
{
	public $telephone, $type;

	function __construct($type, $telephone)
	{
		$this->telephone = $telephone;
		$this->type      = $type;
	}
}

class vcard_source_data_socialnetwork
{
	public $nick, $type, $url;

	function __construct($type, $nick, $url)
	{
		$this->nick = $nick;
		$this->type = $type;
		$this->url  = $url;
	}
}

class vcard_source_data_address
{
	public $street, $street2, $zip, $city, $country, $type;
}

class vcard_source_data_photo
{
	public $binarydata;
	public $width, $height;
	public $type;
}

class vcard_source_data
{
	function __construct($name_first, $name_middle, $name_last)
	{
		$this->name_first  = $name_first;
		$this->name_middle = $name_middle;
		$this->name_last   = $name_last;
	}

	public $name_first, $name_middle, $name_last;
	public $last_update;
	public $picture_data;

	/** @var array|vcard_source_data_telephone[] $telephones */
	public $telephones;

	/** @var array|vcard_source_data_homepage[] $homepages */
	public $homepages;

	/** @var array|vcard_source_data_socialnetwork[] $socialnetworks */
	public $socialnetworks;

	/** @var array|vcard_source_data_email[] $email */
	public $emails;

	/** @var array|vcard_source_data_address[] $addresses */
	public $addresses;

	/** @var vcard_source_data_photo */
	public $photo;
}


/**
 * @param int $phpDate (UTC)
 * @return string (Lokalzeit)
 */
function wdcal_php2MySqlTime($phpDate)
{
	return date("Y-m-d H:i:s", $phpDate);
}

/**
 * @param string $sqlDate
 * @return int
 */
function wdcal_mySql2PhpTime($sqlDate)
{
	$ts = DateTime::createFromFormat("Y-m-d H:i:s", $sqlDate);
	return $ts->format("U");
}

/**
 * @param string $myqlDate
 * @return array
 */
function wdcal_mySql2icalTime($myqlDate)
{
	$x             = explode(" ", $myqlDate);
	$y             = explode("-", $x[0]);
	$ret           = array("year"=> $y[0], "month"=> $y[1], "day"=> $y[2]);
	$y             = explode(":", $x[1]);
	$ret["hour"]   = $y[0];
	$ret["minute"] = $y[1];
	$ret["second"] = $y[2];
	return $ret;
}


/**
 * @param string $str
 * @return string
 */
function icalendar_sanitize_string($str = "")
{
	return preg_replace("/[\\r\\n]+/siu", "\r\n", $str);
}


/**
 * @return Sabre_CalDAV_AnimexxCalendarRootNode
 */
function dav_createRootCalendarNode()
{
	$backends = array(Sabre_CalDAV_Backend_Private::getInstance());
	foreach ($GLOBALS["CALDAV_PRIVATE_SYSTEM_BACKENDS"] as $backendclass) $backends[] = $backendclass::getInstance();
	return new Sabre_CalDAV_AnimexxCalendarRootNode(Sabre_DAVACL_PrincipalBackend_Std::getInstance(), $backends);
}

/**
 * @return Sabre_CardDAV_AddressBookRootFriendica
 */
function dav_createRootContactsNode()
{
	$backends = array(Sabre_CardDAV_Backend_Std::getInstance());
	foreach ($GLOBALS["CARDDAV_PRIVATE_SYSTEM_BACKENDS"] as $backendclass) $backends[] = $backendclass::getInstance();

	return new Sabre_CardDAV_AddressBookRootFriendica(Sabre_DAVACL_PrincipalBackend_Std::getInstance(), $backends);
}


/**
 * @param bool $force_authentication
 * @param bool $needs_caldav
 * @param bool $needs_carddav
 * @return Sabre_DAV_Server
 */
function dav_create_server($force_authentication = false, $needs_caldav = true, $needs_carddav = true)
{
	$arr = array(
		new Sabre_DAV_SimpleCollection('principals', array(
			new Sabre_CalDAV_Principal_Collection(Sabre_DAVACL_PrincipalBackend_Std::getInstance(), "principals/users"),
		)),
	);
	if ($needs_caldav) $arr[] = dav_createRootCalendarNode();
	if ($needs_carddav) $arr[] = dav_createRootContactsNode();


	$tree = new Sabre_DAV_SimpleCollection('root', $arr);

// The object tree needs in turn to be passed to the server class
	$server = new Sabre_DAV_Server($tree);

	if (CALDAV_URL_PREFIX != "") $server->setBaseUri(CALDAV_URL_PREFIX);

	$authPlugin = new Sabre_DAV_Auth_Plugin(Sabre_DAV_Auth_Backend_Std::getInstance(), DAV_APPNAME);
	$server->addPlugin($authPlugin);

	if ($needs_caldav) {
		$caldavPlugin = new Sabre_CalDAV_Plugin();
		$server->addPlugin($caldavPlugin);
	}
	if ($needs_carddav) {
		$carddavPlugin = new Sabre_CardDAV_Plugin();
		$server->addPlugin($carddavPlugin);
	}

	if ($GLOBALS["CALDAV_ACL_PLUGIN_CLASS"] != "") {
		$aclPlugin                      = new $GLOBALS["CALDAV_ACL_PLUGIN_CLASS"]();
		$aclPlugin->defaultUsernamePath = "principals/users";
		$server->addPlugin($aclPlugin);
	} else {
		$aclPlugin = new Sabre_DAVACL_Plugin();
		$aclPlugin->defaultUsernamePath = "principals/users";
		$server->addPlugin($aclPlugin);
	}

	if ($force_authentication) $server->broadcastEvent('beforeMethod', array("GET", "/")); // Make it authenticate

	return $server;
}


/**
 * @param Sabre_DAV_Server $server
 * @param string $with_privilege
 * @return array|Sabre_CalDAV_Calendar[]
 */
function dav_get_current_user_calendars(&$server, $with_privilege = "")
{
	if ($with_privilege == "") $with_privilege = DAV_ACL_READ;

	$a             = get_app();
	$calendar_path = "/calendars/" . strtolower($a->user["nickname"]) . "/";

	/** @var Sabre_CalDAV_AnimexxUserCalendars $tree  */
	$tree = $server->tree->getNodeForPath($calendar_path);
	/** @var array|Sabre_CalDAV_Calendar[] $calendars  */
	$children = $tree->getChildren();

	$calendars = array();
	/** @var Sabre_DAVACL_Plugin $aclplugin  */
	$aclplugin = $server->getPlugin("acl");
	foreach ($children as $child) if (is_a($child, "Sabre_CalDAV_Calendar") || is_subclass_of($child, "Sabre_CalDAV_Calendar")) {
		if ($with_privilege != "") {
			$caluri = $calendar_path . $child->getName();
			if ($aclplugin->checkPrivileges($caluri, $with_privilege, Sabre_DAVACL_Plugin::R_PARENT, false)) $calendars[] = $child;
		} else {
			$calendars[] = $child;
		}
	}
	return $calendars;
}


/**
 * @param Sabre_DAV_Server $server
 * @param Sabre_CalDAV_Calendar $calendar
 * @param string $calendarobject_uri
 * @param string $with_privilege
 * @return null|Sabre\VObject\Component\VCalendar
 */
function dav_get_current_user_calendarobject(&$server, &$calendar, $calendarobject_uri, $with_privilege = "")
{
	$obj = $calendar->getChild($calendarobject_uri);

	if ($with_privilege == "") $with_privilege = DAV_ACL_READ;

	$a   = get_app();
	$uri = "/calendars/" . strtolower($a->user["nickname"]) . "/" . $calendar->getName() . "/" . $calendarobject_uri;

	/** @var Sabre_DAVACL_Plugin $aclplugin  */
	$aclplugin = $server->getPlugin("acl");
	if (!$aclplugin->checkPrivileges($uri, $with_privilege, Sabre_DAVACL_Plugin::R_PARENT, false)) return null;

	$data    = $obj->get();
	$vObject = Sabre\VObject\Reader::read($data);

	return $vObject;
}


/**
 * @param Sabre_DAV_Server $server
 * @param int $id
 * @param string $with_privilege
 * @return null|Sabre_CalDAV_Calendar
 */
function dav_get_current_user_calendar_by_id(&$server, $id, $with_privilege = "")
{
	$calendars = dav_get_current_user_calendars($server, $with_privilege);

	$calendar = null;
	foreach ($calendars as $cal) {
		$prop = $cal->getProperties(array("id"));
		if (isset($prop["id"]) && $prop["id"] == $id) $calendar = $cal;
	}

	return $calendar;
}


/**
 * @param string $uid
 * @return Sabre\VObject\Component\VCalendar $vObject
 */
function dav_create_empty_vevent($uid = "")
{
	if ($uid == "") $uid = uniqid();
	return Sabre\VObject\Reader::read("BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//" . DAV_APPNAME . "//DAV-Plugin//EN\r\nBEGIN:VEVENT\r\nUID:" . $uid . "@" . dav_compat_get_hostname() .
		"\r\nDTSTAMP:" . date("Ymd") . "T" . date("His") . "Z\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n");
}


/**
 * @param Sabre\VObject\Component\VCalendar $vObject
 * @return Sabre\VObject\Component\VEvent|null
 */
function dav_get_eventComponent(&$vObject)
{
	$component     = null;
	$componentType = "";
	foreach ($vObject->getComponents() as $component) {
		if ($component->name !== 'VTIMEZONE') {
			$componentType = $component->name;
			break;
		}
	}
	if ($componentType != "VEVENT") return null;

	return $component;
}
