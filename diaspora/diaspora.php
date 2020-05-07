<?php

/**
 * Name: Diaspora Post Connector
 * Description: Post to Diaspora
 * Version: 0.2
 * Author: Michael Vogel <heluecht@pirati.ca>
 */

require_once 'addon/diaspora/Diaspora_Connection.php';

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\Session;
use Friendica\Database\DBA;
use Friendica\Core\Worker;
use Friendica\DI;

function diaspora_install()
{
	Hook::register('hook_fork',               'addon/diaspora/diaspora.php', 'diaspora_hook_fork');
	Hook::register('post_local',              'addon/diaspora/diaspora.php', 'diaspora_post_local');
	Hook::register('notifier_normal',         'addon/diaspora/diaspora.php', 'diaspora_send');
	Hook::register('jot_networks',            'addon/diaspora/diaspora.php', 'diaspora_jot_nets');
	Hook::register('connector_settings',      'addon/diaspora/diaspora.php', 'diaspora_settings');
	Hook::register('connector_settings_post', 'addon/diaspora/diaspora.php', 'diaspora_settings_post');
}

function diaspora_uninstall()
{
	Hook::unregister('hook_fork',               'addon/diaspora/diaspora.php', 'diaspora_hook_fork');
	Hook::unregister('post_local',              'addon/diaspora/diaspora.php', 'diaspora_post_local');
	Hook::unregister('notifier_normal',         'addon/diaspora/diaspora.php', 'diaspora_send');
	Hook::unregister('jot_networks',            'addon/diaspora/diaspora.php', 'diaspora_jot_nets');
	Hook::unregister('connector_settings',      'addon/diaspora/diaspora.php', 'diaspora_settings');
	Hook::unregister('connector_settings_post', 'addon/diaspora/diaspora.php', 'diaspora_settings_post');
}

function diaspora_jot_nets(App $a, array &$jotnets_fields)
{
	if (!local_user()) {
		return;
	}

	if (DI::pConfig()->get(local_user(), 'diaspora', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'diaspora_enable',
				DI::l10n()->t('Post to Diaspora'),
				DI::pConfig()->get(local_user(), 'diaspora', 'post_by_default')
			]
		];
	}
}

function diaspora_settings(App $a, &$s)
{
	if (! local_user()) {
		return;
	}

	/* Get the current state of our config variables */

	$enabled = DI::pConfig()->get(local_user(),'diaspora','post');
	$def_enabled = DI::pConfig()->get(local_user(),'diaspora','post_by_default');

	$handle = DI::pConfig()->get(local_user(), 'diaspora', 'handle');
	$password = DI::pConfig()->get(local_user(), 'diaspora', 'password');
	$aspect = DI::pConfig()->get(local_user(),'diaspora','aspect');

	$info = '';
	$error = '';
	if (Session::get('my_address')) {
		$info = DI::l10n()->t('Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. ', Session::get('my_address'));
		$info .= DI::l10n()->t('This connector is only meant if you still want to use your old Diaspora account for some time. ');
		$info .= DI::l10n()->t('However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead.', Session::get('my_address'));
	}

	$aspect_select = '';
	if ($handle && $password) {
		$conn = new Diaspora_Connection($handle, $password);
		$conn->logIn();
		$rawAspects = $conn->getAspects();
		if ($rawAspects) {
			$availableAspects = [
				'all_aspects' => DI::l10n()->t('All aspects'),
				'public' => DI::l10n()->t('Public'),
			];
			foreach ($rawAspects as $rawAspect) {
				$availableAspects[$rawAspect->id] = $rawAspect->name;
			}

			$aspect_select = ['aspect', DI::l10n()->t('Post to aspect:'), $aspect, '', $availableAspects];
			$info = DI::l10n()->t('Connected with your Diaspora account <strong>%s</strong>', $handle);
		} else {
			$info = '';
			$error = DI::l10n()->t("Can't login to your Diaspora account. Please check handle (in the format user@domain.tld) and password.");
		}
	}

	DI::page()->registerStylesheet('addon/diaspora/diaspora.css');

	$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/diaspora/');
	$s .= Renderer::replaceMacros($t, [
		'$header'           => DI::l10n()->t('Diaspora Export'),
		'$info_header'      => DI::l10n()->t('Information'),
		'$error_header'     => DI::l10n()->t('Error'),
		'$submit'           => DI::l10n()->t('Save Settings'),
		'$info'             => $info,
		'$error'            => $error,
		'$enabled'          => $enabled,
		'$enabled_checkbox' => ['enabled', DI::l10n()->t('Enable Diaspora Post Addon'), $enabled],
		'$handle'           => ['handle', DI::l10n()->t('Diaspora handle'), $handle, null, null, 'placeholder="user@domain.tld"'],
		'$password'         => ['password', DI::l10n()->t('Diaspora password'), '', DI::l10n()->t('Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it.')],
		'$aspect_select'    => $aspect_select,
		'$post_by_default'  => ['post_by_default', DI::l10n()->t('Post to Diaspora by default'), $def_enabled],
	]);
}


