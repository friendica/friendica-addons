<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Event Export"] = "Export událostí";
$a->strings["You can download public events from: "] = "Veřejné události si můžete stánout z:";
$a->strings["The user does not export the calendar."] = "Uživatel kalenář neexportuje.";
$a->strings["This calendar format is not supported"] = "Tento kalendářový formát není podporován.";
$a->strings["Export Events"] = "Export událostí";
$a->strings["If this is enabled, your public events will be available at"] = "Pokud je toto povoleno, vaše veřejné události budou viditelné na";
$a->strings["Currently supported formats are ical and csv."] = "Aktuálně podporované formáty jsou ical a csv.";
$a->strings["Enable calendar export"] = "Povolit export kalendáře";
$a->strings["Save Settings"] = "Uložit Nastavení";
