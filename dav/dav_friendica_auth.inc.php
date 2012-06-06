<?php

class Sabre_DAV_Auth_Backend_Friendica extends Sabre_DAV_Auth_Backend_AbstractBasic {

    public function __construct() {
    }


    public function getUsers() {
        return array($this->currentUser);
    }
    
    public function getCurrentUser() {
        return $this->currentUser;
    }

	/**
	 * Authenticates the user based on the current request.
	 *
	 * If authentication is successful, true must be returned.
	 * If authentication fails, an exception must be thrown.
	 *
	 * @param Sabre_DAV_Server $server
	 * @param string $realm
	 * @throws Sabre_DAV_Exception_NotAuthenticated
	 * @return bool
	 */
	public function authenticate(Sabre_DAV_Server $server, $realm) {

		$auth = new Sabre_HTTP_BasicAuth();
		$auth->setHTTPRequest($server->httpRequest);
		$auth->setHTTPResponse($server->httpResponse);
		$auth->setRealm($realm);
		$userpass = $auth->getUserPass();
		if (!$userpass) {
			$auth->requireLogin();
			throw new Sabre_DAV_Exception_NotAuthenticated('No basic authentication headers were found');
		}

		// Authenticates the user
		if (!$this->validateUserPass($userpass[0],$userpass[1])) {
			$auth->requireLogin();
			throw new Sabre_DAV_Exception_NotAuthenticated('Username or password does not match');
		}
		$this->currentUser = strtolower($userpass[0]);
		return true;
	}


	protected function validateUserPass($username, $password) {

		$user = array(
	            'uri' => "/" . 'principals/users/' . strtolower($username),
		);
		return $user;
    }
    
}
