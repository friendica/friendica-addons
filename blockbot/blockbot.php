<?php
/**
 * Name: blockbot
 * Description: Blocking bots based on detecting bots/crawlers/spiders via the user agent and http_from header.
 * Version: 1.0
 * Author: Philipp Holzer <admin@philipp.info>
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 *
 */

use Friendica\Core\Hook;
use Friendica\DI;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\System;
use Friendica\Network\HTTPException\ForbiddenException;
use Friendica\Util\HTTPSignature;
use Friendica\Util\Network;

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
		'$security_checker'   => ['security_checker', DI::l10n()->t('Allow security checkers'), DI::config()->get('blockbot', 'security_checker'), DI::l10n()->t("Don't block security checkers. They can be used for good or bad.")],
		'$http_libraries'     => ['http_libraries', DI::l10n()->t('Allow generic HTTP libraries'), DI::config()->get('blockbot', 'http_libraries'), DI::l10n()->t("Don't block agents from generic HTTP libraries that could be used for good or for bad and that currently can't be traced back to any known Fediverse project.")],
		'$training'           => ['training', DI::l10n()->t('Training mode'), DI::config()->get('blockbot', 'training'), DI::l10n()->t("Activates the training mode. This is only meant for developing purposes. Don't activate this on a production machine. This can cut communication with some systems.")],
	]);
}

function blockbot_addon_admin_post()
{
	DI::config()->set('blockbot', 'security_checker', $_POST['security_checker'] ?? false);
	DI::config()->set('blockbot', 'http_libraries', $_POST['http_libraries'] ?? false);
	DI::config()->set('blockbot', 'training', $_POST['training'] ?? false);
}

function blockbot_reject()
{
	throw new ForbiddenException('Bots are not allowed. If you consider this a mistake, create an issue at https://github.com/friendica/friendica');
}

function blockbot_init_1()
{
	if (empty($_SERVER['HTTP_USER_AGENT'])) {
		return;
	}

	$crawlerDetect = new CrawlerDetect();

	$isCrawler = $crawlerDetect->isCrawler();

	blockbot_save('all-agents', $_SERVER['HTTP_USER_AGENT']);

	$parts = blockbot_get_parts($_SERVER['HTTP_USER_AGENT']);

	$logdata = ['isCrawler' => $isCrawler, 'agent' => $_SERVER['HTTP_USER_AGENT'], 'method' => $_SERVER['REQUEST_METHOD'], 'uri' => $_SERVER['REQUEST_URI'], 'parts' => $parts];

	if ($isCrawler) {
		blockbot_check_login_attempt($_SERVER['REQUEST_URI'], $logdata);
	}

	if (empty($parts)) {
		Logger::debug('Known frontend found - accept', $logdata);
		if ($isCrawler) {
			blockbot_save('badly-parsed-agents', $_SERVER['HTTP_USER_AGENT']);
		}
		return;
	}

	blockbot_log_activitypub($_SERVER['REQUEST_URI'], $_SERVER['HTTP_USER_AGENT']);

	if (blockbot_is_crawler($parts)) {
		Logger::debug('Crawler found - reject', $logdata);
		blockbot_reject();
	}

	if (blockbot_is_searchbot($parts)) {
		Logger::debug('Search bot found - reject', $logdata);
		blockbot_reject();
	}

	if (blockbot_is_unwanted($parts)) {
		Logger::debug('Uncategorized unwanted agent found - reject', $logdata);
		blockbot_reject();
	}

	if (blockbot_is_security_checker($parts)) {
		if (!DI::config()->get('blockbot', 'security_checker')) {
			Logger::debug('Security checker found - reject', $logdata);
			blockbot_reject();
		}
		Logger::debug('Security checker found - accept', $logdata);
		return;
	}

	if (blockbot_is_social_media($parts)) {
		Logger::debug('Social media service found - accept', $logdata);
		return;
	}

	if (blockbot_is_fediverse_client($parts)) {
		Logger::debug('Fediverse client found - accept', $logdata);
		return;
	}

	if (blockbot_is_feed_reader($parts)) {
		Logger::debug('Feed reader found - accept', $logdata);
		return;
	}

	if (blockbot_is_fediverse_tool($parts)) {
		Logger::debug('Fediverse tool found - accept', $logdata);
		return;
	}

	if (blockbot_is_service_agent($parts)) {
		Logger::debug('Service agent found - accept', $logdata);
		return;
	}

	if (blockbot_is_monitor($parts)) {
		Logger::debug('Monitoring service found - accept', $logdata);
		return;
	}

	if (blockbot_is_validator($parts)) {
		Logger::debug('Validation service found - accept', $logdata);
		return;
	}

	if (blockbot_is_good_tool($parts)) {
		Logger::debug('Uncategorized helpful service found - accept', $logdata);
		return;
	}

	// Needs to be checked at the end, since other services might use these libraries
	if (blockbot_is_http_library($parts)) {
		blockbot_check_login_attempt($_SERVER['REQUEST_URI'], $logdata);
		if (!DI::config()->get('blockbot', 'http_libraries')) {
			Logger::debug('HTTP Library found - reject', $logdata);
			blockbot_reject();
		}
		Logger::debug('HTTP Library found - accept', $logdata);
		return;
	}

	// This switch here is only meant for developers who want to add more bots to the list above, it is not safe for production.
	if (!DI::config()->get('blockbot', 'training')) {
		return;
	}

	if (!$isCrawler) {
		blockbot_save('good-agents', $_SERVER['HTTP_USER_AGENT']);
		Logger::debug('Non-bot user agent detected', $logdata);
		return;
	}

	blockbot_save('bad-agents', $_SERVER['HTTP_USER_AGENT']);
	Logger::notice('Possible bot found - reject', $logdata);
	blockbot_reject();
}

