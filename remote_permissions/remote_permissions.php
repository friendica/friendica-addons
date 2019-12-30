<?php
/**
 * Name: Remote Permissions
 * Description: Allow the recipients of private posts to see who else can see the post by clicking the lock icon
 * Version: 1.0
 * Author: Zach <https://f.shmuz.in/profile/techcity>
 * Status: Unsupported
 */

use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Util\Strings;

function remote_permissions_install() {
	Hook::register('lockview_content', 'addon/remote_permissions/remote_permissions.php', 'remote_permissions_content');
	Hook::register('addon_settings', 'addon/remote_permissions/remote_permissions.php', 'remote_permissions_settings');
	Hook::register('addon_settings_post', 'addon/remote_permissions/remote_permissions.php', 'remote_permissions_settings_post');
}

function remote_permissions_uninstall() {
	Hook::unregister('lockview_content', 'addon/remote_permissions/remote_permissions.php', 'remote_permissions_content');
	Hook::unregister('addon_settings', 'addon/remote_permissions/remote_permissions.php', 'remote_permissions_settings');
	Hook::unregister('addon_settings_post', 'addon/remote_permissions/remote_permissions.php', 'remote_permissions_settings_post');
}

function remote_permissions_settings(&$a,&$o) {

	if(! local_user())
		return;

	$global = Config::get("remote_perms", "global");
	if($global == 1)
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/remote_permissions/settings.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$remote_perms = PConfig::get(local_user(),'remote_perms','show');

	/* Add some HTML to the existing form */

//	$t = file_get_contents("addon/remote_permissions/settings.tpl" );
	$t = Renderer::getMarkupTemplate("settings.tpl", "addon/remote_permissions/" );
	$o .= Renderer::replaceMacros($t, [
		'$remote_perms_title' => L10n::t('Remote Permissions Settings'),
		'$remote_perms_label' => L10n::t('Allow recipients of your private posts to see the other recipients of the posts'),
		'$checked' => (($remote_perms == 1) ? 'checked="checked"' : ''),
		'$submit' => L10n::t('Save Settings')
	]);

}

function remote_permissions_settings_post($a,$post) {
	if(! local_user() || empty($_POST['remote-perms-submit']))
		return;

	PConfig::set(local_user(),'remote_perms','show',intval($_POST['remote-perms']));
	info(L10n::t('Remote Permissions settings updated.') . EOL);
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
		$baseurl = $a->getBaseURL();
		$baseurl = substr($baseurl, strpos($baseurl, '://') + 3);
		if(strpos($r[0]['url'], $baseurl) === false)
			return;

		// The contact lives here. Get his/her user info
		$nick = $r[0]['nick'];
		$r = q("SELECT uid FROM user WHERE nickname = '%s' LIMIT 1",
		       DBA::escape($nick)
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
				   DBA::escape($item_copy['uri'])
			);
		}
		else {
			// Lockview for a comment
			$r = q("SELECT allow_cid, allow_gid, deny_cid, deny_gid FROM item WHERE uri = '%s'
			        AND parent = ( SELECT id FROM item WHERE uri = '%s' AND type = 'wall' ) LIMIT 1",
				   DBA::escape($item_copy['uri']),
				   DBA::escape($item_copy['parent-uri'])
			);
		}
		if($r) {

			$item = $r[0];

			$aclFormatter = DI::aclFormatter();

			$allowed_users = $aclFormatter->expand($item['allow_cid']);
			$allowed_groups = $aclFormatter->expand($item['allow_gid']);
			$deny_users = $aclFormatter->expand($item['deny_cid']);
			$deny_groups = $aclFormatter->expand($item['deny_gid']);

			$o = L10n::t('Visible to:') . '<br />';
			$allow = [];
			$deny = [];

			if(count($allowed_groups)) {
				$r = q("SELECT DISTINCT `contact-id` FROM group_member WHERE gid IN ( %s )",
					DBA::escape(implode(', ', $allowed_groups))
				);
				foreach($r as $rr)
					$allow[] = $rr['contact-id'];
			}
			$allow = array_unique($allow + $allowed_users);

			if(count($deny_groups)) {
				$r = q("SELECT DISTINCT `contact-id` FROM group_member WHERE gid IN ( %s )",
					DBA::escape(implode(', ', $deny_groups))
				);
				foreach($r as $rr)
					$deny[] = $rr['contact-id'];
			}
			$deny = $deny + $deny_users;

			if($allow)
			{
				$r = q("SELECT name FROM contact WHERE id IN ( %s )",
					   DBA::escape(implode(', ', array_diff($allow, $deny)))
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
				   DBA::escape($item_copy['uri']),
			       DBA::escape($item_copy['guid'])
			);
			if(! $r)
				return;

			$allow = [];
			foreach($r as $rr)
				$allow[] = $rr['uid'];

			$r = q("SELECT username FROM user WHERE uid IN ( %s )",
				DBA::escape(implode(', ', $allow))
			);
			if(! $r)
				return;

			$o = L10n::t('Visible to') . ' (' . L10n::t('may only be a partial list') . '):<br />';

			foreach($r as $rr)
				$allow_names[] = $rr['username'];
		}

		// Sort the names alphabetically, case-insensitive
		natcasesort($allow_names);
		echo $o . implode(', ', $allow_names);
		exit();
	}

	return;
}

function remote_permissions_addon_admin(&$a, &$o){
	$t = Renderer::getMarkupTemplate( "admin.tpl", "addon/remote_permissions/" );
	$o = Renderer::replaceMacros($t, [
		'$submit' => L10n::t('Save Settings'),
		'$global' => ['remotepermschoice', L10n::t('Global'), 1, L10n::t('The posts of every user on this server show the post recipients'),  Config::get('remote_perms', 'global') == 1],
		'$individual' => ['remotepermschoice', L10n::t('Individual'), 2, L10n::t('Each user chooses whether his/her posts show the post recipients'),  Config::get('remote_perms', 'global') == 0]
	]);
}

function remote_permissions_addon_admin_post(&$a){
	$choice	=	(!empty($_POST['remotepermschoice'])		? Strings::escapeTags(trim($_POST['remotepermschoice']))	: '');
	Config::set('remote_perms','global',($choice == 1 ? 1 : 0));
	info(L10n::t('Settings updated.'). EOL);
}
