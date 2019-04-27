<?php
/**
 * Name: blockbot
 * Description: Blocking bots based on detecting bots/crawlers/spiders via the user agent and http_from header.
 * Version: 0.1
 * Author: Philipp Holzer <admin@philipp.info>
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\System;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Friendica\Core\Logger;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function blockbot_install() {
	Hook::register('init_1', __FILE__, 'blockbot_init_1');
}


function blockbot_uninstall() {
	Hook::unregister('init_1', __FILE__, 'blockbot_init_1');
}

function blockbot_init_1(App $a) {
	$crawlerDetect = new CrawlerDetect();

	$logdata = ['agent' => $_SERVER['HTTP_USER_AGENT'], 'uri' => $_SERVER['REQUEST_URI']];

	if (!$crawlerDetect->isCrawler()) {
		logger::debug('Good user agent detected', $logdata);
		return;
	}

	// List of strings of reported false positives
	$agents = ['hackney/', 'Faraday v', 'okhttp', 'UniversalFeedParser', 'PixelFedBot', 'python-requests',
		'WordPress/', 'http.rb/'];
	foreach ($agents as $agent) {
		if (stristr($_SERVER['HTTP_USER_AGENT'], $agent)) {
			// The agents had been reported to https://github.com/JayBizzle/Crawler-Detect/issues/
			logger::notice('Reported false positive', $logdata);
			return;
		}
	}

	// List of false positives' strings of known "good" agents we haven't reported (yet)
	$agents = ['fediverse.network crawler', 'Active_Pods_CheckBot_3.0', 'Social-Relay/',
		'curl', 'zgrab', 'Go-http-client', 'curb'];

	foreach ($agents as $agent) {
		if (stristr($_SERVER['HTTP_USER_AGENT'], $agent)) {
			logger::notice('Unreported falsely detected agent', $logdata);
			return;
		}
	}

	// List of known crawlers. They are added here to avoid having them logged at the end of the function.
	// This helps to detect false positives.
	$agents = ['SEMrushBot', 's~feedly-nikon3', 'Qwantify/Bleriot/', 'ltx71', 'Sogou web spider/',
		'Diffbot/'];

	foreach ($agents as $agent) {
		if (stristr($_SERVER['HTTP_USER_AGENT'], $agent)) {
			System::httpExit(403, 'Bots are not allowed');
		}
	}

	logger::info('Blocked bot', $logdata);
	System::httpExit(403, 'Bots are not allowed');
}
