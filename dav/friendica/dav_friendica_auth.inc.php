<?php

class Sabre_DAV_Auth_Backend_Std extends Sabre_DAV_Auth_Backend_AbstractBasic {

    public function __construct() {
    }


	/**
	 * @var Sabre_DAV_Auth_Backend_Std|null
	 */
	private static $intstance = null;

	/**
	 * @static
	 * @return Sabre_DAV_Auth_Backend_Std
	 */
	public static function &getInstance() {
		if (is_null(self::$intstance)) {
			self::$intstance = new Sabre_DAV_Auth_Backend_Std();
		}
		return self::$intstance;
	}


	/**
	 * @return array
	 */
	public function getUsers() {
        return array($this->currentUser);
    }

	/**
	 * @return null|string
	 */
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

		$a = get_app();
		if (isset($a->user["uid"])) {
			$this->currentUser = strtolower($a->user["nickname"]);
			return true;
		}

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


	/**
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	protected function validateUserPass($username, $password) {
		$encrypted = hash('whirlpool',trim($password));
		$r = q("SELECT COUNT(*) anz FROM `user` WHERE `nickname` = '%s' AND `password` = '%s' AND `blocked` = 0 AND `account_expired` = 0 AND `verified` = 1 LIMIT 1",
			dbesc(trim($username)),
			dbesc($encrypted)
		);
		return ($r[0]["anz"] == 1);
    }
    
}
