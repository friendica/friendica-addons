<?php
/*
 * Name: WebRTC Application
 * Description: add a webrtc instance for video/audio
 * Version: 1.0
 * Author: Stephen Mahood <https://friends.mayfirst.org/profile/marxistvegan>
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 */

use Friendica\Core\Config;

function webrtc_install() {
        register_hook('app_menu', 'addon/webrtc/webrtc.php', 'webrtc_app_menu');
}

function webrtc_uninstall() {
        unregister_hook('app_menu', 'addon/webrtc/webrtc.php', 'webrtc_app_menu');

}

function webrtc_app_menu($a,&$b) {
	$b['app_menu'][] = '<div class="app-title"><a href="webrtc">' . t('WebRTC Videochat') . '</a></div>';
}

function webrtc_plugin_admin (&$a, &$o) {
        $t = get_markup_template( "admin.tpl", "addon/webrtc/" );
	$o = replace_macros( $t, [
	    '$submit' => t('Save Settings'),
	    '$webrtcurl' => ['webrtcurl', t('WebRTC Base URL'), Config::get('webrtc','webrtcurl' ), t('Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org .')],
	]);
}
function webrtc_plugin_admin_post (&$a) {
        $url = ((x($_POST, 'webrtcurl')) ? notags(trim($_POST['webrtcurl'])) : '');
	    Config::set('webrtc', 'webrtcurl', $url);
	    info( t('Settings updated.'). EOL);
}

function webrtc_module() {
	return;
}

function webrtc_content(&$a) {
        $o = '';

        /* landingpage to create chatrooms */
        $webrtcurl = Config::get('webrtc','webrtcurl');

        /* embedd the landing page in an iframe */
        $o .= '<h2>'.t('Video Chat').'</h2>';
        $o .= '<p>'.t('WebRTC is a video and audio conferencing tool that works with Firefox (version 21 and above) and Chrome/Chromium (version 25 and above). Just create a new chat room and send the link to someone you want to chat with.').'</p>';
	if ($webrtcurl == '') {
	    $o .= '<p>'.t('Please contact your friendica admin and send a reminder to configure the WebRTC addon.').'</p>';
	} else {
	    $o .= '<iframe src="'.$webrtcurl.'" width="600px" height="600px"></iframe>';
	}


        return $o;
}
?>
