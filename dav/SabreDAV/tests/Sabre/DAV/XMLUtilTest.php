<?php

class Sabre_DAV_XMLUtilTest extends PHPUnit_Framework_TestCase {

    function testToClarkNotation() {

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><test1 xmlns="http://www.example.org/">Testdoc</test1>');

        $this->assertEquals(
            '{http://www.example.org/}test1',
            Sabre_DAV_XMLUtil::toClarkNotation($dom->firstChild)
        );

    }

    function testToClarkNotation2() {

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><s:test1 xmlns:s="http://www.example.org/">Testdoc</s:test1>');

        $this->assertEquals(
            '{http://www.example.org/}test1',
            Sabre_DAV_XMLUtil::toClarkNotation($dom->firstChild)
        );

    }

    function testToClarkNotationDAVNamespace() {

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><s:test1 xmlns:s="DAV:">Testdoc</s:test1>');

        $this->assertEquals(
            '{DAV:}test1',
            Sabre_DAV_XMLUtil::toClarkNotation($dom->firstChild)
        );

    }

    function testToClarkNotationNoElem() {

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><s:test1 xmlns:s="DAV:">Testdoc</s:test1>');

        $this->assertNull(
            Sabre_DAV_XMLUtil::toClarkNotation($dom->firstChild->firstChild)
        );

    }

    function testLoadDOMDocument() {

        $xml='<?xml version="1.0"?><document></document>';
        $dom = Sabre_DAV_XMLUtil::loadDOMDocument($xml);
        $this->assertTrue($dom instanceof DOMDocument);

    }

    /**
     * @depends testLoadDOMDocument
     * @expectedException Sabre_DAV_Exception_BadRequest
     */
    function testLoadDOMDocumentEmpty() {

        Sabre_DAV_XMLUtil::loadDOMDocument('');

    }

    /**
     * @expectedException Sabre_DAV_Exception_BadRequest
     */
    function testLoadDOMDocumentInvalid() {

        $xml='<?xml version="1.0"?><document></docu';
        $dom = Sabre_DAV_XMLUtil::loadDOMDocument($xml);

    }

    /**
     * @depends testLoadDOMDocument
     */
    function testLoadDOMDocumentUTF16() {

        $xml='<?xml version="1.0" encoding="UTF-16"?><root xmlns="DAV:">blabla</root>';
        $xml = iconv('UTF-8','UTF-16LE',$xml);
        $dom = Sabre_DAV_XMLUtil::loadDOMDocument($xml);
        $this->assertEquals('blabla',$dom->firstChild->nodeValue);

    }


    function testParseProperties() {

        $xml='<?xml version="1.0"?>
<root xmlns="DAV:">
  <prop>
    <displayname>Calendars</displayname>
  </prop>
</root>';

        $dom = Sabre_DAV_XMLUtil::loadDOMDocument($xml);
        $properties = Sabre_DAV_XMLUtil::parseProperties($dom->firstChild);

        $this->assertEquals(array(
            '{DAV:}displayname' => 'Calendars',
        ), $properties);



    }

    /**
     * @depends testParseProperties
     */
    function testParsePropertiesEmpty() {

        $xml='<?xml version="1.0"?>
<root xmlns="DAV:" xmlns:s="http://www.rooftopsolutions.nl/example">
  <prop>
    <displayname>Calendars</displayname>
  </prop>
  <prop>
    <s:example />
  </prop>
</root>';

        $dom = Sabre_DAV_XMLUtil::loadDOMDocument($xml);
        $properties = Sabre_DAV_XMLUtil::parseProperties($dom->firstChild);

        $this->assertEquals(array(
            '{DAV:}displayname' => 'Calendars',
            '{http://www.rooftopsolutions.nl/example}example' => null
        ), $properties);

    }


    /**
     * @depends testParseProperties
     */
    function testParsePropertiesComplex() {

        $xml='<?xml version="1.0"?>
<root xmlns="DAV:">
  <prop>
    <displayname>Calendars</displayname>
  </prop>
  <prop>
    <someprop>Complex value <b>right here</b></someprop>
  </prop>
</root>';

        $dom = Sabre_DAV_XMLUtil::loadDOMDocument($xml);
        $properties = Sabre_DAV_XMLUtil::parseProperties($dom->firstChild);

        $this->assertEquals(array(
            '{DAV:}displayname' => 'Calendars',
            '{DAV:}someprop'    => 'Complex value right here',
        ), $properties);

    }


    /**
     * @depends testParseProperties
     */
    function testParsePropertiesNoProperties() {

        $xml='<?xml version="1.0"?>
<root xmlns="DAV:">
  <prop>
  </prop>
</root>';

        $dom = Sabre_DAV_XMLUtil::loadDOMDocument($xml);
        $properties = Sabre_DAV_XMLUtil::parseProperties($dom->firstChild);

        $this->assertEquals(array(), $properties);

    }

    function testParsePropertiesMapHref() {

        $xml='<?xml version="1.0"?>
<root xmlns="DAV:">
  <prop>
    <displayname>Calendars</displayname>
  </prop>
  <prop>
    <someprop><href>http://sabredav.org/</href></someprop>
  </prop>
</root>';

        $dom = Sabre_DAV_XMLUtil::loadDOMDocument($xml);
        $properties = Sabre_DAV_XMLUtil::parseProperties($dom->firstChild,array('{DAV:}someprop'=>'Sabre_DAV_Property_Href'));

        $this->assertEquals(array(
            '{DAV:}displayname' => 'Calendars',
            '{DAV:}someprop'    => new Sabre_DAV_Property_Href('http://sabredav.org/',false),
        ), $properties);

    }

    function testParseClarkNotation() {

        $this->assertEquals(array(
            'DAV:',
            'foo',
        ), Sabre_DAV_XMLUtil::parseClarkNotation('{DAV:}foo'));

        $this->assertEquals(array(
            'http://example.org/ns/bla',
            'bar-soap',
        ), Sabre_DAV_XMLUtil::parseClarkNotation('{http://example.org/ns/bla}bar-soap'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testParseClarkNotationFail() {

        Sabre_DAV_XMLUtil::parseClarkNotation('}foo');

    }

}

