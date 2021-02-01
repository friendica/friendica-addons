<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["Permission denied."] = "Odmowa uprawnień.";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Client ID"] = "Identyfikator ID klienta";
$a->strings["Client Secret"] = " Tajny klucz klienta";
$a->strings["Error when registering buffer connection:"] = "Błąd podczas rejestrowania połączenia z buforem:";
$a->strings["You are now authenticated to buffer. "] = "Jesteś teraz uwierzytelniony w buforze.";
$a->strings["return to the connector page"] = "powrót do strony połączenia";
$a->strings["Post to Buffer"] = "Opublikuj w buforze";
$a->strings["Buffer Export"] = "Eksportuj Bufor";
$a->strings["Authenticate your Buffer connection"] = "Uwierzytelnij swoje połączenie z buforem";
$a->strings["Enable Buffer Post Addon"] = "Włącz dodatek bufora pocztowego";
$a->strings["Post to Buffer by default"] = "Wyślij domyślnie post do bufora";
$a->strings["Check to delete this preset"] = "Zaznacz, aby usunąć to ustawienie wstępne";
$a->strings["Posts are going to all accounts that are enabled by default:"] = "Wpisy są wysyłane na wszystkie konta, które są domyślnie włączone:";
