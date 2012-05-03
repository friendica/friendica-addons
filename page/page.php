<?php
/**
 * Name: Page
 * Description: Shows lists of community pages (improved performance over 'pages')
 * Version: 1.0
 * Author: Mike Macgirvin <mike@macgirvin.com>
 * based on pages plugin by
 * Author: Michael Vogel <ike@piratenpartei.de>
 *
 */

function page_install() {
	register_hook('page_end', 'addon/page/page.php', 'page_page_end');
}

function page_uninstall() {
	unregister_hook('page_end', 'addon/page/page.php', 'page_page_end');
}


function page_getpage($uid) {


	$pagelist = array();

	$contacts = q("SELECT `id`, `url`, `name`, `micro`FROM `contact`
			WHERE `network`= 'dfrn' AND `forum` = 1 AND `uid` = %d
			order by name asc ",
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
		$page .= '<li style="list-style-type: none;" class="tool"><img height="20" width="20" src="' . $contact['micro'] .'" alt="' . $contact['url'] . '" /> <a href="'.$a->get_baseurl().'/redir/'.$contact["id"].'" title="' . $contact['url'] . '" class="label" target="external-link">'.
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



