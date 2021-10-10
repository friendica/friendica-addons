<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Wordpress'] = 'Bei WordPress veröffentlichen';
$a->strings['Wordpress Export'] = 'Wordpress Export';
$a->strings['Enable WordPress Post Addon'] = 'WordPress Addon aktivieren';
$a->strings['WordPress username'] = 'WordPress-Benutzername';
$a->strings['WordPress password'] = 'WordPress-Passwort';
$a->strings['WordPress API URL'] = 'WordPress-API-URL';
$a->strings['Post to WordPress by default'] = 'Standardmäßig auf WordPress veröffentlichen';
$a->strings['Provide a backlink to the Friendica post'] = 'Einen Link zurück zum Friendica-Beitrag hinzufügen';
$a->strings['Text for the backlink, e.g. Read the original post and comment stream on Friendica.'] = 'Text für den Link zurück, z.B. lies den Original-Post und die Kommentare auf Friendica.';
$a->strings['Don\'t post messages that are too short'] = 'Zu kurze Mitteilungen nicht posten';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Read the orig­i­nal post and com­ment stream on Friendica'] = 'Lies den Original-Post und die Kommentare auf Friendica';
$a->strings['Post from Friendica'] = 'Post via Friendica';
