<?php

namespace Sabre\VObject\Property;

use Sabre\VObject\Component;

class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    public function testSetDateTime()
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = new DateTime('DTSTART');
        $elem->setDateTime($dt);

        $this->assertEquals('19850704T013000', $elem->value);
        $this->assertEquals('Europe/Amsterdam', (string) $elem['TZID']);
        $this->assertEquals('DATE-TIME', (string) $elem['VALUE']);
    }

    public function testSetDateTimeLOCAL()
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = new DateTime('DTSTART');
        $elem->setDateTime($dt, DateTime::LOCAL);

        $this->assertEquals('19850704T013000', $elem->value);
        $this->assertNull($elem['TZID']);
        $this->assertEquals('DATE-TIME', (string) $elem['VALUE']);
    }

    public function testSetDateTimeUTC()
    {
        $tz = new \DateTimeZone('GMT');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = new DateTime('DTSTART');
        $elem->setDateTime($dt, DateTime::UTC);

        $this->assertEquals('19850704T013000Z', $elem->value);
        $this->assertNull($elem['TZID']);
        $this->assertEquals('DATE-TIME', (string) $elem['VALUE']);
    }

    public function testSetDateTimeLOCALTZ()
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = new DateTime('DTSTART');
        $elem->setDateTime($dt, DateTime::LOCALTZ);

        $this->assertEquals('19850704T013000', $elem->value);
        $this->assertEquals('Europe/Amsterdam', (string) $elem['TZID']);
        $this->assertEquals('DATE-TIME', (string) $elem['VALUE']);
    }

    public function testSetDateTimeDATE()
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = new DateTime('DTSTART');
        $elem->setDateTime($dt, DateTime::DATE);

        $this->assertEquals('19850704', $elem->value);
        $this->assertNull($elem['TZID']);
        $this->assertEquals('DATE', (string) $elem['VALUE']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetDateTimeInvalid()
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = new DateTime('DTSTART');
        $elem->setDateTime($dt, 7);
    }

    public function testGetDateTimeCached()
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        $elem = new DateTime('DTSTART');
        $elem->setDateTime($dt);

        $this->assertEquals($elem->getDateTime(), $dt);
    }

    public function testGetDateTimeDateNULL()
    {
        $elem = new DateTime('DTSTART');
        $dt = $elem->getDateTime();

        $this->assertNull($dt);
        $this->assertNull($elem->getDateType());
    }

    public function testGetDateTimeDateDATE()
    {
        $elem = new DateTime('DTSTART', '19850704');
        $dt = $elem->getDateTime();

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 00:00:00', $dt->format('Y-m-d H:i:s'));
        $this->assertEquals(DateTime::DATE, $elem->getDateType());
    }

    public function testGetDateTimeDateLOCAL()
    {
        $elem = new DateTime('DTSTART', '19850704T013000');
        $dt = $elem->getDateTime();

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        $this->assertEquals(DateTime::LOCAL, $elem->getDateType());
    }

    public function testGetDateTimeDateUTC()
    {
        $elem = new DateTime('DTSTART', '19850704T013000Z');
        $dt = $elem->getDateTime();

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $dt->getTimeZone()->getName());
        $this->assertEquals(DateTime::UTC, $elem->getDateType());
    }

    public function testGetDateTimeDateLOCALTZ()
    {
        $elem = new DateTime('DTSTART', '19850704T013000');
        $elem['TZID'] = 'Europe/Amsterdam';

        $dt = $elem->getDateTime();

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        $this->assertEquals('Europe/Amsterdam', $dt->getTimeZone()->getName());
        $this->assertEquals(DateTime::LOCALTZ, $elem->getDateType());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetDateTimeDateInvalid()
    {
        $elem = new DateTime('DTSTART', 'bla');
        $dt = $elem->getDateTime();
    }

    public function testGetDateTimeWeirdTZ()
    {
        $elem = new DateTime('DTSTART', '19850704T013000');
        $elem['TZID'] = '/freeassociation.sourceforge.net/Tzfile/Europe/Amsterdam';

        $event = new Component('VEVENT');
        $event->add($elem);

        $timezone = new Component('VTIMEZONE');
        $timezone->TZID = '/freeassociation.sourceforge.net/Tzfile/Europe/Amsterdam';
        $timezone->{'X-LIC-LOCATION'} = 'Europe/Amsterdam';

        $calendar = new Component('VCALENDAR');
        $calendar->add($event);
        $calendar->add($timezone);

        $dt = $elem->getDateTime();

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        $this->assertEquals('Europe/Amsterdam', $dt->getTimeZone()->getName());
        $this->assertEquals(DateTime::LOCALTZ, $elem->getDateType());
    }

    public function testGetDateTimeBadTimeZone()
    {
        $default = date_default_timezone_get();
        date_default_timezone_set('Canada/Eastern');

        $elem = new DateTime('DTSTART', '19850704T013000');
        $elem['TZID'] = 'Moon';

        $event = new Component('VEVENT');
        $event->add($elem);

        $timezone = new Component('VTIMEZONE');
        $timezone->TZID = 'Moon';
        $timezone->{'X-LIC-LOCATION'} = 'Moon';

        $calendar = new Component('VCALENDAR');
        $calendar->add($event);
        $calendar->add($timezone);

        $dt = $elem->getDateTime();

        $this->assertInstanceOf('DateTime', $dt);
        $this->assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        $this->assertEquals('Canada/Eastern', $dt->getTimeZone()->getName());
        $this->assertEquals(DateTime::LOCALTZ, $elem->getDateType());
        date_default_timezone_set($default);
    }
}
