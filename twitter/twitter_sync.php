<?php
if (!file_exists("boot.php") AND (sizeof($_SERVER["argv"]) != 0)) {
	$directory = dirname($_SERVER["argv"][0]);

	if (substr($directory, 0, 1) != "/")
		$directory = $_SERVER["PWD"]."/".$directory;

	$directory = realpath($directory."/..");

	chdir($directory);
}

require_once("boot.php");


function twitter_sync_run($argv, $argc){
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

	require_once("addon/twitter/twitter.php");
	require_once("include/pidfile.php");

	$maxsysload = intval(get_config('system','maxloadavg'));
	if($maxsysload < 1)
		$maxsysload = 50;
	if(function_exists('sys_getloadavg')) {
		$load = sys_getloadavg();
		if(intval($load[0]) > $maxsysload) {
			logger('system: load ' . $load[0] . ' too high. Twitter sync deferred to next scheduled run.');
			return;
		}
	}

	if ($argc < 3) {
		return;
	}

	$mode = intval($argv[1]);
	$uid = intval($argv[2]);

	/// @todo Replace it with "App::is_already_running" in the next release
	$lockpath = get_lockpath();
	if ($lockpath != '') {
		$pidfile = new pidfile($lockpath, 'twitter_sync-'.$mode.'-'.$uid);
		if($pidfile->is_already_running()) {
			logger("Already running");
			if ($pidfile->running_time() > 9*60) {
				$pidfile->kill();
				logger("killed stale process");
				// Calling a new instance
				proc_run('php','addon/twitter/twitter_sync.php', $mode, $uid);
			}
			exit;
		}
	}

	if ($mode == 1) {
		twitter_fetchtimeline($a, $uid);
	} elseif ($mode == 2) {
		twitter_fetchhometimeline($a, $uid);
	}
}

if (array_search(__file__,get_included_files())===0){
	twitter_sync_run($_SERVER["argv"],$_SERVER["argc"]);
	killme();
}
?>
