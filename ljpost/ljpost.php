<?php
/**
 * Name: LiveJournal Post Connector
 * Description: Post to LiveJournal
 * Version: 1.0
 * Author: Tony Baldwin <https://free-haven.org/profile/tony>
 * Author: Michael Johnston
 * Author: Cat Gray <https://free-haven.org/profile/catness>
 */

use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\DI;
use Friendica\Model\Tag;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Network;
use Friendica\Util\XML;

function ljpost_install() {
    Hook::register('post_local',           'addon/ljpost/ljpost.php', 'ljpost_post_local');
    Hook::register('notifier_normal',      'addon/ljpost/ljpost.php', 'ljpost_send');
    Hook::register('jot_networks',         'addon/ljpost/ljpost.php', 'ljpost_jot_nets');
    Hook::register('connector_settings',      'addon/ljpost/ljpost.php', 'ljpost_settings');
    Hook::register('connector_settings_post', 'addon/ljpost/ljpost.php', 'ljpost_settings_post');

}
function ljpost_uninstall() {
    Hook::unregister('post_local',       'addon/ljpost/ljpost.php', 'ljpost_post_local');
    Hook::unregister('notifier_normal',  'addon/ljpost/ljpost.php', 'ljpost_send');
    Hook::unregister('jot_networks',     'addon/ljpost/ljpost.php', 'ljpost_jot_nets');
    Hook::unregister('connector_settings',      'addon/ljpost/ljpost.php', 'ljpost_settings');
    Hook::unregister('connector_settings_post', 'addon/ljpost/ljpost.php', 'ljpost_settings_post');

}


function ljpost_jot_nets(\Friendica\App &$a, array &$jotnets_fields)
{
    if(! local_user()) {
        return;
    }

    if (DI::pConfig()->get(local_user(),'ljpost','post')) {
	    $jotnets_fields[] = [
		    'type' => 'checkbox',
		    'field' => [
			    'ljpost_enable',
			    DI::l10n()->t('Post to LiveJournal'),
			    DI::pConfig()->get(local_user(),'ljpost','post_by_default')
		    ]
	    ];
    }
}


function ljpost_settings(&$a,&$s) {

    if(! local_user())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    DI::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl()->get() . '/addon/ljpost/ljpost.css' . '" media="all" />' . "\r\n";

    /* Get the current state of our config variables */

    $enabled = DI::pConfig()->get(local_user(),'ljpost','post');

    $checked = (($enabled) ? ' checked="checked" ' : '');

    $def_enabled = DI::pConfig()->get(local_user(),'ljpost','post_by_default');

    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$lj_username = DI::pConfig()->get(local_user(), 'ljpost', 'lj_username');
	$lj_password = DI::pConfig()->get(local_user(), 'ljpost', 'lj_password');


    /* Add some HTML to the existing form */

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . DI::l10n()->t('LiveJournal Post Settings') . '</h3>';
    $s .= '<div id="ljpost-enable-wrapper">';
    $s .= '<label id="ljpost-enable-label" for="ljpost-checkbox">' . DI::l10n()->t('Enable LiveJournal Post Addon') . '</label>';
    $s .= '<input id="ljpost-checkbox" type="checkbox" name="ljpost" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="ljpost-username-wrapper">';
    $s .= '<label id="ljpost-username-label" for="ljpost-username">' . DI::l10n()->t('LiveJournal username') . '</label>';
    $s .= '<input id="ljpost-username" type="text" name="lj_username" value="' . $lj_username . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="ljpost-password-wrapper">';
    $s .= '<label id="ljpost-password-label" for="ljpost-password">' . DI::l10n()->t('LiveJournal password') . '</label>';
    $s .= '<input id="ljpost-password" type="password" name="lj_password" value="' . $lj_password . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="ljpost-bydefault-wrapper">';
    $s .= '<label id="ljpost-bydefault-label" for="ljpost-bydefault">' . DI::l10n()->t('Post to LiveJournal by default') . '</label>';
    $s .= '<input id="ljpost-bydefault" type="checkbox" name="lj_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="ljpost-submit" name="ljpost-submit" class="settings-submit" value="' . DI::l10n()->t('Save Settings') . '" /></div></div>';

}


