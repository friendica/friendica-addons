<?php

namespace Sabre\VObject;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testReadComponent()
    {
        $data = "BEGIN:VCALENDAR\r\nEND:VCALENDAR";

        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCALENDAR', $result->name);
        $this->assertEquals(0, count($result->children));
    }

    public function testReadComponentUnixNewLine()
    {
        $data = "BEGIN:VCALENDAR\nEND:VCALENDAR";

        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCALENDAR', $result->name);
        $this->assertEquals(0, count($result->children));
    }

    public function testReadComponentMacNewLine()
    {
        $data = "BEGIN:VCALENDAR\rEND:VCALENDAR";

        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCALENDAR', $result->name);
        $this->assertEquals(0, count($result->children));
    }

    public function testReadComponentLineFold()
    {
        $data = "BEGIN:\r\n\tVCALENDAR\r\nE\r\n ND:VCALENDAR";

        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCALENDAR', $result->name);
        $this->assertEquals(0, count($result->children));
    }

    /**
     * @expectedException Sabre\VObject\ParseException
     */
    public function testReadCorruptComponent()
    {
        $data = "BEGIN:VCALENDAR\r\nEND:FOO";

        $result = Reader::read($data);
    }

    public function testReadProperty()
    {
        $data = 'PROPNAME:propValue';
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Property', $result);
        $this->assertEquals('PROPNAME', $result->name);
        $this->assertEquals('propValue', $result->value);
    }

    public function testReadPropertyWithNewLine()
    {
        $data = 'PROPNAME:Line1\\nLine2\\NLine3\\\\Not the 4th line!';
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Property', $result);
        $this->assertEquals('PROPNAME', $result->name);
        $this->assertEquals("Line1\nLine2\nLine3\\Not the 4th line!", $result->value);
    }

    public function testReadMappedProperty()
    {
        $data = 'DTSTART:20110529';
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Property\\DateTime', $result);
        $this->assertEquals('DTSTART', $result->name);
        $this->assertEquals('20110529', $result->value);
    }

    public function testReadMappedPropertyGrouped()
    {
        $data = 'foo.DTSTART:20110529';
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Property\\DateTime', $result);
        $this->assertEquals('DTSTART', $result->name);
        $this->assertEquals('20110529', $result->value);
    }

    /**
     * @expectedException Sabre\VObject\ParseException
     */
    public function testReadBrokenLine()
    {
        $data = 'PROPNAME;propValue';
        $result = Reader::read($data);
    }

    public function testReadPropertyInComponent()
    {
        $data = array(
            'BEGIN:VCALENDAR',
            'PROPNAME:propValue',
            'END:VCALENDAR',
        );

        $result = Reader::read(implode("\r\n", $data));

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCALENDAR', $result->name);
        $this->assertEquals(1, count($result->children));
        $this->assertInstanceOf('Sabre\\VObject\\Property', $result->children[0]);
        $this->assertEquals('PROPNAME', $result->children[0]->name);
        $this->assertEquals('propValue', $result->children[0]->value);
    }
    public function testReadNestedComponent()
    {
        $data = array(
            'BEGIN:VCALENDAR',
            'BEGIN:VTIMEZONE',
            'BEGIN:DAYLIGHT',
            'END:DAYLIGHT',
            'END:VTIMEZONE',
            'END:VCALENDAR',
        );

        $result = Reader::read(implode("\r\n", $data));

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCALENDAR', $result->name);
        $this->assertEquals(1, count($result->children));
        $this->assertInstanceOf('Sabre\\VObject\\Component', $result->children[0]);
        $this->assertEquals('VTIMEZONE', $result->children[0]->name);
        $this->assertEquals(1, count($result->children[0]->children));
        $this->assertInstanceOf('Sabre\\VObject\\Component', $result->children[0]->children[0]);
        $this->assertEquals('DAYLIGHT', $result->children[0]->children[0]->name);
    }

    public function testReadPropertyParameter()
    {
        $data = 'PROPNAME;PARAMNAME=paramvalue:propValue';
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Property', $result);
        $this->assertEquals('PROPNAME', $result->name);
        $this->assertEquals('propValue', $result->value);
        $this->assertEquals(1, count($result->parameters));
        $this->assertEquals('PARAMNAME', $result->parameters[0]->name);
        $this->assertEquals('paramvalue', $result->parameters[0]->value);
    }

    public function testReadPropertyNoValue()
    {
        $data = 'PROPNAME;PARAMNAME:propValue';
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Property', $result);
        $this->assertEquals('PROPNAME', $result->name);
        $this->assertEquals('propValue', $result->value);
        $this->assertEquals(1, count($result->parameters));
        $this->assertEquals('PARAMNAME', $result->parameters[0]->name);
        $this->assertEquals('', $result->parameters[0]->value);
    }

    public function testReadPropertyParameterExtraColon()
    {
        $data = 'PROPNAME;PARAMNAME=paramvalue:propValue:anotherrandomstring';
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Property', $result);
        $this->assertEquals('PROPNAME', $result->name);
        $this->assertEquals('propValue:anotherrandomstring', $result->value);
        $this->assertEquals(1, count($result->parameters));
        $this->assertEquals('PARAMNAME', $result->parameters[0]->name);
        $this->assertEquals('paramvalue', $result->parameters[0]->value);
    }

    public function testReadProperty2Parameters()
    {
        $data = 'PROPNAME;PARAMNAME=paramvalue;PARAMNAME2=paramvalue2:propValue';
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Property', $result);
        $this->assertEquals('PROPNAME', $result->name);
        $this->assertEquals('propValue', $result->value);
        $this->assertEquals(2, count($result->parameters));
        $this->assertEquals('PARAMNAME', $result->parameters[0]->name);
        $this->assertEquals('paramvalue', $result->parameters[0]->value);
        $this->assertEquals('PARAMNAME2', $result->parameters[1]->name);
        $this->assertEquals('paramvalue2', $result->parameters[1]->value);
    }

    public function testReadPropertyParameterQuoted()
    {
        $data = 'PROPNAME;PARAMNAME="paramvalue":propValue';
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Property', $result);
        $this->assertEquals('PROPNAME', $result->name);
        $this->assertEquals('propValue', $result->value);
        $this->assertEquals(1, count($result->parameters));
        $this->assertEquals('PARAMNAME', $result->parameters[0]->name);
        $this->assertEquals('paramvalue', $result->parameters[0]->value);
    }
    public function testReadPropertyParameterNewLines()
    {
        $data = 'PROPNAME;PARAMNAME=paramvalue1\\nvalue2\\\\nvalue3:propValue';
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Property', $result);
        $this->assertEquals('PROPNAME', $result->name);
        $this->assertEquals('propValue', $result->value);

        $this->assertEquals(1, count($result->parameters));
        $this->assertEquals('PARAMNAME', $result->parameters[0]->name);
        $this->assertEquals("paramvalue1\nvalue2\\nvalue3", $result->parameters[0]->value);
    }

    public function testReadPropertyParameterQuotedColon()
    {
        $data = 'PROPNAME;PARAMNAME="param:value":propValue';
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Property', $result);
        $this->assertEquals('PROPNAME', $result->name);
        $this->assertEquals('propValue', $result->value);
        $this->assertEquals(1, count($result->parameters));
        $this->assertEquals('PARAMNAME', $result->parameters[0]->name);
        $this->assertEquals('param:value', $result->parameters[0]->value);
    }
}
