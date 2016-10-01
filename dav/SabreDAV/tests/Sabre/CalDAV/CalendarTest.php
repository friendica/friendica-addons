<?php

require_once 'Sabre/CalDAV/TestUtil.php';
require_once 'Sabre/DAVACL/MockPrincipalBackend.php';

class Sabre_CalDAV_CalendarTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Sabre_CalDAV_Backend_PDO
     */
    protected $backend;
    protected $principalBackend;
    /**
     * @var Sabre_CalDAV_Calendar
     */
    protected $calendar;
    /**
     * @var array
     */
    protected $calendars;

    public function setup()
    {
        if (!SABRE_HASSQLITE) {
            $this->markTestSkipped('SQLite driver is not available');
        }
        $this->backend = Sabre_CalDAV_TestUtil::getBackend();
        $this->principalBackend = new Sabre_DAVACL_MockPrincipalBackend();

        $this->calendars = $this->backend->getCalendarsForUser('principals/user1');
        $this->assertEquals(2, count($this->calendars));
        $this->calendar = new Sabre_CalDAV_Calendar($this->principalBackend, $this->backend, $this->calendars[0]);
    }

    public function teardown()
    {
        unset($this->backend);
    }

    public function testSimple()
    {
        $this->assertEquals($this->calendars[0]['uri'], $this->calendar->getName());
    }

    /**
     * @depends testSimple
     */
    public function testUpdateProperties()
    {
        $result = $this->calendar->updateProperties(array(
            '{DAV:}displayname' => 'NewName',
        ));

        $this->assertEquals(true, $result);

        $calendars2 = $this->backend->getCalendarsForUser('principals/user1');
        $this->assertEquals('NewName', $calendars2[0]['{DAV:}displayname']);
    }

    /**
     * @depends testSimple
     */
    public function testGetProperties()
    {
        $question = array(
            '{DAV:}owner',
            '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set',
            '{urn:ietf:params:xml:ns:caldav}supported-calendar-data',
            '{urn:ietf:params:xml:ns:caldav}supported-collation-set',
        );

        $result = $this->calendar->getProperties($question);

        foreach ($question as $q) {
            $this->assertArrayHasKey($q, $result);
        }

        $this->assertEquals(array('VEVENT', 'VTODO'), $result['{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set']->getValue());

        $this->assertTrue($result['{urn:ietf:params:xml:ns:caldav}supported-collation-set'] instanceof Sabre_CalDAV_Property_SupportedCollationSet);

        $this->assertTrue($result['{DAV:}owner'] instanceof Sabre_DAVACL_Property_Principal);
        $this->assertEquals('principals/user1', $result['{DAV:}owner']->getHref());
    }

    /**
     * @expectedException Sabre_DAV_Exception_NotFound
     * @depends testSimple
     */
    public function testGetChildNotFound()
    {
        $this->calendar->getChild('randomname');
    }

    /**
     * @depends testSimple
     */
    public function testGetChildren()
    {
        $children = $this->calendar->getChildren();
        $this->assertEquals(1, count($children));

        $this->assertTrue($children[0] instanceof Sabre_CalDAV_CalendarObject);
    }

    /**
     * @depends testGetChildren
     */
    public function testChildExists()
    {
        $this->assertFalse($this->calendar->childExists('foo'));

        $children = $this->calendar->getChildren();
        $this->assertTrue($this->calendar->childExists($children[0]->getName()));
    }

    /**
     * @expectedException Sabre_DAV_Exception_MethodNotAllowed
     */
    public function testCreateDirectory()
    {
        $this->calendar->createDirectory('hello');
    }

    /**
     * @expectedException Sabre_DAV_Exception_MethodNotAllowed
     */
    public function testSetName()
    {
        $this->calendar->setName('hello');
    }

    public function testGetLastModified()
    {
        $this->assertNull($this->calendar->getLastModified());
    }

    public function testCreateFile()
    {
        $file = fopen('php://memory', 'r+');
        fwrite($file, Sabre_CalDAV_TestUtil::getTestCalendarData());
        rewind($file);

        $this->calendar->createFile('hello', $file);

        $file = $this->calendar->getChild('hello');
        $this->assertTrue($file instanceof Sabre_CalDAV_CalendarObject);
    }

    public function testCreateFileNoSupportedComponents()
    {
        $file = fopen('php://memory', 'r+');
        fwrite($file, Sabre_CalDAV_TestUtil::getTestCalendarData());
        rewind($file);

        $calendar = new Sabre_CalDAV_Calendar($this->principalBackend, $this->backend, $this->calendars[1]);
        $calendar->createFile('hello', $file);

        $file = $calendar->getChild('hello');
        $this->assertTrue($file instanceof Sabre_CalDAV_CalendarObject);
    }

    public function testDelete()
    {
        $this->calendar->delete();

        $calendars = $this->backend->getCalendarsForUser('principals/user1');
        $this->assertEquals(1, count($calendars));
    }

    public function testGetOwner()
    {
        $this->assertEquals('principals/user1', $this->calendar->getOwner());
    }

    public function testGetGroup()
    {
        $this->assertNull($this->calendar->getGroup());
    }

    public function testGetACL()
    {
        $expected = array(
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => 'principals/user1',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1/calendar-proxy-write',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => 'principals/user1/calendar-proxy-write',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => 'principals/user1/calendar-proxy-read',
                'protected' => true,
            ),
            array(
                'privilege' => '{'.Sabre_CalDAV_Plugin::NS_CALDAV.'}read-free-busy',
                'principal' => '{DAV:}authenticated',
                'protected' => true,
            ),
        );
        $this->assertEquals($expected, $this->calendar->getACL());
    }

    /**
     * @expectedException Sabre_DAV_Exception_MethodNotAllowed
     */
    public function testSetACL()
    {
        $this->calendar->setACL(array());
    }

    public function testGetSupportedPrivilegesSet()
    {
        $result = $this->calendar->getSupportedPrivilegeSet();

        $this->assertEquals(
            '{'.Sabre_CalDAV_Plugin::NS_CALDAV.'}read-free-busy',
            $result['aggregates'][0]['aggregates'][2]['privilege']
        );
    }
}
