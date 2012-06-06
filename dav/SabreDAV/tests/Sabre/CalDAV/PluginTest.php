<?php

require_once 'Sabre/HTTP/ResponseMock.php';
require_once 'Sabre/DAV/Auth/MockBackend.php';
require_once 'Sabre/CalDAV/TestUtil.php';
require_once 'Sabre/DAVACL/MockPrincipalBackend.php';

class Sabre_CalDAV_PluginTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Sabre_DAV_Server
     */
    protected $server;
    /**
     * @var Sabre_CalDAV_Plugin
     */
    protected $plugin;
    protected $response;
    /**
     * @var Sabre_CalDAV_Backend_PDO
     */
    protected $caldavBackend;

    function setup() {

        if (!SABRE_HASSQLITE) $this->markTestSkipped('No PDO SQLite support');
        $this->caldavBackend = Sabre_CalDAV_TestUtil::getBackend();
        $principalBackend = new Sabre_DAVACL_MockPrincipalBackend();
        $principalBackend->setGroupMemberSet('principals/admin/calendar-proxy-read',array('principals/user1'));
        $principalBackend->setGroupMemberSet('principals/admin/calendar-proxy-write',array('principals/user1'));
        $principalBackend->addPrincipal(array(
            'uri' => 'principals/admin/calendar-proxy-read',
        ));
        $principalBackend->addPrincipal(array(
            'uri' => 'principals/admin/calendar-proxy-write',
        ));

        $calendars = new Sabre_CalDAV_CalendarRootNode($principalBackend,$this->caldavBackend);
        $principals = new Sabre_CalDAV_Principal_Collection($principalBackend);

        $root = new Sabre_DAV_SimpleCollection('root');
        $root->addChild($calendars);
        $root->addChild($principals);

        $objectTree = new Sabre_DAV_ObjectTree($root);
        $this->server = new Sabre_DAV_Server($objectTree);
        $this->server->debugExceptions = true;
        $this->server->setBaseUri('/');
        $this->plugin = new Sabre_CalDAV_Plugin();
        $this->server->addPlugin($this->plugin);

        $this->response = new Sabre_HTTP_ResponseMock();
        $this->server->httpResponse = $this->response;

    }

    function testSimple() {

        $this->assertEquals(array('MKCALENDAR'), $this->plugin->getHTTPMethods('calendars/user1/randomnewcalendar'));
        $this->assertEquals(array('calendar-access','calendar-proxy'), $this->plugin->getFeatures());
        $this->assertArrayHasKey('urn:ietf:params:xml:ns:caldav', $this->server->xmlNamespaces);

    }

    function testUnknownMethodPassThrough() {

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'MKBREAKFAST',
            'REQUEST_URI'    => '/',
        ));

        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 501 Not Implemented', $this->response->status,'Incorrect status returned. Full response body:' . $this->response->body);

    }

    function testReportPassThrough() {

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD'    => 'REPORT',
            'HTTP_CONTENT_TYPE' => 'application/xml',
            'REQUEST_URI'       => '/',
        ));
        $request->setBody('<?xml version="1.0"?><s:somereport xmlns:s="http://www.rooftopsolutions.nl/NS/example" />');

        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 501 Not Implemented', $this->response->status);

    }

    function testMkCalendarBadLocation() {

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'MKCALENDAR',
            'REQUEST_URI'    => '/blabla',
        ));

        $body = '<?xml version="1.0" encoding="utf-8" ?>
   <C:mkcalendar xmlns:D="DAV:"
                 xmlns:C="urn:ietf:params:xml:ns:caldav">
     <D:set>
       <D:prop>
         <D:displayname>Lisa\'s Events</D:displayname>
         <C:calendar-description xml:lang="en"
   >Calendar restricted to events.</C:calendar-description>
         <C:supported-calendar-component-set>
           <C:comp name="VEVENT"/>
         </C:supported-calendar-component-set>
         <C:calendar-timezone><![CDATA[BEGIN:VCALENDAR
   PRODID:-//Example Corp.//CalDAV Client//EN
   VERSION:2.0
   BEGIN:VTIMEZONE
   TZID:US-Eastern
   LAST-MODIFIED:19870101T000000Z
   BEGIN:STANDARD
   DTSTART:19671029T020000
   RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
   TZOFFSETFROM:-0400
   TZOFFSETTO:-0500
   TZNAME:Eastern Standard Time (US & Canada)
   END:STANDARD
   BEGIN:DAYLIGHT
   DTSTART:19870405T020000
   RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4
   TZOFFSETFROM:-0500
   TZOFFSETTO:-0400
   TZNAME:Eastern Daylight Time (US & Canada)
   END:DAYLIGHT
   END:VTIMEZONE
   END:VCALENDAR
   ]]></C:calendar-timezone>
       </D:prop>
     </D:set>
   </C:mkcalendar>';

        $request->setBody($body);
        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 403 Forbidden', $this->response->status);

    }

    function testMkCalendarNoParentNode() {

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'MKCALENDAR',
            'REQUEST_URI'    => '/doesntexist/calendar',
        ));

        $body = '<?xml version="1.0" encoding="utf-8" ?>
   <C:mkcalendar xmlns:D="DAV:"
                 xmlns:C="urn:ietf:params:xml:ns:caldav">
     <D:set>
       <D:prop>
         <D:displayname>Lisa\'s Events</D:displayname>
         <C:calendar-description xml:lang="en"
   >Calendar restricted to events.</C:calendar-description>
         <C:supported-calendar-component-set>
           <C:comp name="VEVENT"/>
         </C:supported-calendar-component-set>
         <C:calendar-timezone><![CDATA[BEGIN:VCALENDAR
   PRODID:-//Example Corp.//CalDAV Client//EN
   VERSION:2.0
   BEGIN:VTIMEZONE
   TZID:US-Eastern
   LAST-MODIFIED:19870101T000000Z
   BEGIN:STANDARD
   DTSTART:19671029T020000
   RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
   TZOFFSETFROM:-0400
   TZOFFSETTO:-0500
   TZNAME:Eastern Standard Time (US & Canada)
   END:STANDARD
   BEGIN:DAYLIGHT
   DTSTART:19870405T020000
   RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4
   TZOFFSETFROM:-0500
   TZOFFSETTO:-0400
   TZNAME:Eastern Daylight Time (US & Canada)
   END:DAYLIGHT
   END:VTIMEZONE
   END:VCALENDAR
   ]]></C:calendar-timezone>
       </D:prop>
     </D:set>
   </C:mkcalendar>';

        $request->setBody($body);
        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 409 Conflict', $this->response->status);

    }

    function testMkCalendarExistingCalendar() {

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'MKCALENDAR',
            'REQUEST_URI'    => '/calendars/user1/UUID-123467',
        ));

        $body = '<?xml version="1.0" encoding="utf-8" ?>
   <C:mkcalendar xmlns:D="DAV:"
                 xmlns:C="urn:ietf:params:xml:ns:caldav">
     <D:set>
       <D:prop>
         <D:displayname>Lisa\'s Events</D:displayname>
         <C:calendar-description xml:lang="en"
   >Calendar restricted to events.</C:calendar-description>
         <C:supported-calendar-component-set>
           <C:comp name="VEVENT"/>
         </C:supported-calendar-component-set>
         <C:calendar-timezone><![CDATA[BEGIN:VCALENDAR
   PRODID:-//Example Corp.//CalDAV Client//EN
   VERSION:2.0
   BEGIN:VTIMEZONE
   TZID:US-Eastern
   LAST-MODIFIED:19870101T000000Z
   BEGIN:STANDARD
   DTSTART:19671029T020000
   RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
   TZOFFSETFROM:-0400
   TZOFFSETTO:-0500
   TZNAME:Eastern Standard Time (US & Canada)
   END:STANDARD
   BEGIN:DAYLIGHT
   DTSTART:19870405T020000
   RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4
   TZOFFSETFROM:-0500
   TZOFFSETTO:-0400
   TZNAME:Eastern Daylight Time (US & Canada)
   END:DAYLIGHT
   END:VTIMEZONE
   END:VCALENDAR
   ]]></C:calendar-timezone>
       </D:prop>
     </D:set>
   </C:mkcalendar>';

        $request->setBody($body);
        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 405 Method Not Allowed', $this->response->status);

    }

    function testMkCalendarSucceed() {

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'MKCALENDAR',
            'REQUEST_URI'    => '/calendars/user1/NEWCALENDAR',
        ));

        $timezone = 'BEGIN:VCALENDAR
