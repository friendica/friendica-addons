<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Fromapp settings updated.'] = 'Nastavení FromApp aktualizována.';
$a->strings['FromApp Settings'] = 'Nastavení FromApp';
$a->strings['The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting.'] = 'Název aplikace, ze které píšete své příspěvky, kterou chcete zobrazovat. Oddělujte různé názvy aplikací čárkou. Pro každý příspěvek bude zvolena náhodná.';
$a->strings['Use this application name even if another application was used.'] = 'Použít toto jméno aplikace, i když byla použita jiná aplikace';
$a->strings['Save Settings'] = 'Uložit nastavení';
