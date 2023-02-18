<?php
/**
 * Name: public_server
 * Description: Friendica addon with functions suitable for a public server. WARNING: This addon is currently not well maintained. It may produce unexpected results. Use with caution!
 * Version: 1.1
 * Author: Keith Fernie <http://friendika.me4.it/profile/keith>
 */

use Friendica\App;
use Friendica\BaseModule;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Notification;
use Friendica\Model\User;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Util\DateTimeFormat;

function public_server_install()
{
	Hook::register('load_config',      'addon/public_server/public_server.php', 'public_server_load_config');
	Hook::register('register_account', 'addon/public_server/public_server.php', 'public_server_register_account');
	Hook::register('cron', 'addon/public_server/public_server.php', 'public_server_cron');
	Hook::register('enotify', 'addon/public_server/public_server.php', 'public_server_enotify');
	Hook::register('logged_in', 'addon/public_server/public_server.php', 'public_server_login');
}

function public_server_load_config(ConfigFileManager $loader)
{
	DI::app()->getConfigCache()->load($loader->loadAddonConfig('public_server'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function public_server_register_account($b)
{
	$uid = $b;

	$days = DI::config()->get('public_server', 'expiredays');
	$days_posts = DI::config()->get('public_server', 'expireposts');
	if (!$days) {
		return;
	}

	$fields = ['account_expires_on' => DateTimeFormat::utc('now +' . $days . ' days'), 'expire' => $days_posts];
	DBA::update('user', $fields, ['uid' => $uid]);
}

function public_server_cron($b)
{
	Logger::notice("public_server: cron start");

	$users = DBA::selectToArray('user', [], ["`account_expires_on` > ? AND `account_expires_on` < ?
		AND `expire_notification_sent` <= ?", DBA::NULL_DATETIME, DateTimeFormat::utc('now + 5 days'), DBA::NULL_DATETIME]);
	foreach ($users as $rr) {
		DI::notify()->createFromArray([
			'type' => Notification\Type::SYSTEM,
			'event' => 'SYSTEM_PUBLIC_SERVER_EXPIRATION',
			'uid' => $rr['uid'],
			'system_type' => 'public_server_expire',
			'source_name'  => DI::l10n()->t('Administrator'),
			'source_link'  => DI::baseUrl(),
			'source_photo' => DI::baseUrl() . '/images/person-80.jpg',
		]);

		$fields = ['expire_notification_sent' => DateTimeFormat::utcNow()];
		DBA::update('user', $fields, ['uid' => $rr['uid']]);
	}

	$nologin = DI::config()->get('public_server', 'nologin', false);
	if ($nologin) {
		$users = DBA::selectToArray('user', [], ["NOT `account_expired` AND `login_date` <= ? AND `register_date` < ? AND `account_expires_on` <= ?",
			DBA::NULL_DATETIME, DateTimeFormat::utc('now -  ' . (int)$nologin . ' days'), DBA::NULL_DATETIME]);
		foreach ($users as $rr) {
			$fields = ['account_expires_on' => DateTimeFormat::utc('now +6 days')];
			DBA::update('user', $fields, ['uid' => $rr['uid']]);
		}
	}

	$flagusers = DI::config()->get('public_server', 'flagusers', false);
	if ($flagusers) {
		$users = DBA::selectToArray('user', [], ["NOT `account_expired` AND `login_date` < ? AND `account_expires_on` <= ? AND `page-flags` = ?",
            DateTimeFormat::utc('now -  ' . (int)$flagusers . ' days'), DBA::NULL_DATETIME, User::PAGE_FLAGS_NORMAL]);
		foreach ($users as $rr) {
			$fields = ['account_expires_on' => DateTimeFormat::utc('now +6 days')];
			DBA::update('user', $fields, ['uid' => $rr['uid']]);
		}
	}

	$flagposts = DI::config()->get('public_server', 'flagposts');
	$flagpostsexpire = DI::config()->get('public_server', 'flagpostsexpire');
	if ($flagposts && $flagpostsexpire) {
		$users = DBA::selectToArray('user', [], ["NOT `account_expired` AND `login_date` < ? AND `account_expires_on` <= ? AND NOT `expire` AND `page-flags` = ?",
            DateTimeFormat::utc('now -  ' . (int)$flagposts . ' days'), DBA::NULL_DATETIME, User::PAGE_FLAGS_NORMAL]);
		foreach ($users as $rr) {
			DBA::update('user', ['expire' => $flagpostsexpire], ['uid' => $rr['uid']]);
		}
	}

	Logger::notice("public_server: cron end");
}

function public_server_enotify(array &$b)
{
	if (!empty($b['params']) && $b['params']['type'] == Notification\Type::SYSTEM
		&& !empty($b['params']['system_type']) && $b['params']['system_type'] === 'public_server_expire') {
		$b['itemlink'] = DI::baseUrl();
		$b['epreamble'] = $b['preamble'] = DI::l10n()->t('Your account on %s will expire in a few days.', DI::config()->get('system', 'sitename'));
		$b['subject'] = DI::l10n()->t('Your Friendica account is about to expire.');
		$b['body'] = DI::l10n()->t("Hi %1\$s,\n\nYour account on %2\$s will expire in less than five days. You may keep your account by logging in at least once every 30 days", $b['params']['to_name'], "[url=" . DI::config()->get('system', 'url') . "]" . DI::config()->get('config', 'sitename') . "[/url]");
	}
}

function public_server_login($b)
{
	$days = DI::config()->get('public_server', 'expiredays');
	if (!$days) {
		return;
	}

	$fields = ['account_expires_on' => DateTimeFormat::utc('now +' . $days . ' days')];
	$condition = ["`uid` = ? AND `account_expires_on` > ?", DI::userSession()->getLocalUserId(), DBA::NULL_DATETIME];
	DBA::update('user', $fields, $condition);
}

function public_server_addon_admin_post()
{
	BaseModule::checkFormSecurityTokenRedirectOnError('/admin/addons/publicserver', 'publicserver');

	DI::config()->set('public_server', 'expiredays', trim($_POST['expiredays'] ?? ''));
	DI::config()->set('public_server', 'expireposts', trim($_POST['expireposts'] ?? ''));
	DI::config()->set('public_server', 'nologin', trim($_POST['nologin'] ?? ''));
	DI::config()->set('public_server', 'flagusers', trim($_POST['flagusers'] ?? ''));
	DI::config()->set('public_server', 'flagposts', trim($_POST['flagposts'] ?? ''));
	DI::config()->set('public_server', 'flagpostsexpire', trim($_POST['flagpostsexpire'] ?? ''));
}

function public_server_addon_admin(string &$o)
{
	$token = BaseModule::getFormSecurityToken('publicserver');
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/public_server');
	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$form_security_token' => $token,
		'$infotext' => DI::l10n()->t('Set any of these options to 0 to deactivate it.'),
		'$expiredays' => ["expiredays", "Expire Days", intval(DI::config()->get('public_server', 'expiredays')), "When an account is created on the site, it is given a hard "],
		'$expireposts' => ["expireposts", "Expire Posts", intval(DI::config()->get('public_server', 'expireposts')), "Set the default days for posts to expire here"],
		'$nologin' => ["nologin", "No Login", intval(DI::config()->get('public_server', 'nologin')), "Remove users who have never logged in after nologin days "],
		'$flagusers' => ["flagusers", "Flag users", intval(DI::config()->get('public_server', 'flagusers')), "Remove users who last logged in over flagusers days ago"],
		'$flagposts' => ["flagposts", "Flag posts", intval(DI::config()->get('public_server', 'flagposts')), "For users who last logged in over flagposts days ago set post expiry days to flagpostsexpire "],
		'$flagpostsexpire' => ["flagpostsexpire", "Flag posts expire", intval(DI::config()->get('public_server', 'flagpostsexpire'))],
	]);
}
