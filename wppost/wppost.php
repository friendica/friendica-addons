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
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Post;
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

function wppost_jot_nets(\Friendica\App &$a, array &$jotnets_fields)
{
	if (!local_user()) {
		return;
	}

	if (DI::pConfig()->get(local_user(),'wppost','post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'wppost_enable',
				DI::l10n()->t('Post to Wordpress'),
				DI::pConfig()->get(local_user(),'wppost','post_by_default')
			]
		];
	}
}


function wppost_settings(&$a, &$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	DI::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl()->get() . '/addon/wppost/wppost.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variables */

	$enabled = DI::pConfig()->get(local_user(),'wppost','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');

	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = DI::pConfig()->get(local_user(),'wppost','post_by_default');
	$back_enabled = DI::pConfig()->get(local_user(),'wppost','backlink');
	$shortcheck_enabled = DI::pConfig()->get(local_user(),'wppost','shortcheck');

	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');
	$back_checked = (($back_enabled) ? ' checked="checked" ' : '');
	$shortcheck_checked = (($shortcheck_enabled) ? ' checked="checked" ' : '');

	$wp_username = DI::pConfig()->get(local_user(), 'wppost', 'wp_username');
	$wp_password = DI::pConfig()->get(local_user(), 'wppost', 'wp_password');
	$wp_blog = DI::pConfig()->get(local_user(), 'wppost', 'wp_blog');
	$wp_backlink_text = DI::pConfig()->get(local_user(), 'wppost', 'wp_backlink_text');


    /* Add some HTML to the existing form */

    $s .= '<span id="settings_wppost_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_wppost_expanded\'); openClose(\'settings_wppost_inflated\');">';
    $s .= '<img class="connector'.$css.'" src="images/wordpress.png" /><h3 class="connector">'. DI::l10n()->t('Wordpress Export').'</h3>';
    $s .= '</span>';
    $s .= '<div id="settings_wppost_expanded" class="settings-block" style="display: none;">';
    $s .= '<span class="fakelink" onclick="openClose(\'settings_wppost_expanded\'); openClose(\'settings_wppost_inflated\');">';
    $s .= '<img class="connector'.$css.'" src="images/wordpress.png" /><h3 class="connector">'. DI::l10n()->t('Wordpress Export').'</h3>';
    $s .= '</span>';
    $s .= '<div id="wppost-enable-wrapper">';
    $s .= '<label id="wppost-enable-label" for="wppost-enable">' . DI::l10n()->t('Enable WordPress Post Addon') . '</label>';
	$s .= '<input type="hidden" name="wppost" value="0"/>';
    $s .= '<input id="wppost-enable" type="checkbox" name="wppost" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-username-wrapper">';
    $s .= '<label id="wppost-username-label" for="wppost-username">' . DI::l10n()->t('WordPress username') . '</label>';
    $s .= '<input id="wppost-username" type="text" name="wp_username" value="' . $wp_username . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-password-wrapper">';
    $s .= '<label id="wppost-password-label" for="wppost-password">' . DI::l10n()->t('WordPress password') . '</label>';
    $s .= '<input id="wppost-password" type="password" name="wp_password" value="' . $wp_password . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-blog-wrapper">';
    $s .= '<label id="wppost-blog-label" for="wppost-blog">' . DI::l10n()->t('WordPress API URL') . '</label>';
    $s .= '<input id="wppost-blog" type="text" name="wp_blog" value="' . $wp_blog . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-bydefault-wrapper">';
    $s .= '<label id="wppost-bydefault-label" for="wppost-bydefault">' . DI::l10n()->t('Post to WordPress by default') . '</label>';
	$s .= '<input type="hidden" name="wp_bydefault" value="0"/>';
    $s .= '<input id="wppost-bydefault" type="checkbox" name="wp_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-backlink-wrapper">';
    $s .= '<label id="wppost-backlink-label" for="wppost-backlink">' . DI::l10n()->t('Provide a backlink to the Friendica post') . '</label>';
	$s .= '<input type="hidden" name="wp_backlink" value="0"/>';
    $s .= '<input id="wppost-backlink" type="checkbox" name="wp_backlink" value="1" ' . $back_checked . '/>';
    $s .= '</div><div class="clear"></div>';
    $s .= '<div id="wppost-backlinktext-wrapper">';
    $s .= '<label id="wppost-backlinktext-label" for="wp_backlink_text">' . DI::l10n()->t('Text for the backlink, e.g. Read the original post and comment stream on Friendica.') . '</label>';
    $s .= '<input id="wppost-backlinktext" type="text" name="wp_backlink_text" value="'. $wp_backlink_text.'" ' . $wp_backlink_text . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-shortcheck-wrapper">';
    $s .= '<label id="wppost-shortcheck-label" for="wppost-shortcheck">' . DI::l10n()->t("Don't post messages that are too short") . '</label>';
    $s .= '<input type="hidden" name="wp_shortcheck" value="0"/>';
    $s .= '<input id="wppost-shortcheck" type="checkbox" name="wp_shortcheck" value="1" '.$shortcheck_checked.'/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="wppost-submit" name="wppost-submit" class="settings-submit" value="' . DI::l10n()->t('Save Settings') . '" /></div></div>';

}


