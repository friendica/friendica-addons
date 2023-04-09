<?php
/**
 * Name: Tumblr Post Connector
 * Description: Post to Tumblr
 * Version: 2.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'tumblroauth.php';

use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\NPF;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\System;
use Friendica\DI;
use Friendica\Model\Item;
use Friendica\Model\Photo;
use Friendica\Model\Post;
use Friendica\Model\Tag;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Network;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

function tumblr_install()
{
	Hook::register('hook_fork',               'addon/tumblr/tumblr.php', 'tumblr_hook_fork');
	Hook::register('post_local',              'addon/tumblr/tumblr.php', 'tumblr_post_local');
	Hook::register('notifier_normal',         'addon/tumblr/tumblr.php', 'tumblr_send');
	Hook::register('jot_networks',            'addon/tumblr/tumblr.php', 'tumblr_jot_nets');
	Hook::register('connector_settings',      'addon/tumblr/tumblr.php', 'tumblr_settings');
	Hook::register('connector_settings_post', 'addon/tumblr/tumblr.php', 'tumblr_settings_post');
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

	if (isset(DI::args()->getArgv()[1])) {
		switch (DI::args()->getArgv()[1]) {
			case 'connect':
				$o = tumblr_connect();
				break;

			case 'callback':
				$o = tumblr_callback();
				break;

			default:
				$o = print_r(DI::args()->getArgv(), true);
				break;
		}
	} else {
		$o = tumblr_connect();
	}

	return $o;
}

function tumblr_addon_admin(string &$o)
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/tumblr/');

	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		// name, label, value, help, [extra values]
		'$consumer_key' => ['consumer_key', DI::l10n()->t('Consumer Key'), DI::config()->get('tumblr', 'consumer_key'), ''],
		'$consumer_secret' => ['consumer_secret', DI::l10n()->t('Consumer Secret'), DI::config()->get('tumblr', 'consumer_secret'), ''],
	]);
}

function tumblr_addon_admin_post()
{
	DI::config()->set('tumblr', 'consumer_key', trim($_POST['consumer_key'] ?? ''));
	DI::config()->set('tumblr', 'consumer_secret', trim($_POST['consumer_secret'] ?? ''));
}

function tumblr_connect()
{
	// Start a session.  This is necessary to hold on to  a few keys the callback script will also need
	session_start();

	// Define the needed keys
	$consumer_key = DI::config()->get('tumblr', 'consumer_key');
	$consumer_secret = DI::config()->get('tumblr', 'consumer_secret');

	// The callback URL is the script that gets called after the user authenticates with tumblr
	// In this example, it would be the included callback.php
	$callback_url = DI::baseUrl() . '/tumblr/callback';

	// Let's begin.  First we need a Request Token.  The request token is required to send the user
	// to Tumblr's login page.

	// Create a new instance of the TumblrOAuth library.  For this step, all we need to give the library is our
	// Consumer Key and Consumer Secret
	$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret);

	// Ask Tumblr for a Request Token.  Specify the Callback URL here too (although this should be optional)
	$request_token = $tum_oauth->getRequestToken($callback_url);

	// Store the request token and Request Token Secret as out callback.php script will need this
	DI::session()->set('request_token', $request_token['oauth_token']);
	DI::session()->set('request_token_secret', $request_token['oauth_token_secret']);

	// Check the HTTP Code.  It should be a 200 (OK), if it's anything else then something didn't work.
	switch ($tum_oauth->http_code) {
		case 200:
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
			break;

		default:
			// Give an error message
			$o = 'Could not connect to Tumblr. Refresh the page or try again later.';
	}

	return $o;
}

function tumblr_callback()
{
	// Start a session, load the library
	session_start();

	// Define the needed keys
	$consumer_key    = DI::config()->get('tumblr', 'consumer_key');
	$consumer_secret = DI::config()->get('tumblr', 'consumer_secret');

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

	// Make sure nothing went wrong.
	if (200 == $tum_oauth->http_code) {
		// good to go
	} else {
		return 'Unable to authenticate';
	}

	// What's next?  Now that we have an Access Token and Secret, we can make an API call.
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'oauth_token', $access_token['oauth_token']);
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'oauth_token_secret', $access_token['oauth_token_secret']);

	$o = DI::l10n()->t('You are now authenticated to tumblr.');
	$o .= '<br /><a href="' . DI::baseUrl() . '/settings/connectors">' . DI::l10n()->t("return to the connector page") . '</a>';

	return $o;
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

function tumblr_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post', false);
	$def_enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'post_by_default', false);

	$oauth_token        = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'oauth_token');
	$oauth_token_secret = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'oauth_token_secret');
	$consumer_key       = DI::config()->get('tumblr', 'consumer_key');
	$consumer_secret    = DI::config()->get('tumblr', 'consumer_secret');

	if ($consumer_key && $consumer_secret && $oauth_token && $oauth_token_secret) {
		$page = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'tumblr', 'page');

		$blogs = [];

		$connection = tumblr_client($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
		if ($connection) {
			$userinfo = tumblr_get($connection, 'user/info');
			if (!empty($userinfo['success'])) {
				foreach ($userinfo['data']->response->user->blogs as $blog) {
					$blogs[$blog->uuid] = $blog->name;
				}
			}

			if (empty($page) && !empty($blogs)) {
				$page = reset($blogs);
				DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'page', $page);
			}
		}

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

function tumblr_settings_post(array &$b)
{
	if (!empty($_POST['tumblr-submit'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'post',            intval($_POST['tumblr']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'page',            $_POST['tumblr_page']);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'tumblr', 'post_by_default', intval($_POST['tumblr_bydefault']));
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

	$page = DI::pConfig()->get($b['uid'], 'tumblr', 'page');

	$result = tumblr_post($connection, 'blog/' . $page . '/post', $params);

	if ($result['success']) {
		Logger::info('success', ['blog' => $page, 'params' => $params]);
		return true;
	} else {
		Logger::notice('error', ['blog' => $page, 'params' => $params, 'result' => $result['data']]);
		return false;
	}
}

function tumblr_send_npf(array $post): bool
{
	$page = DI::pConfig()->get($post['uid'], 'tumblr', 'page');

	$connection = tumblr_connection($post['uid']);
	if (empty($connection)) {
		Logger::notice('Missing data, post will not be send to Tumblr.', ['uid' => $post['uid'], 'page' => $page, 'id' => $post['id']]);
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

	$result = tumblr_post($connection, 'blog/' . $page . '/posts', $params);

	if ($result['success']) {
		Logger::info('success', ['blog' => $page, 'params' => $params]);
		return true;
	} else {
		Logger::notice('error', ['blog' => $page, 'params' => $params, 'result' => $result['data']]);
		return false;
	}
}

function tumblr_connection(int $uid): GuzzleHttp\Client|null
{
	$oauth_token        = DI::pConfig()->get($uid, 'tumblr', 'oauth_token');
	$oauth_token_secret = DI::pConfig()->get($uid, 'tumblr', 'oauth_token_secret');

	$page = DI::pConfig()->get($uid, 'tumblr', 'page');

	$consumer_key    = DI::config()->get('tumblr', 'consumer_key');
	$consumer_secret = DI::config()->get('tumblr', 'consumer_secret');

	if (!$consumer_key || !$consumer_secret || !$oauth_token || !$oauth_token_secret || !$page) {
		Logger::notice('Missing data, connection is not established', ['uid' => $uid, 'page' => $page]);
		return null;
	}
	return tumblr_client($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
}

function tumblr_client(string $consumer_key, string $consumer_secret, string $oauth_token, string $oauth_token_secret): GuzzleHttp\Client
{
	$stack = HandlerStack::create();

	$middleware = new Oauth1([
		'consumer_key'    => $consumer_key,
		'consumer_secret' => $consumer_secret,
		'token'           => $oauth_token,
		'token_secret'    => $oauth_token_secret
	]);
	$stack->push($middleware);
	
	return new Client([
		'base_uri' => 'https://api.tumblr.com/v2/',
		'handler' => $stack
	]);
}

function tumblr_get($connection, string $url)
{
	try {
		$res = $connection->get($url, ['auth' => 'oauth']);

		$success = true;
		$data    = json_decode($res->getBody()->getContents());
	} catch (RequestException $exception) {
		$success = false;
		Logger::notice('Request failed', ['code' => $exception->getCode(), 'message' => $exception->getMessage()]);
		$data    = json_decode($exception->getResponse()->getBody()->getContents());
	}
	return ['success' => $success, 'data' => $data];
}

function tumblr_post($connection, string $url, array $parameter)
{
	try {
		$res = $connection->post($url, ['auth' => 'oauth', 'json' => $parameter]);

		$success = true;
		$data    = json_decode($res->getBody()->getContents());
	} catch (RequestException $exception) {
		$success = false;
		Logger::notice('Post failed', ['code' => $exception->getCode(), 'message' => $exception->getMessage()]);
		$data    = json_decode($exception->getResponse()->getBody()->getContents());
	}
	return ['success' => $success, 'data' => $data];
}