<?php


/**
* Name: jappixmini
* Description: Inserts a jabber chat
* Version: 1.0
* Author: leberwurscht
*
*/

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
 use hash("some_prefix"+password). This will however not work with OpenID logins.

Problem:
How to discover the jabber addresses of the friendica contacts?

Solution:
Each Friendica site with this addon provides a /jappixmini/ module page. We go through our contacts and retrieve
this information every week using a cron hook.

Problem:
We do not want to make the jabber address public.

Solution:
When two friendica users connect using DFRN, the relation gets a DFRN ID and a keypair is generated.
Using this keypair, we can provide the jabber address only to contacs:

Case 1: Alice has prvkey, Bob has pubkey.
  Alice encrypts request
  Bob decrypts the request, send jabber address unencrypted
  Alice reads address

Case 2: Alice has prvkey, Bob has pubkey
  Alice send request
  Bob encrypts jabber address
  Alice decrypts jabber address

*/

function jappixmini_install() {
register_hook('plugin_settings', 'addon/jappixmini/jappixmini.php', 'jappixmini_settings');
register_hook('plugin_settings_post', 'addon/jappixmini/jappixmini.php', 'jappixmini_settings_post');

register_hook('page_end', 'addon/jappixmini/jappixmini.php', 'jappixmini_script');
register_hook('authenticate', 'addon/jappixmini/jappixmini.php', 'jappixmini_login');

register_hook('cron', 'addon/jappixmini/jappixmini.php', 'jappixmini_cron');

// Jappix source download as required by AGPL
register_hook('about_hook', 'addon/jappixmini/jappixmini.php', 'jappixmini_download_source');
}


function jappixmini_uninstall() {
unregister_hook('plugin_settings', 'addon/jappixmini/jappixmini.php', 'jappixmini_settings');
unregister_hook('plugin_settings_post', 'addon/jappixmini/jappixmini.php', 'jappixmini_settings_post');

unregister_hook('page_end', 'addon/jappixmini/jappixmini.php', 'jappixmini_script');
unregister_hook('authenticate', 'addon/jappixmini/jappixmini.php', 'jappixmini_login');

unregister_hook('cron', 'addon/jappixmini/jappixmini.php', 'jappixmini_cron');

unregister_hook('about_hook', 'addon/jappixmini/jappixmini.php', 'jappixmini_download_source');
}

function jappixmini_plugin_admin(&$a, &$o) {
	if (!file_exists("addon/jappixmini/jappix")) {
		$o .= '<p><strong>You need to install the Jappix application, adapted for Friendica (see README).</strong></p>';
	}
	else if (!file_exists("addon/jappixmini/jappix.zip")) {
		$o .= '<p><strong style="color:#fff;background-color:#f00">The source archive jappix.zip does not exist. This is probably a violation of the Jappix License (see README).</strong></p>';
	}
}

function jappixmini_plugin_admin_post(&$a) {
}

