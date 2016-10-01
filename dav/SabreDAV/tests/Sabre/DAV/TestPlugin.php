<?php

class Sabre_DAV_TestPlugin extends Sabre_DAV_ServerPlugin
{
    public $beforeMethod;

    public function getFeatures()
    {
        return array('drinking');
    }

    public function getHTTPMethods($uri)
    {
        return array('BEER', 'WINE');
    }

    public function initialize(Sabre_DAV_Server $server)
    {
        $server->subscribeEvent('beforeMethod', array($this, 'beforeMethod'));
    }

    public function beforeMethod($method)
    {
        $this->beforeMethod = $method;

        return true;
    }
}
