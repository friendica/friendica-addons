<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Use Cat as Avatar'] = 'Použít kočku jako avatar';
$a->strings['More Random Cat!'] = 'Další náhodné kočky!';
$a->strings['Reset to email Cat'] = 'Resetovat a e-mailovat kočku';
$a->strings['Cat Avatar Settings'] = 'Nastavení Cat Avatar';
$a->strings['The cat hadn\'t found itself.'] = 'Kočka se nenašla.';
$a->strings['There was an error, the cat ran away.'] = 'Vyskytla se chyba, kočka utekla.';
$a->strings['Profile Photos'] = 'Profilové fotky';
$a->strings['Meow!'] = 'Mňau!';
