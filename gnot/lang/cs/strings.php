<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Gnot settings updated."] = "Nastavení Gnot aktualizováno.";
$a->strings["Gnot Settings"] = "Nastavení Gnot";
$a->strings["Allows threading of email comment notifications on Gmail and anonymising the subject line."] = "Umožnit řetězení emailových komentářových notifikací na Gmailu a anonymizací řádky předmětu.";
$a->strings["Enable this addon?"] = "Povolit tento addon/rozšíření?";
$a->strings["Submit"] = "Odeslat";
$a->strings["[Friendica:Notify] Comment to conversation #%d"] = "[Friendica:Upozornění] Komentář ke konverzaci #%d";
