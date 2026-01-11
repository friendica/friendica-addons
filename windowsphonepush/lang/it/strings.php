<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Enable WindowsPhonePush Addon'] = 'Abilita componente aggiuntivo WindowsPhonePush';
$a->strings['Push text of new item'] = 'Notifica il testo dei nuovi elementi';
$a->strings['Device URL'] = 'URL Dispositivo';
$a->strings['WindowsPhonePush Settings'] = 'Impostazioni WindowsPhonePush';
