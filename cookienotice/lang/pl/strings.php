<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["This website uses cookies. If you continue browsing this website, you agree to the usage of cookies."] = "Ta strona używa plików cookie. Jeśli będziesz kontynuować przeglądanie tej strony, zgadzasz się na użycie plików cookie.";
$a->strings["OK"] = "OK";
$a->strings["\"cookienotice\" Settings"] = "Ustawienia \"plików cookie\"";
$a->strings["<b>Configure your cookie usage notice.</b> It should just be a notice, saying that the website uses cookies. It is shown as long as a user didnt confirm clicking the OK button."] = "<b>Skonfiguruj zawiadomienie o użyciu plików cookie.</b> Powinien to być po prostu komunikat, że strona korzysta z plików cookie. Jest wyświetlany, o ile użytkownik nie potwierdził, klikając przycisk OK.";
$a->strings["Cookie Usage Notice"] = "Użyciu plików cookie";
$a->strings["The cookie usage notice"] = "Powiadomienie o użyciu plików cookie";
$a->strings["OK Button Text"] = "Tekst przycisku OK";
$a->strings["The OK Button text"] = "Tekst przycisku OK";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["cookienotice Settings saved."] = "Zapisano stawienia plików cookie.";
$a->strings["This website uses cookies to recognize revisiting and logged in users. You accept the usage of these cookies by continue browsing this website."] = "Ta strona używa plików cookie do rozpoznawania ponownych odwiedzin i zalogowanych użytkowników. Akceptujesz użycie tych plików cookie, kontynuując przeglądanie tej witryny.";
