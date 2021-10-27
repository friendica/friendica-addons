<?php

/**
 * Name: LDAP Authenticate
 * Description: Authenticate a user against an LDAP directory
 * Version: 1.1
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: aymhce
 */

/**
 * Friendica addon
 *
 * Module: LDAP Authenticate
 *
 * Authenticate a user against an LDAP directory
 * Useful for Windows Active Directory and other LDAP-based organisations
 * to maintain a single password across the organisation.
 *
 * Optionally authenticates only if a member of a given group in the directory.
 *
 * By default, the person must have registered with Friendica using the normal registration
 * procedures in order to have a Friendica user record, contact, and profile.
 * However, it's possible with an option to automate the creation of a Friendica basic account.
 *
 * Note when using with Windows Active Directory: you may need to set TLS_CACERT in your site
 * ldap.conf file to the signing cert for your LDAP server.
 *
 * The configuration options for this module may be set in the config/addon.config.php file
 * e.g.:
 *
 * [ldapauth]
 * ; ldap hostname server - required
 * ldap_server = host.example.com
 * ; dn to search users - required
 * ldap_searchdn = ou=users,dc=example,dc=com
 * ; attribute to find username - required
 * ldap_userattr = uid
 *
 * ; admin dn - optional - only if ldap server dont have anonymous access
 * ldap_binddn = cn=admin,dc=example,dc=com
 * ; admin password - optional - only if ldap server dont have anonymous access
 * ldap_bindpw = password
 *
 * ; for create Friendica account if user exist in ldap
 * ;     required an email and a simple (beautiful) nickname on user ldap object
 * ;   active account creation - optional - default none
 * ldap_autocreateaccount = true
 * ;   attribute to get email - optional - default : 'mail'
 * ldap_autocreateaccount_emailattribute = mail
 * ;   attribute to get nickname - optional - default : 'givenName'
 * ldap_autocreateaccount_nameattribute = cn
 *
 * ...etc.
 */

use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\User;
use Friendica\Core\Config\Util\ConfigFileLoader;

function ldapauth_install()
{
	Hook::register('load_config',  'addon/ldapauth/ldapauth.php', 'ldapauth_load_config');
	Hook::register('authenticate', 'addon/ldapauth/ldapauth.php', 'ldapauth_hook_authenticate');
}

function ldapauth_load_config(\Friendica\App $a, ConfigFileLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('ldapauth'));
}

function ldapauth_hook_authenticate($a, &$b)
{
	$user = ldapauth_authenticate($b['username'], $b['password']);
	if (!empty($user['uid'])) {
		$b['user_record'] = User::getById($user['uid']);
		$b['authenticated'] = 1;
	}
}

