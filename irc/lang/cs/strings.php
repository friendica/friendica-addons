<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["IRC Settings"] = "IRC Nastavení";
$a->strings["Channel(s) to auto connect (comma separated)"] = "Kanál(y) pro automatické připojení(oddělené čárkou)";
$a->strings["Popular Channels (comma separated)"] = "Oblíbené Kanály (oddělené čárkou)";
$a->strings["Submit"] = "Odeslat";
$a->strings["IRC settings saved."] = "IRC Nastavení uloženo.";
$a->strings["IRC Chatroom"] = "IRC Místnost";
$a->strings["Popular Channels"] = "Oblíbené kanály";
