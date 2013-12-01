<?php

/**
 * Name: Posterous Post Connector
 * Description: Post to Posterous accounts
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Tony Baldwin <https://free-haven.org/u/tony>
 */

function posterous_install() {
    register_hook('post_local',           'addon/posterous/posterous.php', 'posterous_post_local');
    register_hook('notifier_normal',      'addon/posterous/posterous.php', 'posterous_send');
    register_hook('jot_networks',         'addon/posterous/posterous.php', 'posterous_jot_nets');
    register_hook('connector_settings',      'addon/posterous/posterous.php', 'posterous_settings');
    register_hook('connector_settings_post', 'addon/posterous/posterous.php', 'posterous_settings_post');

}
function posterous_uninstall() {
    unregister_hook('post_local',       'addon/posterous/posterous.php', 'posterous_post_local');
    unregister_hook('notifier_normal',  'addon/posterous/posterous.php', 'posterous_send');
    unregister_hook('jot_networks',     'addon/posterous/posterous.php', 'posterous_jot_nets');
    unregister_hook('connector_settings',      'addon/posterous/posterous.php', 'posterous_settings');
    unregister_hook('connector_settings_post', 'addon/posterous/posterous.php', 'posterous_settings_post');
}


function posterous_jot_nets(&$a,&$b) {
    if(! local_user())
        return;

    $pstr_post = get_pconfig(local_user(),'posterous','post');
    if(intval($pstr_post) == 1) {
        $pstr_defpost = get_pconfig(local_user(),'posterous','post_by_default');
        $selected = ((intval($pstr_defpost) == 1) ? ' checked="checked" ' : '');
        $b .= '<div class="profile-jot-net"><input type="checkbox" name="posterous_enable"' . $selected . ' value="1" /> '
            . t('Post to Posterous') . '</div>';
    }
}


function posterous_settings(&$a,&$s) {

    if(! local_user())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/posterous/posterous.css' . '" media="all" />' . "\r\n";

    /* Get the current state of our config variables */

    $enabled = get_pconfig(local_user(),'posterous','post');

    $checked = (($enabled) ? ' checked="checked" ' : '');

    $def_enabled = get_pconfig(local_user(),'posterous','post_by_default');

    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$pstr_username = get_pconfig(local_user(), 'posterous', 'posterous_username');
	$pstr_password = get_pconfig(local_user(), 'posterous', 'posterous_password');
	$pstr_site_id = get_pconfig(local_user(), 'posterous', 'posterous_site_id');
	$pstr_api_token = get_pconfig(local_user(), 'posterous', 'posterous_api_token');


    /* Add some HTML to the existing form */

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('Posterous Post Settings') . '</h3>';
    $s .= '<div id="posterous-enable-wrapper">';
    $s .= '<label id="posterous-enable-label" for="posterous-checkbox">' . t('Enable Posterous Post Plugin') . '</label>';
    $s .= '<input id="posterous-checkbox" type="checkbox" name="posterous" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="posterous-username-wrapper">';
    $s .= '<label id="posterous-username-label" for="posterous-username">' . t('Posterous login') . '</label>';
    $s .= '<input id="posterous-username" type="text" name="posterous_username" value="' . $pstr_username . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="posterous-password-wrapper">';
    $s .= '<label id="posterous-password-label" for="posterous-password">' . t('Posterous password') . '</label>';
    $s .= '<input id="posterous-password" type="password" name="posterous_password" value="' . $pstr_password . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="posterous-site_id-wrapper">';
    $s .= '<label id="posterous-site_id-label" for="posterous-site_id">' . t('Posterous site ID') . '</label>';
    $s .= '<input id="posterous-site_id" type="text" name="posterous_site_id" value="' . $pstr_site_id . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="posterous-api_token-wrapper">';
    $s .= '<label id="posterous-api_token-label" for="posterous-api_token">' . t('Posterous API token') . '</label>';
    $s .= '<input id="posterous-api_token" type="text" name="posterous_api_token" value="' . $pstr_api_token . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="posterous-bydefault-wrapper">';
    $s .= '<label id="posterous-bydefault-label" for="posterous-bydefault">' . t('Post to Posterous by default') . '</label>';
    $s .= '<input id="posterous-bydefault" type="checkbox" name="posterous_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="posterous-submit" name="posterous-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

}


function posterous_settings_post(&$a,&$b) {

	if(x($_POST,'posterous-submit')) {

		set_pconfig(local_user(),'posterous','post',intval($_POST['posterous']));
		set_pconfig(local_user(),'posterous','post_by_default',intval($_POST['posterous_bydefault']));
		set_pconfig(local_user(),'posterous','posterous_username',trim($_POST['posterous_username']));
		set_pconfig(local_user(),'posterous','posterous_password',trim($_POST['posterous_password']));
		set_pconfig(local_user(),'posterous','posterous_site_id',trim($_POST['posterous_site_id']));
		set_pconfig(local_user(),'posterous','posterous_api_token',trim($_POST['posterous_api_token']));

	}

}

function posterous_post_local(&$a,&$b) {

	// This can probably be changed to allow editing by pointing to a different API endpoint

	if($b['edit'])
		return;

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	if($b['private'] || $b['parent'])
		return;

    $pstr_post   = intval(get_pconfig(local_user(),'posterous','post'));

	$pstr_enable = (($pstr_post && x($_REQUEST,'posterous_enable')) ? intval($_REQUEST['posterous_enable']) : 0);

	if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'posterous','post_by_default')))
		$pstr_enable = 1;

    if(! $pstr_enable)
       return;

    if(strlen($b['postopts']))
       $b['postopts'] .= ',';
     $b['postopts'] .= 'posterous';
}




function posterous_send(&$a,&$b) {

    if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
        return;

    if(! strstr($b['postopts'],'posterous'))
        return;

    if($b['parent'] != $b['id'])
        return;


	$pstr_username = get_pconfig($b['uid'],'posterous','posterous_username');
	$pstr_password = get_pconfig($b['uid'],'posterous','posterous_password');
	$pstr_site_id = get_pconfig($b['uid'],'posterous','posterous_site_id');
	$pstr_blog = "http://posterous.com/api/2/sites/$pstr_site_id/posts";
	$pstr_api_token = get_pconfig($b['uid'],'posterous','posterous_api_token');

	if($pstr_username && $pstr_password && $pstr_blog) {

		require_once('include/bbcode.php');
		$tag_arr = array();
		$tags = '';
		$x = preg_match_all('/\#\[(.*?)\](.*?)\[/',$b['tag'],$matches,PREG_SET_ORDER);

		if($x) {
			foreach($matches as $mtch) {
				$tag_arr[] = $mtch[2];
			}
		}
		if(count($tag_arr))
			$tags = implode(',',$tag_arr);		


		$params = array(
			'post[title]' => (($b['title']) ? $b['title'] : t('Post from Friendica')),
			'post[source]' => 'Friendica',
			'post[tags]' => $tags,
			'post[body]' => bbcode($b['body']),
			'api_token' => $pstr_api_token,
			'site_id' => $pstr_site_id
		);
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $pstr_blog);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $pstr_username . ':' . $pstr_password);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

		$data = curl_exec($ch);
		$result = curl_multi_getcontent($ch);
		curl_close($ch);

		logger('posterous_send: ' . $result);
	}
}

