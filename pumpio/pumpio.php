<?php
/**
 * Name: pump.io Post Connector
 * Description: Bidirectional (posting, relaying and reading) connector for pump.io.
 * Version: 0.2
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 */

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\HTML;
use Friendica\Core\Addon;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Protocol;
use Friendica\Core\Renderer;
use Friendica\Core\Worker;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\Circle;
use Friendica\Model\Item;
use Friendica\Model\Post;
use Friendica\Model\User;
use Friendica\Network\HTTPClient\Client\HttpClientAccept;
use Friendica\Network\HTTPClient\Client\HttpClientOptions;
use Friendica\Protocol\Activity;
use Friendica\Protocol\ActivityNamespace;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Strings;
use Friendica\Util\XML;

require 'addon/pumpio/oauth/http.php';
require 'addon/pumpio/oauth/oauth_client.php';

define('PUMPIO_DEFAULT_POLL_INTERVAL', 5); // given in minutes

function pumpio_install()
{
	Hook::register('load_config',          'addon/pumpio/pumpio.php', 'pumpio_load_config');
	Hook::register('hook_fork',            'addon/pumpio/pumpio.php', 'hook_fork');
	Hook::register('post_local',           'addon/pumpio/pumpio.php', 'pumpio_post_local');
	Hook::register('notifier_normal',      'addon/pumpio/pumpio.php', 'pumpio_send');
	Hook::register('jot_networks',         'addon/pumpio/pumpio.php', 'pumpio_jot_nets');
	Hook::register('connector_settings',      'addon/pumpio/pumpio.php', 'pumpio_settings');
	Hook::register('connector_settings_post', 'addon/pumpio/pumpio.php', 'pumpio_settings_post');
	Hook::register('cron', 'addon/pumpio/pumpio.php', 'pumpio_cron');
	Hook::register('check_item_notification', 'addon/pumpio/pumpio.php', 'pumpio_check_item_notification');
}

/**
 * This is a statement rather than an actual function definition. The simple
 * existence of this method is checked to figure out if the addon offers a
 * module.
 */
function pumpio_module() {}

function pumpio_content()
{
	if (!DI::userSession()->getLocalUserId()) {
		DI::sysmsg()->addNotice(DI::l10n()->t('Permission denied.'));
		return '';
	}

	if (isset(DI::args()->getArgv()[1])) {
		switch (DI::args()->getArgv()[1]) {
			case 'connect':
				$o = pumpio_connect();
				break;

			default:
				$o = print_r(DI::args()->getArgv(), true);
				break;
		}
	} else {
		$o = pumpio_connect();
	}
	return $o;
}

function pumpio_check_item_notification(array &$notification_data)
{
	$hostname = DI::pConfig()->get($notification_data['uid'], 'pumpio', 'host');
	$username = DI::pConfig()->get($notification_data['uid'], 'pumpio', 'user');

	$notification_data['profiles'][] = 'https://' . $hostname . '/' . $username;
}

function pumpio_registerclient($host)
{
	$url = 'https://' . $host . '/api/client/register';

	$params = [];

	$application_name  = DI::config()->get('pumpio', 'application_name');

	if ($application_name == '') {
		$application_name = DI::baseUrl()->getHost();
	}

	$firstAdmin = User::getFirstAdmin(['email']);

	$params['type'] = 'client_associate';
	$params['contacts'] = $firstAdmin['email'];
	$params['application_type'] = 'native';
	$params['application_name'] = $application_name;
	$params['logo_url'] = DI::baseUrl() . '/images/friendica-256.png';
	$params['redirect_uris'] = DI::baseUrl() . '/pumpio/connect';

	Logger::info('pumpio_registerclient: ' . $url . ' parameters', $params);

	// @TODO Rewrite this to our own HTTP client
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Friendica');

	$s = curl_exec($ch);
	$curl_info = curl_getinfo($ch);

	if ($curl_info['http_code'] == '200') {
		$values = json_decode($s);
		Logger::info('pumpio_registerclient: success ', (array)$values);
		return $values;
	}
	Logger::info('pumpio_registerclient: failed: ', $curl_info);
	return false;

}

function pumpio_connect()
{
	// Define the needed keys
	$consumer_key    = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'consumer_key');
	$consumer_secret = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'consumer_secret');
	$hostname        = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'host');

	if ((($consumer_key == '') || ($consumer_secret == '')) && ($hostname != '')) {
		Logger::notice('pumpio_connect: register client');
		$clientdata = pumpio_registerclient($hostname);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'consumer_key', $clientdata->client_id);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'consumer_secret', $clientdata->client_secret);

		$consumer_key    = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'consumer_key');
		$consumer_secret = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'consumer_secret');

		Logger::info('pumpio_connect: ckey: ' . $consumer_key . ' csecrect: ' . $consumer_secret);
	}

	if (($consumer_key == '') || ($consumer_secret == '')) {
		Logger::notice('pumpio_connect: '.sprintf('Unable to register the client at the pump.io server "%s".', $hostname));

		return DI::l10n()->t("Unable to register the client at the pump.io server '%s'.", $hostname);
	}

	// The callback URL is the script that gets called after the user authenticates with pumpio
	$callback_url = DI::baseUrl() . '/pumpio/connect';

	// Let's begin.  First we need a Request Token.  The request token is required to send the user
	// to pumpio's login page.

	// Create a new instance of the oauth_client_class library.  For this step, all we need to give the library is our
	// Consumer Key and Consumer Secret
	$client = new oauth_client_class;
	$client->debug = 0;
	$client->server = '';
	$client->oauth_version = '1.0a';
	$client->request_token_url = 'https://'.$hostname.'/oauth/request_token';
	$client->dialog_url = 'https://'.$hostname.'/oauth/authorize';
	$client->access_token_url = 'https://'.$hostname.'/oauth/access_token';
	$client->url_parameters = false;
	$client->authorization_header = true;
	$client->redirect_uri = $callback_url;
	$client->client_id = $consumer_key;
	$client->client_secret = $consumer_secret;

	if (($success = $client->Initialize())) {
		if (($success = $client->Process())) {
			if (strlen($client->access_token)) {
				Logger::info('pumpio_connect: otoken: ' . $client->access_token . ', osecrect: ' . $client->access_token_secret);
				DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'oauth_token', $client->access_token);
				DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'oauth_token_secret', $client->access_token_secret);
			}
		}
		$success = $client->Finalize($success);
	}
	if ($client->exit)  {
		$o = 'Could not connect to pumpio. Refresh the page or try again later.';
	}

	if ($success) {
		Logger::notice('pumpio_connect: authenticated');
		$o = DI::l10n()->t('You are now authenticated to pumpio.');
		$o .= '<br /><a href="' . DI::baseUrl() . '/settings/connectors">' . DI::l10n()->t('return to the connector page') . '</a>';
	} else {
		Logger::notice('pumpio_connect: could not connect');
		$o = 'Could not connect to pumpio. Refresh the page or try again later.';
	}

	return $o;
}

