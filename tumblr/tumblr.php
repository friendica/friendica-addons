<?php

/**
 * Name: Tumblr Post Connector
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 */

function tumblr_install() {
    register_hook('post_local',           'addon/tumblr/tumblr.php', 'tumblr_post_local');
    register_hook('notifier_normal',      'addon/tumblr/tumblr.php', 'tumblr_send');
    register_hook('jot_networks',         'addon/tumblr/tumblr.php', 'tumblr_jot_nets');
    register_hook('connector_settings',      'addon/tumblr/tumblr.php', 'tumblr_settings');
    register_hook('connector_settings_post', 'addon/tumblr/tumblr.php', 'tumblr_settings_post');

}
function tumblr_uninstall() {
    unregister_hook('post_local',       'addon/tumblr/tumblr.php', 'tumblr_post_local');
    unregister_hook('notifier_normal',  'addon/tumblr/tumblr.php', 'tumblr_send');
    unregister_hook('jot_networks',     'addon/tumblr/tumblr.php', 'tumblr_jot_nets');
    unregister_hook('connector_settings',      'addon/tumblr/tumblr.php', 'tumblr_settings');
    unregister_hook('connector_settings_post', 'addon/tumblr/tumblr.php', 'tumblr_settings_post');
}


function tumblr_jot_nets(&$a,&$b) {
    if(! local_user())
        return;

    $tmbl_post = get_pconfig(local_user(),'tumblr','post');
    if(intval($tmbl_post) == 1) {
        $tmbl_defpost = get_pconfig(local_user(),'tumblr','post_by_default');
        $selected = ((intval($tmbl_defpost) == 1) ? ' checked="checked" ' : '');
        $b .= '<div class="profile-jot-net"><input type="checkbox" name="tumblr_enable"' . $selected . 'value="1" /> '
            . t('Post to Tumblr') . '</div>';
    }
}


function tumblr_settings(&$a,&$s) {

    if(! local_user())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/tumblr/tumblr.css' . '" media="all" />' . "\r\n";

    /* Get the current state of our config variables */

    $enabled = get_pconfig(local_user(),'tumblr','post');

    $checked = (($enabled) ? ' checked="checked" ' : '');

    $def_enabled = get_pconfig(local_user(),'tumblr','post_by_default');

    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$tmbl_username = get_pconfig(local_user(), 'tumblr', 'tumblr_username');
	$tmbl_password = get_pconfig(local_user(), 'tumblr', 'tumblr_password');


    /* Add some HTML to the existing form */

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('Tumblr Post Settings') . '</h3>';
    $s .= '<div id="tumblr-enable-wrapper">';
    $s .= '<label id="tumblr-enable-label" for="tumblr-checkbox">' . t('Enable Tumblr Post Plugin') . '</label>';
    $s .= '<input id="tumblr-checkbox" type="checkbox" name="tumblr" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="tumblr-username-wrapper">';
    $s .= '<label id="tumblr-username-label" for="tumblr-username">' . t('Tumblr login') . '</label>';
    $s .= '<input id="tumblr-username" type="text" name="tumblr_username" value="' . $tmbl_username . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="tumblr-password-wrapper">';
    $s .= '<label id="tumblr-password-label" for="tumblr-password">' . t('Tumblr password') . '</label>';
    $s .= '<input id="tumblr-password" type="password" name="tumblr_password" value="' . $tmbl_password . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="tumblr-bydefault-wrapper">';
    $s .= '<label id="tumblr-bydefault-label" for="tumblr-bydefault">' . t('Post to Tumblr by default') . '</label>';
    $s .= '<input id="tumblr-bydefault" type="checkbox" name="tumblr_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="tumblr-submit" name="tumblr-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}


function tumblr_settings_post(&$a,&$b) {

	if(x($_POST,'tumblr-submit')) {

		set_pconfig(local_user(),'tumblr','post',intval($_POST['tumblr']));
		set_pconfig(local_user(),'tumblr','post_by_default',intval($_POST['tumblr_bydefault']));
		set_pconfig(local_user(),'tumblr','tumblr_username',trim($_POST['tumblr_username']));
		set_pconfig(local_user(),'tumblr','tumblr_password',trim($_POST['tumblr_password']));

	}

}

function tumblr_post_local(&$a,&$b) {

	// This can probably be changed to allow editing by pointing to a different API endpoint

	if($b['edit'])
		return;

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	if($b['private'] || $b['parent'])
		return;

    $tmbl_post   = intval(get_pconfig(local_user(),'tumblr','post'));

	$tmbl_enable = (($tmbl_post && x($_REQUEST,'tumblr_enable')) ? intval($_REQUEST['tumblr_enable']) : 0);

	if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'tumblr','post_by_default')))
		$tmbl_enable = 1;

    if(! $tmbl_enable)
       return;

    if(strlen($b['postopts']))
       $b['postopts'] .= ',';
     $b['postopts'] .= 'tumblr';
}




function tumblr_send(&$a,&$b) {

    if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
        return;

    if(! strstr($b['postopts'],'tumblr'))
        return;

    if($b['parent'] != $b['id'])
        return;


	$tmbl_username = get_pconfig($b['uid'],'tumblr','tumblr_username');
	$tmbl_password = get_pconfig($b['uid'],'tumblr','tumblr_password');
	$tmbl_blog = 'http://www.tumblr.com/api/write';

	if($tmbl_username && $tmbl_password && $tmbl_blog) {

		require_once('include/bbcode.php');

		$params = array(
			'email' => $tmbl_username,
			'password' => $tmbl_password,
			'title' => (($b['title']) ? $b['title'] : t('Post from Friendica')),
			'type' => 'regular',
			'format' => 'html',
			'generator' => 'Friendica',
			'body' => bbcode($b['body'])
		);

		$x = post_url($tmbl_blog,$params);
		$ret_code = $a->get_curl_code();
		if($ret_code == 201)
			logger('tumblr_send: success');
		elseif($ret_code == 403)
			logger('tumblr_send: authentication failure');
		else
			logger('tumblr_send: general error: ' . print_r($x,true)); 

	}
}

