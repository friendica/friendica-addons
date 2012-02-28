<?php

/**
* Name: LiveJournal Post Connector
* Description: Post to LiveJournal (or anything else which uses blogger XMLRPC API)
* Version: 1.0
* Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
* Author: Tony Baldwin <http://theshi.re/profile/tony>
*/

function ljpost_install() {
    register_hook('post_local', 'addon/ljpost/ljpost.php', 'ljpost_post_local');
    register_hook('notifier_normal', 'addon/ljpost/ljpost.php', 'ljpost_send');
    register_hook('jot_networks', 'addon/ljpost/ljpost.php', 'ljpost_jot_nets');
    register_hook('connector_settings', 'addon/ljpost/ljpost.php', 'ljpost_settings');
    register_hook('connector_settings_post', 'addon/ljpost/ljpost.php', 'ljpost_settings_post');

}
function ljpost_uninstall() {
    unregister_hook('post_local', 'addon/ljpost/ljpost.php', 'ljpost_post_local');
    unregister_hook('notifier_normal', 'addon/ljpost/ljpost.php', 'ljpost_send');
    unregister_hook('jot_networks', 'addon/ljpost/ljpost.php', 'ljpost_jot_nets');
    unregister_hook('connector_settings', 'addon/ljpost/ljpost.php', 'ljpost_settings');
    unregister_hook('connector_settings_post', 'addon/ljpost/ljpost.php', 'ljpost_settings_post');

}




function ljpost_jot_nets(&$a,&$b) {
    if(! local_user())
        return;

    $lj_post = get_pconfig(local_user(),'ljpost','post');
    if(intval($lj_post) == 1) {
        $wp_defpost = get_pconfig(local_user(),'ljpost','post_by_default');
        $selected = ((intval($wp_defpost) == 1) ? ' checked="checked" ' : '');
        $b .= '<div class="profile-jot-net"><input type="checkbox" name="ljpost_enable" ' . $selected . ' value="1" /> '
            . t('Post to Livejournal') . '</div>';
    }
}


function ljpost_settings(&$a,&$s) {

    if(! local_user())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="' . $a->get_baseurl() . '/addon/ljpost/ljpost.css' . '" media="all" />' . "\r\n";

    /* Get the current state of our config variables */

    $enabled = get_pconfig(local_user(),'ljpost','post');

    $checked = (($enabled) ? ' checked="checked" ' : '');

    $def_enabled = get_pconfig(local_user(),'ljpost','post_by_default');

    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

$lj_username = get_pconfig(local_user(), 'ljpost', 'lj_username');
$lj_password = get_pconfig(local_user(), 'ljpost', 'lj_password');
$lj_blog = get_pconfig(local_user(), 'ljpost', 'lj_blog');


    /* Add some HTML to the existing form */

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('LiveJournal Post Settings') . '</h3>';
    $s .= '<div id="ljpost-enable-wrapper">';
    $s .= '<label id="ljpost-enable-label" for="ljpost-checkbox">' . t('Enable LiveJournal Post Plugin') . '</label>';
    $s .= '<input id="ljpost-checkbox" type="checkbox" name="ljpost" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="ljpost-username-wrapper">';
    $s .= '<label id="ljpost-username-label" for="ljpost-username">' . t('LiveJournal username') . '</label>';
    $s .= '<input id="ljpost-username" type="text" name="lj_username" value="' . $lj_username . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="ljpost-password-wrapper">';
    $s .= '<label id="ljpost-password-label" for="ljpost-password">' . t('LiveJournal password') . '</label>';
    $s .= '<input id="ljpost-password" type="password" name="lj_password" value="' . $lj_password . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="ljpost-blog-wrapper">';
    $s .= '<label id="ljpost-blog-label" for="ljpost-blog">' . t('LiveJournal API URL') . '</label>';
    $s .= '<input id="ljpost-blog" type="text" name="lj_blog" value="' . $lj_blog . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="ljpost-bydefault-wrapper">';
    $s .= '<label id="ljpost-bydefault-label" for="ljpost-bydefault">' . t('Post to LiveJournal by default') . '</label>';
    $s .= '<input id="ljpost-bydefault" type="checkbox" name="wp_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="ljpost-submit" name="ljpost-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}


function ljpost_settings_post(&$a,&$b) {

if(x($_POST,'ljpost-submit')) {

set_pconfig(local_user(),'ljpost','post',intval($_POST['ljpost']));
set_pconfig(local_user(),'ljpost','post_by_default',intval($_POST['lj_bydefault']));
set_pconfig(local_user(),'ljpost','lj_username',trim($_POST['lj_username']));
set_pconfig(local_user(),'ljpost','lj_password',trim($_POST['lj_password']));
set_pconfig(local_user(),'ljpost','lj_blog',trim($_POST['lj_blog']));

}

}

function ljpost_post_local(&$a,&$b) {

// This can probably be changed to allow editing by pointing to a different API endpoint

if($b['edit'])
return;

if((! local_user()) || (local_user() != $b['uid']))
return;

if($b['private'] || $b['parent'])
return;

    $lj_post = intval(get_pconfig(local_user(),'ljpost','post'));

$lj_enable = (($lj_post && x($_REQUEST,'ljpost_enable')) ? intval($_REQUEST['ljpost_enable']) : 0);

if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'ljpost','post_by_default')))
$lj_enable = 1;

    if(! $lj_enable)
       return;

    if(strlen($b['postopts']))
       $b['postopts'] .= ',';
     $b['postopts'] .= 'ljpost';
}