function diaspora_settings_post(App $a, &$b)
{
	if (!empty($_POST['diaspora-submit'])) {
		DI::pConfig()->set(local_user(),'diaspora', 'post'           , intval($_POST['enabled']));
		if (intval($_POST['enabled'])) {
			if (isset($_POST['handle'])) {
				DI::pConfig()->set(local_user(),'diaspora', 'handle'         , trim($_POST['handle']));
				DI::pConfig()->set(local_user(),'diaspora', 'password'       , trim($_POST['password']));
			}
			if (!empty($_POST['aspect'])) {
				DI::pConfig()->set(local_user(),'diaspora', 'aspect'         , trim($_POST['aspect']));
				DI::pConfig()->set(local_user(),'diaspora', 'post_by_default', intval($_POST['post_by_default']));
			}
			notice(DI::l10n()->t('Diaspora settings updated.'));
		} else {
			DI::pConfig()->delete(local_user(), 'diaspora', 'password');
			notice(DI::l10n()->t('Diaspora connector disabled.'));
		}
	}
}

function diaspora_hook_fork(&$a, &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if ($post['deleted'] || $post['private'] || ($post['created'] !== $post['edited']) ||
		!strstr($post['postopts'], 'diaspora') || ($post['parent'] != $post['id'])) {
		$b['execute'] = false;
		return;
	}
}

function diaspora_post_local(App $a, array &$b)
{
	if ($b['edit']) {
		return;
	}

	if (!local_user() || (local_user() != $b['uid'])) {
		return;
	}

	if ($b['private'] || $b['parent']) {
		return;
	}

	$diaspora_post   = intval(DI::pConfig()->get(local_user(),'diaspora','post'));

	$diaspora_enable = (($diaspora_post && !empty($_REQUEST['diaspora_enable'])) ? intval($_REQUEST['diaspora_enable']) : 0);

	if ($b['api_source'] && intval(DI::pConfig()->get(local_user(),'diaspora','post_by_default'))) {
		$diaspora_enable = 1;
	}

	if (!$diaspora_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'diaspora';
}

function diaspora_send(App $a, array &$b)
{
	$hostname = DI::baseUrl()->getHostname();

	Logger::log('diaspora_send: invoked');

	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if (!strstr($b['postopts'],'diaspora')) {
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

	Logger::log('diaspora_send: prepare posting', Logger::DEBUG);

	$handle = DI::pConfig()->get($b['uid'],'diaspora','handle');
	$password = DI::pConfig()->get($b['uid'],'diaspora','password');
	$aspect = DI::pConfig()->get($b['uid'],'diaspora','aspect');

	if ($handle && $password) {
		Logger::log('diaspora_send: all values seem to be okay', Logger::DEBUG);

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
		$body = BBCode::toMarkdown($body);

		// Adding the title
		if (strlen($title)) {
			$body = "## ".html_entity_decode($title)."\n\n".$body;
		}

		require_once "addon/diaspora/diasphp.php";

		try {
			Logger::log('diaspora_send: prepare', Logger::DEBUG);
			$conn = new Diaspora_Connection($handle, $password);
			Logger::log('diaspora_send: try to log in '.$handle, Logger::DEBUG);
			$conn->logIn();
			Logger::log('diaspora_send: try to send '.$body, Logger::DEBUG);

			$conn->provider = $hostname;
			$conn->postStatusMessage($body, $aspect);

			Logger::log('diaspora_send: success');
		} catch (Exception $e) {
			Logger::log("diaspora_send: Error submitting the post: " . $e->getMessage());

			Logger::log('diaspora_send: requeueing '.$b['uid'], Logger::DEBUG);

			Worker::defer();
		}
	}
}
