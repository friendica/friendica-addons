<?php

/**
 * Name: Dreamwidth Post Connector
 * Description: Post to dreamwidth
 * Version: 1.0
 * Author: Tony Baldwin <https://free-haven.org/profile/tony>
 * Author: Michael Johnston
 */

function dwpost_install() {
    register_hook('post_local',           'addon/dwpost/dwpost.php', 'dwpost_post_local');
    register_hook('notifier_normal',      'addon/dwpost/dwpost.php', 'dwpost_send');
    register_hook('jot_networks',         'addon/dwpost/dwpost.php', 'dwpost_jot_nets');
    register_hook('connector_settings',      'addon/dwpost/dwpost.php', 'dwpost_settings');
    register_hook('connector_settings_post', 'addon/dwpost/dwpost.php', 'dwpost_settings_post');

}
function dwpost_uninstall() {
    unregister_hook('post_local',       'addon/dwpost/dwpost.php', 'dwpost_post_local');
    unregister_hook('notifier_normal',  'addon/dwpost/dwpost.php', 'dwpost_send');
    unregister_hook('jot_networks',     'addon/dwpost/dwpost.php', 'dwpost_jot_nets');
    unregister_hook('connector_settings',      'addon/dwpost/dwpost.php', 'dwpost_settings');
    unregister_hook('connector_settings_post', 'addon/dwpost/dwpost.php', 'dwpost_settings_post');

}


function dwpost_jot_nets(&$a,&$b) {
    if(! local_user())
        return;

    $dw_post = get_pconfig(local_user(),'dwpost','post');
    if(intval($dw_post) == 1) {
        $dw_defpost = get_pconfig(local_user(),'dwpost','post_by_default');
        $selected = ((intval($dw_defpost) == 1) ? ' checked="checked" ' : '');
        $b .= '<div class="profile-jot-net"><input type="checkbox" name="dwpost_enable" ' . $selected . ' value="1" /> '
            . t('Post to Dreamwidth') . '</div>';
    }
}


function dwpost_settings(&$a,&$s) {

    if(! local_user())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/dwpost/dwpost.css' . '" media="all" />' . "\r\n";

    /* Get the current state of our config variables */

    $enabled = get_pconfig(local_user(),'dwpost','post');

    $checked = (($enabled) ? ' checked="checked" ' : '');

    $def_enabled = get_pconfig(local_user(),'dwpost','post_by_default');

    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$dw_username = get_pconfig(local_user(), 'dwpost', 'dw_username');
	$dw_password = get_pconfig(local_user(), 'dwpost', 'dw_password');


    /* Add some HTML to the existing form */

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('Dreamwidth Post Settings') . '</h3>';
    $s .= '<div id="dwpost-enable-wrapper">';
    $s .= '<label id="dwpost-enable-label" for="dwpost-checkbox">' . t('Enable dreamwidth Post Plugin') . '</label>';
    $s .= '<input id="dwpost-checkbox" type="checkbox" name="dwpost" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="dwpost-username-wrapper">';
    $s .= '<label id="dwpost-username-label" for="dwpost-username">' . t('dreamwidth username') . '</label>';
    $s .= '<input id="dwpost-username" type="text" name="dw_username" value="' . $dw_username . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="dwpost-password-wrapper">';
    $s .= '<label id="dwpost-password-label" for="dwpost-password">' . t('dreamwidth password') . '</label>';
    $s .= '<input id="dwpost-password" type="password" name="dw_password" value="' . $dw_password . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="dwpost-bydefault-wrapper">';
    $s .= '<label id="dwpost-bydefault-label" for="dwpost-bydefault">' . t('Post to dreamwidth by default') . '</label>';
    $s .= '<input id="dwpost-bydefault" type="checkbox" name="dw_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="dwpost-submit" name="dwpost-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}


function dwpost_settings_post(&$a,&$b) {

	if(x($_POST,'dwpost-submit')) {

		set_pconfig(local_user(),'dwpost','post',intval($_POST['dwpost']));
		set_pconfig(local_user(),'dwpost','post_by_default',intval($_POST['dw_bydefault']));
		set_pconfig(local_user(),'dwpost','dw_username',trim($_POST['dw_username']));
		set_pconfig(local_user(),'dwpost','dw_password',trim($_POST['dw_password']));

	}

}

function dwpost_post_local(&$a,&$b) {

	// This can probably be changed to allow editing by pointing to a different API endpoint

	if($b['edit'])
		return;

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	if($b['private'] || $b['parent'])
		return;

    $dw_post   = intval(get_pconfig(local_user(),'dwpost','post'));

	$dw_enable = (($dw_post && x($_REQUEST,'dwpost_enable')) ? intval($_REQUEST['dwpost_enable']) : 0);

	if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'dwpost','post_by_default')))
		$dw_enable = 1;

    if(! $dw_enable)
       return;

    if(strlen($b['postopts']))
       $b['postopts'] .= ',';
     $b['postopts'] .= 'dwpost';
}




function dwpost_send(&$a,&$b) {

    if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
        return;

    if(! strstr($b['postopts'],'dwpost'))
        return;

    if($b['parent'] != $b['id'])
        return;

	// dreamwidth post in the LJ user's timezone. 
	// Hopefully the person's Friendica account
	// will be set to the same thing.

	$tz = 'UTC';

	$x = q("select timezone from user where uid = %d limit 1",
		intval($b['uid'])
	);
	if($x && strlen($x[0]['timezone']))
		$tz = $x[0]['timezone'];	

	$dw_username = get_pconfig($b['uid'],'dwpost','dw_username');
	$dw_password = get_pconfig($b['uid'],'dwpost','dw_password');
	$dw_blog = 'http://www.dreamwidth.org/interface/xmlrpc';

	if($dw_username && $dw_password && $dw_blog) {

		require_once('include/bbcode.php');
		require_once('include/datetime.php');

		$title = $b['title'];
		$post = bbcode($b['body']);
		$post = xmlify($post);

		$date = datetime_convert('UTC',$tz,$b['created'],'Y-m-d H:i:s');
		$year = intval(substr($date,0,4));
		$mon  = intval(substr($date,5,2));
		$day  = intval(substr($date,8,2));
		$hour = intval(substr($date,11,2));
		$min  = intval(substr($date,14,2));

		$xml = <<< EOT
<?xml version="1.0" encoding="utf-8"?>
<methodCall><methodName>LJ.XMLRPC.postevent</methodName>
<params><param>
<value><struct>
<member><name>year</name><value><int>$year</int></value></member>
<member><name>mon</name><value><int>$mon</int></value></member>
<member><name>day</name><value><int>$day</int></value></member>
<member><name>hour</name><value><int>$hour</int></value></member>
<member><name>min</name><value><int>$min</int></value></member>
<member><name>event</name><value><string>$post</string></value></member>
<member><name>username</name><value><string>$dw_username</string></value></member>
<member><name>password</name><value><string>$dw_password</string></value></member>
<member><name>subject</name><value><string>$title</string></value></member>
<member><name>lineendings</name><value><string>unix</string></value></member>
<member><name>ver</name><value><int>1</int></value></member>
<member><name>props</name>
<value><struct>
<member><name>useragent</name><value><string>Friendica</string></value></member>
<member><name>taglist</name><value><string>friendica</string></value></member>
</struct></value></member>
</struct></value>
</param></params>
</methodCall>

EOT;

		logger('dwpost: data: ' . $xml, LOGGER_DATA);

		if($dw_blog !== 'test')
			$x = post_url($dw_blog,$xml);
		logger('posted to dreamwidth: ' . ($x) ? $x : '', LOGGER_DEBUG);

	}
}

