<?php
/**
 * Name: Tumblr Post Connector
 * Description: Post to Tumblr
 * Version: 2.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'tumblroauth.php';

use Friendica\Content\PageInfo;
use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\HTML;
use Friendica\Content\Text\NPF;
use Friendica\Core\Cache\Enum\Duration;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Protocol;
use Friendica\Core\Renderer;
use Friendica\Core\System;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\Item;
use Friendica\Model\ItemURI;
use Friendica\Model\Photo;
use Friendica\Model\Post;
use Friendica\Model\Tag;
use Friendica\Protocol\Activity;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Network;
use Friendica\Util\Strings;

define('TUMBLR_DEFAULT_POLL_INTERVAL', 10); // given in minutes

function tumblr_install()
{
	Hook::register('hook_fork',               __FILE__, 'tumblr_hook_fork');
	Hook::register('post_local',              __FILE__, 'tumblr_post_local');
	Hook::register('notifier_normal',         __FILE__, 'tumblr_send');
	Hook::register('jot_networks',            __FILE__, 'tumblr_jot_nets');
	Hook::register('connector_settings',      __FILE__, 'tumblr_settings');
	Hook::register('connector_settings_post', __FILE__, 'tumblr_settings_post');
	Hook::register('cron'                   , __FILE__, 'tumblr_cron');
}

/**
 * This is a statement rather than an actual function definition. The simple
 * existence of this method is checked to figure out if the addon offers a
 * module.
 */
function tumblr_module()
{
}

function tumblr_content()
{
	if (!DI::userSession()->getLocalUserId()) {
		DI::sysmsg()->addNotice(DI::l10n()->t('Permission denied.'));
		return '';
	}

	if (!isset(DI::args()->getArgv()[1])) {
		DI::baseUrl()->redirect('settings/connectors/tumblr');
	}

	switch (DI::args()->getArgv()[1]) {
		case 'connect':
			$o = tumblr_connect();
			break;

		case 'callback':
			$o = tumblr_callback();
			break;

		default:
			DI::baseUrl()->redirect('settings/connectors/tumblr');
			break;
	}

	return $o;
}

function tumblr_connect()
{
	// Define the needed keys
	$consumer_key    = DI::config()->get('tumblr', 'consumer_key');
	$consumer_secret = DI::config()->get('tumblr', 'consumer_secret');

	if (empty($consumer_key) || empty($consumer_secret)) {
		DI::baseUrl()->redirect('settings/connectors/tumblr');
	}

	// The callback URL is the script that gets called after the user authenticates with tumblr
	// In this example, it would be the included callback.php
	$callback_url = DI::baseUrl() . '/tumblr/callback';

	// Let's begin. First we need a Request Token. The request token is required to send the user
	// to Tumblr's login page.

	// Create a new instance of the TumblrOAuth library. For this step, all we need to give the library is our
	// Consumer Key and Consumer Secret
	$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret);

	// Ask Tumblr for a Request Token. Specify the Callback URL here too (although this should be optional)
	$request_token = $tum_oauth->getRequestToken($callback_url);

	if (empty($request_token)) {
		// Give an error message
		return DI::l10n()->t('Could not connect to Tumblr. Refresh the page or try again later.');
	}

	// Store the request token and Request Token Secret as out callback.php script will need this
	DI::session()->set('request_token', $request_token['oauth_token']);
	DI::session()->set('request_token_secret', $request_token['oauth_token_secret']);

	// Ask Tumblr to give us a special address to their login page
	$url = $tum_oauth->getAuthorizeURL($request_token['oauth_token']);

	// Redirect the user to the login URL given to us by Tumblr
	System::externalRedirect($url);

	/*
	 * That's it for our side.  The user is sent to a Tumblr Login page and
	 * asked to authroize our app.  After that, Tumblr sends the user back to
	 * our Callback URL (callback.php) along with some information we need to get
	 * an access token.
	 */
}

