<?php

class Sabre_VObject_ComponentTest extends PHPUnit_Framework_TestCase {

    function testIterate() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        $sub = new Sabre_VObject_Component('VEVENT');
        $comp->children[] = $sub;

        $sub = new Sabre_VObject_Component('VTODO');
        $comp->children[] = $sub;

        $count = 0;
        foreach($comp->children() as $key=>$subcomponent) {

           $count++;
           $this->assertInstanceOf('Sabre_VObject_Component',$subcomponent);

        }
        $this->assertEquals(2,$count);
        $this->assertEquals(1,$key);

    }

    function testMagicGet() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        $sub = new Sabre_VObject_Component('VEVENT');
        $comp->children[] = $sub;

        $sub = new Sabre_VObject_Component('VTODO');
        $comp->children[] = $sub;

        $event = $comp->vevent;
        $this->assertInstanceOf('Sabre_VObject_Component', $event);
        $this->assertEquals('VEVENT', $event->name);

        $this->assertInternalType('null', $comp->vjournal);

    }

    function testMagicGetGroups() {

        $comp = new Sabre_VObject_Component('VCARD');

        $sub = new Sabre_VObject_Property('GROUP1.EMAIL','1@1.com');
        $comp->children[] = $sub;

        $sub = new Sabre_VObject_Property('GROUP2.EMAIL','2@2.com');
        $comp->children[] = $sub;

        $sub = new Sabre_VObject_Property('EMAIL','3@3.com');
        $comp->children[] = $sub;

        $emails = $comp->email;
        $this->assertEquals(3, count($emails));

        $email1 = $comp->{"group1.email"};
        $this->assertEquals('EMAIL', $email1[0]->name);
        $this->assertEquals('GROUP1', $email1[0]->group);

        $email3 = $comp->{".email"};
        $this->assertEquals('EMAIL', $email3[0]->name);
        $this->assertEquals(null, $email3[0]->group);

    }

    function testMagicIsset() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        $sub = new Sabre_VObject_Component('VEVENT');
        $comp->children[] = $sub;

        $sub = new Sabre_VObject_Component('VTODO');
        $comp->children[] = $sub;

        $this->assertTrue(isset($comp->vevent));
        $this->assertTrue(isset($comp->vtodo));
        $this->assertFalse(isset($comp->vjournal));

    }

    function testMagicSetScalar() {

        $comp = new Sabre_VObject_Component('VCALENDAR');
        $comp->myProp = 'myValue';

        $this->assertInstanceOf('Sabre_VObject_Property',$comp->MYPROP);
        $this->assertEquals('myValue',$comp->MYPROP->value);


    }

    function testMagicSetScalarTwice() {

        $comp = new Sabre_VObject_Component('VCALENDAR');
        $comp->myProp = 'myValue';
        $comp->myProp = 'myValue';

        $this->assertEquals(1,count($comp->children));
        $this->assertInstanceOf('Sabre_VObject_Property',$comp->MYPROP);
        $this->assertEquals('myValue',$comp->MYPROP->value);

    }

    function testMagicSetComponent() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        // Note that 'myProp' is ignored here.
        $comp->myProp = new Sabre_VObject_Component('VEVENT');

        $this->assertEquals(1, count($comp->children));

        $this->assertEquals('VEVENT',$comp->VEVENT->name);

    }

    function testMagicSetTwice() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        $comp->VEVENT = new Sabre_VObject_Component('VEVENT');
        $comp->VEVENT = new Sabre_VObject_Component('VEVENT');

        $this->assertEquals(1, count($comp->children));

        $this->assertEquals('VEVENT',$comp->VEVENT->name);

    }

    function testArrayAccessGet() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        $event = new Sabre_VObject_Component('VEVENT');
        $event->summary = 'Event 1';

        $comp->add($event);

        $event2 = clone $event;
        $event2->summary = 'Event 2';

        $comp->add($event2);

        $this->assertEquals(2,count($comp->children()));
        $this->assertTrue($comp->vevent[1] instanceof Sabre_VObject_Component);
        $this->assertEquals('Event 2', (string)$comp->vevent[1]->summary);

    }

    function testArrayAccessExists() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        $event = new Sabre_VObject_Component('VEVENT');
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
    function testArrayAccessSet() {

        $comp = new Sabre_VObject_Component('VCALENDAR');
        $comp['hey'] = 'hi there';

    }
    /**
     * @expectedException LogicException
     */
    function testArrayAccessUnset() {

        $comp = new Sabre_VObject_Component('VCALENDAR');
        unset($comp[0]);

    }

    function testAddScalar() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        $comp->add('myprop','value');

        $this->assertEquals(1, count($comp->children));

        $this->assertTrue($comp->children[0] instanceof Sabre_VObject_Property);
        $this->assertEquals('MYPROP',$comp->children[0]->name);
        $this->assertEquals('value',$comp->children[0]->value);

    }

    function testAddComponent() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        $comp->add(new Sabre_VObject_Component('VEVENT'));

        $this->assertEquals(1, count($comp->children));

        $this->assertEquals('VEVENT',$comp->VEVENT->name);

    }

    function testAddComponentTwice() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        $comp->add(new Sabre_VObject_Component('VEVENT'));
        $comp->add(new Sabre_VObject_Component('VEVENT'));

        $this->assertEquals(2, count($comp->children));

        $this->assertEquals('VEVENT',$comp->VEVENT->name);

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testAddArgFail() {

        $comp = new Sabre_VObject_Component('VCALENDAR');
        $comp->add(new Sabre_VObject_Component('VEVENT'),'hello');

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testAddArgFail2() {

        $comp = new Sabre_VObject_Component('VCALENDAR');
        $comp->add(array());

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testAddArgFail3() {

        $comp = new Sabre_VObject_Component('VCALENDAR');
        $comp->add('hello',array());

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testMagicSetInvalid() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        // Note that 'myProp' is ignored here.
        $comp->myProp = new StdClass();

        $this->assertEquals(1, count($comp->children));

        $this->assertEquals('VEVENT',$comp->VEVENT->name);

    }

    function testMagicUnset() {

        $comp = new Sabre_VObject_Component('VCALENDAR');
        $comp->add(new Sabre_VObject_Component('VEVENT'));

        unset($comp->vevent);

        $this->assertEquals(array(), $comp->children);

    }


    function testCount() {

        $comp = new Sabre_VObject_Component('VCALENDAR');
        $this->assertEquals(1,$comp->count());

    }

    function testChildren() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        // Note that 'myProp' is ignored here.
        $comp->children = array(
            new Sabre_VObject_Component('VEVENT'),
            new Sabre_VObject_Component('VTODO')
        );

        $r = $comp->children();
        $this->assertTrue($r instanceof Sabre_VObject_ElementList);
        $this->assertEquals(2,count($r));
    }

    function testGetComponents() {

        $comp = new Sabre_VObject_Component('VCALENDAR');

        // Note that 'myProp' is ignored here.
        $comp->children = array(
            new Sabre_VObject_Property('FOO','BAR'),
            new Sabre_VObject_Component('VTODO')
        );

        $r = $comp->getComponents();
        $this->assertInternalType('array', $r);
        $this->assertEquals(1, count($r));
        $this->assertEquals('VTODO', $r[0]->name);
    }

    function testSerialize() {

        $comp = new Sabre_VObject_Component('VCALENDAR');
        $this->assertEquals("BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n", $comp->serialize());

    }

    function testSerializeChildren() {

        $comp = new Sabre_VObject_Component('VCALENDAR');
        $comp->children = array(
            new Sabre_VObject_Component('VEVENT'),
            new Sabre_VObject_Component('VTODO')
        );

        $str = $comp->serialize();

        $this->assertEquals("BEGIN:VCALENDAR\r\nBEGIN:VEVENT\r\nEND:VEVENT\r\nBEGIN:VTODO\r\nEND:VTODO\r\nEND:VCALENDAR\r\n", $str);

    }

    function testSerializeOrderCompAndProp() {
        
        $comp = new Sabre_VObject_Component('VCALENDAR');
        $comp->add(new Sabre_VObject_Component('VEVENT'));
        $comp->add('PROP1','BLABLA');
        $comp->add('VERSION','2.0');
        $comp->add(new Sabre_VObject_Component('VTIMEZONE'));

        $str = $comp->serialize();

        $this->assertEquals("BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPROP1:BLABLA\r\nBEGIN:VTIMEZONE\r\nEND:VTIMEZONE\r\nBEGIN:VEVENT\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n", $str);

    }

    function testAnotherSerializeOrderProp() {

        $prop4s=array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10');

        $comp = new Sabre_VObject_Component('VCARD');
        $comp->__set('SOMEPROP','FOO');
        $comp->__set('ANOTHERPROP','FOO');
        $comp->__set('THIRDPROP','FOO');
        foreach ($prop4s as $prop4) {
            $comp->add('PROP4', 'FOO '.$prop4);
        }
        $comp->__set('PROPNUMBERFIVE', 'FOO');
        $comp->__set('PROPNUMBERSIX', 'FOO');
        $comp->__set('PROPNUMBERSEVEN', 'FOO');
        $comp->__set('PROPNUMBEREIGHT', 'FOO');
        $comp->__set('PROPNUMBERNINE', 'FOO');
        $comp->__set('PROPNUMBERTEN', 'FOO');
        $comp->__set('VERSION','2.0');
        $comp->__set('UID', 'FOO');

        $str = $comp->serialize();

        $this->assertEquals("BEGIN:VCARD\r\nVERSION:2.0\r\nSOMEPROP:FOO\r\nANOTHERPROP:FOO\r\nTHIRDPROP:FOO\r\nPROP4:FOO 1\r\nPROP4:FOO 2\r\nPROP4:FOO 3\r\nPROP4:FOO 4\r\nPROP4:FOO 5\r\nPROP4:FOO 6\r\nPROP4:FOO 7\r\nPROP4:FOO 8\r\nPROP4:FOO 9\r\nPROP4:FOO 10\r\nPROPNUMBERFIVE:FOO\r\nPROPNUMBERSIX:FOO\r\nPROPNUMBERSEVEN:FOO\r\nPROPNUMBEREIGHT:FOO\r\nPROPNUMBERNINE:FOO\r\nPROPNUMBERTEN:FOO\r\nUID:FOO\r\nEND:VCARD\r\n", $str);

    }

}
