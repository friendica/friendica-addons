<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to Wordpress'] = 'Invia a Wordpress';
$a->strings['Enable Wordpress Post Addon'] = 'Abilita Componente Aggiuntivo di Pubblicazione Wordpress';
$a->strings['Wordpress username'] = 'Nome utente Wordpress';
$a->strings['Wordpress password'] = 'Password Wordpress';
$a->strings['WordPress API URL'] = 'Indirizzo API Wordpress';
$a->strings['Post to Wordpress by default'] = 'Pubblica su Wordpress per impostazione predefinita';
$a->strings['Provide a backlink to the Friendica post'] = 'Inserisci un collegamento al messaggio originale su Friendica';
$a->strings['Text for the backlink, e.g. Read the original post and comment stream on Friendica.'] = 'Testo per il collegamento al messaggio originale, p.e. Leggi il messaggio originale e i commenti su Friendica.';
$a->strings['Don\'t post messages that are too short'] = 'Non inviare messaggi troppo corti';
$a->strings['Wordpress Export'] = 'Esporta a Wordpress';
$a->strings['Read the orig­i­nal post and com­ment stream on Friendica'] = 'Leggi il messaggio originale e i commenti su Friendica';
$a->strings['Post from Friendica'] = 'Messaggio da Friendica';
