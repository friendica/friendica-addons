<?php
/**
 * Name: BugLink
 * Description: Show link to Friendica bug site at bottom of page
 * Version: 1.0
 * Author: Mike Macgirvin <mike@macgirvin.com>
 */

use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Core\L10n;

function buglink_install()
{
	Addon::registerHook('page_end', 'addon/buglink/buglink.php', 'buglink_active');
}

function buglink_uninstall()
{
	Addon::unregisterHook('page_end', 'addon/buglink/buglink.php', 'buglink_active');
}

function buglink_active(App $a, array &$b)
{
	$b .= '<div id="buglink_wrapper" style="position: fixed; bottom: 5px; left: 5px;"><a href="https://github.com/friendica/friendica/issues" target="_blank" title="' . L10n::t('Report Bug') . '"><img src="addon/buglink/bug-x.gif" alt="' . L10n::t('Report Bug') . '" /></a></div>';
}
