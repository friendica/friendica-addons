<?php


/**
 * Name: external cron
 * Description: Use external server or service to run poller regularly
 * Version: 1.0
 * Author: Mike Macgirvin <https://macgirvin.com/profile/mike>
 * 
 * Notes: External service needs to make a web request to http(s)://yoursite/extcron
 */

function extcron_install() {}

function extcron_uninstall() {}

function extcron_module() {}

function extcron_init(&$a) {
	proc_run('php','include/poller.php');
	killme();
}
