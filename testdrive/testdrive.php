<?php

/**
 * Name: testdrive
 * Description: Sample Friendica plugin/addon for creating a test drive Friendica site with automatic account expiration.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Model\User;


function testdrive_install() {

	Addon::registerHook('register_account', 'addon/testdrive/testdrive.php', 'testdrive_register_account');
	Addon::registerHook('cron', 'addon/testdrive/testdrive.php', 'testdrive_cron');
	Addon::registerHook('enotify','addon/testdrive/testdrive.php', 'testdrive_enotify');
	Addon::registerHook('globaldir_update','addon/testdrive/testdrive.php', 'testdrive_globaldir_update');

}


function testdrive_uninstall() {

	Addon::unregisterHook('register_account', 'addon/testdrive/testdrive.php', 'testdrive_register_account');
	Addon::unregisterHook('cron', 'addon/testdrive/testdrive.php', 'testdrive_cron');
	Addon::unregisterHook('enotify','addon/testdrive/testdrive.php', 'testdrive_enotify');
	Addon::unregisterHook('globaldir_update','addon/testdrive/testdrive.php', 'testdrive_globaldir_update');

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
		dbesc(datetime_convert('UTC','UTC','now +' . $days . ' days')),
		intval($uid)
	);

};


function testdrive_cron($a,$b) {
	require_once('include/enotify.php');

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
				'source_name'  => t('Administrator'),
				'source_link'  => $a->get_baseurl(),
				'source_photo' => $a->get_baseurl() . '/images/person-80.jpg',
			]);

			q("update user set expire_notification_sent = '%s' where uid = %d",
				dbesc(datetime_convert()),
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
    if (x($b, 'params') && $b['params']['type'] == NOTIFY_SYSTEM
		&& x($b['params'], 'system_type') && $b['params']['system_type'] === 'testdrive_expire') {
        $b['itemlink'] = $a->get_baseurl();
        $b['epreamble'] = $b['preamble'] = sprintf( t('Your account on %s will expire in a few days.'), Config::get('system','sitename'));
        $b['subject'] = t('Your Friendica test account is about to expire.');
        $b['body'] = sprintf( t("Hi %1\$s,\n\nYour test account on %2\$s will expire in less than five days. We hope you enjoyed this test drive and use this opportunity to find a permanent Friendica website for your integrated social communications. A list of public sites is available at %s/siteinfo - and for more information on setting up your own Friendica server please see the Friendica project website at http://friendica.com."), $b['params']['to_name'], "[url=".$app->config["system"]["url"]."]".$app->config["sitename"]."[/url]", get_server());
    }
}