function jappixmini_module() {}
function jappixmini_init(&$a) {
	// Here, other friendica sites can fetch the jabber address of local users.
	// Because we do not want to publish the addresses publicly, they are encrypted so
	// that only contacts can read it.
	$encrypt_for = $_REQUEST["encrypt_for"];
	if ($encrypt_for) {
		$r = q("SELECT * FROM `contact` WHERE LENGTH(`pubkey`) AND `dfrn-id` = '%s' LIMIT 1",
			dbesc($encrypt_for)
		);
		if (!count($r)) killme();

		// get public key to encrypt address
		$pubkey = $r[0]['pubkey'];

		// get jabber address
		$uid = $r[0]['uid'];
		$username = get_pconfig($uid, 'jappixmini', 'username');
		if (!$username) killme();
		$server = get_pconfig($uid, 'jappixmini', 'server');
		if (!$server) killme();

		$address = $username."@".$server;

		// encrypt address
		$encrypted = "";
		openssl_public_encrypt($address,$encrypted,$pubkey);

		// calculate hex representation of encrypted address
		$hex = bin2hex($encrypted);

		// construct answer
		$answer = Array("status"=>"ok", "encrypted_address"=>$hex);

		// return answer as json
		echo json_encode($answer);
		killme();
	}

	// If we have only a private key, other site sends encrypted request, we answer unencrypted.
	$encrypted_for = $_REQUEST["encrypted_for"];
	if (!$encrypted_for) killme();

	$encrypted_request_hex = $_REQUEST["encrypted_request"];
	if (!$encrypted_request_hex) killme();
	$encrypted_request = hex2bin($encrypted_request_hex);

	$r = q("SELECT * FROM `contact` WHERE LENGTH(`prvkey`) AND `issued-id` = '%s' LIMIT 1",
		dbesc($encrypted_for)
	);
	if (!count($r)) killme();

	// decrypt request, validate it
	$prvkey = $r[0]['prvkey'];
	$decrypted_request = "";
	openssl_private_decrypt($encrypted_request, $decrypted_request, $prvkey);

	if ($decrypted_request!=$encrypted_for) killme();

	// get jabber address
	$uid = $r[0]['uid'];
	$username = get_pconfig($uid, 'jappixmini', 'username');
	if (!$username) killme();
	$server = get_pconfig($uid, 'jappixmini', 'server');
	if (!$server) killme();

	$address = $username."@".$server;

	// construct answer
	$answer = Array("status"=>"ok", "address"=>$address);

	// return answer as json
	echo json_encode($answer);
	killme();
}

function jappixmini_settings(&$a, &$s) {
    $username = get_pconfig(local_user(),'jappixmini','username');
    $username = htmlentities($username);
    $server = get_pconfig(local_user(),'jappixmini','server');
    $server = htmlentities($server);
    $bosh = get_pconfig(local_user(),'jappixmini','bosh');
    $bosh = htmlentities($bosh);
    $encrypted_password = get_pconfig(local_user(),'jappixmini','encrypted-password');
    $autosubscribe = get_pconfig(local_user(),'jappixmini','autosubscribe');
    $autosubscribe = intval($autosubscribe) ? ' checked="checked"' : '';
    $autoapprove = get_pconfig(local_user(),'jappixmini','autoapprove');
    $autoapprove = intval($autoapprove) ? ' checked="checked"' : '';
    $activate = get_pconfig(local_user(),'jappixmini','activate');
    $activate = intval($activate) ? ' checked="checked"' : '';

    $s .= '<div class="settings-block">';
    $s .= '<h3>Jappix Mini addon settings</h3>';
    $s .= '<div>';
    $s .= '<label for="jappixmini-activate">Activate addon</label>';
    $s .= ' <input id="jappixmini-activate" type="checkbox" name="jappixmini-activate" value="1"'.$activate.' />';
    $s .= '<br />';
    $s .= '<label for="jappixmini-username">Jabber username</label>';
    $s .= ' <input id="jappixmini-username" type="text" name="jappixmini-username" value="'.$username.'" />';
    $s .= '<br />';
    $s .= '<label for="jappixmini-server">Jabber server</label>';
    $s .= ' <input id="jappixmini-server" type="text" name="jappixmini-server" value="'.$server.'" />';
    $s .= '<br />';
    $s .= '<label for="jappixmini-bosh">Jabber BOSH host</label>';
    $s .= ' <input id="jappixmini-bosh" type="text" name="jappixmini-bosh" value="'.$bosh.'" />';
    $s .= '<br />';
    $s .= '<label for="jappixmini-password">Jabber password</label>';
    $s .= ' <input type="hidden" id="jappixmini-encrypted-password" name="jappixmini-encrypted-password" value="'.$encrypted_password.'" />';
    $onchange = "document.getElementById('jappixmini-encrypted-password').value = jappixmini_addon_encrypt_password(document.getElementById('jappixmini-password').value);";
    $s .= ' <input id="jappixmini-password" type="password" value="" onchange="'.$onchange.'" />';
    $s .= '<br />';
    $s .= '<label for="jappixmini-autoapprove">Approve subscription requests from Friendica contacts automatically</label>';
    $s .= ' <input id="jappixmini-autoapprove" type="checkbox" name="jappixmini-autoapprove" value="1"'.$autoapprove.' />';
    $s .= '<br />';
    $s .= '<label for="jappixmini-autosubscribe">Subscribe to Friendica contacts automatically</label>';
    $s .= ' <input id="jappixmini-autosubscribe" type="checkbox" name="jappixmini-autosubscribe" value="1"'.$autosubscribe.' />';
    $s .= '<br />';
    $s .= '<label for="jappixmini-purge">Purge list of jabber addresses of contacts</label>';
    $s .= ' <input id="jappixmini-purge" type="checkbox" name="jappixmini-purge" value="1" />';
    $s .= '<br />';
    $s .= '<input type="submit" name="jappixmini-submit" value="' . t('Submit') . '" />';
    $s .= '</div>';

    $a->page['htmlhead'] .= "<script type=\"text/javascript\">
        jQuery(document).ready(function() {
            document.getElementById('jappixmini-password').value = jappixmini_addon_decrypt_password('$encrypted_password');
        });
    </script>";
}

