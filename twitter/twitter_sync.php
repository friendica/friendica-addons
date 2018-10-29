<?php

use Friendica\Content\Text;
use Friendica\Core\Config;

function twitter_sync_run($argv, $argc)
{
	$a = Friendica\BaseObject::getApp();

	require_once 'addon/twitter/twitter.php';

	if (function_exists('sys_getloadavg')) {
		$load = sys_getloadavg();
		if (intval($load[0]) > Config::get('system', 'maxloadavg', 50)) {
			App::logger('system: load ' . $load[0] . ' too high. Twitter sync deferred to next scheduled run.');
			return;
		}
	}

	if ($argc < 3) {
		return;
	}

	$mode = intval($argv[1]);
	$uid = intval($argv[2]);

	if ($mode == 1) {
		twitter_fetchtimeline($a, $uid);
	} elseif ($mode == 2) {
		twitter_fetchhometimeline($a, $uid);
	}
}
