<?php

class Sabre_DAVACL_Property_PrincipalTest extends PHPUnit_Framework_TestCase {

    function testSimple() {

        $principal = new Sabre_DAVACL_Property_Principal(Sabre_DAVACL_Property_Principal::UNAUTHENTICATED);
        $this->assertEquals(Sabre_DAVACL_Property_Principal::UNAUTHENTICATED, $principal->getType());
        $this->assertNull($principal->getHref());

        $principal = new Sabre_DAVACL_Property_Principal(Sabre_DAVACL_Property_Principal::AUTHENTICATED);
        $this->assertEquals(Sabre_DAVACL_Property_Principal::AUTHENTICATED, $principal->getType());
        $this->assertNull($principal->getHref());

        $principal = new Sabre_DAVACL_Property_Principal(Sabre_DAVACL_Property_Principal::HREF,'admin');
        $this->assertEquals(Sabre_DAVACL_Property_Principal::HREF, $principal->getType());
        $this->assertEquals('admin',$principal->getHref());

    }

    /**
     * @depends testSimple
     * @expectedException Sabre_DAV_Exception
     */
    function testNoHref() {

        $principal = new Sabre_DAVACL_Property_Principal(Sabre_DAVACL_Property_Principal::HREF);

    }

    /**
     * @depends testSimple
     */
    function testSerializeUnAuthenticated() {

        $prin = new Sabre_DAVACL_Property_Principal(Sabre_DAVACL_Property_Principal::UNAUTHENTICATED);

        $doc = new DOMDocument();
        $root = $doc->createElement('d:principal');
        $root->setAttribute('xmlns:d','DAV:');

        $doc->appendChild($root);
        $objectTree = new Sabre_DAV_ObjectTree(new Sabre_DAV_SimpleCollection('rootdir'));
        $server = new Sabre_DAV_Server($objectTree);

        $prin->serialize($server, $root);

        $xml = $doc->saveXML();

        $this->assertEquals(
'<?xml version="1.0"?>
<d:principal xmlns:d="DAV:">' .
'<d:unauthenticated/>' .
'</d:principal>
', $xml);

    }


    /**
     * @depends testSerializeUnAuthenticated
     */
    function testSerializeAuthenticated() {

        $prin = new Sabre_DAVACL_Property_Principal(Sabre_DAVACL_Property_Principal::AUTHENTICATED);

        $doc = new DOMDocument();
        $root = $doc->createElement('d:principal');
        $root->setAttribute('xmlns:d','DAV:');

        $doc->appendChild($root);
        $objectTree = new Sabre_DAV_ObjectTree(new Sabre_DAV_SimpleCollection('rootdir'));
        $server = new Sabre_DAV_Server($objectTree);

        $prin->serialize($server, $root);

        $xml = $doc->saveXML();

        $this->assertEquals(
'<?xml version="1.0"?>
<d:principal xmlns:d="DAV:">' .
'<d:authenticated/>' .
'</d:principal>
', $xml);

    }


    /**
     * @depends testSerializeUnAuthenticated
     */
    function testSerializeHref() {

        $prin = new Sabre_DAVACL_Property_Principal(Sabre_DAVACL_Property_Principal::HREF,'principals/admin');

        $doc = new DOMDocument();
        $root = $doc->createElement('d:principal');
        $root->setAttribute('xmlns:d','DAV:');

        $doc->appendChild($root);
        $objectTree = new Sabre_DAV_ObjectTree(new Sabre_DAV_SimpleCollection('rootdir'));
        $server = new Sabre_DAV_Server($objectTree);

        $prin->serialize($server, $root);

        $xml = $doc->saveXML();

        $this->assertEquals(
'<?xml version="1.0"?>
<d:principal xmlns:d="DAV:">' .
'<d:href>/principals/admin</d:href>' .
'</d:principal>
', $xml);

    }

    function testUnserializeHref() {

        $xml = '<?xml version="1.0"?>
<d:principal xmlns:d="DAV:">' .
'<d:href>/principals/admin</d:href>' .
'</d:principal>';

        $dom = Sabre_DAV_XMLUtil::loadDOMDocument($xml);

        $principal = Sabre_DAVACL_Property_Principal::unserialize($dom->firstChild);
        $this->assertEquals(Sabre_DAVACL_Property_Principal::HREF, $principal->getType());
        $this->assertEquals('/principals/admin', $principal->getHref());

    }

    function testUnserializeAuthenticated() {

        $xml = '<?xml version="1.0"?>
<d:principal xmlns:d="DAV:">' .
'  <d:authenticated />' .
'</d:principal>';

        $dom = Sabre_DAV_XMLUtil::loadDOMDocument($xml);

        $principal = Sabre_DAVACL_Property_Principal::unserialize($dom->firstChild);
        $this->assertEquals(Sabre_DAVACL_Property_Principal::AUTHENTICATED, $principal->getType());

    }

    function testUnserializeUnauthenticated() {

        $xml = '<?xml version="1.0"?>
<d:principal xmlns:d="DAV:">' .
'  <d:unauthenticated />' .
'</d:principal>';

        $dom = Sabre_DAV_XMLUtil::loadDOMDocument($xml);

        $principal = Sabre_DAVACL_Property_Principal::unserialize($dom->firstChild);
        $this->assertEquals(Sabre_DAVACL_Property_Principal::UNAUTHENTICATED, $principal->getType());

    }

    /**
     * @expectedException Sabre_DAV_Exception_BadRequest
     */
    function testUnserializeUnknown() {

        $xml = '<?xml version="1.0"?>
<d:principal xmlns:d="DAV:">' .
'  <d:foo />' .
'</d:principal>';

        $dom = Sabre_DAV_XMLUtil::loadDOMDocument($xml);

        Sabre_DAVACL_Property_Principal::unserialize($dom->firstChild);

    }

}
