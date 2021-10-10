<?php

if(! function_exists("string_plural_select_en_us")) {
function string_plural_select_en_us($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['XMPP settings updated.'] = 'XMPP settings updated.';
$a->strings['XMPP-Chat (Jabber)'] = 'XMPP-Chat (Jabber)';
$a->strings['Enable Webchat'] = 'Enable Webchat';
$a->strings['Individual Credentials'] = 'Individual Credentials';
$a->strings['Jabber BOSH host'] = 'Jabber BOSH host';
$a->strings['Save Settings'] = 'Save Settings';
$a->strings['Use central userbase'] = 'Use central userbase';
$a->strings['If enabled, users will automatically login to an ejabberd server that has to be installed on this machine with synchronized credentials via the "auth_ejabberd.php" script.'] = 'If enabled, users will automatically login to an ejabberd server that has to be installed on this machine with synchronized credentials via the "auth_ejabberd.php" script.';
$a->strings['Settings updated.'] = 'Settings updated.';
