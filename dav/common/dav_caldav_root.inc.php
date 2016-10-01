<?php

/**
 * Users collection.
 *
 * This object is responsible for generating a collection of users.
 *
 * @copyright Copyright (C) 2007-2011 Rooftop Solutions. All rights reserved
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Sabre_CalDAV_AnimexxCalendarRootNode extends Sabre_DAVACL_AbstractPrincipalCollection
{
    /**
     * CalDAV backend.
     *
     * @var array|Sabre_CalDAV_Backend_Abstract[]
     */
    protected $caldavBackends;

    /**
     * Constructor.
     *
     * This constructor needs both an authentication and a caldav backend.
     *
     * @param Sabre_DAVACL_IPrincipalBackend        $principalBackend
     * @param array|Sabre_CalDAV_Backend_Abstract[] $caldavBackends
     * @param string                                $principalPrefix
     */
    public function __construct(Sabre_DAVACL_IPrincipalBackend $principalBackend, $caldavBackends, $principalPrefix = 'principals/users')
    {
        parent::__construct($principalBackend, $principalPrefix);
        $this->caldavBackends = $caldavBackends;
    }

    /**
     * Returns the nodename.
     *
     * We're overriding this, because the default will be the 'principalPrefix',
     * and we want it to be Sabre_CalDAV_Plugin::CALENDAR_ROOT
     *
     * @return string
     */
    public function getName()
    {
        return Sabre_CalDAV_Plugin::CALENDAR_ROOT;
    }

    /**
     * This method returns a node for a principal.
     *
     * The passed array contains principal information, and is guaranteed to
     * at least contain a uri item. Other properties may or may not be
     * supplied by the authentication backend.
     *
     * @param array $principal
     *
     * @return \Sabre_CalDAV_AnimexxUserCalendars|\Sabre_DAVACL_IPrincipal
     */
    public function getChildForPrincipal(array $principal)
    {
        return new Sabre_CalDAV_AnimexxUserCalendars($this->principalBackend, $this->caldavBackends, $principal['uri']);
    }
}
