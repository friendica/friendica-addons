<?php

class Sabre_CardDAV_SogoStripContentType extends Sabre_DAVServerTest {

    protected $setupCardDAV = true;
    protected $carddavAddressBooks = array(
        array(
            'id'  => 1,
            'uri' => 'book1',
            'principaluri' => 'principals/user1',
        ),
    );
    protected $carddavCards = array(
        1 => array(
            'card1.vcf' => "BEGIN:VCARD\nVERSION:3.0\nUID:12345\nEND:VCARD",
        ),
    );

    function testDontStrip() {

        $result = $this->server->getProperties('addressbooks/user1/book1/card1.vcf',array('{DAV:}getcontenttype'));
        $this->assertEquals(array(
            '{DAV:}getcontenttype' => 'text/x-vcard; charset=utf-8'
        ), $result);

    }
    function testStrip() {

        $this->server->httpRequest = new Sabre_HTTP_Request(array(
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:10.0.2) Gecko/20120216 Thunderbird/10.0.2 Lightning/1.2.1',
        ));
        $result = $this->server->getProperties('addressbooks/user1/book1/card1.vcf',array('{DAV:}getcontenttype'));
        $this->assertEquals(array(
            '{DAV:}getcontenttype' => 'text/x-vcard'
        ), $result);

    }

}
