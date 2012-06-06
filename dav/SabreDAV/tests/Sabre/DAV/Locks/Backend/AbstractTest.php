<?php

abstract class Sabre_DAV_Locks_Backend_AbstractTest extends PHPUnit_Framework_TestCase {

    /**
     * @abstract
     * @return Sabre_DAV_Locks_Backend_Abstract
     */
    abstract function getBackend();

    function testSetup() {

       $backend = $this->getBackend();
       $this->assertInstanceOf('Sabre_DAV_Locks_Backend_Abstract', $backend);

    }

    /**
     * @depends testSetup
     */
    function testGetLocks() {

        $backend = $this->getBackend();

        $lock = new Sabre_DAV_Locks_LockInfo();
        $lock->owner = 'Sinterklaas';
        $lock->timeout = 60;
        $lock->created = time();
        $lock->token = 'MY-UNIQUE-TOKEN';
        $lock->uri ='someuri';

        $this->assertTrue($backend->lock('someuri', $lock));

        $locks = $backend->getLocks('someuri', false);

        $this->assertEquals(1,count($locks));
        $this->assertEquals('Sinterklaas',$locks[0]->owner);
        $this->assertEquals('someuri',$locks[0]->uri);

    }

    /**
     * @depends testGetLocks
     */
    function testGetLocksParent() {

        $backend = $this->getBackend();

        $lock = new Sabre_DAV_Locks_LockInfo();
        $lock->owner = 'Sinterklaas';
        $lock->timeout = 60;
        $lock->created = time();
        $lock->depth = Sabre_DAV_Server::DEPTH_INFINITY;
        $lock->token = 'MY-UNIQUE-TOKEN';

        $this->assertTrue($backend->lock('someuri', $lock));

        $locks = $backend->getLocks('someuri/child', false);

        $this->assertEquals(1,count($locks));
        $this->assertEquals('Sinterklaas',$locks[0]->owner);
        $this->assertEquals('someuri',$locks[0]->uri);

    }


    /**
     * @depends testGetLocks
     */
    function testGetLocksParentDepth0() {

        $backend = $this->getBackend();

        $lock = new Sabre_DAV_Locks_LockInfo();
        $lock->owner = 'Sinterklaas';
        $lock->timeout = 60;
        $lock->created = time();
        $lock->depth = 0;
        $lock->token = 'MY-UNIQUE-TOKEN';

        $this->assertTrue($backend->lock('someuri', $lock));

        $locks = $backend->getLocks('someuri/child', false);

        $this->assertEquals(0,count($locks));

    }

    function testGetLocksChildren() {

        $backend = $this->getBackend();

        $lock = new Sabre_DAV_Locks_LockInfo();
        $lock->owner = 'Sinterklaas';
        $lock->timeout = 60;
        $lock->created = time();
        $lock->depth = 0;
        $lock->token = 'MY-UNIQUE-TOKEN';

        $this->assertTrue($backend->lock('someuri/child', $lock));

        $locks = $backend->getLocks('someuri/child', false);
        $this->assertEquals(1,count($locks));

        $locks = $backend->getLocks('someuri', false);
        $this->assertEquals(0,count($locks));

        $locks = $backend->getLocks('someuri', true);
        $this->assertEquals(1,count($locks));

    }

    /**
     * @depends testGetLocks
     */
    function testLockRefresh() {

        $backend = $this->getBackend();

        $lock = new Sabre_DAV_Locks_LockInfo();
        $lock->owner = 'Sinterklaas';
        $lock->timeout = 60;
        $lock->created = time();
        $lock->token = 'MY-UNIQUE-TOKEN';

        $this->assertTrue($backend->lock('someuri', $lock));
        /* Second time */

        $lock->owner = 'Santa Clause';
        $this->assertTrue($backend->lock('someuri', $lock));

        $locks = $backend->getLocks('someuri', false);

        $this->assertEquals(1,count($locks));

        $this->assertEquals('Santa Clause',$locks[0]->owner);
        $this->assertEquals('someuri',$locks[0]->uri);

    }

    /**
     * @depends testGetLocks
     */
    function testUnlock() {

        $backend = $this->getBackend();

        $lock = new Sabre_DAV_Locks_LockInfo();
        $lock->owner = 'Sinterklaas';
        $lock->timeout = 60;
        $lock->created = time();
        $lock->token = 'MY-UNIQUE-TOKEN';

        $this->assertTrue($backend->lock('someuri', $lock));

        $locks = $backend->getLocks('someuri', false);
        $this->assertEquals(1,count($locks));

        $this->assertTrue($backend->unlock('someuri',$lock));

        $locks = $backend->getLocks('someuri', false);
        $this->assertEquals(0,count($locks));

    }

    /**
     * @depends testUnlock
     */
    function testUnlockUnknownToken() {

        $backend = $this->getBackend();

        $lock = new Sabre_DAV_Locks_LockInfo();
        $lock->owner = 'Sinterklaas';
        $lock->timeout = 60;
        $lock->created = time();
        $lock->token = 'MY-UNIQUE-TOKEN';

        $this->assertTrue($backend->lock('someuri', $lock));

        $locks = $backend->getLocks('someuri', false);
        $this->assertEquals(1,count($locks));

        $lock->token = 'SOME-OTHER-TOKEN';
        $this->assertFalse($backend->unlock('someuri',$lock));

        $locks = $backend->getLocks('someuri', false);
        $this->assertEquals(1,count($locks));

    }

}
