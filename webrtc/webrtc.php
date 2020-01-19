<?php
/*
 * Name: WebRTC Application
 * Description: add a webrtc instance for video/audio
 * Version: 1.0
 * Author: Stephen Mahood <https://friends.mayfirst.org/profile/marxistvegan>
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 */
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Util\Strings;

function webrtc_install() {
        Hook::register('app_menu', 'addon/webrtc/webrtc.php', 'webrtc_app_menu');
}

function webrtc_uninstall() {
        Hook::unregister('app_menu', 'addon/webrtc/webrtc.php', 'webrtc_app_menu');

}

function webrtc_app_menu($a,&$b) {
	$b['app_menu'][] = '<div class="app-title"><a href="webrtc">' . DI::l10n()->t('WebRTC Videochat') . '</a></div>';
}

function webrtc_addon_admin (&$a, &$o) {
        $t = Renderer::getMarkupTemplate( "admin.tpl", "addon/webrtc/" );
	$o = Renderer::replaceMacros( $t, [
	    '$submit' => DI::l10n()->t('Save Settings'),
	    '$webrtcurl' => ['webrtcurl', DI::l10n()->t('WebRTC Base URL'), DI::config()->get('webrtc','webrtcurl' ), DI::l10n()->t('Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org .')],
	]);
}
function webrtc_addon_admin_post (&$a) {
        $url = (!empty($_POST['webrtcurl']) ? Strings::escapeTags(trim($_POST['webrtcurl'])) : '');
	    DI::config()->set('webrtc', 'webrtcurl', $url);
	    info(DI::l10n()->t('Settings updated.'). EOL);
}

function webrtc_module() {
	return;
}

function webrtc_content(&$a) {
        $o = '';

        /* landingpage to create chatrooms */
        $webrtcurl = DI::config()->get('webrtc','webrtcurl');

        /* embedd the landing page in an iframe */
        $o .= '<h2>'.DI::l10n()->t('Video Chat').'</h2>';
        $o .= '<p>'.DI::l10n()->t('WebRTC is a video and audio conferencing tool that works with Firefox (version 21 and above) and Chrome/Chromium (version 25 and above). Just create a new chat room and send the link to someone you want to chat with.').'</p>';
	if ($webrtcurl == '') {
	    $o .= '<p>'.DI::l10n()->t('Please contact your friendica admin and send a reminder to configure the WebRTC addon.').'</p>';
	} else {
	    $o .= '<iframe src="'.$webrtcurl.'" width="600px" height="600px"></iframe>';
	}


        return $o;
}
?>
