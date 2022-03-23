<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Save Settings'] = 'Zapisz ustawienia';
$a->strings['Show callstack'] = 'Pokaż stos wywołań';
$a->strings['Show detailed performance measures in the callstack. When deactivated, only the summary will be displayed.'] = 'Pokaż szczegółowe miary wydajności w stosie wywołań. Po dezaktywacji wyświetlane będzie tylko podsumowanie.';
$a->strings['Minimal time'] = 'Czas minimalny';
$a->strings['Minimal time that an activity needs to be listed in the callstack.'] = 'Minimalny czas, przez który czynność musi być wymieniona w stosie wywołań.';
$a->strings['Database: %s/%s, Network: %s, Rendering: %s, Session: %s, I/O: %s, Other: %s, Total: %s'] = 'Baza danych: %s/%s, Sieć: %s, Rendering: %s, Sesja: %s, Wej/Wyj: %s, Inne: %s, Łącznie: %s';
$a->strings['Class-Init: %s, Boot: %s, Init: %s, Content: %s, Other: %s, Total: %s'] = 'Rozpoczęcie klasy: %s, Rozruch: %s, Rozpoczęcie: %s, Zawartość: %s, Inne: %s, Łącznie: %s';
