<?php

require_once 'Sabre/CalDAV/Backend/AbstractPDOTest.php';

class Sabre_CalDAV_Backend_PDOSqliteTest extends Sabre_CalDAV_Backend_AbstractPDOTest
{
    public function setup()
    {
        if (!SABRE_HASSQLITE) {
            $this->markTestSkipped('SQLite driver is not available');
        }
        $this->pdo = Sabre_CalDAV_TestUtil::getSQLiteDB();
    }

    public function teardown()
    {
        $this->pdo = null;
        unlink(SABRE_TEMPDIR.'/testdb.sqlite');
    }
}
