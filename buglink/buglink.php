<?php
/**
 * Name: BugLink
 * Description: Show link to Friendica bug site at bottom of page
 * Version: 1.0
 * Author: Mike Macgirvin <mike@macgirvin.com>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\DI;

function buglink_install()
{
	Hook::register('page_end', 'addon/buglink/buglink.php', 'buglink_active');
}

function buglink_active(App $a, &$b)
{
	$b .= '<div id="buglink_wrapper" style="position: fixed; bottom: 5px; left: 5px;"><a href="https://github.com/friendica/friendica/issues" target="_blank" rel="noopener noreferrer" title="' . DI::l10n()->t('Report Bug') . '"><img src="addon/buglink/bug-x.gif" alt="' . DI::l10n()->t('Report Bug') . '" /></a></div>';
}