function pumpio_jot_nets(array &$jotnets_fields)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'pumpio_enable',
				DI::l10n()->t('Post to pumpio'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'post_by_default')
			]
		];
	}
}

function pumpio_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$pumpio_host        = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'host');
	$pumpio_user        = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'user');
	$oauth_token        = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'oauth_token');
	$oauth_token_secret = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'oauth_token_secret');

	$import_enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'import', false);
	$enabled        = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'post', false);
	$def_enabled    = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'post_by_default', false);
	$public_enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'public', false);
	$mirror_enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'mirror', false);

	$submit = ['pumpio-submit' => DI::l10n()->t('Save Settings')];
	if ($oauth_token && $oauth_token_secret) {
		$submit['pumpio-delete'] = DI::l10n()->t('Delete this preset');
	}

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/pumpio/');
	$html = Renderer::replaceMacros($t, [
		'$l10n'               => [
			'authenticate' => DI::l10n()->t('Authenticate your pump.io connection'),
		],
		'$pumpio_host'        => $pumpio_host,
		'$pumpio_user'        => $pumpio_user,
		'$oauth_token'        => $oauth_token,
		'$oauth_token_secret' => $oauth_token_secret,
		'$authenticate_url'   => DI::baseUrl() . '/pumpio/connect',
		'$servername'         => ['pumpio_host', DI::l10n()->t('Pump.io servername (without "http://" or "https://" )'), $pumpio_host],
		'$username'           => ['pumpio_user', DI::l10n()->t('Pump.io username (without the servername)'), $pumpio_user],
		'$import'             => ['pumpio_import', DI::l10n()->t('Import the remote timeline'), $import_enabled],
		'$enabled'            => ['pumpio', DI::l10n()->t('Enable Pump.io Post Addon'), $enabled],
		'$bydefault'          => ['pumpio_bydefault', DI::l10n()->t('Post to Pump.io by default'), $def_enabled],
		'$public'             => ['pumpio_public', DI::l10n()->t('Should posts be public?'), $public_enabled],
		'$mirror'             => ['pumpio_mirror', DI::l10n()->t('Mirror all public posts'), $mirror_enabled],
	]);

	$data = [
		'connector' => 'pumpio',
		'title'     => DI::l10n()->t('Pump.io Import/Export/Mirror'),
		'image'     => 'images/pumpio.png',
		'enabled'   => $enabled,
		'html'      => $html,
		'submit'    => $submit,
	];
}

function pumpio_settings_post(array &$b)
{
	if (!empty($_POST['pumpio_delete'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'consumer_key'      , '');
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'consumer_secret'   , '');
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'oauth_token'       , '');
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'oauth_token_secret', '');
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'post'              , false);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'import'            , false);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'host'              , '');
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'user'              , '');
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'public'            , false);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'mirror'            , false);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'post_by_default'   , false);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'lastdate'          , 0);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'last_id'           , '');
	} elseif (!empty($_POST['pumpio-submit'])) {
		// filtering the username if it is filled wrong
		$user = $_POST['pumpio_user'];
		if (strstr($user, '@')) {
			$pos = strpos($user, '@');

			if ($pos > 0) {
				$user = substr($user, 0, $pos);
			}
		}

		// Filtering the hostname if someone is entering it with "http"
		$host = $_POST['pumpio_host'];
		$host = trim($host);
		$host = str_replace(['https://', 'http://'], ['', ''], $host);

		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'post'           , $_POST['pumpio'] ?? false);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'import'         , $_POST['pumpio_import'] ?? false);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'host'           , $host);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'user'           , $user);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'public'         , $_POST['pumpio_public'] ?? false);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'mirror'         , $_POST['pumpio_mirror'] ?? false);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'pumpio', 'post_by_default', $_POST['pumpio_bydefault'] ?? false);

		if (!empty($_POST['pumpio_mirror'])) {
			DI::pConfig()->delete(DI::userSession()->getLocalUserId(), 'pumpio', 'lastdate');
		}
	}
}

function pumpio_load_config(ConfigFileManager $loader)
{
	DI::app()->getConfigCache()->load($loader->loadAddonConfig('pumpio'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function pumpio_hook_fork(array &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	// Deleting and editing is not supported by the addon (deleting could, but isn't by now)
	if ($post['deleted'] || ($post['created'] !== $post['edited'])) {
		$b['execute'] = false;
		return;
	}

	// if post comes from pump.io don't send it back
	if ($post['app'] == 'pump.io') {
		$b['execute'] = false;
		return;
	}

	if (DI::pConfig()->get($post['uid'], 'pumpio', 'import')) {
		// Don't fork if it isn't a reply to a pump.io post
		if (($post['parent'] != $post['id']) && !Post::exists(['id' => $post['parent'], 'network' => Protocol::PUMPIO])) {
			Logger::notice('No pump.io parent found for item ' . $post['id']);
			$b['execute'] = false;
			return;
		}
	} else {
		// Comments are never exported when we don't import the pumpio timeline
		if (!strstr($post['postopts'], 'pumpio') || ($post['parent'] != $post['id']) || $post['private']) {
			$b['execute'] = false;
			return;
		}
	}
}

function pumpio_post_local(array &$b)
{
	if (!DI::userSession()->getLocalUserId() || (DI::userSession()->getLocalUserId() != $b['uid'])) {
		return;
	}

	$pumpio_post   = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'post'));

	$pumpio_enable = (($pumpio_post && !empty($_REQUEST['pumpio_enable'])) ? intval($_REQUEST['pumpio_enable']) : 0);

	if ($b['api_source'] && intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'pumpio', 'post_by_default'))) {
		$pumpio_enable = 1;
	}

	if (!$pumpio_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'pumpio';
}

