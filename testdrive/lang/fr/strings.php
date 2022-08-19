<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Administrator'] = 'Administrateur';
$a->strings['Your account on %s will expire in a few days.'] = 'Votre compte sur %s va expirer dans quelques jours.';
$a->strings['Your Friendica test account is about to expire.'] = 'Votre compte Friendica de test est sur le point d\'expirer.';
