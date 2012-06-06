<?php

/**
 * This unittest is created to check if a VALARM TRIGGER of PT0S is supported
 *
 *
 * @copyright Copyright (C) 2007-2012 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Sabre_CalDAV_Issue205Test extends Sabre_DAVServerTest {

    protected $setupCalDAV = true;

    protected $caldavCalendars = array(
        array(
            'id' => 1,
            'name' => 'Calendar',
            'principaluri' => 'principals/user1',
            'uri' => 'calendar1',
        )
    );

    protected $caldavCalendarObjects = array(
        1 => array(
            'event.ics' => array(
                'calendardata' => 'BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:20120330T155305CEST-6585fBUVgV
DTSTAMP:20120330T135305Z
DTSTART;TZID=Europe/Berlin:20120326T155200
DTEND;TZID=Europe/Berlin:20120326T165200
SUMMARY:original summary
TRANSP:OPAQUE
BEGIN:VALARM
ACTION:AUDIO
ATTACH;VALUE=URI:Basso
TRIGGER:PT0S
END:VALARM
END:VEVENT
END:VCALENDAR
',
            ),
        ),
    );

    function testIssue205() {

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'REPORT',
            'HTTP_CONTENT_TYPE' => 'application/xml',
            'REQUEST_URI' => '/calendars/user1/calendar1',
            'HTTP_DEPTH' => '1',
        ));

        $request->setBody('<?xml version="1.0" encoding="utf-8" ?>
<C:calendar-query xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
    <D:prop>
        <C:calendar-data>
            <C:expand start="20120325T220000Z" end="20120401T215959Z"/>
        </C:calendar-data>
        <D:getetag/>
    </D:prop>
    <C:filter>
        <C:comp-filter name="VCALENDAR">
            <C:comp-filter name="VEVENT">
                <C:comp-filter name="VALARM">
                    <C:time-range start="20120325T220000Z" end="20120401T215959Z"/>
                </C:comp-filter>
            </C:comp-filter>
        </C:comp-filter>
    </C:filter>
</C:calendar-query>');

        $response = $this->request($request);

        $this->assertFalse(strpos($response->body, '<s:exception>Exception</s:exception>'), 'Exception occurred: ' . $response->body);
        $this->assertFalse(strpos($response->body, 'Unknown or bad format'), 'DateTime unknown format Exception: ' . $response->body);

        // Everts super awesome xml parser.
        $body = substr(
            $response->body,
            $start = strpos($response->body, 'BEGIN:VCALENDAR'),
            strpos($response->body, 'END:VCALENDAR') - $start + 13
        );
        $body = str_replace('&#13;','',$body);

        $vObject = Sabre_VObject_Reader::read($body);

        $this->assertEquals(1, count($vObject->VEVENT));

    }
}
