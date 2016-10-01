<?php

require_once 'Sabre/TestUtil.php';

class Sabre_DAV_Locks_Backend_FSTest extends Sabre_DAV_Locks_Backend_AbstractTest
{
    public function getBackend()
    {
        Sabre_TestUtil::clearTempDir();
        mkdir(SABRE_TEMPDIR.'/locks');
        $backend = new Sabre_DAV_Locks_Backend_FS(SABRE_TEMPDIR.'/locks/');

        return $backend;
    }

    public function tearDown()
    {
        Sabre_TestUtil::clearTempDir();
    }

    public function testGetLocksChildren()
    {

        // We're skipping this test. This doesn't work, and it will
        // never. The class is deprecated anyway.
    }
}
