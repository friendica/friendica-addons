<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["\"Blockem\""] = "";
$a->strings["Hides user's content by collapsing posts. Also replaces their avatar with generic image."] = "Ukrywa zawartość użytkownika, zwijając posty. Zastępuje również awatar wygenerowanym obrazem.";
$a->strings["Comma separated profile URLS:"] = "Rozdzielone przecinkami adresy URL profilu:";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["BLOCKEM Settings saved."] = "BLOCKEM Ustawienia zapisane.";
$a->strings["Hidden content by %s - Click to open/close"] = "Ukryta zawartość przez %s - Kliknij, aby otworzyć/zamknąć";
$a->strings["Unblock Author"] = "Odblokuj autora";
$a->strings["Block Author"] = "Zablokuj autora";
$a->strings["blockem settings updated"] = "ustawienia blockem zostały zaktualizowane";
