<?php

/**
 * Name: Forum Directory
 * Description: Add a directory of forums hosted on your server, with verbose descriptions.
 * Version: 1.0
 * Author: Thomas Willingham <https://beardyunixer.com/profile/beardyunixer>
 */

use Friendica\Content\Nav;
use Friendica\Content\Widget;
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Database\DBM;

function forumdirectory_install() {
Addon::registerHook('app_menu', 'addon/forumdirectory/forumdirectory.php', 'forumdirectory_app_menu');
}

function forumdirectory_uninstall() {
Addon::unregisterHook('app_menu', 'addon/forumdirectory/forumdirectory.php', 'forumdirectory_app_menu');
}

function forumdirectory_module()
{
	return;
}

function forumdirectory_app_menu($a, &$b)
{
	$b['app_menu'][] = '<div class="app-title"><a href="forumdirectory">' . t('Forum Directory') . '</a></div>';
}

function forumdirectory_init(&$a)
{
	$a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="' . $a->get_baseurl() . '/addon/forumdirectory/forumdirectory.css" media="all" />';

	$a->set_pager_itemspage(60);

	if (local_user()) {
		$a->page['aside'] .= Widget::findPeople();
	} else {
		unset($_SESSION['theme']);
	}
}

function forumdirectory_post(&$a)
{
	if (x($_POST, 'search')) {
		$a->data['search'] = $_POST['search'];
	}
}

function forumdirectory_content(&$a)
{
	if ((Config::get('system', 'block_public')) && (!local_user()) && (!remote_user())) {
		notice(t('Public access denied.') . EOL);
		return;
	}

	$o = '';
	Nav::setSelected('directory');

	if (x($a->data, 'search')) {
		$search = notags(trim($a->data['search']));
	} else {
		$search = ((x($_GET, 'search')) ? notags(trim(rawurldecode($_GET['search']))) : '');
	}

	$tpl = get_markup_template('directory_header.tpl');

	$globaldir = '';
	$gdirpath = Config::get('system', 'directory');
	if (strlen($gdirpath)) {
		$globaldir = '<ul><li><div id="global-directory-link"><a href="'
			. zrl($gdirpath, true) . '">' . t('Global Directory') . '</a></div></li></ul>';
	}

	$admin = '';

	$o .= replace_macros($tpl, [
		'$search'    => $search,
		'$globaldir' => $globaldir,
		'$desc'      => t('Find on this site'),
		'$admin'     => $admin,
		'$finding'   => (strlen($search) ? '<h4>' . t('Finding: ') . "'" . $search . "'" . '</h4>' : ""),
		'$sitedir'   => t('Site Directory'),
		'$submit'    => t('Find')
	]);

	$sql_extra = '';
	if (strlen($search)) {
		$sql_extra = " AND MATCH (`profile`.`name`, `user`.`nickname`, `pdesc`, `locality`,`region`,`country-name`,"
			. "`gender`,`marital`,`sexual`,`about`,`romance`,`work`,`education`,`pub_keywords`,`prv_keywords` )"
			. " AGAINST ('" . dbesc($search) . "' IN BOOLEAN MODE) ";
	}

	$publish = Config::get('system', 'publish_all') ? '' : " AND `publish` = 1 ";

	$r = q("SELECT COUNT(*) AS `total` FROM `profile` LEFT JOIN `user` ON `user`.`uid` = `profile`.`uid`"
		. " WHERE `is-default` = 1 $publish AND `user`.`blocked` = 0 AND `page-flags` = 2 $sql_extra ");
	if (DBM::is_result($r)) {
		$a->set_pager_total($r[0]['total']);
	}

	$order = " ORDER BY `name` ASC ";

	$r = q("SELECT `profile`.*, `profile`.`uid` AS `profile_uid`, `user`.`nickname`, `user`.`timezone` , `user`.`page-flags`"
		. " FROM `profile` LEFT JOIN `user` ON `user`.`uid` = `profile`.`uid` WHERE `is-default` = 1 $publish"
		. " AND `user`.`blocked` = 0 AND `page-flags` = 2 $sql_extra $order LIMIT %d , %d ",
		intval($a->pager['start']),
		intval($a->pager['itemspage'])
	);
	if (DBM::is_result($r)) {
		if (in_array('small', $a->argv)) {
			$photo = 'thumb';
		} else {
			$photo = 'photo';
		}

		foreach ($r as $rr) {
			$profile_link = $a->get_baseurl() . '/profile/' . ((strlen($rr['nickname'])) ? $rr['nickname'] : $rr['profile_uid']);

			$pdesc = (($rr['pdesc']) ? $rr['pdesc'] . '<br />' : '');

			$details = '';
			if (strlen($rr['locality'])) {
				$details .= $rr['locality'];
			}

			if (strlen($rr['region'])) {
				if (strlen($rr['locality'])) {
					$details .= ', ';
				}
				$details .= $rr['region'];
			}
			if (strlen($rr['country-name'])) {
				if (strlen($details)) {
					$details .= ', ';
				}
				$details .= $rr['country-name'];
			}

			if (strlen($rr['dob']) && ($years = age($rr['dob'], $rr['timezone'], '')) != 0) {
				$details .= '<br />' . t('Age: ') . $years;
			}

			if (strlen($rr['gender'])) {
				$details .= '<br />' . t('Gender: ') . $rr['gender'];
			}

			switch ($rr['page-flags']) {
				case PAGE_NORMAL   : $page_type = "Personal Profile"; break;
				case PAGE_SOAPBOX  : $page_type = "Fan Page"        ; break;
				case PAGE_COMMUNITY: $page_type = "Community Forum" ; break;
				case PAGE_FREELOVE : $page_type = "Open Forum"      ; break;
				case PAGE_PRVGROUP : $page_type = "Private Group"   ; break;
			}

			$profile = $rr;

			$location = '';
			if (x($profile, 'address') == 1
				|| x($profile, 'locality') == 1
				|| x($profile, 'region') == 1
				|| x($profile, 'postal-code') == 1
				|| x($profile, 'country-name') == 1
			) {
				$location = t('Location:');
			}

			$gender   = x($profile, 'gender')   == 1 ? t('Gender:')   : false;
			$marital  = x($profile, 'marital')  == 1 ? t('Status:')   : false;
			$homepage = x($profile, 'homepage') == 1 ? t('Homepage:') : false;
			$about    = x($profile, 'about')    == 1 ? t('About:')    : false;

#			$tpl = file_get_contents( dirname(__file__).'/forumdirectory_item.tpl');
			$tpl = get_markup_template('forumdirectory_item.tpl', 'addon/forumdirectory/');

			$entry = replace_macros($tpl, [
				'$id'           => $rr['id'],
				'$profile_link' => $profile_link,
				'$photo'        => $rr[$photo],
				'$alt_text'     => $rr['name'],
				'$name'         => $rr['name'],
				'$details'      => $pdesc . $details,
				'$page_type'    => $page_type,
				'$profile'      => $profile,
				'$location'     => $location,
				'$gender'       => $gender,
				'$pdesc'        => $pdesc,
				'$marital'      => $marital,
				'$homepage'     => $homepage,
				'$about'        => $about,
			]);

			$o .= $entry;
		}

		$o .= "<div class=\"directory-end\" ></div>\r\n";
		$o .= paginate($a);
	} else {
		info(t("No entries \x28some entries may be hidden\x29.") . EOL);
	}

	return $o;
}
