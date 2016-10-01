<?php

class Sabre_DAVACL_MockACLNode extends Sabre_DAV_Node implements Sabre_DAVACL_IACL
{
    public $name;
    public $acl;

    public function __construct($name, array $acl = array())
    {
        $this->name = $name;
        $this->acl = $acl;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOwner()
    {
        return null;
    }

    public function getGroup()
    {
        return null;
    }

    public function getACL()
    {
        return $this->acl;
    }

    public function setACL(array $acl)
    {
        $this->acl = $acl;
    }

    public function getSupportedPrivilegeSet()
    {
        return null;
    }
}
