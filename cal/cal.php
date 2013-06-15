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
}
function cal_uninstall()
{
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

?>
