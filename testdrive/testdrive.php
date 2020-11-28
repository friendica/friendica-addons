<?php
/**
 * Name: testdrive
 * Description: Sample Friendica addon for creating a test drive Friendica site with automatic account expiration.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Search;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Notify\Type;
use Friendica\Model\User;
use Friendica\Util\ConfigFileLoader;
use Friendica\Util\DateTimeFormat;

function testdrive_install() {

	Hook::register('load_config',      'addon/testdrive/testdrive.php', 'testdrive_load_config');
	Hook::register('register_account', 'addon/testdrive/testdrive.php', 'testdrive_register_account');
	Hook::register('cron', 'addon/testdrive/testdrive.php', 'testdrive_cron');
	Hook::register('enotify','addon/testdrive/testdrive.php', 'testdrive_enotify');
	Hook::register('globaldir_update','addon/testdrive/testdrive.php', 'testdrive_globaldir_update');

}

function testdrive_load_config(App $a, ConfigFileLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('testdrive'));
}

function testdrive_globaldir_update($a,&$b) {
	$b['url'] = '';
}

function testdrive_register_account($a,$b) {

	$uid = $b;

	$days = DI::config()->get('testdrive','expiredays');
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
				'type' => Type::SYSTEM,
				'uid' => $rr['uid'],
				'system_type' => 'testdrive_expire',
				'source_name'  => DI::l10n()->t('Administrator'),
				'source_link'  => DI::baseUrl()->get(),
				'source_photo' => DI::baseUrl()->get() . '/images/person-80.jpg',
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
    if (!empty($b['params']) && $b['params']['type'] == Type::SYSTEM
		&& !empty($b['params']['system_type']) && $b['params']['system_type'] === 'testdrive_expire') {
        $b['itemlink'] = DI::baseUrl()->get();
        $b['epreamble'] = $b['preamble'] = DI::l10n()->t('Your account on %s will expire in a few days.', DI::config()->get('system', 'sitename'));
        $b['subject'] = DI::l10n()->t('Your Friendica test account is about to expire.');
        $b['body'] = DI::l10n()->t("Hi %1\$s,\n\nYour test account on %2\$s will expire in less than five days. We hope you enjoyed this test drive and use this opportunity to find a permanent Friendica website for your integrated social communications. A list of public sites is available at %s/siteinfo - and for more information on setting up your own Friendica server please see the Friendica project website at https://friendi.ca.", $b['params']['to_name'], "[url=".DI::config()->get('system', 'url')."]".DI::config()->get('config', 'sitename')."[/url]", Search::getGlobalDirectory());
    }
}
