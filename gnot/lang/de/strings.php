<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Gnot Settings'] = 'Gnot-Einstellungen';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Enable this addon?'] = 'Dieses Addon aktivieren?';
$a->strings['Allows threading of email comment notifications on Gmail and anonymising the subject line.'] = 'Erlaubt das Veröffentlichen von E-Mail-Kommentar-Benachrichtigungen bei Gmail mit anonymisiertem Betreff';
$a->strings['[Friendica:Notify] Comment to conversation #%d'] = '[Friendica-Benachrichtigung] Kommentar zum Beitrag #%d';
