<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['"pageheader" Settings'] = '"pageheader"-Einstellungen';
$a->strings['Message'] = 'Mitteilung';
$a->strings['Message to display on every page on this server (or put a pageheader.html file in your docroot)'] = 'Die Mitteilung, die auf jeder Seite dieses Knotens angezeigt werden soll (alternativ kann die Datei pageheader.html im Stammverzeichnis der Friendica Installation angelegt werden).';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['pageheader Settings saved.'] = 'pageheader-Einstellungen gespeichert.';
