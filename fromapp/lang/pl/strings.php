<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting.'] = 'Nazwa aplikacji, z której chcesz wyświetlać swoje posty. Oddziel różne nazwy aplikacji przecinkami. Zostanie losowo wybrana dla każdej lokalizacji.';
$a->strings['Use this application name even if another application was used.'] = 'Użyj tej nazwy aplikacji, nawet jeśli użyto innej aplikacji.';
$a->strings['FromApp Settings'] = 'Ustawienia FromApp';
