<?php
/**
 * Name: WordPress Post Connector
 * Description: Post to WordPress (or anything else which uses blogger XMLRPC API)
 * Version: 1.1
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */

use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\HTML;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Item;
use Friendica\Model\Post;
use Friendica\Model\User;
use Friendica\Util\XML;

function wppost_install()
{
	Hook::register('hook_fork',            'addon/wppost/wppost.php', 'wppost_hook_fork');
	Hook::register('post_local',           'addon/wppost/wppost.php', 'wppost_post_local');
	Hook::register('notifier_normal',      'addon/wppost/wppost.php', 'wppost_send');
	Hook::register('jot_networks',         'addon/wppost/wppost.php', 'wppost_jot_nets');
	Hook::register('connector_settings',      'addon/wppost/wppost.php', 'wppost_settings');
	Hook::register('connector_settings_post', 'addon/wppost/wppost.php', 'wppost_settings_post');
}

function wppost_jot_nets(array &$jotnets_fields)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'wppost', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'wppost_enable',
				DI::l10n()->t('Post to Wordpress'),
				DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'wppost', 'post_by_default')
			]
		];
	}
}


function wppost_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled            = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'wppost', 'post', false);
	$wp_username        = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'wppost', 'wp_username');
	$wp_blog            = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'wppost', 'wp_blog');
	$def_enabled        = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'wppost', 'post_by_default', false);
	$back_enabled       = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'wppost', 'backlink', false);
	$wp_backlink_text   = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'wppost', 'wp_backlink_text');
	$shortcheck_enabled = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'wppost', 'shortcheck', false);

	$t    = Renderer::getMarkupTemplate('connector_settings.tpl', 'addon/wppost/');
	$html = Renderer::replaceMacros($t, [
		'$enabled'       => ['wppost', DI::l10n()->t('Enable Wordpress Post Addon'), $enabled],
		'$username'      => ['wp_username', DI::l10n()->t('Wordpress username'), $wp_username],
		'$password'      => ['wp_password', DI::l10n()->t('Wordpress password')],
		'$blog'          => ['wp_blog', DI::l10n()->t('WordPress API URL'), $wp_blog],
		'$bydefault'     => ['wp_bydefault', DI::l10n()->t('Post to Wordpress by default'), $def_enabled],
		'$backlink'      => ['wp_backlink', DI::l10n()->t('Provide a backlink to the Friendica post'), $back_enabled],
		'$backlink_text' => ['wp_backlink_text', DI::l10n()->t('Text for the backlink, e.g. Read the original post and comment stream on Friendica.'), $wp_backlink_text],
		'$shortcheck'    => ['wp_shortcheck', DI::l10n()->t('Don\'t post messages that are too short'), $shortcheck_enabled],
	]);

	$data = [
		'connector' => 'wppost',
		'title'     => DI::l10n()->t('Wordpress Export'),
		'image'     => 'images/wordpress.png',
		'enabled'   => $enabled,
		'html'      => $html,
	];
}


function wppost_settings_post(array &$b)
{
	if (!empty($_POST['wppost-submit'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'wppost', 'post', intval($_POST['wppost']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'wppost', 'post_by_default', intval($_POST['wp_bydefault']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'wppost', 'wp_username',   trim($_POST['wp_username']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'wppost', 'wp_password',   trim($_POST['wp_password']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'wppost', 'wp_blog',   trim($_POST['wp_blog']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'wppost', 'backlink', intval($_POST['wp_backlink']));
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'wppost', 'shortcheck', intval($_POST['wp_shortcheck']));
		$wp_backlink_text = BBCode::convertForUriId(User::getSystemUriId(), trim($_POST['wp_backlink_text']), BBCode::BACKLINK);
		$wp_backlink_text = HTML::toPlaintext($wp_backlink_text, 0, true);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'wppost', 'wp_backlink_text', $wp_backlink_text);
	}
}

function wppost_hook_fork(array &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if (
		$post['deleted'] || $post['private'] || ($post['created'] !== $post['edited']) ||
		!strstr($post['postopts'] ?? '', 'wppost') || ($post['parent'] != $post['id'])
	) {
		$b['execute'] = false;
		return;
	}
}

function wppost_post_local(array &$b)
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

	$wp_post   = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'wppost', 'post'));

	$wp_enable = (($wp_post && !empty($_REQUEST['wppost_enable'])) ? intval($_REQUEST['wppost_enable']) : 0);

	if ($b['api_source'] && intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'wppost', 'post_by_default'))) {
		$wp_enable = 1;
	}

	if (!$wp_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'wppost';
}




