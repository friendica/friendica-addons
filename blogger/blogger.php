<?php

/**
 * Name: Blogger Post Connector
 * Description: Post to Blogger (or anything else which uses blogger XMLRPC API)
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */

function blpost_install() {
    register_hook('post_local',           'addon/blpost/blpost.php', 'blpost_post_local');
    register_hook('notifier_normal',      'addon/blpost/blpost.php', 'blpost_send');
    register_hook('jot_networks',         'addon/blpost/blpost.php', 'blpost_jot_nets');
    register_hook('connector_settings',      'addon/blpost/blpost.php', 'blpost_settings');
    register_hook('connector_settings_post', 'addon/blpost/blpost.php', 'blpost_settings_post');

}
function blpost_uninstall() {
    unregister_hook('post_local',       'addon/blpost/blpost.php', 'blpost_post_local');
    unregister_hook('notifier_normal',  'addon/blpost/blpost.php', 'blpost_send');
    unregister_hook('jot_networks',     'addon/blpost/blpost.php', 'blpost_jot_nets');
    unregister_hook('connector_settings',      'addon/blpost/blpost.php', 'blpost_settings');
    unregister_hook('connector_settings_post', 'addon/blpost/blpost.php', 'blpost_settings_post');

	// obsolete - remove
    unregister_hook('post_local_end',   'addon/blpost/blpost.php', 'blpost_send');
    unregister_hook('plugin_settings',  'addon/blpost/blpost.php', 'blpost_settings');
    unregister_hook('plugin_settings_post',  'addon/blpost/blpost.php', 'blpost_settings_post');

}


function blpost_jot_nets(&$a,&$b) {
    if(! local_user())
        return;

    $bl_post = get_pconfig(local_user(),'blpost','post');
    if(intval($bl_post) == 1) {
        $bl_defpost = get_pconfig(local_user(),'blpost','post_by_default');
        $selected = ((intval($bl_defpost) == 1) ? ' checked="checked" ' : '');
        $b .= '<div class="profile-jot-net"><input type="checkbox" name="blpost_enable" ' . $selected . ' value="1" /> '
            . t('Post to blogger') . '</div>';
    }
}


function blpost_settings(&$a,&$s) {

    if(! local_user())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/blpost/blpost.css' . '" media="all" />' . "\r\n";

    /* Get the current state of our config variables */

    $enabled = get_pconfig(local_user(),'blpost','post');

    $checked = (($enabled) ? ' checked="checked" ' : '');

    $def_enabled = get_pconfig(local_user(),'blpost','post_by_default');

    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$bl_username = get_pconfig(local_user(), 'blpost', 'bl_username');
	$bl_password = get_pconfig(local_user(), 'blpost', 'bl_password');
	$bl_blog = get_pconfig(local_user(), 'blpost', 'bl_blog');


    /* Add some HTML to the existing form */

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('Blogger Post Settings') . '</h3>';
    $s .= '<div id="blpost-enable-wrapper">';
    $s .= '<label id="blpost-enable-label" for="blpost-checkbox">' . t('Enable Blogger Post Plugin') . '</label>';
    $s .= '<input id="blpost-checkbox" type="checkbox" name="blpost" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="blpost-username-wrapper">';
    $s .= '<label id="blpost-username-label" for="blpost-username">' . t('Blogger username') . '</label>';
    $s .= '<input id="blpost-username" type="text" name="bl_username" value="' . $bl_username . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="blpost-password-wrapper">';
    $s .= '<label id="blpost-password-label" for="blpost-password">' . t('Blogger password') . '</label>';
    $s .= '<input id="blpost-password" type="password" name="bl_password" value="' . $bl_password . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="blpost-blog-wrapper">';
    $s .= '<label id="blpost-blog-label" for="blpost-blog">' . t('Blogger API URL') . '</label>';
    $s .= '<input id="blpost-blog" type="text" name="bl_blog" value="' . $bl_blog . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="blpost-bydefault-wrapper">';
    $s .= '<label id="blpost-bydefault-label" for="blpost-bydefault">' . t('Post to Blogger by default') . '</label>';
    $s .= '<input id="blpost-bydefault" type="checkbox" name="bl_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="blpost-submit" name="blpost-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}


function blpost_settings_post(&$a,&$b) {

	if(x($_POST,'blpost-submit')) {

		set_pconfig(local_user(),'blpost','post',intval($_POST['blpost']));
		set_pconfig(local_user(),'blpost','post_by_default',intval($_POST['bl_bydefault']));
		set_pconfig(local_user(),'blpost','bl_username',trim($_POST['bl_username']));
		set_pconfig(local_user(),'blpost','bl_password',trim($_POST['bl_password']));
		set_pconfig(local_user(),'blpost','bl_blog',trim($_POST['bl_blog']));

	}

}

function blpost_post_local(&$a,&$b) {

	// This can probably be changed to allow editing by pointing to a different API endpoint

	if($b['edit'])
		return;

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	if($b['private'] || $b['parent'])
		return;

    $bl_post   = intval(get_pconfig(local_user(),'blpost','post'));

	$bl_enable = (($bl_post && x($_REQUEST,'blpost_enable')) ? intval($_REQUEST['blpost_enable']) : 0);

	if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'blpost','post_by_default')))
		$bl_enable = 1;

    if(! $bl_enable)
       return;

    if(strlen($b['postopts']))
       $b['postopts'] .= ',';
     $b['postopts'] .= 'blpost';
}




function blpost_send(&$a,&$b) {

    if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
        return;

    if(! strstr($b['postopts'],'blpost'))
        return;

    if($b['parent'] != $b['id'])
        return;


	$bl_username = xmlify(get_pconfig($b['uid'],'blpost','bl_username'));
	$bl_password = xmlify(get_pconfig($b['uid'],'blpost','bl_password'));
	$bl_blog = get_pconfig($b['uid'],'blpost','bl_blog');

	if($bl_username && $bl_password && $bl_blog) {

		require_once('include/bbcode.php');

		$title = '<title>' . (($b['title']) ? $b['title'] : t('Post from Friendica')) . '</title>';
		$post = $title . bbcode($b['body']);
		$post = xmlify($post);

		$xml = <<< EOT
<?xml version=\"1.0\" encoding=\"utf-8\"?>
<methodCall>
  <methodName>blogger.newPost</methodName>
  <params>
    <param><value><string/></value></param>
    <param><value><string/></value></param>
    <param><value><string>$bl_username</string></value></param>
    <param><value><string>$bl_password</string></value></param>
    <param><value><string>$post</string></value></param>
    <param><value><int>1</int></value></param>
  </params>
</methodCall>

EOT;

		logger('blpost: data: ' . $xml, LOGGER_DATA);

		if($bl_blog !== 'test')
			$x = post_url($bl_blog,$xml);
		logger('posted to blogger: ' . (($x) ? $x : ''), LOGGER_DEBUG);

	}
}

