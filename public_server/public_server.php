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
use Friendica\Util\ConfigFileLoader;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Strings;

function public_server_install()
{
	Hook::register('load_config',      'addon/public_server/public_server.php', 'public_server_load_config');
	Hook::register('register_account', 'addon/public_server/public_server.php', 'public_server_register_account');
	Hook::register('cron', 'addon/public_server/public_server.php', 'public_server_cron');
	Hook::register('enotify', 'addon/public_server/public_server.php', 'public_server_enotify');
	Hook::register('logged_in', 'addon/public_server/public_server.php', 'public_server_login');
}

function public_server_load_config(App $a, ConfigFileLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('public_server'));
}

function public_server_register_account($a, $b)
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

function public_server_cron($a, $b)
{
	Logger::log("public_server: cron start");

	$r = q("SELECT * FROM `user` WHERE `account_expires_on` < UTC_TIMESTAMP() + INTERVAL 5 DAY AND
		`account_expires_on` > '%s' AND
		`expire_notification_sent` <= '%s'",
		DBA::NULL_DATETIME, DBA::NULL_DATETIME);

	if (DBA::isResult($r)) {
		foreach ($r as $rr) {
			notification([
				'type' => Notification/Type::SYSTEM,
				'uid' => $rr['uid'],
				'system_type' => 'public_server_expire',
				'source_name'  => DI::l10n()->t('Administrator'),
				'source_link'  => DI::baseUrl()->get(),
				'source_photo' => DI::baseUrl()->get() . '/images/person-80.jpg',
			]);

			$fields = ['expire_notification_sent' => DateTimeFormat::utcNow()];
			DBA::update('user', $fields, ['uid' => $rr['uid']]);
		}
	}

	$nologin = DI::config()->get('public_server', 'nologin', false);
	if ($nologin) {
		$r = q("SELECT `uid` FROM `user` WHERE NOT `account_expired` AND `login_date` <= '%s' AND `register_date` < UTC_TIMESTAMP() - INTERVAL %d DAY AND `account_expires_on` <= '%s'",
			DBA::NULL_DATETIME, intval($nologin), DBA::NULL_DATETIME);
		if (DBA::isResult($r)) {
			foreach ($r as $rr) {
				$fields = ['account_expires_on' => DateTimeFormat::utc('now +6 days')];
				DBA::update('user', $fields, ['uid' => $rr['uid']]);
			}
		}
	}

	$flagusers = DI::config()->get('public_server', 'flagusers', false);
	if ($flagusers) {
		$r = q("SELECT `uid` FROM `user` WHERE NOT `account_expired` AND `login_date` < UTC_TIMESTAMP() - INTERVAL %d DAY AND `account_expires_on` <= '%s' AND `page-flags` = 0",
			intval($flagusers), DBA::NULL_DATETIME);
		if (DBA::isResult($r)) {
			foreach ($r as $rr) {
				$fields = ['account_expires_on' => DateTimeFormat::utc('now +6 days')];
				DBA::update('user', $fields, ['uid' => $rr['uid']]);
			}
		}
	}

	$flagposts = DI::config()->get('public_server', 'flagposts');
	$flagpostsexpire = DI::config()->get('public_server', 'flagpostsexpire');
	if ($flagposts && $flagpostsexpire) {
		$r = q("SELECT `uid` FROM `user` WHERE NOT `account_expired` AND `login_date` < UTC_TIMESTAMP() - INTERVAL %d DAY AND `account_expires_on` <= '%s' and `expire` = 0 AND `page-flags` = 0",
			intval($flagposts), DBA::NULL_DATETIME);
		if (DBA::isResult($r)) {
			foreach ($r as $rr) {
				DBA::update('user', ['expire' => $flagpostsexpire], ['uid' => $rr['uid']]);
			}
		}
	}

	Logger::log("public_server: cron end");
}