function wppost_send(array &$b)
{
	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if (!strstr($b['postopts'], 'wppost')) {
		return;
	}

	if ($b['gravity'] != Item::GRAVITY_PARENT) {
		return;
	}

	// Dont't post if the post doesn't belong to us.
	// This is a check for group postings
	$self = DBA::selectFirst('contact', ['id'], ['uid' => $b['uid'], 'self' => true]);
	if ($b['contact-id'] != $self['id']) {
		return;
	}

	$b['body'] = Post\Media::addAttachmentsToBody($b['uri-id'], DI::contentItem()->addSharedPost($b));

	$wp_username = XML::escape(DI::pConfig()->get($b['uid'], 'wppost', 'wp_username'));
	$wp_password = XML::escape(DI::pConfig()->get($b['uid'], 'wppost', 'wp_password'));
	$wp_blog = DI::pConfig()->get($b['uid'], 'wppost', 'wp_blog');
	$wp_backlink_text = DI::pConfig()->get($b['uid'], 'wppost', 'wp_backlink_text');
	if ($wp_backlink_text == '') {
		$wp_backlink_text = DI::l10n()->t('Read the orig­i­nal post and com­ment stream on Friendica');
	}

	if ($wp_username && $wp_password && $wp_blog) {
		$wptitle = trim($b['title']);

		if (intval(DI::pConfig()->get($b['uid'], 'wppost', 'shortcheck'))) {
			// Checking, if its a post that is worth a blog post
			$postentry = (bool)Post\Media::getByURIId($b['uri-id'], [Post\Media::HTML, Post\Media::AUDIO, Post\Media::VIDEO, Post\Media::IMAGE]);

			// Does it have a title?
			if ($wptitle != "") {
				$postentry = true;
			}

			// Is it larger than 500 characters?
			if (strlen($b['body']) > 500) {
				$postentry = true;
			}

			if (!$postentry) {
				return;
			}
		}

		// If the title is empty then try to guess
		if ($wptitle == '') {
			// Fetch information about the post
			$media = Post\Media::getByURIId($b['uri-id'], [Post\Media::HTML]);
			if (!empty($media) && !empty($media[0]['name']) && ($media[0]['name'] != $media[0]['url'])) {
				$wptitle = $media[0]['name'];
			}

			// If no bookmark is found then take the first line
			if ($wptitle == '') {
				// Remove the share element before fetching the first line
				$title = trim(preg_replace("/\[share.*?\](.*?)\[\/share\]/ism", "\n$1\n", $b['body']));

				$title = BBCode::toPlaintext($title) . "\n";
				$pos = strpos($title, "\n");
				$trailer = "";
				if (($pos == 0) || ($pos > 100)) {
					$pos = 100;
					$trailer = "...";
				}

				$wptitle = substr($title, 0, $pos) . $trailer;
			}
		}

		$title = '<title>' . (($wptitle) ? $wptitle : DI::l10n()->t('Post from Friendica')) . '</title>';
		$post = BBCode::convertForUriId($b['uri-id'], $b['body'], BBCode::CONNECTORS);

		// If a link goes to youtube then remove the stuff around it. Wordpress detects youtube links and embeds it
		$post = preg_replace('/<a.*?href="(https?:\/\/www.youtube.com\/.*?)".*?>(.*?)<\/a>/ism', "\n$1\n", $post);
		$post = preg_replace('/<a.*?href="(https?:\/\/youtu.be\/.*?)".*?>(.*?)<\/a>/ism', "\n$1\n", $post);

		$post = $title . $post;

		$wp_backlink = intval(DI::pConfig()->get($b['uid'], 'wppost', 'backlink'));
		if ($wp_backlink && $b['plink']) {
			$post .= '<p><a href="' . $b['plink'] . '">' . $wp_backlink_text . '</a></p>';
		}

		$post = XML::escape($post);


		$xml = <<< EOT
<?xml version=\"1.0\" encoding=\"utf-8\"?>
<methodCall>
  <methodName>blogger.newPost</methodName>
  <params>
    <param><value><string/></value></param>
    <param><value><string/></value></param>
    <param><value><string>$wp_username</string></value></param>
    <param><value><string>$wp_password</string></value></param>
    <param><value><string>$post</string></value></param>
    <param><value><int>1</int></value></param>
  </params>
</methodCall>

EOT;

		Logger::debug('wppost: data: ' . $xml);

		if ($wp_blog !== 'test') {
			$x = DI::httpClient()->post($wp_blog, $xml)->getBody();
		}
		Logger::info('posted to wordpress: ' . (($x) ? $x : ''));
	}
}
