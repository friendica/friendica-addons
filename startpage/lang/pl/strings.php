<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Startpage Settings'] = 'Ustawienia strony startowej';
$a->strings['Home page to load after login  - leave blank for profile wall'] = 'Strona główna do załadowania po zalogowaniu - pozostaw puste pole dla strony profilowej';
$a->strings['Examples: &quot;network&quot; or &quot;notifications/system&quot;'] = 'Przykłady: &quot;network&quot; lub &quot;notifications/system&quot; albo &quot;profile/Nazwa profilu&quot;';
$a->strings['Submit'] = 'Prześlij';
