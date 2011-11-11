<?php
/**
 * Name: BugLink
 * Description: Show link to Friendica bug site at bottom of page
 * Version: 1.0
 * Author: Mike Macgirvin <mike@macgirvin.com>
 */


function buglink_install() {
	register_hook('page_end', 'addon/buglink/buglink.php', 'buglink_active');
}

function buglink_uninstall() {
	unregister_hook('page_end', 'addon/buglink/buglink.php', 'buglink_active');
}


function buglink_active(&$a,&$b) {
	$b .= '<div style="position: fixed; bottom: 5px; left: 5px;"><a href="http://bugs.friendica.com" title="' . t('Report Bug') . '"><img src="addon/buglink/bug-x.gif" alt="' . t('Report Bug') . '" /></a></div>';
} 