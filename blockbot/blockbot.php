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

	// List of strings of known "good" agents
	$agents = ['diaspora-connection-tester', 'DiasporaFederation', 'Friendica', '(compatible; zot)',
		'Micro.blog', 'Mastodon', 'hackney', 'GangGo', 'python/federation', 'GNU social', 'winHttp',
		'Go-http-client', 'Mr.4x3 Powered', 'Test Certificate Info', 'WordPress.com', 'zgrab',
		'curl/', 'StatusNet', 'OpenGraphReader/', 'Uptimebot/', 'python-opengraph-jaywink'];

	if ($crawlerDetect->isCrawler()) {
		foreach ($agents as $agent) {
			if (stristr($_SERVER['HTTP_USER_AGENT'], $agent)) {
				// @ToDo: Report every false positive here: https://github.com/JayBizzle/Crawler-Detect/issues/326
				logger::notice('False positive', ['agent' => $_SERVER['HTTP_USER_AGENT']]);
				return;
			}
		}
		logger::info('Blocked bot', ['agent' => $_SERVER['HTTP_USER_AGENT']]);
		System::httpExit(403, 'Bots are not allowed');
	} else {
		logger::debug('Good user agent detected', ['agent' => $_SERVER['HTTP_USER_AGENT']]);
	}
}
