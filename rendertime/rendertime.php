<?php


/**
 * Name: rendertime
 * Description: Shows the time that was needed to render the current page
 * Version: 0.1
 * Author: Michael Vvogel <http://pirati.ca/profile/heluecht>
 *
 */

function rendertime_install() {
	register_hook('page_end', 'addon/rendertime/rendertime.php', 'rendertime_page_end');
}


function rendertime_uninstall() {
	unregister_hook('init_1', 'addon/rendertime/rendertime.php', 'rendertime_init_1');
	unregister_hook('page_end', 'addon/rendertime/rendertime.php', 'rendertime_page_end');
}

function rendertime_init_1(&$a) {
}

function rendertime_page_end(&$a, &$o) {

	$duration = microtime(true)-$a->performance["start"];

	$o = $o.'<div class="renderinfo">'.sprintf(t("Performance: Database: %s, Network: %s, Rendering: %s, Parser: %s, I/O: %s, Other: %s"),
						round($a->performance["database"], 3),
						round($a->performance["network"], 3),
						round($a->performance["rendering"], 3),
						round($a->performance["parser"], 3),
						round($a->performance["file"], 3),
						round($duration - $a->performance["database"] - $a->performance["network"]
							 - $a->performance["rendering"] - $a->performance["parser"]
							 - $a->performance["file"], 3))."</div>";
}