function jappixmini_settings_post(&$a,&$b) {
	if(! local_user()) return;

	if($_POST['jappixmini-submit']) {
		set_pconfig(local_user(),'jappixmini','username',trim($b['jappixmini-username']));
		set_pconfig(local_user(),'jappixmini','server',trim($b['jappixmini-server']));
		set_pconfig(local_user(),'jappixmini','bosh',trim($b['jappixmini-bosh']));
		set_pconfig(local_user(),'jappixmini','encrypted-password',trim($b['jappixmini-encrypted-password']));
		set_pconfig(local_user(),'jappixmini','autosubscribe',intval($b['jappixmini-autosubscribe']));
		set_pconfig(local_user(),'jappixmini','autoapprove',intval($b['jappixmini-autoapprove']));
		set_pconfig(local_user(),'jappixmini','activate',intval($b['jappixmini-activate']));
		info( 'Jappix Mini settings saved.' );

		if (intval($b['jappixmini-purge'])) {
			$uid = local_user();
			q("DELETE FROM `pconfig` WHERE `uid`=$uid AND `cat`='jappixmini' and `k` LIKE 'id%%'");
			info( 'List of addresses purged.' );
		}
	}
}

function jappixmini_script(&$a,&$s) {
    if(! local_user()) return;

    $activate = get_pconfig(local_user(),'jappixmini','activate');
    if (!$activate) return;

    $a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/jappixmini/jappix/php/get.php?t=js&amp;g=mini.xml"></script>'."\r\n";
    $a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/jappixmini/jappix/php/get.php?t=js&amp;f=presence.js"></script>'."\r\n";

    $a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/jappixmini/jappix/php/get.php?t=js&amp;f=caps.js"></script>'."\r\n";
    $a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/jappixmini/jappix/php/get.php?t=js&amp;f=name.js"></script>'."\r\n";

    $a->page['htmlhead'] .= '<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/jappixmini/lib.js"></script>'."\r\n";

    $username = get_pconfig(local_user(),'jappixmini','username');
    $username = str_replace("'", "\\'", $username);
    $server = get_pconfig(local_user(),'jappixmini','server');
    $server = str_replace("'", "\\'", $server);
    $bosh = get_pconfig(local_user(),'jappixmini','bosh');
    $bosh = str_replace("'", "\\'", $bosh);
    $encrypted_password = get_pconfig(local_user(),'jappixmini','encrypted-password');
    $encrypted_password = str_replace("'", "\\'", $encrypted_password);

    $autoapprove = get_pconfig(local_user(),'jappixmini','autoapprove');
    $autoapprove = intval($autoapprove);
    $autosubscribe = get_pconfig(local_user(),'jappixmini','autosubscribe');
    $autosubscribe = intval($autosubscribe);

    // get a list of jabber accounts of the contacts
    $contacts = Array();
    $uid = local_user();
    $rows = q("SELECT `v` FROM `pconfig` WHERE `uid`=$uid AND `cat`='jappixmini' and `k` LIKE 'id%%'");
    foreach ($rows as $row) {
        $value = $row['v'];
        $pos = strpos($value, ":");
        $address = substr($value, $pos+1);
	$contacts[] = $address;
    }
    $contacts_json = json_encode($contacts);

    $a->page['htmlhead'] .= "<script type=\"text/javascript\">
        jQuery(document).ready(function() {
           jappixmini_addon_start('$server', '$username', '$bosh', '$encrypted_password');
           jappixmini_manage_roster($contacts_json, $autoapprove, $autosubscribe);
        });
    </script>";

    return;
}

