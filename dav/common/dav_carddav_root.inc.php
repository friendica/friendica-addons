<?php

class Sabre_CardDAV_AddressBookRootFriendica extends Sabre_DAVACL_AbstractPrincipalCollection {

	/**
	 * Principal Backend
	 *
	 * @var Sabre_DAVACL_IPrincipalBackend
	 */
	protected $principalBackend;

	/**
	 * CardDAV backend
	 *
	 * @var array|Sabre_CardDAV_Backend_Abstract[]
	 */
	protected $carddavBackends;

	/**
	 * Constructor
	 *
	 * This constructor needs both a principal and a carddav backend.
	 *
	 * By default this class will show a list of addressbook collections for
	 * principals in the 'principals' collection. If your main principals are
	 * actually located in a different path, use the $principalPrefix argument
	 * to override this.
	 *
	 * @param Sabre_DAVACL_IPrincipalBackend $principalBackend
	 * @param array|Sabre_CardDAV_Backend_Abstract[] $carddavBackends
	 * @param string $principalPrefix
	 */
	public function __construct(Sabre_DAVACL_IPrincipalBackend $principalBackend, $carddavBackends, $principalPrefix = 'principals/users') {

		$this->carddavBackends = $carddavBackends;
		parent::__construct($principalBackend, $principalPrefix);

	}

	/**
	 * Returns the name of the node
	 *
	 * @return string
	 */
	public function getName() {
		return Sabre_CardDAV_Plugin::ADDRESSBOOK_ROOT;
	}

	/**
	 * This method returns a node for a principal.
	 *
	 * The passed array contains principal information, and is guaranteed to
	 * at least contain a uri item. Other properties may or may not be
	 * supplied by the authentication backend.
	 *
	 * @param array $principal
	 * @return Sabre_DAVACL_IPrincipal
	 */
	public function getChildForPrincipal(array $principal) {
		return new Sabre_CardDAV_UserAddressBooksMultiBackend($this->carddavBackends, $principal['uri']);

	}

}
