<?php
/**
 * Name: OPML Export
 * Description: Export user's RSS/Atom contacts as OPML
 * Version: 1.0
 * Author: Fabio Comuni <https://social.gl-como.it/profile/fabrixxm>
 * License: 3-clause BSD license
 */

use Friendica\DI;
use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Network\HTTPException;
use Friendica\Database\DBA;
use Friendica\Core\Renderer;
use Friendica\Core\Protocol;
use Friendica\Model\Contact;
use Friendica\Model\User;

function opmlexport_install()
{
	Hook::register('addon_settings',        __FILE__, 'opmlexport_addon_settings');
	Hook::register('addon_settings_post',   __FILE__, 'opmlexport_addon_settings_post');
	Logger::notice('installed opmlexport Addon');
}


function opmlexport(App $a)
{
	$condition = [
		'uid' => local_user(),
		'self' => false,
		'deleted' => false,
		'archive' => false,
		'blocked' => false,
		'pending' => false,
		'network' => Protocol::FEED
	];
	$data = Contact::selectToArray([], $condition, ['order' => ['name']]);
	$user = User::getById(local_user());

	$xml = new \DOMDocument( '1.0', 'utf-8' );
	$opml = $xml->createElement('opml');
	$head = $xml->createElement('head');
	$body = $xml->createElement('body');
	$outline = $xml->createElement('outline');
	$outline->setAttribute('title', $user['username'] . '\'s RSS/Atom contacts');
	$outline->setAttribute('text', $user['username'] . '\'s RSS/Atom contacts');

	foreach($data as $c) {
		$entry = $xml->createElement('outline');
		$entry->setAttribute('title',  $c['name']);
		$entry->setAttribute('text',   $c['name']);
		$entry->setAttribute('xmlUrl', $c['url']);
		$outline->appendChild($entry);
	}

	$body->appendChild($outline);
	$opml->appendChild($head);
	$opml->appendChild($body);
	$xml->appendChild($opml);
	header('Content-Type: text/x-opml');
	header('Content-Disposition: attachment; filename=feeds.opml');
	$xml->formatOutput = true;
	echo $xml->saveXML();
	die();
}


function opmlexport_addon_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/opmlexport/');
	$s .= Renderer::replaceMacros($t, [
		'$title'   => DI::l10n()->t('OPML Export'),
		'$submit'  => DI::l10n()->t('Export RSS/Atom contacts'),
	]);
}


function opmlexport_addon_settings_post(App $a, &$b)
{
	if (!local_user() || empty($_POST['opmlexport-submit'])) {
		return;
	}
	opmlexport($a);
}
