<?php
/**
 * Name: Community home
 * Description: Show last community activity in homepage
 * Version: 1.0
 * Author: Fabio Comuni <http://kirgroup.com/profile/fabrixxm>
 */


require_once('mod/community.php');


function communityhome_install() {
	register_hook('home_content', 'addon/communityhome/communityhome.php', 'communityhome_home');
	logger("installed communityhome");
}

function communityhome_uninstall() {
	unregister_hook('home_content', 'addon/communityhome/communityhome.php', 'communityhome_home');
	logger("removed communityhome");
}

function communityhome_home(&$a, &$o){
	// custom css
	$a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'.$a->get_baseurl().'/addon/communityhome/communityhome.css" media="all" />';
	
	$aside = array(
		'$tab_1' => t('Login'),
		'$tab_2' => t('OpenID'),
		'$noOid' => get_config('system','no_openid'),
	);
	
	// login form
	$aside['$login_title'] =  t('Login');
	$aside['$login_form'] = login(($a->config['register_policy'] == REGISTER_CLOSED) ? false : true);
	
	// last 12 users
	$aside['$lastusers_title'] = t('Latest users');
	$aside['$lastusers_items'] = array();
	$sql_extra = "";
	$publish = (get_config('system','publish_all') ? '' : " AND `publish` = 1 " );
	$order = " ORDER BY `register_date` DESC ";

	$r = q("SELECT `profile`.*, `profile`.`uid` AS `profile_uid`, `user`.`nickname`
			FROM `profile` LEFT JOIN `user` ON `user`.`uid` = `profile`.`uid` 
			WHERE `is-default` = 1 $publish AND `user`.`blocked` = 0 $sql_extra $order LIMIT %d , %d ",
		0,
		12
	);
	$tpl = file_get_contents( dirname(__file__).'/directory_item.tpl');
	if(count($r)) {
		$photo = 'thumb';
		foreach($r as $rr) {
			$profile_link = $a->get_baseurl() . '/profile/' . ((strlen($rr['nickname'])) ? $rr['nickname'] : $rr['profile_uid']);
			$entry = replace_macros($tpl,array(
				'$id' => $rr['id'],
				'$profile-link' => $profile_link,
				'$photo' => $rr[$photo],
				'$alt-text' => $rr['name'],
			));
			$aside['$lastusers_items'][] = $entry;
		}
	}
	
	// 12 most active users (by posts and contacts)
	// this query don't work on some mysql versions
	$r = q("SELECT `uni`.`contacts`,`uni`.`items`, `profile`.*, `profile`.`uid` AS `profile_uid`, `user`.`nickname`  FROM
			(SELECT COUNT(`id`) as `contacts`, `uid` FROM `contact` WHERE `self`=0 GROUP BY `uid`) AS `con`,
			(SELECT COUNT(`id`) as `items`, `uid` FROM `item` WHERE `item`.`changed` > DATE(NOW() - INTERVAL 1 MONTH) AND `item`.`wall` = 1 GROUP BY `uid`) AS `ite`,
			(
			SELECT `contacts`,`items`,`ite`.`uid` FROM `con` RIGHT OUTER JOIN `ite` ON `con`.`uid`=`ite`.`uid` 
			UNION ALL 
			SELECT `contacts`,`items`,`con`.`uid` FROM `con` LEFT OUTER JOIN `ite` ON `con`.`uid`=`ite`.`uid`
			) AS `uni`, `user`, `profile`
			WHERE `uni`.`uid`=`user`.`uid`
			AND `uni`.`uid`=`profile`.`uid` AND `profile`.`publish`=1
			GROUP BY `uid`
			ORDER BY `items` DESC,`contacts` DESC
			LIMIT 0,10");
	if($r && count($r)) {
		$aside['$activeusers_title']  = t('Most active users');
		$aside['$activeusers_items']  = array();
		
		$photo = 'thumb';
		foreach($r as $rr) {
			$profile_link = $a->get_baseurl() . '/profile/' . ((strlen($rr['nickname'])) ? $rr['nickname'] : $rr['profile_uid']);
			$entry = replace_macros($tpl,array(
				'$id' => $rr['id'],
				'$profile-link' => $profile_link,
				'$photo' => $rr[$photo],
				'$alt-text' => sprintf("%s (%s posts, %s contacts)",$rr['name'], ($rr['items']?$rr['items']:'0'), ($rr['contacts']?$rr['contacts']:'0'))
			));
			$aside['$activeusers_items'][] = $entry;
		}
	}
	
	
	
	
	$tpl = file_get_contents(dirname(__file__).'/communityhome.tpl');
	$a->page['aside'] = replace_macros($tpl, $aside);
	$o = '';
	if(file_exists('home.html'))
	
 		$o .= file_get_contents('home.html');
	
}
