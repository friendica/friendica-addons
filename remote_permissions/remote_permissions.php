<?php
/**
 * Name: Remote Permissions
 * Description: Allow the recipients of private posts to see who else can see the post by clicking the lock icon
 * Version: 1.0
 * Author: Zach <https://f.shmuz.in/profile/techcity>
 *
 */
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\PConfig;

function remote_permissions_install() {
	Addon::registerHook('lockview_content', 'addon/remote_permissions/remote_permissions.php', 'remote_permissions_content');
	Addon::registerHook('plugin_settings', 'addon/remote_permissions/remote_permissions.php', 'remote_permissions_settings');
	Addon::registerHook('plugin_settings_post', 'addon/remote_permissions/remote_permissions.php', 'remote_permissions_settings_post');
}

function remote_permissions_uninstall() {
	Addon::unregisterHook('lockview_content', 'addon/remote_permissions/remote_permissions.php', 'remote_permissions_content');
	Addon::unregisterHook('plugin_settings', 'addon/remote_permissions/remote_permissions.php', 'remote_permissions_settings');
	Addon::unregisterHook('plugin_settings_post', 'addon/remote_permissions/remote_permissions.php', 'remote_permissions_settings_post');
}

function remote_permissions_settings(&$a,&$o) {

	if(! local_user())
		return;

	$global = Config::get("remote_perms", "global");
	if($global == 1)
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/remote_permissions/settings.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$remote_perms = PConfig::get(local_user(),'remote_perms','show');

	/* Add some HTML to the existing form */

//	$t = file_get_contents("addon/remote_permissions/settings.tpl" );
	$t = get_markup_template("settings.tpl", "addon/remote_permissions/" );
	$o .= replace_macros($t, [
		'$remote_perms_title' => t('Remote Permissions Settings'),
		'$remote_perms_label' => t('Allow recipients of your private posts to see the other recipients of the posts'),
		'$checked' => (($remote_perms == 1) ? 'checked="checked"' : ''),
		'$submit' => t('Save Settings')
	]);

}

function remote_permissions_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'remote-perms-submit')))
		return;

	PConfig::set(local_user(),'remote_perms','show',intval($_POST['remote-perms']));
	info( t('Remote Permissions settings updated.') . EOL);
}

function remote_permissions_content($a, $item_copy) {

	if($item_copy['uid'] != local_user())
		return;

	if(Config::get('remote_perms','global') == 0) {
		// Admin has set Individual choice. We need to find
		// the original poster. First, get the contact's info
		$r = q("SELECT nick, url FROM contact WHERE id = %d LIMIT 1",
		       intval($item_copy['contact-id'])
		);
		if(! $r)
			return;

		// Find out if the contact lives here
		$baseurl = $a->get_baseurl();
		$baseurl = substr($baseurl, strpos($baseurl, '://') + 3);
		if(strpos($r[0]['url'], $baseurl) === false)
			return;

		// The contact lives here. Get his/her user info
		$nick = $r[0]['nick'];
		$r = q("SELECT uid FROM user WHERE nickname = '%s' LIMIT 1",
		       dbesc($nick)
		);
		if(! $r)
			return;

		if(PConfig::get($r[0]['uid'],'remote_perms','show') == 0)
			return;
	}

	if(($item_copy['private'] == 1) && (! strlen($item_copy['allow_cid'])) && (! strlen($item_copy['allow_gid']))
		&& (! strlen($item_copy['deny_cid'])) && (! strlen($item_copy['deny_gid']))) {

		$allow_names = [];

		// Check for the original post here -- that's the only way
		// to definitely get all of the recipients

		if($item_copy['uri'] === $item_copy['parent-uri']) {
			// Lockview for a top-level post
			$r = q("SELECT allow_cid, allow_gid, deny_cid, deny_gid FROM item WHERE uri = '%s' AND type = 'wall' LIMIT 1",
				   dbesc($item_copy['uri'])
			);
		}
		else {
			// Lockview for a comment
			$r = q("SELECT allow_cid, allow_gid, deny_cid, deny_gid FROM item WHERE uri = '%s'
			        AND parent = ( SELECT id FROM item WHERE uri = '%s' AND type = 'wall' ) LIMIT 1",
				   dbesc($item_copy['uri']),
				   dbesc($item_copy['parent-uri'])
			);
		}
		if($r) {

			$item = $r[0];

			$allowed_users = expand_acl($item['allow_cid']);
			$allowed_groups = expand_acl($item['allow_gid']);
			$deny_users = expand_acl($item['deny_cid']);
			$deny_groups = expand_acl($item['deny_gid']);

			$o = t('Visible to:') . '<br />';
			$allow = [];
			$deny = [];

			if(count($allowed_groups)) {
				$r = q("SELECT DISTINCT `contact-id` FROM group_member WHERE gid IN ( %s )",
					dbesc(implode(', ', $allowed_groups))
				);
				foreach($r as $rr)
					$allow[] = $rr['contact-id'];
			}
			$allow = array_unique($allow + $allowed_users);

			if(count($deny_groups)) {
				$r = q("SELECT DISTINCT `contact-id` FROM group_member WHERE gid IN ( %s )",
					dbesc(implode(', ', $deny_groups))
				);
				foreach($r as $rr)
					$deny[] = $rr['contact-id'];
			}
			$deny = $deny + $deny_users;

			if($allow)
			{
				$r = q("SELECT name FROM contact WHERE id IN ( %s )",
					   dbesc(implode(', ', array_diff($allow, $deny)))
				);
				foreach($r as $rr)
					$allow_names[] = $rr['name'];
			}
		}
		else {
			// We don't have the original post. Let's try for the next best thing:
			// checking who else has the post on our own server. Note that comments
			// that were sent to Diaspora and were relayed to others on our server
			// will have different URIs than the original. We can match the GUID for
			// those
			$r = q("SELECT `uid` FROM item WHERE uri = '%s' OR guid = '%s'",
				   dbesc($item_copy['uri']),
			       dbesc($item_copy['guid'])
			);
			if(! $r)
				return;

			$allow = [];
			foreach($r as $rr)
				$allow[] = $rr['uid'];

			$r = q("SELECT username FROM user WHERE uid IN ( %s )",
				dbesc(implode(', ', $allow))
			);
			if(! $r)
				return;

			$o = t('Visible to') . ' (' . t('may only be a partial list') . '):<br />';

			foreach($r as $rr)
				$allow_names[] = $rr['username'];
		}

		// Sort the names alphabetically, case-insensitive
		natcasesort($allow_names);
		echo $o . implode(', ', $allow_names);
		killme();
	}

	return;
}

function remote_permissions_plugin_admin(&$a, &$o){
	$t = get_markup_template( "admin.tpl", "addon/remote_permissions/" );
	$o = replace_macros($t, [
		'$submit' => t('Save Settings'),
		'$global' => ['remotepermschoice', t('Global'), 1, t('The posts of every user on this server show the post recipients'),  Config::get('remote_perms', 'global') == 1],
		'$individual' => ['remotepermschoice', t('Individual'), 2, t('Each user chooses whether his/her posts show the post recipients'),  Config::get('remote_perms', 'global') == 0]
	]);
}

function remote_permissions_plugin_admin_post(&$a){
	$choice	=	((x($_POST,'remotepermschoice'))		? notags(trim($_POST['remotepermschoice']))	: '');
	Config::set('remote_perms','global',($choice == 1 ? 1 : 0));
	info( t('Settings updated.'). EOL );
}

