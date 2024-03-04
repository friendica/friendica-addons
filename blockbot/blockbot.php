<?php
/**
 * Name: blockbot
 * Description: Blocking bots based on detecting bots/crawlers/spiders via the user agent and http_from header.
 * Version: 0.2
 * Author: Philipp Holzer <admin@philipp.info>
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 *
 */

use Friendica\Core\Hook;
use Friendica\DI;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Network\HTTPException\ForbiddenException;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function blockbot_install()
{
	Hook::register('init_1', __FILE__, 'blockbot_init_1');
}

function blockbot_addon_admin(string &$o)
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/blockbot/');

	$o = Renderer::replaceMacros($t, [
		'$submit'             => DI::l10n()->t('Save Settings'),
		'$good_crawlers'      => ['good_crawlers', DI::l10n()->t('Allow "good" crawlers'), DI::config()->get('blockbot', 'good_crawlers'), DI::l10n()->t("Don't block fediverse crawlers, relay servers and other bots with good purposes.")],
		'$socialmedia_agents' => ['socialmedia_agents', DI::l10n()->t('Allow preview agents'), DI::config()->get('blockbot', 'socialmedia_agents'), DI::l10n()->t("Don't block agents from social media systems that want to generate preview data for links that had been set by their users.")],
		'$block_gab'          => ['block_gab', DI::l10n()->t('Block GabSocial'), DI::config()->get('blockbot', 'block_gab'), DI::l10n()->t('Block the software GabSocial. This will block every access for that software. You can block dedicated gab instances in the blocklist settings in the admin section.')],
		'$training'           => ['training', DI::l10n()->t('Training mode'), DI::config()->get('blockbot', 'training'), DI::l10n()->t("Activates the training mode. This is only meant for developing purposes. Don't activate this on a production machine. This can cut communication with some systems.")],
	]);
}

function blockbot_addon_admin_post()
{
	DI::config()->set('blockbot', 'good_crawlers', $_POST['good_crawlers'] ?? false);
	DI::config()->set('blockbot', 'socialmedia_agents', $_POST['socialmedia_agents'] ?? false);
	DI::config()->set('blockbot', 'block_gab', $_POST['block_gab'] ?? false);
	DI::config()->set('blockbot', 'training', $_POST['training'] ?? false);
}

