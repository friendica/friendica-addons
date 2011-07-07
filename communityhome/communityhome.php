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
	
	// login form
	$aside .= "<h3>". t('Login'). "</h3>";
	$aside .= login(($a->config['register_policy'] == REGISTER_CLOSED) ? false : true);
	
	// last 10 users
	$aside .= "<h3>". t('Last users'). "</h3>";
	$sql_extra = "";
	$publish = (get_config('system','publish_all') ? '' : " AND `publish` = 1 " );
	$order = " ORDER BY `register_date` DESC ";

	$r = q("SELECT `profile`.*, `profile`.`uid` AS `profile_uid`, `user`.`nickname`, `user`.`timezone` 
			FROM `profile` LEFT JOIN `user` ON `user`.`uid` = `profile`.`uid` 
			WHERE `is-default` = 1 $publish AND `user`.`blocked` = 0 $sql_extra $order LIMIT %d , %d ",
		0,
		10
	);
	$aside .= "<div class='items-wrapper'>";
	if(count($r)) {

		$tpl = file_get_contents( dirname(__file__).'/directory_item.tpl');

		$photo = 'thumb';

		foreach($r as $rr) {
			$profile_link = $a->get_baseurl() . '/profile/' . ((strlen($rr['nickname'])) ? $rr['nickname'] : $rr['profile_uid']);
		
			$entry = replace_macros($tpl,array(
				'$id' => $rr['id'],
				'$profile-link' => $profile_link,
				'$photo' => $rr[$photo],
				'$alt-text' => $rr['name'],
			));

			$aside .= $entry;

		}
	}
	$aside .= "</div>";
	
	// last 10 photos
	$aside .= "<h3>". t('Last photos'). "</h3>";
	$r = q("SELECT `photo`.`id`, `photo`.`resource-id`, `photo`.`scale`, `photo`.`desc`, `user`.`nickname`, `user`.`username` FROM 
				(SELECT `resource-id`, MAX(`scale`) as maxscale FROM `photo` 
					WHERE `profile`=0 AND `height` NOT IN ( 175, 80, 48) 
						AND `allow_cid`='' AND `allow_gid`='' AND `deny_cid`='' AND `deny_gid`='' GROUP BY `resource-id`) AS `t1`
				INNER JOIN `photo` ON `photo`.`resource-id`=`t1`.`resource-id` AND `photo`.`scale` = `t1`.`maxscale`,
				`user` 
				WHERE `user`.`uid` = `photo`.`uid`
				AND `user`.`blockwall`=0
				ORDER BY `photo`.`edited` DESC
				LIMIT 0, 10");

	$aside .= "<div class='items-wrapper'>";				
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

			$aside .= $entry;
		}
	}
	$aside .= "</div>";
	
	// last 10 liked items
	$aside .= "<h3>". t('Last likes'). "</h3>";
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

	$aside .= "<ul id='likes'>";
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

		$aside .= "<li>". sprintf( t('%1$s likes %2$s\'s %3$s'), $author, $objauthor, $plink) ."</li>";
		
	}
	$aside .= "</ul>";
		
	
	$a->page['aside'] = $aside;
	
	$o = '<h1>' . ((x($a->config,'sitename')) ? sprintf( t("Welcome to %s") ,$a->config['sitename']) : "" ) . '</h1>';
	
	$oldset = get_config('system','no_community_page');
	set_config('system','no_community_page', false);
	$o .= community_content($a,1);
	set_config('system','no_community_page', $oldset);
}
