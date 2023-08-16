<?php
/**
 * Name: Twitter Post Connector
 * Description: Post to Twitter
 * Version: 2.0
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 * Maintainer: Hypolite Petovan <https://friendica.mrpetovan.com/profile/hypolite>
 * Maintainer: Michael Vogel <https://pirati.ca/profile/heluecht>
 *
 * Copyright (c) 2011-2023 Tobias Diekershoff, Michael Vogel, Hypolite Petovan
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above
 *    * copyright notice, this list of conditions and the following disclaimer in
 *      the documentation and/or other materials provided with the distribution.
 *    * Neither the name of the <organization> nor the names of its contributors
 *      may be used to endorse or promote products derived from this software
 *      without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\Plaintext;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\Worker;
use Friendica\DI;
use Friendica\Model\Item;
use Friendica\Model\Post;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Model\Photo;
use Friendica\Object\Image;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

const TWITTER_IMAGE_SIZE = [2000000, 1000000, 500000, 100000, 50000];

function twitter_install()
{
	Hook::register('load_config', __FILE__, 'twitter_load_config');
	Hook::register('connector_settings', __FILE__, 'twitter_settings');
	Hook::register('connector_settings_post', __FILE__, 'twitter_settings_post');
	Hook::register('hook_fork', __FILE__, 'twitter_hook_fork');
	Hook::register('post_local', __FILE__, 'twitter_post_local');
	Hook::register('notifier_normal', __FILE__, 'twitter_post_hook');
	Hook::register('jot_networks', __FILE__, 'twitter_jot_nets');
}

function twitter_load_config(ConfigFileManager $loader)
{
	DI::app()->getConfigCache()->load($loader->loadAddonConfig('twitter'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function twitter_jot_nets(array &$jotnets_fields)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'twitter_enable',
				DI::l10n()->t('Post to Twitter'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'post_by_default')
			]
		];
	}
}

function twitter_settings_post()
{
	if (!DI::userSession()->getLocalUserId() || empty($_POST['twitter-submit'])) {
		return;
	}

	$api_key       = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'api_key');
	$api_secret    = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'api_secret');
	$access_token  = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'access_token');
	$access_secret = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'access_secret');

	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'twitter', 'post',            (bool)$_POST['twitter-enable']);
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'twitter', 'post_by_default', (bool)$_POST['twitter-default']);
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'twitter', 'api_key',         $_POST['twitter-api-key']);
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'twitter', 'api_secret',      $_POST['twitter-api-secret']);
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'twitter', 'access_token',    $_POST['twitter-access-token']);
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'twitter', 'access_secret',   $_POST['twitter-access-secret']);

	if (
		empty(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'last_status')) ||
		($api_key != $_POST['twitter-api-key']) || ($api_secret != $_POST['twitter-api-secret']) ||
		($access_token != $_POST['twitter-access-token']) || ($access_secret != $_POST['twitter-access-secret'])
	) {
		twitter_test_connection(DI::userSession()->getLocalUserId());
	}
}

function twitter_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled      = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'post') ?? false;
	$def_enabled  = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'post_by_default') ?? false;

	$api_key       = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'api_key');
	$api_secret    = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'api_secret');
	$access_token  = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'access_token');
	$access_secret = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'access_secret');

	$last_status = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'last_status');
	if (!empty($last_status['code']) && !empty($last_status['reason'])) {
		$status_title = sprintf('%d - %s', $last_status['code'], $last_status['reason']);
	} else {
		$status_title = DI::l10n()->t('No status.');
	}
	$status_content = $last_status['content'] ?? '';

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/twitter/');
	$html = Renderer::replaceMacros($t, [
		'$enable'        => ['twitter-enable', DI::l10n()->t('Allow posting to Twitter'), $enabled, DI::l10n()->t('If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.')],
		'$default'       => ['twitter-default', DI::l10n()->t('Send public postings to Twitter by default'), $def_enabled],
		'$api_key'       => ['twitter-api-key', DI::l10n()->t('API Key'), $api_key],
		'$api_secret'    => ['twitter-api-secret', DI::l10n()->t('API Secret'), $api_secret],
		'$access_token'  => ['twitter-access-token', DI::l10n()->t('Access Token'), $access_token],
		'$access_secret' => ['twitter-access-secret', DI::l10n()->t('Access Secret'), $access_secret],
		'$help'          => DI::l10n()->t('Each user needs to register their own app to be able to post to Twitter. Please visit https://developer.twitter.com/en/portal/projects-and-apps to register a project. Inside the project you then have to register an app. You will find the needed data for the connector on the page "Keys and token" in the app settings.'),
		'$status_title'  => ['twitter-status-title', DI::l10n()->t('Last Status Summary'), $status_title, '', '', 'readonly'],
		'$status'        => ['twitter-status', DI::l10n()->t('Last Status Content'), $status_content, '', '', 'readonly'],
	]);

	$data = [
		'connector' => 'twitter',
		'title'     => DI::l10n()->t('Twitter Export'),
		'enabled'   => $enabled,
		'image'     => 'images/twitter.png',
		'html'      => $html,
	];
}

function twitter_hook_fork(array &$b)
{
	DI::logger()->debug('twitter_hook_fork', $b);

	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if (
		$post['deleted'] || $post['private'] || ($post['created'] !== $post['edited']) ||
		!strstr($post['postopts'], 'twitter') || ($post['gravity'] != Item::GRAVITY_PARENT)
	) {
		$b['execute'] = false;
		return;
	}
}

function twitter_post_local(array &$b)
{
	if (!DI::userSession()->getLocalUserId() || (DI::userSession()->getLocalUserId() != $b['uid'])) {
		return;
	}

	if ($b['edit'] || $b['private'] || $b['parent']) {
		return;
	}

	$twitter_post   = (bool)DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'post');
	$twitter_enable = (($twitter_post && !empty($_REQUEST['twitter_enable'])) ? (bool)$_REQUEST['twitter_enable'] : false);

	// if API is used, default to the chosen settings
	if ($b['api_source'] && intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'twitter', 'post_by_default'))) {
		$twitter_enable = true;
	}

	if (!$twitter_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'twitter';
}

function twitter_post_hook(array &$b)
{
	DI::logger()->debug('Invoke post hook', $b);

	if (($b['gravity'] != Item::GRAVITY_PARENT) || !strstr($b['postopts'], 'twitter') || $b['private'] || $b['deleted'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	$b['body'] = Post\Media::addAttachmentsToBody($b['uri-id'], DI::contentItem()->addSharedPost($b));

	Logger::notice('twitter post invoked', ['id' => $b['id'], 'guid' => $b['guid']]);

	DI::pConfig()->load($b['uid'], 'twitter');

	$api_key       = DI::pConfig()->get($b['uid'], 'twitter', 'api_key');
	$api_secret    = DI::pConfig()->get($b['uid'], 'twitter', 'api_secret');
	$access_token  = DI::pConfig()->get($b['uid'], 'twitter', 'access_token');
	$access_secret = DI::pConfig()->get($b['uid'], 'twitter', 'access_secret');

	if (empty($api_key) || empty($api_secret) || empty($access_token) || empty($access_secret)) {
		Logger::info('Missing keys, secrets or tokens.');
		return;
	}

	$msgarr = Plaintext::getPost($b, 280, true, BBCode::TWITTER);
	Logger::debug('Got plaintext', ['id' => $b['id'], 'message' => $msgarr]);

	$media_ids = [];

	if (!empty($msgarr['images']) || !empty($msgarr['remote_images'])) {
		Logger::info('Got images', ['id' => $b['id'], 'images' => $msgarr['images'] ?? []]);

		$retrial = Worker::getRetrial();
		if ($retrial > 4) {
			return;
		}
		foreach ($msgarr['images'] ?? [] as $image) {
			if (count($media_ids) == 4) {
				continue;
			}
			try {
				$media_ids[] = twitter_upload_image($b['uid'], $image, $retrial);
			} catch (RequestException $exception) {
				Logger::warning('Error while uploading image', ['image' => $image, 'code' => $exception->getCode(), 'message' => $exception->getMessage()]);
				Worker::defer();
				return;
			}
		}
	}

	$in_reply_to_tweet_id = 0;

	Logger::debug('Post message', ['id' => $b['id'], 'parts' => count($msgarr['parts'])]);
	foreach ($msgarr['parts'] as $key => $part) {
		try {
			$id = twitter_post_status($b['uid'], $part, $media_ids, $in_reply_to_tweet_id);
			Logger::info('twitter_post send', ['part' => $key, 'id' => $b['id'], 'result' => $id]);
		} catch (RequestException $exception) {
			Logger::warning('Error while posting message', ['part' => $key, 'id' => $b['id'], 'code' => $exception->getCode(), 'message' => $exception->getMessage()]);
			$status = [
				'code'    => $exception->getCode(),
				'reason'  => $exception->getResponse()->getReasonPhrase(),
				'content' => $exception->getMessage()
			];
			DI::pConfig()->set($b['uid'], 'twitter', 'last_status', $status);
			if ($key == 0) {
				Worker::defer();
			}
			break;
		}

		$in_reply_to_tweet_id = $id;
		$media_ids = [];
	}
}

function twitter_post_status(int $uid, string $status, array $media_ids = [], string $in_reply_to_tweet_id = ''): string
{
	$parameters = ['text' => $status];
	if (!empty($media_ids)) {
		$parameters['media'] = ['media_ids' => $media_ids];
	}
	if (!empty($in_reply_to_tweet_id)) {
		$parameters['reply'] = ['in_reply_to_tweet_id' => $in_reply_to_tweet_id];
	}

	$response = twitter_post($uid, 'https://api.twitter.com/2/tweets', 'json', $parameters);

	return $response->data->id;
}

function twitter_upload_image(int $uid, array $image, int $retrial)
{
	if (!empty($image['id'])) {
		$photo = Photo::selectFirst([], ['id' => $image['id']]);
	} else {
		$photo = Photo::createPhotoForExternalResource($image['url']);
	}

	$picturedata = Photo::getImageForPhoto($photo);

	$picture = new Image($picturedata, $photo['type']);
	$height  = $picture->getHeight();
	$width   = $picture->getWidth();
	$size    = strlen($picturedata);

	$picture     = Photo::resizeToFileSize($picture, TWITTER_IMAGE_SIZE[$retrial]);
	$new_height  = $picture->getHeight();
	$new_width   = $picture->getWidth();
	$picturedata = $picture->asString();
	$new_size    = strlen($picturedata);

	Logger::info('Uploading', ['uid' => $uid, 'retrial' => $retrial, 'height' => $new_height, 'width' => $new_width, 'size' => $new_size, 'orig-height' => $height, 'orig-width' => $width, 'orig-size' => $size, 'image' => $image]);
	$media = twitter_post($uid, 'https://upload.twitter.com/1.1/media/upload.json', 'form_params', ['media' => base64_encode($picturedata)]);
	Logger::info('Uploading done', ['uid' => $uid, 'retrial' => $retrial, 'height' => $new_height, 'width' => $new_width, 'size' => $new_size, 'orig-height' => $height, 'orig-width' => $width, 'orig-size' => $size, 'image' => $image]);

	if (isset($media->media_id_string)) {
		$media_id = $media->media_id_string;

		if (!empty($image['description'])) {
			$data = [
				'media_id' => $media->media_id_string,
				'alt_text' => [
					'text' => substr($image['description'], 0, 1000)
				]
			];
			$ret = twitter_post($uid, 'https://upload.twitter.com/1.1/media/metadata/create.json', 'json', $data);
			Logger::info('Metadata create', ['uid' => $uid, 'data' => $data, 'return' => $ret]);
		}
	} else {
		Logger::error('Failed upload', ['uid' => $uid, 'size' => strlen($picturedata), 'image' => $image['url'], 'return' => $media]);
		throw new Exception('Failed upload of ' . $image['url']);
	}

	return $media_id;
}

function twitter_post(int $uid, string $url, string $type, array $data): stdClass
{
	$stack = HandlerStack::create();

	$middleware = new Oauth1([
		'consumer_key'    => DI::pConfig()->get($uid, 'twitter', 'api_key'),
		'consumer_secret' => DI::pConfig()->get($uid, 'twitter', 'api_secret'),
		'token'           => DI::pConfig()->get($uid, 'twitter', 'access_token'),
		'token_secret'    => DI::pConfig()->get($uid, 'twitter', 'access_secret'),
	]);

	$stack->push($middleware);

	$client = new Client([
		'handler' => $stack
	]);

	$response = $client->post($url, ['auth' => 'oauth', $type => $data]);
	$body     = $response->getBody()->getContents();

	$status = [
		'code'    => $response->getStatusCode(),
		'reason'  => $response->getReasonPhrase(),
		'content' => $body
	];

	DI::pConfig()->set($uid, 'twitter', 'last_status', $status);

	$content = json_decode($body) ?? new stdClass;
	Logger::debug('Success', ['content' => $content]);
	return $content;
}

function twitter_test_connection(int $uid)
{
	$stack = HandlerStack::create();

	$middleware = new Oauth1([
		'consumer_key'    => DI::pConfig()->get($uid, 'twitter', 'api_key'),
		'consumer_secret' => DI::pConfig()->get($uid, 'twitter', 'api_secret'),
		'token'           => DI::pConfig()->get($uid, 'twitter', 'access_token'),
		'token_secret'    => DI::pConfig()->get($uid, 'twitter', 'access_secret'),
	]);

	$stack->push($middleware);

	$client = new Client([
		'handler' => $stack
	]);

	try {
		$response = $client->get('https://api.twitter.com/2/users/me', ['auth' => 'oauth']);
		$status = [
			'code'   => $response->getStatusCode(),
			'reason'  => $response->getReasonPhrase(),
			'content' => $response->getBody()->getContents()
		];
		DI::pConfig()->set(1, 'twitter', 'last_status',  $status);
		Logger::info('Test successful', ['uid' => $uid]);
	} catch (RequestException $exception) {
		$status = [
			'code'    => $exception->getCode(),
			'reason'  => $exception->getResponse()->getReasonPhrase(),
			'content' => $exception->getMessage()
		];
		DI::pConfig()->set(1, 'twitter', 'last_status',  $status);
		Logger::info('Test failed', ['uid' => $uid]);
	}
}