function blockbot_init_1()
{
	if (empty($_SERVER['HTTP_USER_AGENT'])) {
		return;
	}

	$logdata = ['agent' => $_SERVER['HTTP_USER_AGENT'], 'uri' => $_SERVER['REQUEST_URI']];

	// List of "good" crawlers, mostly from the fediverse.
	$good_agents = [
		'fediverse.space crawler', 'fediverse.network crawler', 'Active_Pods_CheckBot_3.0',
		'Social-Relay/', 'Test Certificate Info', 'Uptimebot/', 'GNUSocialBot', 'UptimeRobot/',
		'PTST/', 'Zabbix', 'Poduptime/', 'FediFetcher', 'lemmy-stats-crawler',
		'FedditLemmyverseCrawler/', 'kbinBot/', 'lemmy-explorer-crawler/',
	];

	// List of agents from social media systems that fetch preview data via opem graph or twitter cards.
	$socialmedia_agents = ['Twitterbot/', 'facebookexternalhit/', 'SkypeUriPreview Preview/',
		'TelegramBot', 'WhatsApp/', 'github-camo', 'Bluesky Cardyb/', 'XING-contenttabreceiver/', 
		'LinkedInBot/', 'Instagram ', 'Synapse (bot; ', 'Discordbot/', 'SummalyBot/'];

	// List of known unwanted crawlers.
	$agents = [
		'SemrushBot', 's~feedly-nikon3', 'Qwantify/Bleriot/', 'ltx71', 'Sogou web spider/',
		'Diffbot/', 'YisouSpider', 'evc-batch/', 'LivelapBot/', 'TrendsmapResolver/',
		'PaperLiBot/', 'Nuzzel', 'um-LN/', 'Google Favicon', 'Datanyze', 'BLEXBot/', '360Spider',
		'adscanner/', 'HeadlessChrome', 'wpif', 'startmebot/', 'Googlebot/', 'Applebot/',
		'GoogleImageProxy', 'bingbot/', 'heritrix/', 'ldspider',
		'AwarioRssBot/', 'TweetmemeBot/', 'dcrawl/', 'PhantomJS/', 'Googlebot-Image/',
		'CrowdTanglebot/', 'Mediapartners-Google', 'Baiduspider/', 'datagnionbot',
		'MegaIndex.ru/', 'SMUrlExpander', 'Hatena-Favicon/', 'Wappalyzer', 'FlipboardProxy/',
		'NetcraftSurveyAgent/', 'Dataprovider.com', 'SMTBot/', 'Nimbostratus-Bot/',
		'DuckDuckGo-Favicons-Bot/', 'IndieWebCards/', 'proximic', 'netEstate NE Crawler',
		'AhrefsBot/', 'YandexBot/', 'Exabot/', 'Mediumbot-MetaTagFetcher/',
		'SurdotlyBot/', 'BingPreview/', 'SabsimBot/', 'CCBot/', 'WbSrch/',
		'DuckDuckBot-Https/', 'HTTP Banner Detection', 'YandexImages/', 'archive.org_bot',
		'ArchiveTeam ArchiveBot/', 'yacybot', 'https://developers.google.com/+/web/snippet/',
		'Scrapy/', 'MJ12bot/', 'DotBot/', 'Pinterestbot/', 'Jooblebot/',
		'Cliqzbot/', 'YaK/', 'Mediatoolkitbot', 'Snacktory', 'FunWebProducts', 'oBot/',
		'7Siters/', 'KOCMOHABT', 'Google-SearchByImage', 'FemtosearchBot/',
		'HubSpot Crawler', 'DomainStatsBot/', 'Re-re Studio', 'AwarioSmartBot/',
		'DNSResearchBot/', 'PetalBot;', 'Nmap Scripting Engine;',
		'Google-Apps-Script; beanserver;', 'woorankreview/', 'Seekport Crawler;', 'AHC/',
		'Semanticbot/', 'Embed PHP library', 'XoviOnpageCrawler;', 'Pinterest/',
		'GetHPinfo.com-Bot/', 'BoardReader Favicon Fetcher', 'Google-Adwords-Instant', 'newspaper/',
		'YurichevBot/', 'Crawling at Home Project', 'InfoTigerBot/',
		'AdIdxBot/', 'MicrosoftPreview/', 'masscan/'
	];

	if (!DI::config()->get('blockbot', 'good_crawlers')) {
		$agents = array_merge($agents, $good_agents);
	} else {
		foreach ($good_agents as $good_agent) {
			if (stristr($_SERVER['HTTP_USER_AGENT'], $good_agent)) {
				return;
			}
		}
	}

	if (!DI::config()->get('blockbot', 'socialmedia_agents')) {
		$agents = array_merge($agents, $socialmedia_agents);
	} else {
		foreach ($socialmedia_agents as $socialmedia_agent) {
			if (stristr($_SERVER['HTTP_USER_AGENT'], $socialmedia_agent)) {
				return;
			}
		}
	}

	if (DI::config()->get('blockbot', 'block_gab')) {
		$agents[] = 'GabSocial/';
	}

	foreach ($agents as $agent) {
		if (stristr($_SERVER['HTTP_USER_AGENT'], $agent)) {
			throw new ForbiddenException('Bots are not allowed. If you consider this a mistake, create an issue at https://github.com/friendica/friendica');
		}
	}

	// This switch here is only meant for developers who want to add more bots to the list above, it is not safe for production.
	if (!DI::config()->get('blockbot', 'training')) {
		return;
	}

	$crawlerDetect = new CrawlerDetect();

	if (!$crawlerDetect->isCrawler()) {
		logger::debug('Good user agent detected', $logdata);
		return;
	}

	// List of false positives' strings of known "good" agents.
	$agents = [
		'curl', 'zgrab', 'Go-http-client', 'curb', 'github.com', 'reqwest', 'Feedly/',
		'Python-urllib/', 'Liferea/', 'aiohttp/', 'WordPress.com Reader', 'hackney/',
		'Faraday v', 'okhttp', 'UniversalFeedParser', 'PixelFedBot', 'python-requests',
		'WordPress/', 'http.rb/', 'Apache-HttpClient/', 'WordPress.com;', 'Pleroma',
		'Dispatch/', 'Ruby', 'Java/', 'libwww-perl/', 'Mastodon/', 'FeedlyApp/',
		'lua-resty-http/', 'Tiny Tiny RSS/', 'Wget/', 'PostmanRuntime/',
		'W3C_Validator/', 'NetNewsWire', 'FeedValidator/', 'theoldreader.com', 'axios/',
		'Paw/', 'PeerTube/', 'fedi.inex.dev', 'FediDB/', 'index.community crawler',
		'Slackbot-LinkExpanding', 'Firefish/', 'Takahe/', 'Akkoma ', 'Misskey/'
	];

	if (DI::config()->get('blockbot', 'good_crawlers')) {
		$agents = array_merge($agents, $good_agents);
	}

	foreach ($agents as $agent) {
		if (stristr($_SERVER['HTTP_USER_AGENT'], $agent)) {
			logger::info('False positive', $logdata);
			return;
		}
	}

	logger::notice('Blocked bot', $logdata);
	throw new ForbiddenException('Bots are not allowed. If you consider this a mistake, create an issue at https://github.com/friendica/friendica');
}