function jappixmini_login(&$a, &$o) {
    // save hash of password using setDB
    $o = str_replace("<form ", "<form onsubmit=\"jappixmini_addon_set_client_secret(this.elements['id_password'].value);return true;\" ", $o);
}

function jappixmini_cron(&$a, $d) {
	// For autosubscribe/autoapprove, we need to maintain a list of jabber addresses of our contacts.

	// go through list of users with jabber enabled
	$users = q("SELECT `uid` FROM `pconfig` WHERE `cat`='jappixmini' AND (`k`='autosubscribe' OR `k`='autoapprove') AND `v`='1'");

	foreach ($users as $row) {
		$uid = $row["uid"];

		// for each user, go through list of contacts
		$contacts = q("SELECT * FROM `contact` WHERE `uid`=%d AND ((LENGTH(`dfrn-id`) AND LENGTH(`pubkey`)) OR (LENGTH(`issued-id`) AND LENGTH(`prvkey`)))", intval($uid));
		foreach ($contacts as $contact_row) {
			$request = $contact_row["request"];
			if (!$request) continue;

			$dfrn_id = $contact_row["dfrn-id"];
			$pubkey = $contact_row["pubkey"];
			if (!$dfrn_id) {
				$dfrn_id = $contact_row["issued-id"];
				$prvkey = $contact_row["prvkey"];
			}

			// check if jabber address already present
			$present = get_pconfig($uid, "jappixmini", "id:".$dfrn_id);
			$now = intval(time());
			if ($present) {
				// $present has format "timestamp:jabber_address"
				$p = strpos($present, ":");
				$timestamp = intval(substr($present, 0, $p));

				// do not re-retrieve jabber address if last retrieval
				// is not older than a week
				if ($now-$timestamp<3600*24*7) continue;
			}

			// construct base retrieval address
			$pos = strpos($request, "/dfrn_request/");
			if ($pos===false) continue;

			$base = substr($request, 0, $pos)."/jappixmini";

			// retrieve address
			if ($prvkey) {
				$retrieval_address = $base."?encrypt_for=".urlencode($dfrn_id);

				$answer_json = fetch_url($retrieval_address);
				$answer = json_decode($answer_json);
				if ($answer->status != "ok") continue;

				$encrypted_address_hex = $answer->encrypted_address;
				if (!$encrypted_address_hex) continue;
				$encrypted_address = hex2bin($encrypted_address_hex);

				$decrypted_address = "";
				openssl_private_decrypt($encrypted_address, $decrypted_address, $prvkey);
				if (!$decrypted_address) continue;

				$address = $decrypted_address;
			} else if ($pubkey) {
				$encrypted_request = "";
				openssl_public_encrypt($dfrn_id, $encrypted_request, $pubkey);
				if (!$encrypted_request) continue;
				$encrypted_request_hex = bin2hex($encrypted_request);

				$retrieval_address = $base."?encrypted_for=".urlencode($dfrn_id)."&encrypted_request=".urlencode($encrypted_request_hex);

				$answer_json = fetch_url($retrieval_address);
				$answer = json_decode($answer_json);
				if ($answer->status != "ok") continue;

				$address = $answer->address;
				if (!$address) continue;
			}

			// save address
			set_pconfig($uid, "jappixmini", "id:$dfrn_id", "$now:$address");
		}
	}
}

function jappixmini_download_source(&$a,&$b) {
	$b .= '<h1>Jappix Mini</h1>';
	$b .= '<p>This site uses Jappix Mini by the <a href="'.$a->get_baseurl().'/addon/jappixmini/jappix/AUTHORS">Jappix authors</a>, which is distributed under the terms of the <a href="'.$a->get_baseurl().'/addon/jappixmini/jappix/COPYING">GNU Affero General Public License</a>.</p>';
	$b .= '<p>You can download the <a href="'.$a->get_baseurl().'/addon/jappixmini/jappix.zip">source code</a>.</p>';
}
