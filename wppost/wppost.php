<?php

/**
 * Name: WordPress Post Connector
 * Description: Post to WordPress (or anything else which uses blogger XMLRPC API)
 * Version: 1.1
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */

function wppost_install() {
    register_hook('post_local',           'addon/wppost/wppost.php', 'wppost_post_local');
    register_hook('notifier_normal',      'addon/wppost/wppost.php', 'wppost_send');
    register_hook('jot_networks',         'addon/wppost/wppost.php', 'wppost_jot_nets');
    register_hook('connector_settings',      'addon/wppost/wppost.php', 'wppost_settings');
    register_hook('connector_settings_post', 'addon/wppost/wppost.php', 'wppost_settings_post');

}
function wppost_uninstall() {
    unregister_hook('post_local',       'addon/wppost/wppost.php', 'wppost_post_local');
    unregister_hook('notifier_normal',  'addon/wppost/wppost.php', 'wppost_send');
    unregister_hook('jot_networks',     'addon/wppost/wppost.php', 'wppost_jot_nets');
    unregister_hook('connector_settings',      'addon/wppost/wppost.php', 'wppost_settings');
    unregister_hook('connector_settings_post', 'addon/wppost/wppost.php', 'wppost_settings_post');

	// obsolete - remove
    unregister_hook('post_local_end',   'addon/wppost/wppost.php', 'wppost_send');
    unregister_hook('plugin_settings',  'addon/wppost/wppost.php', 'wppost_settings');
    unregister_hook('plugin_settings_post',  'addon/wppost/wppost.php', 'wppost_settings_post');

}


function wppost_jot_nets(&$a,&$b) {
    if(! local_user())
        return;

    $wp_post = get_pconfig(local_user(),'wppost','post');
    if(intval($wp_post) == 1) {
        $wp_defpost = get_pconfig(local_user(),'wppost','post_by_default');
        $selected = ((intval($wp_defpost) == 1) ? ' checked="checked" ' : '');
        $b .= '<div class="profile-jot-net"><input type="checkbox" name="wppost_enable" ' . $selected . ' value="1" /> '
            . t('Post to Wordpress') . '</div>';
    }
}


function wppost_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/wppost/wppost.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variables */

	$enabled = get_pconfig(local_user(),'wppost','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');

	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = get_pconfig(local_user(),'wppost','post_by_default');
	$back_enabled = get_pconfig(local_user(),'wppost','backlink');
	$shortcheck_enabled = get_pconfig(local_user(),'wppost','shortcheck');

	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');
	$back_checked = (($back_enabled) ? ' checked="checked" ' : '');
	$shortcheck_checked = (($shortcheck_enabled) ? ' checked="checked" ' : '');

	$wp_username = get_pconfig(local_user(), 'wppost', 'wp_username');
	$wp_password = get_pconfig(local_user(), 'wppost', 'wp_password');
	$wp_blog = get_pconfig(local_user(), 'wppost', 'wp_blog');
	$wp_backlink_text = get_pconfig(local_user(), 'wppost', 'wp_backlink_text');


    /* Add some HTML to the existing form */

    $s .= '<span id="settings_wppost_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_wppost_expanded\'); openClose(\'settings_wppost_inflated\');">';
    $s .= '<img class="connector'.$css.'" src="images/wordpress.png" /><h3 class="connector">'. t('Wordpress Export').'</h3>';
    $s .= '</span>';
    $s .= '<div id="settings_wppost_expanded" class="settings-block" style="display: none;">';
    $s .= '<span class="fakelink" onclick="openClose(\'settings_wppost_expanded\'); openClose(\'settings_wppost_inflated\');">';
    $s .= '<img class="connector'.$css.'" src="images/wordpress.png" /><h3 class="connector">'. t('Wordpress Export').'</h3>';
    $s .= '</span>';
    $s .= '<div id="wppost-enable-wrapper">';
    $s .= '<label id="wppost-enable-label" for="wppost-checkbox">' . t('Enable WordPress Post Plugin') . '</label>';
    $s .= '<input id="wppost-checkbox" type="checkbox" name="wppost" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-username-wrapper">';
    $s .= '<label id="wppost-username-label" for="wppost-username">' . t('WordPress username') . '</label>';
    $s .= '<input id="wppost-username" type="text" name="wp_username" value="' . $wp_username . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-password-wrapper">';
    $s .= '<label id="wppost-password-label" for="wppost-password">' . t('WordPress password') . '</label>';
    $s .= '<input id="wppost-password" type="password" name="wp_password" value="' . $wp_password . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-blog-wrapper">';
    $s .= '<label id="wppost-blog-label" for="wppost-blog">' . t('WordPress API URL') . '</label>';
    $s .= '<input id="wppost-blog" type="text" name="wp_blog" value="' . $wp_blog . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-bydefault-wrapper">';
    $s .= '<label id="wppost-bydefault-label" for="wppost-bydefault">' . t('Post to WordPress by default') . '</label>';
    $s .= '<input id="wppost-bydefault" type="checkbox" name="wp_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-backlink-wrapper">';
    $s .= '<label id="wppost-backlink-label" for="wppost-backlink">' . t('Provide a backlink to the Friendica post') . '</label>';
    $s .= '<input id="wppost-backlink" type="checkbox" name="wp_backlink" value="1" ' . $back_checked . '/>';
    $s .= '</div><div class="clear"></div>';
    $s .= '<div id="wppost-backlinktext-wrapper">';
    $s .= '<label id="wppost-backlinktext-label" for="wp_backlink_text">' . t('Text for the backlink, e.g. Read the original post and comment stream on Friendica.') . '</label>';
    $s .= '<input id="wppost-backlinktext" type="text" name="wp_backlink_text" value="'. $wp_backlink_text.'" ' . $wp_backlink_text . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="wppost-shortcheck-wrapper">';
    $s .= '<label id="wppost-shortcheck-label" for="wppost-shortcheck">' . t("Don't post messages that are too short") . '</label>';
    $s .= '<input id="wppost-shortcheck" type="checkbox" name="wp_shortcheck" value="1" '.$shortcheck_checked.'/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="wppost-submit" name="wppost-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

}


