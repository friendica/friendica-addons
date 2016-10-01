<?php

class Sabre_CalDAV_Principal_ProxyReadTest extends PHPUnit_Framework_TestCase
{
    protected $backend;

    public function getInstance()
    {
        $backend = new Sabre_DAVACL_MockPrincipalBackend();
        $principal = new Sabre_CalDAV_Principal_ProxyRead($backend, array(
            'uri' => 'principal/user',
        ));
        $this->backend = $backend;

        return $principal;
    }

    public function testGetName()
    {
        $i = $this->getInstance();
        $this->assertEquals('calendar-proxy-read', $i->getName());
    }
    public function testGetDisplayName()
    {
        $i = $this->getInstance();
        $this->assertEquals('calendar-proxy-read', $i->getDisplayName());
    }

    public function testGetLastModified()
    {
        $i = $this->getInstance();
        $this->assertNull($i->getLastModified());
    }

    /**
     * @expectedException Sabre_DAV_Exception_Forbidden
     */
    public function testDelete()
    {
        $i = $this->getInstance();
        $i->delete();
    }

    /**
     * @expectedException Sabre_DAV_Exception_Forbidden
     */
    public function testSetName()
    {
        $i = $this->getInstance();
        $i->setName('foo');
    }

    public function testGetAlternateUriSet()
    {
        $i = $this->getInstance();
        $this->assertEquals(array(), $i->getAlternateUriSet());
    }

    public function testGetPrincipalUri()
    {
        $i = $this->getInstance();
        $this->assertEquals('principal/user/calendar-proxy-read', $i->getPrincipalUrl());
    }

    public function testGetGroupMemberSet()
    {
        $i = $this->getInstance();
        $this->assertEquals(array(), $i->getGroupMemberSet());
    }

    public function testGetGroupMembership()
    {
        $i = $this->getInstance();
        $this->assertEquals(array(), $i->getGroupMembership());
    }

    public function testSetGroupMemberSet()
    {
        $i = $this->getInstance();
        $i->setGroupMemberSet(array('principals/foo'));

        $expected = array(
            $i->getPrincipalUrl() => array('principals/foo'),
        );

        $this->assertEquals($expected, $this->backend->groupMembers);
    }
}
