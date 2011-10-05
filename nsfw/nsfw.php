<?php


/**
 * Name: NSFW
 * Description: Collapse posts with inappropriate content
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 */

function nsfw_install() {
	register_hook('prepare_body', 'addon/nsfw/nsfw.php', 'nsfw_prepare_body');
}


function nsfw_uninstall() {
	unregister_hook('prepare_body', 'addon/nsfw/nsfw.php', 'nsfw_prepare_body');
}

function nsfw_prepare_body(&$a,&$b) {
	if(stristr($b,'nsfw')) {
		$rnd = random_string(8);
		$b = '<div id="nsfw-wrap-' . $rnd . '" class="fakelink" onclick=openClose(\'nsfw-' . $rnd . '\'); >' . t('NSFW - Click to open/close') . '</div><div id="nsfw-' . $rnd . '" style="display: none; " >' . $b . '</div>';  
	}
}