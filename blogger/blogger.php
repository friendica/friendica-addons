<?php

/**
 * Name: Blogger Post Connector
 * Description: Post to Blogger (or anything else which uses blogger XMLRPC API)
 * Version: 1.0
 * 
 */

function blogger_install() {
    register_hook('post_local',           'addon/blogger/blogger.php', 'blogger_post_local');
    register_hook('notifier_normal',      'addon/blogger/blogger.php', 'blogger_send');
    register_hook('jot_networks',         'addon/blogger/blogger.php', 'blogger_jot_nets');
    register_hook('connector_settings',      'addon/blogger/blogger.php', 'blogger_settings');
    register_hook('connector_settings_post', 'addon/blogger/blogger.php', 'blogger_settings_post');

}
function blogger_uninstall() {
    unregister_hook('post_local',       'addon/blogger/blogger.php', 'blogger_post_local');
    unregister_hook('notifier_normal',  'addon/blogger/blogger.php', 'blogger_send');
    unregister_hook('jot_networks',     'addon/blogger/blogger.php', 'blogger_jot_nets');
    unregister_hook('connector_settings',      'addon/blogger/blogger.php', 'blogger_settings');
    unregister_hook('connector_settings_post', 'addon/blogger/blogger.php', 'blogger_settings_post');

	// obsolete - remove
    unregister_hook('post_local_end',   'addon/blogger/blogger.php', 'blogger_send');
    unregister_hook('plugin_settings',  'addon/blogger/blogger.php', 'blogger_settings');
    unregister_hook('plugin_settings_post',  'addon/blogger/blogger.php', 'blogger_settings_post');

}


function blogger_jot_nets(&$a,&$b) {
    if(! local_user())
        return;

    $bl_post = get_pconfig(local_user(),'blogger','post');
    if(intval($bl_post) == 1) {
        $bl_defpost = get_pconfig(local_user(),'blogger','post_by_default');
        $selected = ((intval($bl_defpost) == 1) ? ' checked="checked" ' : '');
        $b .= '<div class="profile-jot-net"><input type="checkbox" name="blogger_enable" ' . $selected . ' value="1" /> '
            . t('Post to blogger') . '</div>';
    }
}


function blogger_settings(&$a,&$s) {

    if(! local_user())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/blogger/blogger.css' . '" media="all" />' . "\r\n";

    /* Get the current state of our config variables */

    $enabled = get_pconfig(local_user(),'blogger','post');

    $checked = (($enabled) ? ' checked="checked" ' : '');

    $def_enabled = get_pconfig(local_user(),'blogger','post_by_default');

    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$bl_username = get_pconfig(local_user(), 'blogger', 'bl_username');
	$bl_password = get_pconfig(local_user(), 'blogger', 'bl_password');
	$bl_blog = get_pconfig(local_user(), 'blogger', 'bl_blog');


    /* Add some HTML to the existing form */

    $s .= '<span id="settings_blogger_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_blogger_expanded\'); openClose(\'settings_blogger_inflated\');">';
    $s .= '<h3>' . t('Blogger Post Settings') . '</h3>';
    $s .= '</span>';
    $s .= '<div id="settings_blogger_expanded" class="settings-block" style="display: none;">';
    $s .= '<span class="fakelink" onclick="openClose(\'settings_blogger_expanded\'); openClose(\'settings_blogger_inflated\');">';
    $s .= '<h3>' . t('Blogger Post Settings') . '</h3>';
    $s .= '</span>';

    $s .= '<div id="blogger-enable-wrapper">';
    $s .= '<label id="blogger-enable-label" for="blogger-checkbox">' . t('Enable Blogger Post Plugin') . '</label>';
    $s .= '<input id="blogger-checkbox" type="checkbox" name="blogger" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="blogger-username-wrapper">';
    $s .= '<label id="blogger-username-label" for="blogger-username">' . t('Blogger username') . '</label>';
    $s .= '<input id="blogger-username" type="text" name="bl_username" value="' . $bl_username . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="blogger-password-wrapper">';
    $s .= '<label id="blogger-password-label" for="blogger-password">' . t('Blogger password') . '</label>';
    $s .= '<input id="blogger-password" type="password" name="bl_password" value="' . $bl_password . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="blogger-blog-wrapper">';
    $s .= '<label id="blogger-blog-label" for="blogger-blog">' . t('Blogger API URL') . '</label>';
    $s .= '<input id="blogger-blog" type="text" name="bl_blog" value="' . $bl_blog . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="blogger-bydefault-wrapper">';
    $s .= '<label id="blogger-bydefault-label" for="blogger-bydefault">' . t('Post to Blogger by default') . '</label>';
    $s .= '<input id="blogger-bydefault" type="checkbox" name="bl_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="blogger-submit" name="blogger-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}


function blogger_settings_post(&$a,&$b) {

	if(x($_POST,'blogger-submit')) {

		set_pconfig(local_user(),'blogger','post',intval($_POST['blogger']));
		set_pconfig(local_user(),'blogger','post_by_default',intval($_POST['bl_bydefault']));
		set_pconfig(local_user(),'blogger','bl_username',trim($_POST['bl_username']));
		set_pconfig(local_user(),'blogger','bl_password',trim($_POST['bl_password']));
		set_pconfig(local_user(),'blogger','bl_blog',trim($_POST['bl_blog']));

	}

}

function blogger_post_local(&$a,&$b) {

	// This can probably be changed to allow editing by pointing to a different API endpoint

	if($b['edit'])
		return;

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	if($b['private'] || $b['parent'])
		return;

    $bl_post   = intval(get_pconfig(local_user(),'blogger','post'));

	$bl_enable = (($bl_post && x($_REQUEST,'blogger_enable')) ? intval($_REQUEST['blogger_enable']) : 0);

	if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'blogger','post_by_default')))
		$bl_enable = 1;

    if(! $bl_enable)
       return;

    if(strlen($b['postopts']))
       $b['postopts'] .= ',';
     $b['postopts'] .= 'blogger';
}




function blogger_send(&$a,&$b) {

    if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
        return;

    if(! strstr($b['postopts'],'blogger'))
        return;

    if($b['parent'] != $b['id'])
        return;


	$bl_username = xmlify(get_pconfig($b['uid'],'blogger','bl_username'));
	$bl_password = xmlify(get_pconfig($b['uid'],'blogger','bl_password'));
	$bl_blog = get_pconfig($b['uid'],'blogger','bl_blog');

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

		logger('blogger: data: ' . $xml, LOGGER_DATA);

		if($bl_blog !== 'test')
			$x = post_url($bl_blog,$xml);
		logger('posted to blogger: ' . (($x) ? $x : ''), LOGGER_DEBUG);

	}
}

