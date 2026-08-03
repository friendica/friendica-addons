<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Permission denied.'] = 'Permission denied.';
$a->strings['Unable to register the client at the pump.io server \'%s\'.'] = 'Unable to register the client at the pump.io server \'%s\'.';
$a->strings['You are now authenticated to pumpio.'] = 'You are now authenticated to pump.io.';
$a->strings['return to the connector page'] = 'Return to the connector page';
$a->strings['Post to pumpio'] = 'Post to pump.io';
$a->strings['Save Settings'] = 'Save Settings';
$a->strings['Authenticate your pump.io connection'] = 'Authenticate your pump.io connection';
$a->strings['Import the remote timeline'] = 'Import the remote timeline';
$a->strings['Should posts be public?'] = 'Should posts be public?';
$a->strings['Mirror all public posts'] = 'Mirror all public posts';
$a->strings['Pump.io Import/Export/Mirror'] = 'Pump.io Import/Export/Mirror';
$a->strings['status'] = 'status';
$a->strings['%1$s likes %2$s\'s %3$s'] = '%1$s likes %2$s\'s %3$s';
