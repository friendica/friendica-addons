<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Post to Wordpress"] = "Příspěvky do WordPress";
$a->strings["Wordpress Export"] = "Wordpress Export";
$a->strings["Enable WordPress Post Plugin"] = "Povolit rozšíření na WordPress";
$a->strings["WordPress username"] = "WordPress uživatelské jméno";
$a->strings["WordPress password"] = "WordPress heslo";
$a->strings["WordPress API URL"] = "URL adresa API WordPress";
$a->strings["Post to WordPress by default"] = "standardně posílat příspěvky na WordPress";
$a->strings["Provide a backlink to the Friendica post"] = "Poskytuje zpětný link na Friendica příspěvek";
$a->strings["Don't post messages that are too short"] = "Neposílat zprávy, které jsou příliš krátké";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Post from Friendica"] = "Příspěvek z Friendica";
$a->strings["Read the original post and comment stream on Friendica"] = "Přečíst si originální příspěvek a komentáře na Friendica";
