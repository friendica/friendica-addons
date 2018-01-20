<?php
/**
 * Name: MemberSince
 * Description: Display membership date in profile
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */
use Friendica\Core\Addon;

require_once 'include/datetime.php';

function membersince_install()
{
	Addon::registerHook('profile_advanced', 'addon/membersince/membersince.php', 'membersince_display');
}

function membersince_uninstall()
{
	Addon::unregisterHook('profile_advanced', 'addon/membersince/membersince.php', 'membersince_display');
}

function membersince_display(&$a, &$b)
{
	// Works in Vier
	$b = preg_replace('/<\/dl>/', "</dl>\n\n\n<dl id=\"aprofile-membersince\" class=\"aprofile\">\n<dt>" . t('Member since:') . "</dt>\n<dd>" . datetime_convert('UTC', date_default_timezone_get(), $a->profile['register_date']) . "</dd>\n</dl>", $b, 1);

	// Trying for Frio
	//$b = preg_replace('/<\/div>/', "<div id=\"aprofile-membersince\" class=\"aprofile\"><hr class=\"profile-separator\"><div class=\"profile-label-name\">" . t('Member since:') . "</div><div class=\"profile-entry\">" . datetime_convert('UTC', date_default_timezone_get(), $a->profile['register_date']) . "</div></div>", $b, 1);
}
