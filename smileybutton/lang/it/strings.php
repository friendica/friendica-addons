<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Smileybutton settings'] = 'Impostazioni "Bottone faccine"';
$a->strings['You can hide the button and show the smilies directly.'] = 'Puoi nascondere il bottone e mostrare le faccine direttamente.';
$a->strings['Hide the button'] = 'Nascondi il bottone';
$a->strings['Save Settings'] = 'Salva Impostazioni';
