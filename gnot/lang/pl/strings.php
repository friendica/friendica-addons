<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["Gnot settings updated."] = "Zaktualizowano ustawienia Gnot.";
$a->strings["Gnot Settings"] = "Ustawienia Gnot";
$a->strings["Allows threading of email comment notifications on Gmail and anonymising the subject line."] = "Umożliwia nawiązywanie powiadomień o komentarzach e-mail w Gmailu i anonimizowanie wiersza tematu.";
$a->strings["Enable this addon?"] = "Włączyć ten dodatek?";
$a->strings["Submit"] = "Wyślij";
$a->strings["[Friendica:Notify] Comment to conversation #%d"] = "[Friendica:Powiadomienie] Komentarz do rozmowy #%d";
