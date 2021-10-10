<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['XMPP-Chat (Jabber)'] = 'XMPP-Chat (Jabber)';
$a->strings['Enable Webchat'] = 'Aktiviere Webchat';
$a->strings['Individual Credentials'] = 'Individuelle Anmeldedaten';
$a->strings['Jabber BOSH host'] = 'Jabber-BOSH-Host';
$a->strings['Save Settings'] = 'Speichere Einstellungen';
$a->strings['Use central userbase'] = 'Nutze zentrale Nutzerbasis';
$a->strings['If enabled, users will automatically login to an ejabberd server that has to be installed on this machine with synchronized credentials via the "auth_ejabberd.php" script.'] = 'Wenn aktiviert, werden die Nutzer automatisch auf dem EJabber-Server, der auf dieser Maschine installiert ist, angemeldet, und die Zugangsdaten werden über das "auth_ejabberd.php"-Script synchronisiert.';
