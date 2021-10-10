<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Permission denied.'] = 'Zugriff verweigert.';
$a->strings['Unable to register the client at the pump.io server \'%s\'.'] = 'Die Registrierung des Nutzers auf dem Pump.io-Server \'%s\' ist nicht möglich.';
$a->strings['You are now authenticated to pumpio.'] = 'Du bist nun auf pumpio authentifiziert.';
$a->strings['return to the connector page'] = 'zurück zur Connector Seite';
$a->strings['Post to pumpio'] = 'Auf pumpio veröffentlichen';
$a->strings['Pump.io Import/Export/Mirror'] = ' Pump.io Import/Export/Spiegeln';
$a->strings['pump.io username (without the servername)'] = ' Pump.io Nutzername (ohne den Servernamen)';
$a->strings['pump.io servername (without "http://" or "https://" )'] = ' Pump.io Servername (ohne "http://" oder "https://" )';
$a->strings['Authenticate your pump.io connection'] = 'Authentifiziere deine  Pump.io Verbindung';
$a->strings['Import the remote timeline'] = 'Importiere die entfernte Zeitleiste';
$a->strings['Enable pump.io Post Addon'] = 'Pump.io-Post-Addon aktivieren';
$a->strings['Post to pump.io by default'] = 'Standardmäßig bei  Pump.io veröffentlichen';
$a->strings['Should posts be public?'] = 'Sollen Nachrichten öffentlich sein ?';
$a->strings['Mirror all public posts'] = 'Spiegle alle öffentlichen Nachrichten';
$a->strings['Check to delete this preset'] = 'Markieren um dieses Preset zu löschen';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Pump.io post failed. Queued for retry.'] = 'Veröffentlichung bei Pump.io gescheitert. Wir versuchen es später erneut.';
$a->strings['Pump.io like failed. Queued for retry.'] = 'Veröffentlichung bei Pump.io gescheitert. Wir versuchen es später erneut.';
$a->strings['status'] = 'Status';
$a->strings['%1$s likes %2$s\'s %3$s'] = '%1$s mag %2$s\'s %3$s';
