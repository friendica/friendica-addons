<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
;
$a->strings["Post to Wordpress"] = "Odesílat na WordPress";
$a->strings["Wordpress Export"] = "Exportovat do WordPress";
$a->strings["Enable WordPress Post Addon"] = "Povolit doplněk WordPress Post";
$a->strings["WordPress username"] = "WordPress uživatelské jméno";
$a->strings["WordPress password"] = "WordPress heslo";
$a->strings["WordPress API URL"] = "URL adresa API WordPress";
$a->strings["Post to WordPress by default"] = "standardně posílat příspěvky na WordPress";
$a->strings["Provide a backlink to the Friendica post"] = "Poskytnout zpětný odkaz na příspěvek Friendica";
$a->strings["Text for the backlink, e.g. Read the original post and comment stream on Friendica."] = "Text pro zpětné odkazy, např. \"Přečtěte si původní příspěvek a komentáře na Friendica\".";
$a->strings["Don't post messages that are too short"] = "Neposílejte zprávy, které jsou příliš krátké";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Read the orig­i­nal post and com­ment stream on Friendica"] = "Přečtěte si původní příspěvek a komentáře na Friendica";
$a->strings["Post from Friendica"] = "Příspěvek z Friendica";