function pumpio_send(array &$b)
{
	if (!DI::pConfig()->get($b['uid'], 'pumpio', 'import') && ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))) {
		return;
	}

	Logger::debug('pumpio_send: parameter ', $b);

	$b['body'] = Post\Media::addAttachmentsToBody($b['uri-id'], DI::contentItem()->addSharedPost($b));

	if ($b['parent'] != $b['id']) {
		// Looking if its a reply to a pumpio post
		$condition = ['id' => $b['parent'], 'network' => Protocol::PUMPIO];
		$orig_post = Post::selectFirst([], $condition);

		if (!DBA::isResult($orig_post)) {
			Logger::notice('pumpio_send: no pumpio post ' . $b['parent']);
			return;
		} else {
			$iscomment = true;
		}
	} else {
		$iscomment = false;

		$receiver = pumpio_getreceiver($b);

		Logger::notice('pumpio_send: receiver ', $receiver);

		if (!count($receiver) && ($b['private'] || !strstr($b['postopts'], 'pumpio'))) {
			return;
		}

		// Dont't post if the post doesn't belong to us.
		// This is a check for group postings
		$self = User::getOwnerDataById($b['uid']);
		if ($b['contact-id'] != $self['id']) {
			return;
		}
	}

	if ($b['verb'] == Activity::LIKE) {
		if ($b['deleted']) {
			pumpio_action($b['uid'], $b['thr-parent'], 'unlike');
		} else {
			pumpio_action($b['uid'], $b['thr-parent'], 'like');
		}
		return;
	}

	if ($b['verb'] == Activity::DISLIKE) {
		return;
	}

	if (($b['verb'] == Activity::POST) && ($b['created'] !== $b['edited']) && !$b['deleted']) {
		pumpio_action($b['uid'], $b['uri'], 'update', $b['body']);
	}

	if (($b['verb'] == Activity::POST) && $b['deleted']) {
		pumpio_action($b['uid'], $b['uri'], 'delete');
	}

	if ($b['deleted'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	// if post comes from pump.io don't send it back
	if ($b['app'] == 'pump.io') {
		return;
	}

	// To-Do;
	// Support for native shares
	// http://<hostname>/api/<type>/shares?id=<the-object-id>

	$oauth_token        = DI::pConfig()->get($b['uid'], 'pumpio', 'oauth_token');
	$oauth_token_secret = DI::pConfig()->get($b['uid'], 'pumpio', 'oauth_token_secret');
	$consumer_key       = DI::pConfig()->get($b['uid'], 'pumpio', 'consumer_key');
	$consumer_secret    = DI::pConfig()->get($b['uid'], 'pumpio', 'consumer_secret');

	$host   = DI::pConfig()->get($b['uid'], 'pumpio', 'host');
	$user   = DI::pConfig()->get($b['uid'], 'pumpio', 'user');
	$public = DI::pConfig()->get($b['uid'], 'pumpio', 'public');

	if ($oauth_token && $oauth_token_secret) {
		$title = trim($b['title']);

		$content = BBCode::convertForUriId($b['uri-id'], $b['body'], BBCode::CONNECTORS);

		$params = [];

		$params['verb'] = 'post';

		if (!$iscomment) {
			$params['object'] = [
				'objectType' => 'note',
				'content' => $content];

			if (!empty($title)) {
				$params['object']['displayName'] = $title;
			}

			if (!empty($receiver['to'])) {
				$params['to'] = $receiver['to'];
			}

			if (!empty($receiver['bto'])) {
				$params['bto'] = $receiver['bto'];
			}

			if (!empty($receiver['cc'])) {
				$params['cc'] = $receiver['cc'];
			}

			if (!empty($receiver['bcc'])) {
				$params['bcc'] = $receiver['bcc'];
			}
		 } else {
			$inReplyTo = [
				'id' => $orig_post['uri'],
				'objectType' => 'note',
			];

			if (($orig_post['object-type'] != '') && (strstr($orig_post['object-type'], ActivityNamespace::ACTIVITY_SCHEMA))) {
				$inReplyTo['objectType'] = str_replace(ActivityNamespace::ACTIVITY_SCHEMA, '', $orig_post['object-type']);
			}

			$params['object'] = [
				'objectType' => 'comment',
				'content' => $content,
				'inReplyTo' => $inReplyTo];

			if ($title != '') {
				$params['object']['displayName'] = $title;
			}
		}

		$client = new oauth_client_class;
		$client->oauth_version = '1.0a';
		$client->url_parameters = false;
		$client->authorization_header = true;
		$client->access_token = $oauth_token;
		$client->access_token_secret = $oauth_token_secret;
		$client->client_id = $consumer_key;
		$client->client_secret = $consumer_secret;

		$username = $user . '@' . $host;
		$url = 'https://' . $host . '/api/user/' . $user . '/feed';

		if (pumpio_reachable($url)) {
			$success = $client->CallAPI($url, 'POST', $params, ['FailOnAccessError' => true, 'RequestContentType' => 'application/json'], $user);
		} else {
			$success = false;
		}

		if ($success) {
			if ($user->generator->displayName) {
				DI::pConfig()->set($b['uid'], 'pumpio', 'application_name', $user->generator->displayName);
			}

			$post_id = $user->object->id;
			Logger::notice('pumpio_send ' . $username . ': success ' . $post_id);
			if ($post_id && $iscomment) {
				Logger::notice('pumpio_send ' . $username . ': Update extid ' . $post_id . ' for post id ' . $b['id']);
				Item::update(['extid' => $post_id], ['id' => $b['id']]);
			}
		} else {
			Logger::notice('pumpio_send '.$username.': '.$url.' general error: ' . print_r($user, true));
			Worker::defer();
		}
	}
}

function pumpio_action(int $uid, string $uri, string $action, string $content = '')
{
	// Don't do likes and other stuff if you don't import the timeline
	if (!DI::pConfig()->get($uid, 'pumpio', 'import')) {
		return;
	}

	$ckey     = DI::pConfig()->get($uid, 'pumpio', 'consumer_key');
	$csecret  = DI::pConfig()->get($uid, 'pumpio', 'consumer_secret');
	$otoken   = DI::pConfig()->get($uid, 'pumpio', 'oauth_token');
	$osecret  = DI::pConfig()->get($uid, 'pumpio', 'oauth_token_secret');
	$hostname = DI::pConfig()->get($uid, 'pumpio', 'host');
	$username = DI::pConfig()->get($uid, 'pumpio', 'user');

	$orig_post = Post::selectFirst([], ['uri' => $uri, 'uid' => $uid]);

	if (!DBA::isResult($orig_post)) {
		return;
	}

	if ($orig_post['extid'] && !strstr($orig_post['extid'], '/proxy/')) {
		$uri = $orig_post['extid'];
	} else {
		$uri = $orig_post['uri'];
	}

	if (($orig_post['object-type'] != '') && (strstr($orig_post['object-type'], ActivityNamespace::ACTIVITY_SCHEMA))) {
		$objectType = str_replace(ActivityNamespace::ACTIVITY_SCHEMA, '', $orig_post['object-type']);
	} elseif (strstr($uri, '/api/comment/')) {
		$objectType = 'comment';
	} elseif (strstr($uri, '/api/note/')) {
		$objectType = 'note';
	} elseif (strstr($uri, '/api/image/')) {
		$objectType = 'image';
	}

	$params['verb'] = $action;
	$params['object'] = [
		'id' => $uri,
		'objectType' => $objectType,
		'content' => $content,
	];

	$client = new oauth_client_class;
	$client->oauth_version = '1.0a';
	$client->authorization_header = true;
	$client->url_parameters = false;

	$client->client_id = $ckey;
	$client->client_secret = $csecret;
	$client->access_token = $otoken;
	$client->access_token_secret = $osecret;

	$url = 'https://'.$hostname.'/api/user/'.$username.'/feed';

	if (pumpio_reachable($url)) {
		$success = $client->CallAPI($url, 'POST', $params, ['FailOnAccessError' => true, 'RequestContentType' => 'application/json'], $user);
	} else {
		$success = false;
	}

	if ($success) {
		Logger::notice('pumpio_action '.$username.' '.$action.': success '.$uri);
	} else {
		Logger::notice('pumpio_action '.$username.' '.$action.': general error: '.$uri);
		Worker::defer();
	}
}

function pumpio_sync()
{
	if (!Addon::isEnabled('pumpio')) {
		return;
	}

	$last = DI::keyValue()->get('pumpio_last_poll');

	$poll_interval = intval(DI::config()->get('pumpio', 'poll_interval', PUMPIO_DEFAULT_POLL_INTERVAL));

	if ($last) {
		$next = $last + ($poll_interval * 60);
		if ($next > time()) {
			Logger::notice('pumpio: poll intervall not reached');
			return;
		}
	}
	Logger::notice('pumpio: cron_start');

	$pconfigs = DBA::selectToArray('pconfig', ['uid'], ['cat' => 'pumpio', 'k' => 'mirror', 'v' => '1']);
	foreach ($pconfigs as $rr) {
		Logger::notice('pumpio: mirroring user '.$rr['uid']);
		pumpio_fetchtimeline($rr['uid']);
	}

	$abandon_days = intval(DI::config()->get('system', 'account_abandon_days'));
	if ($abandon_days < 1) {
		$abandon_days = 0;
	}

	$abandon_limit = date(DateTimeFormat::MYSQL, time() - $abandon_days * 86400);

	$pconfigs = DBA::selectToArray('pconfig', ['uid'], ['cat' => 'pumpio', 'k' => 'import', 'v' => '1']);
	foreach ($pconfigs as $rr) {
		if ($abandon_days != 0) {
			if (DBA::exists('user', ["uid = ? AND `login_date` >= ?", $rr['uid'], $abandon_limit])) {
				Logger::notice('abandoned account: timeline from user '.$rr['uid'].' will not be imported');
				continue;
			}
		}

		Logger::notice('pumpio: importing timeline from user '.$rr['uid']);
		pumpio_fetchinbox($rr['uid']);

		// check for new contacts once a day
		$last_contact_check = DI::pConfig()->get($rr['uid'], 'pumpio', 'contact_check');
		if ($last_contact_check) {
			$next_contact_check = $last_contact_check + 86400;
		} else {
			$next_contact_check = 0;
		}

		if ($next_contact_check <= time()) {
			pumpio_getallusers($rr['uid']);
			DI::pConfig()->set($rr['uid'], 'pumpio', 'contact_check', time());
		}
	}

	Logger::notice('pumpio: cron_end');

	DI::keyValue()->set('pumpio_last_poll', time());
}

function pumpio_cron($b)
{
	Worker::add(Worker::PRIORITY_MEDIUM, 'addon/pumpio/pumpio_sync.php');
}

function pumpio_fetchtimeline(int $uid)
{
	$ckey     = DI::pConfig()->get($uid, 'pumpio', 'consumer_key');
	$csecret  = DI::pConfig()->get($uid, 'pumpio', 'consumer_secret');
	$otoken   = DI::pConfig()->get($uid, 'pumpio', 'oauth_token');
	$osecret  = DI::pConfig()->get($uid, 'pumpio', 'oauth_token_secret');
	$lastdate = DI::pConfig()->get($uid, 'pumpio', 'lastdate');
	$hostname = DI::pConfig()->get($uid, 'pumpio', 'host');
	$username = DI::pConfig()->get($uid, 'pumpio', 'user');

	//  get the application name for the pump.io app
	//  1st try personal config, then system config and fallback to the
	//  hostname of the node if neither one is set.
	$application_name  = DI::pConfig()->get($uid, 'pumpio', 'application_name');
	if ($application_name == '') {
		$application_name  = DI::config()->get('pumpio', 'application_name');
	}
	if ($application_name == '') {
		$application_name = DI::baseUrl()->getHost();
	}

	$first_time = ($lastdate == '');

	$client = new oauth_client_class;
	$client->oauth_version = '1.0a';
	$client->authorization_header = true;
	$client->url_parameters = false;

	$client->client_id = $ckey;
	$client->client_secret = $csecret;
	$client->access_token = $otoken;
	$client->access_token_secret = $osecret;

	$url = 'https://'.$hostname.'/api/user/'.$username.'/feed/major';

	Logger::notice('pumpio: fetching for user ' . $uid . ' ' . $url . ' C:' . $client->client_id . ' CS:' . $client->client_secret . ' T:' . $client->access_token . ' TS:' . $client->access_token_secret);

	$useraddr = $username.'@'.$hostname;

	if (pumpio_reachable($url)) {
		$success = $client->CallAPI($url, 'GET', [], ['FailOnAccessError' => true], $user);
	} else {
		$success = false;
		$user = [];
	}

	if (!$success) {
		Logger::notice('pumpio: error fetching posts for user ' . $uid . ' ' . $useraddr . ' ', $user);
		return;
	}

	$posts = array_reverse($user->items);

	$initiallastdate = $lastdate;
	$lastdate = '';

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->published <= $initiallastdate) {
				continue;
			}

			if ($lastdate < $post->published) {
				$lastdate = $post->published;
			}

			if ($first_time) {
				continue;
			}

			$receiptians = [];
			if (@is_array($post->cc)) {
				$receiptians = array_merge($receiptians, $post->cc);
			}

			if (@is_array($post->to)) {
				$receiptians = array_merge($receiptians, $post->to);
			}

			$public = false;
			foreach ($receiptians as $receiver) {
				if (is_string($receiver->objectType) && ($receiver->id == 'http://activityschema.org/collection/public')) {
					$public = true;
				}
			}

			if ($public && !stristr($post->generator->displayName, $application_name)) {
				$postarray['uid'] = $uid;
				$postarray['app'] = 'pump.io';

				if ($post->object->displayName != '') {
					$postarray['title'] = HTML::toBBCode($post->object->displayName);
				} else {
					$postarray['title'] = '';
				}

				$postarray['body'] = HTML::toBBCode($post->object->content);

				// To-Do: Picture has to be cached and stored locally
				if ($post->object->fullImage->url != '') {
					if ($post->object->fullImage->pump_io->proxyURL != '') {
						$postarray['body'] = '[url=' . $post->object->fullImage->pump_io->proxyURL . '][img]' . $post->object->image->pump_io->proxyURL . "[/img][/url]\n" . $postarray['body'];
					} else {
						$postarray['body'] = '[url=' . $post->object->fullImage->url . '][img]' . $post->object->image->url . "[/img][/url]\n" . $postarray['body'];
					}
				}

				Logger::notice('pumpio: posting for user ' . $uid);

				Item::insert($postarray, true);

				Logger::notice('pumpio: posting done - user ' . $uid);
			}
		}
	}

	if ($lastdate != 0) {
		DI::pConfig()->set($uid, 'pumpio', 'lastdate', $lastdate);
	}
}

