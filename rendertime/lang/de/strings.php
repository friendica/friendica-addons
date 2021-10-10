<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Database: %s/%s, Network: %s, Rendering: %s, Session: %s, I/O: %s, Other: %s, Total: %s'] = 'Datenbank: %s/%s, Netzwerk: %s, Darstellung: %s, Sitzung: %s, I/O: %s, Sonstiges: %s, Gesamt: %s';
$a->strings['Class-Init: %s, Boot: %s, Init: %s, Content: %s, Other: %s, Total: %s'] = 'Class-Init: %s, Boot: %s, Init: %s, Inhalt: %s, Sonstiges: %s, Gesamt: %s';
