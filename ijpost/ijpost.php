<?php
/**
 * Name: Insanejournal Post Connector
 * Description: Post to Insanejournal
 * Version: 1.0
 * Author: Tony Baldwin <https://free-haven.org/profile/tony>
 * Author: Michael Johnston
 * Author: Cat Gray <https://free-haven.org/profile/catness>
 */
use Friendica\Core\Addon;
use Friendica\Core\PConfig;

function ijpost_install() {
    Addon::registerHook('post_local',           'addon/ijpost/ijpost.php', 'ijpost_post_local');
    Addon::registerHook('notifier_normal',      'addon/ijpost/ijpost.php', 'ijpost_send');
    Addon::registerHook('jot_networks',         'addon/ijpost/ijpost.php', 'ijpost_jot_nets');
    Addon::registerHook('connector_settings',      'addon/ijpost/ijpost.php', 'ijpost_settings');
    Addon::registerHook('connector_settings_post', 'addon/ijpost/ijpost.php', 'ijpost_settings_post');

}
function ijpost_uninstall() {
    Addon::unregisterHook('post_local',       'addon/ijpost/ijpost.php', 'ijpost_post_local');
    Addon::unregisterHook('notifier_normal',  'addon/ijpost/ijpost.php', 'ijpost_send');
    Addon::unregisterHook('jot_networks',     'addon/ijpost/ijpost.php', 'ijpost_jot_nets');
    Addon::unregisterHook('connector_settings',      'addon/ijpost/ijpost.php', 'ijpost_settings');
    Addon::unregisterHook('connector_settings_post', 'addon/ijpost/ijpost.php', 'ijpost_settings_post');

}


function ijpost_jot_nets(&$a,&$b) {
    if(! local_user())
        return;

    $ij_post = PConfig::get(local_user(),'ijpost','post');
    if(intval($ij_post) == 1) {
        $ij_defpost = PConfig::get(local_user(),'ijpost','post_by_default');
        $selected = ((intval($ij_defpost) == 1) ? ' checked="checked" ' : '');
        $b .= '<div class="profile-jot-net"><input type="checkbox" name="ijpost_enable" ' . $selected . ' value="1" /> '
            . t('Post to Insanejournal') . '</div>';
    }
}


function ijpost_settings(&$a,&$s) {

    if(! local_user())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/ijpost/ijpost.css' . '" media="all" />' . "\r\n";

    /* Get the current state of our config variables */

    $enabled = PConfig::get(local_user(),'ijpost','post');

    $checked = (($enabled) ? ' checked="checked" ' : '');

    $def_enabled = PConfig::get(local_user(),'ijpost','post_by_default');

    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$ij_username = PConfig::get(local_user(), 'ijpost', 'ij_username');
	$ij_password = PConfig::get(local_user(), 'ijpost', 'ij_password');


    /* Add some HTML to the existing form */
    $s .= '<span id="settings_ijpost_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_ijpost_expanded\'); openClose(\'settings_ijpost_inflated\');">';
    $s .= '<img class="connector" src="images/insanejournal.gif" /><h3 class="connector">'. t("InsaneJournal Export").'</h3>';
    $s .= '</span>';
    $s .= '<div id="settings_ijpost_expanded" class="settings-block" style="display: none;">';
    $s .= '<span class="fakelink" onclick="openClose(\'settings_ijpost_expanded\'); openClose(\'settings_ijpost_inflated\');">';
    $s .= '<img class="connector" src="images/insanejournal.gif" /><h3 class="connector">'. t("InsaneJournal Export").'</h3>';
    $s .= '</span>';

    $s .= '<div id="ijpost-enable-wrapper">';
    $s .= '<label id="ijpost-enable-label" for="ijpost-checkbox">' . t('Enable InsaneJournal Post Plugin') . '</label>';
    $s .= '<input id="ijpost-checkbox" type="checkbox" name="ijpost" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="ijpost-username-wrapper">';
    $s .= '<label id="ijpost-username-label" for="ijpost-username">' . t('InsaneJournal username') . '</label>';
    $s .= '<input id="ijpost-username" type="text" name="ij_username" value="' . $ij_username . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="ijpost-password-wrapper">';
    $s .= '<label id="ijpost-password-label" for="ijpost-password">' . t('InsaneJournal password') . '</label>';
    $s .= '<input id="ijpost-password" type="password" name="ij_password" value="' . $ij_password . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="ijpost-bydefault-wrapper">';
    $s .= '<label id="ijpost-bydefault-label" for="ijpost-bydefault">' . t('Post to InsaneJournal by default') . '</label>';
    $s .= '<input id="ijpost-bydefault" type="checkbox" name="ij_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="ijpost-submit" name="ijpost-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

}


