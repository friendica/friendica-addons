<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['"pageheader" Settings'] = 'Impostazioni "Intestazione pagina"';
$a->strings['Message'] = 'Messaggio';
$a->strings['Message to display on every page on this server (or put a pageheader.html file in your docroot)'] = 'Il messaggio da mostrare su ogni pagina di questo server (puoi anche aggiungere un file pageheader.html nella root)';
$a->strings['Save Settings'] = 'Salva Impostazioni';
