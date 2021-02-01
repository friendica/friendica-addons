<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
;
$a->strings["IRC Settings"] = "Nastavení IRC";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Channel(s) to auto connect (comma separated)"] = "Kanál(y) pro automatické připojení (oddělené čárkami)";
$a->strings["List of channels that shall automatically connected to when the app is launched."] = "Seznam kanálů, které budou při spuštění aplikace automaticky připojeny.";
$a->strings["Popular Channels (comma separated)"] = "Populární kanály (oddělené čárkami)";
$a->strings["List of popular channels, will be displayed at the side and hotlinked for easy joining."] = "Seznam populárních kanálů, bude zobrazen na straně a bude obsahovat odkazy pro snadné připojení.";
$a->strings["IRC settings saved."] = "IRC Nastavení uloženo.";
$a->strings["IRC Chatroom"] = "IRC Místnost";
$a->strings["Popular Channels"] = "Populární kanály";
