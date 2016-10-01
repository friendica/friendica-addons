<?php

class Sabre_CardDAV_UserAddressBooksTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Sabre_CardDAV_UserAddressBooks
     */
    protected $s;
    protected $backend;

    public function setUp()
    {
        $this->backend = new Sabre_CardDAV_Backend_Mock();
        $this->s = new Sabre_CardDAV_UserAddressBooks(
            $this->backend,
            'principals/user1'
        );
    }

    public function testGetName()
    {
        $this->assertEquals('user1', $this->s->getName());
    }

    /**
     * @expectedException Sabre_DAV_Exception_MethodNotAllowed
     */
    public function testSetName()
    {
        $this->s->setName('user2');
    }

    /**
     * @expectedException Sabre_DAV_Exception_MethodNotAllowed
     */
    public function testDelete()
    {
        $this->s->delete();
    }

    public function testGetLastModified()
    {
        $this->assertNull($this->s->getLastModified());
    }

    /**
     * @expectedException Sabre_DAV_Exception_MethodNotAllowed
     */
    public function testCreateFile()
    {
        $this->s->createFile('bla');
    }

    /**
     * @expectedException Sabre_DAV_Exception_MethodNotAllowed
     */
    public function testCreateDirectory()
    {
        $this->s->createDirectory('bla');
    }

    public function testGetChild()
    {
        $child = $this->s->getChild('book1');
        $this->assertInstanceOf('Sabre_CardDAV_AddressBook', $child);
        $this->assertEquals('book1', $child->getName());
    }

    /**
     * @expectedException Sabre_DAV_Exception_NotFound
     */
    public function testGetChild404()
    {
        $this->s->getChild('book2');
    }

    public function testGetChildren()
    {
        $children = $this->s->getChildren();
        $this->assertEquals(1, count($children));
        $this->assertInstanceOf('Sabre_CardDAV_AddressBook', $children[0]);
        $this->assertEquals('book1', $children[0]->getName());
    }

    public function testCreateExtendedCollection()
    {
        $resourceType = array(
            '{'.Sabre_CardDAV_Plugin::NS_CARDDAV.'}addressbook',
            '{DAV:}collection',
        );
        $this->s->createExtendedCollection('book2', $resourceType, array('{DAV:}displayname' => 'a-book 2'));

        $this->assertEquals(array(
            'id' => 'book2',
            'uri' => 'book2',
            '{DAV:}displayname' => 'a-book 2',
            'principaluri' => 'principals/user1',
        ), $this->backend->addressBooks[1]);
    }

    /**
     * @expectedException Sabre_DAV_Exception_InvalidResourceType
     */
    public function testCreateExtendedCollectionInvalid()
    {
        $resourceType = array(
            '{DAV:}collection',
        );
        $this->s->createExtendedCollection('book2', $resourceType, array('{DAV:}displayname' => 'a-book 2'));
    }

    public function testACLMethods()
    {
        $this->assertEquals('principals/user1', $this->s->getOwner());
        $this->assertNull($this->s->getGroup());
        $this->assertEquals(array(
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
        ), $this->s->getACL());
    }

    /**
     * @expectedException Sabre_DAV_Exception_MethodNotAllowed
     */
    public function testSetACL()
    {
        $this->s->setACL(array());
    }

    public function testGetSupportedPrivilegeSet()
    {
        $this->assertNull(
            $this->s->getSupportedPrivilegeSet()
        );
    }
}
