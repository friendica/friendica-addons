<?php
/**
 * Name: Tumblr Post Connector
 * Description: Post to Tumblr
 * Version: 2.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'tumblroauth.php';

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Post;
use Friendica\Model\Tag;

function tumblr_install()
{
	Hook::register('hook_fork',               'addon/tumblr/tumblr.php', 'tumblr_hook_fork');
	Hook::register('post_local',              'addon/tumblr/tumblr.php', 'tumblr_post_local');
	Hook::register('notifier_normal',         'addon/tumblr/tumblr.php', 'tumblr_send');
	Hook::register('jot_networks',            'addon/tumblr/tumblr.php', 'tumblr_jot_nets');
	Hook::register('connector_settings',      'addon/tumblr/tumblr.php', 'tumblr_settings');
	Hook::register('connector_settings_post', 'addon/tumblr/tumblr.php', 'tumblr_settings_post');
}

function tumblr_module()
{
}

function tumblr_content(App $a)
{
	if (! local_user()) {
		notice(DI::l10n()->t('Permission denied.') . EOL);
		return '';
	}

	if (isset(DI::args()->getArgv()[1])) {
		switch (DI::args()->getArgv()[1]) {
			case "connect":
				$o = tumblr_connect($a);
				break;

			case "callback":
				$o = tumblr_callback($a);
				break;

			default:
				$o = print_r(DI::args()->getArgv(), true);
				break;
		}
	} else {
		$o = tumblr_connect($a);
	}

	return $o;
}

function tumblr_addon_admin(App $a, &$o)
{
	$t = Renderer::getMarkupTemplate( "admin.tpl", "addon/tumblr/" );

	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		// name, label, value, help, [extra values]
		'$consumer_key' => ['consumer_key', DI::l10n()->t('Consumer Key'),  DI::config()->get('tumblr', 'consumer_key' ), ''],
		'$consumer_secret' => ['consumer_secret', DI::l10n()->t('Consumer Secret'),  DI::config()->get('tumblr', 'consumer_secret' ), ''],
	]);
}

function tumblr_addon_admin_post(App $a)
{
	$consumer_key    = trim($_POST['consumer_key'] ?? '');
	$consumer_secret = trim($_POST['consumer_secret'] ?? '');

	DI::config()->set('tumblr', 'consumer_key',$consumer_key);
	DI::config()->set('tumblr', 'consumer_secret',$consumer_secret);
}

function tumblr_connect(App $a)
{
	// Start a session.  This is necessary to hold on to  a few keys the callback script will also need
	session_start();

	// Include the TumblrOAuth library
	//require_once('addon/tumblr/tumblroauth/tumblroauth.php');

	// Define the needed keys
	$consumer_key = DI::config()->get('tumblr', 'consumer_key');
	$consumer_secret = DI::config()->get('tumblr', 'consumer_secret');

	// The callback URL is the script that gets called after the user authenticates with tumblr
	// In this example, it would be the included callback.php
	$callback_url = DI::baseUrl()->get()."/tumblr/callback";

	// Let's begin.  First we need a Request Token.  The request token is required to send the user
	// to Tumblr's login page.

	// Create a new instance of the TumblrOAuth library.  For this step, all we need to give the library is our
	// Consumer Key and Consumer Secret
	$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret);

	// Ask Tumblr for a Request Token.  Specify the Callback URL here too (although this should be optional)
	$request_token = $tum_oauth->getRequestToken($callback_url);

	// Store the request token and Request Token Secret as out callback.php script will need this
	$_SESSION['request_token'] = $token = $request_token['oauth_token'];
	$_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];

	// Check the HTTP Code.  It should be a 200 (OK), if it's anything else then something didn't work.
	switch ($tum_oauth->http_code) {
		case 200:
			// Ask Tumblr to give us a special address to their login page
			$url = $tum_oauth->getAuthorizeURL($token);

			// Redirect the user to the login URL given to us by Tumblr
			header('Location: ' . $url);

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

function tumblr_callback(App $a)
{
	// Start a session, load the library
	session_start();
	//require_once('addon/tumblr/tumblroauth/tumblroauth.php');

	// Define the needed keys
	$consumer_key = DI::config()->get('tumblr', 'consumer_key');
	$consumer_secret = DI::config()->get('tumblr', 'consumer_secret');

	// Once the user approves your app at Tumblr, they are sent back to this script.
	// This script is passed two parameters in the URL, oauth_token (our Request Token)
	// and oauth_verifier (Key that we need to get Access Token).
	// We'll also need out Request Token Secret, which we stored in a session.

	// Create instance of TumblrOAuth.
	// It'll need our Consumer Key and Secret as well as our Request Token and Secret
	$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $_SESSION['request_token'], $_SESSION['request_token_secret']);

	// Ok, let's get an Access Token. We'll need to pass along our oauth_verifier which was given to us in the URL.
	$access_token = $tum_oauth->getAccessToken($_REQUEST['oauth_verifier']);

	// We're done with the Request Token and Secret so let's remove those.
	unset($_SESSION['request_token']);
	unset($_SESSION['request_token_secret']);

	// Make sure nothing went wrong.
	if (200 == $tum_oauth->http_code) {
		// good to go
	} else {
		return 'Unable to authenticate';
	}

	// What's next?  Now that we have an Access Token and Secret, we can make an API call.
	DI::pConfig()->set(local_user(), "tumblr", "oauth_token", $access_token['oauth_token']);
	DI::pConfig()->set(local_user(), "tumblr", "oauth_token_secret", $access_token['oauth_token_secret']);

	$o = DI::l10n()->t("You are now authenticated to tumblr.");
	$o .= '<br /><a href="' . DI::baseUrl()->get() . '/settings/connectors">' . DI::l10n()->t("return to the connector page") . '</a>';

	return $o;
}

function tumblr_jot_nets(App $a, array &$jotnets_fields)
{
	if (! local_user()) {
		return;
	}

	if (DI::pConfig()->get(local_user(),'tumblr','post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'tumblr_enable',
				DI::l10n()->t('Post to Tumblr'),
				DI::pConfig()->get(local_user(),'tumblr','post_by_default')
			]
		];
	}
}

function tumblr_settings(App $a, array &$data)
{
	if (!local_user()) {
		return;
	}

	$enabled     = DI::pConfig()->get(local_user(), 'tumblr', 'post', false);
	$def_enabled = DI::pConfig()->get(local_user(), 'tumblr', 'post_by_default', false);

	$oauth_token        = DI::pConfig()->get(local_user(), 'tumblr', 'oauth_token');
	$oauth_token_secret = DI::pConfig()->get(local_user(), 'tumblr', 'oauth_token_secret');

	if ($oauth_token && $oauth_token_secret) {
		$page            = DI::pConfig()->get(local_user(), 'tumblr', 'page');
		$consumer_key    = DI::config()->get('tumblr', 'consumer_key');
		$consumer_secret = DI::config()->get('tumblr', 'consumer_secret');

		$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
		$userinfo  = $tum_oauth->get('user/info');

		$blogs = array_map(function ($blog) {
			return substr(str_replace(["http://", "https://"], ["", ""], $blog->url), 0, -1);
		}, $userinfo->response->user->blogs);

		$page_select = ['tumblr-page', DI::l10n()->t('Post to page:'), $page, '', $blogs];
	}

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/tumblr/');
	$html = Renderer::replaceMacros($t, [
		'$l10n' => [
			'connect'   => DI::l10n()->t('(Re-)Authenticate your tumblr page'),
			'noconnect' => DI::l10n()->t('You are not authenticated to tumblr'),
		],

		'$authenticate_url' => DI::baseUrl()->get() . '/tumblr/connect',

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

function tumblr_settings_post(App $a, array &$b)
{
	if (!empty($_POST['tumblr-submit'])) {
		DI::pConfig()->set(local_user(), 'tumblr', 'post',            intval($_POST['tumblr']));
		DI::pConfig()->set(local_user(), 'tumblr', 'page',            $_POST['tumblr_page']);
		DI::pConfig()->set(local_user(), 'tumblr', 'post_by_default', intval($_POST['tumblr_bydefault']));
	}
}

function tumblr_hook_fork(App $a, array &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if ($post['deleted'] || $post['private'] || ($post['created'] !== $post['edited']) ||
		!strstr($post['postopts'], 'tumblr') || ($post['parent'] != $post['id'])) {
		$b['execute'] = false;
		return;
	}
}

function tumblr_post_local(App $a, array &$b)
{
	// This can probably be changed to allow editing by pointing to a different API endpoint

	if ($b['edit']) {
		return;
	}

	if (!local_user() || (local_user() != $b['uid'])) {
		return;
	}

	if ($b['private'] || $b['parent']) {
		return;
	}

	$tmbl_post   = intval(DI::pConfig()->get(local_user(), 'tumblr', 'post'));

	$tmbl_enable = (($tmbl_post && !empty($_REQUEST['tumblr_enable'])) ? intval($_REQUEST['tumblr_enable']) : 0);

	if ($b['api_source'] && intval(DI::pConfig()->get(local_user(), 'tumblr', 'post_by_default'))) {
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




function tumblr_send(App $a, array &$b) {

	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if (! strstr($b['postopts'],'tumblr')) {
		return;
	}

	if ($b['parent'] != $b['id']) {
		return;
	}

	// Dont't post if the post doesn't belong to us.
	// This is a check for forum postings
	$self = DBA::selectFirst('contact', ['id'], ['uid' => $b['uid'], 'self' => true]);
	if ($b['contact-id'] != $self['id']) {
		return;
	}

	$b['body'] = Post\Media::addAttachmentsToBody($b['uri-id'], $b['body']);

	$oauth_token = DI::pConfig()->get($b['uid'], "tumblr", "oauth_token");
	$oauth_token_secret = DI::pConfig()->get($b['uid'], "tumblr", "oauth_token_secret");
	$page = DI::pConfig()->get($b['uid'], "tumblr", "page");
	$tmbl_blog = 'blog/' . $page . '/post';

	if ($oauth_token && $oauth_token_secret && $tmbl_blog) {
		$tags = Tag::getByURIId($b['uri-id']);

		$tag_arr = [];

		foreach($tags as $tag) {
			$tag_arr[] = $tag['name'];
		}

		if (count($tag_arr)) {
			$tags = implode(',', $tag_arr);
		}

		$title = trim($b['title']);

		$siteinfo = BBCode::getAttachedData($b["body"]);

		$params = [
			'state'  => 'published',
			'tags'   => $tags,
			'tweet'  => 'off',
			'format' => 'html',
		];

		if (!isset($siteinfo["type"])) {
			$siteinfo["type"] = "";
		}

		if (($title == "") && isset($siteinfo["title"])) {
			$title = $siteinfo["title"];
		}

		if (isset($siteinfo["text"])) {
			$body = $siteinfo["text"];
		} else {
			$body = BBCode::removeShareInformation($b["body"]);
		}

		switch ($siteinfo["type"]) {
			case "photo":
				$params['type']    = "photo";
				$params['caption'] = BBCode::convertForUriId($b['uri-id'], $body, BBCode::CONNECTORS);;

				if (isset($siteinfo["url"])) {
					$params['link'] = $siteinfo["url"];
				}

				$params['source'] = $siteinfo["image"];
				break;

			case "link":
				$params['type']        = "link";
				$params['title']       = $title;
				$params['url']         = $siteinfo["url"];
				$params['description'] = BBCode::convertForUriId($b['uri-id'], $body, BBCode::CONNECTORS);
				break;

			case "audio":
				$params['type']         = "audio";
				$params['external_url'] = $siteinfo["url"];
				$params['caption']      = BBCode::convertForUriId($b['uri-id'], $body, BBCode::CONNECTORS);
				break;

			case "video":
				$params['type']    = "video";
				$params['embed']   = $siteinfo["url"];
				$params['caption'] = BBCode::convertForUriId($b['uri-id'], $body, BBCode::CONNECTORS);
				break;

			default:
				$params['type']  = "text";
				$params['title'] = $title;
				$params['body']  = BBCode::convertForUriId($b['uri-id'], $b['body'], BBCode::CONNECTORS);
				break;
		}

		if (isset($params['caption']) && (trim($title) != "")) {
			$params['caption'] = '<h1>'.$title."</h1>".
						"<p>".$params['caption']."</p>";
		}

		if (empty($params['caption']) && !empty($siteinfo["description"])) {
			$params['caption'] = BBCode::convertForUriId($b['uri-id'], "[quote]" . $siteinfo["description"] . "[/quote]", BBCode::CONNECTORS);
		}

		$consumer_key = DI::config()->get('tumblr','consumer_key');
		$consumer_secret = DI::config()->get('tumblr','consumer_secret');

		$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);

		// Make an API call with the TumblrOAuth instance.
		$x = $tum_oauth->post($tmbl_blog,$params);
		$ret_code = $tum_oauth->http_code;

		//print_r($params);
		if ($ret_code == 201) {
			Logger::notice('tumblr_send: success');
		} elseif ($ret_code == 403) {
			Logger::notice('tumblr_send: authentication failure');
		} else {
			Logger::notice('tumblr_send: general error', ['error' => $x]);
		}
	}
}

