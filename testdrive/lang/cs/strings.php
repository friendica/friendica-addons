<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Administrator'] = 'Administrátor';
$a->strings['Your account on %s will expire in a few days.'] = 'Platnost Vašeho účtu na %s vyprší během několika dní.';
$a->strings['Your Friendica test account is about to expire.'] = 'Váš testovací účet na Friendica brzy vyprší.';
