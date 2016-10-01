<?php

require_once 'Sabre/TestUtil.php';

class Sabre_DAV_FSExt_FileTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        file_put_contents(SABRE_TEMPDIR.'/file.txt', 'Contents');
    }

    public function tearDown()
    {
        Sabre_TestUtil::clearTempDir();
    }

    public function testPut()
    {
        $file = new Sabre_DAV_FSExt_File(SABRE_TEMPDIR.'/file.txt');
        $result = $file->put('New contents');

        $this->assertEquals('New contents', file_get_contents(SABRE_TEMPDIR.'/file.txt'));
        $this->assertEquals('"'.md5('New contents').'"', $result);
    }

    public function testRange()
    {
        $file = new Sabre_DAV_FSExt_File(SABRE_TEMPDIR.'/file.txt');
        $file->put('0000000');
        $file->putRange('111', 3);

        $this->assertEquals('0011100', file_get_contents(SABRE_TEMPDIR.'/file.txt'));
    }

    public function testGet()
    {
        $file = new Sabre_DAV_FSExt_File(SABRE_TEMPDIR.'/file.txt');
        $this->assertEquals('Contents', stream_get_contents($file->get()));
    }

    public function testDelete()
    {
        $file = new Sabre_DAV_FSExt_File(SABRE_TEMPDIR.'/file.txt');
        $file->delete();

        $this->assertFalse(file_exists(SABRE_TEMPDIR.'/file.txt'));
    }

    public function testGetETag()
    {
        $file = new Sabre_DAV_FSExt_File(SABRE_TEMPDIR.'/file.txt');
        $this->assertEquals('"'.md5('Contents').'"', $file->getETag());
    }

    public function testGetContentType()
    {
        $file = new Sabre_DAV_FSExt_File(SABRE_TEMPDIR.'/file.txt');
        $this->assertNull($file->getContentType());
    }

    public function testGetSize()
    {
        $file = new Sabre_DAV_FSExt_File(SABRE_TEMPDIR.'/file.txt');
        $this->assertEquals(8, $file->getSize());
    }
}
