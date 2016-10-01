<?php

namespace Sabre\VObject;

class ComponentTest extends \PHPUnit_Framework_TestCase
{
    public function testIterate()
    {
        $comp = new Component('VCALENDAR');

        $sub = new Component('VEVENT');
        $comp->children[] = $sub;

        $sub = new Component('VTODO');
        $comp->children[] = $sub;

        $count = 0;
        foreach ($comp->children() as $key => $subcomponent) {
            ++$count;
            $this->assertInstanceOf('Sabre\\VObject\\Component', $subcomponent);
        }
        $this->assertEquals(2, $count);
        $this->assertEquals(1, $key);
    }

    public function testMagicGet()
    {
        $comp = new Component('VCALENDAR');

        $sub = new Component('VEVENT');
        $comp->children[] = $sub;

        $sub = new Component('VTODO');
        $comp->children[] = $sub;

        $event = $comp->vevent;
        $this->assertInstanceOf('Sabre\\VObject\\Component', $event);
        $this->assertEquals('VEVENT', $event->name);

        $this->assertInternalType('null', $comp->vjournal);
    }

    public function testMagicGetGroups()
    {
        $comp = new Component('VCARD');

        $sub = new Property('GROUP1.EMAIL', '1@1.com');
        $comp->children[] = $sub;

        $sub = new Property('GROUP2.EMAIL', '2@2.com');
        $comp->children[] = $sub;

        $sub = new Property('EMAIL', '3@3.com');
        $comp->children[] = $sub;

        $emails = $comp->email;
        $this->assertEquals(3, count($emails));

        $email1 = $comp->{'group1.email'};
        $this->assertEquals('EMAIL', $email1[0]->name);
        $this->assertEquals('GROUP1', $email1[0]->group);

        $email3 = $comp->{'.email'};
        $this->assertEquals('EMAIL', $email3[0]->name);
        $this->assertEquals(null, $email3[0]->group);
    }

    public function testMagicIsset()
    {
        $comp = new Component('VCALENDAR');

        $sub = new Component('VEVENT');
        $comp->children[] = $sub;

        $sub = new Component('VTODO');
        $comp->children[] = $sub;

        $this->assertTrue(isset($comp->vevent));
        $this->assertTrue(isset($comp->vtodo));
        $this->assertFalse(isset($comp->vjournal));
    }

    public function testMagicSetScalar()
    {
        $comp = new Component('VCALENDAR');
        $comp->myProp = 'myValue';

        $this->assertInstanceOf('Sabre\\VObject\\Property', $comp->MYPROP);
        $this->assertEquals('myValue', $comp->MYPROP->value);
    }

    public function testMagicSetScalarTwice()
    {
        $comp = new Component('VCALENDAR');
        $comp->myProp = 'myValue';
        $comp->myProp = 'myValue';

        $this->assertEquals(1, count($comp->children));
        $this->assertInstanceOf('Sabre\\VObject\\Property', $comp->MYPROP);
        $this->assertEquals('myValue', $comp->MYPROP->value);
    }

    public function testMagicSetComponent()
    {
        $comp = new Component('VCALENDAR');

        // Note that 'myProp' is ignored here.
        $comp->myProp = new Component('VEVENT');

        $this->assertEquals(1, count($comp->children));

        $this->assertEquals('VEVENT', $comp->VEVENT->name);
    }

    public function testMagicSetTwice()
    {
        $comp = new Component('VCALENDAR');

        $comp->VEVENT = new Component('VEVENT');
        $comp->VEVENT = new Component('VEVENT');

        $this->assertEquals(1, count($comp->children));

        $this->assertEquals('VEVENT', $comp->VEVENT->name);
    }

    public function testArrayAccessGet()
    {
        $comp = new Component('VCALENDAR');

        $event = new Component('VEVENT');
        $event->summary = 'Event 1';

        $comp->add($event);

        $event2 = clone $event;
        $event2->summary = 'Event 2';

        $comp->add($event2);

        $this->assertEquals(2, count($comp->children()));
        $this->assertTrue($comp->vevent[1] instanceof Component);
        $this->assertEquals('Event 2', (string) $comp->vevent[1]->summary);
    }

    public function testArrayAccessExists()
    {
        $comp = new Component('VCALENDAR');

        $event = new Component('VEVENT');
        $event->summary = 'Event 1';

        $comp->add($event);

        $event2 = clone $event;
        $event2->summary = 'Event 2';

        $comp->add($event2);

        $this->assertTrue(isset($comp->vevent[0]));
        $this->assertTrue(isset($comp->vevent[1]));
    }

    /**
     * @expectedException LogicException
     */
    public function testArrayAccessSet()
    {
        $comp = new Component('VCALENDAR');
        $comp['hey'] = 'hi there';
    }
    /**
     * @expectedException LogicException
     */
    public function testArrayAccessUnset()
    {
        $comp = new Component('VCALENDAR');
        unset($comp[0]);
    }

    public function testAddScalar()
    {
        $comp = new Component('VCALENDAR');

        $comp->add('myprop', 'value');

        $this->assertEquals(1, count($comp->children));

        $this->assertTrue($comp->children[0] instanceof Property);
        $this->assertEquals('MYPROP', $comp->children[0]->name);
        $this->assertEquals('value', $comp->children[0]->value);
    }

    public function testAddScalarParams()
    {
        $comp = Component::create('VCALENDAR');

        $comp->add('myprop', 'value', array('param1' => 'value1'));

        $this->assertEquals(1, count($comp->children));

        $this->assertTrue($comp->children[0] instanceof Property);
        $this->assertEquals('MYPROP', $comp->children[0]->name);
        $this->assertEquals('value', $comp->children[0]->value);

        $this->assertEquals(1, count($comp->children[0]->parameters));

        $this->assertTrue($comp->children[0]->parameters[0] instanceof Parameter);
        $this->assertEquals('PARAM1', $comp->children[0]->parameters[0]->name);
        $this->assertEquals('value1', $comp->children[0]->parameters[0]->value);
    }

