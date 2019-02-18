<?php
/**
 * Name: rendertime
 * Description: Shows the time that was needed to render the current page
 * Version: 0.1
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 *
 */
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;

function rendertime_install() {
	Hook::register('page_end', 'addon/rendertime/rendertime.php', 'rendertime_page_end');
}


function rendertime_uninstall() {
	Hook::unregister('init_1', 'addon/rendertime/rendertime.php', 'rendertime_init_1');
	Hook::unregister('page_end', 'addon/rendertime/rendertime.php', 'rendertime_page_end');
}

function rendertime_init_1(&$a) {
}

function rendertime_page_end(&$a, &$o) {

	$duration = microtime(true)-$a->getProfiler()->get("start");

	$ignored_modules = ["fbrowser"];
	$ignored = in_array($a->module, $ignored_modules);

	if (is_site_admin() && (defaults($_GET, "mode", '') != "minimal") && !$a->is_mobile && !$a->is_tablet && !$ignored) {
		$o = $o.'<div class="renderinfo">'. L10n::t("Database: %s/%s, Network: %s, Rendering: %s, Session: %s, I/O: %s, Other: %s, Total: %s",
			round($a->getProfiler()->get("database") - $a->getProfiler()->get("database_write"), 3),
			round($a->getProfiler()->get("database_write"), 3),
			round($a->getProfiler()->get("network"), 2),
			round($a->getProfiler()->get("rendering"), 2),
			round($a->getProfiler()->get("parser"), 2),
			round($a->getProfiler()->get("file"), 2),
			round($duration - $a->getProfiler()->get("database")
					- $a->getProfiler()->get("network") - $a->getProfiler()->get("rendering")
					- $a->getProfiler()->get("parser") - $a->getProfiler()->get("file"), 2),
			round($duration, 2)
			//round($a->getProfiler()->get("markstart"), 3)
			//round($a->getProfiler()->get("plugin"), 3)
			)."</div>";

		if (Config::get("rendertime", "callstack")) {
			$o .= "<pre>";
			$o .= "\nDatabase Read:\n";
			foreach ($a->callstack["database"] as $func => $time) {
				$time = round($time, 3);

				if ($time > 0) {
					$o .= $func.": ".$time."\n";
				}
			}
			$o .= "\nDatabase Write:\n";
			foreach ($a->callstack["database_write"] as $func => $time) {
				$time = round($time, 3);

				if ($time > 0) {
					$o .= $func.": ".$time."\n";
				}
			}

			$o .= "\nNetwork:\n";
			foreach ($a->callstack["network"] as $func => $time) {
				$time = round($time, 3);

				if ($time > 0) {
					$o .= $func.": ".$time."\n";
				}
			}

			$o .= "</pre>";
		}
	}
}