function pumpio_dounlike(int $uid, array $self, $post, string $own_id)
{
	// Searching for the unliked post
	// Two queries for speed issues
	$orig_post = Post::selectFirst([], ['uri' => $post->object->id, 'uid' => $uid]);
	if (!DBA::isResult($orig_post)) {
		$orig_post = Post::selectFirst([], ['extid' => $post->object->id, 'uid' => $uid]);
		if (!DBA::isResult($orig_post)) {
			return;
		}
	}

	$contactid = 0;

	if (Strings::compareLink($post->actor->url, $own_id)) {
		$contactid = $self['id'];
	} else {
		$contact = Contact::selectFirst([], ['nurl' => Strings::normaliseLink($post->actor->url), 'uid' => $uid, 'blocked' => false, 'readonly' => false]);
		if (DBA::isResult($contact)) {
			$contactid = $contact['id'];
		}

		if ($contactid == 0) {
			$contactid = $orig_post['contact-id'];
		}
	}

	Item::markForDeletion(['verb' => Activity::LIKE, 'uid' => $uid, 'contact-id' => $contactid, 'thr-parent' => $orig_post['uri']]);

	if (DBA::isResult($contact)) {
		Logger::notice('pumpio_dounlike: unliked existing like. User ' . $own_id . ' ' . $uid . ' Contact: ' . $contactid . ' URI ' . $orig_post['uri']);
	} else {
		Logger::notice('pumpio_dounlike: not found. User ' . $own_id . ' ' . $uid . ' Contact: ' . $contactid . ' Url ' . $orig_post['uri']);
	}
}

