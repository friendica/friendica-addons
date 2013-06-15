<?php
/********************************************************************
 * Name: Calendar Export
 * Description: This addon exports the public events of your users as calendar files
 * Version: 0.1
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 * License: MIT
 * ******************************************************************/


function cal_install()
{
    register_hook('plugin_settings', 'addon/cal/cal.php', 'cal_addon_settings');
    register_hook('plugin_settings_post', 'addon/cal/cal.php', 'cal_addon_settings_post');
}
function cal_uninstall()
{
    unregister_hook('plugin_settings', 'addon/cal/cal.php', 'cal_addon_settings');
    unregister_hook('plugin_settings_post', 'addon/cal/cal.php', 'cal_addon_settings_post');
}
function cal_module()
{
}
/*  pathes
 *  /cal/$user/export/$format
 */
function cal_content()
{
    $a = get_app();
    $o = "";
    if ($a->argc == 1) {
	$o = "<p>".t('Some text to explain what this does.')."</p>";
    } elseif ($a->argc==4) {
	$username = $a->argv[1];
	$do = $a->argv[2];
	$format = $a->argv[3];
	$o = "<p>".$do." calendar for ".$username." as ".$format." file.</p>";
    } else {
	$o = "<p>".t('Wrong number of parameters')."</p>";
    }
    return $o;
}

function cal_addon_settings_post ( &$a, &$b  )
{
    if (! local_user())
	return;
}
function cal_addon_settings ( &$a, &$s  )
{
    if (! local_user())
	return;
}

?>