function wppost_settings_post(&$a, &$b)
{
	if(!empty($_POST['wppost-submit'])) {
		DI::pConfig()->set(local_user(), 'wppost', 'post'           , intval($_POST['wppost']));
		DI::pConfig()->set(local_user(), 'wppost', 'post_by_default', intval($_POST['wp_bydefault']));
		DI::pConfig()->set(local_user(), 'wppost', 'wp_username'    ,   trim($_POST['wp_username']));
		DI::pConfig()->set(local_user(), 'wppost', 'wp_password'    ,   trim($_POST['wp_password']));
		DI::pConfig()->set(local_user(), 'wppost', 'wp_blog'        ,   trim($_POST['wp_blog']));
		DI::pConfig()->set(local_user(), 'wppost', 'backlink'       , intval($_POST['wp_backlink']));
		DI::pConfig()->set(local_user(), 'wppost', 'shortcheck'     , intval($_POST['wp_shortcheck']));
		$wp_backlink_text = BBCode::convert(trim($_POST['wp_backlink_text']), false, BBCode::BACKLINK);
		$wp_backlink_text = HTML::toPlaintext($wp_backlink_text, 0, true);
		DI::pConfig()->set(local_user(), 'wppost', 'wp_backlink_text', $wp_backlink_text);
	}
}

function wppost_hook_fork(&$a, &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if ($post['deleted'] || $post['private'] || ($post['created'] !== $post['edited']) ||
		!strstr($post['postopts'], 'wppost') || ($post['parent'] != $post['id'])) {
		$b['execute'] = false;
		return;
	}
}

function wppost_post_local(&$a, &$b) {

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

	$wp_post   = intval(DI::pConfig()->get(local_user(), 'wppost', 'post'));

	$wp_enable = (($wp_post && !empty($_REQUEST['wppost_enable'])) ? intval($_REQUEST['wppost_enable']) : 0);

	if ($b['api_source'] && intval(DI::pConfig()->get(local_user(), 'wppost', 'post_by_default'))) {
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




function wppost_send(&$a, &$b)
{
	if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if(! strstr($b['postopts'],'wppost')) {
		return;
	}

	if($b['parent'] != $b['id']) {
		return;
	}

	// Dont't post if the post doesn't belong to us.
	// This is a check for forum postings
	$self = DBA::selectFirst('contact', ['id'], ['uid' => $b['uid'], 'self' => true]);
	if ($b['contact-id'] != $self['id']) {
		return;
	}

	$b['body'] = Post\Media::addAttachmentsToBody($b['uri-id'], $b['body']);

	$wp_username = XML::escape(DI::pConfig()->get($b['uid'], 'wppost', 'wp_username'));
	$wp_password = XML::escape(DI::pConfig()->get($b['uid'], 'wppost', 'wp_password'));
	$wp_blog = DI::pConfig()->get($b['uid'],'wppost','wp_blog');
	$wp_backlink_text = DI::pConfig()->get($b['uid'],'wppost','wp_backlink_text');
	if ($wp_backlink_text == '') {
		$wp_backlink_text = DI::l10n()->t('Read the orig­i­nal post and com­ment stream on Friendica');
	}

	if ($wp_username && $wp_password && $wp_blog) {
		$wptitle = trim($b['title']);

		if (intval(DI::pConfig()->get($b['uid'], 'wppost', 'shortcheck'))) {
			// Checking, if its a post that is worth a blog post
			$postentry = false;
			$siteinfo = BBCode::getAttachedData($b["body"]);

			// Is it a link to an aricle, a video or a photo?
			if (isset($siteinfo["type"])) {
				if (in_array($siteinfo["type"], ["link", "audio", "video", "photo"])) {
					$postentry = true;
				}
			}

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
			$siteinfo = BBCode::getAttachedData($b["body"]);
			if (isset($siteinfo["title"])) {
				$wptitle = $siteinfo["title"];
			}

			// If no bookmark is found then take the first line
			if ($wptitle == '') {
				// Remove the share element before fetching the first line
				$title = trim(preg_replace("/\[share.*?\](.*?)\[\/share\]/ism", "\n$1\n", $b['body']));

				$title = BBCode::toPlaintext($title)."\n";
				$pos = strpos($title, "\n");
				$trailer = "";
				if (($pos == 0) || ($pos > 100)) {
					$pos = 100;
					$trailer = "...";
				}

				$wptitle = substr($title, 0, $pos).$trailer;
			}
		}

		$title = '<title>' . (($wptitle) ? $wptitle : DI::l10n()->t('Post from Friendica')) . '</title>';
		$post = BBCode::convertForUriId($b['uri-id'], $b['body'], BBCode::CONNECTORS);

		// If a link goes to youtube then remove the stuff around it. Wordpress detects youtube links and embeds it
		$post = preg_replace('/<a.*?href="(https?:\/\/www.youtube.com\/.*?)".*?>(.*?)<\/a>/ism', "\n$1\n", $post);
		$post = preg_replace('/<a.*?href="(https?:\/\/youtu.be\/.*?)".*?>(.*?)<\/a>/ism', "\n$1\n", $post);

		$post = $title.$post;

		$wp_backlink = intval(DI::pConfig()->get($b['uid'],'wppost','backlink'));
		if($wp_backlink && $b['plink']) {
			$post .= EOL . EOL . '<a href="' . $b['plink'] . '">'
				. $wp_backlink_text . '</a>' . EOL . EOL;
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

		Logger::log('wppost: data: ' . $xml, Logger::DATA);

		if ($wp_blog !== 'test') {
			$x = DI::httpClient()->post($wp_blog, $xml)->getBody();
		}
		Logger::log('posted to wordpress: ' . (($x) ? $x : ''), Logger::DEBUG);
	}
}
