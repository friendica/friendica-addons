<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['How many contacts to display on profile sidebar'] = 'Nombre de contacts à afficher dans le volet de profil';
$a->strings['Numfriends Settings'] = 'Réglages de Numfriends';
