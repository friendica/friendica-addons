<?php

use Friendica\DI;

function pumpio_sync_run(array $argv, int $argc)
{
	require_once 'addon/pumpio/pumpio.php';

	if (function_exists('sys_getloadavg')) {
		$load = sys_getloadavg();
		if (intval($load[0]) > DI::config()->get('system', 'maxloadavg', 50)) {
			DI::logger()->notice('system: load ' . $load[0] . ' too high. Pumpio sync deferred to next scheduled run.');
			return;
		}
	}

	pumpio_sync();
}