function pumpio_dolike(int $uid, array $self, $post, string $own_id, $threadcompletion = true)
{
	if (empty($post->object->id)) {
		Logger::info('Got empty like: '.print_r($post, true));
		return;
	}

	// Searching for the liked post
	// Two queries for speed issues
	$orig_post = Post::selectFirst([], ['uri' => $post->object->id, 'uid' => $uid]);
	if (!DBA::isResult($orig_post)) {
		$orig_post = Post::selectFirst([], ['extid' => $post->object->id, 'uid' => $uid]);
		if (!DBA::isResult($orig_post)) {
			return;
		}
	}

	// thread completion
	if ($threadcompletion) {
		pumpio_fetchallcomments($uid, $post->object->id);
	}

	$contactid = 0;

	if (Strings::compareLink($post->actor->url, $own_id)) {
		$contactid = $self['id'];
		$post->actor->displayName = $self['name'];
		$post->actor->url = $self['url'];
		$post->actor->image->url = $self['photo'];
	} else {
		$contact = Contact::selectFirst([], ['nurl' => Strings::normaliseLink($post->actor->url), 'uid' => $uid, 'blocked' => false, 'readonly' => false]);
		if (DBA::isResult($contact)) {
			$contactid = $contact['id'];
		}

		if ($contactid == 0) {
			$contactid = $orig_post['contact-id'];
		}
	}

	$condition = [
		'verb' => Activity::LIKE,
		'uid' => $uid,
		'contact-id' => $contactid,
		'thr-parent' => $orig_post['uri'],
	];

	if (Post::exists($condition)) {
		Logger::notice('pumpio_dolike: found existing like. User ' . $own_id . ' ' . $uid . ' Contact: ' . $contactid . ' URI ' . $orig_post['uri']);
		return;
	}

	$likedata = [];
	$likedata['parent'] = $orig_post['id'];
	$likedata['verb'] = Activity::LIKE;
	$likedata['gravity'] = Item::GRAVITY_ACTIVITY;
	$likedata['uid'] = $uid;
	$likedata['wall'] = 0;
	$likedata['network'] = Protocol::PUMPIO;
	$likedata['uri'] = Item::newURI();
	$likedata['thr-parent'] = $orig_post['uri'];
	$likedata['contact-id'] = $contactid;
	$likedata['app'] = $post->generator->displayName;
	$likedata['author-name'] = $post->actor->displayName;
	$likedata['author-link'] = $post->actor->url;
	if (!empty($post->actor->image)) {
		$likedata['author-avatar'] = $post->actor->image->url;
	}

	$author  = '[url=' . $likedata['author-link'] . ']' . $likedata['author-name'] . '[/url]';
	$objauthor =  '[url=' . $orig_post['author-link'] . ']' . $orig_post['author-name'] . '[/url]';
	$post_type = DI::l10n()->t('status');
	$plink = '[url=' . $orig_post['plink'] . ']' . $post_type . '[/url]';
	$likedata['object-type'] = Activity\ObjectType::NOTE;

	$likedata['body'] = DI::l10n()->t('%1$s likes %2$s\'s %3$s', $author, $objauthor, $plink);

	$likedata['object'] = '<object><type>' . Activity\ObjectType::NOTE . '</type><local>1</local>' .
		'<id>' . $orig_post['uri'] . '</id><link>' . XML::escape('<link rel="alternate" type="text/html" href="' . XML::escape($orig_post['plink']) . '" />') . '</link><title>' . $orig_post['title'] . '</title><content>' . $orig_post['body'] . '</content></object>';

	$ret = Item::insert($likedata);

	Logger::notice('pumpio_dolike: ' . $ret . ' User ' . $own_id . ' ' . $uid . ' Contact: ' . $contactid . ' URI ' . $orig_post['uri']);
}

