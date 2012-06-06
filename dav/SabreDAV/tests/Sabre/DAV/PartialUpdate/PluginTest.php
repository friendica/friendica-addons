<?php

require_once 'Sabre/DAV/PartialUpdate/FileMock.php';

class Sabre_DAV_PartialUpdate_PluginTest extends Sabre_DAVServerTest {

    protected $node;
    protected $plugin;

    public function setUp() {

        $this->node = new Sabre_DAV_PartialUpdate_FileMock();
        $this->tree[] = $this->node;

        parent::setUp();

        $this->plugin = new Sabre_DAV_PartialUpdate_Plugin();
        $this->server->addPlugin($this->plugin);



    }

    public function testInit() {

        $this->assertEquals('partialupdate', $this->plugin->getPluginName());
        $this->assertEquals(array('sabredav-partialupdate'), $this->plugin->getFeatures());
        $this->assertEquals(array(
            'PATCH'
        ), $this->plugin->getHTTPMethods('partial'));
        $this->assertEquals(array(
        ), $this->plugin->getHTTPMethods(''));

        $this->assertNull($this->plugin->unknownMethod('FOO','partial'));

    }

    public function testPatchNoRange() {

        $this->node->put('00000000');
        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'PATCH',
            'REQUEST_URI'    => '/partial',
        ));
        $response = $this->request($request);

        $this->assertEquals('HTTP/1.1 400 Bad request', $response->status, 'Full response body:' . $response->body);

    }

    public function testPatchNotSupported() {

        $this->node->put('00000000');
        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD' => 'PATCH',
            'REQUEST_URI'    => '/',
            'X_UPDATE_RANGE' => '3-4',

        ));
        $request->setBody(
            '111'
        );
        $response = $this->request($request);

        $this->assertEquals('HTTP/1.1 405 Method Not Allowed', $response->status, 'Full response body:' . $response->body);

    }

    public function testPatchNoContentType() {

        $this->node->put('00000000');
        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD'      => 'PATCH',
            'REQUEST_URI'         => '/partial',
            'HTTP_X_UPDATE_RANGE' => 'bytes=3-4',

        ));
        $request->setBody(
            '111'
        );
        $response = $this->request($request);

        $this->assertEquals('HTTP/1.1 415 Unsupported Media Type', $response->status, 'Full response body:' . $response->body);

    }

    public function testPatchBadRange() {

        $this->node->put('00000000');
        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD'      => 'PATCH',
            'REQUEST_URI'         => '/partial',
            'HTTP_X_UPDATE_RANGE' => 'bytes=3-4',
            'HTTP_CONTENT_TYPE'   => 'application/x-sabredav-partialupdate',
        ));
        $request->setBody(
            '111'
        );
        $response = $this->request($request);

        $this->assertEquals('HTTP/1.1 416 Requested Range Not Satisfiable', $response->status, 'Full response body:' . $response->body);

    }

    public function testPatchSuccess() {

        $this->node->put('00000000');
        $request = new Sabre_HTTP_Request(array(
            'REQUEST_METHOD'      => 'PATCH',
            'REQUEST_URI'         => '/partial',
            'HTTP_X_UPDATE_RANGE' => 'bytes=3-5',
            'HTTP_CONTENT_TYPE'   => 'application/x-sabredav-partialupdate',
            'HTTP_CONTENT_LENGTH' => 3,
        ));
        $request->setBody(
            '111'
        );
        $response = $this->request($request);

        $this->assertEquals('HTTP/1.1 204 No Content', $response->status, 'Full response body:' . $response->body);
        $this->assertEquals('00111000', $this->node->get());

    }

}
