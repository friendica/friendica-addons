<?php
/**
 * Name: Page
 * Description: This addon is now deprecated.  Administrators should switch to forumlist instead.  Developers should also add any functionality to forumlist instead of here.
 * Version: 1.0
 * Author: Mike Macgirvin <mike@macgirvin.com>
 * based on pages plugin by
 * Author: Michael Vogel <ike@piratenpartei.de>
 *
 */

function page_install() {
	register_hook('network_mod_init', 'addon/page/page.php', 'page_network_mod_init');
	register_hook('plugin_settings', 'addon/page/page.php', 'page_plugin_settings');
	register_hook('plugin_settings_post', 'addon/page/page.php', 'page_plugin_settings_post');
	register_hook('profile_advanced', 'addon/page/page.php', 'page_profile_advanced');

}

function page_uninstall() {
	unregister_hook('network_mod_init', 'addon/page/page.php', 'page_network_mod_init');
	unregister_hook('plugin_settings', 'addon/page/page.php', 'page_plugin_settings');
	unregister_hook('plugin_settings_post', 'addon/page/page.php', 'page_plugin_settings_post');
	unregister_hook('profile_advanced', 'addon/page/page.php', 'page_profile_advanced');

	// remove only - obsolete
	unregister_hook('page_end', 'addon/page/page.php', 'page_page_end');
}


function page_getpage($uid,$showhidden = true,$randomise = false) {


	$pagelist = array();

	$order = (($showhidden) ? '' : " and hidden = 0 ");
	$order .= (($randomise) ? ' order by rand() ' : ' order by name asc ');

	$contacts = q("SELECT `id`, `url`, `name`, `micro` FROM `contact`
			WHERE `network`= 'dfrn' AND `forum` = 1 AND `uid` = %d
			and blocked = 0 and hidden = 0 and pending = 0 and archive = 0
			$order ",
			intval($uid)
	);

	$page = array();

	// Look if the profile is a community page
	foreach($contacts as $contact) {
		$page[] = array("url"=>$contact["url"], "name"=>$contact["name"], "id"=>$contact["id"], "micro"=>$contact['micro']);
	}
	return($page);
}

function page_page_end($a,&$b) {
	// Only move on if if it's the "network" module and there is a logged on user
	if (($a->module != "network") OR ($a->user['uid'] == 0))
		return;

	$page = '<div id="page-sidebar" class="widget">
			<div class="title tool">
			<h3>'.t("Forums").'</h3></div>
			<div id="sidebar-page-list"><ul>';


	$contacts = page_getpage($a->user['uid']);

	$total_shown = 0;
	$more = false;

	foreach($contacts as $contact) {
		$page .= '<li style="list-style-type: none;" class="tool"><img height="20" width="20" src="' . $contact['micro'] .'" alt="' . $contact['url'] . '" /> <a href="'.$a->get_baseurl().'/redir/'.$contact["id"].'" title="' . $contact['url'] . '" class="label sparkle" target="external-link">'.
				$contact["name"]."</a></li>";
		$total_shown ++;
		if($total_shown == 6) {
			$more = true;
			$page .= '</ul><div id="hide-comments-page-widget" class="fakelink" onclick="showHideComments(\'page-widget\');" >' . t('show more') 
				. '</div><div id="collapsed-comments-page-widget" style="display: none;" ><ul>';
		} 
	}
	if($more)
		$page .= '</div>';
	$page .= "</ul></div></div>";
	if (sizeof($contacts) > 0)
		$a->page['aside'] = $page . $a->page['aside'];
}

function page_network_mod_init($a,$b) {

	$page = '<div id="page-sidebar" class="widget">
			<div class="title tool">
			<h3>'.t("Forums").'</h3></div>
			<div id="sidebar-page-list"><ul>';

	$show_total = intval(get_pconfig(local_user(),'page','max_pages'));
	if($show_total === false)
		$show_total = 6;
	$randomise = intval(get_pconfig(local_user(),'page','randomise'));

	$contacts = page_getpage($a->user['uid'],true,$randomise);

	$total_shown = 0;
	$more = false;

	foreach($contacts as $contact) {
		$page .= '<li style="list-style-type: none;" class="tool"><img height="20" width="20" src="' . $contact['micro'] .'" alt="' . $contact['url'] . '" /> <a href="'.$a->get_baseurl().'/redir/'.$contact["id"].'" title="' . $contact['url'] . '" class="label sparkle" target="external-link">'.
				$contact["name"]."</a></li>";
		$total_shown ++;
		if(($show_total) && ($total_shown == $show_total)) {
			$more = true;
			$page .= '</ul><div id="hide-comments-page-widget" class="fakelink" onclick="showHideComments(\'page-widget\');" >' . t('show more') 
				. '</div><div id="collapsed-comments-page-widget" style="display: none;" ><ul>';
		} 
	}
	if($more)
		$page .= '</div>';
	$page .= "</ul></div></div>";
	if (sizeof($contacts) > 0)
		$a->page['aside'] = $page . $a->page['aside'];
}


function page_profile_advanced($a,&$b) {

	$profile = intval(get_pconfig($a->profile['profile_uid'],'page','show_on_profile'));
	if(! $profile)
		return;

	$page = '<div id="page-profile">
			<div class="title">'.t("Forums:").'</div>
			<div id="profile-page-list">';

	// place holder in case somebody wants configurability
	$show_total = 9999;

	$randomise = true;

	$contacts = page_getpage($a->user['uid'],false,$randomise);

	$total_shown = 0;
	$more = false;

	foreach($contacts as $contact) {
		$page .= micropro($contact,false,'page-profile-advanced');
		$total_shown ++;
		if($total_shown == $show_total)
			break;
	}
	$page .= '</div></div><div class="clear"></div>';

	if(count($contacts) > 0)
		$b .= $page;

}



function page_plugin_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'page-settings-submit')))
		return;

	set_pconfig(local_user(),'page','max_pages',intval($_POST['page_max_pages']));
	set_pconfig(local_user(),'page','randomise',intval($_POST['page_random']));
	set_pconfig(local_user(),'page','show_on_profile',intval($_POST['page_profile']));

	info( t('Page settings updated.') . EOL);
}


function page_plugin_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/page/page.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$max_pages = get_pconfig(local_user(),'page','max_pages');
	if($max_pages === false)
		$max_pages = 6;

	$randomise = intval(get_pconfig(local_user(),'page','randomise'));
	$randomise_checked = (($randomise) ? ' checked="checked" ' : '');

	$profile = intval(get_pconfig(local_user(),'page','show_on_profile'));
	$profile_checked = (($profile) ? ' checked="checked" ' : '');
	
	
	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('Page Settings') . '</h3>';
	$s .= '<div id="page-settings-wrapper">';
	$s .= '<label id="page-settings-label" for="page-max-pages">' . t('How many forums to display on sidebar without paging') . '</label>';
	$s .= '<input id="page-max-pages" type="text" name="page_max_pages" value="' . intval($max_pages) . '" ' . '/>';
	$s .= '<div class="clear"></div>';
	$s .= '<label id="page-random-label" for="page-random">' . t('Randomise Page/Forum list') . '</label>';
	$s .= '<input id="page-random" type="checkbox" name="page_random" value="1" ' . $randomise_checked . '/>';
	$s .= '<div class="clear"></div>';
	$s .= '<label id="page-profile-label" for="page-profile">' . t('Show pages/forums on profile page') . '</label>';
	$s .= '<input id="page-profile" type="checkbox" name="page_profile" value="1" ' . $profile_checked . '/>';
	$s .= '<div class="clear"></div>';

	$s .= '</div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="page-settings-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}


