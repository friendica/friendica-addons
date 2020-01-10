<?php
use Friendica\Core\Config;
use Friendica\Core\Logger;
use Friendica\Registry\DI;

function pumpio_sync_run(&$argv, &$argc) {
	$a = DI::app();

	require_once("addon/pumpio/pumpio.php");

	if (function_exists('sys_getloadavg')) {
		$load = sys_getloadavg();
		if (intval($load[0]) > Config::get('system', 'maxloadavg', 50)) {
			Logger::log('system: load ' . $load[0] . ' too high. Pumpio sync deferred to next scheduled run.');
			return;
		}
	}

	pumpio_sync($a);
}
