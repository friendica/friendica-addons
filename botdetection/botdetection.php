<?php
/**
 * Name: botdetection
 * Description: Blocking bots based on detecting bots/crawlers/spiders via the user agent and http_from header.
 * Version: 0.1
 * Author: Philipp Holzer <admin@philipp.info>
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\System;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

function botdetection_install() {
	Hook::register('init_1', 'addon/botdetection/botdetection.php', 'botdetection_init_1');
}


function botdetection_uninstall() {
	Hook::unregister('init_1', 'addon/botdetection/botdetection.php', 'botdetection_init_1');
}

function botdetection_init_1(App $a) {
	$crawlerDetect = new CrawlerDetect();

	if ($crawlerDetect->isCrawler()) {
		System::httpExit(404, 'Bots are not allowed');
	}
}