function pumpio_get_contact($uid, $contact, $no_insert = false)
{
	$cid = Contact::getIdForURL($contact->url, $uid);

	if ($no_insert) {
		return $cid;
	}

	$r = Contact::selectFirst([], ['uid' => $uid, 'nurl' => Strings::normaliseLink($contact->url)]);
	if (!DBA::isResult($r)) {
		// create contact record
		Contact::insert([
			'uid'      => $uid,
			'created'  => DateTimeFormat::utcNow(),
			'url'      => $contact->url,
			'nurl'     => Strings::normaliseLink($contact->url),
			'addr'     => str_replace('acct:', '', $contact->id),
			'alias'    => '',
			'notify'   => $contact->id,
			'poll'     => 'pump.io ' . $contact->id,
			'name'     => $contact->displayName,
			'nick'     => $contact->preferredUsername,
			'photo'    => $contact->image->url,
			'network'  => Protocol::PUMPIO,
			'rel'      => Contact::FRIEND,
			'priority' => 1,
			'location' => $contact->location->displayName,
			'about'    => $contact->summary,
			'writable' => 1,
			'blocked'  => 0,
			'readonly' => 0,
			'pending'  => 0
		]);

		$r = Contact::selectFirst([], ['uid' => $uid, 'nurl' => Strings::normaliseLink($contact->url)]);
		if (!DBA::isResult($r)) {
			return false;
		}

		$contact_id = $r['id'];

		Circle::addMember(User::getDefaultCircle($uid), $contact_id);
	} else {
		$contact_id = $r['id'];
	}

	if (!empty($contact->image->url)) {
		Contact::updateAvatar($contact_id, $contact->image->url);
	}

	return $contact_id;
}

function pumpio_dodelete(int $uid, array $self, $post, string $own_id)
{
	// Two queries for speed issues
	$condition = ['uri' => $post->object->id, 'uid' => $uid];
	if (Post::exists($condition)) {
		Item::markForDeletion($condition);
		return true;
	}

	$condition = ['extid' => $post->object->id, 'uid' => $uid];
	if (Post::exists($condition)) {
		Item::markForDeletion($condition);
		return true;
	}
	return false;
}

function pumpio_dopost($client, int $uid, array $self, $post, string $own_id, bool $threadcompletion = true)
{
	if (($post->verb == 'like') || ($post->verb == 'favorite')) {
		return pumpio_dolike($uid, $self, $post, $own_id);
	}

	if (($post->verb == 'unlike') || ($post->verb == 'unfavorite')) {
		return pumpio_dounlike($uid, $self, $post, $own_id);
	}

	if ($post->verb == 'delete') {
		return pumpio_dodelete($uid, $self, $post, $own_id);
	}

	if ($post->verb != 'update') {
		// Two queries for speed issues
		if (Post::exists(['uri' => $post->object->id, 'uid' => $uid])) {
			return false;
		}
		if (Post::exists(['extid' => $post->object->id, 'uid' => $uid])) {
			return false;
		}
	}

	// Only handle these three types
	if (!strstr('post|share|update', $post->verb)) {
		return false;
	}

	$receiptians = [];
	if (@is_array($post->cc)) {
		$receiptians = array_merge($receiptians, $post->cc);
	}

	if (@is_array($post->to)) {
		$receiptians = array_merge($receiptians, $post->to);
	}

	$public = false;

	foreach ($receiptians as $receiver) {
		if (is_string($receiver->objectType) && ($receiver->id == 'http://activityschema.org/collection/public')) {
			$public = true;
		}
	}

	$postarray = [];
	$postarray['network'] = Protocol::PUMPIO;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;
	$postarray['uri'] = $post->object->id;
	$postarray['object-type'] = ActivityNamespace::ACTIVITY_SCHEMA . strtolower($post->object->objectType);

	if ($post->object->objectType != 'comment') {
		$contact_id = pumpio_get_contact($uid, $post->actor);

		if (!$contact_id) {
			$contact_id = $self['id'];
		}

		$postarray['thr-parent'] = $post->object->id;

		if (!$public) {
			$postarray['private'] = 1;
			$postarray['allow_cid'] = '<' . $self['id'] . '>';
		}
	} else {
		$contact_id = pumpio_get_contact($uid, $post->actor, true);

		if (Strings::compareLink($post->actor->url, $own_id)) {
			$contact_id = $self['id'];
			$post->actor->displayName = $self['name'];
			$post->actor->url = $self['url'];
			$post->actor->image->url = $self['photo'];
		} elseif ($contact_id == 0) {
			// Take an existing contact, the contact of the note or - as a fallback - the id of the user
			$contact = Contact::selectFirst([], ['nurl' => Strings::normaliseLink($post->actor->url), 'uid' => $uid, 'blocked' => false, 'readonly' => false]);
			if (DBA::isResult($contact)) {
				$contact_id = $contact['id'];
			} else {
				$contact_id = $self['id'];
			}
		}

		$reply = new stdClass;
		$reply->verb = 'note';

		if (isset($post->cc)) {
			$reply->cc = $post->cc;
		}

		if (isset($post->to)) {
			$reply->to = $post->to;
		}

		$reply->object = new stdClass;
		$reply->object->objectType = $post->object->inReplyTo->objectType;
		$reply->object->content = $post->object->inReplyTo->content;
		$reply->object->id = $post->object->inReplyTo->id;
		$reply->actor = $post->object->inReplyTo->author;
		$reply->url = $post->object->inReplyTo->url;
		$reply->generator = new stdClass;
		$reply->generator->displayName = 'pumpio';
		$reply->published = $post->object->inReplyTo->published;
		$reply->received = $post->object->inReplyTo->updated;
		pumpio_dopost($client, $uid, $self, $reply, $own_id, false);

		$postarray['thr-parent'] = $post->object->inReplyTo->id;
	}

	// When there is no content there is no need to continue
	if (empty($post->object->content)) {
		return false;
	}

	if (!empty($post->object->pump_io->proxyURL)) {
		$postarray['extid'] = $post->object->pump_io->proxyURL;
	}

	$postarray['contact-id'] = $contact_id;
	$postarray['verb'] = Activity::POST;
	$postarray['owner-name'] = $post->actor->displayName;
	$postarray['owner-link'] = $post->actor->url;
	$postarray['author-name'] = $postarray['owner-name'];
	$postarray['author-link'] = $postarray['owner-link'];
	if (!empty($post->actor->image)) {
		$postarray['owner-avatar'] = $post->actor->image->url;
		$postarray['author-avatar'] = $postarray['owner-avatar'];
	}
	$postarray['plink'] = $post->object->url;
	$postarray['app'] = $post->generator->displayName;
	$postarray['title'] = '';
	$postarray['body'] = HTML::toBBCode($post->object->content);
	$postarray['object'] = json_encode($post);

	if (!empty($post->object->fullImage->url)) {
		$postarray['body'] = '[url=' . $post->object->fullImage->url . '][img]' . $post->object->image->url . "[/img][/url]\n" . $postarray['body'];
	}

	if (!empty($post->object->displayName)) {
		$postarray['title'] = $post->object->displayName;
	}

	$postarray['created'] = DateTimeFormat::utc($post->published);
	if (isset($post->updated)) {
		$postarray['edited'] = DateTimeFormat::utc($post->updated);
	} elseif (isset($post->received)) {
		$postarray['edited'] = DateTimeFormat::utc($post->received);
	} else {
		$postarray['edited'] = $postarray['created'];
	}

	if ($post->verb == 'share') {
		if (isset($post->object->author->displayName) && ($post->object->author->displayName != '')) {
			$share_author = $post->object->author->displayName;
		} elseif (isset($post->object->author->preferredUsername) && ($post->object->author->preferredUsername != '')) {
			$share_author = $post->object->author->preferredUsername;
		} else {
			$share_author = $post->object->author->url;
		}

		if (isset($post->object->created)) {
			$created = DateTimeFormat::utc($post->object->created);
		} else {
			$created = '';
		}

		$postarray['body'] = Friendica\Content\Text\BBCode::getShareOpeningTag($share_author, $post->object->author->url,
						$post->object->author->image->url, $post->links->self->href, $created) .
					$postarray['body'] . '[/share]';
	}

	if (trim($postarray['body']) == '') {
		return false;
	}

	$top_item = Item::insert($postarray);
	$postarray['id'] = $top_item;

	if (($top_item == 0) && ($post->verb == 'update')) {
		$fields = [
			'title' => $postarray['title'],
			'body' => $postarray['body'],
			'changed' => $postarray['edited'],
		];
		$condition = ['uri' => $postarray['uri'], 'uid' => $uid];
		Item::update($fields, $condition);
	}

	if (($post->object->objectType == 'comment') && $threadcompletion) {
		pumpio_fetchallcomments($uid, $postarray['thr-parent']);
	}

	return $top_item;
}

