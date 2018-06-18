<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Remote Permissions Settings"] = "Nastavení Vzdálených oprávnění";
$a->strings["Allow recipients of your private posts to see the other recipients of the posts"] = "Umožnit příjemcům Vašich soukromých příspěvků vidět ostatní příjemce příspěvků";
$a->strings["Submit"] = "Odeslat";
$a->strings["Remote Permissions settings updated."] = "Nastavení Vzdálených opravnění aktualizováno.";
$a->strings["Visible to:"] = "Viditelné pro:";
$a->strings["Visible to"] = "Viditelné pro";
$a->strings["may only be a partial list"] = "pouze pro část seznamu";
$a->strings["Global"] = "Globální";
$a->strings["The posts of every user on this server show the post recipients"] = "Příspěvek každého uživatele na tomto serveru zobrazuje příjemce příspěvků";
$a->strings["Individual"] = "Individuálové";
$a->strings["Each user chooses whether his/her posts show the post recipients"] = "Každý uživatel si zvolí, zda-li jeho/její příspěvek zobrazí příjemce příspěvku.";
$a->strings["Settings updated."] = "Nastavení aktualizováno.";
