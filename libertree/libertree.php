<?php
/**
 * Name: Libertree Post Connector
 * Description: Post to libertree accounts
 * Version: 1.0
 * Author: Tony Baldwin <https://free-haven.org/u/tony>
 */

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Post;

function libertree_install()
{
	Hook::register('hook_fork',            'addon/libertree/libertree.php', 'libertree_hook_fork');
	Hook::register('post_local',           'addon/libertree/libertree.php', 'libertree_post_local');
	Hook::register('notifier_normal',      'addon/libertree/libertree.php', 'libertree_send');
	Hook::register('jot_networks',         'addon/libertree/libertree.php', 'libertree_jot_nets');
	Hook::register('connector_settings',      'addon/libertree/libertree.php', 'libertree_settings');
	Hook::register('connector_settings_post', 'addon/libertree/libertree.php', 'libertree_settings_post');
}

function libertree_jot_nets(array &$jotnets_fields)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'libertree', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'libertree_enable',
				DI::l10n()->t('Post to libertree'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'libertree', 'post_by_default'),
			],
		];
	}
}

function libertree_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled         = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'libertree', 'post', false);
	$ltree_api_token = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'libertree', 'libertree_api_token');
	$ltree_url       = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'libertree', 'libertree_url');
	$def_enabled     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'libertree', 'post_by_default');

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/libertree/');
	$html = Renderer::replaceMacros($t, [
		'$enabled'         => ['libertree', DI::l10n()->t('Enable Libertree Post Addon'), $enabled],
		'$ltree_url'       => ['libertree_url', DI::l10n()->t('Libertree site URL'), $ltree_url],
		'$ltree_api_token' => ['libertree_api_token', DI::l10n()->t('Libertree API token'), $ltree_api_token],
		'$bydefault'       => ['ij_bydefault', DI::l10n()->t('Post to Libertree by default'), $def_enabled],
	]);

	$data = [
		'connector' => 'libertree',
		'title'     => DI::l10n()->t('Libertree Export'),
		'image'     => 'images/libertree.png',
		'enabled'   => $enabled,
		'html'      => $html,
	];
}

function libertree_settings_post(array &$b)
{
	if (!empty($_POST['libertree-submit'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(),'libertree','post',intval($_POST['libertree']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(),'libertree','post_by_default',intval($_POST['libertree_bydefault']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(),'libertree','libertree_api_token',trim($_POST['libertree_api_token']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(),'libertree','libertree_url',trim($_POST['libertree_url']));

	}

}

function libertree_hook_fork(array &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if ($post['deleted'] || $post['private'] || ($post['created'] !== $post['edited']) ||
		!strstr($post['postopts'], 'libertree') || ($post['parent'] != $post['id'])) {
		$b['execute'] = false;
		return;
	}
}

function libertree_post_local(array &$b)
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

	$ltree_post   = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(),'libertree','post'));

	$ltree_enable = (($ltree_post && !empty($_REQUEST['libertree_enable'])) ? intval($_REQUEST['libertree_enable']) : 0);

	if ($b['api_source'] && intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(),'libertree','post_by_default'))) {
		$ltree_enable = 1;
	}

	if (!$ltree_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'libertree';
}

function libertree_send(array &$b)
{
	Logger::notice('libertree_send: invoked');

	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if (! strstr($b['postopts'],'libertree')) {
		return;
	}

	if ($b['parent'] != $b['id']) {
		return;
	}

	// Dont't post if the post doesn't belong to us.
	// This is a check for group postings
	$self = DBA::selectFirst('contact', ['id'], ['uid' => $b['uid'], 'self' => true]);
	if ($b['contact-id'] != $self['id']) {
		return;
	}

	$b['body'] = Post\Media::addAttachmentsToBody($b['uri-id'], DI::contentItem()->addSharedPost($b));

	$ltree_api_token = DI::pConfig()->get($b['uid'],'libertree','libertree_api_token');
	$ltree_url = DI::pConfig()->get($b['uid'],'libertree','libertree_url');
	$ltree_blog = "$ltree_url/api/v1/posts/create/?token=$ltree_api_token";
	$ltree_source = DI::baseUrl()->getHost();

	if ($b['app'] != "")
		$ltree_source .= " (".$b['app'].")";

	if($ltree_url && $ltree_api_token && $ltree_blog && $ltree_source) {
		$title = $b['title'];
		$body = $b['body'];
		// Insert a newline before and after a quote
		$body = str_ireplace("[quote", "\n\n[quote", $body);
		$body = str_ireplace("[/quote]", "[/quote]\n\n", $body);

		// Removal of tags and mentions
		// #-tags
		$body = preg_replace('/#\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', '#$2', $body);
 		// @-mentions
		$body = preg_replace('/@\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', '@$2', $body);

		// remove multiple newlines
		do {
			$oldbody = $body;
			$body = str_replace("\n\n\n", "\n\n", $body);
		} while ($oldbody != $body);

		// convert to markdown
		$body = BBCode::toMarkdown($body, false);

		// Adding the title
		if (strlen($title)) {
			$body = '## ' . html_entity_decode($title) . "\n\n" . $body;
		}


		$params = [
			'text' => $body,
			'source' => $ltree_source
		//	'token' => $ltree_api_token
		];

		$result = DI::httpClient()->post($ltree_blog, $params)->getBody();
		Logger::notice('libertree: ' . $result);
	}
}
