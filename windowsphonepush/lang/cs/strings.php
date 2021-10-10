<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['WindowsPhonePush settings updated.'] = 'Nastavení WindowsPhonePush aktualizována';
$a->strings['WindowsPhonePush Settings'] = 'Nastavení WindowsPhonePush';
$a->strings['Enable WindowsPhonePush Addon'] = 'Povolit doplněk WindowsPhonePush';
$a->strings['Push text of new item'] = 'Načíst text nové položky';
$a->strings['Save Settings'] = 'Uložit nastavení';
