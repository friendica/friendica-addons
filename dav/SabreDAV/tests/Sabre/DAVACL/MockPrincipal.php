<?php

class Sabre_DAVACL_MockPrincipal extends Sabre_DAV_Node implements Sabre_DAVACL_IPrincipal
{
    public $name;
    public $principalUrl;
    public $groupMembership = array();
    public $groupMemberSet = array();

    public function __construct($name, $principalUrl, array $groupMembership = array(), array $groupMemberSet = array())
    {
        $this->name = $name;
        $this->principalUrl = $principalUrl;
        $this->groupMembership = $groupMembership;
        $this->groupMemberSet = $groupMemberSet;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDisplayName()
    {
        return $this->getName();
    }

    public function getAlternateUriSet()
    {
        return array();
    }

    public function getPrincipalUrl()
    {
        return $this->principalUrl;
    }

    public function getGroupMemberSet()
    {
        return $this->groupMemberSet;
    }

    public function getGroupMemberShip()
    {
        return $this->groupMembership;
    }

    public function setGroupMemberSet(array $groupMemberSet)
    {
        $this->groupMemberSet = $groupMemberSet;
    }
}