function pumpio_fetchinbox(int $uid)
{
	$ckey     = DI::pConfig()->get($uid, 'pumpio', 'consumer_key');
	$csecret  = DI::pConfig()->get($uid, 'pumpio', 'consumer_secret');
	$otoken   = DI::pConfig()->get($uid, 'pumpio', 'oauth_token');
	$osecret  = DI::pConfig()->get($uid, 'pumpio', 'oauth_token_secret');
	$lastdate = DI::pConfig()->get($uid, 'pumpio', 'lastdate');
	$hostname = DI::pConfig()->get($uid, 'pumpio', 'host');
	$username = DI::pConfig()->get($uid, 'pumpio', 'user');

	$own_id = 'https://' . $hostname . '/' . $username;

	$self = User::getOwnerDataById($uid);

	$lastitems = DBA::p("SELECT `uri` FROM `post-thread-user`
		INNER JOIN `post-view` ON `post-view`.`uri-id` = `post-thread-user`.`uri-id`
		WHERE `post-thread-user`.`network` = ? AND `post-thread-user`.`uid` = ? AND `post-view`.`extid` != ''
		ORDER BY `post-thread-user`.`commented` DESC LIMIT 10", Protocol::PUMPIO, $uid);

	$client = new oauth_client_class();
	$client->oauth_version = '1.0a';
	$client->authorization_header = true;
	$client->url_parameters = false;

	$client->client_id = $ckey;
	$client->client_secret = $csecret;
	$client->access_token = $otoken;
	$client->access_token_secret = $osecret;

	$last_id = DI::pConfig()->get($uid, 'pumpio', 'last_id');

	$url = 'https://'.$hostname.'/api/user/'.$username.'/inbox';

	if ($last_id != '') {
		$url .= '?since=' . urlencode($last_id);
	}

	if (pumpio_reachable($url)) {
		$success = $client->CallAPI($url, 'GET', [], ['FailOnAccessError' => true], $user);
	} else {
		$success = false;
	}

	if (!$success) {
		return;
	}

	if (!empty($user->items)) {
		$posts = array_reverse($user->items);

		if (count($posts)) {
			foreach ($posts as $post) {
				$last_id = $post->id;
				pumpio_dopost($client, $uid, $self, $post, $own_id, true);
			}
		}
	}

	while ($item = DBA::fetch($lastitems)) {
		pumpio_fetchallcomments($uid, $item['uri']);
	}
	DBA::close($lastitems);

	DI::pConfig()->set($uid, 'pumpio', 'last_id', $last_id);
}

function pumpio_getallusers(int $uid)
{
	$ckey     = DI::pConfig()->get($uid, 'pumpio', 'consumer_key');
	$csecret  = DI::pConfig()->get($uid, 'pumpio', 'consumer_secret');
	$otoken   = DI::pConfig()->get($uid, 'pumpio', 'oauth_token');
	$osecret  = DI::pConfig()->get($uid, 'pumpio', 'oauth_token_secret');
	$hostname = DI::pConfig()->get($uid, 'pumpio', 'host');
	$username = DI::pConfig()->get($uid, 'pumpio', 'user');

	$client = new oauth_client_class;
	$client->oauth_version = '1.0a';
	$client->authorization_header = true;
	$client->url_parameters = false;

	$client->client_id = $ckey;
	$client->client_secret = $csecret;
	$client->access_token = $otoken;
	$client->access_token_secret = $osecret;

	$url = 'https://' . $hostname . '/api/user/' . $username . '/following';

	if (pumpio_reachable($url)) {
		$success = $client->CallAPI($url, 'GET', [], ['FailOnAccessError' => true], $users);
	} else {
		$success = false;
	}

	if (empty($users)) {
		return;
	}

	if ($users->totalItems > count($users->items)) {
		$url = 'https://'.$hostname.'/api/user/'.$username.'/following?count='.$users->totalItems;

		if (pumpio_reachable($url)) {
			$success = $client->CallAPI($url, 'GET', [], ['FailOnAccessError' => true], $users);
		} else {
			$success = false;
		}
	}

	if (!empty($users->items)) {
		foreach ($users->items as $user) {
			pumpio_get_contact($uid, $user);
		}
	}
}

function pumpio_getreceiver(array $b)
{
	$receiver = [];

	if (!$b['private']) {
		if (!strstr($b['postopts'], 'pumpio')) {
			return $receiver;
		}

		$public = DI::pConfig()->get($b['uid'], 'pumpio', 'public');

		if ($public) {
			$receiver['to'][] = [
				'objectType' => 'collection',
				'id' => 'http://activityschema.org/collection/public'
			];
		}
	} else {
		$cids = explode('><', $b['allow_cid']);
		$gids = explode('><', $b['allow_gid']);

		foreach ($cids as $cid) {
			$cid = trim($cid, ' <>');

			$contact = Contact::selectFirst(['name', 'nick', 'url'], ['id' => $cid, 'uid' => $b['uid'], 'network' => Protocol::PUMPIO, 'blocked' => false, 'readonly' => false]);
			if (DBA::isResult($contact)) {
				$receiver['bcc'][] = [
					'displayName' => $contact['name'],
					'objectType' => 'person',
					'preferredUsername' => $contact['nick'],
					'url' => $contact['url'],
				];
			}
		}
		foreach ($gids as $gid) {
			$gid = trim($gid, ' <>');

			$contacts = DBA::p("SELECT `contact`.`name`, `contact`.`nick`, `contact`.`url`, `contact`.`network`
				FROM `group_member` AS `circle_member`, `contact` WHERE `circle_member`.`gid` = ?
				AND `contact`.`id` = `circle_member`.`contact-id` AND `contact`.`network` = ?",
				$gid, Protocol::PUMPIO);

			while ($row = DBA::fetch($contacts)) {
				$receiver['bcc'][] = [
					'displayName' => $row['name'],
					'objectType' => 'person',
					'preferredUsername' => $row['nick'],
					'url' => $row['url'],
				];
			}
			DBA::close($contacts);
		}
	}

	if ($b['inform'] != '') {
		$inform = explode(',', $b['inform']);

		foreach ($inform as $cid) {
			if (substr($cid, 0, 4) != 'cid:') {
				continue;
			}

			$cid = str_replace('cid:', '', $cid);

			$contact = Contact::selectFirst(['name', 'nick', 'url'], ['id' => $cid, 'uid' => $b['uid'], 'network' => Protocol::PUMPIO, 'blocked' => false, 'readonly' => false]);
			if (DBA::isResult($contact)) {
				$receiver['to'][] = [
					'displayName' => $contact['name'],
					'objectType' => 'person',
					'preferredUsername' => $contact['nick'],
					'url' => $contact['url'],
				];
			}
		}
	}

	return $receiver;
}

function pumpio_fetchallcomments($uid, $id)
{
	$ckey     = DI::pConfig()->get($uid, 'pumpio', 'consumer_key');
	$csecret  = DI::pConfig()->get($uid, 'pumpio', 'consumer_secret');
	$otoken   = DI::pConfig()->get($uid, 'pumpio', 'oauth_token');
	$osecret  = DI::pConfig()->get($uid, 'pumpio', 'oauth_token_secret');
	$hostname = DI::pConfig()->get($uid, 'pumpio', 'host');
	$username = DI::pConfig()->get($uid, 'pumpio', 'user');

	Logger::notice('pumpio_fetchallcomments: completing comment for user ' . $uid . ' post id ' . $id);

	$own_id = 'https://' . $hostname . '/' . $username;

	$self = User::getOwnerDataById($uid);

	// Fetching the original post
	$condition = ["`uri` = ? AND `uid` = ? AND `extid` != ''", $id, $uid];
	$original = Post::selectFirst(['extid'], $condition);
	if (!DBA::isResult($original)) {
		return false;
	}

	$url = $original['extid'];

	$client = new oauth_client_class;
	$client->oauth_version = '1.0a';
	$client->authorization_header = true;
	$client->url_parameters = false;

	$client->client_id = $ckey;
	$client->client_secret = $csecret;
	$client->access_token = $otoken;
	$client->access_token_secret = $osecret;

	Logger::notice('pumpio_fetchallcomments: fetching comment for user ' . $uid . ', URL ' . $url);

	if (pumpio_reachable($url)) {
		$success = $client->CallAPI($url, 'GET', [], ['FailOnAccessError' => true], $item);
	} else {
		$success = false;
	}

	if (!$success) {
		return;
	}

	if ($item->likes->totalItems != 0) {
		foreach ($item->likes->items as $post) {
			$like = new stdClass;
			$like->object = new stdClass;
			$like->object->id = $item->id;
			$like->actor = new stdClass;
			if (!empty($item->displayName)) {
				$like->actor->displayName = $item->displayName;
			}
			//$like->actor->preferredUsername = $item->preferredUsername;
			//$like->actor->image = $item->image;
			$like->actor->url = $item->url;
			$like->generator = new stdClass;
			$like->generator->displayName = 'pumpio';
			pumpio_dolike($uid, $self, $post, $own_id, false);
		}
	}

	if ($item->replies->totalItems == 0) {
		return;
	}

	foreach ($item->replies->items as $item) {
		if ($item->id == $id) {
			continue;
		}

		// Checking if the comment already exists - Two queries for speed issues
		if (Post::exists(['uri' => $item->id, 'uid' => $uid])) {
			continue;
		}

		if (Post::exists(['extid' => $item->id, 'uid' => $uid])) {
			continue;
		}

		$post = new stdClass;
		$post->verb = 'post';
		$post->actor = $item->author;
		$post->published = $item->published;
		$post->received = $item->updated;
		$post->generator = new stdClass;
		$post->generator->displayName = 'pumpio';
		// To-Do: Check for public post

		unset($item->author);
		unset($item->published);
		unset($item->updated);

		$post->object = $item;

		Logger::notice('pumpio_fetchallcomments: posting comment ' . $post->object->id . ' ', json_decode(json_encode($post), true));
		pumpio_dopost($client, $uid, $self, $post, $own_id, false);
	}
}

function pumpio_reachable(string $url): bool
{
	return DI::httpClient()->get($url, HttpClientAccept::DEFAULT, [HttpClientOptions::TIMEOUT => 10])->isSuccess();
}

/*
To-Do:
 - edit own notes
 - delete own notes
*/
