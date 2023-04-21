<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Permission denied.'] = 'Permesso negato.';
$a->strings['Unable to register the client at the pump.io server \'%s\'.'] = 'Impossibile registrare il client sul server pump.io \'%s\'';
$a->strings['You are now authenticated to pumpio.'] = 'Sei autenticato su pump.io';
$a->strings['return to the connector page'] = 'ritorna alla pagina del connettore';
$a->strings['Post to pumpio'] = 'Invia a pump.io';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['Delete this preset'] = 'Elimina questa preimpostazione';
$a->strings['Authenticate your pump.io connection'] = 'Autentica la tua connessione pump.io';
$a->strings['Pump.io servername (without "http://" or "https://" )'] = 'Nome server Pump.io (senza "http://" o "https://" )';
$a->strings['Pump.io username (without the servername)'] = 'Nome utente Pump.io (senza nome server)';
$a->strings['Import the remote timeline'] = 'Importa la timeline remota';
$a->strings['Enable Pump.io Post Addon'] = 'Abilita componente aggiuntivo di pubblicazione Pump.io';
$a->strings['Post to Pump.io by default'] = 'Pubblica su Pump.io per impostazione predefinita';
$a->strings['Should posts be public?'] = 'I messaggi devono essere pubblici?';
$a->strings['Mirror all public posts'] = 'Clona tutti i messaggi pubblici';
$a->strings['Pump.io Import/Export/Mirror'] = 'Esporta/Importa/Clona pump.io';
$a->strings['status'] = 'stato';
$a->strings['%1$s likes %2$s\'s %3$s'] = 'a %1$s piace %2$s di %3$s';
