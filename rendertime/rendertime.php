<?php


/**
 * Name: rendertime
 * Description: Shows the time that was needed to render the current page
 * Version: 0.1
 * Author: Michael Vvogel <http://pirati.ca/profile/heluecht>
 *
 */

function rendertime_install() {
	register_hook('init_1', 'addon/rendertime/rendertime.php', 'rendertime_init_1');
	register_hook('page_end', 'addon/rendertime/rendertime.php', 'rendertime_page_end');
}


function rendertime_uninstall() {
	unregister_hook('init_1', 'addon/rendertime/rendertime.php', 'rendertime_init_1');
	unregister_hook('page_end', 'addon/rendertime/rendertime.php', 'rendertime_page_end');
}

function rendertime_init_1(&$a) {
	global $rendertime_start;

	$rendertime_start = microtime(true);
}

function rendertime_page_end(&$a, &$o) {
	global $rendertime_start;

	$duration = round(microtime(true)-$rendertime_start, 3);

	$o = $o.'<div class="renderinfo">'.sprintf(t("This page took %s seconds to render"), $duration)."</div>";
}
