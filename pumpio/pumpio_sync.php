<?php

use Friendica\App;

if (!file_exists("boot.php") AND (sizeof($_SERVER["argv"]) != 0)) {
	$directory = dirname($_SERVER["argv"][0]);

	if (substr($directory, 0, 1) != "/")
		$directory = $_SERVER["PWD"]."/".$directory;

	$directory = realpath($directory."/..");

	chdir($directory);
}

require_once("boot.php");


function pumpio_sync_run(&$argv, &$argc){
	global $a, $db;

	if(is_null($a)) {
		$a = new App;
	}

	if(is_null($db)) {
		@include(".htconfig.php");
		require_once("include/dba.php");
		$db = new dba($db_host, $db_user, $db_pass, $db_data);
		unset($db_host, $db_user, $db_pass, $db_data);
	};

	require_once("addon/pumpio/pumpio.php");
	require_once("include/pidfile.php");

	$maxsysload = intval(get_config('system','maxloadavg'));
	if($maxsysload < 1)
		$maxsysload = 50;
	if(function_exists('sys_getloadavg')) {
		$load = sys_getloadavg();
		if(intval($load[0]) > $maxsysload) {
			logger('system: load ' . $load[0] . ' too high. Pumpio sync deferred to next scheduled run.');
			return;
		}
	}

	// This is deprecated with the worker
	if (function_exists("get_lockpath")) {
		$lockpath = get_lockpath();
		if ($lockpath != '') {
			$pidfile = new pidfile($lockpath, 'pumpio_sync');
			if($pidfile->is_already_running()) {
				logger("Already running");
				if ($pidfile->running_time() > 9*60) {
					$pidfile->kill();
					logger("killed stale process");
					// Calling a new instance
					proc_run('php','addon/pumpio/pumpio_sync.php');
				}
				exit;
			}
		}
	}
	pumpio_sync($a);
}

if (array_search(__file__,get_included_files())===0){
	pumpio_sync_run($_SERVER["argv"],$_SERVER["argc"]);
	killme();
}
?>
