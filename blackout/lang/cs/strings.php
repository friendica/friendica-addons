<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this"] = "Datum konce odstávky je před datem zahájení odstávky, prosím opravte to";
$a->strings["Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>."] = "Prosím zkontrolujte svá aktuální nastavení pro odstávku. Začne <strong>%s</strong> a skončí <strong>%s</strong>.";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Redirect URL"] = "URL přesměrování";
$a->strings["all your visitors from the web will be redirected to this URL"] = "všichni vaši návštěvníci z webu budou přesměrování na tuto URL adresu";
$a->strings["Begin of the Blackout"] = "Zahájení odstávky";
$a->strings["Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute."] = "Formát je <tt>RRRR-MM-DD hh:mm</tt>; <em>RRRR</em> rok, <em>MM</em> měsíc, <em>DD</em> den, <em>hh</em> hodina a <em>mm</em> minuta.";
$a->strings["End of the Blackout"] = "Konec odstávky";
$a->strings["<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out should the blackout is still in place."] = "<strong>Poznámka</strong>: Přesměrování bude aktivní od chvíle, kdy stisknete tlačítko pro odeslání. Aktuálně přihlášení uživatelé <strong>nebudou</strong> odhlášeni, ale po odhlášení se po dobu trvání odstávky nebudou moci znovu přihlásit.";
