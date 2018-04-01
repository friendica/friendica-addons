<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["From Address"] = "Z adresu";
$a->strings["Email address that stream items will appear to be from."] = "Adres e-mail, z którego będą przesyłane strumienie pochodzące z.";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Re:"] = "Re:";
$a->strings["Friendica post"] = "Friendica post";
$a->strings["Diaspora post"] = "Post Diaspora";
$a->strings["Feed item"] = "Element kanału";
$a->strings["Email"] = "E-mail";
$a->strings["Friendica Item"] = "Pozycja Friendica";
$a->strings["Upstream"] = "Nadrzędny";
$a->strings["Local"] = "Lokalny";
$a->strings["Enabled"] = "Włączone";
$a->strings["Email Address"] = "Adres e-mail";
$a->strings["Leave blank to use your account email address"] = "Pozostaw puste, aby użyć adresu e-mail swojego konta";
$a->strings["Exclude Likes"] = "Wykluczanie polubień";
$a->strings["Check this to omit mailing \"Like\" notifications"] = "Zaznacz to pole, aby pominąć wysyłanie powiadomień typu \"Lubię to\"";
$a->strings["Attach Images"] = "Dołącz zdjęcia";
$a->strings["Download images in posts and attach them to the email.  Useful for reading email while offline."] = "Pobierz zdjęcia w postach i dołącz je do wiadomości e-mail. Przydatny do czytania wiadomości e-mail w trybie offline.";
$a->strings["Mail Stream Settings"] = "Ustawienia strumienia poczty";
