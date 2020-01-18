<?php
/**
 * Name: Forum Directory
 * Description: Add a directory of forums hosted on your server, with verbose descriptions.
 * Version: 1.1
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
use Friendica\DI;
use Friendica\Model\Profile;
use Friendica\Util\Strings;

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
	$b['app_menu'][] = '<div class="app-title"><a href="forumdirectory">' . DI::l10n()->t('Forum Directory') . '</a></div>';
}

function forumdirectory_init(App $a)
{
	if (local_user()) {
		DI::page()['aside'] .= Widget::findPeople();
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
		notice(DI::l10n()->t('Public access denied.') . EOL);
		return;
	}

	$o = '';
	$entries = [];

	Nav::setSelected('directory');

	if (!empty($a->data['search'])) {
		$search = Strings::escapeTags(trim($a->data['search']));
	} else {
		$search = (!empty($_GET['search']) ? Strings::escapeTags(trim(rawurldecode($_GET['search']))) : '');
	}

	$gdirpath = '';
	$dirurl = Config::get('system', 'directory');
	if (strlen($dirurl)) {
		$gdirpath = Profile::zrl($dirurl, true);
	}

	$sql_extra = '';
	if (strlen($search)) {
		$search = DBA::escape($search);

		$sql_extra = " AND ((`profile`.`name` LIKE '%$search%') OR
				(`user`.`nickname` LIKE '%$search%') OR
				(`profile`.`pdesc` LIKE '%$search%') OR
				(`profile`.`locality` LIKE '%$search%') OR
				(`profile`.`region` LIKE '%$search%') OR
				(`profile`.`country-name` LIKE '%$search%') OR
				(`profile`.`gender` LIKE '%$search%') OR
				(`profile`.`marital` LIKE '%$search%') OR
				(`profile`.`sexual` LIKE '%$search%') OR
				(`profile`.`about` LIKE '%$search%') OR
				(`profile`.`romance` LIKE '%$search%') OR
				(`profile`.`work` LIKE '%$search%') OR
				(`profile`.`education` LIKE '%$search%') OR
				(`profile`.`pub_keywords` LIKE '%$search%') OR
				(`profile`.`prv_keywords` LIKE '%$search%'))";
	}

	$publish = Config::get('system', 'publish_all') ? '' : " AND `publish` = 1 ";

	$total = 0;
	$cnt = DBA::fetchFirst("SELECT COUNT(*) AS `total` FROM `profile`
				LEFT JOIN `user` ON `user`.`uid` = `profile`.`uid`
				WHERE `is-default` $publish AND NOT `user`.`blocked` AND NOT `user`.`account_removed` `user`.`page-flags` = 2 $sql_extra");
	if (DBA::isResult($cnt)) {
		$total = $cnt['total'];
	}

	$pager = new Pager(DI::args()->getQueryString(), 60);

	$order = " ORDER BY `name` ASC ";

	$limit = $pager->getStart()."," . $pager->getItemsPerPage();

	$r = DBA::p("SELECT `profile`.*, `profile`.`uid` AS `profile_uid`, `user`.`nickname`, `user`.`timezone` , `user`.`page-flags`,
			`contact`.`addr`, `contact`.`url` AS `profile_url` FROM `profile`
			LEFT JOIN `user` ON `user`.`uid` = `profile`.`uid`
			LEFT JOIN `contact` ON `contact`.`uid` = `user`.`uid`
			WHERE `is-default` $publish AND NOT `user`.`blocked` AND NOT `user`.`account_removed` AND `user`.`page-flags` = 2 AND `contact`.`self`
			$sql_extra $order LIMIT $limit"
	);

	if (DBA::isResult($r)) {
		if (in_array('small', $a->argv)) {
			$photo = 'thumb';
		} else {
			$photo = 'photo';
		}

		while ($rr = DBA::fetch($r)) {
			$entries[] = Friendica\Module\Directory::formatEntry($rr, $photo);
		}
		DBA::close($r);
	} else {
		info(DI::l10n()->t("No entries \x28some entries may be hidden\x29.") . EOL);
	}

	$tpl = Renderer::getMarkupTemplate('directory_header.tpl');
	$o .= Renderer::replaceMacros($tpl, [
		'$search'     => $search,
		'$globaldir'  => DI::l10n()->t('Global Directory'),
		'$gdirpath'   => $gdirpath,
		'$desc'       => DI::l10n()->t('Find on this site'),
		'$contacts'   => $entries,
		'$finding'    => DI::l10n()->t('Results for:'),
		'$findterm'   => (strlen($search) ? $search : ""),
		'$title'      => DI::l10n()->t('Forum Directory'),
		'$search_mod' => 'forumdirectory',
		'$submit'     => DI::l10n()->t('Find'),
		'$paginate'   => $pager->renderFull($total),
	]);

	return $o;
}