function tumblr_callback()
{
	// Define the needed keys
	$consumer_key    = DI::config()->get('tumblr', 'consumer_key');
	$consumer_secret = DI::config()->get('tumblr', 'consumer_secret');

	if (empty($_REQUEST['oauth_verifier']) || empty($consumer_key) || empty($consumer_secret)) {
		DI::baseUrl()->redirect('settings/connectors/tumblr');
	}

	// Once the user approves your app at Tumblr, they are sent back to this script.
	// This script is passed two parameters in the URL, oauth_token (our Request Token)
	// and oauth_verifier (Key that we need to get Access Token).
	// We'll also need out Request Token Secret, which we stored in a session.

	// Create instance of TumblrOAuth.
	// It'll need our Consumer Key and Secret as well as our Request Token and Secret
	$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret);

	// Ok, let's get an Access Token. We'll need to pass along our oauth_verifier which was given to us in the URL.
	$access_token = $tum_oauth->getAccessToken($_REQUEST['oauth_verifier'], DI::session()->get('request_token'), DI::session()->get('request_token_secret'));

	// We're done with the Request Token and Secret so let's remove those.
	DI::session()->remove('request_token');
	DI::session()->remove('request_token_secret');

	if (empty($access_token)) {
		return DI::l10n()->t('Unable to authenticate');
	}

	// What's next?  Now that we have an Access Token and Secret, we can make an API call.
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'oauth_token', $access_token['oauth_token']);
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'oauth_token_secret', $access_token['oauth_token_secret']);

	DI::baseUrl()->redirect('settings/connectors/tumblr');
}

function tumblr_addon_admin(string &$o)
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/tumblr/');

	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		// name, label, value, help, [extra values]
		'$consumer_key'    => ['consumer_key', DI::l10n()->t('Consumer Key'), DI::config()->get('tumblr', 'consumer_key'), ''],
		'$consumer_secret' => ['consumer_secret', DI::l10n()->t('Consumer Secret'), DI::config()->get('tumblr', 'consumer_secret'), ''],
	]);
}

function tumblr_addon_admin_post()
{
	DI::config()->set('tumblr', 'consumer_key', trim($_POST['consumer_key'] ?? ''));
	DI::config()->set('tumblr', 'consumer_secret', trim($_POST['consumer_secret'] ?? ''));
}

function tumblr_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post', false);
	$def_enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post_by_default', false);
	$import      = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'import', false);

	$cachekey = 'tumblr-blogs-' . DI::userSession()->getLocalUserId();
	$blogs = DI::cache()->get($cachekey);
	if (empty($blogs)) {
		$blogs = tumblr_get_blogs(DI::userSession()->getLocalUserId());
		if (!empty($blogs)) {
			DI::cache()->set($cachekey, $blogs, Duration::HALF_HOUR);
		}
	} elseif (empty(tumblr_connection(DI::userSession()->getLocalUserId()))) {
		$blogs = null;
		DI::cache()->delete($cachekey);
	}

	if (!empty($blogs)) {
		$page = tumblr_get_page(DI::userSession()->getLocalUserId(), $blogs);

		$page_select = ['tumblr_page', DI::l10n()->t('Post to page:'), $page, '', $blogs];
	}

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/tumblr/');
	$html = Renderer::replaceMacros($t, [
		'$l10n' => [
			'connect'   => DI::l10n()->t('(Re-)Authenticate your tumblr page'),
			'noconnect' => DI::l10n()->t('You are not authenticated to tumblr'),
		],

		'$authenticate_url' => DI::baseUrl() . '/tumblr/connect',

		'$enable'      => ['tumblr', DI::l10n()->t('Enable Tumblr Post Addon'), $enabled],
		'$bydefault'   => ['tumblr_bydefault', DI::l10n()->t('Post to Tumblr by default'), $def_enabled],
		'$import'      => ['tumblr_import', DI::l10n()->t('Import the remote timeline'), $import],
		'$page_select' => $page_select ?? '',
	]);

	$data = [
		'connector' => 'tumblr',
		'title'     => DI::l10n()->t('Tumblr Export'),
		'image'     => 'images/tumblr.png',
		'enabled'   => $enabled,
		'html'      => $html,
	];
}

function tumblr_jot_nets(array &$jotnets_fields)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'tumblr_enable',
				DI::l10n()->t('Post to Tumblr'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post_by_default')
			]
		];
	}
}

