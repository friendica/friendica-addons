<?php

namespace Sabre\VObject\Property;

class MultiDateTimeTest extends \PHPUnit_Framework_TestCase
{
    public function testSetDateTime()
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt1 = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt2 = new \DateTime('1986-07-04 01:30:00', $tz);
        $dt1->setTimeZone($tz);
        $dt2->setTimeZone($tz);

        $elem = new MultiDateTime('DTSTART');
        $elem->setDateTimes(array($dt1, $dt2));

        $this->assertEquals('19850704T013000,19860704T013000', $elem->value);
        $this->assertEquals('Europe/Amsterdam', (string) $elem['TZID']);
        $this->assertEquals('DATE-TIME', (string) $elem['VALUE']);
    }

    public function testSetDateTimeLOCAL()
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt1 = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt2 = new \DateTime('1986-07-04 01:30:00', $tz);
        $dt1->setTimeZone($tz);
        $dt2->setTimeZone($tz);

        $elem = new MultiDateTime('DTSTART');
        $elem->setDateTimes(array($dt1, $dt2), DateTime::LOCAL);

        $this->assertEquals('19850704T013000,19860704T013000', $elem->value);
        $this->assertNull($elem['TZID']);
        $this->assertEquals('DATE-TIME', (string) $elem['VALUE']);
    }

    public function testSetDateTimeUTC()
    {
        $tz = new \DateTimeZone('GMT');
        $dt1 = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt2 = new \DateTime('1986-07-04 01:30:00', $tz);
        $dt1->setTimeZone($tz);
        $dt2->setTimeZone($tz);

        $elem = new MultiDateTime('DTSTART');
        $elem->setDateTimes(array($dt1, $dt2), DateTime::UTC);

        $this->assertEquals('19850704T013000Z,19860704T013000Z', $elem->value);
        $this->assertNull($elem['TZID']);
        $this->assertEquals('DATE-TIME', (string) $elem['VALUE']);
    }

    public function testSetDateTimeLOCALTZ()
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt1 = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt2 = new \DateTime('1986-07-04 01:30:00', $tz);
        $dt1->setTimeZone($tz);
        $dt2->setTimeZone($tz);

        $elem = new MultiDateTime('DTSTART');
        $elem->setDateTimes(array($dt1, $dt2), DateTime::LOCALTZ);

        $this->assertEquals('19850704T013000,19860704T013000', $elem->value);
        $this->assertEquals('Europe/Amsterdam', (string) $elem['TZID']);
        $this->assertEquals('DATE-TIME', (string) $elem['VALUE']);
    }

    public function testSetDateTimeDATE()
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt1 = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt2 = new \DateTime('1986-07-04 01:30:00', $tz);
        $dt1->settimezone($tz);
        $dt2->settimezone($tz);

        $elem = new MultiDateTime('DTSTART');
        $elem->setDateTimes(array($dt1, $dt2), DateTime::DATE);

        $this->assertEquals('19850704,19860704', $elem->value);
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

        $elem = new MultiDateTime('DTSTART');
        $elem->setDateTimes(array($dt), 7);
    }

    public function testGetDateTimeCached()
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt1 = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt2 = new \DateTime('1986-07-04 01:30:00', $tz);
        $dt1->settimezone($tz);
        $dt2->settimezone($tz);

        $elem = new MultiDateTime('DTSTART');
        $elem->setDateTimes(array($dt1, $dt2));

        $this->assertEquals($elem->getDateTimes(), array($dt1, $dt2));
    }

    public function testGetDateTimeDateNULL()
    {
        $elem = new MultiDateTime('DTSTART');
        $dt = $elem->getDateTimes();

        $this->assertNull($dt);
        $this->assertNull($elem->getDateType());
    }

    public function testGetDateTimeDateDATE()
    {
        $elem = new MultiDateTime('DTSTART', '19850704,19860704');
        $dt = $elem->getDateTimes();

        $this->assertEquals('1985-07-04 00:00:00', $dt[0]->format('Y-m-d H:i:s'));
        $this->assertEquals('1986-07-04 00:00:00', $dt[1]->format('Y-m-d H:i:s'));
        $this->assertEquals(DateTime::DATE, $elem->getDateType());
    }

    public function testGetDateTimeDateDATEReverse()
    {
        $elem = new MultiDateTime('DTSTART', '19850704,19860704');

        $this->assertEquals(DateTime::DATE, $elem->getDateType());

        $dt = $elem->getDateTimes();
        $this->assertEquals('1985-07-04 00:00:00', $dt[0]->format('Y-m-d H:i:s'));
        $this->assertEquals('1986-07-04 00:00:00', $dt[1]->format('Y-m-d H:i:s'));
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
}
