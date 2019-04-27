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
	$agents = ['Mastodon', 'hackney', 'Faraday', 'okhttp', 'UniversalFeedParser', 'PixelFedBot', 'python-requests',
		'WordPress', 'http.rb'];
	foreach ($agents as $agent) {
		if (stristr($_SERVER['HTTP_USER_AGENT'], $agent)) {
			// The agents had been reported to https://github.com/JayBizzle/Crawler-Detect/issues/
			logger::notice('Already reported wrong detection', $logdata);
			return;
		}
	}

	// List of strings of known "good" agents
	$agents = ['diaspora-connection-tester', 'DiasporaFederation', 'Friendica', '(compatible; zot)',
		'Micro.blog', 'GangGo', 'python/federation', 'GNU social', 'winHttp',
		'Go-http-client', 'Mr.4x3 Powered', 'Test Certificate Info', 'WordPress.com', 'zgrab',
		'curl/', 'StatusNet', 'OpenGraphReader/', 'Uptimebot/', 'python-opengraph-jaywink',
		'fediverse.network crawler', 'Active_Pods_CheckBot_3.0', 'Social-Relay'];

	foreach ($agents as $agent) {
		if (stristr($_SERVER['HTTP_USER_AGENT'], $agent)) {
			// Report every false positive here: https://github.com/JayBizzle/Crawler-Detect/issues/
			// After report move it into the array above
			logger::notice('False positive', $logdata);
			return;
		}
	}

	// List of known crawlers. They are added here to avoid having them logged at the end of the function.
	// This helps to detect false positives
	$agents = ['Mozilla/5.0 (compatible; SemrushBot/3~bl; +http://www.semrush.com/bot.html)', 'SEMrushBot',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36 AppEngine-Google; (+http://code.google.com/appengine; appid: s~feedly-nikon3)'];

	foreach ($agents as $agent) {
		if ($_SERVER['HTTP_USER_AGENT'] == $agent) {
			System::httpExit(403, 'Bots are not allowed');
		}
	}

	logger::info('Blocked bot', $logdata);
	System::httpExit(403, 'Bots are not allowed');
}