function tumblr_settings_post(array &$b)
{
	if (!empty($_POST['tumblr-submit'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'post',            intval($_POST['tumblr']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'page',            $_POST['tumblr_page']);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'post_by_default', intval($_POST['tumblr_bydefault']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'import',          intval($_POST['tumblr_import']));
	}
}

function tumblr_hook_fork(array &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if (
		$post['deleted'] || $post['private'] || ($post['created'] !== $post['edited']) ||
		!strstr($post['postopts'] ?? '', 'tumblr') || ($post['parent'] != $post['id'])
	) {
		$b['execute'] = false;
		return;
	}
}

function tumblr_post_local(array &$b)
{
	// This can probably be changed to allow editing by pointing to a different API endpoint

	if ($b['edit']) {
		return;
	}

	if (!DI::userSession()->getLocalUserId() || (DI::userSession()->getLocalUserId() != $b['uid'])) {
		return;
	}

	if ($b['private'] || $b['parent']) {
		return;
	}

	$tmbl_post   = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post'));
	$tmbl_enable = (($tmbl_post && !empty($_REQUEST['tumblr_enable'])) ? intval($_REQUEST['tumblr_enable']) : 0);

	// if API is used, default to the chosen settings
	if ($b['api_source'] && intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post_by_default'))) {
		$tmbl_enable = 1;
	}

	if (!$tmbl_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'tumblr';
}

function tumblr_send(array &$b)
{
	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if (!strstr($b['postopts'], 'tumblr')) {
		return;
	}

	if ($b['gravity'] != Item::GRAVITY_PARENT) {
		return;
	}

	if (tumblr_send_npf($b)) {
		return;
	}

	$connection = tumblr_connection($b['uid']);
	if (empty($connection)) {
		return;
	}

	$b['body'] = BBCode::removeAttachment($b['body']);

	$title = trim($b['title']);

	$media = Post\Media::getByURIId($b['uri-id'], [Post\Media::HTML, Post\Media::AUDIO, Post\Media::VIDEO, Post\Media::IMAGE]);

	$photo = array_search(Post\Media::IMAGE, array_column($media, 'type'));
	$link  = array_search(Post\Media::HTML, array_column($media, 'type'));
	$audio = array_search(Post\Media::AUDIO, array_column($media, 'type'));
	$video = array_search(Post\Media::VIDEO, array_column($media, 'type'));

	$params = [
		'state'  => 'published',
		'tags'   => implode(',', array_column(Tag::getByURIId($b['uri-id']), 'name')),
		'tweet'  => 'off',
		'format' => 'html',
	];

	$body = BBCode::removeShareInformation($b['body']);
	$body = Post\Media::removeFromEndOfBody($body);

	if ($photo !== false) {
		$params['type'] = 'photo';
		$params['caption'] = BBCode::convertForUriId($b['uri-id'], $body, BBCode::CONNECTORS);
		$params['data'] = [];
		foreach ($media as $photo) {
			if ($photo['type'] == Post\Media::IMAGE) {
				if (Network::isLocalLink($photo['url']) && ($data = Photo::getResourceData($photo['url']))) {
					$photo = Photo::selectFirst([], ["`resource-id` = ? AND `scale` > ?", $data['guid'], 0]);
					if (!empty($photo)) {
						$params['data'][] = Photo::getImageDataForPhoto($photo);
					}
				}
			}
		}
	} elseif ($link !== false) {
		$params['type']        = 'link';
		$params['title']       = $media[$link]['name'];
		$params['url']         = $media[$link]['url'];
		$params['description'] = BBCode::convertForUriId($b['uri-id'], $body, BBCode::CONNECTORS);

		if (!empty($media[$link]['preview'])) {
			$params['thumbnail'] = $media[$link]['preview'];
		}
		if (!empty($media[$link]['description'])) {
			$params['excerpt'] = $media[$link]['description'];
		}
		if (!empty($media[$link]['author-name'])) {
			$params['author'] = $media[$link]['author-name'];
		}
	} elseif ($audio !== false) {
		$params['type']         = 'audio';
		$params['external_url'] = $media[$audio]['url'];
		$params['caption']      = BBCode::convertForUriId($b['uri-id'], $body, BBCode::CONNECTORS);
	} elseif ($video !== false) {
		$params['type']    = 'video';
		$params['embed']   = $media[$video]['url'];
		$params['caption'] = BBCode::convertForUriId($b['uri-id'], $body, BBCode::CONNECTORS);
	} else {
		$params['type']  = 'text';
		$params['title'] = $title;
		$params['body']  = BBCode::convertForUriId($b['uri-id'], $b['body'], BBCode::CONNECTORS);
	}

	if (isset($params['caption']) && (trim($title) != '')) {
		$params['caption'] = '<h1>' . $title . '</h1>' .
			'<p>' . $params['caption'] . '</p>';
	}

	$page = tumblr_get_page($b['uid']);

	$result = $connection->post('blog/' . $page . '/post', $params);

	if ($result->meta->status < 400) {
		Logger::info('Success (legacy)', ['blog' => $page, 'meta' => $result->meta, 'response' => $result->response]);
		return true;
	} else {
		Logger::notice('Error posting blog (legacy)', ['blog' => $page, 'meta' => $result->meta, 'response' => $result->response, 'errors' => $result->errors, 'params' => $params]);
		return false;
	}
}

function tumblr_send_npf(array $post): bool
{
	$page = tumblr_get_page($post['uid']);

	$connection = tumblr_connection($post['uid']);
	if (empty($page)) {
		Logger::notice('Missing page, post will not be send to Tumblr.', ['uid' => $post['uid'], 'page' => $page, 'id' => $post['id']]);
		// "true" is returned, since the legacy function will fail as well.
		return true;
	}

	$post['body'] = Post\Media::addAttachmentsToBody($post['uri-id'], $post['body']);
	if (!empty($post['title'])) {
		$post['body'] = '[h1]' . $post['title'] . "[/h1]\n" . $post['body'];
	}

	$params = [
		'content'                => NPF::fromBBCode($post['body'], $post['uri-id']),
		'state'                  => 'published',
		'date'                   => DateTimeFormat::utc($post['created'], DateTimeFormat::ATOM),
		'tags'                   => implode(',', array_column(Tag::getByURIId($post['uri-id']), 'name')),
		'is_private'             => false,
		'interactability_reblog' => 'everyone'
	];

	$result = $connection->post('blog/' . $page . '/posts', $params);

	if ($result->meta->status < 400) {
		Logger::info('Success (NPF)', ['blog' => $page, 'meta' => $result->meta, 'response' => $result->response]);
		return true;
	} else {
		Logger::notice('Error posting blog (NPF)', ['blog' => $page, 'meta' => $result->meta, 'response' => $result->response, 'errors' => $result->errors, 'params' => $params]);
		return false;
	}
}

function tumblr_cron()
{
	$last = DI::keyValue()->get('tumblr_last_poll');

	$poll_interval = intval(DI::config()->get('tumblr', 'poll_interval'));
	if (!$poll_interval) {
		$poll_interval = TUMBLR_DEFAULT_POLL_INTERVAL;
	}

	if ($last) {
		$next = $last + ($poll_interval * 60);
		if ($next > time()) {
			Logger::notice('poll intervall not reached');
			return;
		}
	}
	Logger::notice('cron_start');

	$abandon_days = intval(DI::config()->get('system', 'account_abandon_days'));
	if ($abandon_days < 1) {
		$abandon_days = 0;
	}

	$abandon_limit = date(DateTimeFormat::MYSQL, time() - $abandon_days * 86400);

	$pconfigs = DBA::selectToArray('pconfig', [], ['cat' => 'tumblr', 'k' => 'import', 'v' => true]);
	foreach ($pconfigs as $pconfig) {
		if ($abandon_days != 0) {
			if (!DBA::exists('user', ["`uid` = ? AND `login_date` >= ?", $pconfig['uid'], $abandon_limit])) {
				Logger::notice('abandoned account: timeline from user will not be imported', ['user' => $pconfig['uid']]);
				continue;
			}
		}

		Logger::notice('importing timeline - start', ['user' => $pconfig['uid']]);
		tumblr_fetch_dashboard($pconfig['uid']);
		Logger::notice('importing timeline - done', ['user' => $pconfig['uid']]);
	}

	Logger::notice('cron_end');

	DI::keyValue()->set('tumblr_last_poll', time());
}

function tumblr_add_npf_data(string $html, string $plink): string
{
	$doc = new DOMDocument();

	$doc->formatOutput = true;
	@$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
	$xpath = new DomXPath($doc);
	$list = $xpath->query('//p[@class="npf_link"]');
	foreach ($list as $node) {
		$data = tumblr_get_npf_data($node);
		if (empty($data)) {
			continue;
		}

		tumblr_replace_with_npf($doc, $node, tumblr_get_type_replacement($data, $plink));
	}

	$list = $xpath->query('//div[@data-npf]');
	foreach ($list as $node) {
		$data = tumblr_get_npf_data($node);
		if (empty($data)) {
			continue;
		}

		tumblr_replace_with_npf($doc, $node, tumblr_get_type_replacement($data, $plink));
	}

	$list = $xpath->query('//figure[@data-provider="youtube"]');
	foreach ($list as $node) {
		$attributes = tumblr_get_attributes($node);
		if (empty($attributes['data-url'])) {
			continue;
		}
		tumblr_replace_with_npf($doc, $node, '[youtube]' . $attributes['data-url'] . '[/youtube]');
	}

	return $doc->saveHTML();
}

function tumblr_get_type_replacement(array $data, string $plink): string
{
	switch ($data['type']) {
		case 'poll':
			$body = '[p][url=' . $plink. ']'. $data['question'] . '[/url][/p][ul]';
			foreach ($data['answers'] as $answer) {
				$body .= '[li]' . $answer['answer_text'] . '[/li]';
			}
			$body .= '[/ul]';
			break;

		case 'link':
			$body = PageInfo::getFooterFromUrl($data['url']);

		default:
			Logger::notice('Unknown type', ['type' => $data['type'], 'data' => $data, 'plink' => $plink]);
			$body = '';
	}

	return $body;
}

function tumblr_get_attributes($node): array
{
	$attributes = [];
	foreach ($node->attributes as $key => $attribute) {
		$attributes[$key] = trim($attribute->value);
	}
	return $attributes;
}

function tumblr_get_npf_data(DOMNode $node): array
{
	$attributes = tumblr_get_attributes($node);
	if (empty($attributes['data-npf'])) {
		return [];
	}

	return json_decode($attributes['data-npf'], true);
}

function tumblr_replace_with_npf(DOMDocument $doc, DOMNode $node, string $replacement)
{
	$replace = $doc->createTextNode($replacement);
	$node->parentNode->insertBefore($replace, $node);
	$node->parentNode->removeChild($node);
}

function tumblr_fetch_dashboard(int $uid)
{
	$page = tumblr_get_page($uid);

	$parameters = ['reblog_info' => false, 'notes_info' => false, 'npf' => false];

	$last = DI::pConfig()->get($uid, 'tumblr', 'last_id');
	if (!empty($last)) {
		$parameters['since_id'] = $last;
	}

	$connection = tumblr_connection($uid);
	$dashboard = $connection->get('user/dashboard', $parameters);
	if ($dashboard->meta->status > 399) {
		Logger::notice('Error fetching dashboard', ['meta' => $dashboard->meta, 'response' => $dashboard->response, 'errors' => $dashboard->errors]);
		return [];
	}

	if (empty($dashboard->response->posts)) {
		return;
	}

	foreach (array_reverse($dashboard->response->posts) as $post) {
		$uri = 'tumblr::' . $post->id_string;

		if ($post->id > $last) {
			$last = $post->id;
		}

		if (Post::exists(['uri' => $uri, 'uid' => $uid]) || ($post->blog->uuid == $page)) {
			DI::pConfig()->set($uid, 'tumblr', 'last_id', $last);
			continue;
		}

		$item = tumblr_get_header($post, $uri, $uid);

		$item = tumblr_get_content($item, $post);
		item::insert($item);

		DI::pConfig()->set($uid, 'tumblr', 'last_id', $last);
	}
}

function tumblr_get_header(stdClass $post, string $uri, int $uid): array
{
	$contact = tumblr_get_contact($post->blog, $uid);
	$item = [
		'network'       => Protocol::TUMBLR,
		'uid'           => $uid,
		'wall'          => false,
		'uri'           => $uri,
		'private'       => Item::UNLISTED,
		'verb'          => Activity::POST,
		'contact-id'    => $contact['id'],
		'author-name'   => $contact['name'],
		'author-link'   => $contact['url'],
		'author-avatar' => $contact['avatar'],
		'plink'         => $post->post_url,
		'created'       => date(DateTimeFormat::MYSQL, $post->timestamp)
	];

	$item['owner-name']   = $item['author-name'];
	$item['owner-link']   = $item['author-link'];
	$item['owner-avatar'] = $item['author-avatar'];

	// @todo process $post->tags;

	return $item;
}

function tumblr_get_content(array $item, stdClass $post): array
{
	switch ($post->type) {
		case 'text':
			$item['title'] = $post->title;
			$item['body'] = HTML::toBBCode(tumblr_add_npf_data($post->body, $post->post_url));
			break;

		case 'quote':
			if (empty($post->text)) {
				$body = HTML::toBBCode($post->text) . "\n";
			} else {
				$body = '';
			}
			if (!empty($post->source_title) && !empty($post->source_url)) {
				$body .= '[url=' . $post->source_url . ']' . $post->source_title . "[/url]:\n";
			} elseif (!empty($post->source_title)) {
				$body .= $post->source_title . ":\n";
			}
			$body .= '[quote]' . HTML::toBBCode($post->source) . '[/quote]';
			$item['body'] = $body;
			break;

		case 'link':
			$item['body'] = HTML::toBBCode($post->description) . "\n" . PageInfo::getFooterFromUrl($post->url);
			break;

		case 'answer':
			if (!empty($post->asking_name) && !empty($post->asking_url)) {
				$body = '[url=' . $post->asking_url . ']' . $post->asking_name . "[/url]:\n";
			} elseif (!empty($post->asking_name)) {
				$body = $post->asking_name . ":\n";
			} else {
				$body = '';
			}
			$body .= '[quote]' . HTML::toBBCode($post->question) . "[/quote]\n" . HTML::toBBCode($post->answer);
			$item['body'] = $body;
			break;

		case 'video':
			$item['body'] = HTML::toBBCode($post->caption);
			if (!empty($post->video_url)) {
				$item['body'] .= "\n[video]" . $post->video_url . "[/video]\n";
			} elseif(!empty($post->thumbnail_url)) {
				$item['body'] .= "\n[url=" . $post->permalink_url ."][img]" . $post->thumbnail_url . "[/img][/url]\n";
			} elseif(!empty($post->permalink_url)) {
				$item['body'] .= "\n[url]" . $post->permalink_url ."[/url]\n";
			} elseif(!empty($post->source_url) && !empty($post->source_title)) {
				$item['body'] .= "\n[url=" . $post->source_url ."]" . $post->source_title . "[/url]\n";
			} elseif(!empty($post->source_url)) {
				$item['body'] .= "\n[url]" . $post->source_url ."[/url]\n";
			}
			break;

		case 'audio':
			$item['body'] = HTML::toBBCode($post->caption);
			if(!empty($post->source_url) && !empty($post->source_title)) {
				$item['body'] .= "\n[url=" . $post->source_url ."]" . $post->source_title . "[/url]\n";
			} elseif(!empty($post->source_url)) {
				$item['body'] .= "\n[url]" . $post->source_url ."[/url]\n";
			}
			break;

		case 'photo':
			$item['body'] = HTML::toBBCode($post->caption);
			foreach ($post->photos as $photo) {
				if (!empty($photo->original_size)) {
					$item['body'] .= "\n[img]" . $photo->original_size->url . "[/img]";
				} elseif (!empty($photo->alt_sizes)) {
					$item['body'] .= "\n[img]" . $photo->alt_sizes[0]->url . "[/img]";
				}
			}
			break;

		case 'chat':
			$item['title'] = $post->title;
			$item['body']  = "\n[ul]";
			foreach ($post->dialogue as $line) {
				$item['body'] .= "\n[li]" . $line->label . " " . $line->phrase . "[/li]";
			}
			$item['body'] .= "[/ul]\n";
			break;
	}
	return $item;
}

function tumblr_get_contact(stdClass $blog, int $uid)
{
	$condition = ['network' => Protocol::TUMBLR, 'uid' => $uid, 'poll' => 'tumblr::' . $blog->uuid];
	$contact = Contact::selectFirst([], $condition);
	if (!empty($contact) && (strtotime($contact['updated']) >= $blog->updated)) {
		return $contact;
	}
	if (empty($contact)) {
		$cid = tumblr_insert_contact($blog, $uid);
	} else {
		$cid = $contact['id'];
	}

	$condition['uid'] = 0;

	$contact = Contact::selectFirst([], $condition);
	if (empty($contact)) {
		$pcid = tumblr_insert_contact($blog, 0);
	} else {
		$pcid = $contact['id'];
	}

	tumblr_update_contact($blog, $uid, $cid, $pcid);

	return Contact::getById($cid);
}

function tumblr_insert_contact(stdClass $blog, int $uid)
{
	$baseurl = 'https://tumblr.com';
	$url     = $baseurl . '/' . $blog->name;

	$fields = [
		'uid'      => $uid,
		'network'  => Protocol::TUMBLR,
		'poll'     => 'tumblr::' . $blog->uuid,
		'baseurl'  => $baseurl,
		'priority' => 1,
		'writable' => false, // @todo Allow interaction at a later point in time
		'blocked'  => false,
		'readonly' => false,
		'pending'  => false,
		'url'      => $url,
		'nurl'     => Strings::normaliseLink($url),
		'alias'    => $blog->url,
		'name'     => $blog->title,
		'nick'     => $blog->name,
		'addr'     => $blog->name . '@tumblr.com',
		'about'    => $blog->description,
		'updated'  => date(DateTimeFormat::MYSQL, $blog->updated)
	];
	return Contact::insert($fields);
}

function tumblr_update_contact(stdClass $blog, int $uid, int $cid, int $pcid)
{
	$connection = tumblr_connection($uid);
	$info = $connection->get('blog/' . $blog->uuid . '/info');
	if ($info->meta->status > 399) {
		Logger::notice('Error fetching dashboard', ['meta' => $info->meta, 'response' => $info->response, 'errors' => $info->errors]);
		return;
	}

	$avatar = $info->response->blog->avatar;
	if (!empty($avatar)) {
		Contact::updateAvatar($cid, $avatar[0]->url);
	}

	$baseurl = 'https://tumblr.com';
	$url     = $baseurl . '/' . $info->response->blog->name;

	if ($info->response->blog->followed && $info->response->blog->subscribed) {
		$rel = Contact::FRIEND;
	} elseif ($info->response->blog->followed && !$info->response->blog->subscribed) {
		$rel = Contact::SHARING;
	} elseif (!$info->response->blog->followed && $info->response->blog->subscribed) {
		$rel = Contact::FOLLOWER;
	} else {
		$rel = Contact::NOTHING;
	}

	$fields = [
		'url'     => $url,
		'nurl'    => Strings::normaliseLink($url),
		'uri-id'  => ItemURI::getIdByURI($url),
		'alias'   => $info->response->blog->url,
		'name'    => $info->response->blog->title,
		'nick'    => $info->response->blog->name,
		'addr'    => $info->response->blog->name . '@tumblr.com',
		'about'   => $info->response->blog->description,
		'updated' => date(DateTimeFormat::MYSQL, $info->response->blog->updated),
		'header'  => $info->response->blog->theme->header_image_focused,
		'rel'     => $rel,
	];

	Contact::update($fields, ['id' => $cid]);

	$fields['rel'] = Contact::NOTHING;
	Contact::update($fields, ['id' => $pcid]);
}

function tumblr_connection(int $uid): ?TumblrOAuth
{
	$oauth_token        = DI::pConfig()->get($uid, 'tumblr', 'oauth_token');
	$oauth_token_secret = DI::pConfig()->get($uid, 'tumblr', 'oauth_token_secret');

	$consumer_key    = DI::config()->get('tumblr', 'consumer_key');
	$consumer_secret = DI::config()->get('tumblr', 'consumer_secret');

	if (!$consumer_key || !$consumer_secret || !$oauth_token || !$oauth_token_secret) {
		Logger::notice('Missing data, connection is not established', ['uid' => $uid]);
		return null;
	}

	return new TumblrOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
}

function tumblr_get_page(int $uid, array $blogs = []): string
{
	$page = DI::pConfig()->get($uid, 'tumblr', 'page');

	if (!empty($page) && (strpos($page, '/') === false)) {
		return $page;
	}

	if (empty($blogs)) {
		$blogs = tumblr_get_blogs($uid);
	}

	if (!empty($blogs)) {
		$page = array_key_first($blogs);
		DI::pConfig()->set($uid, 'tumblr', 'page', $page);
		return $page;
	}

	return '';
}

function tumblr_get_blogs(int $uid): array
{
	$connection = tumblr_connection($uid);
	if (empty($connection)) {
		return [];
	}

	$userinfo = $connection->get('user/info');
	if ($userinfo->meta->status > 299) {
		Logger::notice('Error fetching blogs', ['meta' => $userinfo->meta, 'response' => $userinfo->response, 'errors' => $userinfo->errors]);
		return [];
	}

	$blogs = [];
	foreach ($userinfo->response->user->blogs as $blog) {
		$blogs[$blog->uuid] = $blog->name;
	}
	return $blogs;
}