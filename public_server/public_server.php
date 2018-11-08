<?php
/**
 * Name: public_server
 * Description: Friendica addon with functions suitable for a public server.
 * Version: 1.1
 * Author: Keith Fernie <http://friendika.me4.it/profile/keith>
 */

use Friendica\App;
use Friendica\BaseModule;
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Strings;

function public_server_install()
{
	Addon::registerHook('load_config',      'addon/public_server/public_server.php', 'public_server_load_config');
	Addon::registerHook('register_account', 'addon/public_server/public_server.php', 'public_server_register_account');
	Addon::registerHook('cron', 'addon/public_server/public_server.php', 'public_server_cron');
	Addon::registerHook('enotify', 'addon/public_server/public_server.php', 'public_server_enotify');
	Addon::registerHook('logged_in', 'addon/public_server/public_server.php', 'public_server_login');
}

function public_server_uninstall()
{
	Addon::unregisterHook('load_config',      'addon/public_server/public_server.php', 'public_server_load_config');
	Addon::unregisterHook('register_account', 'addon/public_server/public_server.php', 'public_server_register_account');
	Addon::unregisterHook('cron', 'addon/public_server/public_server.php', 'public_server_cron');
	Addon::unregisterHook('enotify', 'addon/public_server/public_server.php', 'public_server_enotify');
	Addon::unregisterHook('logged_in', 'addon/public_server/public_server.php', 'public_server_login');
}

function public_server_load_config(App $a)
{
	$a->loadConfigFile(__DIR__. '/config/public_server.ini.php');
}

function public_server_register_account($a, $b)
{
	$uid = $b;

	$days = Config::get('public_server', 'expiredays');
	$days_posts = Config::get('public_server', 'expireposts');
	if (!$days) {
		return;
	}

	$fields = ['account_expires_on' => DateTimeFormat::utc('now +' . $days . ' days'), 'expire' => $days_posts];
	DBA::update('user', $fields, ['uid' => $uid]);
}

function public_server_cron($a, $b)
{
	Logger::log("public_server: cron start");

	require_once('include/enotify.php');
	$r = q("SELECT * FROM `user` WHERE `account_expires_on` < UTC_TIMESTAMP() + INTERVAL 5 DAY AND
		`account_expires_on` > '%s' AND
		`expire_notification_sent` <= '%s'",
		DBA::NULL_DATETIME, DBA::NULL_DATETIME);

	if (DBA::isResult($r)) {
		foreach ($r as $rr) {
			notification([
				'uid' => $rr['uid'],
				'type' => NOTIFY_SYSTEM,
				'system_type' => 'public_server_expire',
				'language'     => $rr['language'],
				'to_name'      => $rr['username'],
				'to_email'     => $rr['email'],
				'source_name'  => L10n::t('Administrator'),
				'source_link'  => $a->getBaseURL(),
				'source_photo' => $a->getBaseURL() . '/images/person-80.jpg',
			]);

			$fields = ['expire_notification_sent' => DateTimeFormat::utcNow()];
			DBA::update('user', $fields, ['uid' => $rr['uid']]);
		}
	}

	$nologin = Config::get('public_server', 'nologin', false);
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

	$flagusers = Config::get('public_server', 'flagusers', false);
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

	$flagposts = Config::get('public_server', 'flagposts');
	$flagpostsexpire = Config::get('public_server', 'flagpostsexpire');
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
	if (x($b, 'params') && $b['params']['type'] == NOTIFY_SYSTEM
		&& x($b['params'], 'system_type') && $b['params']['system_type'] === 'public_server_expire') {
		$b['itemlink'] = $a->getBaseURL();
		$b['epreamble'] = $b['preamble'] = L10n::t('Your account on %s will expire in a few days.', Config::get('system', 'sitename'));
		$b['subject'] = L10n::t('Your Friendica account is about to expire.');
		$b['body'] = L10n::t("Hi %1\$s,\n\nYour account on %2\$s will expire in less than five days. You may keep your account by logging in at least once every 30 days", $b['params']['to_name'], "[url=" . Config::get('system', 'url') . "]" . Config::get('config', 'sitename') . "[/url]");
	}
}

function public_server_login($a, $b)
{
	$days = Config::get('public_server', 'expiredays');
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
	$expiredays = (x($_POST, 'expiredays') ? Strings::removeTags(trim($_POST['expiredays'])) : '');
	$expireposts = (x($_POST, 'expireposts') ? Strings::removeTags(trim($_POST['expireposts'])) : '');
	$nologin = (x($_POST, 'nologin') ? Strings::removeTags(trim($_POST['nologin'])) : '');
	$flagusers = (x($_POST, 'flagusers') ? Strings::removeTags(trim($_POST['flagusers'])) : '');
	$flagposts = (x($_POST, 'flagposts') ? Strings::removeTags(trim($_POST['flagposts'])) : '');
	$flagpostsexpire = (x($_POST, 'flagpostsexpire') ? Strings::removeTags(trim($_POST['flagpostsexpire'])) : '');
	Config::set('public_server', 'expiredays', $expiredays);
	Config::set('public_server', 'expireposts', $expireposts);
	Config::set('public_server', 'nologin', $nologin);
	Config::set('public_server', 'flagusers', $flagusers);
	Config::set('public_server', 'flagposts', $flagposts);
	Config::set('public_server', 'flagpostsexpire', $flagpostsexpire);
	info(L10n::t('Settings saved').EOL);
}

function public_server_addon_admin(&$a, &$o)
{
	$token = BaseModule::getFormSecurityToken("publicserver");
	$t = Renderer::getMarkupTemplate("admin.tpl", "addon/public_server");
	$o = Renderer::replaceMacros($t, [
		'$submit' => L10n::t('Save Settings'),
		'$form_security_token' => $token,
		'$infotext' => L10n::t('Set any of these options to 0 to deactivate it.'),
		'$expiredays' => ["expiredays","Expire Days", intval(Config::get('public_server', 'expiredays')), "When an account is created on the site, it is given a hard "],
		'$expireposts' => ["expireposts", "Expire Posts", intval(Config::get('public_server', 'expireposts')), "Set the default days for posts to expire here"],
		'$nologin' => ["nologin", "No Login", intval(Config::get('public_server', 'nologin')), "Remove users who have never logged in after nologin days "],
		'$flagusers' => ["flagusers", "Flag users", intval(Config::get('public_server', 'flagusers')), "Remove users who last logged in over flagusers days ago"],
		'$flagposts' => ["flagposts", "Flag posts", intval(Config::get('public_server', 'flagposts')), "For users who last logged in over flagposts days ago set post expiry days to flagpostsexpire "],
		'$flagpostsexpire' => ["flagpostsexpire", "Flag posts expire", intval(Config::get('public_server', 'flagpostsexpire'))],
	]);
}
