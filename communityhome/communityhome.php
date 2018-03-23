<?php

/**
 * Name: Community home
 * Description: Show last community activity in homepage
 * Version: 2.0
 * Author: Fabio Comuni <http://kirgroup.com/profile/fabrixxm>
 * Status: Unsupported
 */

use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Module\Login;

require_once 'mod/community.php';

function communityhome_install()
{
	Addon::registerHook('home_content', 'addon/communityhome/communityhome.php', 'communityhome_home');
	logger("installed communityhome");
}

function communityhome_uninstall()
{
	Addon::unregisterHook('home_content', 'addon/communityhome/communityhome.php', 'communityhome_home');
	logger("removed communityhome");
}

function communityhome_getopts()
{
	return [
		'hidelogin' => L10n::t('Hide login form'),
		'showlastusers' => L10n::t('Show last new users'),
		'showlastphotos' => L10n::t('Show last photos'),
		'showlastlike' => L10n::t('Show last liked items'),
		'showcommunitystream' => L10n::t('Show community stream')
	];
}

function communityhome_addon_admin(App $a, &$o)
{
	$tpl = get_markup_template('settings.tpl', 'addon/communityhome/');

	$opts = communityhome_getopts();
	$ctx = [
		'$submit' => L10n::t("Submit"),
		'$fields' => [],
	];

	foreach ($opts as $k => $v) {
		$ctx['fields'][] = ['communityhome_' . $k, $v, Config::get('communityhome', $k)];
	}
	$o = replace_macros($tpl, $ctx);
}

function communityhome_addon_admin_post(App $a)
{
	if (x($_POST, 'communityhome-submit')) {
		$opts = communityhome_getopts();
		foreach ($opts as $k => $v) {
			Config::set('communityhome', $k, x($_POST, 'communityhome_' . $k));
		}
	}
}