function ijpost_settings_post(&$a,&$b) {

	if(x($_POST,'ijpost-submit')) {

		PConfig::set(local_user(),'ijpost','post',intval($_POST['ijpost']));
		PConfig::set(local_user(),'ijpost','post_by_default',intval($_POST['ij_bydefault']));
		PConfig::set(local_user(),'ijpost','ij_username',trim($_POST['ij_username']));
		PConfig::set(local_user(),'ijpost','ij_password',trim($_POST['ij_password']));

	}

}

function ijpost_post_local(&$a,&$b) {

	// This can probably be changed to allow editing by pointing to a different API endpoint

	if($b['edit'])
		return;

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	if($b['private'] || $b['parent'])
		return;

    $ij_post   = intval(PConfig::get(local_user(),'ijpost','post'));

	$ij_enable = (($ij_post && x($_REQUEST,'ijpost_enable')) ? intval($_REQUEST['ijpost_enable']) : 0);

	if($_REQUEST['api_source'] && intval(PConfig::get(local_user(),'ijpost','post_by_default')))
		$ij_enable = 1;

    if(! $ij_enable)
       return;

    if(strlen($b['postopts']))
       $b['postopts'] .= ',';
     $b['postopts'] .= 'ijpost';
}




function ijpost_send(&$a,&$b) {

    if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
        return;

    if(! strstr($b['postopts'],'ijpost'))
        return;

    if($b['parent'] != $b['id'])
        return;

	// insanejournal post in the LJ user's timezone.
	// Hopefully the person's Friendica account
	// will be set to the same thing.

	$tz = 'UTC';

	$x = q("select timezone from user where uid = %d limit 1",
		intval($b['uid'])
	);
	if($x && strlen($x[0]['timezone']))
		$tz = $x[0]['timezone'];

	$ij_username = PConfig::get($b['uid'],'ijpost','ij_username');
	$ij_password = PConfig::get($b['uid'],'ijpost','ij_password');
	$ij_blog = 'http://www.insanejournal.com/interface/xmlrpc';

	if($ij_username && $ij_password && $ij_blog) {

		require_once('include/bbcode.php');
		require_once('include/datetime.php');

		$title = $b['title'];
		$post = bbcode($b['body']);
		$post = xmlify($post);
		$tags = ijpost_get_tags($b['tag']);

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
<member><name>username</name><value><string>$ij_username</string></value></member>
<member><name>password</name><value><string>$ij_password</string></value></member>
<member><name>subject</name><value><string>$title</string></value></member>
<member><name>lineendings</name><value><string>unix</string></value></member>
<member><name>ver</name><value><int>1</int></value></member>
<member><name>props</name>
<value><struct>
<member><name>useragent</name><value><string>Friendica</string></value></member>
<member><name>taglist</name><value><string>$tags</string></value></member>
</struct></value></member>
</struct></value>
</param></params>
</methodCall>

EOT;

		logger('ijpost: data: ' . $xml, LOGGER_DATA);

		if($ij_blog !== 'test')
			$x = post_url($ij_blog,$xml,["Content-Type: text/xml"]);
		logger('posted to insanejournal: ' . ($x) ? $x : '', LOGGER_DEBUG);

	}
}

function ijpost_get_tags($post)
{
	preg_match_all("/\]([^\[#]+)\[/",$post,$matches);
	$tags = implode(', ',$matches[1]);
	return $tags;
}