PRODID:-//Example Corp.//CalDAV Client//EN
VERSION:2.0
BEGIN:VTIMEZONE
TZID:US-Eastern
LAST-MODIFIED:19870101T000000Z
BEGIN:STANDARD
DTSTART:19671029T020000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
TZOFFSETFROM:-0400
TZOFFSETTO:-0500
TZNAME:Eastern Standard Time (US & Canada)
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:19870405T020000
RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4
TZOFFSETFROM:-0500
TZOFFSETTO:-0400
TZNAME:Eastern Daylight Time (US & Canada)
END:DAYLIGHT
END:VTIMEZONE
END:VCALENDAR';

        $body = '<?xml version="1.0" encoding="utf-8" ?>
   <C:mkcalendar xmlns:D="DAV:"
                 xmlns:C="urn:ietf:params:xml:ns:caldav">
     <D:set>
       <D:prop>
         <D:displayname>Lisa\'s Events</D:displayname>
         <C:calendar-description xml:lang="en"
   >Calendar restricted to events.</C:calendar-description>
         <C:supported-calendar-component-set>
           <C:comp name="VEVENT"/>
         </C:supported-calendar-component-set>
         <C:calendar-timezone><![CDATA[' . $timezone . ']]></C:calendar-timezone>
       </D:prop>
     </D:set>
   </C:mkcalendar>';

        $request->setBody($body);
        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 201 Created', $this->response->status,'Invalid response code received. Full response body: ' .$this->response->body);

        $calendars = $this->caldavBackend->getCalendarsForUser('principals/user1');
        $this->assertEquals(3, count($calendars));

        $newCalendar = null;
        foreach($calendars as $calendar) {
           if ($calendar['uri'] === 'NEWCALENDAR') {
                $newCalendar = $calendar;
                break;
           }
        }

        $this->assertInternalType('array',$newCalendar);

        $keys = array(
            'uri' => 'NEWCALENDAR',
            'id' => null,
            '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'Calendar restricted to events.',
            '{urn:ietf:params:xml:ns:caldav}calendar-timezone' => $timezone,
            '{DAV:}displayname' => 'Lisa\'s Events',
            '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => null,
        );

        foreach($keys as $key=>$value) {

            $this->assertArrayHasKey($key, $newCalendar);

            if (is_null($value)) continue;
            $this->assertEquals($value, $newCalendar[$key]);

        }
        $sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
        $this->assertTrue($newCalendar[$sccs] instanceof Sabre_CalDAV_Property_SupportedCalendarComponentSet);
        $this->assertEquals(array('VEVENT'),$newCalendar[$sccs]->getValue());

    }

    function testMkCalendarEmptyBodySucceed() {

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'MKCALENDAR',
            'REQUEST_URI'    => '/calendars/user1/NEWCALENDAR',
        ));

        $request->setBody('');
        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 201 Created', $this->response->status,'Invalid response code received. Full response body: ' .$this->response->body);

        $calendars = $this->caldavBackend->getCalendarsForUser('principals/user1');
        $this->assertEquals(3, count($calendars));

        $newCalendar = null;
        foreach($calendars as $calendar) {
           if ($calendar['uri'] === 'NEWCALENDAR') {
                $newCalendar = $calendar;
                break;
           }
        }

        $this->assertInternalType('array',$newCalendar);

        $keys = array(
            'uri' => 'NEWCALENDAR',
            'id' => null,
            '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => null,
        );

        foreach($keys as $key=>$value) {

            $this->assertArrayHasKey($key, $newCalendar);

            if (is_null($value)) continue;
            $this->assertEquals($value, $newCalendar[$key]);

        }
        $sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
        $this->assertTrue($newCalendar[$sccs] instanceof Sabre_CalDAV_Property_SupportedCalendarComponentSet);
        $this->assertEquals(array('VEVENT','VTODO'),$newCalendar[$sccs]->getValue());

    }

    function testPrincipalProperties() {

        $httpRequest = new Sabre_HTTP_Request(array(
            'HTTP_HOST' => 'sabredav.org',
        ));
        $this->server->httpRequest = $httpRequest;

        $props = $this->server->getPropertiesForPath('/principals/user1',array(
            '{urn:ietf:params:xml:ns:caldav}calendar-home-set',
            '{urn:ietf:params:xml:ns:caldav}schedule-outbox-URL',
            '{urn:ietf:params:xml:ns:caldav}calendar-user-address-set',
            '{' . Sabre_CalDAV_Plugin::NS_CALENDARSERVER . '}calendar-proxy-read-for',
            '{' . Sabre_CalDAV_Plugin::NS_CALENDARSERVER . '}calendar-proxy-write-for',
        ));

        $this->assertArrayHasKey(0,$props);
        $this->assertArrayHasKey(200,$props[0]);


        $this->assertArrayHasKey('{urn:ietf:params:xml:ns:caldav}calendar-home-set',$props[0][200]);
        $prop = $props[0][200]['{urn:ietf:params:xml:ns:caldav}calendar-home-set'];
        $this->assertTrue($prop instanceof Sabre_DAV_Property_Href);
        $this->assertEquals('calendars/user1/',$prop->getHref());

        $this->assertArrayHasKey('{urn:ietf:params:xml:ns:caldav}schedule-outbox-URL',$props[0][200]);
        $prop = $props[0][200]['{urn:ietf:params:xml:ns:caldav}schedule-outbox-URL'];
        $this->assertTrue($prop instanceof Sabre_DAV_Property_Href);
        $this->assertEquals('calendars/user1/outbox',$prop->getHref());

        $this->assertArrayHasKey('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set',$props[0][200]);
        $prop = $props[0][200]['{urn:ietf:params:xml:ns:caldav}calendar-user-address-set'];
        $this->assertTrue($prop instanceof Sabre_DAV_Property_HrefList);
        $this->assertEquals(array('mailto:user1.sabredav@sabredav.org','/principals/user1'),$prop->getHrefs());

        $this->assertArrayHasKey('{http://calendarserver.org/ns/}calendar-proxy-read-for', $props[0][200]);
        $prop = $props[0][200]['{http://calendarserver.org/ns/}calendar-proxy-read-for'];
        $this->assertInstanceOf('Sabre_DAV_Property_HrefList', $prop);
        $this->assertEquals(array('principals/admin'), $prop->getHrefs());

        $this->assertArrayHasKey('{http://calendarserver.org/ns/}calendar-proxy-write-for', $props[0][200]);
        $prop = $props[0][200]['{http://calendarserver.org/ns/}calendar-proxy-write-for'];
        $this->assertInstanceOf('Sabre_DAV_Property_HrefList', $prop);
        $this->assertEquals(array('principals/admin'), $prop->getHrefs());

    }

    function testSupportedReportSetPropertyNonCalendar() {

        $props = $this->server->getPropertiesForPath('/calendars/user1',array(
            '{DAV:}supported-report-set',
        ));

        $this->assertArrayHasKey(0,$props);
        $this->assertArrayHasKey(200,$props[0]);
        $this->assertArrayHasKey('{DAV:}supported-report-set',$props[0][200]);

        $prop = $props[0][200]['{DAV:}supported-report-set'];

        $this->assertTrue($prop instanceof Sabre_DAV_Property_SupportedReportSet);
        $value = array(
        );
        $this->assertEquals($value,$prop->getValue());

    }

    /**
     * @depends testSupportedReportSetPropertyNonCalendar
     */
    function testSupportedReportSetProperty() {

        $props = $this->server->getPropertiesForPath('/calendars/user1/UUID-123467',array(
            '{DAV:}supported-report-set',
        ));

        $this->assertArrayHasKey(0,$props);
        $this->assertArrayHasKey(200,$props[0]);
        $this->assertArrayHasKey('{DAV:}supported-report-set',$props[0][200]);

        $prop = $props[0][200]['{DAV:}supported-report-set'];

        $this->assertTrue($prop instanceof Sabre_DAV_Property_SupportedReportSet);
        $value = array(
            '{urn:ietf:params:xml:ns:caldav}calendar-multiget',
            '{urn:ietf:params:xml:ns:caldav}calendar-query',
            '{urn:ietf:params:xml:ns:caldav}free-busy-query',
        );
        $this->assertEquals($value,$prop->getValue());

    }

    /**
     * @depends testSupportedReportSetProperty
     */
    function testCalendarMultiGetReport() {

        $body =
            '<?xml version="1.0"?>' .
            '<c:calendar-multiget xmlns:c="urn:ietf:params:xml:ns:caldav" xmlns:d="DAV:">' .
            '<d:prop>' .
            '  <c:calendar-data />' .
            '  <d:getetag />' .
            '</d:prop>' .
            '<d:href>/calendars/user1/UUID-123467/UUID-2345</d:href>' .
            '</c:calendar-multiget>';

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'REPORT',
            'REQUEST_URI'    => '/calendars/user1',
            'HTTP_DEPTH'     => '1',
        ));
        $request->setBody($body);

        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 207 Multi-Status',$this->response->status,'Invalid HTTP status received. Full response body: ' . $this->response->body);

        $xml = simplexml_load_string(Sabre_DAV_XMLUtil::convertDAVNamespace($this->response->body));

        $xml->registerXPathNamespace('d','urn:DAV');
        $xml->registerXPathNamespace('c','urn:ietf:params:xml:ns:caldav');

        $check = array(
            '/d:multistatus',
            '/d:multistatus/d:response',
            '/d:multistatus/d:response/d:href',
            '/d:multistatus/d:response/d:propstat',
            '/d:multistatus/d:response/d:propstat/d:prop',
            '/d:multistatus/d:response/d:propstat/d:prop/d:getetag',
            '/d:multistatus/d:response/d:propstat/d:prop/c:calendar-data',
            '/d:multistatus/d:response/d:propstat/d:status' => 'HTTP/1.1 200 OK',
        );

        foreach($check as $v1=>$v2) {

            $xpath = is_int($v1)?$v2:$v1;

            $result = $xml->xpath($xpath);
            $this->assertEquals(1,count($result));

            if (!is_int($v1)) $this->assertEquals($v2,(string)$result[0]);

        }

        // The response object should have a reference to the Asia/Seoul
        // timezone.
        $this->assertTrue(strpos($this->response->body,'Asia/Seoul')!==false);

    }

    /**
     * @depends testCalendarMultiGetReport
     */
    function testCalendarMultiGetReportExpand() {

        $body =
            '<?xml version="1.0"?>' .
            '<c:calendar-multiget xmlns:c="urn:ietf:params:xml:ns:caldav" xmlns:d="DAV:">' .
            '<d:prop>' .
            '  <c:calendar-data>' .
            '     <c:expand start="20110101T000000Z" end="20111231T235959Z" />' .
            '  </c:calendar-data>' .
            '  <d:getetag />' .
            '</d:prop>' .
            '<d:href>/calendars/user1/UUID-123467/UUID-2345</d:href>' .
            '</c:calendar-multiget>';

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'REPORT',
            'REQUEST_URI'    => '/calendars/user1',
            'HTTP_DEPTH'     => '1',
        ));
        $request->setBody($body);

        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 207 Multi-Status',$this->response->status,'Invalid HTTP status received. Full response body: ' . $this->response->body);

        $xml = simplexml_load_string(Sabre_DAV_XMLUtil::convertDAVNamespace($this->response->body));

        $xml->registerXPathNamespace('d','urn:DAV');
        $xml->registerXPathNamespace('c','urn:ietf:params:xml:ns:caldav');

        $check = array(
            '/d:multistatus',
            '/d:multistatus/d:response',
            '/d:multistatus/d:response/d:href',
            '/d:multistatus/d:response/d:propstat',
            '/d:multistatus/d:response/d:propstat/d:prop',
            '/d:multistatus/d:response/d:propstat/d:prop/d:getetag',
            '/d:multistatus/d:response/d:propstat/d:prop/c:calendar-data',
            '/d:multistatus/d:response/d:propstat/d:status' => 'HTTP/1.1 200 OK',
        );

        foreach($check as $v1=>$v2) {

            $xpath = is_int($v1)?$v2:$v1;

            $result = $xml->xpath($xpath);
            $this->assertEquals(1,count($result));

            if (!is_int($v1)) $this->assertEquals($v2,(string)$result[0]);

        }
        // The response object should no longer hold references to timezones.
        $this->assertTrue(strpos($this->response->body,'Asia/Seoul')===false);

    }

    /**
     * @depends testSupportedReportSetProperty
     * @depends testCalendarMultiGetReport
     */
    function testCalendarQueryReport() {

        $body =
            '<?xml version="1.0"?>' .
            '<c:calendar-query xmlns:c="urn:ietf:params:xml:ns:caldav" xmlns:d="DAV:">' .
            '<d:prop>' .
            '  <c:calendar-data>' .
            '     <c:expand start="20000101T000000Z" end="20101231T235959Z" />' .
            '  </c:calendar-data>' .
            '  <d:getetag />' .
            '</d:prop>' .
            '<c:filter>' .
            '  <c:comp-filter name="VCALENDAR">' .
            '    <c:comp-filter name="VEVENT" />' .
            '  </c:comp-filter>' .
            '</c:filter>' .
            '</c:calendar-query>';

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'REPORT',
            'REQUEST_URI'    => '/calendars/user1/UUID-123467',
            'HTTP_DEPTH'     => '1',
        ));
        $request->setBody($body);

        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 207 Multi-Status',$this->response->status,'Received an unexpected status. Full response body: ' . $this->response->body);

        $xml = simplexml_load_string(Sabre_DAV_XMLUtil::convertDAVNamespace($this->response->body));

        $xml->registerXPathNamespace('d','urn:DAV');
        $xml->registerXPathNamespace('c','urn:ietf:params:xml:ns:caldav');

        $check = array(
            '/d:multistatus',
            '/d:multistatus/d:response',
            '/d:multistatus/d:response/d:href',
            '/d:multistatus/d:response/d:propstat',
            '/d:multistatus/d:response/d:propstat/d:prop',
            '/d:multistatus/d:response/d:propstat/d:prop/d:getetag',
            '/d:multistatus/d:response/d:propstat/d:prop/c:calendar-data',
            '/d:multistatus/d:response/d:propstat/d:status' => 'HTTP/1.1 200 OK',
        );

        foreach($check as $v1=>$v2) {

            $xpath = is_int($v1)?$v2:$v1;

            $result = $xml->xpath($xpath);
            $this->assertEquals(1,count($result), 'We expected 1 ' . $xpath . ' elements. We\'ve found ' . count($result) . '. Full result: ' . $this->response->body);

            if (!is_int($v1)) $this->assertEquals($v2,(string)$result[0]);

        }

    }

    /**
     * @depends testCalendarQueryReport
     */
    function testCalendarQueryReportNoCalData() {

        $body =
            '<?xml version="1.0"?>' .
            '<c:calendar-query xmlns:c="urn:ietf:params:xml:ns:caldav" xmlns:d="DAV:">' .
            '<d:prop>' .
            '  <d:getetag />' .
            '</d:prop>' .
            '<c:filter>' .
            '  <c:comp-filter name="VCALENDAR">' .
            '    <c:comp-filter name="VEVENT" />' .
            '  </c:comp-filter>' .
            '</c:filter>' .
            '</c:calendar-query>';

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'REPORT',
            'REQUEST_URI'    => '/calendars/user1//UUID-123467',
            'HTTP_DEPTH'     => '1',
        ));
        $request->setBody($body);

        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 207 Multi-Status',$this->response->status,'Received an unexpected status. Full response body: ' . $this->response->body);

        $xml = simplexml_load_string(Sabre_DAV_XMLUtil::convertDAVNamespace($this->response->body));

        $xml->registerXPathNamespace('d','urn:DAV');
        $xml->registerXPathNamespace('c','urn:ietf:params:xml:ns:caldav');

        $check = array(
            '/d:multistatus',
            '/d:multistatus/d:response',
            '/d:multistatus/d:response/d:href',
            '/d:multistatus/d:response/d:propstat',
            '/d:multistatus/d:response/d:propstat/d:prop',
            '/d:multistatus/d:response/d:propstat/d:prop/d:getetag',
            '/d:multistatus/d:response/d:propstat/d:status' => 'HTTP/1.1 200 OK',
        );

        foreach($check as $v1=>$v2) {

            $xpath = is_int($v1)?$v2:$v1;

            $result = $xml->xpath($xpath);
            $this->assertEquals(1,count($result), 'We expected 1 ' . $xpath . ' elements. We\'ve found ' . count($result) . '. Full result: ' . $this->response->body);

            if (!is_int($v1)) $this->assertEquals($v2,(string)$result[0]);

        }

    }

    /**
     * @depends testCalendarQueryReport
     */
    function testCalendarQueryReportNoFilters() {

        $body =
            '<?xml version="1.0"?>' .
            '<c:calendar-query xmlns:c="urn:ietf:params:xml:ns:caldav" xmlns:d="DAV:">' .
            '<d:prop>' .
            '  <c:calendar-data />' .
            '  <d:getetag />' .
            '</d:prop>' .
            '</c:calendar-query>';

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'REPORT',
            'REQUEST_URI'    => '/calendars/user1//UUID-123467',
        ));
        $request->setBody($body);

        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 400 Bad request',$this->response->status,'Received an unexpected status. Full response body: ' . $this->response->body);

    }

    /**
     * @depends testSupportedReportSetProperty
     * @depends testCalendarMultiGetReport
     */
    function testCalendarQueryReport1Object() {

        $body =
            '<?xml version="1.0"?>' .
            '<c:calendar-query xmlns:c="urn:ietf:params:xml:ns:caldav" xmlns:d="DAV:">' .
            '<d:prop>' .
            '  <c:calendar-data>' .
            '     <c:expand start="20000101T000000Z" end="20101231T235959Z" />' .
            '  </c:calendar-data>' . 
            '  <d:getetag />' .
            '</d:prop>' .
            '<c:filter>' .
            '  <c:comp-filter name="VCALENDAR">' .
            '    <c:comp-filter name="VEVENT" />' .
            '  </c:comp-filter>' .
            '</c:filter>' .
            '</c:calendar-query>';

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'REPORT',
            'REQUEST_URI'    => '/calendars/user1/UUID-123467/UUID-2345',
            'HTTP_DEPTH'     => '0',
        ));
        $request->setBody($body);

        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 207 Multi-Status',$this->response->status,'Received an unexpected status. Full response body: ' . $this->response->body);

        $xml = simplexml_load_string(Sabre_DAV_XMLUtil::convertDAVNamespace($this->response->body));

        $xml->registerXPathNamespace('d','urn:DAV');
        $xml->registerXPathNamespace('c','urn:ietf:params:xml:ns:caldav');

        $check = array(
            '/d:multistatus',
            '/d:multistatus/d:response',
            '/d:multistatus/d:response/d:href',
            '/d:multistatus/d:response/d:propstat',
            '/d:multistatus/d:response/d:propstat/d:prop',
            '/d:multistatus/d:response/d:propstat/d:prop/d:getetag',
            '/d:multistatus/d:response/d:propstat/d:prop/c:calendar-data',
            '/d:multistatus/d:response/d:propstat/d:status' => 'HTTP/1.1 200 OK',
        );

        foreach($check as $v1=>$v2) {

            $xpath = is_int($v1)?$v2:$v1;

            $result = $xml->xpath($xpath);
            $this->assertEquals(1,count($result), 'We expected 1 ' . $xpath . ' elements. We\'ve found ' . count($result) . '. Full result: ' . $this->response->body);

            if (!is_int($v1)) $this->assertEquals($v2,(string)$result[0]);

        }

    }

    /**
     * @depends testSupportedReportSetProperty
     * @depends testCalendarMultiGetReport
     */
    function testCalendarQueryReport1ObjectNoCalData() {

        $body =
            '<?xml version="1.0"?>' .
            '<c:calendar-query xmlns:c="urn:ietf:params:xml:ns:caldav" xmlns:d="DAV:">' .
            '<d:prop>' .
            '  <d:getetag />' .
            '</d:prop>' .
            '<c:filter>' .
            '  <c:comp-filter name="VCALENDAR">' .
            '    <c:comp-filter name="VEVENT" />' .
            '  </c:comp-filter>' .
            '</c:filter>' .
            '</c:calendar-query>';

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'REPORT',
            'REQUEST_URI'    => '/calendars/user1/UUID-123467/UUID-2345',
            'HTTP_DEPTH'     => '0',
        ));
        $request->setBody($body);

        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 207 Multi-Status',$this->response->status,'Received an unexpected status. Full response body: ' . $this->response->body);

        $xml = simplexml_load_string(Sabre_DAV_XMLUtil::convertDAVNamespace($this->response->body));

        $xml->registerXPathNamespace('d','urn:DAV');
        $xml->registerXPathNamespace('c','urn:ietf:params:xml:ns:caldav');

        $check = array(
            '/d:multistatus',
            '/d:multistatus/d:response',
            '/d:multistatus/d:response/d:href',
            '/d:multistatus/d:response/d:propstat',
            '/d:multistatus/d:response/d:propstat/d:prop',
            '/d:multistatus/d:response/d:propstat/d:prop/d:getetag',
            '/d:multistatus/d:response/d:propstat/d:status' => 'HTTP/1.1 200 OK',
        );

        foreach($check as $v1=>$v2) {

            $xpath = is_int($v1)?$v2:$v1;

            $result = $xml->xpath($xpath);
            $this->assertEquals(1,count($result), 'We expected 1 ' . $xpath . ' elements. We\'ve found ' . count($result) . '. Full result: ' . $this->response->body);

            if (!is_int($v1)) $this->assertEquals($v2,(string)$result[0]);

        }

    }

    function testHTMLActionsPanel() {

        $output = '';
        $r = $this->server->broadcastEvent('onHTMLActionsPanel', array($this->server->tree->getNodeForPath('calendars/user1'), &$output));
        $this->assertFalse($r);

        $this->assertTrue(!!strpos($output,'Display name'));

    }

    function testBrowserPostAction() {

        $r = $this->server->broadcastEvent('onBrowserPostAction', array('calendars/user1', 'mkcalendar', array(
            'name' => 'NEWCALENDAR',
            '{DAV:}displayname' => 'foo',
        )));
        $this->assertFalse($r);

        $calendars = $this->caldavBackend->getCalendarsForUser('principals/user1');
        $this->assertEquals(3, count($calendars));

        $newCalendar = null;
        foreach($calendars as $calendar) {
           if ($calendar['uri'] === 'NEWCALENDAR') {
                $newCalendar = $calendar;
                break;
           }
        }
        if (!$newCalendar)
            $this->fail('Could not find newly created calendar');


    }

    /**
     * @depends testCalendarMultiGetReport
     */
    function testCalendarMultiGetReportNoEnd() {

        $body =
            '<?xml version="1.0"?>' .
            '<c:calendar-multiget xmlns:c="urn:ietf:params:xml:ns:caldav" xmlns:d="DAV:">' .
            '<d:prop>' .
            '  <c:calendar-data>' .
            '     <c:expand start="20110101T000000Z" />' .
            '  </c:calendar-data>' .
            '  <d:getetag />' .
            '</d:prop>' .
            '<d:href>/calendars/user1/UUID-123467/UUID-2345</d:href>' .
            '</c:calendar-multiget>';

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'REPORT',
            'REQUEST_URI'    => '/calendars/user1',
            'HTTP_DEPTH'     => '1',
        ));
        $request->setBody($body);

        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 400 Bad request',$this->response->status,'Invalid HTTP status received. Full response body: ' . $this->response->body);

    }

    /**
     * @depends testCalendarMultiGetReport
     */
    function testCalendarMultiGetReportNoStart() {

        $body =
            '<?xml version="1.0"?>' .
            '<c:calendar-multiget xmlns:c="urn:ietf:params:xml:ns:caldav" xmlns:d="DAV:">' .
            '<d:prop>' .
            '  <c:calendar-data>' .
            '     <c:expand end="20110101T000000Z" />' .
            '  </c:calendar-data>' .
            '  <d:getetag />' .
            '</d:prop>' .
            '<d:href>/calendars/user1/UUID-123467/UUID-2345</d:href>' .
            '</c:calendar-multiget>';

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'REPORT',
            'REQUEST_URI'    => '/calendars/user1',
            'HTTP_DEPTH'     => '1',
        ));
        $request->setBody($body);

        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 400 Bad request',$this->response->status,'Invalid HTTP status received. Full response body: ' . $this->response->body);

    }

    /**
     * @depends testCalendarMultiGetReport
     */
    function testCalendarMultiGetReportEndBeforeStart() {

        $body =
            '<?xml version="1.0"?>' .
            '<c:calendar-multiget xmlns:c="urn:ietf:params:xml:ns:caldav" xmlns:d="DAV:">' .
            '<d:prop>' .
            '  <c:calendar-data>' .
            '     <c:expand start="20200101T000000Z" end="20110101T000000Z" />' .
            '  </c:calendar-data>' .
            '  <d:getetag />' .
            '</d:prop>' .
            '<d:href>/calendars/user1/UUID-123467/UUID-2345</d:href>' .
            '</c:calendar-multiget>';

        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'REPORT',
            'REQUEST_URI'    => '/calendars/user1',
            'HTTP_DEPTH'     => '1',
        ));
        $request->setBody($body);

        $this->server->httpRequest = $request;
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 400 Bad request',$this->response->status,'Invalid HTTP status received. Full response body: ' . $this->response->body);

    }
}