function ljpost_settings_post(&$a,&$b) {

	if(!empty($_POST['ljpost-submit'])) {

		DI::pConfig()->set(local_user(),'ljpost','post',intval($_POST['ljpost']));
		DI::pConfig()->set(local_user(),'ljpost','post_by_default',intval($_POST['lj_bydefault']));
		DI::pConfig()->set(local_user(),'ljpost','lj_username',trim($_POST['lj_username']));
		DI::pConfig()->set(local_user(),'ljpost','lj_password',trim($_POST['lj_password']));

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

    $lj_post   = intval(DI::pConfig()->get(local_user(),'ljpost','post'));

	$lj_enable = (($lj_post && !empty($_REQUEST['ljpost_enable'])) ? intval($_REQUEST['ljpost_enable']) : 0);

	if($b['api_source'] && intval(DI::pConfig()->get(local_user(),'ljpost','post_by_default')))
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

	// LiveJournal post in the LJ user's timezone.
	// Hopefully the person's Friendica account
	// will be set to the same thing.

	$tz = 'UTC';

	$x = q("select timezone from user where uid = %d limit 1",
		intval($b['uid'])
	);
	if($x && strlen($x[0]['timezone']))
		$tz = $x[0]['timezone'];

	$lj_username = XML::escape(DI::pConfig()->get($b['uid'],'ljpost','lj_username'));
	$lj_password = XML::escape(DI::pConfig()->get($b['uid'],'ljpost','lj_password'));
	$lj_journal = XML::escape(DI::pConfig()->get($b['uid'],'ljpost','lj_journal'));
//	if(! $lj_journal)
//		$lj_journal = $lj_username;

	$lj_blog = XML::escape(DI::pConfig()->get($b['uid'],'ljpost','lj_blog'));
	if(! strlen($lj_blog))
		$lj_blog = XML::escape('http://www.livejournal.com/interface/xmlrpc');

	if($lj_username && $lj_password && $lj_blog) {
		$title = XML::escape($b['title']);
		$post = BBCode::convert($b['body']);
		$post = XML::escape($post);
		$tags = Tag::getCSVByURIId($b['uri-id'], [Tag::HASHTAG]);

		$date = DateTimeFormat::convert($b['created'], $tz);
		$year = intval(substr($date,0,4));
		$mon  = intval(substr($date,5,2));
		$day  = intval(substr($date,8,2));
		$hour = intval(substr($date,11,2));
		$min  = intval(substr($date,14,2));

		$xml = <<< EOT
<?xml version="1.0" encoding="utf-8"?>
<methodCall>
  <methodName>LJ.XMLRPC.postevent</methodName>
  <params>
    <param><value>
        <struct>
        <member><name>username</name><value><string>$lj_username</string></value></member>
        <member><name>password</name><value><string>$lj_password</string></value></member>
        <member><name>event</name><value><string>$post</string></value></member>
        <member><name>subject</name><value><string>$title</string></value></member>
        <member><name>lineendings</name><value><string>unix</string></value></member>
        <member><name>year</name><value><int>$year</int></value></member>
        <member><name>mon</name><value><int>$mon</int></value></member>
        <member><name>day</name><value><int>$day</int></value></member>
        <member><name>hour</name><value><int>$hour</int></value></member>
        <member><name>min</name><value><int>$min</int></value></member>
		<member><name>usejournal</name><value><string>$lj_username</string></value></member>
		<member>
			<name>props</name>
			<value>
				<struct>
					<member>
						<name>useragent</name>
						<value><string>Friendica</string></value>
					</member>
					<member>
						<name>taglist</name>
						<value><string>$tags</string></value>
					</member>
				</struct>
			</value>
		</member>
        </struct>
    </value></param>
  </params>
</methodCall>

EOT;

		Logger::log('ljpost: data: ' . $xml, Logger::DATA);

		if ($lj_blog !== 'test') {
			$x = Network::post($lj_blog, $xml, ["Content-Type: text/xml"])->getBody();
		}
		Logger::log('posted to livejournal: ' . ($x) ? $x : '', Logger::DEBUG);
	}
}
