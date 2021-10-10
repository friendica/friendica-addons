<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
$a->strings['Post to Wordpress'] = 'Postați pe Wordpress';
$a->strings['Wordpress Export'] = 'Export pe Wordpress';
$a->strings['Enable WordPress Post Addon'] = 'Activare Modul Postare pe Wordpress';
$a->strings['WordPress username'] = 'Utilizator WordPress ';
$a->strings['WordPress password'] = 'Parolă WordPress ';
$a->strings['WordPress API URL'] = 'URL Cheie API WordPress';
$a->strings['Post to WordPress by default'] = 'Postați implicit pe Wordpress';
$a->strings['Provide a backlink to the Friendica post'] = 'Oferiți un backlink către postarea de pe Friendica';
$a->strings['Don\'t post messages that are too short'] = 'Nu publica mesajele prea scurte';
$a->strings['Save Settings'] = 'Salvare Configurări';
$a->strings['Post from Friendica'] = 'Postați din Friendica';
$a->strings['Read the original post and comment stream on Friendica'] = 'Citiți publicația originală și fluxul de comentarii, pe Friendica';
