<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Permission denied.'] = 'Zugriff verweigert.';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Consumer Key'] = 'Consumer Key';
$a->strings['Consumer Secret'] = 'Consumer Secret';
$a->strings['Maximum tags'] = 'Maximale Anzahl an Tags';
$a->strings['Maximum number of tags that a user can follow. Enter 0 to deactivate the feature.'] = 'Maximale Anzahl von Tags, die ein Benutzer verfolgen kann. Geben Sie 0 ein, um die Funktion zu deaktivieren.';
$a->strings['Post to page:'] = 'Auf tumblr veröffentlichen';
$a->strings['(Re-)Authenticate your tumblr page'] = '(Re-)Authentifizierung deiner tumblr-Seite';
$a->strings['You are not authenticated to tumblr'] = 'Du bist gegenüber tumblr nicht authentifiziert';
$a->strings['Enable Tumblr Post Addon'] = 'Tumblr-Post-Addon aktivieren';
$a->strings['Post to Tumblr by default'] = 'Standardmäßig bei Tumblr veröffentlichen';
$a->strings['Import the remote timeline'] = 'Importiere die entfernte Timeline';
$a->strings['Subscribed tags'] = 'Abonnierte Tags';
$a->strings['Comma separated list of up to %d tags that will be imported additionally to the timeline'] = 'Durch Kommata getrennte Liste von bis zu  %d Tags, die zusätzlich in die Timeline importiert werden sollen';
$a->strings['Tumblr Import/Export'] = 'Tumblr Import/Export';
$a->strings['Post to Tumblr'] = 'Auf Tumblr veröffentlichen';