    public function testAddComponent()
    {
        $comp = new Component('VCALENDAR');

        $comp->add(new Component('VEVENT'));

        $this->assertEquals(1, count($comp->children));

        $this->assertEquals('VEVENT', $comp->VEVENT->name);
    }

    public function testAddComponentTwice()
    {
        $comp = new Component('VCALENDAR');

        $comp->add(new Component('VEVENT'));
        $comp->add(new Component('VEVENT'));

        $this->assertEquals(2, count($comp->children));

        $this->assertEquals('VEVENT', $comp->VEVENT->name);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddArgFail()
    {
        $comp = new Component('VCALENDAR');
        $comp->add(new Component('VEVENT'), 'hello');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddArgFail2()
    {
        $comp = new Component('VCALENDAR');
        $comp->add(array());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddArgFail3()
    {
        $comp = new Component('VCALENDAR');
        $comp->add('hello', array());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMagicSetInvalid()
    {
        $comp = new Component('VCALENDAR');

        // Note that 'myProp' is ignored here.
        $comp->myProp = new \StdClass();

        $this->assertEquals(1, count($comp->children));

        $this->assertEquals('VEVENT', $comp->VEVENT->name);
    }

    public function testMagicUnset()
    {
        $comp = new Component('VCALENDAR');
        $comp->add(new Component('VEVENT'));

        unset($comp->vevent);

        $this->assertEquals(array(), $comp->children);
    }

    public function testCount()
    {
        $comp = new Component('VCALENDAR');
        $this->assertEquals(1, $comp->count());
    }

    public function testChildren()
    {
        $comp = new Component('VCALENDAR');

        // Note that 'myProp' is ignored here.
        $comp->children = array(
            new Component('VEVENT'),
            new Component('VTODO'),
        );

        $r = $comp->children();
        $this->assertTrue($r instanceof ElementList);
        $this->assertEquals(2, count($r));
    }

    public function testGetComponents()
    {
        $comp = new Component('VCALENDAR');

        // Note that 'myProp' is ignored here.
        $comp->children = array(
            new Property('FOO', 'BAR'),
            new Component('VTODO'),
        );

        $r = $comp->getComponents();
        $this->assertInternalType('array', $r);
        $this->assertEquals(1, count($r));
        $this->assertEquals('VTODO', $r[0]->name);
    }

    public function testSerialize()
    {
        $comp = new Component('VCALENDAR');
        $this->assertEquals("BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n", $comp->serialize());
    }

    public function testSerializeChildren()
    {
        $comp = new Component('VCALENDAR');
        $comp->children = array(
            new Component('VEVENT'),
            new Component('VTODO'),
        );

        $str = $comp->serialize();

        $this->assertEquals("BEGIN:VCALENDAR\r\nBEGIN:VEVENT\r\nEND:VEVENT\r\nBEGIN:VTODO\r\nEND:VTODO\r\nEND:VCALENDAR\r\n", $str);
    }

    public function testSerializeOrderCompAndProp()
    {
        $comp = new Component('VCALENDAR');
        $comp->add(new Component('VEVENT'));
        $comp->add('PROP1', 'BLABLA');
        $comp->add('VERSION', '2.0');
        $comp->add(new Component('VTIMEZONE'));

        $str = $comp->serialize();

        $this->assertEquals("BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPROP1:BLABLA\r\nBEGIN:VTIMEZONE\r\nEND:VTIMEZONE\r\nBEGIN:VEVENT\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n", $str);
    }

    public function testAnotherSerializeOrderProp()
    {
        $prop4s = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10');

        $comp = new Component('VCARD');
        $comp->__set('SOMEPROP', 'FOO');
        $comp->__set('ANOTHERPROP', 'FOO');
        $comp->__set('THIRDPROP', 'FOO');
        foreach ($prop4s as $prop4) {
            $comp->add('PROP4', 'FOO '.$prop4);
        }
        $comp->__set('PROPNUMBERFIVE', 'FOO');
        $comp->__set('PROPNUMBERSIX', 'FOO');
        $comp->__set('PROPNUMBERSEVEN', 'FOO');
        $comp->__set('PROPNUMBEREIGHT', 'FOO');
        $comp->__set('PROPNUMBERNINE', 'FOO');
        $comp->__set('PROPNUMBERTEN', 'FOO');
        $comp->__set('VERSION', '2.0');
        $comp->__set('UID', 'FOO');

        $str = $comp->serialize();

        $this->assertEquals("BEGIN:VCARD\r\nVERSION:2.0\r\nSOMEPROP:FOO\r\nANOTHERPROP:FOO\r\nTHIRDPROP:FOO\r\nPROP4:FOO 1\r\nPROP4:FOO 2\r\nPROP4:FOO 3\r\nPROP4:FOO 4\r\nPROP4:FOO 5\r\nPROP4:FOO 6\r\nPROP4:FOO 7\r\nPROP4:FOO 8\r\nPROP4:FOO 9\r\nPROP4:FOO 10\r\nPROPNUMBERFIVE:FOO\r\nPROPNUMBERSIX:FOO\r\nPROPNUMBERSEVEN:FOO\r\nPROPNUMBEREIGHT:FOO\r\nPROPNUMBERNINE:FOO\r\nPROPNUMBERTEN:FOO\r\nUID:FOO\r\nEND:VCARD\r\n", $str);
    }
}
