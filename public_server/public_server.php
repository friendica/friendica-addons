<?php
/**
 * Name: public_server
 * Description: Friendica addon with functions suitable for a public server.
 * Version: 1.1
 * Author: Keith Fernie <http://friendika.me4.it/profile/keith>
 */

use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Database\DBM;
use Friendica\Util\DateTimeFormat;

function public_server_install()
{
	Addon::registerHook('register_account', 'addon/public_server/public_server.php', 'public_server_register_account');
	Addon::registerHook('cron', 'addon/public_server/public_server.php', 'public_server_cron');
	Addon::registerHook('enotify', 'addon/public_server/public_server.php', 'public_server_enotify');
	Addon::registerHook('logged_in', 'addon/public_server/public_server.php', 'public_server_login');
}

function public_server_uninstall()
{
	Addon::unregisterHook('register_account', 'addon/public_server/public_server.php', 'public_server_register_account');
	Addon::unregisterHook('cron', 'addon/public_server/public_server.php', 'public_server_cron');
	Addon::unregisterHook('enotify', 'addon/public_server/public_server.php', 'public_server_enotify');
	Addon::unregisterHook('logged_in', 'addon/public_server/public_server.php', 'public_server_login');
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
	dba::update('user', $fields, ['uid' => $uid]);
}

function public_server_cron($a, $b)
{
	logger("public_server: cron start");

	require_once('include/enotify.php');
	$r = q("SELECT * FROM `user` WHERE `account_expires_on` < UTC_TIMESTAMP() + INTERVAL 5 DAY AND
		`account_expires_on` > '%s' AND
		`expire_notification_sent` <= '%s'",
		dbesc(NULL_DATE), dbesc(NULL_DATE));

	if (DBM::is_result($r)) {
		foreach ($r as $rr) {
			notification([
				'uid' => $rr['uid'],
				'type' => NOTIFY_SYSTEM,
				'system_type' => 'public_server_expire',
				'language'     => $rr['language'],
				'to_name'      => $rr['username'],
				'to_email'     => $rr['email'],
				'source_name'  => L10n::t('Administrator'),
				'source_link'  => $a->get_baseurl(),
				'source_photo' => $a->get_baseurl() . '/images/person-80.jpg',
			));

			$fields = ['expire_notification_sent' => DateTimeFormat::utcNow()];
			dba::update('user', $fields, ['uid' => $rr['uid']]);
		}
	}

	$nologin = Config::get('public_server', 'nologin', false);
	if ($nologin) {
		$r = q("SELECT `uid` FROM `user` WHERE NOT `account_expired` AND `login_date` <= '%s' AND `register_date` < UTC_TIMESTAMP() - INTERVAL %d DAY AND `account_expires_on` <= '%s'",
			dbesc(NULL_DATE), intval($nologin), dbesc(NULL_DATE));
		if (DBM::is_result($r)) {
			foreach ($r as $rr) {
				$fields = ['account_expires_on' => DateTimeFormat::utc('now +6 days')];
				dba::update('user', $fields, ['uid' => $rr['uid']]);
			}
		}
	}

	$flagusers = Config::get('public_server', 'flagusers', false);
	if ($flagusers) {
		$r = q("SELECT `uid` FROM `user` WHERE NOT `account_expired` AND `login_date` < UTC_TIMESTAMP() - INTERVAL %d DAY AND `account_expires_on` <= '%s' AND `page-flags` = 0",
			intval($flagusers), dbesc(NULL_DATE));
		if (DBM::is_result($r)) {
			foreach ($r as $rr) {
				$fields = ['account_expires_on' => DateTimeFormat::utc('now +6 days')];
				dba::update('user', $fields, ['uid' => $rr['uid']]);
			}
		}
	}

	$flagposts = Config::get('public_server', 'flagposts');
	$flagpostsexpire = Config::get('public_server', 'flagpostsexpire');
	if ($flagposts && $flagpostsexpire) {
		$r = q("SELECT `uid` FROM `user` WHERE NOT `account_expired` AND `login_date` < UTC_TIMESTAMP() - INTERVAL %d DAY AND `account_expires_on` <= '%s' and `expire` = 0 AND `page-flags` = 0",
			intval($flagposts), dbesc(NULL_DATE));
		if (DBM::is_result($r)) {
			foreach ($r as $rr) {
				dba::update('user', ['expire' => $flagpostsexpire], ['uid' => $rr['uid']]);
			}
		}
	}

	logger("public_server: cron end");
}

function public_server_enotify(&$a, &$b)
{
	if (x($b, 'params') && $b['params']['type'] == NOTIFY_SYSTEM
		&& x($b['params'], 'system_type') && $b['params']['system_type'] === 'public_server_expire') {
		$b['itemlink'] = $a->get_baseurl();
		$b['epreamble'] = $b['preamble'] = L10n::t('Your account on %s will expire in a few days.', Config::get('system', 'sitename'));
		$b['subject'] = L10n::t('Your Friendica account is about to expire.');
		$b['body'] = L10n::t("Hi %1\$s,\n\nYour account on %2\$s will expire in less than five days. You may keep your account by logging in at least once every 30 days", $b['params']['to_name'], "[url=" . $app->config["system"]["url"] . "]" . $app->config["sitename"] . "[/url]");
	}
}

function public_server_login($a, $b)
{
	$days = Config::get('public_server', 'expiredays');
	if (!$days) {
		return;
	}

	$fields = ['account_expires_on' => DateTimeFormat::utc('now +' . $days . ' days')];
	$condition = ["`uid` = ? AND `account_expires_on` > ?", local_user(), NULL_DATE];
	dba::update('user', $fields, $condition);
}

function public_server_addon_admin_post(&$a)
{
	check_form_security_token_redirectOnErr('/admin/addons/publicserver', 'publicserver');
	$expiredays = (x($_POST, 'expiredays') ? notags(trim($_POST['expiredays'])) : '');
	$expireposts = (x($_POST, 'expireposts') ? notags(trim($_POST['expireposts'])) : '');
	$nologin = (x($_POST, 'nologin') ? notags(trim($_POST['nologin'])) : '');
	$flagusers = (x($_POST, 'flagusers') ? notags(trim($_POST['flagusers'])) : '');
	$flagposts = (x($_POST, 'flagposts') ? notags(trim($_POST['flagposts'])) : '');
	$flagpostsexpire = (x($_POST, 'flagpostsexpire') ? notags(trim($_POST['flagpostsexpire'])) : '');
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
	$token = get_form_security_token("publicserver");
	$t = get_markup_template("admin.tpl", "addon/public_server");
	$o = replace_macros($t, [
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
