<?php
/**
 * Name: ForumList
 * Description: Shows list of subscribed community forums on network sidebar
 * Version: 1.1
 * Author: Mike Macgirvin <mike@macgirvin.com>
 * based on pages plugin by
 * Author: Michael Vogel <ike@piratenpartei.de>
 * Status: Unsupported
 *
 */

function forumlist_install() {
	register_hook('network_mod_init', 'addon/forumlist/forumlist.php', 'forumlist_network_mod_init');
	register_hook('plugin_settings', 'addon/forumlist/forumlist.php', 'forumlist_plugin_settings');
	register_hook('plugin_settings_post', 'addon/forumlist/forumlist.php', 'forumlist_plugin_settings_post');
	register_hook('profile_advanced', 'addon/forumlist/forumlist.php', 'forumlist_profile_advanced');

}

function forumlist_uninstall() {
	unregister_hook('network_mod_init', 'addon/forumlist/forumlist.php', 'forumlist_network_mod_init');
	unregister_hook('plugin_settings', 'addon/forumlist/forumlist.php', 'forumlist_plugin_settings');
	unregister_hook('plugin_settings_post', 'addon/forumlist/forumlist.php', 'forumlist_plugin_settings_post');
	unregister_hook('profile_advanced', 'addon/forumlist/forumlist.php', 'forumlist_profile_advanced');

}


function forumlist_getpage($uid,$showhidden = true,$randomise = false, $showprivate = false) {


	$forumlist = array();

	$order = (($showhidden) ? '' : " and hidden = 0 ");
        $order .= (($randomise) ? ' order by rand() ' : ' order by name asc ');
        $select = "`forum` = 1";
        if ($showprivate) {
            $select = "( `forum` = 1 OR `prv` = 1 )";
        }

	$contacts = q("SELECT `contact`.`id`, `contact`.`url`, `contact`.`name`, `contact`.`micro` from contact 
			WHERE `network`= 'dfrn' AND $select AND `uid` = %d
			and blocked = 0 and hidden = 0 and pending = 0 and archive = 0
			$order ",
			intval($uid)
	);

	// Look if the profile is a community page
	foreach($contacts as $contact) {
		$forumlist[] = array("url"=>$contact["url"], "name"=>$contact["name"], "id"=>$contact["id"], "micro"=>$contact['micro']);
	}
	return($forumlist);
}

function forumlist_network_mod_init($a,$b) {

	if(! intval(get_pconfig(local_user(),'forumlist','show_on_network')))
		return;

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/forumlist/forumlist.css' . '" media="all" />' . "\r\n";

	$forumlist = '<div id="forumlist-sidebar" class="widget">
			<div class="title tool">
			<h3>'.t("Forums").'</h3></div>';

	$forumlist .= '<div id="hide-forum-list" class="fakelink" onclick="openClose(\'forum-list\');" >' 
				. t('show/hide') . '</div>'
				. '<div role="menu" id="forum-list" style="display: none;">';


	$randomise = intval(get_pconfig(local_user(),'forumlist','randomise'));

	$contacts = forumlist_getpage($a->user['uid'],true,$randomise, true);

	if(count($contacts)) {
		foreach($contacts as $contact) {
			$forumlist .= '<div role="menuitem"><a href="' . $a->get_baseurl() . '/redir/' . $contact["id"] . '" title="'.t('External link to forum').'" class="label sparkle" target="_blank"><img class="forumlist-img" height="20" width="20" src="' . $contact['micro'] .'" alt="'.t('External link to forum').'" /></a> <a href="' . $a->get_baseurl() . '/network?f=&cid=' . $contact['id'] . '" >' . $contact["name"]."</a></div>";
		}
	}
	else {
		$forumlist .= t('No forum subscriptions');
	}

	$forumlist .= "</div></div>";
	if (sizeof($contacts) > 0)
		$a->page['aside'] = $forumlist . $a->page['aside'];
}


function forumlist_profile_advanced($a,&$b) {
	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/forumlist/forumlist.css' . '" media="all" />' . "\r\n";

	$profile = intval(get_pconfig($a->profile['profile_uid'],'forumlist','show_on_profile'));
	if(! $profile)
		return;

	$forumlist = '<div id="forumlist-profile">
			<div class="title">'.t("Forums:").'</div>
			<div id="profile-forumlist-list">';

	// place holder in case somebody wants configurability
	$show_total = 9999;

	$randomise = true;

	$contacts = forumlist_getpage($a->user['uid'],false,$randomise,false);

	$total_shown = 0;
	$more = false;

	foreach($contacts as $contact) {
		$forumlist .= micropro($contact,false,'forumlist-profile-advanced');
		$total_shown ++;
		if($total_shown == $show_total)
			break;
	}
	$forumlist .= '</div></div><div class="clear"></div>';

	if(count($contacts) > 0)
		$b .= $forumlist;

}



function forumlist_plugin_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'forumlist-settings-submit')))
		return;
//	set_pconfig(local_user(),'forumlist','max_forumlists',intval($_POST['forumlist_max_forumlists']));
	set_pconfig(local_user(),'forumlist','randomise',intval($_POST['forumlist_random']));
	set_pconfig(local_user(),'forumlist','show_on_profile',intval($_POST['forumlist_profile']));
	set_pconfig(local_user(),'forumlist','show_on_network',intval($_POST['forumlist_network']));

	info( t('Forumlist settings updated.') . EOL);
}


function forumlist_plugin_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the forumlist so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/forumlist/forumlist.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$randomise = intval(get_pconfig(local_user(),'forumlist','randomise'));
	$randomise_checked = (($randomise) ? ' checked="checked" ' : '');

	$profile = intval(get_pconfig(local_user(),'forumlist','show_on_profile'));
	$profile_checked = (($profile) ? ' checked="checked" ' : '');

	$network = intval(get_pconfig(local_user(),'forumlist','show_on_network'));
	$network_checked = (($network) ? ' checked="checked" ' : '');


	/* Add some HTML to the existing form */
	$s .= '<span id="settings_forumlist_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_forumlist_expanded\'); openClose(\'settings_forumlist_inflated\');">';
	$s .= '<h3>' . t('Forumlist') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_forumlist_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_forumlist_expanded\'); openClose(\'settings_forumlist_inflated\');">';
	$s .= '<h3>' . t('Forumlist') . '</h3>';
	$s .= '</span>';

	$s .= '<div id="forumlist-settings-wrapper">';
	$s .= '<label id="forumlist-random-label" for="forumlist-random">' . t('Randomise forum list') . '</label>';
	$s .= '<input id="forumlist-random" type="checkbox" name="forumlist_random" value="1" ' . $randomise_checked . '/>';
	$s .= '<div class="clear"></div>';
	$s .= '<label id="forumlist-profile-label" for="forumlist-profile">' . t('Show forums on profile page') . '</label>';
	$s .= '<input id="forumlist-profile" type="checkbox" name="forumlist_profile" value="1" ' . $profile_checked . '/>';
	$s .= '<div class="clear"></div>';
	$s .= '<label id="forumlist-network-label" for="forumlist-network">' . t('Show forums on network page') . '</label>';
	$s .= '<input id="forumlist-network" type="checkbox" name="forumlist_network" value="1" ' . $network_checked . '/>';
	$s .= '<div class="clear"></div>';

	$s .= '</div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="forumlist-settings-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

}


