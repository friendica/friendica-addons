<?php
/**
 * Name: WordPress Post Connector
 * Description: Post to WordPress (or anything else which uses blogger XMLRPC API)
 * Version: 1.1
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\HTML;
use Friendica\Core\Addon;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;
use Friendica\Database\DBA;
use Friendica\Util\Network;
use Friendica\Util\Strings;
use Friendica\Util\XML;

function wppost_install()
{
	Addon::registerHook('hook_fork',            'addon/wppost/wppost.php', 'wppost_hook_fork');
	Addon::registerHook('post_local',           'addon/wppost/wppost.php', 'wppost_post_local');
	Addon::registerHook('notifier_normal',      'addon/wppost/wppost.php', 'wppost_send');
	Addon::registerHook('jot_networks',         'addon/wppost/wppost.php', 'wppost_jot_nets');
	Addon::registerHook('connector_settings',      'addon/wppost/wppost.php', 'wppost_settings');
	Addon::registerHook('connector_settings_post', 'addon/wppost/wppost.php', 'wppost_settings_post');
}

function wppost_uninstall()
{
	Addon::unregisterHook('hook_fork',        'addon/wppost/wppost.php', 'wppost_hook_fork');
	Addon::unregisterHook('post_local',       'addon/wppost/wppost.php', 'wppost_post_local');
	Addon::unregisterHook('notifier_normal',  'addon/wppost/wppost.php', 'wppost_send');
	Addon::unregisterHook('jot_networks',     'addon/wppost/wppost.php', 'wppost_jot_nets');
	Addon::unregisterHook('connector_settings',      'addon/wppost/wppost.php', 'wppost_settings');
	Addon::unregisterHook('connector_settings_post', 'addon/wppost/wppost.php', 'wppost_settings_post');

	// obsolete - remove
	Addon::unregisterHook('post_local_end',   'addon/wppost/wppost.php', 'wppost_send');
	Addon::unregisterHook('addon_settings',  'addon/wppost/wppost.php', 'wppost_settings');
	Addon::unregisterHook('addon_settings_post',  'addon/wppost/wppost.php', 'wppost_settings_post');
}


function wppost_jot_nets(&$a, &$b)
{
	if (!local_user()) {
		return;
	}

	$wp_post = PConfig::get(local_user(), 'wppost', 'post');
	if (intval($wp_post) == 1) {
		$wp_defpost = PConfig::get(local_user(),'wppost','post_by_default');
		$selected = ((intval($wp_defpost) == 1) ? ' checked="checked" ' : '');
		$b .= '<div class="profile-jot-net"><input type="checkbox" name="wppost_enable" ' . $selected . ' value="1" /> '
			. L10n::t('Post to Wordpress') . '</div>';
	}
}


function wppost_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/wppost/wppost.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variables */

	$enabled = PConfig::get(local_user(),'wppost','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');

	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = PConfig::get(local_user(),'wppost','post_by_default');
	$back_enabled = PConfig::get(local_user(),'wppost','backlink');
	$shortcheck_enabled = PConfig::get(local_user(),'wppost','shortcheck');

	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');
	$back_checked = (($back_enabled) ? ' checked="checked" ' : '');
	$shortcheck_checked = (($shortcheck_enabled) ? ' checked="checked" ' : '');

	$wp_username = PConfig::get(local_user(), 'wppost', 'wp_username');
	$wp_password = PConfig::get(local_user(), 'wppost', 'wp_password');
	$wp_blog = PConfig::get(local_user(), 'wppost', 'wp_blog');
	$wp_backlink_text = PConfig::get(local_user(), 'wppost', 'wp_backlink_text');


    /* Add some HTML to the existing form */

    $s .= '<span id="settings_wppost_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_wppost_expanded\'); openClose(\'settings_wppost_inflated\');">';
    $s .= '<img class="connector'.$css.'" src="images/wordpress.png" /><h3 class="connector">'. L10n::t('Wordpress Export').'</h3>';
    $s .= '</span>';
    $s .= '<div id="settings_wppost_expanded" class="settings-block" style="display: none;">';
    $s .= '<span class="fakelink" onclick="openClose(\'settings_wppost_expanded\'); openClose(\'settings_wppost_inflated\');">';
    $s .= '<img class="connector'.$css.'" src="images/wordpress.png" /><h3 class="connector">'. L10n::t('Wordpress Export').'</h3>';
    $s .= '</span>';
    $s .= '<div id="wppost-enable-wrapper">';
    $s .= '<label id="wppost-enable-label" for="wppost-checkbox">' . L10n::t('Enable WordPress Post Addon') . '</label>';
    $s .= '<input id="wppost-checkbox" type="checkbox" name="wppost" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-username-wrapper">';
    $s .= '<label id="wppost-username-label" for="wppost-username">' . L10n::t('WordPress username') . '</label>';
    $s .= '<input id="wppost-username" type="text" name="wp_username" value="' . $wp_username . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-password-wrapper">';
    $s .= '<label id="wppost-password-label" for="wppost-password">' . L10n::t('WordPress password') . '</label>';
    $s .= '<input id="wppost-password" type="password" name="wp_password" value="' . $wp_password . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-blog-wrapper">';
    $s .= '<label id="wppost-blog-label" for="wppost-blog">' . L10n::t('WordPress API URL') . '</label>';
    $s .= '<input id="wppost-blog" type="text" name="wp_blog" value="' . $wp_blog . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-bydefault-wrapper">';
    $s .= '<label id="wppost-bydefault-label" for="wppost-bydefault">' . L10n::t('Post to WordPress by default') . '</label>';
    $s .= '<input id="wppost-bydefault" type="checkbox" name="wp_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-backlink-wrapper">';
    $s .= '<label id="wppost-backlink-label" for="wppost-backlink">' . L10n::t('Provide a backlink to the Friendica post') . '</label>';
    $s .= '<input id="wppost-backlink" type="checkbox" name="wp_backlink" value="1" ' . $back_checked . '/>';
    $s .= '</div><div class="clear"></div>';
    $s .= '<div id="wppost-backlinktext-wrapper">';
    $s .= '<label id="wppost-backlinktext-label" for="wp_backlink_text">' . L10n::t('Text for the backlink, e.g. Read the original post and comment stream on Friendica.') . '</label>';
    $s .= '<input id="wppost-backlinktext" type="text" name="wp_backlink_text" value="'. $wp_backlink_text.'" ' . $wp_backlink_text . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-shortcheck-wrapper">';
    $s .= '<label id="wppost-shortcheck-label" for="wppost-shortcheck">' . L10n::t("Don't post messages that are too short") . '</label>';
    $s .= '<input id="wppost-shortcheck" type="checkbox" name="wp_shortcheck" value="1" '.$shortcheck_checked.'/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="wppost-submit" name="wppost-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';

}


