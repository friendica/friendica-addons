<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['You are authenticated to Bluesky. For security reasons the password isn\'t stored.'] = 'Du bist auf Bluesky authentifiziert. Aus Sicherheitsgründen wird das Passwort nicht gespeichert.';
$a->strings['You are not authenticated. Please enter the app password.'] = 'Du bist derzeit nicht authentifiziert. Bitte gib dein App Passwort ein.';
$a->strings['Enable Bluesky Post Addon'] = 'Bluesky Post Addon aktivieren';
$a->strings['Post to Bluesky by default'] = 'Standardmäßig auf Bluesky veröffentlichen';
$a->strings['Import the remote timeline'] = 'Importiere die entfernte Timeline';
$a->strings['Bluesky host'] = 'Bluesky Host';
$a->strings['Bluesky handle'] = 'Bluesky Handle';
$a->strings['Bluesky DID'] = 'Bluesky DID';
$a->strings['This is the unique identifier. It will be fetched automatically, when the handle is entered.'] = 'Sobald das Handle eingegeben ist, wird diese einzigartige Kennung automatisch abgerufen.';
$a->strings['Bluesky app password'] = 'Bluesky App Passwort';
$a->strings['Please don\'t add your real password here, but instead create a specific app password in the Bluesky settings.'] = 'Bitte verwende hier nicht dein echtes Passwort, sondern stattdessen ein speziell für diese App in den Bluesky Einstellungen festgelegtes Passwort.';
$a->strings['Bluesky Import/Export'] = 'Bluesky Import/Export';
$a->strings['Post to Bluesky'] = 'Auf Bluesky veröffentlichen';