function blockbot_save($database, $userAgent)
{
	if (!DI::config()->get('blockbot', 'logging') || !function_exists('dba_open')) {
		return;
	}

	$resource = dba_open(System::getTempPath() . '/' . $database, 'cl');
	$result = dba_fetch($userAgent, $resource);
	if ($result === false) {
		dba_insert($userAgent, true, $resource);
	}
	dba_close($resource);
}

function blockbot_log_activitypub(string $url, string $agent)
{
	if (!DI::config()->get('blockbot', 'logging')) {
		return;
	}

	$bot = ['/.well-known/nodeinfo', '/nodeinfo/2.0', '/nodeinfo/1.0'];
	if (in_array($url, $bot)) {
		blockbot_save('activitypub-stats', $agent);
	}

	$bot = ['/api/v1/instance', '/api/v2/instance', '/api/v1/instance/extended_description',
		'/api/v1/instance/peers'];
	if (in_array($url, $bot)) {
		blockbot_save('activitypub-api-stats', $agent);
	}

	if (substr($url, 0, 6) == '/api/v') {
		blockbot_save('activitypub-api', $agent);
	}

	if (($_SERVER['REQUEST_METHOD'] == 'POST') && in_array('inbox', explode('/', parse_url($url, PHP_URL_PATH)))) {
		blockbot_save('activitypub-inbox-agents', $agent);
	}

	if (!empty($_SERVER['HTTP_SIGNATURE']) && !empty(HTTPSignature::getSigner('', $_SERVER))) {
		blockbot_save('activitypub-signature-agents', $agent);
	}
}

function blockbot_check_login_attempt(string $url, array $logdata)
{
	if (in_array(trim(parse_url($url, PHP_URL_PATH), '/'), ['login', 'lostpass', 'register'])) {
		Logger::debug('Login attempt detected - reject', $logdata);
		blockbot_reject();
	}
}

/**
 * Uncategorized and unwanted services
 *
 * @param array $parts
 * @return boolean
 */