function wppost_settings_post(&$a,&$b) {

	if(!empty($_POST['wppost-submit'])) {

		PConfig::set(local_user(),'wppost','post',intval($_POST['wppost']));
		PConfig::set(local_user(),'wppost','post_by_default',intval($_POST['wp_bydefault']));
		PConfig::set(local_user(),'wppost','wp_username',trim($_POST['wp_username']));
		PConfig::set(local_user(),'wppost','wp_password',trim($_POST['wp_password']));
		PConfig::set(local_user(),'wppost','wp_blog',trim($_POST['wp_blog']));
		PConfig::set(local_user(),'wppost','backlink',trim($_POST['wp_backlink']));
		PConfig::set(local_user(),'wppost','shortcheck',trim($_POST['wp_shortcheck']));
		$wp_backlink_text = Strings::escapeTags(trim($_POST['wp_backlink_text']));
		$wp_backlink_text = BBCode::convert($wp_backlink_text, false, 8);
		$wp_backlink_text = HTML::toPlaintext($wp_backlink_text, 0, true);
		PConfig::set(local_user(),'wppost','wp_backlink_text', $wp_backlink_text);

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

	$wp_post   = intval(PConfig::get(local_user(),'wppost','post'));

	$wp_enable = (($wp_post && !empty($_REQUEST['wppost_enable'])) ? intval($_REQUEST['wppost_enable']) : 0);

	if ($b['api_source'] && intval(PConfig::get(local_user(),'wppost','post_by_default'))) {
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

	$wp_username = XML::escape(PConfig::get($b['uid'], 'wppost', 'wp_username'));
	$wp_password = XML::escape(PConfig::get($b['uid'], 'wppost', 'wp_password'));
	$wp_blog = PConfig::get($b['uid'],'wppost','wp_blog');
	$wp_backlink_text = PConfig::get($b['uid'],'wppost','wp_backlink_text');
	if ($wp_backlink_text == '') {
		$wp_backlink_text = L10n::t('Read the orig­i­nal post and com­ment stream on Friendica');
	}

	if ($wp_username && $wp_password && $wp_blog) {
		$wptitle = trim($b['title']);

		if (intval(PConfig::get($b['uid'], 'wppost', 'shortcheck'))) {
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
				$title = trim(preg_replace("/\[share.*?\](.*?)\[\/share\]/ism","\n$1\n",$b['body']));

				$title = HTML::toPlaintext(BBCode::convert($title, false), 0, true)."\n";
				$pos = strpos($title, "\n");
				$trailer = "";
				if (($pos == 0) || ($pos > 100)) {
					$pos = 100;
					$trailer = "...";
				}

				$wptitle = substr($title, 0, $pos).$trailer;
			}
		}

		$title = '<title>' . (($wptitle) ? $wptitle : L10n::t('Post from Friendica')) . '</title>';
		$post = BBCode::convert($b['body'], false, 4);

		// If a link goes to youtube then remove the stuff around it. Wordpress detects youtube links and embeds it
		$post = preg_replace('/<a.*?href="(https?:\/\/www.youtube.com\/.*?)".*?>(.*?)<\/a>/ism',"\n$1\n",$post);
		$post = preg_replace('/<a.*?href="(https?:\/\/youtu.be\/.*?)".*?>(.*?)<\/a>/ism',"\n$1\n",$post);

		$post = $title.$post;

		$wp_backlink = intval(PConfig::get($b['uid'],'wppost','backlink'));
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
			$x = Network::post($wp_blog, $xml)->getBody();
		}
		Logger::log('posted to wordpress: ' . (($x) ? $x : ''), Logger::DEBUG);
	}
}
