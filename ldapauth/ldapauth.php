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

use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Model\User;
use Friendica\Util\ConfigFileLoader;

function ldapauth_install()
{
	Hook::register('load_config',  'addon/ldapauth/ldapauth.php', 'ldapauth_load_config');
	Hook::register('authenticate', 'addon/ldapauth/ldapauth.php', 'ldapauth_hook_authenticate');
}

function ldapauth_uninstall()
{
	Hook::unregister('load_config',  'addon/ldapauth/ldapauth.php', 'ldapauth_load_config');
	Hook::unregister('authenticate', 'addon/ldapauth/ldapauth.php', 'ldapauth_hook_authenticate');
}

function ldapauth_load_config(\Friendica\App $a, ConfigFileLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('ldapauth'));
}

function ldapauth_hook_authenticate($a, &$b)
{
	if (ldapauth_authenticate($b['username'], $b['password'])) {
		$results = get_existing_account($b['username']);
		if (!empty($results)) {
			$b['user_record'] = $results[0];
			$b['authenticated'] = 1;
		}
	}
	return;
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

	if (!(strlen($password) && function_exists('ldap_connect') && strlen($ldap_server))) {
		Logger::log("ldapauth: not configured or missing php-ldap module");
		return false;
	}

	$connect = @ldap_connect($ldap_server);

	if ($connect === false) {
		Logger::log("ldapauth: could not connect to $ldap_server");
		return false;
	}

	@ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
	@ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);
	if ((@ldap_bind($connect, $ldap_binddn, $ldap_bindpw)) === false) {
		Logger::log("ldapauth: could not bind $ldap_server as $ldap_binddn");
		return false;
	}

	$res = @ldap_search($connect, $ldap_searchdn, $ldap_userattr . '=' . $username);

	if (!$res) {
		Logger::log("ldapauth: $ldap_userattr=$username,$ldap_searchdn not found");
		return false;
	}

	$id = @ldap_first_entry($connect, $res);

	if (!$id) {
		return false;
	}

	$dn = @ldap_get_dn($connect, $id);

	if (!@ldap_bind($connect, $dn, $password)) {
		return false;
	}

	$emailarray = [];
	$namearray = [];
	if ($ldap_autocreateaccount == "true") {
		if (!strlen($ldap_autocreateaccount_emailattribute)) {
			$ldap_autocreateaccount_emailattribute = "mail";
		}
		if (!strlen($ldap_autocreateaccount_nameattribute)) {
			$ldap_autocreateaccount_nameattribute = "givenName";
		}
		$emailarray = @ldap_get_values($connect, $id, $ldap_autocreateaccount_emailattribute);
		$namearray = @ldap_get_values($connect, $id, $ldap_autocreateaccount_nameattribute);
	}

	if (!strlen($ldap_group)) {
		ldap_autocreateaccount($ldap_autocreateaccount, $username, $password, $emailarray[0], $namearray[0]);
		return true;
	}

	$r = @ldap_compare($connect, $ldap_group, 'member', $dn);
	if ($r === -1) {
		$err = @ldap_error($connect);
		$eno = @ldap_errno($connect);
		@ldap_close($connect);

		if ($eno === 32) {
			Logger::log("ldapauth: access control group Does Not Exist");
			return false;
		} elseif ($eno === 16) {
			Logger::log('ldapauth: membership attribute does not exist in access control group');
			return false;
		} else {
			Logger::log('ldapauth: error: ' . $err);
			return false;
		}
	} elseif ($r === false) {
		@ldap_close($connect);
		return false;
	}

	ldap_autocreateaccount($ldap_autocreateaccount, $username, $password, $emailarray[0], $namearray[0]);
	return true;
}

function ldap_autocreateaccount($ldap_autocreateaccount, $username, $password, $email, $name)
{
	if ($ldap_autocreateaccount == "true") {
		$results = get_existing_account($username);
		if (empty($results)) {
			if (strlen($email) > 0 && strlen($name) > 0) {
				$arr = ['username' => $name, 'nickname' => $username, 'email' => $email, 'password' => $password, 'verified' => 1];

				try {
					User::create($arr);
					Logger::log("ldapauth: account " . $username . " created");
				} catch (Exception $ex) {
					Logger::log("ldapauth: account " . $username . " was not created ! : " . $ex->getMessage());
				}
			} else {
				Logger::log("ldapauth: unable to create account, no email or nickname found");
			}
		}
	}
}

function get_existing_account($username)
{
	return q("SELECT * FROM `user` WHERE `nickname` = '%s' AND `blocked` = 0 AND `verified` = 1 LIMIT 1", $username);
}
