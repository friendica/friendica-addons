<?php

class Sabre_DAV_Auth_Backend_FileTest extends PHPUnit_Framework_TestCase {

    function tearDown() {

        if (file_exists(SABRE_TEMPDIR . '/filebackend')) unlink(SABRE_TEMPDIR .'/filebackend');

    }

    function testConstruct() {

        $file = new Sabre_DAV_Auth_Backend_File();
        $this->assertTrue($file instanceof Sabre_DAV_Auth_Backend_File);

    }

    /**
     * @expectedException Sabre_DAV_Exception
     */
    function testLoadFileBroken() {

        file_put_contents(SABRE_TEMPDIR . '/backend','user:realm:hash');
        $file = new Sabre_DAV_Auth_Backend_File();
        $file->loadFile(SABRE_TEMPDIR .'/backend');

    }

    function testLoadFile() {

        file_put_contents(SABRE_TEMPDIR . '/backend','user:realm:' . md5('user:realm:password'));
        $file = new Sabre_DAV_Auth_Backend_File();
        $file->loadFile(SABRE_TEMPDIR . '/backend');

        $this->assertFalse($file->getDigestHash('realm','blabla'));
        $this->assertEquals(md5('user:realm:password'), $file->getDigesthash('realm','user'));

    }

}