function ldapauth_authenticate($username, $password)
{
	$ldap_server   = DI::config()->get('ldapauth', 'ldap_server');
	$ldap_binddn   = DI::config()->get('ldapauth', 'ldap_binddn');
	$ldap_bindpw   = DI::config()->get('ldapauth', 'ldap_bindpw');
	$ldap_searchdn = DI::config()->get('ldapauth', 'ldap_searchdn');
	$ldap_userattr = DI::config()->get('ldapauth', 'ldap_userattr');
	$ldap_group    = DI::config()->get('ldapauth', 'ldap_group');
	$ldap_autocreateaccount = DI::config()->get('ldapauth', 'ldap_autocreateaccount');
	$ldap_autocreateaccount_emailattribute = DI::config()->get('ldapauth', 'ldap_autocreateaccount_emailattribute');
	$ldap_autocreateaccount_nameattribute  = DI::config()->get('ldapauth', 'ldap_autocreateaccount_nameattribute');

	if (!extension_loaded('ldap') || !strlen($ldap_server)) {
		Logger::error('Addon not configured or missing php-ldap extension', ['extension_loaded' => extension_loaded('ldap'), 'server' => $ldap_server]);
		return false;
	}

	if (!strlen($password)) {
		Logger::error('Empty password disallowed', ['provided_password_length' => strlen($password)]);
		return false;
	}

	$connect = @ldap_connect($ldap_server);
	if ($connect === false) {
		Logger::warning('Could not connect to LDAP server', ['server' => $ldap_server]);
		return false;
	}

	@ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
	@ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);
	if ((@ldap_bind($connect, $ldap_binddn, $ldap_bindpw)) === false) {
		Logger::warning('Could not bind to LDAP server', ['server' => $ldap_server, 'binddn' => $ldap_binddn, 'errno' => ldap_errno($connect), 'error' => ldap_error($connect)]);
		return false;
	}

	$res = @ldap_search($connect, $ldap_searchdn, $ldap_userattr . '=' . $username);
	if (!$res) {
		Logger::notice('LDAP user not found.', ['searchdn' => $ldap_searchdn, 'userattr' => $ldap_userattr, 'username' => $username, 'errno' => ldap_errno($connect), 'error' => ldap_error($connect)]);
		return false;
	}

	$id = @ldap_first_entry($connect, $res);
	if (!$id) {
		Logger::notice('Could not retrieve first LDAP entry.', ['searchdn' => $ldap_searchdn, 'userattr' => $ldap_userattr, 'username' => $username, 'errno' => ldap_errno($connect), 'error' => ldap_error($connect)]);
		return false;
	}

	$dn = @ldap_get_dn($connect, $id);
	if (!@ldap_bind($connect, $dn, $password)) {
		Logger::notice('Could not authenticate LDAP user with provided password', ['errno' => ldap_errno($connect), 'error' => ldap_error($connect)]);
		return false;
	}

	if (strlen($ldap_group) && @ldap_compare($connect, $ldap_group, 'member', $dn) !== true) {
		$errno = @ldap_errno($connect);
		if ($errno === 32) {
			Logger::notice('LDAP Access Control Group does not exist', ['errno' => $errno, 'error' => ldap_error($connect)]);
		} elseif ($errno === 16) {
			Logger::notice('LDAP membership attribute does not exist in access control group', ['errno' => $errno, 'error' => ldap_error($connect)]);
		} else {
			Logger::notice('LDAP user isn\'t part of the authorized group', ['dn' => $dn]);
		}

		@ldap_close($connect);
		return false;
	}

	if ($ldap_autocreateaccount == 'true' && !DBA::exists('user', ['nickname' => $username])) {
		if (!strlen($ldap_autocreateaccount_emailattribute)) {
			$ldap_autocreateaccount_emailattribute = 'mail';
		}
		if (!strlen($ldap_autocreateaccount_nameattribute)) {
			$ldap_autocreateaccount_nameattribute = 'givenName';
		}
		$email_values = @ldap_get_values($connect, $id, $ldap_autocreateaccount_emailattribute);
		$name_values = @ldap_get_values($connect, $id, $ldap_autocreateaccount_nameattribute);

		return ldap_createaccount($username, $password, $email_values[0] ?? '', $name_values[0] ?? '');
	}

	try {
		$authentication = User::getAuthenticationInfo($username);
		return User::getById($authentication['uid']);
	} catch (Exception $e) {
		Logger::notice('LDAP authentication error: ' . $e->getMessage());
		return false;
	}
}

function ldap_createaccount($username, $password, $email, $name)
{
	if (!strlen($email) || !strlen($name)) {
		Logger::notice('Could not create local user from LDAP data, no email or nickname provided');
		return false;
	}

	try {
		$user = User::create([
			'username' => $name,
			'nickname' => $username,
			'email'    => $email,
			'password' => $password,
			'verified' => 1
		]);
		Logger::info('Local user created from LDAP data', ['username' => $username, 'name' => $name]);
		return $user;
	} catch (Exception $ex) {
		Logger::error('Could not create local user from LDAP data', ['username' => $username, 'exception' => $ex->getMessage()]);
	}

	return false;
}
