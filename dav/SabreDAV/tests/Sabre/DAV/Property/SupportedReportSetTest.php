<?php

require_once 'Sabre/HTTP/ResponseMock.php';
require_once 'Sabre/DAV/AbstractServer.php';

class Sabre_DAV_Property_SupportedReportSetTest extends Sabre_DAV_AbstractServer {

    public function sendPROPFIND($body) {

        $serverVars = array(
            'REQUEST_URI'    => '/',
            'REQUEST_METHOD' => 'PROPFIND',
            'HTTP_DEPTH'          => '0',
        );

        $request = new Sabre_HTTP_Request($serverVars);
        $request->setBody($body);

        $this->server->httpRequest = ($request);
        $this->server->exec();

    }

    /**
     * @covers Sabre_DAV_Property_SupportedReportSet
     */
    function testNoReports() {

        $xml = '<?xml version="1.0"?>
<d:propfind xmlns:d="DAV:">
  <d:prop>
    <d:supported-report-set />
  </d:prop>
</d:propfind>';

        $this->sendPROPFIND($xml);

        $this->assertEquals('HTTP/1.1 207 Multi-Status',$this->response->status,'We expected a multi-status response. Full response body: ' . $this->response->body);

        $body = preg_replace("/xmlns(:[A-Za-z0-9_])?=(\"|\')DAV:(\"|\')/","xmlns\\1=\"DAV:\"",$this->response->body);
        $xml = simplexml_load_string($body);
        $xml->registerXPathNamespace('d','DAV:');

        $data = $xml->xpath('/d:multistatus/d:response/d:propstat/d:prop');
        $this->assertEquals(1,count($data),'We expected 1 \'d:prop\' element');

        $data = $xml->xpath('/d:multistatus/d:response/d:propstat/d:prop/d:supported-report-set');
        $this->assertEquals(1,count($data),'We expected 1 \'d:supported-report-set\' element');

        $data = $xml->xpath('/d:multistatus/d:response/d:propstat/d:status');
        $this->assertEquals(1,count($data),'We expected 1 \'d:status\' element');

        $this->assertEquals('HTTP/1.1 200 OK',(string)$data[0],'The status for this property should have been 200');

    }

    /**
     * @covers Sabre_DAV_Property_SupportedReportSet
     * @depends testNoReports
     */
    function testCustomReport() {

        // Intercepting the report property
        $this->server->subscribeEvent('afterGetProperties',array($this,'addProp'));

        $xml = '<?xml version="1.0"?>
<d:propfind xmlns:d="DAV:">
  <d:prop>
    <d:supported-report-set />
  </d:prop>
</d:propfind>';

        $this->sendPROPFIND($xml);

        $this->assertEquals('HTTP/1.1 207 Multi-Status',$this->response->status,'We expected a multi-status response. Full response body: ' . $this->response->body);

        $body = preg_replace("/xmlns(:[A-Za-z0-9_])?=(\"|\')DAV:(\"|\')/","xmlns\\1=\"DAV:\"",$this->response->body);
        $xml = simplexml_load_string($body);
        $xml->registerXPathNamespace('d','DAV:');
        $xml->registerXPathNamespace('x','http://www.rooftopsolutions.nl/testnamespace');

        $data = $xml->xpath('/d:multistatus/d:response/d:propstat/d:prop');
        $this->assertEquals(1,count($data),'We expected 1 \'d:prop\' element');

        $data = $xml->xpath('/d:multistatus/d:response/d:propstat/d:prop/d:supported-report-set');
        $this->assertEquals(1,count($data),'We expected 1 \'d:supported-report-set\' element');

        $data = $xml->xpath('/d:multistatus/d:response/d:propstat/d:prop/d:supported-report-set/d:supported-report');
        $this->assertEquals(2,count($data),'We expected 2 \'d:supported-report\' elements');

        $data = $xml->xpath('/d:multistatus/d:response/d:propstat/d:prop/d:supported-report-set/d:supported-report/d:report');
        $this->assertEquals(2,count($data),'We expected 2 \'d:report\' elements');

        $data = $xml->xpath('/d:multistatus/d:response/d:propstat/d:prop/d:supported-report-set/d:supported-report/d:report/x:myreport');
        $this->assertEquals(1,count($data),'We expected 1 \'x:myreport\' element. Full body: ' . $this->response->body);

        $data = $xml->xpath('/d:multistatus/d:response/d:propstat/d:prop/d:supported-report-set/d:supported-report/d:report/d:anotherreport');
        $this->assertEquals(1,count($data),'We expected 1 \'d:anotherreport\' element. Full body: ' . $this->response->body);

        $data = $xml->xpath('/d:multistatus/d:response/d:propstat/d:status');
        $this->assertEquals(1,count($data),'We expected 1 \'d:status\' element');

        $this->assertEquals('HTTP/1.1 200 OK',(string)$data[0],'The status for this property should have been 200');

    }

    /**
     * This method is used as a callback for afterGetProperties
     */
    function addProp($path, &$properties) {

        if (isset($properties[200]['{DAV:}supported-report-set'])) {
            $properties[200]['{DAV:}supported-report-set']->addReport('{http://www.rooftopsolutions.nl/testnamespace}myreport');
            $properties[200]['{DAV:}supported-report-set']->addReport('{DAV:}anotherreport');
        }

    }



}

?>