function public_server_enotify(&$a, &$b)
{
	if (!empty($b['params']) && $b['params']['type'] == Type::SYSTEM
		&& !empty($b['params']['system_type']) && $b['params']['system_type'] === 'public_server_expire') {
		$b['itemlink'] = DI::baseUrl()->get();
		$b['epreamble'] = $b['preamble'] = DI::l10n()->t('Your account on %s will expire in a few days.', DI::config()->get('system', 'sitename'));
		$b['subject'] = DI::l10n()->t('Your Friendica account is about to expire.');
		$b['body'] = DI::l10n()->t("Hi %1\$s,\n\nYour account on %2\$s will expire in less than five days. You may keep your account by logging in at least once every 30 days", $b['params']['to_name'], "[url=" . DI::config()->get('system', 'url') . "]" . DI::config()->get('config', 'sitename') . "[/url]");
	}
}

function public_server_login($a, $b)
{
	$days = DI::config()->get('public_server', 'expiredays');
	if (!$days) {
		return;
	}

	$fields = ['account_expires_on' => DateTimeFormat::utc('now +' . $days . ' days')];
	$condition = ["`uid` = ? AND `account_expires_on` > ?", local_user(), DBA::NULL_DATETIME];
	DBA::update('user', $fields, $condition);
}

function public_server_addon_admin_post(&$a)
{
	BaseModule::checkFormSecurityTokenRedirectOnError('/admin/addons/publicserver', 'publicserver');
	$expiredays = (!empty($_POST['expiredays']) ? Strings::escapeTags(trim($_POST['expiredays'])) : '');
	$expireposts = (!empty($_POST['expireposts']) ? Strings::escapeTags(trim($_POST['expireposts'])) : '');
	$nologin = (!empty($_POST['nologin']) ? Strings::escapeTags(trim($_POST['nologin'])) : '');
	$flagusers = (!empty($_POST['flagusers']) ? Strings::escapeTags(trim($_POST['flagusers'])) : '');
	$flagposts = (!empty($_POST['flagposts']) ? Strings::escapeTags(trim($_POST['flagposts'])) : '');
	$flagpostsexpire = (!empty($_POST['flagpostsexpire']) ? Strings::escapeTags(trim($_POST['flagpostsexpire'])) : '');
	DI::config()->set('public_server', 'expiredays', $expiredays);
	DI::config()->set('public_server', 'expireposts', $expireposts);
	DI::config()->set('public_server', 'nologin', $nologin);
	DI::config()->set('public_server', 'flagusers', $flagusers);
	DI::config()->set('public_server', 'flagposts', $flagposts);
	DI::config()->set('public_server', 'flagpostsexpire', $flagpostsexpire);
}

function public_server_addon_admin(&$a, &$o)
{
	$token = BaseModule::getFormSecurityToken("publicserver");
	$t = Renderer::getMarkupTemplate("admin.tpl", "addon/public_server");
	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$form_security_token' => $token,
		'$infotext' => DI::l10n()->t('Set any of these options to 0 to deactivate it.'),
		'$expiredays' => ["expiredays","Expire Days", intval(DI::config()->get('public_server', 'expiredays')), "When an account is created on the site, it is given a hard "],
		'$expireposts' => ["expireposts", "Expire Posts", intval(DI::config()->get('public_server', 'expireposts')), "Set the default days for posts to expire here"],
		'$nologin' => ["nologin", "No Login", intval(DI::config()->get('public_server', 'nologin')), "Remove users who have never logged in after nologin days "],
		'$flagusers' => ["flagusers", "Flag users", intval(DI::config()->get('public_server', 'flagusers')), "Remove users who last logged in over flagusers days ago"],
		'$flagposts' => ["flagposts", "Flag posts", intval(DI::config()->get('public_server', 'flagposts')), "For users who last logged in over flagposts days ago set post expiry days to flagpostsexpire "],
		'$flagpostsexpire' => ["flagpostsexpire", "Flag posts expire", intval(DI::config()->get('public_server', 'flagpostsexpire'))],
	]);
}
