<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
$a->strings['Post to blogger'] = 'Postați pe Blogger';
$a->strings['Blogger Export'] = 'Export pe Blogger ';
$a->strings['Enable Blogger Post Addon'] = 'Activare Modul Postare pe Blogger ';
$a->strings['Blogger username'] = 'Utilizator Blogger';
$a->strings['Blogger password'] = 'Parolă Blogger ';
$a->strings['Blogger API URL'] = 'URL Cheie API Blogger ';
$a->strings['Post to Blogger by default'] = 'Postați implicit pe Blogger';
$a->strings['Save Settings'] = 'Salvare Configurări';
$a->strings['Post from Friendica'] = 'Postați din Friendica';
