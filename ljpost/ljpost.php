<?php
/**
 * Name: LiveJournal Post Connector
 * Description: Post to LiveJournal
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

function ljpost_install()
{
	Hook::register('post_local',   'addon/ljpost/ljpost.php', 'ljpost_post_local');
	Hook::register('notifier_normal',  'addon/ljpost/ljpost.php', 'ljpost_send');
	Hook::register('jot_networks', 'addon/ljpost/ljpost.php', 'ljpost_jot_nets');
	Hook::register('connector_settings',  'addon/ljpost/ljpost.php', 'ljpost_settings');
	Hook::register('connector_settings_post', 'addon/ljpost/ljpost.php', 'ljpost_settings_post');
}

function ljpost_jot_nets(array &$jotnets_fields)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(),'ljpost','post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'ljpost_enable',
				DI::l10n()->t('Post to LiveJournal'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ljpost', 'post_by_default'),
			],
		];
	}
}

function ljpost_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled     = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ljpost', 'post', false);
	$ij_username = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ljpost', 'ij_username');
	$def_enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ljpost', 'post_by_default');

	$t= Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/ljpost/');
	$html = Renderer::replaceMacros($t, [
		'$enabled'   => ['ljpost', DI::l10n()->t('Enable LiveJournal Post Addon'), $enabled],
		'$username'  => ['ij_username', DI::l10n()->t('LiveJournal username'), $ij_username],
		'$password'  => ['ij_password', DI::l10n()->t('LiveJournal password')],
		'$bydefault' => ['ij_bydefault', DI::l10n()->t('Post to LiveJournal by default'), $def_enabled],
	]);

	$data = [
		'connector' => 'ljpost',
		'title' => DI::l10n()->t('LiveJournal Export'),
		'image' => 'addon/ljpost/livejournal.png',
		'enabled'   => $enabled,
		'html'  => $html,
	];
}

function ljpost_settings_post(array &$b)
{
	if (!empty($_POST['ljpost-submit'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'ljpost', 'post', intval($_POST['ljpost']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'ljpost', 'post_by_default', intval($_POST['lj_bydefault']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'ljpost', 'lj_username', trim($_POST['lj_username']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'ljpost', 'lj_password', trim($_POST['lj_password']));
	}
}

function ljpost_post_local(array &$b)
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

	$lj_post   = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(),'ljpost','post'));
	$lj_enable = (($lj_post && !empty($_REQUEST['ljpost_enable'])) ? intval($_REQUEST['ljpost_enable']) : 0);

	if ($b['api_source'] && intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'ljpost', 'post_by_default'))) {
		$lj_enable = 1;
	}

	if (!$lj_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}
	$b['postopts'] .= 'ljpost';
}

function ljpost_send(array &$b)
{
	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if (!strstr($b['postopts'],'ljpost')) {
		return;
	}

	if ($b['parent'] != $b['id']) {
		return;
	}

	$b['body'] = Post\Media::addAttachmentsToBody($b['uri-id'], DI::contentItem()->addSharedPost($b));

	// LiveJournal post in the LJ user's timezone.
	// Hopefully the person's Friendica account
	// will be set to the same thing.

	$user = User::getById($b['uid']);
	$tz = $user['timezone'] ?: 'UTC';

	$lj_username = XML::escape(DI::pConfig()->get($b['uid'],'ljpost','lj_username'));
	$lj_password = XML::escape(DI::pConfig()->get($b['uid'],'ljpost','lj_password'));
	$lj_journal = XML::escape(DI::pConfig()->get($b['uid'],'ljpost','lj_journal'));
//	if(! $lj_journal)
//		$lj_journal = $lj_username;

	$lj_blog = XML::escape(DI::pConfig()->get($b['uid'],'ljpost','lj_blog'));
	if (!strlen($lj_blog)) {
		$lj_blog = XML::escape('http://www.livejournal.com/interface/xmlrpc');
	}

	if ($lj_username && $lj_password && $lj_blog) {
		$title = XML::escape($b['title']);
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
<methodCall>
<methodName>LJ.XMLRPC.postevent</methodName>
	<params>
		<param>
			<value>
				<struct>
					<member><name>username</name><value><string>$lj_username</string></value></member>
					<member><name>password</name><value><string>$lj_password</string></value></member>
					<member><name>event</name><value><string>$post</string></value></member>
					<member><name>subject</name><value><string>$title</string></value></member>
					<member><name>lineendings</name><value><string>unix</string></value></member>
					<member><name>year</name><value><int>$year</int></value></member>
					<member><name>mon</name><value><int>$mon</int></value></member>
					<member><name>day</name><value><int>$day</int></value></member>
					<member><name>hour</name><value><int>$hour</int></value></member>
					<member><name>min</name><value><int>$min</int></value></member>
					<member><name>usejournal</name><value><string>$lj_username</string></value></member>
					<member>
						<name>props</name>
						<value>
							<struct>
								<member>
									<name>useragent</name>
									<value><string>Friendica</string></value>
								</member>
								<member>
									<name>taglist</name>
									<value><string>$tags</string></value>
								</member>
							</struct>
						</value>
					</member>
				</struct>
			</value>
		</param>
	</params>
</methodCall>
EOT;

		Logger::debug('ljpost: data: ' . $xml);

		if ($lj_blog !== 'test') {
			$x = DI::httpClient()->post($lj_blog, $xml, ['Content-Type' => 'text/xml'])->getBody();
		}

		Logger::info('posted to livejournal: ' . ($x) ? $x : '');
	}
}