function communityhome_home(App $a, &$o)
{
	// custom css
	$a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="' . $a->get_baseurl() . '/addon/communityhome/communityhome.css" media="all" />';

	if (!Config::get('communityhome', 'hidelogin')) {
		$aside = [
			'$tab_1' => L10n::t('Login'),
			'$tab_2' => L10n::t('OpenID'),
			'$noOid' => Config::get('system', 'no_openid'),
		];

		// login form
		$aside['$login_title'] = L10n::t('Login');
		$aside['$login_form'] = Login::form($a->query_string, $a->config['register_policy'] == REGISTER_CLOSED ? false : true);
	} else {
		$aside = [
			//'$tab_1' => L10n::t('Login'),
			//'$tab_2' => L10n::t('OpenID'),
			//'$noOid' => Config::get('system','no_openid'),
		];
	}

	// last 12 users
	if (Config::get('communityhome', 'showlastusers')) {
		$aside['$lastusers_title'] = L10n::t('Latest users');
		$aside['$lastusers_items'] = [];
		$sql_extra = "";
		$publish = (Config::get('system', 'publish_all') ? '' : " AND `publish` = 1 " );
		$order = " ORDER BY `register_date` DESC ";

		$r = q("SELECT `profile`.*, `profile`.`uid` AS `profile_uid`, `user`.`nickname`
				FROM `profile` LEFT JOIN `user` ON `user`.`uid` = `profile`.`uid`
				WHERE `is-default` = 1 $publish AND `user`.`blocked` = 0 $sql_extra $order LIMIT %d, %d ",
			0,
			12
		);
		#	$tpl = file_get_contents( dirname(__file__).'/directory_item.tpl');
		$tpl = get_markup_template('directory_item.tpl', 'addon/communityhome/');
		if (count($r)) {
			$photo = 'thumb';
			foreach ($r as $rr) {
				$profile_link = $a->get_baseurl() . '/profile/' . ((strlen($rr['nickname'])) ? $rr['nickname'] : $rr['profile_uid']);
				$entry = replace_macros($tpl, [
					'$id' => $rr['id'],
					'$profile_link' => $profile_link,
					'$photo' => $a->get_cached_avatar_image($rr[$photo]),
					'$alt_text' => $rr['name'],
				));
				$aside['$lastusers_items'][] = $entry;
			}
		}
	}

	// last 12 photos
	if (Config::get('communityhome', 'showlastphotos')) {
		$aside['$photos_title'] = L10n::t('Latest photos');
		$aside['$photos_items'] = [];
		$r = q("SELECT `photo`.`id`, `photo`.`resource-id`, `photo`.`scale`, `photo`.`desc`, `user`.`nickname`, `user`.`username` FROM
					(SELECT `resource-id`, MAX(`scale`) as maxscale FROM `photo`
						WHERE `profile`=0 AND `contact-id`=0 AND `album` NOT IN ('Contact Photos', '%s', 'Profile Photos', '%s')
							AND `allow_cid`='' AND `allow_gid`='' AND `deny_cid`='' AND `deny_gid`='' GROUP BY `resource-id`) AS `t1`
					INNER JOIN `photo` ON `photo`.`resource-id`=`t1`.`resource-id` AND `photo`.`scale` = `t1`.`maxscale`,
					`user` 
					WHERE `user`.`uid` = `photo`.`uid`
					AND `user`.`blockwall`=0
					AND `user`.`hidewall` = 0
					ORDER BY `photo`.`edited` DESC
					LIMIT 0, 12",
			dbesc(L10n::t('Contact Photos')),
			dbesc(L10n::t('Profile Photos'))
		);


		if (count($r)) {
			#		$tpl = file_get_contents( dirname(__file__).'/directory_item.tpl');
			$tpl = get_markup_template('directory_item.tpl', 'addon/communityhome/');
			foreach ($r as $rr) {
				$photo_page = $a->get_baseurl() . '/photos/' . $rr['nickname'] . '/image/' . $rr['resource-id'];
				$photo_url  = $a->get_baseurl() . '/photo/' . $rr['resource-id'] . '-' . $rr['scale'] . '.jpg';

				$entry = replace_macros($tpl, [
					'$id' => $rr['id'],
					'$profile_link' => $photo_page,
					'$photo' => $photo_url,
					'$alt_text' => $rr['username']." : ".$rr['desc'],
				));

				$aside['$photos_items'][] = $entry;
			}
		}
	}

	// last 10 liked items
	if (Config::get('communityhome', 'showlastlike')) {
		$aside['$like_title'] = L10n::t('Latest likes');
		$aside['$like_items'] = [];
		$r = q("SELECT `T1`.`created`, `T1`.`liker`, `T1`.`liker-link`, `item`.* FROM
				(SELECT `parent-uri`, `created`, `author-name` AS `liker`,`author-link` AS `liker-link`
					FROM `item` WHERE `verb`='http://activitystrea.ms/schema/1.0/like' GROUP BY `parent-uri` ORDER BY `created` DESC) AS T1
				INNER JOIN `item` ON `item`.`uri`=`T1`.`parent-uri` 
				WHERE `T1`.`liker-link` LIKE '%s%%' OR `item`.`author-link` LIKE '%s%%'
				GROUP BY `uri`
				ORDER BY `T1`.`created` DESC
				LIMIT 0,10",
			$a->get_baseurl(),
			$a->get_baseurl()
		);

		foreach ($r as $rr) {
			$author = '<a href="' . $rr['liker-link'] . '">' . $rr['liker'] . '</a>';
			$objauthor = '<a href="' . $rr['author-link'] . '">' . $rr['author-name'] . '</a>';

			//var_dump($rr['verb'],$rr['object-type']); killme();
			switch ($rr['verb']) {
				case 'http://activitystrea.ms/schema/1.0/post':
					switch ($rr['object-type']) {
						case 'http://activitystrea.ms/schema/1.0/event':
							$post_type = L10n::t('event');
							break;
						default:
							$post_type = L10n::t('status');
					}
					break;
				default:
					if ($rr['resource-id']) {
						$post_type = L10n::t('photo');
						$m = [];
						preg_match("/\[url=([^]]*)\]/", $rr['body'], $m);
						$rr['plink'] = $m[1];
					} else {
						$post_type = L10n::t('status');
					}
			}
			$plink = '<a href="' . $rr['plink'] . '">' . $post_type . '</a>';

			$aside['$like_items'][] = L10n::t('%1$s likes %2$s\'s %3$s', $author, $objauthor, $plink);
		}
	}

#	$tpl = file_get_contents(dirname(__file__).'/communityhome.tpl');
	$tpl = get_markup_template('communityhome.tpl', 'addon/communityhome/');
	$a->page['aside'] = replace_macros($tpl, $aside);

	$o = '<h1>' . ((x($a->config, 'sitename')) ? L10n::t("Welcome to %s", $a->config['sitename']) : "" ) . '</h1>';

	if (file_exists('home.html')) $o = file_get_contents('home.html');

	if (Config::get('communityhome', 'showcommunitystream')) {
		$oldset = Config::get('system', 'community_page_style');
		if ($oldset == CP_NO_COMMUNITY_PAGE) Config::set('system', 'community_page_style', CP_USERS_ON_SERVER);

		$o .= community_content($a, 1);

		if ($oldset == CP_NO_COMMUNITY_PAGE) Config::set('system', 'community_page_style', $oldset);
	}
}
