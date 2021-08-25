<?php

/**
 * Name: jappixmini
 * Description: Provides a Facebook-like chat using Jappix Mini
 * Version: 1.0.1
 * Author: leberwurscht <leberwurscht@hoegners.de>
 *
 */
//
// Copyright 2012 "Leberwurscht" <leberwurscht@hoegners.de>
//
// This file is dual-licensed under the MIT license (see MIT.txt) and the AGPL license (see jappix/COPYING).
//

/*

  Problem:
 * jabber password should not be stored on server
 * jabber password should not be sent between server and browser as soon as the user is logged in
 * jabber password should not be reconstructible from communication between server and browser as soon as the user is logged in

  Solution:
  Only store an encrypted version of the jabber password on the server. The encryption key is only available to the browser
  and not to the server (at least as soon as the user is logged in). It can be stored using the jappix setDB function.

  This encryption key could be the friendica password, but then this password would be stored in the browser in cleartext.
  It is better to use a hash of the password.
  The server should not be able to reconstruct the password, so we can't take the same hash the server stores. But we can
  use hash("some_prefix"+password). This will however not work with OpenID logins, for this type of login the password must
  be queried manually.

  Problem:
  How to discover the jabber addresses of the friendica contacts?

  Solution:
  Each Friendica site with this addon provides a /jappixmini/ module page. We go through our contacts and retrieve
  this information every week using a cron hook.

  Problem:
  We do not want to make the jabber address public.

  Solution:
  When two friendica users connect using DFRN, the relation gets a DFRN ID and a keypair is generated.
  Using this keypair, we can provide the jabber address only to contacts:

  Alice:
  signed_address = openssl_*_encrypt(alice_jabber_address)
  send signed_address to Bob, who does
  trusted_address = openssl_*_decrypt(signed_address)
  save trusted_address
  encrypted_address = openssl_*_encrypt(bob_jabber_address)
  reply with encrypted_address to Alice, who does
  decrypted_address = openssl_*_decrypt(encrypted_address)
  save decrypted_address

  Interface for this:
  GET /jappixmini/?role=%s&signed_address=%s&dfrn_id=%s

  Response:
  json({"status":"ok", "encrypted_address":"%s"})

 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Protocol;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\User;

function jappixmini_install()
{
	Hook::register('addon_settings', 'addon/jappixmini/jappixmini.php', 'jappixmini_settings');
	Hook::register('addon_settings_post', 'addon/jappixmini/jappixmini.php', 'jappixmini_settings_post');

	Hook::register('page_end', 'addon/jappixmini/jappixmini.php', 'jappixmini_script');
	Hook::register('authenticate', 'addon/jappixmini/jappixmini.php', 'jappixmini_login');

	Hook::register('cron', 'addon/jappixmini/jappixmini.php', 'jappixmini_cron');

	// Jappix source download as required by AGPL
	Hook::register('about_hook', 'addon/jappixmini/jappixmini.php', 'jappixmini_download_source');

	// set standard configuration
	$info_text = DI::config()->get("jappixmini", "infotext");
	if (!$info_text)
		DI::config()->set("jappixmini", "infotext", "To get the chat working, you need to know a BOSH host which works with your Jabber account. " .
			"An example of a BOSH server that works for all accounts is https://bind.jappix.com/, but keep " .
			"in mind that the BOSH server can read along all chat messages. If you know that your Jabber " .
			"server also provides an own BOSH server, it is much better to use this one!"
		);

	$bosh_proxy = DI::config()->get("jappixmini", "bosh_proxy");
	if ($bosh_proxy === "") {
		DI::config()->set("jappixmini", "bosh_proxy", "1");
	}

	// set addon version so that safe updates are possible later
	$addon_version = DI::config()->get("jappixmini", "version");
	if ($addon_version === "") {
		DI::config()->set("jappixmini", "version", "1");
	}
}

function jappixmini_addon_admin(App $a, &$o)
{
	// display instructions and warnings on addon settings page for admin
	if (!file_exists("addon/jappixmini.tgz")) {
		$o .= '<p><strong style="color:#fff;background-color:#f00">The source archive jappixmini.tgz does not exist. This is probably a violation of the Jappix License (AGPL).</strong></p>';
	}

	// warn if cron job has not yet been executed
	$cron_run = DI::config()->get("jappixmini", "last_cron_execution");
	if (!$cron_run) {
		$o .= "<p><strong>Warning: The cron job has not yet been executed. If this message is still there after some time (usually 10 minutes), this means that autosubscribe and autoaccept will not work.</strong></p>";
	}

	// bosh proxy
	$bosh_proxy = intval(DI::config()->get("jappixmini", "bosh_proxy"));
	$bosh_proxy = intval($bosh_proxy) ? ' checked="checked"' : '';
	$o .= '<label for="jappixmini-proxy">Activate BOSH proxy</label>';
	$o .= ' <input id="jappixmini-proxy" type="checkbox" name="jappixmini-proxy" value="1"' . $bosh_proxy . ' /><br />';

	// bosh address
	$bosh_address = DI::config()->get("jappixmini", "bosh_address");
	$o .= '<p><label for="jappixmini-address">Adress of the default BOSH proxy. If enabled it overrides the user settings:</label><br />';
	$o .= '<input id="jappixmini-address" type="text" name="jappixmini-address" value="' . $bosh_address . '" /></p>';

	// default server address
	$default_server = DI::config()->get("jappixmini", "default_server");
	$o .= '<p><label for="jappixmini-server">Adress of the default jabber server:</label><br />';
	$o .= '<input id="jappixmini-server" type="text" name="jappixmini-server" value="' . $default_server . '" /></p>';

	// default user name to friendica nickname
	$default_user = intval(DI::config()->get("jappixmini", "default_user"));
	$default_user = intval($default_user) ? ' checked="checked"' : '';
	$o .= '<label for="jappixmini-user">Set the default username to the nickname:</label>';
	$o .= ' <input id="jappixmini-user" type="checkbox" name="jappixmini-defaultuser" value="1"' . $default_user . ' /><br />';

	// info text field
	$info_text = DI::config()->get("jappixmini", "infotext");
	$o .= '<p><label for="jappixmini-infotext">Info text to help users with configuration (important if you want to provide your own BOSH host!):</label><br />';
	$o .= '<textarea id="jappixmini-infotext" name="jappixmini-infotext" rows="5" cols="50">' . htmlentities($info_text) . '</textarea></p>';

	// submit button
	$o .= '<input type="submit" name="jappixmini-admin-settings" value="OK" />';
}

function jappixmini_addon_admin_post(App $a)
{
	// set info text
	$submit = $_REQUEST['jappixmini-admin-settings'];
	if ($submit) {
		$info_text = $_REQUEST['jappixmini-infotext'];
		$bosh_proxy = intval($_REQUEST['jappixmini-proxy']);
		$default_user = intval($_REQUEST['jappixmini-defaultuser']);
		$bosh_address = $_REQUEST['jappixmini-address'];
		$default_server = $_REQUEST['jappixmini-server'];
		DI::config()->set("jappixmini", "infotext", $info_text);
		DI::config()->set("jappixmini", "bosh_proxy", $bosh_proxy);
		DI::config()->set("jappixmini", "bosh_address", $bosh_address);
		DI::config()->set("jappixmini", "default_server", $default_server);
		DI::config()->set("jappixmini", "default_user", $default_user);
	}
}

function jappixmini_module()
{

}

function jappixmini_init()
{
	// module page where other Friendica sites can submit Jabber addresses to and also can query Jabber addresses
	// of local users
	$dfrn_id = $_REQUEST["dfrn_id"];
	if (!$dfrn_id) {
		exit();
	}

	$role = $_REQUEST["role"];
	if ($role == "pub") {
		$r = q("SELECT * FROM `contact` WHERE LENGTH(`pubkey`) AND `dfrn-id`='%s' LIMIT 1", DBA::escape($dfrn_id));
		if (!count($r)) {
			exit();
		}

		$encrypt_func = 'openssl_public_encrypt';
		$decrypt_func = 'openssl_public_decrypt';
		$key = $r[0]["pubkey"];
	} else if ($role == "prv") {
		$r = q("SELECT * FROM `contact` WHERE LENGTH(`prvkey`) AND `issued-id`='%s' LIMIT 1", DBA::escape($dfrn_id));
		if (!count($r)) {
			exit();
		}

		$encrypt_func = 'openssl_private_encrypt';
		$decrypt_func = 'openssl_private_decrypt';
		$key = $r[0]["prvkey"];
	} else {
		exit();
	}

	$uid = $r[0]["uid"];

	// save the Jabber address we received
	try {
		$signed_address_hex = $_REQUEST["signed_address"];
		$signed_address = hex2bin($signed_address_hex);

		$trusted_address = "";
		$decrypt_func($signed_address, $trusted_address, $key);

		$now = intval(time());
		DI::pConfig()->set($uid, "jappixmini", "id:$dfrn_id", "$now:$trusted_address");
	} catch (Exception $e) {

	}

	// do not return an address if user deactivated addon
	$activated = DI::pConfig()->get($uid, 'jappixmini', 'activate');
	if (!$activated) {
		exit();
	}

	// return the requested Jabber address
	try {
		$username = DI::pConfig()->get($uid, 'jappixmini', 'username');
		$server = DI::pConfig()->get($uid, 'jappixmini', 'server');
		$address = "$username@$server";

		$encrypted_address = "";
		$encrypt_func($address, $encrypted_address, $key);

		$encrypted_address_hex = bin2hex($encrypted_address);

		$answer = [
			"status" => "ok",
			"encrypted_address" => $encrypted_address_hex
		];

		$answer_json = json_encode($answer);
		echo $answer_json;
		exit();
	} catch (Exception $e) {
		exit();
	}
}

function jappixmini_settings(App $a, &$s)
{
	// addon settings for a user
	$activate = DI::pConfig()->get(local_user(), 'jappixmini', 'activate');
	$activate = intval($activate) ? ' checked="checked"' : '';
	$dontinsertchat = DI::pConfig()->get(local_user(), 'jappixmini', 'dontinsertchat');
	$insertchat = !(intval($dontinsertchat) ? ' checked="checked"' : '');

	$defaultbosh = DI::config()->get("jappixmini", "bosh_address");

	if ($defaultbosh != "") {
		DI::pConfig()->set(local_user(), 'jappixmini', 'bosh', $defaultbosh);
	}

	$username = DI::pConfig()->get(local_user(), 'jappixmini', 'username');
	$username = htmlentities($username);
	$server = DI::pConfig()->get(local_user(), 'jappixmini', 'server');
	$server = htmlentities($server);
	$bosh = DI::pConfig()->get(local_user(), 'jappixmini', 'bosh');
	$bosh = htmlentities($bosh);
	$password = DI::pConfig()->get(local_user(), 'jappixmini', 'password');
	$autosubscribe = DI::pConfig()->get(local_user(), 'jappixmini', 'autosubscribe');
	$autosubscribe = intval($autosubscribe) ? ' checked="checked"' : '';
	$autoapprove = DI::pConfig()->get(local_user(), 'jappixmini', 'autoapprove');
	$autoapprove = intval($autoapprove) ? ' checked="checked"' : '';
	$encrypt = intval(DI::pConfig()->get(local_user(), 'jappixmini', 'encrypt'));
	$encrypt_checked = $encrypt ? ' checked="checked"' : '';
	$encrypt_disabled = $encrypt ? '' : ' disabled="disabled"';

	if ($server == "") {
		$server = DI::config()->get("jappixmini", "default_server");
	}

	if (($username == "") && DI::config()->get("jappixmini", "default_user")) {
		$username = $a->getLoggedInUserNickname();
	}

	$info_text = DI::config()->get("jappixmini", "infotext");
	$info_text = htmlentities($info_text);
	$info_text = str_replace("\n", "<br />", $info_text);

	// count contacts
	$r = q("SELECT COUNT(1) as `cnt` FROM `pconfig` WHERE `uid`=%d AND `cat`='jappixmini' AND `k` LIKE 'id:%%'", local_user());
	if (count($r)) {
		$contact_cnt = $r[0]["cnt"];
	} else {
		$contact_cnt = 0;
	}

	// count jabber addresses
	$r = q("SELECT COUNT(1) as `cnt` FROM `pconfig` WHERE `uid`=%d AND `cat`='jappixmini' AND `k` LIKE 'id:%%' AND `v` LIKE '%%@%%'", local_user());
	if (count($r)) {
		$address_cnt = $r[0]["cnt"];
	} else {
		$address_cnt = 0;
	}

	if (!$activate) {
		// load scripts if not yet activated so that password can be saved
		DI::page()['htmlhead'] .= '<script type="text/javascript" src="' . DI::baseUrl()->get() . '/addon/jappixmini/jappix/php/get.php?t=js&amp;g=mini.xml"></script>' . "\r\n";
		DI::page()['htmlhead'] .= '<script type="text/javascript" src="' . DI::baseUrl()->get() . '/addon/jappixmini/jappix/php/get.php?t=js&amp;f=presence.js~caps.js~name.js~roster.js"></script>' . "\r\n";

		DI::page()['htmlhead'] .= '<script type="text/javascript" src="' . DI::baseUrl()->get() . '/addon/jappixmini/lib.js"></script>' . "\r\n";
	}

	$s .= '<span id="settings_jappixmini_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_jappixmini_expanded\'); openClose(\'settings_jappixmini_inflated\');">';
	$s .= '<h3>' . DI::l10n()->t('Jappix Mini') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_jappixmini_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_jappixmini_expanded\'); openClose(\'settings_jappixmini_inflated\');">';
	$s .= '<h3>' . DI::l10n()->t('Jappix Mini') . '</h3>';
	$s .= '</span>';

	$s .= '<label for="jappixmini-activate">' . DI::l10n()->t('Activate addon') . '</label>';
	$s .= ' <input id="jappixmini-activate" type="checkbox" name="jappixmini-activate" value="1"' . $activate . ' />';
	$s .= '<br />';
	$s .= '<label for"jappixmini-dont-insertchat">' . DI::l10n()->t('Do <em>not</em> insert the Jappixmini Chat-Widget into the webinterface') . '</label>';
	$s .= '<input id="jappixmini-dont-insertchat" type="checkbox" name="jappixmini-dont-insertchat" value="1"' . $insertchat . ' />';
	$s .= '<br />';
	$s .= '<label for="jappixmini-username">' . DI::l10n()->t('Jabber username') . '</label>';
	$s .= ' <input id="jappixmini-username" type="text" name="jappixmini-username" value="' . $username . '" />';
	$s .= '<br />';
	$s .= '<label for="jappixmini-server">' . DI::l10n()->t('Jabber server') . '</label>';
	$s .= ' <input id="jappixmini-server" type="text" name="jappixmini-server" value="' . $server . '" />';
	$s .= '<br />';

	if ($defaultbosh == "") {
		$s .= '<label for="jappixmini-bosh">' . DI::l10n()->t('Jabber BOSH host') . '</label>';
		$s .= ' <input id="jappixmini-bosh" type="text" name="jappixmini-bosh" value="' . $bosh . '" />';
		$s .= '<br />';
	}

	$s .= '<label for="jappixmini-password">' . DI::l10n()->t('Jabber password') . '</label>';
	$s .= ' <input type="hidden" id="jappixmini-password" name="jappixmini-encrypted-password" value="' . $password . '" />';
	$s .= ' <input id="jappixmini-clear-password" type="password" value="" onchange="jappixmini_set_password();" />';
	$s .= '<br />';
	$onchange = "document.getElementById('jappixmini-friendica-password').disabled = !this.checked;jappixmini_set_password();";
	$s .= '<label for="jappixmini-encrypt">' . DI::l10n()->t('Encrypt Jabber password with Friendica password (recommended)') . '</label>';
	$s .= ' <input id="jappixmini-encrypt" type="checkbox" name="jappixmini-encrypt" onchange="' . $onchange . '" value="1"' . $encrypt_checked . ' />';
	$s .= '<br />';
	$s .= '<label for="jappixmini-friendica-password">' . DI::l10n()->t('Friendica password') . '</label>';
	$s .= ' <input id="jappixmini-friendica-password" name="jappixmini-friendica-password" type="password" onchange="jappixmini_set_password();" value=""' . $encrypt_disabled . ' />';
	$s .= '<br />';
	$s .= '<label for="jappixmini-autoapprove">' . DI::l10n()->t('Approve subscription requests from Friendica contacts automatically') . '</label>';
	$s .= ' <input id="jappixmini-autoapprove" type="checkbox" name="jappixmini-autoapprove" value="1"' . $autoapprove . ' />';
	$s .= '<br />';
	$s .= '<label for="jappixmini-autosubscribe">' . DI::l10n()->t('Subscribe to Friendica contacts automatically') . '</label>';
	$s .= ' <input id="jappixmini-autosubscribe" type="checkbox" name="jappixmini-autosubscribe" value="1"' . $autosubscribe . ' />';
	$s .= '<br />';
	$s .= '<label for="jappixmini-purge">' . DI::l10n()->t('Purge internal list of jabber addresses of contacts') . '</label>';
	$s .= ' <input id="jappixmini-purge" type="checkbox" name="jappixmini-purge" value="1" />';
	$s .= '<br />';
	if ($info_text) {
		$s .= '<br />Configuration help:<p style="margin-left:2em;">' . $info_text . '</p>';
	}
	$s .= '<br />Status:<p style="margin-left:2em;">Addon knows ' . $address_cnt . ' Jabber addresses of ' . $contact_cnt . ' Friendica contacts (takes some time, usually 10 minutes, to update).</p>';
	$s .= '<input type="submit" name="jappixmini-submit" value="' . DI::l10n()->t('Save Settings') . '" />';
	$s .= ' <input type="button" value="' . DI::l10n()->t('Add contact') . '" onclick="jappixmini_addon_subscribe();" />';

	$s .= '</div>';

	DI::page()['htmlhead'] .= "<script type=\"text/javascript\">
        function jappixmini_set_password() {
            encrypt = document.getElementById('jappixmini-encrypt').checked;
            password = document.getElementById('jappixmini-password');
            clear_password = document.getElementById('jappixmini-clear-password');
            if (encrypt) {
                friendica_password = document.getElementById('jappixmini-friendica-password');

                if (friendica_password) {
                    jappixmini_addon_set_client_secret(friendica_password.value);
                    jappixmini_addon_encrypt_password(clear_password.value, function(encrypted_password){
                        password.value = encrypted_password;
                    });
                }
            }
            else {
                password.value = clear_password.value;
            }
        }

        jQuery(document).ready(function() {
            encrypt = document.getElementById('jappixmini-encrypt').checked;
            password = document.getElementById('jappixmini-password');
            clear_password = document.getElementById('jappixmini-clear-password');
            if (encrypt) {
                jappixmini_addon_decrypt_password(password.value, function(decrypted_password){
                    clear_password.value = decrypted_password;
                });
            }
            else {
                clear_password.value = password.value;
            }
        });
    </script>";
}

function jappixmini_settings_post(App $a, &$b)
{
	// save addon settings for a user
	if (!local_user()) {
		return;
	}
	$uid = local_user();

	if ($_POST['jappixmini-submit']) {
		$encrypt = intval($b['jappixmini-encrypt']);
		if ($encrypt) {
			// check that Jabber password was encrypted with correct Friendica password
			$friendica_password = trim($b['jappixmini-friendica-password']);
			if (!User::authenticate((int) $uid, $friendica_password)) {
				notice("Wrong friendica password!");
				return;
			}
		}

		$purge = intval($b['jappixmini-purge']);

		$username = trim($b['jappixmini-username']);
		$old_username = DI::pConfig()->get($uid, 'jappixmini', 'username');
		if ($username != $old_username) {
			$purge = 1;
		}

		$server = trim($b['jappixmini-server']);
		$old_server = DI::pConfig()->get($uid, 'jappixmini', 'server');
		if ($server != $old_server) {
			$purge = 1;
		}

		DI::pConfig()->set($uid, 'jappixmini', 'username'      , $username);
		DI::pConfig()->set($uid, 'jappixmini', 'server'        , $server);
		DI::pConfig()->set($uid, 'jappixmini', 'bosh'          , trim($b['jappixmini-bosh']));
		DI::pConfig()->set($uid, 'jappixmini', 'password'      , trim($b['jappixmini-encrypted-password']));
		DI::pConfig()->set($uid, 'jappixmini', 'autosubscribe' , intval($b['jappixmini-autosubscribe']));
		DI::pConfig()->set($uid, 'jappixmini', 'autoapprove'   , intval($b['jappixmini-autoapprove']));
		DI::pConfig()->set($uid, 'jappixmini', 'activate'      , intval($b['jappixmini-activate']));
		DI::pConfig()->set($uid, 'jappixmini', 'dontinsertchat', intval($b['jappixmini-dont-insertchat']));
		DI::pConfig()->set($uid, 'jappixmini', 'encrypt'       , $encrypt);

		if ($purge) {
			q("DELETE FROM `pconfig` WHERE `uid`=$uid AND `cat`='jappixmini' AND `k` LIKE 'id:%%'");
		}
	}
}

function jappixmini_script(App $a)
{
	// adds the script to the page header which starts Jappix Mini
	if (!local_user()) {
		return;
	}

	if (($_GET['mode'] ?? '') == 'minimal') {
		return;
	}

	$activate = DI::pConfig()->get(local_user(), 'jappixmini', 'activate');
	$dontinsertchat = DI::pConfig()->get(local_user(), 'jappixmini', 'dontinsertchat');
	if (!$activate || $dontinsertchat) {
		return;
	}

	DI::page()['htmlhead'] .= '<script type="text/javascript" src="' . DI::baseUrl()->get() . '/addon/jappixmini/jappix/php/get.php?t=js&amp;g=mini.xml"></script>' . "\r\n";
	DI::page()['htmlhead'] .= '<script type="text/javascript" src="' . DI::baseUrl()->get() . '/addon/jappixmini/jappix/php/get.php?t=js&amp;f=presence.js~caps.js~name.js~roster.js"></script>' . "\r\n";

	DI::page()['htmlhead'] .= '<script type="text/javascript" src="' . DI::baseUrl()->get() . '/addon/jappixmini/lib.js"></script>' . "\r\n";

	$username = DI::pConfig()->get(local_user(), 'jappixmini', 'username');
	$username = str_replace("'", "\\'", $username);
	$server = DI::pConfig()->get(local_user(), 'jappixmini', 'server');
	$server = str_replace("'", "\\'", $server);
	$bosh = DI::pConfig()->get(local_user(), 'jappixmini', 'bosh');
	$bosh = str_replace("'", "\\'", $bosh);
	$encrypt = DI::pConfig()->get(local_user(), 'jappixmini', 'encrypt');
	$encrypt = intval($encrypt);
	$password = DI::pConfig()->get(local_user(), 'jappixmini', 'password');
	$password = str_replace("'", "\\'", $password);

	$autoapprove = DI::pConfig()->get(local_user(), 'jappixmini', 'autoapprove');
	$autoapprove = intval($autoapprove);
	$autosubscribe = DI::pConfig()->get(local_user(), 'jappixmini', 'autosubscribe');
	$autosubscribe = intval($autosubscribe);

	// set proxy if necessary
	$use_proxy = DI::config()->get('jappixmini', 'bosh_proxy');
	if ($use_proxy) {
		$proxy = DI::baseUrl()->get() . '/addon/jappixmini/proxy.php';
	} else {
		$proxy = "";
	}

	// get a list of jabber accounts of the contacts
	$contacts = [];
	$uid = local_user();
	$rows = q("SELECT * FROM `pconfig` WHERE `uid`=$uid AND `cat`='jappixmini' AND `k` LIKE 'id:%%'");
	foreach ($rows as $row) {
		$key = $row['k'];
		$pos = strpos($key, ":");
		$dfrn_id = substr($key, $pos + 1);
		$r = q("SELECT `name` FROM `contact` WHERE `uid`=$uid AND (`dfrn-id`='%s' OR `issued-id`='%s')", DBA::escape($dfrn_id), DBA::escape($dfrn_id));
		if (count($r))
			$name = $r[0]["name"];

		$value = $row['v'];
		$pos = strpos($value, ":");
		$address = substr($value, $pos + 1);
		if (!$address) {
			continue;
		}
		if (!$name) {
			$name = $address;
		}

		$contacts[$address] = $name;
	}
	$contacts_json = json_encode($contacts);
	$contacts_hash = sha1($contacts_json);

	// get nickname
	$r = q("SELECT `username` FROM `user` WHERE `uid`=$uid");
	$nickname = json_encode($r[0]["username"]);
	$groupchats = DI::config()->get('jappixmini', 'groupchats');
	//if $groupchats has no value jappix_addon_start will produce a syntax error
	if (empty($groupchats)) {
		$groupchats = "{}";
	}

	// add javascript to start Jappix Mini
	DI::page()['htmlhead'] .= "<script type=\"text/javascript\">
        jQuery(document).ready(function() {
           jappixmini_addon_start('$server', '$username', '$proxy', '$bosh', $encrypt, '$password', $nickname, $contacts_json, '$contacts_hash', $autoapprove, $autosubscribe, $groupchats);
        });
    </script>";

	return;
}

function jappixmini_login(App $a, &$o)
{
	// create client secret on login to be able to encrypt jabber passwords
	// for setDB and str_sha1, needed by jappixmini_addon_set_client_secret
	DI::page()['htmlhead'] .= '<script type="text/javascript" src="' . DI::baseUrl()->get() . '/addon/jappixmini/jappix/php/get.php?t=js&amp;f=datastore.js~jsjac.js"></script>' . "\r\n";

	// for jappixmini_addon_set_client_secret
	DI::page()['htmlhead'] .= '<script type="text/javascript" src="' . DI::baseUrl()->get() . '/addon/jappixmini/lib.js"></script>' . "\r\n";

	// save hash of password
	$o = str_replace("<form ", "<form onsubmit=\"jappixmini_addon_set_client_secret(this.elements['id_password'].value);return true;\" ", $o);
}

function jappixmini_cron(App $a, $d)
{
	// For autosubscribe/autoapprove, we need to maintain a list of jabber addresses of our contacts.
	DI::config()->set("jappixmini", "last_cron_execution", $d);

	// go through list of users with jabber enabled
	$users = q("SELECT `uid` FROM `pconfig` WHERE `cat`='jappixmini' AND (`k`='autosubscribe' OR `k`='autoapprove') AND `v`='1'");
	Logger::log("jappixmini: Update list of contacts' jabber accounts for " . count($users) . " users.");

	if (!count($users)) {
		return;
	}

	foreach ($users as $row) {
		$uid = $row["uid"];

		// for each user, go through list of contacts
		$contacts = q("SELECT * FROM `contact` WHERE `uid`=%d AND ((LENGTH(`dfrn-id`) AND LENGTH(`pubkey`)) OR (LENGTH(`issued-id`) AND LENGTH(`prvkey`))) AND `network` = '%s'",
			intval($uid), DBA::escape(Protocol::DFRN));
		foreach ($contacts as $contact_row) {
			$request = $contact_row["request"];
			if (!$request) {
				continue;
			}

			$dfrn_id = $contact_row["dfrn-id"];
			if ($dfrn_id) {
				$key = $contact_row["pubkey"];
				$encrypt_func = 'openssl_public_encrypt';
				$decrypt_func = 'openssl_public_decrypt';
				$role = "prv";
			} else {
				$dfrn_id = $contact_row["issued-id"];
				$key = $contact_row["prvkey"];
				$encrypt_func = 'openssl_private_encrypt';
				$decrypt_func = 'openssl_private_decrypt';
				$role = "pub";
			}

			// check if jabber address already present
			$present = DI::pConfig()->get($uid, "jappixmini", "id:" . $dfrn_id);
			$now = intval(time());
			if ($present) {
				// $present has format "timestamp:jabber_address"
				$p = strpos($present, ":");
				$timestamp = intval(substr($present, 0, $p));

				// do not re-retrieve jabber address if last retrieval
				// is not older than a week
				if ($now - $timestamp < 3600 * 24 * 7) {
					continue;
				}
			}

			// construct base retrieval address
			$pos = strpos($request, "/dfrn_request/");
			if ($pos === false) {
				continue;
			}

			$base = substr($request, 0, $pos) . "/jappixmini?role=$role";

			// construct own address
			$username = DI::pConfig()->get($uid, 'jappixmini', 'username');
			if (!$username) {
				continue;
			}
			$server = DI::pConfig()->get($uid, 'jappixmini', 'server');
			if (!$server) {
				continue;
			}

			$address = $username . "@" . $server;

			// sign address
			$signed_address = "";
			$encrypt_func($address, $signed_address, $key);

			// construct request url
			$signed_address_hex = bin2hex($signed_address);
			$url = $base . "&signed_address=$signed_address_hex&dfrn_id=" . urlencode($dfrn_id);

			try {
				// send request
				$answer_json = DI::httpClient()->fetch($url);

				// parse answer
				$answer = json_decode($answer_json);
				if (empty($answer->status) || ($answer->status != "ok")) {
					throw new Exception();
				}

				$encrypted_address_hex = $answer->encrypted_address;
				if (!$encrypted_address_hex) {
					throw new Exception();
				}

				$encrypted_address = hex2bin($encrypted_address_hex);
				if (!$encrypted_address) {
					throw new Exception();
				}

				// decrypt address
				$decrypted_address = "";
				$decrypt_func($encrypted_address, $decrypted_address, $key);
				if (!$decrypted_address) {
					throw new Exception();
				}
			} catch (Exception $e) {
				$decrypted_address = "";
			}

			// save address
			DI::pConfig()->set($uid, "jappixmini", "id:$dfrn_id", "$now:$decrypted_address");
		}
	}
}

function jappixmini_download_source(App $a, &$b)
{
	// Jappix Mini source download link on About page
	$b .= '<h1>Jappix Mini</h1>';
	$b .= '<p>This site uses the jappixmini addon, which includes Jappix Mini by the <a href="' . DI::baseUrl()->get() . '/addon/jappixmini/jappix/AUTHORS">Jappix authors</a> and is distributed under the terms of the <a href="' . DI::baseUrl()->get() . '/addon/jappixmini/jappix/COPYING">GNU Affero General Public License</a>.</p>';
	$b .= '<p>You can download the <a href="' . DI::baseUrl()->get() . '/addon/jappixmini.tgz">source code of the addon</a>. The rest of Friendica is distributed under compatible licenses and can be retrieved from <a href="https://github.com/friendica/friendica">https://github.com/friendica/friendica</a> and <a href="https://github.com/friendica/friendica-addons">https://github.com/friendica/friendica-addons</a></p>';
}
