<?php

/**
 * Name: MemberSince
 * Description: Display membership date in profile
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 */

require_once('include/datetime.php');

function membersince_install(){ register_hook('profile_advanced','addon/membersince/membersince.php','membersince_display'); }

function membersince_uninstall(){ unregister_hook('profile_advanced','addon/membersince/membersince.php','membersince_display'); }

function membersince_display(&$a,&$b) { 
$b = preg_replace('/<\/dl>/',"</dl>\n<dl><dt>" . t('Member since:') . '</dt><dd>' . datetime_convert('UTC',date_default_timezone_get(),$a->profile['register_date']) . '</dd></dl>' ,$b, 1); 
//$b = str_replace('</div>' . "\n" . '<div id="advanced-profile-name-end"></div>',sprintf( t(' - Member since: %s') . EOL, datetime_convert('UTC',date_default_timezone_get(),$a->profile['register_date'])) . '</div>' . "\n" . '<div id="advanced-profile-name-end"></div>',$b); 
}