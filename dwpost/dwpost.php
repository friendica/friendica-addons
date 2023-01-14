<?php
/**
 * Name: Dreamwidth Post Connector
 * Description: Post to dreamwidth
 * Version: 1.0
 * Author: Tony Baldwin <https://free-haven.org/profile/tony>
 * Author: Michael Johnston
 * Author: Cat Gray <https://free-haven.org/profile/catness>
 */

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Model\Post;
use Friendica\Model\Tag;
use Friendica\Model\User;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\XML;

function dwpost_install()
{
	Hook::register('post_local',              'addon/dwpost/dwpost.php', 'dwpost_post_local');
	Hook::register('notifier_normal',         'addon/dwpost/dwpost.php', 'dwpost_send');
	Hook::register('jot_networks',            'addon/dwpost/dwpost.php', 'dwpost_jot_nets');
	Hook::register('connector_settings',      'addon/dwpost/dwpost.php', 'dwpost_settings');
	Hook::register('connector_settings_post', 'addon/dwpost/dwpost.php', 'dwpost_settings_post');
}

function dwpost_jot_nets(array &$jotnets_fields)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'dwpost', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'dwpost_enable',
				DI::l10n()->t('Post to Dreamwidth'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'dwpost', 'post_by_default')
			]
		];
	}
}


function dwpost_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'dwpost', 'post', false);
	$dw_username = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'dwpost', 'dw_username');
	$def_enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'dwpost', 'post_by_default');

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/dwpost/');
	$html = Renderer::replaceMacros($t, [
		'$enabled'   => ['dwpost', DI::l10n()->t('Enable Dreamwidth Post Addon'), $enabled],
		'$username'  => ['dw_username', DI::l10n()->t('Dreamwidth username'), $dw_username],
		'$password'  => ['dw_password', DI::l10n()->t('Dreamwidth password')],
		'$bydefault' => ['dw_bydefault', DI::l10n()->t('Post to Dreamwidth by default'), $def_enabled],
	]);

	$data = [
		'connector' => 'dwpost',
		'title'     => DI::l10n()->t('Dreamwidth Export'),
		'image'     => 'images/dreamwidth.png',
		'enabled'   => $enabled,
		'html'      => $html,
	];
}


function dwpost_settings_post(array &$b)
{
	if (!empty($_POST['dwpost-submit'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'dwpost', 'post',            intval($_POST['dwpost']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'dwpost', 'post_by_default', intval($_POST['dw_bydefault']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'dwpost', 'dw_username',     trim($_POST['dw_username']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'dwpost', 'dw_password',     trim($_POST['dw_password']));
	}
}

function dwpost_post_local(array &$b)
{
	// This can probably be changed to allow editing by pointing to a different API endpoint
	if ($b['edit']) {
		return;
	}

	if ((!DI::userSession()->getLocalUserId()) || (DI::userSession()->getLocalUserId() != $b['uid'])) {
		return;
	}

	if ($b['private'] || $b['parent']) {
		return;
	}

	$dw_post = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(),'dwpost','post'));

	$dw_enable = (($dw_post && !empty($_REQUEST['dwpost_enable'])) ? intval($_REQUEST['dwpost_enable']) : 0);

	if ($b['api_source'] && intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(),'dwpost','post_by_default'))) {
		$dw_enable = 1;
	}

	if (!$dw_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'dwpost';
}

function dwpost_send(array &$b)
{
	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if (strpos($b['postopts'] ?? '', 'dwpost') === false) {
		return;
	}

	if ($b['parent'] != $b['id']) {
		return;
	}

	$b['body'] = Post\Media::addAttachmentsToBody($b['uri-id'], DI::contentItem()->addSharedPost($b));

	/*
	 * dreamwidth post in the LJ user's timezone.
	 * Hopefully the person's Friendica account
	 * will be set to the same thing.
	 */

	$user = User::getById($b['uid']);
	$tz = $user['timezone'] ?: 'UTC';

	$dw_username = DI::pConfig()->get($b['uid'],'dwpost','dw_username');
	$dw_password = DI::pConfig()->get($b['uid'],'dwpost','dw_password');
	$dw_blog = 'http://www.dreamwidth.org/interface/xmlrpc';

	if ($dw_username && $dw_password && $dw_blog) {
		$title = $b['title'];
		$post = BBCode::convertForUriId($b['uri-id'], $b['body'], BBCode::CONNECTORS);
		$post = XML::escape($post);
		$tags = Tag::getCSVByURIId($b['uri-id'], [Tag::HASHTAG]);

		$date = DateTimeFormat::convert($b['created'], $tz);
		$year = intval(substr($date,0,4));
		$mon  = intval(substr($date,5,2));
		$day  = intval(substr($date,8,2));
		$hour = intval(substr($date,11,2));
		$min  = intval(substr($date,14,2));

		$xml = <<< EOT
<?xml version="1.0" encoding="utf-8"?>
<methodCall><methodName>LJ.XMLRPC.postevent</methodName>
<params><param>
<value><struct>
<member><name>year</name><value><int>$year</int></value></member>
<member><name>mon</name><value><int>$mon</int></value></member>
<member><name>day</name><value><int>$day</int></value></member>
<member><name>hour</name><value><int>$hour</int></value></member>
<member><name>min</name><value><int>$min</int></value></member>
<member><name>event</name><value><string>$post</string></value></member>
<member><name>username</name><value><string>$dw_username</string></value></member>
<member><name>password</name><value><string>$dw_password</string></value></member>
<member><name>subject</name><value><string>$title</string></value></member>
<member><name>lineendings</name><value><string>unix</string></value></member>
<member><name>ver</name><value><int>1</int></value></member>
<member><name>props</name>
<value><struct>
<member><name>useragent</name><value><string>Friendica</string></value></member>
<member><name>taglist</name><value><string>$tags</string></value></member>
</struct></value></member>
</struct></value>
</param></params>
</methodCall>

EOT;

		Logger::debug('dwpost: data: ' . $xml);

		if ($dw_blog !== 'test') {
			$x = DI::httpClient()->post($dw_blog, $xml, ['Content-Type' => 'text/xml'])->getBody();
		}

		Logger::info('posted to dreamwidth: ' . ($x) ? $x : '');
	}
}
