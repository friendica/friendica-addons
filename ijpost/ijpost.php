<?php
/**
 * Name: Insanejournal Post Connector
 * Description: Post to Insanejournal
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
use Friendica\Model\Tag;
use Friendica\Model\User;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\XML;

function ijpost_install()
{
	Hook::register('post_local',           'addon/ijpost/ijpost.php', 'ijpost_post_local');
	Hook::register('notifier_normal',      'addon/ijpost/ijpost.php', 'ijpost_send');
	Hook::register('jot_networks',         'addon/ijpost/ijpost.php', 'ijpost_jot_nets');
	Hook::register('connector_settings',      'addon/ijpost/ijpost.php', 'ijpost_settings');
	Hook::register('connector_settings_post', 'addon/ijpost/ijpost.php', 'ijpost_settings_post');
}

function ijpost_jot_nets(array &$jotnets_fields)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ijpost', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'ijpost_enable',
				DI::l10n()->t('Post to Insanejournal'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ijpost', 'post_by_default')
			]
		];
	}
}

function ijpost_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ijpost', 'post', false);
	$ij_username = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ijpost', 'ij_username');
	$def_enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ijpost', 'post_by_default');

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/ijpost/');
	$html = Renderer::replaceMacros($t, [
		'$enabled'   => ['ijpost', DI::l10n()->t('Enable InsaneJournal Post Addon'), $enabled],
		'$username'  => ['ij_username', DI::l10n()->t('InsaneJournal username'), $ij_username],
		'$password'  => ['ij_password', DI::l10n()->t('InsaneJournal password')],
		'$bydefault' => ['ij_bydefault', DI::l10n()->t('Post to InsaneJournal by default'), $def_enabled],
	]);

	$data = [
		'connector' => 'ijpost',
		'title'     => DI::l10n()->t('InsaneJournal Export'),
		'image'     => 'images/insanejournal.gif',
		'enabled'   => $enabled,
		'html'      => $html,
	];
}

function ijpost_settings_post(array &$b)
{
	if (!empty($_POST['ijpost-submit'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'ijpost', 'post', intval($_POST['ijpost']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'ijpost', 'post_by_default', intval($_POST['ij_bydefault']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'ijpost', 'ij_username', trim($_POST['ij_username']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'ijpost', 'ij_password', trim($_POST['ij_password']));
	}
}

function ijpost_post_local(array &$b)
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

	$ij_post   = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ijpost', 'post'));

	$ij_enable = (($ij_post && !empty($_REQUEST['ijpost_enable'])) ? intval($_REQUEST['ijpost_enable']) : 0);

	if ($b['api_source'] && intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ijpost', 'post_by_default'))) {
		$ij_enable = 1;
	}

	if (!$ij_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'ijpost';
}

function ijpost_send(array &$b)
{
	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if (!strstr($b['postopts'], 'ijpost')) {
		return;
	}

	if ($b['parent'] != $b['id']) {
		return;
	}

	// insanejournal post in the LJ user's timezone.
	// Hopefully the person's Friendica account
	// will be set to the same thing.

	$user = User::getById($b['uid']);
	$tz = $user['timezone'] ?: 'UTC';

	$ij_username = DI::pConfig()->get($b['uid'], 'ijpost', 'ij_username');
	$ij_password = DI::pConfig()->get($b['uid'], 'ijpost', 'ij_password');
	$ij_blog = 'http://www.insanejournal.com/interface/xmlrpc';

	if ($ij_username && $ij_password && $ij_blog) {
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
<member><name>username</name><value><string>$ij_username</string></value></member>
<member><name>password</name><value><string>$ij_password</string></value></member>
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

		Logger::debug('ijpost: data: ' . $xml);

		if ($ij_blog !== 'test') {
			$x = DI::httpClient()->post($ij_blog, $xml, ['Content-Type' => 'text/xml'])->getBody();
		}
		Logger::info('posted to insanejournal: ' . $x ? $x : '');
	}
}
