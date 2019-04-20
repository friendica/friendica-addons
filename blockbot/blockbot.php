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

function blockbot_install() {
	Hook::register('init_1', 'addon/blockbot/blockbot.php', 'blockbot_init_1');
}


function blockbot_uninstall() {
	Hook::unregister('init_1', 'addon/blockbot/blockbot.php', 'blockbot_init_1');
}

function blockbot_init_1(App $a) {
	$crawlerDetect = new CrawlerDetect();

	if ($crawlerDetect->isCrawler()) {
		System::httpExit(403, 'Bots are not allowed');
	}
}
