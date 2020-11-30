<?php

use Friendica\Core\Logger;
use Friendica\DI;

function twitter_sync_run($argv, $argc)
{
	$a = Friendica\DI::app();

	require_once 'addon/twitter/twitter.php';

	if (function_exists('sys_getloadavg')) {
		$load = sys_getloadavg();
		$maxload = DI::config()->get('system', 'maxloadavg', 50);
		if (intval($load[0]) > $maxload) {
			Logger::notice('load too high. Twitter sync deferred to next scheduled run.', ['current' => $load[0], 'max' => $maxload]);
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
