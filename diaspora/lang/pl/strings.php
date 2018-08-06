<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Post to Diaspora"] = "Napisz do Diaspory";
$a->strings["Can't login to your Diaspora account. Please check username and password and ensure you used the complete address (including http...)"] = "Nie możesz zalogować się na swoje konto Diaspora. Sprawdź nazwę użytkownika i hasło i upewnij się, że użyłeś pełnego adresu (w tym http ...)";
$a->strings["Diaspora Export"] = "Eksportuj do Diaspory";
$a->strings["Enable Diaspora Post Addon"] = "Włącz dodatek Diaspora";
$a->strings["Diaspora username"] = "Nazwa użytkownika Diaspora";
$a->strings["Diaspora password"] = "Hasło Diaspora";
$a->strings["Diaspora site URL"] = "Adres URL witryny Diaspora";
$a->strings["Post to Diaspora by default"] = "Wyślij domyślnie do Diaspory";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Diaspora post failed. Queued for retry."] = "Post do Diaspora nie powiódł się. W kolejce do ponowienia.";