function ljpost_send(&$a,&$b) {

    if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
        return;

    if(! strstr($b['postopts'],'ljpost'))
        return;

    if($b['parent'] != $b['id'])
        return;


$lj_username = get_pconfig($b['uid'],'ljpost','lj_username');
$lj_password = get_pconfig($b['uid'],'ljpost','lj_password');
$lj_blog = get_pconfig($b['uid'],'ljpost','lj_blog');

if($lj_username && $lj_password && $lj_blog) {

require_once('include/bbcode.php');

$title = '<title>' . (($b['title']) ? $b['title'] : t('Post from Friendica')) . '</title>';
$post = $title . bbcode($b['body']);
$post = xmlify($post);

$year = date('Y')
$month = date('F')
$day = date('l')
$hour = date('H')
$min = date('i')

$xml = <<< EOT

<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
<methodCall><methodName>LJ.XMLRPC.postevent</methodName>
<params><param>
<value><struct>
<member><name>year</name><value><int>$year</int></value></member>
<member><name>mon</name><value><int>$month</int></value></member>
<member><name>day</name><value><int>$day</int></value></member>
<member><name>hour</name><value><int>$hour</int></value></member>
<member><name>min</name><value><int>$min</int></value></member>
<member><name>usejournal</name><value><string>$lj_blog</string></value></member>
<member><name>event</name><value><string>$post</string></value></member>
<member><name>username</name><value><string>$lj_username</string></value></member>
<member><name>password</name><value><string>$lj_password</string></value></member>
<member><name>subject</name><value><string>friendica post</string></value></member>
<member><name>lineendings</name><value><string>unix</string></value></member>
<member><name>ver</name><value><int>1</int></value></member>
<member><name>props</name>
<value><struct>
<member><name>useragent</name><value><string>Friendica</string></value></member>
<member><name>taglist</name><value><string>friendica,crosspost</string></value></member>
</struct></value></member>
</struct></value>
</param></params>
</methodCall>

EOT;

logger('ljpost: data: ' . $xml, LOGGER_DATA);

if($lj_blog !== 'test')
$x = post_url($lj_blog,$xml);
logger('posted to livejournal: ' . ($x) ? $x : '');

	}
}
