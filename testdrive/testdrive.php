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
use Friendica\Model\Notification;
use Friendica\Model\User;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Util\DateTimeFormat;

function testdrive_install()
{
	Hook::register('load_config',      'addon/testdrive/testdrive.php', 'testdrive_load_config');
	Hook::register('register_account', 'addon/testdrive/testdrive.php', 'testdrive_register_account');
	Hook::register('cron', 'addon/testdrive/testdrive.php', 'testdrive_cron');
	Hook::register('enotify','addon/testdrive/testdrive.php', 'testdrive_enotify');
	Hook::register('globaldir_update','addon/testdrive/testdrive.php', 'testdrive_globaldir_update');
}

function testdrive_load_config(App $a, ConfigFileManager $configFileManager)
{
	$a->getConfigCache()->load($configFileManager->loadAddonConfig('testdrive'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function testdrive_globaldir_update(App $a, array &$b)
{
	$b['url'] = '';
}

function testdrive_register_account(App $a, $b)
{
	$uid = $b;

	$days = DI::config()->get('testdrive','expiredays');
	if (!$days) {
		return;
	}

	DBA::update('user', ['account_expires_on' => DateTimeFormat::convert('now +' . $days . ' days')], ['uid' => $uid]);
}


function testdrive_cron(App $a, $b)
{
	$users = DBA::selectToArray('user', [], ["`account_expires_on` < ? AND `expire_notification_sent` <= ?",
		DateTimeFormat::utc('now + 5 days'), DBA::NULL_DATETIME]);

	foreach ($users as $rr) {
		DI::notify()->createFromArray([
			'type' => Notification\Type::SYSTEM,
			'uid' => $rr['uid'],
			'system_type' => 'testdrive_expire',
			'source_name'  => DI::l10n()->t('Administrator'),
			'source_link'  => DI::baseUrl()->get(),
			'source_photo' => DI::baseUrl()->get() . '/images/person-80.jpg',
		]);

		DBA::update('user', ['expire_notification_sent' => DateTimeFormat::utcNow()], ['uid' => $rr['uid']]);
	}

	$users = DBA::selectToArray('user', [], ["`account_expired` AND `account_expires_on` < ?", DateTimeFormat::utc('now - 5 days')]);
	foreach($users as $rr) {
		User::remove($rr['uid']);
	}
}

function testdrive_enotify(App $a, array &$b)
{
	if (!empty($b['params']) && $b['params']['type'] == Notification\Type::SYSTEM
		&& !empty($b['params']['system_type']) && $b['params']['system_type'] === 'testdrive_expire') {
		$b['itemlink'] = DI::baseUrl()->get();
		$b['epreamble'] = $b['preamble'] = DI::l10n()->t('Your account on %s will expire in a few days.', DI::config()->get('system', 'sitename'));
		$b['subject'] = DI::l10n()->t('Your Friendica test account is about to expire.');
		$b['body'] = DI::l10n()->t("Hi %1\$s,\n\nYour test account on %2\$s will expire in less than five days. We hope you enjoyed this test drive and use this opportunity to find a permanent Friendica website for your integrated social communications. A list of public sites is available at %s/siteinfo - and for more information on setting up your own Friendica server please see the Friendica project website at https://friendi.ca.", $b['params']['to_name'], "[url=".DI::config()->get('system', 'url')."]".DI::config()->get('config', 'sitename')."[/url]", Search::getGlobalDirectory());
	}
}
