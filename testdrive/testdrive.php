<?php
/**
 * Name: testdrive
 * Description: Sample Friendica addon for creating a test drive Friendica site with automatic account expiration.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */

use Friendica\App;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Database\DBA;
use Friendica\Model\User;
use Friendica\Util\DateTimeFormat;

function testdrive_install() {

	Hook::register('load_config',      'addon/testdrive/testdrive.php', 'testdrive_load_config');
	Hook::register('register_account', 'addon/testdrive/testdrive.php', 'testdrive_register_account');
	Hook::register('cron', 'addon/testdrive/testdrive.php', 'testdrive_cron');
	Hook::register('enotify','addon/testdrive/testdrive.php', 'testdrive_enotify');
	Hook::register('globaldir_update','addon/testdrive/testdrive.php', 'testdrive_globaldir_update');

}


function testdrive_uninstall() {

	Hook::unregister('load_config',      'addon/testdrive/testdrive.php', 'testdrive_load_config');
	Hook::unregister('register_account', 'addon/testdrive/testdrive.php', 'testdrive_register_account');
	Hook::unregister('cron', 'addon/testdrive/testdrive.php', 'testdrive_cron');
	Hook::unregister('enotify','addon/testdrive/testdrive.php', 'testdrive_enotify');
	Hook::unregister('globaldir_update','addon/testdrive/testdrive.php', 'testdrive_globaldir_update');

}

function testdrive_load_config(App $a, Config\Cache\ConfigCacheLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('testdrive'));
}

function testdrive_globaldir_update($a,&$b) {
	$b['url'] = '';
}

function testdrive_register_account($a,$b) {

	$uid = $b;

	$days = Config::get('testdrive','expiredays');
	if(! $days)
		return;

	$r = q("UPDATE user set account_expires_on = '%s' where uid = %d",
		DBA::escape(DateTimeFormat::convert('now +' . $days . ' days')),
		intval($uid)
	);

};


function testdrive_cron($a,$b) {
	$r = q("select * from user where account_expires_on < UTC_TIMESTAMP() + INTERVAL 5 DAY and
		expire_notification_sent = '0000-00-00 00:00:00' ");

	if(count($r)) {
		foreach($r as $rr) {
			notification([
				'uid' => $rr['uid'],
				'type' => NOTIFY_SYSTEM,
				'system_type' => 'testdrive_expire',
				'language'     => $rr['language'],
				'to_name'      => $rr['username'],
				'to_email'     => $rr['email'],
				'source_name'  => L10n::t('Administrator'),
				'source_link'  => $a->getBaseURL(),
				'source_photo' => $a->getBaseURL() . '/images/person-80.jpg',
			]);

			q("update user set expire_notification_sent = '%s' where uid = %d",
				DBA::escape(DateTimeFormat::utcNow()),
				intval($rr['uid'])
			);

		}
	}

	$r = q("select * from user where account_expired = 1 and account_expires_on < UTC_TIMESTAMP() - INTERVAL 5 DAY ");
	if(count($r)) {
		foreach($r as $rr) {
			User::remove($rr['uid']);
		}
	}
}

function testdrive_enotify(&$a, &$b) {
    if (!empty($b['params']) && $b['params']['type'] == NOTIFY_SYSTEM
		&& !empty($b['params']['system_type']) && $b['params']['system_type'] === 'testdrive_expire') {
        $b['itemlink'] = $a->getBaseURL();
        $b['epreamble'] = $b['preamble'] = L10n::t('Your account on %s will expire in a few days.', Config::get('system', 'sitename'));
        $b['subject'] = L10n::t('Your Friendica test account is about to expire.');
        $b['body'] = L10n::t("Hi %1\$s,\n\nYour test account on %2\$s will expire in less than five days. We hope you enjoyed this test drive and use this opportunity to find a permanent Friendica website for your integrated social communications. A list of public sites is available at %s/siteinfo - and for more information on setting up your own Friendica server please see the Friendica project website at https://friendi.ca.", $b['params']['to_name'], "[url=".Config::get('system', 'url')."]".Config::get('config', 'sitename')."[/url]", get_server());
    }
}
