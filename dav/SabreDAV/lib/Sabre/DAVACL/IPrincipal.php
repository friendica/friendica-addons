<?php

/**
 * IPrincipal interface.
 *
 * Implement this interface to define your own principals
 *
 * @copyright Copyright (C) 2007-2012 Rooftop Solutions. All rights reserved
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
interface Sabre_DAVACL_IPrincipal extends Sabre_DAV_INode
{
    /**
     * Returns a list of alternative urls for a principal.
     *
     * This can for example be an email address, or ldap url.
     *
     * @return array
     */
    public function getAlternateUriSet();

    /**
     * Returns the full principal url.
     *
     * @return string
     */
    public function getPrincipalUrl();

    /**
     * Returns the list of group members.
     *
     * If this principal is a group, this function should return
     * all member principal uri's for the group.
     *
     * @return array
     */
    public function getGroupMemberSet();

    /**
     * Returns the list of groups this principal is member of.
     *
     * If this principal is a member of a (list of) groups, this function
     * should return a list of principal uri's for it's members.
     *
     * @return array
     */
    public function getGroupMembership();

    /**
     * Sets a list of group members.
     *
     * If this principal is a group, this method sets all the group members.
     * The list of members is always overwritten, never appended to.
     *
     * This method should throw an exception if the members could not be set.
     *
     * @param array $principals
     */
    public function setGroupMemberSet(array $principals);

    /**
     * Returns the displayname.
     *
     * This should be a human readable name for the principal.
     * If none is available, return the nodename.
     *
     * @return string
     */
    public function getDisplayName();
}
