<?php

class Sabre_CalDAV_Backend_AbstractTest extends PHPUnit_Framework_TestCase
{
    public function testUpdateCalendar()
    {
        $abstract = new Sabre_CalDAV_Backend_AbstractMock();
        $this->assertEquals(false, $abstract->updateCalendar('randomid', array('{DAV:}displayname' => 'anything')));
    }

    public function testCalendarQuery()
    {
        $abstract = new Sabre_CalDAV_Backend_AbstractMock();
        $filters = array(
            'name' => 'VCALENDAR',
            'comp-filters' => array(
                array(
                    'name' => 'VEVENT',
                    'comp-filters' => array(),
                    'prop-filters' => array(),
                    'is-not-defined' => false,
                    'time-range' => null,
                ),
            ),
            'prop-filters' => array(),
            'is-not-defined' => false,
            'time-range' => null,
        );

        $this->assertEquals(array(
            'event1.ics',
        ), $abstract->calendarQuery(1, $filters));
    }
}

class Sabre_CalDAV_Backend_AbstractMock extends Sabre_CalDAV_Backend_Abstract
{
    public function getCalendarsForUser($principalUri)
    {
    }
    public function createCalendar($principalUri, $calendarUri, array $properties)
    {
    }
    public function deleteCalendar($calendarId)
    {
    }
    public function getCalendarObjects($calendarId)
    {
        return array(
            array(
                'id' => 1,
                'calendarid' => 1,
                'uri' => 'event1.ics',
            ),
            array(
                'id' => 2,
                'calendarid' => 1,
                'uri' => 'task1.ics',
            ),
        );
    }
    public function getCalendarObject($calendarId, $objectUri)
    {
        switch ($objectUri) {

            case 'event1.ics':
                return array(
                    'id' => 1,
                    'calendarid' => 1,
                    'uri' => 'event1.ics',
                    'calendardata' => "BEGIN:VCALENDAR\r\nBEGIN:VEVENT\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n",
                );
            case 'task1.ics':
                return array(
                    'id' => 1,
                    'calendarid' => 1,
                    'uri' => 'event1.ics',
                    'calendardata' => "BEGIN:VCALENDAR\r\nBEGIN:VTODO\r\nEND:VTODO\r\nEND:VCALENDAR\r\n",
                );

        }
    }
    public function createCalendarObject($calendarId, $objectUri, $calendarData)
    {
    }
    public function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {
    }
    public function deleteCalendarObject($calendarId, $objectUri)
    {
    }
}
