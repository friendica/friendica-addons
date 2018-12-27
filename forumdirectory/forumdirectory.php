<?php
/**
 * Name: Forum Directory
 * Description: Add a directory of forums hosted on your server, with verbose descriptions.
 * Version: 1.0
 * Author: Thomas Willingham <https://beardyunixer.com/profile/beardyunixer>
 */

use Friendica\App;
use Friendica\Content\Nav;
use Friendica\Content\Pager;
use Friendica\Content\Widget;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\Model\Contact;
use Friendica\Model\Profile;
use Friendica\Util\Strings;
use Friendica\Util\Temporal;

require_once 'boot.php';
require_once 'include/dba.php';
require_once 'include/text.php';

function forumdirectory_install()
{
	Hook::register('app_menu', 'addon/forumdirectory/forumdirectory.php', 'forumdirectory_app_menu');
}

function forumdirectory_uninstall()
{
	Hook::unregister('app_menu', 'addon/forumdirectory/forumdirectory.php', 'forumdirectory_app_menu');
}

function forumdirectory_module()
{
	return;
}

function forumdirectory_app_menu(App $a, array &$b)
{
	$b['app_menu'][] = '<div class="app-title"><a href="forumdirectory">' . L10n::t('Forum Directory') . '</a></div>';
}

function forumdirectory_init(App $a)
{
	$a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="' . $a->getBaseURL() . '/addon/forumdirectory/forumdirectory.css" media="all" />';

	if (local_user()) {
		$a->page['aside'] .= Widget::findPeople();
	} else {
		unset($_SESSION['theme']);
	}
}

function forumdirectory_post(App $a)
{
	if (!empty($_POST['search'])) {
		$a->data['search'] = $_POST['search'];
	}
}

function forumdirectory_content(App $a)
{
	if ((Config::get('system', 'block_public')) && (!local_user()) && (!remote_user())) {
		notice(L10n::t('Public access denied.') . EOL);
		return;
	}

	$o = '';
	Nav::setSelected('directory');

	if (!empty($a->data['search'])) {
		$search = Strings::escapeTags(trim($a->data['search']));
	} else {
		$search = (!empty($_GET['search']) ? Strings::escapeTags(trim(rawurldecode($_GET['search']))) : '');
	}

	$tpl = Renderer::getMarkupTemplate('directory_header.tpl');

	$globaldir = '';
	$gdirpath = Config::get('system', 'directory');
	if (strlen($gdirpath)) {
		$globaldir = '<ul><li><div id="global-directory-link"><a href="'
			. Profile::zrl($gdirpath, true) . '">' . L10n::t('Global Directory') . '</a></div></li></ul>';
	}

	$admin = '';

	$o .= Renderer::replaceMacros($tpl, [
		'$search'    => $search,
		'$globaldir' => $globaldir,
		'$desc'      => L10n::t('Find on this site'),
		'$admin'     => $admin,
		'$finding'   => (strlen($search) ? '<h4>' . L10n::t('Finding: ') . "'" . $search . "'" . '</h4>' : ""),
		'$sitedir'   => L10n::t('Site Directory'),
		'$submit'    => L10n::t('Find')
	]);

	$sql_extra = '';
	if (strlen($search)) {
		$sql_extra = " AND MATCH (`profile`.`name`, `user`.`nickname`, `pdesc`, `locality`,`region`,`country-name`,"
			. "`gender`,`marital`,`sexual`,`about`,`romance`,`work`,`education`,`pub_keywords`,`prv_keywords` )"
			. " AGAINST ('" . DBA::escape($search) . "' IN BOOLEAN MODE) ";
	}

	$publish = Config::get('system', 'publish_all') ? '' : " AND `publish` = 1 ";

	$total = 0;
	$r = q("SELECT COUNT(*) AS `total` FROM `profile` LEFT JOIN `user` ON `user`.`uid` = `profile`.`uid`"
		. " WHERE `is-default` = 1 $publish AND `user`.`blocked` = 0 AND `page-flags` = 2 $sql_extra ");
	if (DBA::isResult($r)) {
		$total = $r[0]['total'];
	}

	$pager = new Pager($a->query_string, 60);

	$order = " ORDER BY `name` ASC ";

	$r = q("SELECT `profile`.*, `profile`.`uid` AS `profile_uid`, `user`.`nickname`, `user`.`timezone` , `user`.`page-flags`"
		. " FROM `profile` LEFT JOIN `user` ON `user`.`uid` = `profile`.`uid` WHERE `is-default` = 1 $publish"
		. " AND `user`.`blocked` = 0 AND `page-flags` = 2 $sql_extra $order LIMIT %d , %d ",
		$pager->getStart(),
		$pager->getItemsPerPage()
	);

	if (DBA::isResult($r)) {
		if (in_array('small', $a->argv)) {
			$photo = 'thumb';
		} else {
			$photo = 'photo';
		}

		foreach ($r as $rr) {
			$profile_link = $a->getBaseURL() . '/profile/' . ((strlen($rr['nickname'])) ? $rr['nickname'] : $rr['profile_uid']);

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

			if (strlen($rr['dob']) && ($years = Temporal::getAgeByTimezone($rr['dob'], $rr['timezone'], '')) != 0) {
				$details .= '<br />' . L10n::t('Age: ') . $years;
			}

			if (strlen($rr['gender'])) {
				$details .= '<br />' . L10n::t('Gender: ') . $rr['gender'];
			}

			switch ($rr['page-flags']) {
				case Contact::PAGE_NORMAL   : $page_type = "Personal Profile"; break;
				case Contact::PAGE_SOAPBOX  : $page_type = "Fan Page"        ; break;
				case Contact::PAGE_COMMUNITY: $page_type = "Community Forum" ; break;
				case Contact::PAGE_FREELOVE : $page_type = "Open Forum"      ; break;
				case Contact::PAGE_PRVGROUP : $page_type = "Private Group"   ; break;
			}

			$profile = $rr;

			$location = '';
			if (!empty($profile['address'])
				|| !empty($profile['locality'])
				|| !empty($profile['region'])
				|| !empty($profile['postal-code'])
				|| !empty($profile['country-name'])
			) {
				$location = L10n::t('Location:');
			}

			$gender   = !empty($profile['gender'])   ? L10n::t('Gender:')   : false;
			$marital  = !empty($profile['marital'])  ? L10n::t('Status:')   : false;
			$homepage = !empty($profile['homepage']) ? L10n::t('Homepage:') : false;
			$about    = !empty($profile['about'])    ? L10n::t('About:')    : false;

			$tpl = Renderer::getMarkupTemplate('forumdirectory_item.tpl', 'addon/forumdirectory/');

			$entry = Renderer::replaceMacros($tpl, [
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
		$o .= $pager->renderFull($total);
	} else {
		info(L10n::t("No entries \x28some entries may be hidden\x29.") . EOL);
	}

	return $o;
}
