<?php
/*
 * Name: WebRTC Application
 * Description: add a webrtc instance for video/audio
 * Version: 1.0
 * Author: stephen mahood <https://friends.mayfirst.org/profile/marxistvegan>
 * Author: Tobias Diekershoff <http://diekershoff.homeunix.net/friendica/profile/tobias>
 */

function webrtc_install() {
        register_hook('app_menu', 'addon/webrtc/webrtc.php', 'webrtc_app_menu');
}

function web_uninstall() {
        unregister_hook('app_menu', 'addon/webrtc/webrtc.php', 'webrtc_app_menu');

}

function webrtc_app_menu($a,&$b) {
	$b['app_menu'][] = '<div class="app-title"><a href="webrtc">' . t('WebRTC Videochat') . '</a></div>';
}

function webrtc_plugin_admin (&$a, &$o) {
        $t = get_markup_template( "admin.tpl", "addon/webrtc/" );
	$o = replace_macros( $t, array(
	    '$submit' => t('Submit'),
	    '$webrtcurl' => array('webrtcurl', t('WebRTC Base URL'), get_config('webrtc','webrtcurl' ), t('Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org .')),
	));
}
function webrtc_plugin_admin_post (&$a) {
        $url = ((x($_POST, 'webrtcurl')) ? notags(trim($_POST['webrtcurl'])) : '');
	    set_config('webrtc', 'webrtcurl', $url);
	    info( t('Settings updated.'). EOL);
}

function webrtc_module() {
	return;
}

function webrtc_content(&$a) {
        $o = '';

        /* landingpage to create chatrooms */
        $webrtcurl = get_config('webrtc','webrtcurl');

        /* embedd the landing page in an iframe */
        $o .= '<h2>'.t('Video Chat').'</h2>';
        $o .= '<p>FIXME some short information for the enduser what to do. Best surrounded in a <code>t()</code> call so the text can be translated.</p>';
        $o .= '<iframe src="'.$webrtcurl.'" width="600px" height="600px"></iframe>';

        return $o;
}
?>