function wppost_settings_post(&$a,&$b) {

	if(x($_POST,'wppost-submit')) {

		set_pconfig(local_user(),'wppost','post',intval($_POST['wppost']));
		set_pconfig(local_user(),'wppost','post_by_default',intval($_POST['wp_bydefault']));
		set_pconfig(local_user(),'wppost','wp_username',trim($_POST['wp_username']));
		set_pconfig(local_user(),'wppost','wp_password',trim($_POST['wp_password']));
		set_pconfig(local_user(),'wppost','wp_blog',trim($_POST['wp_blog']));
		set_pconfig(local_user(),'wppost','backlink',trim($_POST['wp_backlink']));
		set_pconfig(local_user(),'wppost','shortcheck',trim($_POST['wp_shortcheck']));
		$wp_backlink_text = notags(trim($_POST['wp_backlink_text']));
		$wp_backlink_text = bbcode($wp_backlink_text, false, false, 8);
		$wp_backlink_text = html2plain($wp_backlink_text, 0, true);
		set_pconfig(local_user(),'wppost','wp_backlink_text', $wp_backlink_text);

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

	$wp_post   = intval(get_pconfig(local_user(),'wppost','post'));

	$wp_enable = (($wp_post && x($_REQUEST,'wppost_enable')) ? intval($_REQUEST['wppost_enable']) : 0);

	if ($b['api_source'] && intval(get_pconfig(local_user(),'wppost','post_by_default'))) {
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




function wppost_send(&$a,&$b) {

	if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
		return;

	if(! strstr($b['postopts'],'wppost'))
		return;

	if($b['parent'] != $b['id'])
		return;


	$wp_username = xmlify(get_pconfig($b['uid'],'wppost','wp_username'));
	$wp_password = xmlify(get_pconfig($b['uid'],'wppost','wp_password'));
	$wp_blog = get_pconfig($b['uid'],'wppost','wp_blog');
	$wp_backlink_text = get_pconfig($b['uid'],'wppost','wp_backlink_text');
	if ($wp_backlink_text == '') {
		$wp_backlink_text = t('Read the orig­i­nal post and com­ment stream on Friendica');
	}

	if($wp_username && $wp_password && $wp_blog) {

		require_once('include/bbcode.php');
		require_once('include/html2plain.php');
		require_once('include/plaintext.php');

		$wptitle = trim($b['title']);

		if (intval(get_pconfig($b['uid'],'wppost','shortcheck'))) {
			// Checking, if its a post that is worth a blog post
			$postentry = false;
			$siteinfo = get_attached_data($b["body"]);

			// Is it a link to an aricle, a video or a photo?
			if (isset($siteinfo["type"])) {
				if (in_array($siteinfo["type"], array("link", "audio", "video", "photo"))) {
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
			$siteinfo = get_attached_data($b["body"]);
			if (isset($siteinfo["title"])) {
				$wptitle = $siteinfo["title"];
			}

			// If no bookmark is found then take the first line
			if ($wptitle == '') {
				// Remove the share element before fetching the first line
				$title = trim(preg_replace("/\[share.*?\](.*?)\[\/share\]/ism","\n$1\n",$b['body']));

				$title = html2plain(bbcode($title, false, false), 0, true)."\n";
				$pos = strpos($title, "\n");
				$trailer = "";
				if (($pos == 0) || ($pos > 100)) {
					$pos = 100;
					$trailer = "...";
				}

				$wptitle = substr($title, 0, $pos).$trailer;
			}
		}

		$title = '<title>' . (($wptitle) ? $wptitle : t('Post from Friendica')) . '</title>';
		$post = bbcode($b['body'], false, false, 4);

		// If a link goes to youtube then remove the stuff around it. Wordpress detects youtube links and embeds it
		$post = preg_replace('/<a.*?href="(https?:\/\/www.youtube.com\/.*?)".*?>(.*?)<\/a>/ism',"\n$1\n",$post);
		$post = preg_replace('/<a.*?href="(https?:\/\/youtu.be\/.*?)".*?>(.*?)<\/a>/ism',"\n$1\n",$post);

		$post = $title.$post;

		$wp_backlink = intval(get_pconfig($b['uid'],'wppost','backlink'));
		if($wp_backlink && $b['plink']) {
			$post .= EOL . EOL . '<a href="' . $b['plink'] . '">'
				. $wp_backlink_text . '</a>' . EOL . EOL;
		}

		$post = xmlify($post);


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

		logger('wppost: data: ' . $xml, LOGGER_DATA);

		if($wp_blog !== 'test') {
			$x = post_url($wp_blog,$xml);
		}
		logger('posted to wordpress: ' . (($x) ? $x : ''), LOGGER_DEBUG);

	}
}
