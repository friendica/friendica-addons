<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Post to blogger"] = "Opublikuj w bloggerze";
$a->strings["Blogger Export"] = "Eksport Bloggera";
$a->strings["Enable Blogger Post Addon"] = "Włącz dodatek Blogger";
$a->strings["Blogger username"] = "Nazwa użytkownika Bloggera";
$a->strings["Blogger password"] = "Hasło Bloggera";
$a->strings["Blogger API URL"] = "Adres URL interfejsu API Blogger";
$a->strings["Post to Blogger by default"] = "";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Post from Friendica"] = "Post od Friendica";
