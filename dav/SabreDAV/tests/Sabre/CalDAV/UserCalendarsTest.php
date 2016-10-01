<?php

require_once 'Sabre/CalDAV/TestUtil.php';
require_once 'Sabre/DAVACL/MockPrincipalBackend.php';

/**
 * @covers Sabre_CalDAV_UserCalendars
 */
class Sabre_CalDAV_UserCalendarsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Sabre_CalDAV_UserCalendars
     */
    protected $usercalendars;
    /**
     * @var Sabre_CalDAV_Backend_PDO
     */
    protected $backend;
    protected $principalBackend;

    public function setup()
    {
        if (!SABRE_HASSQLITE) {
            $this->markTestSkipped('SQLite driver is not available');
        }
        $this->backend = Sabre_CalDAV_TestUtil::getBackend();
        $this->principalBackend = new Sabre_DAVACL_MockPrincipalBackend('realm');
        $this->usercalendars = new Sabre_CalDAV_UserCalendars($this->principalBackend, $this->backend, 'principals/user1');
    }

    public function testSimple()
    {
        $this->assertEquals('user1', $this->usercalendars->getName());
    }

    /**
     * @expectedException Sabre_DAV_Exception_NotFound
     * @depends testSimple
     */
    public function testGetChildNotFound()
    {
        $this->usercalendars->getChild('randomname');
    }

    public function testChildExists()
    {
        $this->assertFalse($this->usercalendars->childExists('foo'));
        $this->assertTrue($this->usercalendars->childExists('UUID-123467'));
    }

    public function testGetOwner()
    {
        $this->assertEquals('principals/user1', $this->usercalendars->getOwner());
    }

    public function testGetGroup()
    {
        $this->assertNull($this->usercalendars->getGroup());
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
        );
        $this->assertEquals($expected, $this->usercalendars->getACL());
    }

    /**
     * @expectedException Sabre_DAV_Exception_MethodNotAllowed
     */
    public function testSetACL()
    {
        $this->usercalendars->setACL(array());
    }

    /**
     * @expectedException Sabre_DAV_Exception_Forbidden
     * @depends testSimple
     */
    public function testSetName()
    {
        $this->usercalendars->setName('bla');
    }

    /**
     * @expectedException Sabre_DAV_Exception_Forbidden
     * @depends testSimple
     */
    public function testDelete()
    {
        $this->usercalendars->delete();
    }

    /**
     * @depends testSimple
     */
    public function testGetLastModified()
    {
        $this->assertNull($this->usercalendars->getLastModified());
    }

    /**
     * @expectedException Sabre_DAV_Exception_MethodNotAllowed
     * @depends testSimple
     */
    public function testCreateFile()
    {
        $this->usercalendars->createFile('bla');
    }

    /**
     * @expectedException Sabre_DAV_Exception_MethodNotAllowed
     * @depends testSimple
     */
    public function testCreateDirectory()
    {
        $this->usercalendars->createDirectory('bla');
    }

    /**
     * @depends testSimple
     */
    public function testCreateExtendedCollection()
    {
        $result = $this->usercalendars->createExtendedCollection('newcalendar', array('{DAV:}collection', '{urn:ietf:params:xml:ns:caldav}calendar'), array());
        $this->assertNull($result);
        $cals = $this->backend->getCalendarsForUser('principals/user1');
        $this->assertEquals(3, count($cals));
    }

    /**
     * @expectedException Sabre_DAV_Exception_InvalidResourceType
     * @depends testSimple
     */
    public function testCreateExtendedCollectionBadResourceType()
    {
        $this->usercalendars->createExtendedCollection('newcalendar', array('{DAV:}collection', '{DAV:}blabla'), array());
    }

    public function testGetSupportedPrivilegesSet()
    {
        $this->assertNull($this->usercalendars->getSupportedPrivilegeSet());
    }
}
