<?php
/**
 * Name: Proc Runner
 * Description: Derivative of poormancron when proc_open() and exec() are disabled
 * Version: 1.0
 * Author: Fabio Comuni <http://kirgroup.com/profile/fabrix>
 * Author: Mike Macgirvin
 */

function procrunner_install() {

	$addons = get_config('system','addon');
	if(strstr('poormancron',$addons)) {
		logger('procrunner incompatible with poormancron. Not installing procrunner.');
		return;
	}

	// check for command line php
	$a = get_app();
	$ex = Array();
	$ex[0] = ((x($a->config,'php_path')) && (strlen($a->config['php_path'])) ? $a->config['php_path'] : 'php');
	$ex[1] = dirname(dirname(dirname(__file__)))."/testargs.php";
	$ex[2] = "test";
	$out = exec(implode(" ", $ex));
	if ($out==="test") {
		logger('procrunner not required on this system. Not installing.');
		return;
	} else {
		register_hook('proc_run', 'addon/procrunner/procrunner.php','procrunner_procrun');
		logger("installed procrunner");
	}
	
}

function procrunner_uninstall() {
	unregister_hook('proc_run', 'addon/procrunner/procrunner.php','procrunner_procrun');
	logger("removed procrunner");
}



function procrunner_procrun(&$a, &$arr) {

	$argv = $arr['args'];
	$arr['run_cmd'] = false;
	logger("procrunner procrun ".implode(", ",$argv));
	array_shift($argv);
	$argc = count($argv);
	logger("procrunner procrun require_once ".basename($argv[0]));
	require_once(basename($argv[0]));
	$funcname=str_replace(".php", "", basename($argv[0]))."_run";  
	$funcname($argv, $argc);
}
