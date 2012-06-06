<?php

require_once 'Sabre/HTTP/ResponseMock.php';
require_once 'Sabre/DAVACL/MockPrincipalBackend.php';

class Sabre_DAVACL_PrincipalSearchPropertySetTest extends PHPUnit_Framework_TestCase {

    function getServer() {

        $backend = new Sabre_DAVACL_MockPrincipalBackend();

        $dir = new Sabre_DAV_SimpleCollection('root');
        $principals = new Sabre_DAVACL_PrincipalCollection($backend);
        $dir->addChild($principals);

        $fakeServer = new Sabre_DAV_Server(new Sabre_DAV_ObjectTree($dir));
        $fakeServer->httpResponse = new Sabre_HTTP_ResponseMock();
        $plugin = new Sabre_DAVACL_Plugin($backend,'realm');
        $this->assertTrue($plugin instanceof Sabre_DAVACL_Plugin);
        $fakeServer->addPlugin($plugin);
        $this->assertEquals($plugin, $fakeServer->getPlugin('acl'));

        return $fakeServer;

    }

    function testDepth1() {

        $xml = '<?xml version="1.0"?>
<d:principal-search-property-set xmlns:d="DAV:" />';

        $serverVars = array(
            'REQUEST_METHOD' => 'REPORT',
            'HTTP_DEPTH'     => '1',
            'REQUEST_URI'    => '/principals',
        );

        $request = new Sabre_HTTP_Request($serverVars);
        $request->setBody($xml);

        $server = $this->getServer();
        $server->httpRequest = $request;

        $server->exec();

        $this->assertEquals('HTTP/1.1 400 Bad request', $server->httpResponse->status);
        $this->assertEquals(array(
            'Content-Type' => 'application/xml; charset=utf-8',
        ), $server->httpResponse->headers);

    }

    function testDepthIncorrectXML() {

        $xml = '<?xml version="1.0"?>
<d:principal-search-property-set xmlns:d="DAV:"><d:ohell /></d:principal-search-property-set>';

        $serverVars = array(
            'REQUEST_METHOD' => 'REPORT',
            'HTTP_DEPTH'     => '0',
            'REQUEST_URI'    => '/principals',
        );

        $request = new Sabre_HTTP_Request($serverVars);
        $request->setBody($xml);

        $server = $this->getServer();
        $server->httpRequest = $request;

        $server->exec();

        $this->assertEquals('HTTP/1.1 400 Bad request', $server->httpResponse->status, $server->httpResponse->body);
        $this->assertEquals(array(
            'Content-Type' => 'application/xml; charset=utf-8',
        ), $server->httpResponse->headers);

    }

    function testCorrect() {

        $xml = '<?xml version="1.0"?>
<d:principal-search-property-set xmlns:d="DAV:"/>';

        $serverVars = array(
            'REQUEST_METHOD' => 'REPORT',
            'HTTP_DEPTH'     => '0',
            'REQUEST_URI'    => '/principals',
        );

        $request = new Sabre_HTTP_Request($serverVars);
        $request->setBody($xml);

        $server = $this->getServer();
        $server->httpRequest = $request;

        $server->exec();

        $this->assertEquals('HTTP/1.1 200 OK', $server->httpResponse->status, $server->httpResponse->body);
        $this->assertEquals(array(
            'Content-Type' => 'application/xml; charset=utf-8',
        ), $server->httpResponse->headers);


        $check = array(
            '/d:principal-search-property-set',
            '/d:principal-search-property-set/d:principal-search-property' => 2,
            '/d:principal-search-property-set/d:principal-search-property/d:prop' => 2,
            '/d:principal-search-property-set/d:principal-search-property/d:prop/d:displayname' => 1,
            '/d:principal-search-property-set/d:principal-search-property/d:prop/s:email-address' => 1,
            '/d:principal-search-property-set/d:principal-search-property/d:description' => 2,
        );

        $xml = simplexml_load_string($server->httpResponse->body);
        $xml->registerXPathNamespace('d','DAV:');
        $xml->registerXPathNamespace('s','http://sabredav.org/ns');
        foreach($check as $v1=>$v2) {

            $xpath = is_int($v1)?$v2:$v1;

            $result = $xml->xpath($xpath);

            $count = 1;
            if (!is_int($v1)) $count = $v2;

            $this->assertEquals($count,count($result), 'we expected ' . $count . ' appearances of ' . $xpath . ' . We found ' . count($result) . '. Full response body: ' . $server->httpResponse->body);

        }

    }

}
