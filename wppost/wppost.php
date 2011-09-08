<?php

/**
 * Name: WordPress Post Connector
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */

function wppost_install() {
    register_hook('post_local_end',   'addon/wppost/wppost.php', 'wppost_send');
    register_hook('jot_networks',     'addon/wppost/wppost.php', 'wppost_jot_nets');
    register_hook('plugin_settings',  'addon/wppost/wppost.php', 'wppost_settings');
    register_hook('plugin_settings_post',  'addon/wppost/wppost.php', 'wppost_settings_post');

}
function wppost_uninstall() {
    unregister_hook('post_local_end',   'addon/wppost/wppost.php', 'wppost_send');
    unregister_hook('jot_networks',     'addon/wppost/wppost.php', 'wppost_jot_nets');
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
        $b .= '<div class="profile-jot-net"><input type="checkbox" name="wppost_enable"' . $selected . 'value="1" /> '
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

    $def_enabled = get_pconfig(local_user(),'wppost','post_by_default');

    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$wp_username = get_pconfig(local_user(), 'wppost', 'wp_username');
	$wp_password = get_pconfig(local_user(), 'wppost', 'wp_password');
	$wp_blog = get_pconfig(local_user(), 'wppost', 'wp_blog');


    /* Add some HTML to the existing form */

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('WordPress Post Settings') . '</h3>';
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

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="wppost-submit" name="wppost-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}


function wppost_settings_post(&$a,&$b) {

	if(x($_POST,'wppost-submit')) {

		set_pconfig(local_user(),'wppost','post',intval($_POST['wppost']));
		set_pconfig(local_user(),'wppost','post_by_default',intval($_POST['wp_bydefault']));
		set_pconfig(local_user(),'wppost','wp_username',trim($_POST['wp_username']));
		set_pconfig(local_user(),'wppost','wp_password',trim($_POST['wp_password']));
		set_pconfig(local_user(),'wppost','wp_blog',trim($_POST['wp_blog']));

	}

}




function wppost_send(&$a,&$b) {

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	if($b['prvnets'] && $b['private'])
		return;

	if($b['parent'])
		return;

	$wp_username = get_pconfig(local_user(),'wppost','wp_username');
	$wp_password = get_pconfig(local_user(),'wppost','wp_password');
	$wp_blog = get_pconfig(local_user(),'wppost','wp_blog');
    $wp_post   = intval(get_pconfig(local_user(),'wppost','post'));

	$wp_enable = (($wp_post && x($_POST,'wppost_enable')) ? intval($_POST['wppost_enable']) : 0);

	if($_POST['api_source'] && intval(get_pconfig(local_user(),'wppost','post_by_default')))
		$wp_enable = 1;

	if($wp_username && $wp_password && $wp_blog && $wp_post && $wp_enable) {

		require_once('include/bbcode.php');

		$post = (($b['title']) ? '<title>' . $b['title'] . '</title>' : '');
		$post .= bbcode($b['body']);
			$post = xmlify($post);

		$xml = <<< EOT

<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
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

		if($wp_blog !== 'test')
			$x = post_url($wp_blog,$xml);
		logger('posted to wordpress');

	}
}

