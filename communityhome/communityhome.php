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
				'$photo' => $a->get_cached_avatar_image($rr[$photo]),
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
	
	// last 12 photos
	$aside['$photos_title'] = t('Latest photos');
	$aside['$photos_items'] = array();
	$r = q("SELECT `photo`.`id`, `photo`.`resource-id`, `photo`.`scale`, `photo`.`desc`, `user`.`nickname`, `user`.`username` FROM 
				(SELECT `resource-id`, MAX(`scale`) as maxscale FROM `photo` 
					WHERE `profile`=0 AND `contact-id`=0 AND `album` NOT IN ('Contact Photos', '%s', 'Profile Photos', '%s')
						AND `allow_cid`='' AND `allow_gid`='' AND `deny_cid`='' AND `deny_gid`='' GROUP BY `resource-id`) AS `t1`
				INNER JOIN `photo` ON `photo`.`resource-id`=`t1`.`resource-id` AND `photo`.`scale` = `t1`.`maxscale`,
				`user` 
				WHERE `user`.`uid` = `photo`.`uid`
				AND `user`.`blockwall`=0
				ORDER BY `photo`.`edited` DESC
				LIMIT 0, 12",
				dbesc(t('Contact Photos')),
				dbesc(t('Profile Photos'))
				);

		
	if(count($r)) {
		$tpl = file_get_contents( dirname(__file__).'/directory_item.tpl');
		foreach($r as $rr) {
			$photo_page = $a->get_baseurl() . '/photos/' . $rr['nickname'] . '/image/' . $rr['resource-id'];
			$photo_url = $a->get_baseurl() . '/photo/' .  $rr['resource-id'] . '-' . $rr['scale'] .'.jpg';
		
			$entry = replace_macros($tpl,array(
				'$id' => $rr['id'],
				'$profile-link' => $photo_page,
				'$photo' => $photo_url,
				'$alt-text' => $rr['username']." : ".$rr['desc'],
			));

			$aside['$photos_items'][] = $entry;
		}
	}
	
	// last 10 liked items
	$aside['$like_title'] = t('Latest likes');
	$aside['$like_items'] = array();
	$r = q("SELECT `T1`.`created`, `T1`.`liker`, `T1`.`liker-link`, `item`.* FROM 
			(SELECT `parent-uri`, `created`, `author-name` AS `liker`,`author-link` AS `liker-link` 
				FROM `item` WHERE `verb`='http://activitystrea.ms/schema/1.0/like' GROUP BY `parent-uri` ORDER BY `created` DESC) AS T1
			INNER JOIN `item` ON `item`.`uri`=`T1`.`parent-uri` 
			WHERE `T1`.`liker-link` LIKE '%s%%' OR `item`.`author-link` LIKE '%s%%'
			GROUP BY `uri`
			ORDER BY `T1`.`created` DESC
			LIMIT 0,10",
			$a->get_baseurl(),$a->get_baseurl()
			);

	foreach ($r as $rr) {
		$author	 = '<a href="' . $rr['liker-link'] . '">' . $rr['liker'] . '</a>';
		$objauthor =  '<a href="' . $rr['author-link'] . '">' . $rr['author-name'] . '</a>';
		
		//var_dump($rr['verb'],$rr['object-type']); killme();
		switch($rr['verb']){
			case 'http://activitystrea.ms/schema/1.0/post':
				switch ($rr['object-type']){
					case 'http://activitystrea.ms/schema/1.0/event':
						$post_type = t('event');
						break;
					default:
						$post_type = t('status');
				}
				break;
			default:
				if ($rr['resource-id']){
					$post_type = t('photo');
					$m=array();	preg_match("/\[url=([^]]*)\]/", $rr['body'], $m);
					$rr['plink'] = $m[1];
				} else {
					$post_type = t('status');
				}
		}
		$plink = '<a href="' . $rr['plink'] . '">' . $post_type . '</a>';

		$aside['$like_items'][] = sprintf( t('%1$s likes %2$s\'s %3$s'), $author, $objauthor, $plink);
		
	}
	
	$tpl = file_get_contents(dirname(__file__).'/communityhome.tpl');
	$a->page['aside'] = replace_macros($tpl, $aside);
	
	$o = '<h1>' . ((x($a->config,'sitename')) ? sprintf( t("Welcome to %s") ,$a->config['sitename']) : "" ) . '</h1>';
	
	$oldset = get_config('system','no_community_page');
	set_config('system','no_community_page', false);
	$o .= community_content($a,1);
	set_config('system','no_community_page', $oldset);
}