function blockbot_is_unwanted(array $parts): bool
{
	$agents = [
		'oii-research', 'yisouspider', 'bots.retroverse.social', 'gaisbot', 'bloglines', 'emailwolf',
		'webtech', 'facebookscraper', 'www.ecsl.cs.sunysb.edu/~maxim/cgi-bin/link',
		'gulper', 'magellan', 'linkcheck', 'nerdybot', 'ms search robot', 'fast-webcrawler',
		'yioopbot', 'webster', 'www.admantx.com', 'openhosebot', 'lssrocketcrawler', 'dow jones searchbot',
		'gomezagent', 'domainsigmacrawler', 'netseer crawler', 'superbot', 'searchexpress',
		'alittle client', 'amazon-kendra', 'scanner.ducks.party', 'isscyberriskcrawler',
		'google wireless transcoder',
	];

	foreach ($parts as $part) {
		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

/**
 * Services defined as "crawlers"
 *
 * @param array $parts
 * @return boolean
 */
function blockbot_is_crawler(array $parts): bool
{
	$agents = [
		'+http://yourls.org', 'adbeat.com/policy', 'https://gtmetrix.com', 'hubspot', 'nutch-',
		'openwebspider'
	];
	foreach ($parts as $part) {
		foreach ($agents as $agent) {
			if (strpos($part, $agent) !== false) {
				return true;
			}
		}
	}

	$agents = [
		'ahrefsbot', 'pinterest', 'proximic', 'applebot', 'synapseworkstation.3.2.1',
		'slackbot-linkexpanding', 'semrushbot-sa', 'qwantify', 'google search console',
		'tbot-nutch', 'screaming frog seo spider', 'exaleadcloudview', 'dotbot', 'exabot',
		'spbot', 'surdotlybot', 'tweetmemebot', 'cliqzbot', 'startmebot', 'ccbot', 'zoombot',
		'domain re-animator bot', 'nutch', 'archive.org_bot http://www.archive.org/details',
		'yahoo link preview', 'mxt', 'grapeshotcrawler', 'maxpointcrawler', 'vagabondo',
		'archive.org_bot', 'infegyatlas', '2ip bot', 'accompanybot', 'antbot', 'anthropic-ai',
		'aspiegelbot', 'cispa web analyzer', 'claudebot', 'colly', 'petalbot', 'ioncrawl',
		'embedly +support@embed.ly', 'gitcrawlerbot', 'google favicon', 'httpx', 'seokicks',
		'kocmohabt', 'masscan-ng', 'mixnodecache', 'nicecrawler', 'birdcrawlerbot', 'seolyt',
		'dataprovider.com', 'dnsresearchbot', 'domains project', 'evc-batch', 'ev-crawler',
		'example3', 'geedobot', 'internetmeasurement', 'ips-agent', 'semanticscholarbot',
		'sputnikfaviconbot', 't3versionsbot', 'tchelebi', 'thinkchaos', 'velenpublicwebcrawler',
		'webwikibot', 'woobot', 'project-resonance', 'mtrobot', 'webprosbot', 'youbot',
		'queryseekerspider', 'scanning for research', 'semrushbot', 'senutobot', 'spawning-ai',
		'statista.com publication finder crawler', 'turnitin', 'who.is bot', 'zaldamosearchbot',
		'nuzzel', 'boardreader blog indexer', 'hatena-favicon', 'nbertaupete95', 'scrapy',
		"electronic frontier foundation's do not track verifier", 'synapse', 'trendsmapresolver',
		'pinterestbot', 'um-ln', 'slack-imgproxy', 'diffbot', 'dataforseobot', 'bw', 'bitlybot',
		'twingly recon-klondike', 'imagesiftbot', 'rogerbot', 'yahoocachesystem', 'favicon',
		'vkshare', 'appid: s~virustotalcloud', 'clickagy intelligence bot v2', 'gptbot',
		'archive.org_bot http://archive.org/details', 'wellknownbot', 'archiveteam archivebot',
		'megaindex.ru', 'adbeat_bot', 'masscan', 'embedly', 'cloudflare-amp', 'exabot-thumbnails',
		'yahoo ad monitoring', 'seokicks-robot', 'trendiction search', 'semrushbot-si', 'plukkie',
		'hubpages v0.2.2', 'aream.bot', 'safednsbot', 'linkpadbot', 'gluten free crawler',
		'turnitinbot', 'xovibot', 'domaincrawler', 'nettrack', 'domaincrawler', 'yak', 'bubing',
		'netestate ne crawler', 'blexbot', 'the knowledge ai', 'optimizer', 'hubspot webcrawler',
		'venuscrawler', 'adstxtcrawler', 'iframely', 'checkmarknetwork', 'semrushbot-ba',
		'archive.org bot', 'aihitbot', 'sitesucker', 'adstxtlab.com crawler', 'jobboersebot',
		'http://www.archive.org/details/archive.org_bot', 'heritrix', 'appid: s~snapchat-proxy',
		'icc-crawler', 'mbcrawler', 'slackbot', 'trumind-crawler', 'newspaper', 'online-webceo-bot',
		'haena-pepper', 'y! crawler', 'linkwalker', 'seznamemailproxy', 'seekport crawler',
		'domainstatsbot', 'qwantify/mermoz', 'sprinklr', 'komodiabot', 'seoscanners.net',
		'domainappender', 'mixrankbot', 'abonti', 'urlappendbot', 'sistrix crawler',
		'hatenabookmark', 'metainspector', 'ezooms', 'quora link preview', 'semrushbot-bm',
		'barkrowler', 'panscient.com', 'http://tweetedtimes.com', 'twingly recon',
		'collection@infegy.com', 'mediatoolkitbot', 'cloudflare-amphtml', 'ramblermail',
		'tineye', 'adscanner', 'datagnionbot', 'aa_crawler', 'http://www.profound.net/domainappender',
		'appid: e~arsnova-filter-system', 'kinglandsystemscorp', 'crmnlcrawlagent', 'techfetch-bot',
	];

	foreach ($parts as $part) {
		if (substr($part, -13) == ' accompanybot') {
			return true;
		}

		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

/**
 * Services defined as search bots
 *
 * @param array $parts
 * @return boolean
 */
function blockbot_is_searchbot(array $parts): bool
{
	$agents = ['baiduspider'];
	foreach ($parts as $part) {
		foreach ($agents as $agent) {
			if (strpos($part, $agent) !== false) {
				return true;
			}
		}
	}

	$agents = [
		'yahoo! slurp', 'linkcheck by siteimprove.com', 'googlebot', '360spider', 'haosouspider',
		'mj12bot', 'feedfetcher-google', 'mediapartners-google', 'duckduckgo-favicons-bot',
		'googlebot-mobile', 'gigablastopensource', 'bingbot', 'surveybot', 'yandexbot',
		'google web preview', 'meanpathbot', 'wesee_bot:we_help_monitize_your_site',
		'seznambot', 'sogou web spider', 'linkdexbot', 'msnbot', 'smtbot', 'yandexmetrika',
		'google-site-verification', 'netcraft ssl server survey - contact info@netcraft.com',
		'orangebot', 'google-adwords-instant', 'googlebot-richsnippets', 'google-lens',
		'googleother', 'google-test', 'linkdex.com', 'mail.ru', 'awariobot', 'bytespider',
		'coccocbot-image', 'discobot', 'google-inspectiontool', 'netcraftsurveyagent',
		'tineye-bot', 'tineye-bot-live', 'bingpreview', 'ask jeeves', 'adsbot-google', "msnbot-media ",
		'googlebot-image', 'googlebot-news', 'googlebot-video', 'msnbot-media', 'yahoo! slurp china',
		'inoreader.com-like feedfetcher-google', 'google-amphtml', 'duckduckbot', 'coccocbot-web',
		'googleassociationservice', 'yandexwebmaster', 'yacybot', 'duckduckbot-https', 'yandexmobilebot',
		'mail.ru_bot/fast', 'yandeximages', 'mail.ru_bot/img', 'ia_archiver', 'yandexblogs',
		'yandexaccessibilitybot', 'yandeximageresizer', 'mail.ru_bot', 'yeti', 'obot', 'baiduspider-render',
		'netcraft web server survey', 'yandexnews', 'google', 'yandexrenderresourcesbot',
		'match by siteimprove.com', 'yandexsitelinks', 'yandexantivirus', 'daum', 'mail.ru_bot/robots',
		'yandexmedia', 'msnbot-products', 'yandexvideo', 'yandexvertis', 'catexplorador', 'yandexcalendar',
		'yandexfavicons', 'user-agent\x09baiduspider', 'baiduspider-image', 'yandexpagechecker', 'mojeekbot',
		'adsbot-google-mobile', 'google-adwords-displayads-webrender', 'seznam screenshot-generator',
		'yandexscreenshotbot', 'zumbot', 'tracemyfile', 'wotbox', 'google-adwords-express',
		'google-adwords-displayads', 'google-youtube-links', 'yandexvideoparser', 'paperlibot',
		'weborama-fetcher', 'googleproducer', 'coccoc', 'acoonbot', 'psbot', 'sosospider', 'voilabot',
		'blekkobot', 'easouspider', 'omgili', 'yadirectfetcher', 'sogou pic spider', 'daumoa',
	];

	foreach ($parts as $part) {
		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

/**
 * Services in the "security" context
 *
 * @param array $parts
 * @return boolean
 */
function blockbot_is_security_checker(array $parts): bool
{
	$agents = [
		'http banner detection', 'l9explore', 'l9tcpid', 'lkx-apache2449traversalplugin',
		'bitsightbot', 'censysinspect', 'pathspider', 'repolookoutbot', 'sqlmap', 'ltx71',
		'netsystemsresearch studies the availability of various services across the internet. our website is netsystemsresearch.com',
		'expanse a palo alto networks company searches across the global ipv4 space multiple times per day to identify customers&#39',
		'zgrab', 'nmap scripting engine', 'l9scan', 'riddler', 'cloud mapping experiment. contact research@pdrlabs.net',
	];

	foreach ($parts as $part) {
		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

/**
 * Services that check pages for e.g. valid HTML
 *
 * @param array $parts
 * @return boolean
 */
function blockbot_is_validator(array $parts): bool
{
	$agents = [
		'jigsaw', 'ssl labs', 'w3c_validator', 'w3c-checklink', 'p3p validator', 'csscheck', 'validator.nu',
		'google-structured-data-testing-tool https://search.google.com/structured-data', 'w3c_unicorn',
	];

	foreach ($parts as $part) {
		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

/**
 * Services that monitor a page
 *
 * @param array $parts
 * @return boolean
 */
function blockbot_is_monitor(array $parts): bool
{
	$agents = [
		'alexa site audit', 'catchpoint', 'google page speed insights', 'checkhost',
		'poduptime', 'chrome-lighthouse', 'zabbix', 'cloudflare-alwaysonline', 'ptst',
		'pingadmin.ru', 'pingdomtms', 'nimbostratus-bot', 'uptimebot', 'uptimerobot',
		'http://notifyninja.com/monitoring', 'http://www.freewebmonitoring.com',
	];

	foreach ($parts as $part) {
		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

/**
 * Services in the centralized and decentralized social media environment 
 *
 * @param array $parts
 * @return boolean
 */
function blockbot_is_social_media(array $parts): bool
{
	$agents = ['camo-rs asset proxy', 'camo asset proxy'];
	foreach ($parts as $part) {
		foreach ($agents as $agent) {
			if (strpos($part, $agent) !== false) {
				return true;
			}
		}
	}

	$agents = [
		'facebookexternalhit', 'twitterbot', 'mastodon', 'facebookexternalua',
		'friendica', 'diasporafederation', 'buzzrelay', 'activityrelay',
		'aoderelay', 'ap-relay', 'peertube', 'misskey', 'pleroma', 'foundkey', 'akkoma',
		'lemmy', 'calckey', 'mobilizon', 'zot', 'camo-rs', 'gotosocial', 'pixelfed',
		'pixelfedbot', 'app.wafrn.net', 'go-camo', 'http://a.gup.pe', 'iceshrimp',
		'firefish', 'activity-relay', 'juick', 'camo', 'python/federation', 'nextcloud',
		'snac', 'bovine', 'takahe', 'freedica', 'gnu social', 'microblogpub',
		'mbin', 'mammoth', 'kbinbot', 'honksnonk', 'misskeymediaproxy', 'kbinbot', 'jistflow',
		'mastodon/3.4.1 fedibird', 'fedibird', 'funkwhale', 'linkedinbot',
		'wafrn-cache-generator', 'simple social network', 'mbinbot', 'wordpress.com',
		'catnip', 'castopod', 'enby-town', 'vernissage', 'iceshrimp.net', 'plasmatrap',
		'imgproxy', 'rustypub', 'flipboard activitypub', 'gnu social activitypub plugin',
		'micro.blog', 'mastodon-bookmark-rss', 'bookwyrm', 'damus', 'primal', 'misskeyadmin',
		'ruby, mastodon', 'nextcloud social', 'camo asset proxy', 'smithereen', 'sorasns',
		'cherrypick', 'bonfire activitypub federation', 'upub+0.1.0', 'plume', 'incestoma',
		'gyptazyfedi', 'apogee', 'quolibet', 'magpie-crawler', 'redditbot', 'facebookplatform',
	];

	foreach ($parts as $part) {
		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

/**
 * Fediverse clients
 *
 * @param array $parts
 * @return boolean
 */
function blockbot_is_fediverse_client(array $parts): bool
{
	$agents = [
		'mastodonandroid', 'tootdeck-worker', 'piefed', 'brighteon', 'pachli', 'tusky', 'mona', 'mitra',
		'megalodonandroid', 'fedilab', 'mastodonapp', 'toot!', 'intravnews',
		'pixeldroid', 'greatnews', 'protopage', 'newsfox', 'vienna', 'wp-urldetails', 'husky',
		'activitypub-go-http-client', 'mobilesafari', 'mastodon-ios', 'mastodonpy', 'techniverse',
	];

	foreach ($parts as $part) {
		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

/**
 * Feed reading clients and services
 *
 * @param array $parts
 * @return boolean
 */
function blockbot_is_feed_reader(array $parts): bool
{
	$agents = [
		'tiny tiny rss', 'mlem', 'feedly', 'flipboardproxy',  'reeder', 'netnewswire',
		'freshrss', 'feedlyapp', 'feedlybot', 'feeddemon', 'rssowl', 'simplepie',
		'magpierss',  'universalfeedparser', 'newsgatoronline', 'theoldreader.com',
		'quiterss', 'feedburner', 'digg feed fetcher', 'r6_feedfetcher', 'apple-pubsub',
		'netvibes', 'newsblur page fetcher', 'newsblur favicon fetcher', 'newsblur favicon fetcher',
		'liferea', 'http://www.jetbrains.com/omea_reader/', 'feedblitz', 'bloglovin',
		'windows-rss-platform', 'feedshow', 'feedreader', 'rssbandit', 'everyfeed-spider',
		'feeeed', 'spacecowboys android rss reader', 'gregarius', 'feedspot',
		'feedspot ssl asset proxy', 'newsgator', 'newsgator fetchlinks extension',
		'akregator', 'appid: s~feedly-nikon3',
	];

	foreach ($parts as $part) {
		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

function blockbot_is_fediverse_tool(array $parts): bool
{
	$agents = [
		'diaspora-connection-tester', 'fediblock.manalejandro.com',
		'mastodoninstances', 'fedilist agent', 'https://fedilist.com/', 'fedidb',
		'https://wiki.communitydata.science/communitydata:fediverse_research', 'mastofeed.com',
		'lemmy-explorer-crawler', 'fedicheck.online v1.0', 'momostr', 'fedditlemmyversecrawler',
		'fediseer', 'fedistatscrawler', 'gnusocialbot', 'fedifetcher', 'fedineko', 'bird.makeup',
		'fediverse', 'fedicheck.online', 'https://fed.brid.gy/', 'lemmy-stats-crawler',
		"fediverse's stats", 'friendicadirectory', 'rss discovery engine',
		'python-opengraph-jaywink', 'connect.rocks', 'tootsdk',
	];


	foreach ($parts as $part) {
		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

/**
 * General services
 *
 * @param array $parts
 * @return boolean
 */
function blockbot_is_service_agent(array $parts): bool
{
	$agents = ['wordpress.com'];
	foreach ($parts as $part) {
		foreach ($agents as $agent) {
			if (strpos($part, $agent) !== false) {
				return true;
			}
		}
	}

	$agents = [
		'chrome privacy preserving prefetch proxy', 'http compression test', 'microsoftpreview',
		'pocketimagecache', 'wordpress', 'skypeuripreview preview', 'wordpress.com', 'discordbot',
		'summalybot', 'livelapbot', 'whatsapp', 'facebot', 'skypeuripreview',
		'plasmatrap image proxy server', 'grammarly', 'browsershots', 'google-apps-script',
		'yahoomailproxy', 'pocketparser', 'apachebench',
	];

	foreach ($parts as $part) {
		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

/**
 * Libraries that perform HTTP requests
 *
 * @param array $parts
 * @return boolean
 */
function blockbot_is_http_library(array $parts): bool
{
	if ((count($parts) == 1) && in_array($parts[0], ['okhttp', 'useragent', 'faraday'])) {
		return true;
	}

	$agents = ['faraday '];
	foreach ($parts as $part) {
		foreach ($agents as $agent) {
			if (strpos($part, $agent) !== false) {
				return true;
			}
		}
	}

	$agents = [
		'python-urllib', 'go-http-client', 'axios', 'java', 'undici', 'node', 'ruby',
		'mint', 'wget', 'dart:io', 'dart', 'caveman-sieve', 'guzzlehttp', 'deno',
		'aiohttp', 'networkingextension', 'python-asks', 'fasthttp', 't7', 'scalaj-http',
		'curl', 'python-requests', 'node-fetch', 'offline explorer', 'aria2',
		'link_thumbnailer', 'python-httpx', 'com.apple.safari.searchhelper',
		'com.apple.webkit.networking', 'luasocket', 'libwww-perl', 'google-http-java-client',
		'appengine-google', 'reqwest', 'htmlparser', 'headlesschrome', 'winhttp',
		'webcopier', 'webzip', 'http.jl', 'got', 'hackney', 'oca\mail\vendor\favicon',
		'winhttp.winhttprequest.5', 'go package http', 'jakarta commons-httpclient',
		'cpp-httplib', 'fuzz faster u fool v1.3.1-dev', 'fuzz faster u fool v1.5.0-dev',
		'go http package', 'go-resty', 'http.rb', 'ivre-masscan', 'java1.0.21.0',
		'jsdom', 'python-urllib3', 'reactornetty', 'req', 'restsharp', 'ruby-rdf-distiller',
		'pycurl', 'fdm', 'fdmx', 'lua-resty-http', 'python-httplib2', 'anyevent-http',
		'node-superagent', 'unirest-java', 'gvfs', 'http_request2', 'java browser', 'cakephp',
		'curly http client', 'lavf', 'typhoeus',
	];

	foreach ($parts as $part) {
		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

/**
 * Uncategorized helpful services
 *
 * @param array $parts
 * @return boolean
 */
function blockbot_is_good_tool(array $parts): bool
{
	$agents = [
		'easy-feed-oven', 'cutycapt', 'rss-is-dead.lol web bot', 'dnt-policy@eff.org',
		'https://socnetv.org', 'opengraphreader', 'trendfetcher', 'iabot', 'rss-is-dead.lol feed bot',
		'androiddownloadmanager', 'readybot.io', 'hydra', 'httrack', 'vlc', 'wdg_validator', 'download demon',
	];


	foreach ($parts as $part) {
		if (in_array($part, $agents)) {
			return true;
		}
	}
	return false;
}

function blockbot_get_parts(string $agent): array
{
	$parts        = [];
	$level        = 0;
	$start        = 0;
	$end          = 0;
	$has_brackets = false;
	for ($pos = 0; $pos < strlen($agent); $pos++) {
		if ((strpos(substr($agent, $pos), '(') === false) && ($level == 0)) {
			$part = substr($agent, $pos);
			$parts = array_merge($parts, blockbot_split_parts($part, strpos($part, '/'), !$has_brackets));
			break;
		} elseif (substr($agent, $pos, 1) == '(') {
			$level++;
			$has_brackets = true;
			if ($level == 1) {
				$part = substr($agent, $end, $pos - $end);
				$parts = array_merge($parts, blockbot_split_parts($part, $start != 0, false));
				$start = $pos + 1;
			}
		} elseif (substr($agent, $pos, 1) == ')') {
			$level--;
			if ($level == 0) {
				$part = substr($agent, $start, $pos - $start);
				$parts = array_merge($parts, blockbot_split_parts($part, false, true));
				$end = $pos + 1;
			}
		}
	}
	return blockbot_remove_browser_parts($parts);
}

function blockbot_remove_browser_parts(array $parts): array
{
	$cleaned = [];
	foreach ($parts as $part) {
		if (substr($part, -6) == ' build') {
			continue;
		}
		$known = [
			'mozilla', 'x11', 'ubuntu', 'linux x86_64', 'gecko', 'firefox', 'windows nt',
			'win64', 'x64', 'android', 'applewebkit', 'khtml', 'like', 'chrome', 'safari', 'edg',
			'unsupported', 'compatible', 'macintosh', 'intel mac os x', 'version', 'windows',
			'u', 'en-us', '.net', '.net', 'wow64', 'linux', 'k', 'mobile', 'opr', 'msie',
			'dalvik', 'build', 'nt', 'mobile safari', 'gecko/firefox', 'zh-cn', 'en-gb', 'clr',
			'trident', '.net clr', 'qtwebengine', 'linux i686', 'tablet pc', 'ppc mac os x',
			'en', 'fedora', 'ppc', 'edge', 'yabrowser', 'yowser', 'media center pc', 'arm_64',
			'android 9', 'cros x86_64', 'iphone', 'cpu iphone os like mac os x', 'core',
			'qqbrowser', 'beta', 'khtml like gecko', 'psp (playstation portable)', 'ia64',
			'firephp', 'live', 'slcc2', 'infopath.2', 'bidubrowser', 'ubrowser', 'baiduboxapp',
			'waterfox', 'lynx', 'libwww-fm', 'ssl-mm', 'openssl', 'gnutls', '.net4.0c', '.net4.0e',
			'infopath.3', 'opera', 'palemoon', 'goanna', 'vivaldi', 'presto', 'intrepid', 'ru',
			'ipad', 'cpu os like mac os x', 'omniweb', 'chromium', 'thunderbird', 'ubuntu lts',
			'os', 'qupzilla', 'seamonkey', 'warp', 'konqueror', 'meego', 'nokian9', 'nokiabrowser',
			'profile', 'configuration', 'untrusted', 'samsungbrowser', 'es-us', 'pocophone f1',
			'sonyericssonw995', 'crios', 'lbbrowser', 'gwx:qualified', 'gwx:red', 'gwx:reserved',
			'microsoft outlook', 'word', 'microsoft', 'office', 'powerpoint', 'excel',
			'internet explorer', 'like gecko', 'shuame', 'qianniu', 'khtml, like gecko',
			'cutycapt version', 'khtml, live gecko', '68k', 'sv1', 'aef', 'gtb7.5', 'gfe',
			'embedded web browser from: http://bsalsa.com', 'wv', 'malnjs', '2.00',
			'fsl', 'lcjb', 'malcjs', 'touch', 'masmjs', 'malc', 'maln', 'foxy', 'bri', 'lcte',
			'embeddedwb from: http://www.bsalsa.com', '2345explorer', 'hpntdfjs', 'h4213',
			'rb.gy', 'sm-a505fn', 'lenovo tb-8504x', 'silk', 'lya-al00', 'windows xp', 'openbsd',
			'netbsd amd64', 'sa', 'samsung sm-g950f', 'redmi note', 'hry-lx1', 'cph2205',
			'16th', 'redmi note pro', 'xiaomi/miuibrowser', 'sk-sk', 'linux i686 on x86_64',
			'debian iceweasel', 'rmx2101', 'mi note pro', 'rmx1921', 'nokia6100', '04.01',
			'fr-fr', 'slackware', 'sm-a225f', 'fennec', 'links', 'i386', 'windows phone os',
			'blackberry', 'maxthon', 'opera mini', 'j2me', 'winnt4.0', 'phoenix', 'avant browser',
			'iceweasel', 'moto e(7) plus', 'like geckoo', 'wpdesktop', 'nokia', 'lumia', 'arm',
			'de-at', 'pixel', 'puffin', 'zte blade a7', 'linux armv7l', 'hd1913', 'symbianos',
			'symbian os', 'de', '452', 'opera [en-us]', 'iemobile', 'windows phone', 'sm-g991b',
			'sm-j810g', 'da-dk', 'symbian', 'series60', 'nokiax7-00', 'freebsd amd64', 'openbsd amd64',
			'sm-n920c', 'blazer', 'palmsource', '16;320x320', 'sm-g998b', 'sm-a505g', 'freebsd i386',
			'jaunty', 'shiretoko', 'playbook', 'rim tablet os', 'asus;galaxy6', 'minimo',
			'linux arm7tdmi', 'blackberry7520', 'dl1036', '100011886a', 'lt-gtklauncher',
			'browserng', 'nokiae7-00', 'ubuntu chromium', 'silk-accelerated=true', 'openbsd i386',
			'windows ce', 'microsoft zunehd', 'epiphany', 'es-es', 'ru-ru', 'netbsd', 'ipod',
			'safari', 'xbox', 'xbox one', 'fxios', 'opx', 'ucbrowser', 'u3',
			'webos', 'desktop', 'compatible msie windows nt', 'sm-a525f', 'sm-g991u', 'ze520kl',
			'cros i686', 'de-de', 'en-ca', 'config', 'i686', 'sm-g970u', 'win95', 'i',
			'nokia7250', 'oneplus a6003', 'i2126', 'nintendo wii', 'vog-l29', 'msoffice', 'ms-office',
			'oneplus a5010', 'linux mint', 'blackberry8320', 'observatory', 'qdesk',
			'alexatoolbar', 'se metasr', 'qqdownload', 'alexa toolbar', 'baiduclient', 'ddg_android',
			'com.duckduckgo.mobile.android', 'android api', 'duckduckgo', 'googletoolbar', 'amaya',
		];
		if (!in_array($part, $known) && !preg_match('=^rv:[\d]+\S*$=', $part)) {
			$cleaned[] = $part;
		}
	}
	return $cleaned;
}

function blockbot_clean_part(string $part): string
{
	$part = trim($part);
	$subparts = [];
	foreach (explode(' ', $part) as $subpart) {
		$subpart = trim($subpart, ' +,');
		if (!empty($subpart) && (!preg_match('=^\d+[\w\-\+\.]+$=', $subpart) || empty($subparts))) {
			$subparts[] = $subpart;
		}
	}
	return implode(' ', $subparts);
}

function blockbot_split_parts(string $agent, bool $parse_spaces, bool $parse_semicolon): array
{
	$agent = strtolower(trim($agent, ' ;'));
	$cleaned = [];

	while (preg_match('=\w+[\s\w/\._\-]*/\d+[^;\s]*=', $agent, $matches)) {
		$part = $matches[0];
		if (preg_match('=/\d+[^;\s]*=', $part, $matches, PREG_OFFSET_CAPTURE)) {
			$cleaned[] = substr($part, 0, $matches[0][1]);
			$part = substr($part, 0, $matches[0][1] +  strlen($matches[0][0]));
		}
		$agent = trim(str_replace($part, '', $agent));
	}
	if ($parse_semicolon && strpos($agent, ';') !== false) {
		$parse_spaces = false;
		$parts = [];
		foreach (explode(';', $agent) as $part) {
			$parts[] = blockbot_clean_part($part);
		}
	} elseif (strpos($agent, ' - ') !== false) {
		$parts = [];
		foreach (explode(' - ', $agent) as $part) {
			$parts[] = blockbot_clean_part($part);
		}
	} elseif ($parse_spaces) {
		$parts = explode(' ', $agent);
	} else {
		$parts = [$agent];
	}

	if ($parse_spaces) {
		$subparts = [];
		foreach ($parts as $part) {
			while (($pos_space = strpos($part, ' ')) !== false && ($pos_slash = strpos($part, '/')) !== false) {
				if ($pos_space > $pos_slash) {
					$subparts[] = substr($part, 0, $pos_space);
					$part = trim(substr($part, $pos_space + 1), ' +,-;');
				} else {
					$subparts[] = $part;
					$part = '';
				}
			}
			if ($part != '') {
				$subparts[] = $part;
			}
		}
		$parts = $subparts;
	}

	foreach ($parts as $part) {
		$part = trim($part, ' +');

		if (!Network::isValidHttpUrl($part) && strpos($part, '/') !== false) {
			$split = explode('/', $part);
			array_pop($split);
			$part = implode('/', $split);
		}

		$pos1 = strpos($part, "'");
		$pos2 = strrpos($part, "'");
		if ($pos1 != $pos2) {
			$part = substr($part, 0, $pos1 - 1) . substr($part, $pos2 + 1);
		}

		$part = trim(preg_replace('=(.*) [\d\.]+=', '$1', $part), " +,-;\u{00AD}");
		if (!empty($part)) {
			$cleaned[] = $part;
		}
	}
	return $cleaned;
}
